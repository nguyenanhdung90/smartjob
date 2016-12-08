<?php  

/*
 * Login user
 */
add_action('wp_ajax_nopriv_et_mobile_login','et_ajax_mobile_login');
function et_ajax_mobile_login(){
	$response = array('success' => false, 'message' => __('Login failed', ET_DOMAIN) );
	try {
		$error = '';
		$user  = '';
		$creds = array();
		$creds['user_login'] = isset ($_POST['username']) ? $_POST['username']  : '';
		$creds['user_password'] = isset ($_POST['password']) ? $_POST['password']  : '';

		if(empty ($creds['user_password']))
			$error = __('Please type your password !',ET_DOMAIN);
		if(empty ($creds['user_login']))
			$error = __('Please type your username !',ET_DOMAIN);
		if(!empty($error)) {
			$response = array('success' => false, 'message' => $error );
		}else {
			$user = wp_signon($creds, false);
			if(is_wp_error($user)){
				$error = __('Invalid username or password!', ET_DOMAIN);
				$response = array('success' => false, 'message' => $error );
			} else	{
				$response['redirect_url'] = home_url();
				$response['success']      = true;
				$response['message']      = __('Login successfully',ET_DOMAIN);
			}
		}
	} catch (Exception $e) {
		$response = array('success' => false, 'message' => __('There is an error occurred', ET_DOMAIN) );
	}
	wp_send_json($response);
}

/*
 * Logout user
 */
add_action('wp_ajax_et_mobile_logout','et_ajax_mobile_logout');
function et_ajax_mobile_logout(){
	wp_logout();
	$response = array('success' => true, 'redirect_url' => home_url());
	wp_send_json($response);

}

/*
 * Register user
 */
add_action('wp_ajax_nopriv_et_mobile_register','et_ajax_mobile_register');
function et_ajax_mobile_register(){
	$response = array('success' => false, 'message' => __('Register failed ', ET_DOMAIN) );
		try {
			$error = '';
			$user  = '';
			//$role  = isset ($_POST['register_user_type']) ? $_POST['register_user_type']  : '';
			$creds = array();
			$creds['user_login']      = isset ($_POST['username']) 		? $_POST['username']  	: '';
			$creds['user_email']      = isset ($_POST['email']) 		? $_POST['email']  		: '';
			$creds['user_password']   = isset ($_POST['password']) 		? $_POST['password']  	: '';
			$creds['user_repassword'] = isset ($_POST['retype_pass']) 	? $_POST['retype_pass'] : '';
			$creds['remember']        = true;

			if(empty($creds['user_login']))
				$error = __('Please enter your username !',ET_DOMAIN);

			if(empty($creds['user_email']))
				$error = __('Please enter a valid email address ',ET_DOMAIN);
			elseif(!et_mobile_validate_email($creds['user_email']))
				$error = __('Invalid email address!',ET_DOMAIN);
			elseif(email_exists($creds['user_email']))
				$error = __('This email address already exists !',ET_DOMAIN);


			if(empty($creds['user_password']))
				$error = __('Please enter password !', ET_DOMAIN);
			elseif(empty($creds['user_repassword']))
				$error = __('Please retype your password !', ET_DOMAIN);
			elseif($creds['user_password'] != $creds['user_repassword'])
				$error = __("Your passwords doesn't match!", ET_DOMAIN);

			if(!empty($error)) {
				$response = array('success' => false, 'message' => $error );
			}else {
				$diplay_name = explode('@', $creds['user_login']);
				$userdata = array(
							'user_login' 	=> $creds['user_login'],
							'user_pass' 	=> $creds['user_password'],
							'user_email' 	=> $creds['user_email'],
							'display_name' 	=> $diplay_name[0],
							'user_nicename' => $diplay_name[0],
							'show_admin_bar_front' => false,
							'show_admin_bar_admin' => false
							//'role'	=> $role
							);
				wp_insert_user($userdata);

				$general_opt=	new ET_GeneralOptions();
				$websitename = $general_opt->get_site_title();

				$message = $general_opt->get_site_demonstration ();
				$message = str_replace("[website]", "{$websitename}", $message);
				$message = str_replace("[user]", "{$userdata['user_login']}", $message);

				$to 		= $creds['user_email'];
				$mes 		= $message;
				$subject 	= 'Email Notification from ' . $websitename;
				$from 		= get_bloginfo( 'admin_email' );

				$headers  = 'MIME-Version: 1.0 \r\n';
	        	$headers .= 'Content-Type: text/html; charset=utf-8\r\n';
				$headers .= 'From: '.$websitename.' <'.$from.'>' . "\r\n" .
							'Reply-To: '.$to.' <'.$to.'>' . "\r\n" .
							'X-Mailer: PHP/' . phpversion();

				add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
				$sent_mail = wp_mail($to,$subject,$mes,$headers);
				if( $sent_mail )
					$response['message'] = __('The email has been sent to ',ET_DOMAIN).$userdata['user_login'];
				else
					$response['message'] = __('Failed to send email',ET_DOMAIN);

				//Login
				wp_signon( $creds, false);
				$response['redirect_url'] = home_url();
				$response['success'] = true;
				$response['message'] .= '\n'.__('Registered successfully.',ET_DOMAIN);
			}
		} catch (Exception $e) {
			$response = array('success' => false, 'message' => __('There is an error occurred', ET_DOMAIN) );
		}
	wp_send_json($response);

}

