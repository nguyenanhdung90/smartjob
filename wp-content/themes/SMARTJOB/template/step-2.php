<?php
global $steps, $term_of_use;
?>
<div class="step" id='step_auth'>
	<div class="toggle-title f-left-all">
		<div class="icon-border"><?php echo array_shift($steps) ?></div>
		<span class="icon" data-icon="2"></span>
		<span><?php _e('Login or create an account', ET_DOMAIN );?></span>
	</div>
	<div class="toggle-content login clearfix" style="display: none">
		<div class="tab-title f-left-all clearfix">
			<div class="bg-tab active"><?php _e('Register', ET_DOMAIN );?></div>
			<div class="bg-tab"><span><?php _e('Already have an account?', ET_DOMAIN );?></span> <?php _e('Login', ET_DOMAIN );?></div>
		</div>
		<div class="tab-content">
			<div class="form current">
				<form id="register" novalidate="novalidate" autocomplete="on">
					<div class="form-item">
						<div class="label">
							<label for="reg_email">
								<h6 class="font-quicksand"><?php _e('USER NAME', ET_DOMAIN );?></h6>
								<?php _e('Please enter your username', ET_DOMAIN );?>
							</label>
						</div>
						<div>
							<input class="bg-default-input is_user_name" tabindex="1" name="reg_user_name" id="reg_user_name" type="text"/>
						</div>
					</div>
					<div class="form-item">
						<div class="label">
							<label for="reg_email">
								<h6 class="font-quicksand"><?php _e('EMAIL ADDRESS', ET_DOMAIN );?></h6>
								<?php _e('Please enter your email address', ET_DOMAIN );?>
							</label>
						</div>
						<div>
							<input class="bg-default-input is_email" tabindex="1" name="reg_email" id="reg_email" type="email"/>
						</div>
					</div>

					<?php do_action('je_after_register_form'); ?>

					<div class="form-item">
						<div class="label">
							<label for="reg_pass">
								<h6 class="font-quicksand"><?php _e('PASSWORD', ET_DOMAIN );?></h6>
								<?php _e('Enter your password', ET_DOMAIN );?>
							</label>
						</div>
						<div>
							<input class="bg-default-input is_pass" tabindex="2" name="reg_pass" id="reg_pass" type="password" />
						</div>
					</div>
					<div class="form-item">
						<div class="label">
							<label for="reg_pass_again">
								<h6 class="font-quicksand repeat_pass "><?php _e('RETYPE YOUR PASSWORD', ET_DOMAIN );?></h6>
								<?php _e('Retype your password', ET_DOMAIN );?>
							</label>
						</div>
						<div>
							<input class="bg-default-input" tabindex="3" name="reg_pass_again" id="reg_pass_again" type="password" />
						</div>
					</div>

					<?php do_action('je_render_captcha_register_form'); ?>

					<!-- term and coditions !-->
					<?php if($term_of_use){ ?>
					<div class="form-item" id="term-of-use">
						<div class="label">&nbsp;</div>

					  	<div class="fld-wrap" id="">
							<input name="register_term" class="required not_empty" id="term_of" type="checkbox" />
							<label for="term_of"><?php printf(__("I agree with <a href='%s' target='_blank' > the Terms of use </a>", ET_DOMAIN), et_get_page_link('terms-of-use') ); ?> </label>
					  	</div>
					</div>
					<?php } ?>
					<!-- End term !-->

					<div class="form-item no-border-bottom clearfix">
						<div class="label">&nbsp;</div>
						<div class="btn-select">
							<button class="bg-btn-action border-radius" tabindex="4" type="submit" id="submit_register"><?php _e('CONTINUE', ET_DOMAIN );?></button>
						</div>
					</div>
				</form>
			</div>
			<div class="form">
				<form id="login" novalidate="novalidate" autocomplete="on">
					<div class="form-item">
						<div class="label">
							<h6 class="font-quicksand"><?php _e('USERNAME or EMAIL ADDRESS', ET_DOMAIN );?></h6>
							<?php _e('Please enter your username or email', ET_DOMAIN );?>
						</div>
						<div>
							<input class="bg-default-input is_email is_user_name" tabindex="1" name="log_email" id="log_email" type="text" />
						</div>
					</div>
					<div class="form-item">
						<div class="label">
							<h6 class="font-quicksand"><?php _e('PASSWORD', ET_DOMAIN );?></h6>
							<?php _e('Enter your password', ET_DOMAIN );?>
						</div>
						<div>
							<input class="bg-default-input is_pass" tabindex="2" name="log_pass" id="log_pass" type="password" />
						</div>
					</div>
					<div class="form-item no-border-bottom clearfix">
						<div class="label">&nbsp;</div>
						<div class="btn-select">
							<button class="bg-btn-action border-radius" tabindex="3" type="submit" id="submit_login"><?php _e('LOGIN', ET_DOMAIN );?></button>
						</div>
						<a href="#" class="forgot-pass-link"><?php _e('FORGOT PASSWORD', ET_DOMAIN)?></a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>