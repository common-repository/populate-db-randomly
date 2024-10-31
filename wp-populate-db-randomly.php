<?php
/*
Plugin Name: Populate database randomly
Version: 0.3
Description: Populate a database with dummy (lorem) data
Author: mustela
Text Domain: populate-db-randomly
*/

class PopulateDbRandomly{
	
	const paragrah	= "paras";
	const words		= "words";
	const bytes		= "bytes";
	const lists		= "lists";
	const populateDbRandomlyKey = '_populateDbRandomly';
	const createDataAction = 'pdbr_createData';
	const createDataNonceName = 'pdbr_create_data';
	
	public function __construct() {
		
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		
		add_action('load-toplevel_page_pop-db-randomly', array( $this, 'adminInit' ) );
		
		add_action( 'admin_print_scripts-toplevel_page_pop-db-randomly', array( $this, 'addScripts' ) );
		
	}
	
	public function addScripts(){

		wp_enqueue_script('populate-db-randomly', $this->getPluginDirUrl(). '/wp-populate-db-randomly.js', array('jquery'));
	}
	
	private function getPluginDirUrl(){
		
		//In case you are using symbolic links, it will help you :)
		if ( defined('DEV_ENV') && DEV_ENV ){
			return WP_PLUGIN_URL.'/wp-populate-db-randomly';
		}
		else
			return plugin_dir_url( __FILE__ );
	}
	public function adminInit(){
		
		if ( !empty($_POST) && check_admin_referer( self::createDataAction, self::createDataNonceName ) ) {
			
			if ( $this->getPostValue( 'remove' ) )
				$this->removeRandomData( true );
			else{
				$categoryCount	= (int)$this->getPostValue( 'category_count' );
				$postCount		= (int)$this->getPostValue( 'post_count' );
				$dummySource	= $this->getPostValue( 'dummy_data_source' );
				
				switch( $dummySource ){
					case "lipsum":
						$this->createCategories( $categoryCount, $postCount );
						break;
					case "cultofmac":
						$this->createPosts( "http://cultofmac.com.feedsportal.com/c/33797/f/606249/index.rss", $postCount );
						break;
					case "matt":
						$this->createPosts( "http://ma.tt/feed/", $postCount );
						break;
				}
				
				if ( isset( $_POST['create_elements_page'] ) )
					$this->createPageWithElements ( );
				
			}
		}
		
	}
	private function removeRandomData( $all ){
		
		if ( $all ){
			
			$savedData = $this->getSavedData();
			foreach( $savedData['categories'] as $category ){
				wp_delete_category( $category );
				unset( $savedData['categories'][$category] );
			}
			
			foreach( $savedData['posts'] as $postId ){
				wp_delete_post( $postId );
				unset( $savedData['posts'][$postId] );
			}
			
			wp_delete_post( $savedData['elementsPage'] );
			
			$this->updateIds($savedData);
			
		}
	}
	
	private function getPostValue( $key ){
		
		if ( $_POST && isset( $_POST[$key] ) )
			return  $_POST[$key];
		
		return false;
	}
	
	public function admin_menu(){
		
		add_menu_page('Populate DB Randomly', 'Populate DB Randomly', 'manage_options', 'pop-db-randomly', array( $this, 'showForm' ) );
		 
	}
	
	public function generateLoremDummy( $amount, $what ){
		
		$lipsum = simplexml_load_file("http://www.lipsum.com/feed/xml?amount={$amount}&what={$what}&start=0");
		
		if ( $lipsum )
			return (string)$lipsum->lipsum;
		
	}
	
	private function getSavedData(){
		
		$generatedData = get_option( self::populateDbRandomlyKey );
		
		if ( ! $generatedData )
			$generatedData = array('categories'=>array(), 'posts' => array(), 'elementsPage'=>false); 
		
		return $generatedData;
		
	}
	
