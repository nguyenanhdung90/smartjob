<?php

if(class_exists("ET_JobEngineMailTemplate")):

	class JE_Mailing extends ET_JobEngineMailTemplate {
		static $instance = null;
		static public function get_instance(){
			if ( self::$instance == null){
				self::$instance = new JE_Mailing();
			}
			return self::$instance;
		}

		function filter_job_placeholder ( $content, $job ) {

			$job_id		=	$job->ID;

			$content 	=	str_ireplace('[job_title]', $job->post_title, $content);
			$content 	=	str_ireplace('[job_desc]', $job->post_content, $content);
			$content 	=	str_ireplace('[job_excerpt]', $job->post_excerpt, $content);
			$content 	=	str_ireplace('[job_link]', get_permalink($job_id), $content);
			$content 	=	str_ireplace('[dashboard]', et_get_page_link('dashboard'), $content);
			$content 	= 	apply_filters('et_filter_job_email', $content, $job_id);

			return $content;
		}

		function filter_auth_placeholder ( $content, $user_id) {
			$user 		=	new WP_User ($user_id);

			$content 	=	str_ireplace('[user_login]', $user->user_login, $content);
			$content 	=	str_ireplace('[user_name]', $user->user_login, $content);
			$content 	=	str_ireplace('[user_nicename]',ucfirst( $user->user_nicename ), $content);
			$content 	=	str_ireplace('[user_email]', $user->user_email, $content);
			$content 	=	str_ireplace('[display_name]', ucfirst( $user->display_name ), $content);
			$content 	=	str_ireplace('[company]', ucfirst( $user->display_name ) , $content);
			$content 	=	str_ireplace('[dashboard]', et_get_page_link('dashboard'), $content);

			return $content;
		}

		/*
		 * send mail to employer when have a candidate
		*/
		function apply_job ($job, $application, $attachs, $apply_info	=	array()) {
			// verify if sending mail is allowed
			$apply_info	=	wp_parse_args( $apply_info , array('email' => '' , 'emp_name' => '', 'apply_note' => '') );
			if (!et_get_auto_email('apply')){
				$res = array(
					'success'	=> true,
					'msg'		=> __('<span><strong>Congratulations!</strong></span><br /><span class="msg">Your application has been sent. Good luck!</span>', ET_DOMAIN)
				);
			}

			// this array will hold the file paths of the attachments for wp_mail
			extract($apply_info);
			$attachments	= array();
			// make this application the post_parent of all attachments
			foreach($attachs as $att){
				$att	= et_update_post(array('ID' => $att, 'post_parent' => $application));
				if ($att) {
					$attachments[]	= get_attached_file($att);
				}
			}
			$job_id			=	$job->ID;
			// get commpany detail to send mail
			$company		=	et_create_companies_response($job->post_author);
			$company_email	=	et_get_post_field($job_id, 'apply_email');
			$company_email	=	($company_email != '')? $company_email : $company['apply_email'];
			$company_name	=	$company['display_name'];
			$blog_name		=	get_option('blogname');

			// application mail subject and content
			$subject 		=	sprintf(__("Application for %s you posted on %s",ET_DOMAIN),$job->post_title,$blog_name);

			/**
			 * get apply job mail template 
			*/
			$message 		=	$this->get_apply_mail();

			$message		=	str_ireplace('[seeker_note]', $apply_note, $message);
			$message 		=	str_ireplace('[seeker_name]', $emp_name, $message);
			$message 		=	str_ireplace('[seeker_mail]', $email, $message);

			$seeker			=	array( 'email' => $email,'name' => $emp_name, 'job' => $job_id);


			$message		=	$this->filter($message, $job );

			// filter mail content and title
			$subject 		=	apply_filters ('et_job_apply_email_title', $subject, $job_id);
			$message 		=	apply_filters ('et_job_apply_email_content',$message, $seeker );

			/**
			 * mail content send to employee, who apply job - confirm mail
			*/
			$employee_subject	=	sprintf(__("Application for %s you sent through %s",ET_DOMAIN),$job->post_title,$blog_name);

			$employee_message	=	sprintf(__("<p>Dear %s,</p> <p>You have sent your application successfully for this job: %s. Here is the email which was sent to the employer.</p>",ET_DOMAIN),ucfirst($emp_name),$job->post_title); 
			$employee_message	=  $employee_message.'<br/>'.$message;

			// $employee_message	=	et_get_mail_header().$employee_message.et_get_mail_footer();

			// mail header
		 	$reply_to_headers  = 'MIME-Version: 1.0' . "\r\n";
			$reply_to_headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
			$reply_to_headers .= "From: ".$blog_name." < ".get_option('admin_email') ."> \r\n";
            $reply_to_headers .= "Bcc: ".$blog_name."< hr@dcv.vn> \r\n";
			$reply_to_headers =	$reply_to_headers." Reply-To: " . $email . "\r\n";

			// send mail, if successful, response to user
			if( $this->mail($company_email, $subject , $message, $reply_to_headers, $attachments) &&
				$this->mail($email, $employee_subject , $employee_message, '', $attachments)
				
			){
				$res = array(
					'success'			=> true,
					'msg'				=> __('<span><strong>Congratulations!</strong></span><br /><span class="msg">Your application has been sent. Good luck!</span>', ET_DOMAIN),
					'mobile_msg'		=> __('Congratulations! Your application has been sent. Good luck!', ET_DOMAIN)
				); 
				return $res;
			} else {
				return false;
			}
		}
		/*
		 * send mail to employer when have a candidate  when this job of editor
		*/
		function apply_job_editor ($job, $application, $attachs, $apply_info	=	array(),$email_company_editor, $display_name_editor) {
			// verify if sending mail is allowed
			$apply_info	=	wp_parse_args( $apply_info , array('email' => '' , 'emp_name' => '', 'apply_note' => '') );
			if (!et_get_auto_email('apply')){
				$res = array(
					'success'	=> true,
					'msg'		=> __('<span><strong>Congratulations!</strong></span><br /><span class="msg">Your application has been sent. Good luck!</span>', ET_DOMAIN)
				);
			}

			// this array will hold the file paths of the attachments for wp_mail
			extract($apply_info);
			$attachments	= array();
			// make this application the post_parent of all attachments
			foreach($attachs as $att){
				$att	= et_update_post(array('ID' => $att, 'post_parent' => $application));
				if ($att) {
					$attachments[]	= get_attached_file($att);
				}
			}
			$job_id			=	$job->ID;
			// get commpany detail to send mail
			$company		=	et_create_companies_response($job->post_author);
			$company_email	=	et_get_post_field($job_id, 'apply_email');
			$company_email	=	($company_email != '')? $company_email : $company['apply_email'];
			$company_name	=	$company['display_name'];
			$blog_name		=	get_option('blogname');

			// application mail subject and content
			$subject 		=	sprintf(__("Application for %s you posted on %s",ET_DOMAIN),$job->post_title,$blog_name);

			/**
			 * get apply job mail template 
			*/
			$message 		=	$this->get_apply_mail_editor();

			$message		=	str_ireplace('[display_name_editor]', $display_name_editor, $message);
			$message		=	str_ireplace('[seeker_note]', $apply_note, $message);
			$message 		=	str_ireplace('[seeker_name]', $emp_name, $message);
			$message 		=	str_ireplace('[seeker_mail]', $email, $message);

			$seeker			=	array( 'email' => $email,'name' => $emp_name, 'job' => $job_id);


			$message		=	$this->filter($message, $job );

			// filter mail content and title
			$subject 		=	apply_filters ('et_job_apply_email_title', $subject, $job_id);
			$message 		=	apply_filters ('et_job_apply_email_content',$message, $seeker );

			/**
			 * mail content send to employee, who apply job - confirm mail
			*/
			$employee_subject	=	sprintf(__("Application for %s you sent through %s",ET_DOMAIN),$job->post_title,$blog_name);

			$employee_message	=	sprintf(__("<p>Dear %s,</p> <p>You have sent your application successfully for this job: %s. Here is the email which was sent to the employer.</p>",ET_DOMAIN),ucfirst($emp_name),$job->post_title); 
			$employee_message	=  $employee_message.'<br/>'.$message;

			// $employee_message	=	et_get_mail_header().$employee_message.et_get_mail_footer();

			// mail header
		 	$reply_to_headers  = 'MIME-Version: 1.0' . "\r\n";
			$reply_to_headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
			$reply_to_headers .= "From: ".$blog_name." < ".get_option('admin_email') ."> \r\n";
			$reply_to_headers .= "Bcc: ".$blog_name."< hr@dcv.vn> \r\n";
			$reply_to_headers =	$reply_to_headers." Reply-To: " . $email . "\r\n";

			// send mail, if successful, response to user
			if( $this->mail($email_company_editor, $subject , $message, $reply_to_headers, $attachments) &&
				$this->mail($email, $employee_subject , $employee_message, '', $attachments) 
				
			){
				$res = array(
					'success'			=> true,
					'msg'				=> __('<span><strong>Congratulations!</strong></span><br /><span class="msg">Your application has been sent. Good luck!</span>', ET_DOMAIN),
					'mobile_msg'		=> __('Congratulations! Your application has been sent. Good luck!', ET_DOMAIN)
				); 
				return $res;
			} else {
				return false;
			}
		}
		/**
		 *  send remind job mail
		*/
		function remind_job ( $email, $job, $remind_note ) {

			$message	=	$this->get_remind_mail();

			$subject	=	sprintf(__("You have saved this job for later review: %s", ET_DOMAIN),$job->post_title);

			$message	=	$this->filter( $message, $job );
			$message	=	str_ireplace('[remind_note]', $remind_note, $message);
			$message	=	str_ireplace('[seeker_email]', $email, $message);

			$message	=	apply_filters('et_share_job_message', $message, $job->ID ,$email);
			$subject	=	apply_filters('et_share_job_title', $subject, $job->ID ,$email);

			//$message	=	et_get_mail_header(). $message .et_get_mail_footer();
			//$message	.=	'<br/>'.__("You can view the job",ET_DOMAIN).' <a href="'.get_permalink($job_id).'">'.__("here",ET_DOMAIN).'</a>';
			return $this->mail( $email, $subject, $message );
		}

		/*
		 * send mail when job archived or approved
	 	*/
		function change_job_status ( $job_id, $status ) {

			$job_title	=	get_the_title( $job_id );
			$job 		=	get_post($job_id);
			// verify if sending mail is allowed
			if (!et_get_auto_email('approve') && $status == 'publish')
				return false;
			else if (!et_get_auto_email('archive') && $status == 'archive')
				return false;

			switch ($status) {
				case 'publish':
					$subject	=	apply_filters('et_publish_job_mail_title',
										sprintf(__('Your job " %s " posted in %s has been approved!', ET_DOMAIN),
										$job_title, get_option('blogname') )
									);
					$message	=	$this->get_approve_mail ();
					$message	=	apply_filters( 'et_approve_mail_message' , $this->filter( $message, $job ) );
					break;

				case 'archive' :
					$subject	=	apply_filters('et_archive_job_mail_title',
										sprintf(__('Your job %s posted in %s has been archived!', ET_DOMAIN),
										$job_title, get_option('blogname') )
									);
					$message	=	$this->get_archive_mail ();
					$message	=	apply_filters( 'et_archive_mail_message' , $this->filter( $message, $job ) );
					break;

				default:
					return false;
					break;
			}

			$company_email	=	get_the_author_meta('email', $job->post_author);
			$this->mail ( $company_email, $subject, $message , ''  );

		}

		/*
		 * send reject job mail
		*/
		function reject_job( $company , $job, $reason ) {
			if (!et_get_auto_email('reject'))
				return true;
			$reject_mail	=	 $this->get_reject_mail();
			$mail = array (
				'header' => '',
				'to' => $company->data->user_email, 
				'subject' => apply_filters( 'et_reject_mail_subject', __('Your job has been rejected', ET_DOMAIN) ),
				'message' => $this->filter( $reject_mail, $job )
			) ;

			extract($mail);
			$message	=	str_ireplace('[reason]', $reason, $message);
			$message	=	apply_filters( 'et_reject_mail_message', $message, $job );
			// $message	=	et_get_mail_header(). $message.et_get_mail_footer();
			return $this->mail( $to, $subject, $message );
		}

		/**
		 * send reset pass success mail
		*/
		function password_reset_mail ( $user, $new_pass ) {
			$new_pass_msg	=	$this->get_reset_pass_mail();
			$new_pass_msg	=	$this->filter_auth_placeholder( $new_pass_msg,  $user->ID ) ;
			$subject 		=	apply_filters('et_reset_pass_mail_subject',__('Password updated successfully!', ET_DOMAIN));
			return $this->mail($user->user_email, $subject , $new_pass_msg);
		}

		/**
		 * send mail to registered company
		*/
		public function company_register ( $user_id ) {
			$user			=   new WP_User($user_id);
			$user_email		=	$user->user_email;

			$register_mail	=	$this->get_register_mail();

			$register_mail	=	$this->filter_auth_placeholder ( $register_mail, $user_id );
			$subject		=	sprintf(__("Congratulations! You have successfully registered to %s.",ET_DOMAIN),get_option('blogname'));

			//$register_mail	=	et_get_mail_header().$register_mail.et_get_mail_footer();

			return $this->mail($user_email, $subject , $register_mail ) ;
		}

		/**
		 * filter mail content to replace placeholder
		*/
		function filter ( $message, $job ) {
			$message	=	$this->filter_job_placeholder( $message, $job ) ;
			$message	=	$this->filter_auth_placeholder( $message,  $job->post_author ) ;
			return $message;
		}

		/**
		 * append mail header, footer and send mail
		*/
		function mail ( $to, $subject, $message ,  $headers = '' , $attachments =''  ) {

			$message	=	et_get_mail_header().$message.et_get_mail_footer();
			return wp_mail( $to, $subject, $message, $headers, $attachments );

		}

	}
