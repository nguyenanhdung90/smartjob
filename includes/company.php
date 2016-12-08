<?php 

/**
 * Login a company by ajax
 * return company information json after login
 * return errors if having any
 */
function et_ajax_user_login() {
	// validate here, later
	// login by user name
	$user 	= et_login($_POST['user_name'], $_POST['user_pass'], true);
	if( is_wp_error($user) ) {
		// apply login by email here
		$user 	= et_login_by_email($_POST['user_email'], $_POST['user_pass'], true);
	}
	if ( !is_wp_error($user) ){

		$roles	=	$user->roles;
		$role	=	array_pop($roles);

		$response = array(
			'status' => true,
			'code' => 200,
			'msg' => __('You have logged in successfully', ET_DOMAIN),
			'data' => ($role == 'jobseeker') ? et_create_user_response($user) : et_create_companies_response($user),
		);
		if( isset($_POST['renew_logo_nonce']) ){
			//$response['logo_nonce']	= wp_create_nonce('user_logo_et_uploader');
			$response['logo_nonce']		= je_create_nonce('user_logo_et_uploader');
		};
		// echo "<pre>";
		// print_r($user) ;
		// echo "</pre>";
		$response['data']['role']	=	$role;
		$response['data']	=	array_merge($response['data'], $_POST);
	}
	else {
		$response = array(
			'status' => false,
			'code' => 401,
			'msg' => __('Your login information was incorrect. Please try again.', ET_DOMAIN),
		);
	}

	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	echo json_encode($response);
	exit;
}
add_action( 'wp_ajax_nopriv_et_login', 'et_ajax_user_login' );

/**
 * Register a company by ajax
 * login & return company information after register
 * return errors if having any
 */
function et_ajax_user_register() { 
	$args = array(
		'user_email' => $_POST['user_email'],
		'user_pass' => $_POST['user_pass'],
		'user_login' => $_POST['user_name'],
		'display_name' => isset($_POST['display_name']) ? $_POST['display_name'] : $_POST['user_name']
	);

	// validate here, later
	try {

		if( isset($_REQUEST['role'] ) && $_REQUEST['role'] == 'jobseeker' ) {
			$role	=	$_REQUEST['role'];
		} else {
			$role	=	'company';
		}
		do_action ('je_before_user_register', $args);

		/**
		* validate captcha
		*/
		$job_option	=	ET_JobOptions::get_instance();
		$useCaptcha	=	$job_option->use_captcha () ;
		if($useCaptcha) {
			$captcha	=	ET_GoogleCaptcha::getInstance();
			if( !isset($_POST['recaptcha_challenge_field']) ||
				!$captcha->checkCaptcha( $_POST['recaptcha_challenge_field'] , $_POST['recaptcha_response_field']  ) ) {
				throw new Exception(__("You enter an invalid captcha!", ET_DOMAIN), 400);
			}
		}

		// apply register & log the user in
		$user_id = et_register( $args , $role, true );

		if ( is_wp_error($user_id) ){
			throw new Exception($user_id->get_error_message() , 401);
		}

		if($role == 'company')
			$data	=	et_create_companies_response($user_id);
		else
			$data	=	et_create_user_response($user_id);

		/*
		* Verion : 2.9.3
		*/
		do_action ('je_after_user_register', $user_id, $_REQUEST);
		
		/* strat insert record to wp_post_company table infomation about compnay ostable wp_users*/
		
		if($role == 'company')
		{
		date_default_timezone_set("Asia/Bangkok");
		$dung=date("Y-m-d h:i:s");
		$confile =isset($_POST['display_name']) ? $_POST['display_name'] : $_POST['user_name'];       
        global $wpdb;$wpdb->query("INSERT INTO   wp_post_company (users_id,display_name,user_email,user_registered,manager) VALUES ('".$user_id."','".$confile."','".$_POST['user_email']."','".$dung."','company')");
		unset($confile);unset($dung);
		}
		
		$response = array(
			'status' 		=> true,
			'code' 			=> 200,
			'msg' 			=> __('You are registered and logged in successfully.', ET_DOMAIN),
			'data' 			=> $data,
			'redirect_url'	=> apply_filters( 'je_filter_redirect_link_after_register', $data['profile_url'] )
		);

		if( isset($_POST['renew_logo_nonce']) ){
			//$response['logo_nonce']	= wp_create_nonce('user_logo_et_uploader');
			/**
			* @since version 2.9.5
			*/
			$response['logo_nonce']		= je_create_nonce('user_logo_et_uploader');
		};

	} catch (Exception $e) {
		$response = array(
			'status' => false,
			'code' => $e->getCode(),
			'msg' => $e->getMessage()
		);
	}

	wp_send_json($response);

}
add_action( 'wp_ajax_nopriv_et_register', 'et_ajax_user_register' );

