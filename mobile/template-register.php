<?php
$term_of_use	= et_get_page_link('terms-of-use' , array () , false);
?>
<h1 class="page-title" >
    <?php _e("Post a Job", ET_DOMAIN); ?>
    <span class="step-number"><?php _e("Step Authentication", ET_DOMAIN); ?></span>
</h1>

<div class="authentication-form">

	<form class="post-job-form register-form register" data-ajax="false" method="post" >
		<div data-role="fieldcontain" class="post-new-job">
			<label for="username"><?php _e("COMPANY NAME", ET_DOMAIN); ?><span class="subtitle"><?php _e("Enter your company name", ET_DOMAIN); ?></span></label>
			<input type="text" name="display_name"  value="" placeholder="<?php _e("Company name", ET_DOMAIN); ?>" required />
		</div>

		<div data-role="fieldcontain" class="post-new-job">
			<label for="username"><?php _e("LOGIN ID", ET_DOMAIN); ?><span class="subtitle"><?php _e("Enter your username", ET_DOMAIN); ?></span></label>
			<input type="text" name="user_name"  value="" placeholder="<?php _e("Username", ET_DOMAIN); ?>" required />
		</div>
		<div data-role="fieldcontain" class="post-new-job">
			<label for="email"><?php _e("EMAIL ADDRESS", ET_DOMAIN); ?><span class="subtitle"><?php _e("Jobseeker can apply job via this", ET_DOMAIN); ?></span></label>
			<input type="email" name="user_email" value="" placeholder="<?php _e("Email address", ET_DOMAIN); ?>" required />
		</div>
		<?php do_action('je_after_register_form');?>
		<div data-role="fieldcontain" class="post-new-job">
			<label for="username"><?php _e("PASSWORD", ET_DOMAIN); ?><span class="subtitle"><?php _e("Enter words keep your account private", ET_DOMAIN) ?></span></label>
			<input type="password" name="user_pass"  value="" placeholder="<?php _e("Password", ET_DOMAIN); ?>" required /> <br/>
			<input type="password" name="password_again" value="" placeholder="<?php _e("Retype Password", ET_DOMAIN); ?>" required />
		</div>
		<?php do_action('je_render_captcha_register_form');?>
		<!-- term of user !-->
		<div data-role="fieldcontain" class="post-new-job">

		<?php if($term_of_use){ ?>
		<div class="form-item" id="term-of-use">
			<div class="label">&nbsp;</div>

		  	<div class="fld-wrap" id="">
				<input name="register_term" class="required not_empty" id="term_of" type="checkbox" required />
				<label for="term_of"><?php printf(__("I agree with <a href='%s' target='_blank' > the Terms of use </a>", ET_DOMAIN), et_get_page_link('terms-of-use') ); ?> </label>
		  	</div>
		</div>
		<?php } ?>

		</div>

		<!-- end term of !-->
		<div data-role="fieldcontain" class="post-new-job">
			<input type="submit" value="<?php _e('Submit',ET_DOMAIN);?>" data-icon="check" data-iconpos="right" data-inline="true">
			<span style="margin-top:10px; display:block;">
			<?php _e("Already have an account?", ET_DOMAIN); ?> <a href="#" class="open-login" ><?php _e("Login here", ET_DOMAIN); ?></a>
			</span>
		</div>

	</form>

	<form class="post-job-form register-form login" data-ajax="false" method="post" style="display:none;" >
		<div data-role="fieldcontain" class="post-new-job">
			<label for="username"><?php _e("LOGIN ID", ET_DOMAIN); ?><span class="subtitle"><?php _e("Enter your username or email", ET_DOMAIN); ?></span></label>
			<input type="text" name="user_name"  value="" placeholder="<?php _e("Username", ET_DOMAIN); ?>" required />
		</div>
		<div data-role="fieldcontain" class="post-new-job">
			<label for="username"><?php _e("PASSWORD", ET_DOMAIN); ?><span class="subtitle"><?php _e("Enter your password", ET_DOMAIN); ?></span></label>
			<input type="password" name="user_pass" value="" placeholder="<?php _e("Password", ET_DOMAIN); ?>" required /> <br/>
		</div>

		<div data-role="fieldcontain" class="post-new-job">
			<input type="submit" value="<?php _e('Submit',ET_DOMAIN);?>" data-icon="check" data-iconpos="right" data-inline="true">
			<span style="margin-top:10px; display:block;">
			<?php _e("Forgot your password?", ET_DOMAIN); ?> <a href="#" class="forgot-password" ><?php _e("Click here", ET_DOMAIN); ?></a>
			</span>
		</div>
	</form>

	<form class="forgot-password" data-ajax="false" method="post" style="display:none;" >
		<div data-role="fieldcontain" class="post-new-job">
			<label for="username"><span class="subtitle"><?php _e("Enter your email", ET_DOMAIN); ?></span></label> <br />

			<input type="email" name="user_login"  class="required email" placeholder="<?php _e("Your Email", ET_DOMAIN); ?>" required />
		</div>


		<div data-role="fieldcontain" class="post-new-job">
			<input type="submit" value="<?php _e('Reset Password',ET_DOMAIN);?>" data-icon="check" data-iconpos="right" data-inline="true">
		</div>
	</form>

</div>