endif;

add_filter('wp_mail','et_filter_wp_mail') ;
function et_filter_wp_mail ( $compact ) {

	if(isset($_GET['action']) && $_GET['action'] == 'lostpassword')
		return $compact;


	if($compact['headers'] == '') {
		$compact['headers']  	= 'MIME-Version: 1.0' . "\r\n";
		$compact['headers'] 	.= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$compact['headers'] 	.= "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n";
	}



	$compact['message']		=	str_ireplace('[site_url]', get_bloginfo('url'), $compact['message']	);
	$compact['message']		=	str_ireplace('[blogname]', get_bloginfo('name'), $compact['message']);
	$compact['message']		=	str_ireplace('[admin_email]', get_option('admin_email'), $compact['message']);

	$compact['message']		=	html_entity_decode ($compact['message'] , ENT_QUOTES, 'UTF-8');
	$compact['subject']		=	html_entity_decode ($compact['subject'] , ENT_QUOTES, 'UTF-8' );

	//$compact['message']		= 	et_get_mail_header().$compact['message'].et_get_mail_footer();
	return $compact;
}

function et_get_mail_header () {
	$opt 		=	new ET_GeneralOptions ();
	$size		=	apply_filters( 'je_mail_logo_size', array (120, 50) );
	$logo_url	=	$opt->get_website_logo ($size);

	$customize	=	$opt->get_customization ();
	$customize	=	apply_filters('et_mail_header_customize' , $customize);

	$mail_header = '<html>
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
						<meta name="format-detection" content="telephone=no">
						<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
						<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE" />
						<style  type="text/css">
							@media only  screen and (max-width: 599px) {
						        td[class="logo"],
						        td.logo {
						            width: 100%;
						            height: auto !important;
						            display:table;
						            padding-bottom:0px !important;

						        }
							    td[class="blog-info"],
							    td.blog-info {
							        width: 100%;
							        clear:both;
							        display:table;
							    }
							    *[class="logo"] {float: none !important;width: 100% !important;}
						        *[align="center"] {float: none !important;width: 100% !important;}
							}
						</style>

					</head>
					<body style="font-family: Arial, sans-serif;font-size: 0.9em;margin: 0;	padding: 0;	color: #222222;">

						<table width="100%" cellspacing="0" cellpadding="0">
						<tr style="background: #2e4c6b; height: 63px; vertical-align: middle;">
							<td align="left" style="padding:10px 5px 10px 20px;width:20%;min-width:300px;display:inline-block;">                                              
								<div style="font-weight:bold;font-size:29px;height:35px;"><span style="color:white;">SMART</span><span style="color:#e63a35;">JOB</span></div>
								<div style="color:white;font-size:17px;">For the successful life</div>						
							</td>
							<td align="left" style="padding:10px 20px 10px 5px;min-width:300px;display:inline-block;">
								<span style="color:#b0b0b0;"></span>
							</td>
						</tr>						
						<tr>
							<td colspan="2" style="background: #ffffff; color: #222222; line-height: 18px; padding: 10px 20px;">';
	return apply_filters ('et_get_mail_header', $mail_header);
}

function et_get_mail_footer () {

	$info 	=	apply_filters ('et_mail_footer_contact_info' , get_option('blogname').' <br>
					'.get_option('admin_email').' <br>'
				);
	$opt 		=	new ET_GeneralOptions ();
	$customize	=	$opt->get_customization ();
	$customize	=	apply_filters('et_mail_header_customize' , $customize);

	$mail_footer =  '</td>
					</tr>
					<tr>
						<td colspan="2" style="background: '.$customize['background'].'; padding: 10px 20px; color: #666;">
							<table width="100%" cellspacing="0" cellpadding="0">
								<tr>
									<td style="vertical-align: top; text-align: left; width: 50%;">'.$opt->get_copyright ().'</td>
									<td style="text-align: right; width: 50%;">'.$info.'</td>
								</tr>
							</table>
						</td>
					</tr>
					</table>
				</body>
				</html>';
	return apply_filters ('et_get_mail_footer', $mail_footer);
}