/**
 * Handle request of reseting user's password
 * @since 1.0
 */
function et_ajax_user_request_reset_password(){

	// include the library
	//require_once ABSPATH . 'wp-login.php';

	// call the retrieve password request
	$result = et_retrieve_password();

	if ( is_wp_error($result) ){
		$response = array(
			'success' 	=> false,
			'code' 		=> 400,
			'msg' 		=> $result->get_error_message(),
			'data' 		=> array(
				'redirect_url' => home_url()
				)
			);
	}
	else {
		$response = array(
			'success' 	=> true,
			'code' 		=> 200,
			'msg' 		=> __('Please check your email inbox to reset password.', ET_DOMAIN),
			'data' 		=> array(
				'redirect_url' => home_url()
				)
			);
	}
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	echo json_encode($response);
	exit;
}
add_action( 'wp_ajax_nopriv_et_request_reset_password', 'et_ajax_user_request_reset_password');
/**
 * Handle request of reseting user's password
 * @since 1.0
 */
function et_ajax_user_reset_password(){
	try {
		if ( empty($_REQUEST['user_login']) )
			throw new Exception( __("This user is not found.", ET_DOMAIN) );
		if ( empty($_REQUEST['user_key']) )
			throw new Exception( __("Invalid Key", ET_DOMAIN) );
		if ( empty($_REQUEST['user_pass']) )
			throw new Exception( __("Please enter your new password", ET_DOMAIN) );

		// validate activation key
		$validate_result = et_check_password_reset_key($_REQUEST['user_key'], $_REQUEST['user_login']);
		if ( is_wp_error($validate_result) ){
			throw new Exception( $validate_result->get_error_message() );
		}

		// do reset password
		$user = get_user_by('login', $_REQUEST['user_login']);
		$reset_result = et_reset_password($user, $_REQUEST['user_pass']);
		// print_r($reset_result);
		// exit;

		if ( is_wp_error($reset_result) ){
			throw new Exception( $reset_result->get_error_message() );
		}
		else {
			$response = array(
				'success' 	=> true,
				'code' 		=> 200,
				'msg' 		=> __('Your password has been changed. Please log in again.', ET_DOMAIN),
				'data' 		=> array(
					'redirect_url' => home_url()
					)
			);
		}
	} catch (Exception $e) {
		$response = array(
			'success' 	=> false,
			'code' 		=> 400,
			'msg' 		=> $e->getMessage(),
			'data' 		=> array(
				'redirect_url' => home_url()
				)
			);
	}
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	echo json_encode($response);
	exit;
}
add_action( 'wp_ajax_nopriv_et_reset_password', 'et_ajax_user_reset_password');

function et_custom_reset_password_link($link, $key, $user_login){
	return et_get_page_link('reset-password', array('user_login' => $user_login, 'key' => $key));
}
add_filter('et_reset_password_link', 'et_custom_reset_password_link', 10, 3);