	private function createPosts( $sourceUrl, $count ){
		
		$generatedData = $this->getSavedData();

		// Get a SimplePie feed object from the specified feed source.
		$rss = fetch_feed( $sourceUrl );

		if ( is_wp_error( $rss ) ) 
			throw new Exception( $rss->get_error_message() );
		

		// Figure out how many total items there are, but limit it to 5. 
		$maxitems = $rss->get_item_quantity( $count ); 

		// Build an array of all the items, starting with element 0 (first element).
		$rss_items = $rss->get_items( 0, $maxitems );


		foreach ( $rss_items as $item ) {
			
			
			$category = $item->get_category();
			$categoryId = false;
			
			if ( $category ){
 				 $categoryId = wp_create_category (  $category->get_label()  );
				 $generatedData['categories'][$categoryId] = $categoryId;
			}
			
			$currentUserId	= get_current_user_id();
			$postTitle		= $item->get_title();
			$postContent	= $item->get_content();
			
			$postData = array('post_status' => 'publish', 'post_type' => 'post', 'post_author' => $currentUserId,
							'post_content' => $postContent, 'post_title' => $postTitle, 'post_category' => array( $categoryId ) );

			$postId = wp_insert_post( $postData );
			
			$generatedData['posts'][$postId] = $postId;
			
		}
		
		$this->updateIds( $generatedData );
			
	}
	
					
	private function updateIds( $newData ){
		
		update_option(self::populateDbRandomlyKey, $newData);
	} 
	
	public function createCategories( $count = 10, $postByCategory = 5 ){
		
		$generatedData = $this->getSavedData();
			
		for( $i = 0; $i<=$count-1; $i++ ){
			
			$title = $this->generateLoremDummy( 3, self::words );
			$categoryId = wp_create_category ( $title );
			
			if ( is_wp_error(  $categoryId ) ) 
				return false;
			
			$generatedData['categories'][$categoryId] = $categoryId;
			
			for ( $j = 0; $j<=$postByCategory-1; $j++ ){
				
				$postId = $this->insertPost( $categoryId );
				
				if ( is_wp_error(  $categoryId ) ) 
					return false;
				
				$generatedData['posts'][$postId] = $postId;
				
			}
		}
		
		$this->updateIds( $generatedData );
		
		return true;
	}
	
	
	private function getCountCategories(){
		
		$generatedData = $this->getSavedData();
			
		if ( $generatedData && isset( $generatedData['categories'] ) )
			return count( $generatedData['categories'] );
		
		return 0;
	}
	
	private function getCountPost(){
		
		$generatedData = $this->getSavedData();
			
		if ( $generatedData && isset( $generatedData['posts'] ) )
			return count( $generatedData['posts'] );
		
		return 0;
	}
	
	private function insertPost( $categoryId = false ){
		
			$currentUserId = get_current_user_id();
			$postTitle = $this->generateDummy( 3, self::words );
			$postContent = $this->generateDummy( 3, self::paragrah );
			
			$postData = array('post_status' => 'publish', 'post_type' => 'post', 'post_author' => $currentUserId,
							'post_content' => $postContent, 'post_title' => $postTitle, 'post_category'=>array( $categoryId ));

			$postId = wp_insert_post( $postData );
			
			return $postId;
			
	}
	 
	public function showForm(){
		
		$categoriesCount = $this->getCountCategories();
		$postCount		 = $this->getCountPost();
		require_once 'admin-view.php';
		
	}
	
	public function createPageWithElements(){
		
		$generatedData = $this->getSavedData();
		
		// First, check if already exists a page
		if ( $generatedData['elementsPage'] ) {
			wp_delete_post( $generatedData['elementsPage'] );
		}
		
		$currentUserId = get_current_user_id();
		
		ob_start();
		require_once( "elements.html" );
		$content = ob_get_clean();
		
		$pageData = array('post_status' => 'publish', 'post_type' => 'page', 'post_author' => $currentUserId,
						  'post_content' => $content, 'post_title' => 'Elements');

		$pagetId = wp_insert_post( $pageData );
		
		$generatedData['elementsPage'] = $pagetId;
		
		$this->updateIds( $generatedData );
		
	}
	
}


$populateDbRandomly = new PopulateDbRandomly();