/*
 * Query job
 */
add_action('wp_ajax_nopriv_et_mobile_main_query','et_ajax_mobile_main_query');
add_action('wp_ajax_et_mobile_main_query','et_ajax_mobile_main_query');
function et_ajax_mobile_main_query(){
	global $et_global,$wp_query;
	$response   = 	array('success' => true, 'message' => __('no items found', ET_DOMAIN));
	try {
		// query vars
		$args_feature	=	array();

		// query paged 
		$max_page =  $_POST['max_page'] ;
		if ( isset( $_POST['m_paged'] ) && $_POST['m_paged'] <= $max_page && !empty($_POST['m_paged'])) {
			$args_feature['paged'] = $_POST['m_paged'];
			$response['load_more'] = true;
		}

		// Have the search key
		if( isset($_POST['search_key']) && !empty($_POST['search_key'] )){
			$args_feature['s']	=	$_POST['search_key'];
			$response['search'] = true;
		}
		// query job
		$query_job_load_more = et_query_jobs($args_feature);
		while ($query_job_load_more->have_posts()) {	$query_job_load_more->the_post();

			$job_id		=	get_the_ID();
			$first_post = 	$_POST['first_post'];
			$job_type	=	et_get_the_job_type ($job_id);
			$job_type 	=	isset($job_type[0]) ? $job_type[0] : '';
			$first_job_type     = et_get_the_job_type( $first_post, 'featured' );
			$first_job_type_tax = isset($first_job_type[0]) ? $first_job_type[0]->term_taxonomy_id : '';
			if ($job_type != '') {
				$class_name = ($job_type->term_taxonomy_id == $first_job_type_tax) ? 'job-fulltime' : 'job-freelance' ;
			}

			$job_location =	et_get_post_field ($job_id, 'location');
			$featured = et_get_post_field( $job_id, 'featured' );

			$data[get_the_ID()]	= array(
				'job_ID'         =>	$job_id,
				'job_title'      =>	get_the_title(),
				'job_content'    =>	get_the_content(),
				'job_author'     => get_the_author(),
				'job_permalink'  => get_permalink($job_id),
				'job_type'       =>	$job_type->name,
				'job_location'   =>	$job_location,
				'job_class_name' =>	$class_name,
				'job_featured'	 => $featured
			);
		}

		$response['contents']  = (isset($data)) ? $data : '';
		$response['success']   = true;
		$response['message']   = 'Query successfully !';
		$response['max_page']  = $query_job_load_more->max_num_pages;
		$response['cur_page']  = $_POST['m_paged'];
		$response['enable_feature'] = et_is_enable_feature();

	} catch (Exception $e) {
		array('success' => true, 'message' => __('There is an error occurred : ', ET_DOMAIN).$e);
	}
	wp_send_json($response);

}

function et_mobile_validate_email($email) {
  return
	is_string($email) &&
	!empty($email) &&
	eregi("^[a-z0-9_-]+[a-z0-9_.-]*@[a-z0-9_-]+[a-z0-9_.-]*\.[a-z]{2,5}$", $email);
}

// Remind Email User Handle
add_action('wp_ajax_nopriv_et_mobile_remind_email','et_ajax_mobile_remind_email');
add_action('wp_ajax_et_mobile_remind_email','et_ajax_mobile_remind_email');
function et_ajax_mobile_remind_email(){
	$response   = 	array('success' => true, 'message' => '');
	try {

		// Validate email
		if(empty($_POST['email']))
			$error = __('Please enter a valid email address ',ET_DOMAIN);
		elseif(!et_mobile_validate_email($_POST['email']))
			$error = __('Invalid email address!',ET_DOMAIN);

		if (empty($error)) {
			$general_opt =	new ET_GeneralOptions();
			$websitename = $general_opt->get_site_title();

			// query post job
			$job_id    = $_POST['job_id'];
			$query_job = get_post($job_id);

			$message 	 = $query_job->post_title;
			$message	.= '\n Link : '. get_permalink( $job_id );
			$message	.= '\n Thank you for visiting.';

			$to = $_POST['email'];
			$mes = $message;
			$subject = 'Email Notification from ' . $websitename;
			$from = get_bloginfo( 'admin_email' );

			$headers  = 'MIME-Version: 1.0 \r\n';
	     	$headers .= 'Content-Type: text/html; charset=utf-8\r\n';
			$headers .= 'From: '.$websitename.' <'.$from.'>' . "\r\n" .
			 			'Reply-To: '.$to.' <'.$to.'>' . "\r\n" .
			 			'X-Mailer: PHP/' . phpversion();

			add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));

			$mes	=	et_get_mail_header().$mes.et_get_mail_footer();
			$sent_mail =	je_remind_job_mail($to,$subject,$mes,$headers);
			if( $sent_mail )
				$response['message'] = __('The email has been sent.',ET_DOMAIN);
			else
				$response['message'] = __('Failed to send email',ET_DOMAIN);

		}else{
			$response   = 	array('success' => false, 'message' => $error);
		}
	} catch (Exception $e) {
		$response   = 	array('success' => false, 'message' => $e);
	}

	wp_send_json($response);
}
?>