function et_ajax_user_sync(){
	$method		= $_REQUEST['method'];
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	$response	= array();

	switch($method){
		case 'create':
			echo json_encode($response);
			break;

		case 'update':
			$data = $_REQUEST['content'];

			try {
				global $user_ID,$user_login;
				/**
				 * update company view resume
				*/
				if( current_user_can( 'manage_options' ) && isset($data['view_resume_status']) && function_exists('je_update_view_resume_duration')) {
					$status	=	$data['view_resume_status'];
					if($status == 'publish') {
						$order_data	=	get_user_meta ($data['id'] , 'je_resume_view_order_data', true);
						$duration	=	$order_data['duration'];
						je_update_view_resume_duration ($data['id'], $duration );
						update_user_meta( $data['id'] , 'je_resume_view_order_status', 'publish');
						$order_id = wp_update_post( array('ID' => $order_data['order_id'], 'post_status' =>'publish'));

						if(is_wp_error( $order_id )){
							wp_send_json( array('success' => false, 'msg' => __('Don\'t exit this order',ET_DOMAIN) ) );
						}

					} else {
						update_user_meta( $data['id'] , 'je_resume_view_order_status', 'reject');
					}
					$response = array(
						'success' => true,
						'code' => 200,
						'msg' => __('Information has been saved 1', ET_DOMAIN),
						'data' => array(
							'user_id' => $data['id']
							),
						'method'	=> 'update'
						);
					echo json_encode($response);
					break;
				}

				if( current_user_can('edit_users') || $data['id'] == $user_ID) {
					$job_field_require = get_theme_mod('job_require_fields',array( 'user_url') );
					if(!current_user_can( 'manage_options' ) && !et_validate('url',$data['user_url']) && in_array('user_url',$job_field_require) ) {
						throw new Exception(__("Please enter a valid company URL.", ET_DOMAIN));
					}

					$user_data	=	get_userdata( $data['id'] );
					if($user_data->user_email !== $data['user_email'] && email_exists( $data['user_email'] ) ) {
						throw new Exception( __("Sorry, that email address already exists.", ET_DOMAIN) );
					}

					$user_id = et_update_user(array(
						'ID' 			=> $data['id'],
						'user_url' 		=> $data['user_url'],
						'display_name' 	=> !empty($data['display_name']) ? $data['display_name'] : $user_login,
						'user_email'	=> $data['user_email'],
						'description'	=> nl2br(substr ($data['description'], 0, 500 ))
					));
					/**
					* @since 2.9.8
					*/
					do_action("after_update_company_profile", $user_id, $data );

				} else {
					throw new Exception("Permission denied",401);
				}
                $e=!empty($data['display_name']) ? $data['display_name'] : $user_login;
				$response = array(
					'success' => true,
					'code' => 200,
					'msg' => __('Information has been saved 2s'.$data['user_url'].'-'.$user_ID.'-'.$data['user_email'].'-', ET_DOMAIN),
					'data' => array(
						'user_id' => $user_id
						),
					'method'	=> 'update'
					);
					
					/*start update wp_post_company infomation of wp_users when you change your profile acount company*/
					global $wpdb;
					$wpdb->query("UPDATE wp_post_company SET display_name='".$e."' , user_email='".$data['user_email']."', user_url='".$data['user_url']."' WHERE users_id= '".$user_ID."' ");unset($e);
					/*end update wp_post_company infomation of wp_userss when you change your profile acount company*/

				// flush user data cache
				et_create_companies_response ($user_id, true );

			} catch (Exception $e) {
				$response = array(
					'code' => 400,
					'msg' => $e->getMessage(),
					'data' => false,
					'method'	=> 'update'
					);
			}

			echo json_encode($response);
			break;

		case 'read':
			try{
				$data 		= $_REQUEST['content'];
				$response	= array(
					'success'	=> true,
					'code'		=> 200,
					'msg'		=> '',
					'data'		=> et_create_companies_response($data['id']),
					'method'	=> 'read'
					);
			} catch (Exception $e) {
				$response = array(
					'success'	=> false,
					'code' 		=> 400,
					'msg' 		=> __('Failed to get user data', ET_DOMAIN),
					'data' 		=> false,
					'method'	=> 'read'
					);
			}

			echo json_encode($response);
			break;
	}
	exit;
}
add_action('wp_ajax_et_company_sync', 'et_ajax_user_sync');

