<?php
	global $job, $current_user, $user_ID;
?>

<div class="bg-job-frame job-apply clearfix margin-top25" id="apply_form" style="display:none;float:none">
		<div class="auth-apply">
			<h3 class="title font-quicksand"><?php _e("Use your professional profile to apply", ET_DOMAIN); ?> </h3>
			<!-- user logged in -->
			<div class="form-login <?php if (is_user_logged_in()) echo 'hide' ?>">
				<form class="form" id="jobseeker_auth">
					<div class="form-item no-padding">
						<div class="width50 f-left">
							<div class="label">
								<h6><?php _e("Username or email address",ET_DOMAIN);?></h6>
							</div>
							<div class="">
								<input name="user_email" type="text" class="bg-default-input" value=""/>
							</div>
						</div>
						<div class="width50 f-right">
							<div class="label">
								<h6><?php _e("Password",ET_DOMAIN);?></h6>
							</div>
							<div class="">
								<input name="user_pass" type="password" class="bg-default-input" value=""/>
							</div>
						</div>
					</div>

					<div class="form-item padding-top30">
						<button type="submit" id="apply" class="jse-btn-login bg-btn-action border-radius btn-default action">
							<?php _e("Login",ET_DOMAIN);?>
							<span>»</span>
						</button>
						<span class="register"><?php _e("Not having an account? ", ET_DOMAIN); ?><a href="<?php echo et_get_page_link ('jobseeker-signup') ?>"><?php _e("Register here!", ET_DOMAIN); ?></a></span>

					</div>
				</form>
			</div>
			<!-- end of user logged in -->

			<!-- user have not logged in -->
			<?php if (is_user_logged_in()) { ?>
			<div class="form-apply ">

				<form action="" id="auth_application_form">
					<div class="logged-name">
						<?php printf(__('Logged in as <span>%s</span>.', ET_DOMAIN ) , $current_user->ID ? $current_user->display_name : '' ) ?>
						<a href="<?php echo wp_logout_url(get_permalink( $job->ID )) ?>" title="<?php _e("Log out?", ET_DOMAIN); ?>" ><?php  _e("Log out?", ET_DOMAIN); ?> </a>
					</div>

					<div class="form-item">
						<div class="label">
							<?php _e("Notes",ET_DOMAIN);?>
						</div>
						<div class="">
							<input type="hidden" name="jobseeker_id" value="<?php echo $current_user->ID ?>">
							<input type="hidden" name="emp_name" value="<?php echo empty($current_user->display_name) ? '' : $current_user->display_name ?>">
							<input type="hidden" name="emp_email" value="<?php echo empty($current_user->user_email) ? '' : $current_user->user_email ?>">
							<input type="hidden" name="_ajax_nonce" value="<?php wp_create_nonce( 'apply_job' ) ?>">
							<input type="hidden" name="job_id" value="<?php echo $job->ID ?>">
							<textarea name="apply_note" class="bg-default-input mini required"></textarea>
						</div>
					</div>
					<div id="resume_upload_container" class="form-item" style="position:relative;">
						<div class="input-file clearfix et_uploader">
							<p class="clearfix">
								<span class="" style="float:left;">
								<?php _e('Upload resume', ET_DOMAIN);?>&nbsp; &nbsp; &nbsp;
								</span>
								<span tabindex="8" id="resume_upload_browse_button" class="border-radius button-upload thumb btn-background"> &nbsp;
										<?php _e('Browse',ET_DOMAIN);?> &nbsp; <span data-icon="o" class="icon"></span>
								</span>
							</p>
							<p class="clearfix resume-file"><span class="resume-show"></span></p>
							<p class="clearfix">
							<?php _e('Up to 3MB for file types .pdf .doc .docx .rtf .txt',ET_DOMAIN);?>
							</p>
						   	<input type="hide" name="job_id" value ="<?php echo $job->ID ?>" />
						</div>

					</div>

					<?php do_action ('je_apply_job_form') ?>

					<div class="form-item">
						<button type="submit" class="bg-btn-action btn-apply btn-background border-radius btn-default action">
							<?php _e("APPLY FOR THIS JOB",ET_DOMAIN);?>
							<span class="icon" data-icon="p"></span>
						</button>
						<button type="button" class="btn-background border-radius btn-default cancel">
							<?php _e('Cancel',ET_DOMAIN);?>
							<span class="icon" data-icon="D"></span>
						</button>
					</div>
				</form>

			</div>
			<?php } ?>
			<!-- user have not logged in -->

		</div>
		<!-- end of auth apply  -->

		<div class="unauth-apply <?php if (is_user_logged_in()) echo 'hide' ?>">
			<div class="line-padding"></div>
			<h3 class="title font-quicksand"><?php _e("Or send your application quickly without registration", ET_DOMAIN); ?></h3>
			<form class="form" id="applicationForm" >
				<div class="form-item no-padding">
					<div class="width50 f-left">
						<div class="label">
							<h6><?php _e("Full name",ET_DOMAIN);?></h6>
						</div>
						<div class="">
							<input name="apply_name" id="apply_name" type="text" class="bg-default-input required" value="<?php if(isset($_COOKIE['seeker_name'])) echo $_COOKIE['seeker_name'] ; ?>"/>
						</div>
					</div>
					<div class="width50 f-right">
						<div class="label">
							<h6><?php _e("Email Address",ET_DOMAIN);?></h6>
						</div>
						<div class="">
							<input name="apply_email" id="apply_email" type="email" class="bg-default-input required email" value="<?php if(isset($_COOKIE['seeker_email'])) echo $_COOKIE['seeker_email'] ; ?>"/>
						</div>
					</div>
				</div>

				<?php $uploaderID = 'apply_docs';?>
				<div class="form-item" id="<?php echo $uploaderID;?>_container">
					<div class="label">
						<h6><?php _e("Attachments",ET_DOMAIN);?></h6>
						<ul class="list-file" id="<?php echo $uploaderID;?>_file_list">
						</ul>
					</div>
					<div class="input-file">
						<span class="btn-background border-radius button" id="<?php echo $uploaderID;?>_browse_button">
							<?php _e("Browse...",ET_DOMAIN);?>
							<span class="icon" data-icon="o"></span>
						</span>
						<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( $uploaderID . '_et_uploader' ); ?>"></span>
					</div>
				</div>

				<div class="form-item">
					<div class="label">
						<h6><?php _e("Notes",ET_DOMAIN);?></h6>
					</div>
					<div class="">
						<textarea name="apply_note" id="apply_note" class="bg-default-input mini"></textarea>
					</div>
				</div>
				<?php do_action ('je_apply_job_form') ?>
				<div class="form-item">
					<button type="submit" id="apply" class="btn-backgrounds bg-btn-action btn-any-apply border-radius btn-default action">
						<?php _e("APPLY FOR THIS JOB",ET_DOMAIN);?>
						<span class="icon" data-icon="p"></span>
					</button>
					<button type="button" class="btn-background border-radius btn-default cancel">
						<?php _e('Cancel',ET_DOMAIN);?>
						<span class="icon" data-icon="D"></span>
					</button>
				</div>
				<input type="hidden" name="action" value="et_apply_job" />
				<input type="hidden" name="job_id" value="<?php echo $job->ID ?>"/>
			</form>
		</div>

	</div>