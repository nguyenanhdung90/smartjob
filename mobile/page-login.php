<?php
/**
 * Template Name: Mobile Login
 */
global $current_user , $user_ID;
if($user_ID) {
	$roles	=	$current_user->roles;
	$role	=	array_pop($roles);
	$user	=	($role == 'jobseeker') ? et_create_user_response($current_user) : et_create_companies_response($current_user);
	wp_redirect( $user['profile_url'] );
	exit;
}

et_get_mobile_header('mobile'); ?>
<div data-role="content" class="post-content">
	<h1 class="post-title job-title title-resume">
		<?php
			$general_opt=	new ET_GeneralOptions();
			printf(__('Login to %s',ET_DOMAIN) , $general_opt->get_site_title () );

		?>
	</h1>
	<form action="" method="post">
		<div class="content-field inset-shadow">
			<h3><?php _e('Username',ET_DOMAIN); ?></h3>
			<div class="input-text">
				<input type="text" name="username" autocomplete="off" id="login_username">
			</div>
			<h3><?php _e('Password',ET_DOMAIN); ?></h3>
			<div class="input-text">
				<input type="password" name="Password" autocomplete="off" id="login_pass">
			</div>

			<?php if(isset($_REQUEST['redirect_url'])) { ?>
				<input type="hidden" name="redirect_url" autocomplete="off" id="redirect_url" value="<?php echo $_REQUEST['redirect_url'] ?>">
			<?php } ?>

		</div>
		<div class="content-field f-padding">
			<div class="input-button">
				<input type="button" class="et_login" value="<?php _e('Continue',ET_DOMAIN); ?>">
			</div>
			<div class="clearfix"></div>
		</div>
	</form>
	<?php if(function_exists('et_is_resume_menu')) { ?>
		<h1 class="post-title job-title title-resume">
			<?php _e("Need to have an online resume?", ET_DOMAIN); ?> <br/>
			<a style="text-decoration:none;" class="btn-custom-submit" href="<?php echo et_get_page_link('jobseeker-signup'); ?>" ><?php _e("SIGN UP AS A CREATIVE", ET_DOMAIN); ?> </a>
		</h1>
	<?php } ?>
</div><!-- /content -->

<?php et_get_mobile_footer('mobile'); ?>