function et_ajax_user_fetch(){
	$method		= $_REQUEST['method'];
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );

	$response	= array();

	switch($method){
		case 'read':
			try{
				$data 		= $_REQUEST['content'];
				$response	= array(
					'success'	=> true,
					'code'		=> 200,
					'msg'		=> '',
					'data'		=> et_create_companies_response($data['id']),
					'method'	=> 'read'
					);
			} catch (Exception $e) {
				$response = array(
					'success'	=> false,
					'code' 		=> 400,
					'msg' 		=> __('Failed to get user data', ET_DOMAIN),
					'data' 		=> false,
					'method'	=> 'read'
					);
			}

			echo json_encode($response);
			break;
	}
	exit;
}
add_action('wp_ajax_nopriv_et_company_sync', 'et_ajax_user_fetch');

/**
 * 
 */
function et_ajax_logout(){
	wp_logout();
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	echo json_encode(array(
		'status' => 200,
		'msg' => __('You were logged out successfully.', ET_DOMAIN)
		));
	exit;
}
add_action('wp_ajax_et_logout', 'et_ajax_logout');

function et_ajax_change_pass(){
	global $current_user;

	try {
		$user_email	= $current_user->data->user_email;

		if( !isset( $_REQUEST['user_old_pass'] ) || !isset( $_REQUEST['user_pass'] ) ){
			throw new Exception(__('Please enter all required information to reset your password.', ET_DOMAIN ), 400 );
		}

		// check old password
		$pass_check = wp_check_password( $_REQUEST['user_old_pass'], $current_user->data->user_pass, $current_user->data->ID );

		if ( !$pass_check ) {
			throw new Exception(__('Old password is not correct.', ET_DOMAIN), 401);
		}

		if ( empty($_REQUEST['user_pass']) ) 
			throw new Exception(__('Your new password cannot be empty.', ET_DOMAIN), 400);

		// set new password
		wp_set_password( $_REQUEST['user_pass'], $current_user->data->ID );

		// relogin the user automatically
		if( is_wp_error( et_login_by_email( $user_email, $_REQUEST['user_pass'] ) ) ){
			throw new Exception(__('Your password was changed! Please login again!',ET_DOMAIN ), 401 );
		};

		$resp = array(
			'success' 	=> true,
			'msg' 		=> __('Your password has been changed successfully!', ET_DOMAIN),
			'code' 		=> 200,
			'data' 		=> ''
			);

	} catch (Exception $e) {
		$resp = array(
			'success' 	=> false,
			'msg' 		=> $e->getMessage() ? $e->getMessage() : __('An error has occurred!', ET_DOMAIN),
			'code' 		=> $e->getCode() ? $e->getCode() : 400,
			'data' 		=> ''
			);
	}

	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	echo json_encode($resp);
	exit;
}
add_action('wp_ajax_et_change_pass', 'et_ajax_change_pass');

