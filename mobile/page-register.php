<?php et_get_mobile_header('mobile'); ?>
<div data-role="content" class="post-content">
	<h1 class="post-title job-title">
		<?php
			$general_opt=	new ET_GeneralOptions();
			printf ( __('Register to %s',ET_DOMAIN) ,$general_opt->get_site_title() ) ;
		?>
		<span class="post-title-right"><a href="<?php echo et_get_page_link('login'); ?>"><?php _e('Login',ET_DOMAIN); ?></a></span>
	</h1>
	<form action="" method="post">
		<div class="content-field inset-shadow">
			<h3><?php _e('Username',ET_DOMAIN); ?></h3>
			<div class="input-text">
				<input type="text" name="username" id="reg_username">
			</div>

			<h3><?php _e('Email Address',ET_DOMAIN); ?></h3>
			<div class="input-text">
				<input type="text" name="email" id="reg_email">
			</div>

			<h3><?php _e('Password',ET_DOMAIN); ?></h3>
			<div class="input-text">
				<input type="password" name="Password" id="reg_pass">
			</div>

			<h3><?php _e('Retype Password',ET_DOMAIN); ?></h3>
			<div class="input-text">
				<input type="password" name="retype_pass" id="reg_retype_pass">
			</div>
		</div>
		<div class="content-field f-padding">
			<div class="input-button">
				<input type="button" class="et_register" value="<?php _e('Continue',ET_DOMAIN); ?>">
			</div>
		</div>
	</form>
</div><!-- /content -->

<?php et_get_mobile_footer('mobile'); ?>