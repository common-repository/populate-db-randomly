
<div class='wrap'>
	<div id="icon-edit" class="icon32"></div>
	<h2>Populate your db</h2>
	<p>The data will be populate from an external site, so it can take some time to complete. Be patience :)</p>
	<div id="message" class="updated">
		<p>
			Use this plugin for testing your theme layout only or performance issues. Don't copy content from another site just for copying.
		</p>
	</div> 
	<form method="POST" >
		<?php wp_nonce_field( PopulateDbRandomly::createDataAction, PopulateDbRandomly::createDataNonceName ); ?>
		<table class='form-table'>
			<tbody>

				<tr>
					<th><label for=""><strong>Existing Random Categories: <?php echo $categoriesCount; ?></strong></label></th>
				</tr>
				<tr>
					<th><label for=""><strong>Existing Random Posts: <?php echo $postCount; ?></strong></label></th>
				</tr>
				<tr>
					<th><label for="dummy_data_type">Dummy data source</label></th>
					<td>
						<select   id="dummy_data_source" name="dummy_data_source" >
							<option  value="lipsum">   lipsum.com </option>
							<option value="cultofmac">  cultofmac.com </option>
							<option value="matt">  ma.tt </option>
						</select>
					</td>
				</tr>
				<tr class="hide">
					<th><label for="category_count">How many categories?</label></th>
					<td><input type="text" id="category_count" name="category_count" class="small-text" value="10" /> </td>
				</tr>
				<tr>
					<th><label for="post_count">How many post per category?</label></th>
					<td><input type="text" id="post_count" name="post_count" class="small-text" value="5" /> </td>
				</tr>
				<tr>
					<th><label for="post_count">Create html elements page</label></th>
					<td><input type="checkbox" id="create_elements_page" name="create_elements_page" class="small-text" value="1" /> </td>
				</tr>

			</tbody>
		</table>
		<br/><br/>

		<input class='button-primary' type='submit' name='save' value='<?php _e( 'Create random data' ); ?>' id='save' />
		<input class='button' type='submit' name='remove' value='<?php _e( 'Remove all random data' ); ?>' id='remove' />

	</form>

</div>