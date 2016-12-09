<div class="modal-job modal-login" id="modal_login">
		<div class="edit-job-inner">
		<div class="title font-quicksand">
			<span class="login active" rel="login"><?php _e('LOGIN',ET_DOMAIN);?> </span> 
			<span class="register unactive" rel="modal_register"><?php _e('REGISTER',ET_DOMAIN);?> </span>
		</div>
		<form class="modal-form" id="login" novalidate="novalidate" autocomplete="on">
			<div class="content">

				<div class="form-item">
				  <div class="label">
					<h6><?php _e('Username or email address', ET_DOMAIN);?></h6>
				  </div>
				  <div class="fld-wrap" id="fld_login_email">
					<input name="log_email" class="bg-default-input is_email is_user_name not_empty" id="log_email" type="text" />
					<?php do_action('je_linkedin_button') ?>
				  </div>
				</div>
				<div class="form-item">
				  <div class="label">
					<h6><?php _e('Password',ET_DOMAIN);?></h6>
				  </div>
				  <div class="fld-wrap" id="fld_login_password">
					<input name="log_pass" class="bg-default-input is_pass not_empty" id="log_pass" type="password" />
				  </div>
				</div>

			</div>
			<div class="footer font-quicksand">
				<div class="button">
					<input type="submit" class="bg-btn-action border-radius" value="<?php _e('LOGIN',ET_DOMAIN);?>" id="submit_login">
					<span class="arr">&raquo;</span>
				</div>
				<a href="#" class="forgot-pass-link"><?php _e('FORGOT PASSWORD', ET_DOMAIN)?></a>
			</div>

			<!-- Social login !-->
			<?php

			$job_option	=	new ET_JobOptions();
			if($job_option->get_fb_login() || $job_option->get_tw_login()){ ?>

			<div class="social-login font-quicksand">
				<div class ="label-social-login font-quicksand"><?php _e('SIGN IN WITH',ET_DOMAIN);?></div>
				<div class="right-social right">

					<ul>
						<?php
						if($job_option->get_tw_login()){?>
	                    	<li><a class="twitter-auth-btn btn-social btn-twitter" id="tw_auth_btn" href="<?php echo home_url('?action=twitterauth');?>"> TWiter</a></li>
	                    <?php } ?>

	                    <?php if($job_option->get_fb_login()){?>
	                    <li><a class="facebook-auth-btn btn-social btn-fb"  id="facebook_auth_btn" href="#">FB</a></li>
	                    <?php } ?>

	            	</ul>
	            </div>
			</div>
			<?php
			}
			?>
			<!-- End Social login !-->
		</form>

		<form class="modal-form" id="modal_register" >
			<div class="content">

				<div class="form-item">
				  	<div class="label">
						<h6><?php _e('Username', ET_DOMAIN);?></h6>
				  	</div>
				 	<div class="fld-wrap" id="">
						<input name="user_name" class="bg-default-input is_user_name not_empty required" id="register_user_name" type="text" />

				  	</div>
				</div>
				<div class="form-item">
				  	<div class="label">
						<h6><?php _e('Email', ET_DOMAIN);?></h6>
				  	</div>
				 	<div class="fld-wrap" id="">
						<input name="user_email" class="bg-default-input is_email is_user_name not_empty required" id="register_email" type="text" />

				  	</div>
				</div>
				<?php if( function_exists('et_is_resume_menu') && !is_page_template( 'page-post-a-job.php' ) ) { ?>
				<div class="form-item">
				  	<div class="label">
						<h6><?php _e('I want to register as a', ET_DOMAIN);?></h6>
				  	</div>
				 	<div class="fld-wrap" id="">
						<input checked="true" name="role" class="bg-default-input required" id="role-company" type="radio" value="company" />
						<label for="role-company"><?php _e("Company", ET_DOMAIN); ?> </label>
						<input name="role" class="bg-default-input required" id="role-seeker" type="radio" value="jobseeker" />
						<label for="role-seeker"><?php _e("Jobseeker", ET_DOMAIN); ?> </label>
				  	</div>
				</div>

				<?php do_action( 'after_user_register_form' ); ?>
				<?php }else { ?>
					<input type="hidden" name="role" class="bg-default-input required" id="role-company" value="company" />
				<?php } ?>

				<div class="form-item">
				  	<div class="label">
						<h6><?php _e('Password',ET_DOMAIN);?></h6>
				  	</div>
				  	<div class="fld-wrap" id="">
						<input name="user_pass" class="bg-default-input is_pass not_empty required" id="register_pass" type="password" />
				  	</div>
				</div>

				<div class="form-item">
				  	<div class="label">
						<h6><?php _e('Retype Password',ET_DOMAIN);?></h6>
				  	</div>
				  	<div class="fld-wrap" id="">
						<input name="re_register_pass" class="bg-default-input is_pass not_empty required" id="re_register_pass" type="password" />
				  	</div>
				</div>

				<?php do_action( 'je_end_modal_login' ); ?>

				<div class="form-item" id="term-of-use">
				  	<div class="fld-wrap" id="">
						<input name="register_term" class="bg-default-input is_pass not_empty" id="term_of" type="checkbox" />
						<label for="term_of"><?php printf(__("I agree with <a href='%s' target='_blank' > the Terms of use </a>", ET_DOMAIN), et_get_page_link('terms-of-use') ); ?> </label>
				  	</div>
				</div>

			</div>
			<div class="footer font-quicksand">
				<div class="button">
					<input type="submit" class="bg-btn-action border-radius" value="<?php _e('REGISTER',ET_DOMAIN);?>" id="submit_modal_register">
					<span class="arr">&raquo;</span>
				</div>
			</div>
		</form>
	</div>
	<div class="modal-close"></div>
</div>