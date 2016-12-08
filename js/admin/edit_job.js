(function($){

$(document).ready(function(){
	// set autocomplete
	jQuery('#et_company').autocomplete({
		source: JSON.parse(jQuery('#et_companies').html()),
		focus:function(event, ui){
			$('#et_company').val(ui.item.label);			
			return false;
		},
		select: function(event, ui){
			$('#et_company').val(ui.item.label);
			$('input[name=et_author]').val(ui.item.value);
			return false;
		}
	});
	
	jQuery('#et_date').datepicker({
		dateFormat : et_data.dateFormat,
		defaultDate : new Date(jQuery('#et_date').val())
	});

	jQuery('.datepicker').datepicker({
		dateFormat : et_data.dateFormat
	});

	$('#et_location').blur(function(event){
		var address = $(this).val();
		//gmaps = new GMaps
		GMaps.geocode({
			address : address,
			callback : function(results, status){
				if (status == 'OK'){
					var latlng = results[0].geometry.location;
						$('#et_lat').val(latlng.lat());
						$('#et_lng').val(latlng.lng());
				}
			}
		});
	});

	jQuery('#job_categorychecklist li input, #job_typechecklist li input, #job_typechecklist-pop li input, #job_categorychecklist-pop li input').change(function(event){
		var current 		= $(this),
			parent_list 	= current.closest('ul.categorychecklist'),
			children 		= parent_list.find('li input:checked[value!=' + current.val() + ']');

		if (parent_list.attr('id') == 'job_categorychecklist')
			$('#job_categorychecklist-pop li input').removeAttr('checked');
		if (parent_list.attr('id') == 'job_categorychecklist-pop')
			$('#job_categorychecklist li input').removeAttr('checked');
		if (parent_list.attr('id') == 'job_typechecklist')
			$('#job_typechecklist-pop li input').removeAttr('checked');
		if (parent_list.attr('id') == 'job_typechecklist-pop')
			$('#job_typechecklist li input').removeAttr('checked');

		if (current.is(':checked')){
			children.each(function(){
				$(this).removeAttr('checked');
			});
		}
	});
});

})(jQuery);