function et_ajax_logo_upload(){
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	$res	= array(
		'success'	=> false,
		'msg'		=> __('There is an error occurred', ET_DOMAIN ),
		'code'		=> 400,
	);
	// check fileID
	if(!isset($_POST['fileID']) || empty($_POST['fileID']) ){
		$res['msg']	= __('Missing image ID', ET_DOMAIN );
	}
	else {
		$fileID	= $_POST["fileID"];

		// check author
		if(!isset($_POST['author']) || empty($_POST['author']) || !is_numeric($_POST['author']) ){
			$res['msg']	= __('Missing company data', ET_DOMAIN );
		}
		else{
			$author	= $_POST['author'];

			// check ajax nonce
			//if (!de_check_ajax_referer($imgType . '_et_uploader', false, false) && !check_ajax_referer($imgType . '_et_uploader', false, false) ) {
			if ( !je_check_ajax_referer('user_logo_et_uploader', false, false) &&  !check_ajax_referer( 'user_logo_et_uploader', '_ajax_nonce', false ) ){
				$res['msg']	= __('Security error!', ET_DOMAIN );
			}
			elseif(isset($_FILES[$fileID])){

				// handle file upload
				$attach_id	= et_process_file_upload( $_FILES[$fileID], $author, 0, array(
						'jpg|jpeg|jpe'	=> 'image/jpeg',
						'gif'			=> 'image/gif',
						'png'			=> 'image/png',
						'bmp'			=> 'image/bmp',
						'tif|tiff'		=> 'image/tiff'
					) );

				if ( !is_wp_error($attach_id) ){

					// Update the author meta with this logo
					try {
						$user_logo	= et_get_attachment_data($attach_id);
						/**
						 * get old logo and delete it
						 */
						$old_logo	= et_get_user_field($author, 'user_logo');
						if(isset($old_logo['attach_id'])) {
							$old_logo_id = $old_logo['attach_id'];
							wp_delete_attachment( $old_logo_id, true);
						}
						/**
						 * update new user logo
						*/
						et_update_user(array(
							'ID'		=> $author,
							'user_logo'	=> $user_logo
						));

						// flush user data cache
						et_create_companies_response ($author, true );

						$res	= array(
							'success'	=> true,
							'msg'		=> __('Company logo has been uploaded successfully!'.$user_logo, ET_DOMAIN ),
							'data'		=> $user_logo
						);
						
						// start logo insert table wp_post_company
						global $wpdb;$deee=serialize($user_logo);
						$wpdb->query("UPDATE wp_post_company SET logo='".$deee."' WHERE users_id='".$author."' ");unset($deee);
                        //end  logo insert table wp_post_company
					}
					catch (Exception $e) {
						$res['msg']	= __( 'Problem occurred while updating user field', ET_DOMAIN );
					}
				}
				else{
					$res['msg']	= $attach_id->get_error_message();
				}
			}
			else {
				$res['msg']	= __('Uploaded file not found', ET_DOMAIN);
			}
		}
	}
	echo json_encode($res);
	exit;
}
add_action('wp_ajax_et_logo_upload', 'et_ajax_logo_upload');

function et_ajax_backend_fetch_companies(){
	global $wpdb;
	$data = $_REQUEST['content'];

	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	try {
		$items_per_page = apply_filters('et_items_per_page', 10);
		// if ( $result <= $paged * $items_per_page )

		$companies = et_get_users_post_count( array(
				'users_per_page' 	=> $items_per_page,
				'paged' 			=> $data['paged'] ,
				's' 				=> !empty($data['s']) ? $data['s'] : ''
			));
		$total 			= (int)$wpdb->get_var("SELECT FOUND_ROWS()");

		foreach ($companies as $key => $company) {
			$companies[$key]->permalink 	= get_author_posts_url($company->ID);
			$companies[$key]->count_text 	= sprintf( et_number( __('No job', ET_DOMAIN), __('%d job', ET_DOMAIN), __('%d jobs', ET_DOMAIN), $company->count ), $company->count);
		}

		$res = array(
			'success' 	=> true,
			'msg' 		=> '',
			'data' 		=> array(
				'companies'  => $companies,
				'pagination' => array(
					'paged' 		=> $data['paged'],
					'total' 		=> $total,
					'total_page' 	=> ceil( $total / $items_per_page )
					),
				'query' 	=> $data
				)
			);
	} catch (Exception $e) {
		$res	= array(
			'success'	=> false,
			'msg'		=> $e->getMessage(), // __('There is an error occurred', ET_DOMAIN ),
			'code'		=> $e->getCode()
		);
	}

	echo json_encode($res);
	exit;
}
add_action('wp_ajax_et_backend_fetch_companies', 'et_ajax_backend_fetch_companies');

/** ==================================================
 *  Company Functions
 *  ==================================================*/

/**
 * return the default company data to response to client
 * @param  [int/WP_User] $user user ID or WP_USER object
 * @return [type]       array of user data
 * @since  1.0
 */
