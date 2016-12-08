(function($){

	$(document).ready (function () {

		var url = $(location).attr("href"),
			home_url = $('#google-url').html();

		url = url.replace(home_url, "");
		array_url = url.split("/");

		var key = "";
		for(i=0; i<array_url.length; i++) {
			key += array_url[i] + " ";
		}
		
		// $('#google-url').text(array_url[2]);
		$('#google-search').append( $('<input type="text" id="goolge-key" name="q" class="ui-input-text ui-body-c ui-corner-all ui-shadow-inset" value="'+key+'">') );
	
	});

})(jQuery);