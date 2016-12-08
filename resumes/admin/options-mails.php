<div class="et-main-main" id="setting-mails" style="display: none">
	<div class="title font-quicksand"><?php _e('Mail template', ET_DOMAIN) ?></div>
	<div class="desc">
		<?php _e('Email templates for authentication process. You can use placeholders to include some specific content.',ET_DOMAIN); ?> 
		<a class="icon btn-template-help" data-icon="?" href="#" title="View more details"></a>
		<div class="mail-control-btn">
			<div>
				(*)[user_login],[display_name],[user_email] : <?php _e("user's details you want to send mail", ET_DOMAIN); ?>
				(*)[resume_title]: <?php _e('The resume  name', ET_DOMAIN) ?> <br/>
				(*)[seeker_name]: <?php _e("Job seeker's name", ET_DOMAIN) ?> <br/>
				(*)[seeker_mail]: <?php _e("Job seeker's email", ET_DOMAIN) ?> <br/>
				(*)[admin_email]: <?php _e("Admin email", ET_DOMAIN) ?> <br/>
				(*)[profile_link]]: <?php _e("Profile link of seeker", ET_DOMAIN) ?> <br/>
				(*)[seeker_note]: <?php _e("Resume seeker's decscription", ET_DOMAIN) ?> <br/>
				(*)[resume_link]: <?php _e("Resume seeker's profile address", ET_DOMAIN) ?> <br/>
				(*)[job_link]: <?php _e("The address of the job that job seeker applied for", ET_DOMAIN) ?> <br/>
				(*)[blogname]: <?php _e("Your page name", ET_DOMAIN) ?>
			</div>
			
		</div>

		<?php
			$template = JE_Resumes_Mailing::get_instance();
		?>
		<div class="inner email-template">

			<div class="item">
				<div class="payment">
					<?php _e("Jobseeker registration confirmation",ET_DOMAIN);?>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item clearfix">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $template->get_template('register') ,'register' , je_editor_settings () ); ?>
								<!-- <textarea name="register" id="register" style="width:100%;"><?php echo $template->get_template('register'); ?></textarea> -->
							</div>
							<a href="#" class="trigger-editor" ><?php  _e('Toggle Editor',ET_DOMAIN) ?></a> | 
							<a href="#" data="register" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>

			<div class="item">
				<div class="payment">
					<?php _e("Job application confirmation",ET_DOMAIN);?>					
					
					<div class="button-enable font-quicksand">
						<a href="#" data="et_send_mail_apply" title="Resume Status" class="toggle-button deactive <?php if ($options['et_send_mail_apply'] == 0) echo 'selected' ?>">
							<span><?php _e('Disable', ET_DOMAIN) ?></span>
						</a>
						<a href="#" data="et_send_mail_apply" title="Resume Status" class="toggle-button active <?php if ($options['et_send_mail_apply'] == 1) echo 'selected' ?>">
							<span><?php _e('Enable', ET_DOMAIN) ?></span>
						</a>
					</div>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item clearfix">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $template->get_template('apply') ,'apply' , je_editor_settings () ); ?>
								<!-- <textarea name="apply" id="apply" style="width:100%;"><?php echo $template->get_template('apply'); ?></textarea> -->
							</div>
							<a href="#" class="trigger-editor" ><?php  _e('Toggle Editor',ET_DOMAIN) ?></a> | 
							<a href="#" data="apply" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
							
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="item">
				<div class="payment">
					<?php _e("Resume approved",ET_DOMAIN);?>
					<div class="button-enable font-quicksand">
						<a href="#" data="et_send_mail_approve" title="Resume Status" class="toggle-button deactive <?php if ($options['et_send_mail_approve'] == 0) echo 'selected' ?>">
							<span><?php _e('Disable', ET_DOMAIN) ?></span>
						</a>
						<a href="#" data="et_send_mail_approve" title="Resume Status" class="toggle-button active <?php if ($options['et_send_mail_approve'] == 1) echo 'selected' ?>">
							<span><?php _e('Enable', ET_DOMAIN) ?></span>
						</a>
					</div>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item clearfix">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $template->get_template('approve') ,'approve' , je_editor_settings () ); ?>
								<!-- <textarea name="approve" id="mail_approve" style="width:100%; height:auto; overflow:hidden;"><?php echo $template->get_template('approve'); ?></textarea> -->
							</div>
							<a href="#" class="trigger-editor" ><?php  _e('Toggle Editor',ET_DOMAIN) ?></a> | 
							<a href="#" data="approve" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
													
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>

			<div class="item">
				<div class="payment">
					<?php _e("Resume rejected",ET_DOMAIN);?>
					<div class="button-enable font-quicksand">
						<a href="#" data="et_send_mail_reject" title="Resume Status" class="toggle-button deactive <?php if ($options['et_send_mail_reject'] == 0) echo 'selected' ?>">
							<span><?php _e('Disable', ET_DOMAIN) ?></span>
						</a>
						<a href="#" data="et_send_mail_reject" title="Resume Status" class="toggle-button active <?php if ($options['et_send_mail_reject'] == 1) echo 'selected' ?>">
							<span><?php _e('Enable', ET_DOMAIN) ?></span>
						</a>
					</div>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item clearfix">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $template->get_template('reject') ,'reject' , je_editor_settings () ); ?>
								<!-- <textarea name="reject" id="email_reject_resume" style="width:100%;"><?php echo $template->get_template('reject'); ?></textarea> -->
							</div>
							<a href="#" class="trigger-editor" ><?php  _e('Toggle Editor',ET_DOMAIN) ?></a> | 
							<a href="#" data="reject" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
														
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>

			<div class="item">
				<div class="payment">
					<?php _e("Contact jobseeker",ET_DOMAIN);?>
					<div class="button-enable font-quicksand">
						<a href="#" data="et_send_mail_contact" title="Resume Status" class="toggle-button deactive <?php if ($options['et_send_mail_contact'] == 0) echo 'selected' ?>">
							<span><?php _e('Disable', ET_DOMAIN) ?></span>
						</a>
						<a href="#" data="et_send_mail_contact" title="Resume Status" class="toggle-button active <?php if ($options['et_send_mail_contact'] == 1) echo 'selected' ?>">
							<span><?php _e('Enable', ET_DOMAIN) ?></span>
						</a>
					</div>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item clearfix">
						<div class="mail-template">
							<div class="mail-input">
								<?php wp_editor( $template->get_template('contact') ,'contact' , je_editor_settings () ); ?>
								<!-- <textarea name="contact" id="email_contact_jobseeker" style="width:100%;"><?php echo $template->get_template('contact'); ?></textarea> -->
							</div>
							<div>
								(*)[contact_name]: <?php _e("Sender's name", ET_DOMAIN) ?> </br>
								(*)[contact_email]: <?php _e("Sender's email", ET_DOMAIN) ?>
							</div>
							<a href="#" class="trigger-editor" ><?php  _e('Toggle Editor',ET_DOMAIN) ?></a> | 
							<a href="#" data="reject" class="reset-default" ><?php _e('Reset to default', ET_DOMAIN)?></a>
														
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		
		</div>

				

	</div>
</div>