function et_create_companies_response($user , $flush = false ){
	if ( is_numeric( $user ) ){
		$user	= get_userdata( (int)$user );
	}
	if ( empty( $user->ID ) ){
	 	return;
	}
	else{
		global $user_ID;
		$company_response	=	wp_cache_get( $user->ID, 'je_company' );

		if( $flush || !$company_response ) {

			$user_logo			= 	et_get_company_logo( $user->ID );
			$apply_method		=	trim(et_get_user_field ($user->ID,'apply_method'));
			$apply_email		=	trim(et_get_user_field ($user->ID,'apply_email'));
			$applicant_detail	=	trim(et_get_user_field ($user->ID,'applicant_detail'));
			$company_response	=	array(
				'id' 			=> $user->ID,
				'ID' 			=> $user->ID,
				//'user_email' 	=> $user->user_email,
				'display_name' 	=> $user->display_name,
				'user_url' 		=> $user->user_url,
				//'login_name' 	=> $user->user_login,
				'post_url'		=> get_author_posts_url( $user->ID ),
				'user_logo'		=> $user_logo,
				'recent_location' => et_get_user_field ($user->ID,'recent_job_location'),
				'description'	  => apply_filters('et_author_description', get_the_author_meta('description', $user->ID )),
				'apply_method'		=> ($apply_method != '') ? $apply_method : 'isapplywithprofile' ,
				'apply_email'		=> ($apply_email != '') ? $apply_email : $user->user_email ,
				'applicant_detail'	=> ($applicant_detail != '') ? $applicant_detail : /*__("Write your instructions here", ET_DOMAIN)*/ '',
				'payment_plans' 	=> et_get_purchased_quantity($user->ID),
				'profile_url'		=> et_get_page_link('dashboard'),
				'is_admin'			=> et_is_admin($user->ID)
			) ;

			if($user_ID == $user->ID) {
				$company_response['used_free'] = get_user_meta( $user_ID , 'je_free_plan_used' , true );
			}

			$company_response	=	apply_filters('et_companies_response' , $company_response);
			wp_cache_set( $user->ID, $company_response, 'je_company', 15*24*60*60 ) ;
		}
		
     
		return $company_response;
	}
}

function et_is_admin($user_id){
	if(current_user_can('manage_options' ))
		return true;
	return false;
}

/**
 * return company logo
 * param: $user user id
*/
function et_get_company_logo($user){

	if(!is_numeric($user)) {
		if($user instanceof WP_User)
			$user	=	$user->ID;
	}

	$user_logo	= et_get_user_field( $user, 'user_logo' );

	if ( empty($user_logo) ){ // return default logo if user logo empty
		$general_opt = new ET_GeneralOptions();
		$default_logo	= $general_opt->get_default_logo();
		$default_user_logo = array(
			'small_thumb'	=> $default_logo,
			'company-logo'	=> $default_logo,
			'thumbnail'		=> $default_logo,
			'attach_id'		=> 0
		);
		return $default_user_logo;
	}

	return $user_logo;

}

/**
 * Return all job type count in database
 *
 * @since 1.0
 */
function et_get_companies_count(){
	global $wpdb;
	$count = count_users();
	if ( isset( $count['avail_roles']['company'] ) )
		return $count['avail_roles']['company'];
	else return 0;
}

/**
 * get companies list
 *
 * @since 1.0
 */
