<?php
/**
 * Template Name: Reset Password
 */

 et_get_mobile_header('mobile');

?>
<div data-role="content" class="post-classified" >
    <?php
    $user_login = isset($_REQUEST['user_login']) ? $_REQUEST['user_login'] :'';
    $key        = isset($_REQUEST['key']) ? $_REQUEST['key'] :'';
    ?>
    <div class="form-account">
        <form action="" id="reset_password" class="reset_password"novalidate="novalidate">
            <input type="hidden" value="<?php echo $user_login;?>" name="user_login" id="user_login">
            <input type="hidden" value="<?php echo $key;?>" name="user_key" id="user_key">
            <input name="action" value="et_reset_password" type="hidden">
            <div class="form-item form-group" id="">
                <label><?php _e("New Password",ET_DOMAIN);?></label>
                <div class="controls">
                    <input type="password" class="required"  id="user_new_pass" class="bg-default-input " name="user_new_pass">
                </div>
            </div>
            <div class="form-item form-group" id="">
                <label>Retype New Password</label>
                <div class="controls">
                    <input type="password" class="required"   id="user_pass_again" class="bg-default-input " name="user_pass_again">
                </div>
            </div>
            <div class="line-hr"></div>
            <div class="form-item form-group">
                <button type="submit" class="btn  btn-primary" id="submit_profile"><?php _e('SAVE CHANGE',ET_DOMAIN);?></button>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
	(function($){
		$(document).on('pageinit' , function () {
			$("form.reset_password").on('submit',function(e){
				e.preventDefault();
				var user_key 	= $(e.currentTarget).find('input[name=user_key]').val(),
				user_login 		= $(e.currentTarget).find('input[name=user_login]').val(),
				user_new_pass 	= $(e.currentTarget).find('input[name=user_new_pass]').val(),
				user_pass_again = $(e.currentTarget).find('input[name=user_pass_again]').val();
				if(user_new_pass != user_pass_again){
					alert('Password is not equal');
					return false;
				}

				$.ajax({
						url 	: et_globals.ajaxURL,
						type 	: 'post',
						contentType	: 'application/x-www-form-urlencoded;charset=UTF-8',
						data	: {
							action		: 'et_reset_password',
							user_login 	: user_login,
							user_key 	: user_key,
							user_pass 	: user_new_pass,
							user_pass_again: user_pass_again,
						},
						beforeSend : function() {
							$.mobile.showPageLoadingMsg();
						},
						success : function (response) {
							$.mobile.hidePageLoadingMsg();
							if(response.success){
								alert(response.msg);
								//setTimeout(function() {window.location.reload();}, 1000);

							} else {

								alert(response.msg);
							}

						}
					});
				return false;
			});
		});
	})(jQuery);

</script>
<?php et_get_mobile_footer('mobile'); ?>
