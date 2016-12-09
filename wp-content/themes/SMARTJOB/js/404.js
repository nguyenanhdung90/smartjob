(function ($) {
jQuery(document).ready (function ($) {

	var home_url	= $('#google-url').html(),
		url			= $(location).attr("href").replace(home_url, ""),
		array_url	= url.split("/"),
		q = $("form#google-search input#q").val(),
		key = "";

	for(i=0; i<array_url.length; i++) {
		if(array_url[i].indexOf("?doing_wp_cron") == -1){
			key += array_url[i] + " ";
		}
	}
	
	$('<input type="text" id="google-key" name="" value="'+ key + '">').appendTo($('#google-search'));

	$("form#google-search input#q").val(q + $("input#google-key").val());

	$("input#google-key").bind("change",function(){
		$("form#google-search input#q").val(q + $("input#google-key").val());
	});

});
})(jQuery);