function et_get_companies($args = array()){

	$only_load_active	=	get_theme_mod( 'je_show_active_company' , true );
	if( $only_load_active && !isset($args['include']) ) {
		$companies = et_get_active_companies();
		return $companies;
	}

	global $et_global;

	$db_prefix = $et_global['db_prefix'];
	$args = wp_parse_args($args, array(
		'role' => 'company',
		'orderby' => 'display_name',
		'order' => 'ASC'
		));

	$companies = get_users($args);
	$return = array();

	foreach ($companies as $company) {
		if (isset($company->data))
			$com = (array)$company->data;
		else
			$com = (array)$company;

		// default field
		$com['logo'] = get_user_meta($company->ID, $db_prefix . 'logo', true);
		$return[] = (object)$com;
	}

	return $return;
}
function et_get_companies2($args = array()){

	$only_load_active	=	get_theme_mod( 'je_show_active_company' , true );
	if( $only_load_active && !isset($args['include']) ) {
		$companies = et_get_active_companies();
		return $companies;
	}

	global $et_global,$wpdb;

	$db_prefix = $et_global['db_prefix'];
	$args = wp_parse_args($args, array(
		'role' => 'company',
		'orderby' => 'display_name',
		'order' => 'ASC'
		));

	$companies = get_users($args);
	$return = array();

	foreach ($companies as $company) {
		if (isset($company->data))
			$com = (array)$company->data;
		else
			$com = (array)$company;

		// default field
		//$com['logo'] = get_user_meta($company->ID, $db_prefix . 'logo', true);
		$return[] = (object)$com;
	}
	$results = $wpdb->get_results( "SELECT  ID,display_name,users_id FROM wp_post_company WHERE manager = 'editor' OR manager = 'administrator' ", OBJECT );
	foreach ($results as $result) {
		$com['display_name']=$result->display_name;		
		$com['ID']=$result->users_id;			
		$com['case_manager']=$result->ID;;		
		$user_login = $wpdb->get_results( "SELECT  user_login FROM wp_users WHERE ID = '".$result->users_id."' " );
		$com['user_login']=$user_login[0]->user_login;
		$return[] = (object)$com;
	}
	return $return;
}
/**
 * Get employers who have active jobs
 * @return array objects
 */
function et_get_active_companies($args = array()){
	global $wpdb;

	$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT post_author FROM {$wpdb->posts} as job
			JOIN {$wpdb->users} as user ON job.post_author = user.ID
			JOIN {$wpdb->usermeta} as user_meta ON user.ID = user_meta.user_id AND user_meta.meta_key = '{$wpdb->prefix}capabilities' AND user_meta.meta_value LIKE '%company%'
			WHERE post_status = 'publish' AND post_type ='job'";
	$authors = $wpdb->get_results($sql, ARRAY_N);
	$ids = array();
	foreach ($authors as $key => $value) {
		$ids[] = $value[0];
	}
	if (!empty($ids))
		return et_get_companies(array('include' => $ids));
	else
		return array();
}

/**
 * Get companies in alphabet
 *
 * @since 1.0
 */
function et_get_companies_in_alphabet($hide_empty = false){
	$only_load_active	=	get_theme_mod( 'je_show_active_company' , true );
	if($only_load_active)
		$companies = et_get_active_companies();
	else 
		$companies =	et_get_companies2 ();

	$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

	// generate list
	$new_list = array();
	$new_list['numbers']	=	array();
	for ($i=0; $i < 26; $i++) { 
		$key = substr($alphabet, $i, 1);
		$new_list[$key] = array();
	}

	foreach ($companies as $key => $company) {
		$first_letter = strtoupper( substr( trim($company->display_name), 0, 1 ) );
		if ( preg_match('/^[0-9]/', $company->display_name) ){
			$new_list['numbers'][] = $company;
			unset($companies[$key]);
		}else if ( isset($new_list[$first_letter]) ){
			$new_list[$first_letter][] = $company;
			unset($companies[$key]);
		}
	}
	$new_list['Others'] = $companies;

	if ( $hide_empty ){
		foreach ($new_list as $key => $letter) {
			if ( empty($letter) ){
				unset($new_list[$key]);
			}
		}
	}

	return $new_list;
}

/**
 * Count job number of company
 *
 * @since 1.0
 */
