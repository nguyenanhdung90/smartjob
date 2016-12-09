<?php
	$mailt_opt			=	new ET_JobEngineMailTemplate ();

	$register_mail 	 	= 	$mailt_opt->get_register_mail ();
	$forgot_pass_mail   = 	$mailt_opt->get_forgot_pass_mail ();
	$reset_pass_mail	=	$mailt_opt->get_reset_pass_mail ();
	$apply_mail			=	$mailt_opt->get_apply_mail ();
	$remind_mail		=	$mailt_opt->get_remind_mail ();
	$approve_mail		=	$mailt_opt->get_approve_mail ();
	$reject_mail		=	$mailt_opt->get_reject_mail ();
	$archive_mail		=	$mailt_opt->get_archive_mail ();
	$cash_mail			=	$mailt_opt->get_cash_notification_mail ();
?>
<style type="text/css">
	.email-template .form {display: none;}
</style>
<div class="et-main-main clearfix inner-content" id="setting-mail-template"  <?php if ($sub_section != 'mail-template') echo 'style="display:none"' ?> >

	<div class="title font-quicksand mail-template-title" id="auth-mail-template-title">
		<?php _e("Authentication Mail Template",ET_DOMAIN);?>

	</div>
	<div class="desc" id="authentication-mail-template">
		<?php _e("Email templates for authentication process. You can use placeholders to include some specific content.",ET_DOMAIN);?> 
		<!-- <a class="find-out font-quicksand" href="#">
			Find out more <span class="icon" data-icon="i"></span>
		</a> -->
		<a class="icon btn-template-help" data-icon="?" href="#" title="<?php  _e("View more details",ET_DOMAIN) ?>"></a>
		<div class="cont-template-help">
			[user_login],[display_name],[user_email] : <?php _e("user's details you want to send mail", ET_DOMAIN) ?><br />
			[company],[dashboard] : <?php _e("company name, user dashboard url ", ET_DOMAIN) ?><br />
			[job_title], [job_link], [job_excerpt],[job_desc] : <?php _e("job infomation and detail", ET_DOMAIN) ?> <br />
			[activate_url] : <?php _e("activate link is require for user to renew their pass", ET_DOMAIN) ?> <br />
			[reason] : <?php _e(" reject job reason ", ET_DOMAIN) ?> <br />
			[seeker_note],[seeker_name], [seeker_mail] :<?php _e(" seeker infomation ", ET_DOMAIN) ?><br />
			[remind_note], [seeker_email] : <?php _e("reminder info ", ET_DOMAIN) ?><br />
			[cash_message] :<?php _e(" cash message when user pay success ", ET_DOMAIN) ?>
			[site_url],[blogname],[admin_email] :<?php _e(" site info, admin email", ET_DOMAIN) ?>
		</div>
		<div class="inner email-template" >
			<div class="item">
				<div class="payment">
					<?php _e("Company Register Mail Template",ET_DOMAIN);?>
				</div>
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $register_mail ,'register_mail' , je_editor_settings () ); ?>
								<!-- <textarea name="register_mail" id="register-mail" style="width:100%;"><?php echo $register_mail ?></textarea> -->
								<span class="icon" data-icon="3"></span>
							</div>
							<div class="mail-control-btn">
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>

					</div>
				</div>
			</div>

			<div class="item">
				<div class="payment">
					<?php _e("Forgot Password Mail Template",ET_DOMAIN);?>
				</div>
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $forgot_pass_mail ,'forgot_pass_mail' , je_editor_settings () ); ?>
								<!-- <textarea name="forgot_pass_mail" id="forgot-pass-mail" style="width:100%;"><?php echo $forgot_pass_mail ?></textarea> -->
								<span class="icon" data-icon="3"></span>
							</div>
							<div class="mail-control-btn">
								<div>(*)[activate_url] : activate url is require for user to renew their pass, you must have it in your mail </div>
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="item">
				<div class="payment">
					<?php _e("Reset Password Mail Template",ET_DOMAIN);?>
				</div>
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $reset_pass_mail ,'reset_pass_mail' , je_editor_settings () ); ?>
								<!-- <textarea name="reset_pass_mail" id="reset-pass-mail" style="width:100%;"><?php echo $reset_pass_mail ?></textarea> -->
								<span class="icon" data-icon="3"></span>
							</div>
							<div class="mail-control-btn">
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

	<div class="title font-quicksand mail-template-title" id="job-mail-template-title">
		<?php _e("Job-related Email Templates",ET_DOMAIN);?>
	</div>
	<div class="desc" id="job-mail-template">
		<?php _e("Email templates used for job-related event. You can use placeholders to include some specific content.",ET_DOMAIN);?>
		<!-- <a class="find-out font-quicksand" href="#">
			Find out more <span class="icon" data-icon="i"></span>
		</a> -->
		<?php
		$auto_email = et_get_auto_emails();
		?>
		<div class="inner email-template" >
			<div class="item">
				<div class="payment">
					<?php _e("Sent to employers when a candidate apply for their jobs.",ET_DOMAIN);?>
					<div class="button-enable font-quicksand enable-email">
						<a href="#" rel="mail_apply" title="Disable" class="deactive <?php echo $auto_email['apply'] == 0 ? 'selected' : ''?>">
							<span><?php _e('Disable', ET_DOMAIN) ?></span>
						</a>
						<a href="#" rel="mail_apply" title="Enable" class="active <?php echo $auto_email['apply'] == 1 ? 'selected' : ''?>">
							<span><?php _e('Enable', ET_DOMAIN) ?></span>
						</a>
					</div>
				</div>
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $apply_mail ,'apply_mail' , je_editor_settings () ); ?>
								<!-- <textarea name="apply_mail" id="apply-mail" style="width:100%;"><?php echo $apply_mail ?></textarea> -->
								<span class="icon" data-icon="3"></span>
							</div>
							<div class="mail-control-btn">
								<div>(*)[seeker_note] : <?php _e("job seeker note send to company when they submit an application", ET_DOMAIN) ?></div>
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="item">
				<div class="payment">
					<?php _e("Sent to job seekers when they want to save a job for viewing later.",ET_DOMAIN);?>
					<div class="button-enable font-quicksand enable-email">
						<a href="#" rel="mail_remind" title="Disable" class="deactive <?php echo $auto_email['remind'] == 0 ? 'selected' : ''?>">
							<span>Disable</span>
						</a>
						<a href="#" rel="mail_remind" title="Enable" class="active <?php echo $auto_email['remind'] == 1 ? 'selected' : ''?>">
							<span>Enable</span>
						</a>
					</div>
				</div>
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $remind_mail ,'remind_mail' , je_editor_settings () ); ?>
								<!-- <textarea name="remind_mail" id="remind-mail" style="width:100%;"><?php echo $remind_mail ?></textarea> -->
								<span class="icon" data-icon="3"></span>
							</div>
							<div class="mail-control-btn">
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="item">
				<div class="payment">
					<?php _e("Sent to employers to notify that one of their posted jobs has been published.",ET_DOMAIN);?>
					<div class="button-enable font-quicksand enable-email">
						<a href="#" rel="mail_approve" title="Disable" class="deactive <?php echo $auto_email['approve'] == 0 ? 'selected' : ''?>">
							<span>Disable</span>
						</a>
						<a href="#" rel="mail_approve" title="Enable" class="active <?php echo $auto_email['approve'] == 1 ? 'selected' : ''?>">
							<span>Enable</span>
						</a>
					</div>
				</div>
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $approve_mail ,'approve_mail' , je_editor_settings () ); ?>
								<!-- <textarea name="approve_mail" id="approve-mail" style="width:100%;"><?php echo $approve_mail ?></textarea> -->
								<span class="icon" data-icon="3"></span>
							</div>
							<div class="mail-control-btn">
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="item">
				<div class="payment">
					<?php _e("Sent to employers to notify that one of their jobs has been archived due to expiration or manual administrative action.",ET_DOMAIN);?>
					<div class="button-enable font-quicksand enable-email">
						<a href="#" rel="mail_archive" title="Disable" class="deactive <?php echo $auto_email['archive'] == 0 ? 'selected' : ''?>">
							<span>Disable</span>
						</a>
						<a href="#" rel="mail_archive" title="Enable" class="active <?php echo $auto_email['archive'] == 1 ? 'selected' : ''?>">
							<span>Enable</span>
						</a>
					</div>
				</div>
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $archive_mail ,'archive_mail' , je_editor_settings () ); ?>
								<!-- <textarea name="archive_mail" id="archive-mail" style="width:100%;"><?php echo $archive_mail ?></textarea> -->
								<span class="icon" data-icon="3"></span>
							</div>
							<div class="mail-control-btn">
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="item">
				<div class="payment">
					<?php _e("Sent to employers to notify that one of their posted jobs has been rejected.",ET_DOMAIN);?>
					<div class="button-enable font-quicksand enable-email">
						<a href="#" rel="mail_reject" title="Disable" class="deactive <?php echo $auto_email['reject'] == 0 ? 'selected' : ''?>">
							<span>Disable</span>
						</a>
						<a href="#" rel="mail_reject" title="Enable" class="active <?php echo $auto_email['reject'] == 1 ? 'selected' : ''?>">
							<span>Enable</span>
						</a>
					</div>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">

								<?php wp_editor( $reject_mail ,'reject_mail' , je_editor_settings () ); ?>
								<!-- <textarea name="reject_mail" id="reject-mail" style="width:100%;"><?php echo $reject_mail ?></textarea> -->
								<span class="icon" data-icon="3"></span>
							</div>
							<div class="mail-control-btn">
								<div>(*)[reason] : <?php _e("reason when you reject a job ", ET_DOMAIN) ?></div>
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="item">
				<div class="payment">
					<?php _e("Sent to employers when they post a job and choose to pay by cash.",ET_DOMAIN);?>
					<div class="button-enable font-quicksand enable-email">
						<a href="#" rel="mail_cash_notice" title="Disable" class="deactive <?php echo $auto_email['cash_notice'] == 0 ? 'selected' : ''?>">
							<span>Disable</span>
						</a>
						<a href="#" rel="mail_cash_notice" title="Enable" class="active <?php echo $auto_email['cash_notice'] == 1 ? 'selected' : ''?>">
							<span>Enable</span>
						</a>
					</div>
				</div>
				<div class="form payment-setting">
					<div class="form-item">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $cash_mail ,'cash_notification_mail' , je_editor_settings () ); ?>
								<!-- <textarea name="cash_notification_mail" id="cash_notification_mail" style="width:100%;"><?php echo $cash_mail ?></textarea> -->
								<span class="icon" data-icon="3"></span>
							</div>
							<div class="mail-control-btn">
								<div>(*)[reason] : <?php _e("reason when you reject a job ", ET_DOMAIN) ?></div>
								<a href="#" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							</div>
						</div>
					</div>
				</div>    						
			</div>		

		</div>
	</div>
	
</div>

