window.fbAsyncInit = function() {
	// init the FB JS SDK
	FB.init({
		appId      : facebook_auth.appID,//facebook_auth.appID, //'649507738451547',                        // App ID from the app dashboard
		//channelUrl : '//192.168.10.112/channel.html', // Channel file for x-domain comms
		status     : true,                                 // Check Facebook Login status
		xfbml      : true                                  // Look for social plugins on the page
	});


};
// Load the SDK asynchronously
(function(d, s, id){
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) {return;}
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/en_US/all.js";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
(function($){
	$(document).ready(function(){
		$('#facebook_auth_btn').bind('click', function(event){
		event.preventDefault();
		if ( FB ){
			FB.login(function(response) {
				if (response.authResponse) {
					access_token = response.authResponse.accessToken; //get access token
					user_id = response.authResponse.userID; //get FB UID

					FB.api('/me', function(response) {
						user_email = response.email; //get user email
						// you can store this data into your database
						var params = {
							url 	: et_globals.ajaxURL,
							type 	: 'post',
							data 	: {
								action: 'et_facebook_auth',
								content: response
							},
							beforeSend: function(){
							},
							success: function(resp){
								if ( resp.success && typeof resp.data.redirect_url != 'undefined' ){
									window.location = resp.data.redirect_url;
								}
								else if ( resp.success && typeof resp.data.user != 'undefined' ){
									window.location.reload(true);
								} else if ( resp.msg ) {
									alert( resp.msg);
								}
							},
							complete: function(){
								//$('#facebook_auth_btn').loader('unload');
							}
						}
						$.ajax(params);

					});

				} else {
					//user hit cancel button
					console.log('User cancelled login or did not fully authorize.');
				}
			}, {
				scope: 'email,user_about_me'
			});
		}
		});

	});

})(jQuery);