function et_get_users_post_count($args = array()){
	global $wpdb, $companies_found;

	$args = wp_parse_args($args, array(
		'users_per_page' => apply_filters('et_items_per_page', 10),
		'paged' 		=> 1,
		'order' 		=> 'DESC',
		'orderby' 		=> 'user_registered',
		'role' 			=> 'company',
		's' 			=> ''
		));

	$condition = !empty($args['s']) ? "AND `user`.display_name LIKE '%{$args['s']}%'" : "";

	$meta_key 	= $wpdb->prefix . 'capabilities';
	$offset		= ($args['paged'] - 1) * $args['users_per_page'];
	$count 		= $args['users_per_page'];
	$limit 		= $args['users_per_page'] > 0 ?  "LIMIT {$offset}, {$count}" : '';

	$sql = "SELECT `post`.post_author, COUNT(`post`.ID) as count FROM {$wpdb->posts} as `post` WHERE `post`.post_type ='job' AND `post`.post_status = 'publish' GROUP BY `post`.post_author ";
	$sql = "SELECT SQL_CALC_FOUND_ROWS `user`.*, (SELECT COUNT(`post`.ID)
				FROM {$wpdb->posts} as `post`
				WHERE `post`.post_type = 'job'
					AND `post`.post_status = 'publish'
					AND `post`.post_author = `user`.ID ) as count
			FROM {$wpdb->users} as `user`
			INNER JOIN {$wpdb->usermeta} as `meta`
				ON `meta`.user_id = `user`.ID AND `meta`.meta_key = '{$meta_key}'
			WHERE `meta`.meta_value LIKE '%{$args['role']}%' $condition
			ORDER BY {$args['orderby']} {$args['order']}
			$limit ";

	$results = $wpdb->get_results( $sql );
	//$companies_found = $wpdb->get_var("SELECT FOUND_ROWS()");
	return $results;
}

/**
 * Getting companies number within a time
 */
function et_count_companies_by_time( $time = 259200 ){
	global $wpdb;

	$from = date('Y-m-d h-i-s', strtotime('now') - $time );
	$fromsql = $time == 0 ? "" : " AND `user`.user_registered > '{$from}' ";

	$sql = "SELECT COUNT(ID) FROM {$wpdb->users} `user`
		INNER JOIN {$wpdb->usermeta} as `meta` ON `meta`.user_id = `user`.ID AND `meta`.meta_key = '{$wpdb->prefix}capabilities'
		WHERE `meta`.meta_value LIKE '%company%' {$fromsql} ";

	$result = $wpdb->get_var($sql);
	return (int)$result;
}


function je_user_package_data ( $user_ID = 0 , $view = 'all' ) {
	if(!$user_ID) {
		global $user_ID;
	}
	if( $user_ID ) {
		$user_paid	=	je_get_current_user_order ($user_ID);

		$purchase_plans = et_get_purchased_quantity($user_ID);

		$plans 			= et_get_payment_plans();

		$purchase_count = 0;
		foreach ($purchase_plans as $id => $quantity) {
			if(isset($plans[$id]))
				$purchase_count += $quantity;
		}

		$resume_view_duration	=	0;
		if(function_exists('je_get_resume_view_duration')) {
			$resume_view_duration	=	je_get_resume_view_duration ($user_ID);
			if($resume_view_duration > time () )
				$resume_view_duration	=	$resume_view_duration - time();
		}

		if( $purchase_count ||  $resume_view_duration > 0 ) { ?>
			<div class="widget bg-grey-widget companies-statis">
				<?php
				if( $view != 'resume' ) {
					foreach ($purchase_plans as $id => $quantity) {
						if ($quantity <= 0 || !isset($plans[$id])) continue;
						echo '<div>';
						if ($quantity >= 1 && isset($plans[$id])) {
							$current_order		=	(isset($user_paid[$id] ) ? $user_paid[$id] : 0 );
							$order	=	get_post ($current_order);
							$status	=	$order->post_status;

							if($status == 'publish')
								printf(__("You purchased package <strong>%s</strong> and have %d job/s left.", ET_DOMAIN), $plans[$id]['title'] , $quantity ); 
							if( $status == 'pending' )
								printf(__("You purchased package <strong>%s</strong> and have %d job/s left. Your posted job is pending until payment.", ET_DOMAIN), $plans[$id]['title'] , $quantity );
							echo '</div>';
						}

					}
				}

				if( $resume_view_duration && $view != 'job' ) {
					echo '<div>';
					$time	=	round($resume_view_duration / ( 24 *60 * 60 ) );
					if($time > 1 ) {
						printf(__("You have %s days remaining to view resume details.", ET_DOMAIN) , $time );
					} else {
						printf(__("You have %s day remaining to view resume details.", ET_DOMAIN) , $time );
					}
					echo '</div>';
				}

				?>

			</div>
		<?php }
	}
}

?>