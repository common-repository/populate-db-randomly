jQuery(document).ready(function($){
	
	$('#dummy_data_source').change(function(){
		if ( $('#dummy_data_source').val()!=='lipsum' )
			$('.hide').hide('slow');
		else
			$('.hide').show('slow');
	});
	
});