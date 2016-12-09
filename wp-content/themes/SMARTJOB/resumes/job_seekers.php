<?php 
/**
 * JobEngine Job Seeker API
 * @since 2.0
 */

/**
 * Handle all the job seeker tasks in Job Engine
 * @since 2.0
 */
class JE_Job_Seeker extends ET_Base{
	const ROLE = 'jobseeker';
	static $fields = array('et_profession_title', 'et_location', 'description', 'et_avatar', 'first_name', 'et_privacy' , 'et_accessible_companies', 'et_contact');

	/**
	 * Initialize function
	 */
	function __construct(){
		$this->add_action('init', 'add_role');
		$this->add_action('et_after_register', 'after_register_user');
		$this->add_filter( 'et_user_response', 'user_response' ,10 , 2);

		$this->add_filter('user_contactmethods', 'modify_contact_methods');
		$this->add_filter('je_filter_header_account_link', 'je_filter_header_account_link');
	}

	/**
	 * Add Job Seeker role into jobengine
	 */
	public function add_role(){
		//remove_role('jobseeker');
		if (get_role('jobseeker')) return;

		$caps = apply_filters( 'jobseeker_cap', array(
			'read' 			=> true,
			'edit_posts' 	=> true,
			'delete_posts' 	=> true,
			'upload_files' 	=> true
			) );
		add_role('jobseeker', 'Job Seeker', $caps);
	}

	/**
	 * 
	 */
	public function modify_contact_methods($fields){

		// Add new fields
		$profile_fields['twitter'] = 'Twitter Username';
		$profile_fields['facebook'] = 'Facebook URL';
		$profile_fields['gplus'] = 'Google+ URL';
		$profile_fields['linkedin'] = 'linkedin URL';

		return $profile_fields;
	}

	/**
	 * Insert new job seeker
	 * @param $args array contains user data
	 */
	static public function insert($args, $wp_error = false){
		try {
			if (empty($args['user_login']))
				throw new Exception(__('Missing username', ET_DOMAIN));

			$args = wp_parse_args( $args, array( 'role' => self::ROLE ) );
			$args = apply_filters( 'insert_job_seeker', $args );

			$fields = array();
			foreach (self::$fields as $meta_key) {
				if ( isset($args[$meta_key]) ){
					$fields[$meta_key] = $args[$meta_key];
					unset($args[$meta_key]);
				}
			}

			$result = wp_insert_user( $args, $wp_error );

			if ( !$result || is_wp_error( $result ) ) 
				return $wp_error;

			// insert user meta
			foreach ($fields as $key => $value) {
				update_user_meta( $result, $key, $value );
			}

			// call action
			do_action('insert_job_seeker', $result);

			// insert new user
			return $result;
		} catch (Exception $e) {
			if ($wp_error)
				return new WP_Error('add_job_seeker_username', __('Missing User Name', ET_DOMAIN));
			else 
				return false;
		}
	}

	/**
	 * update job seeker
	 */
	static public function update($args, $wp_error = false){
		try {
			if (empty($args['ID']))
				throw new Exception(__('Missing ID', ET_DOMAIN));

			$args = wp_parse_args( $args, array( 'role' => self::ROLE ) );
			$args = apply_filters( 'insert_job_seeker', $args );

			// Filter all meta data to another array
			$fields = array();
			foreach (self::$fields as $meta_key) {
				if ( isset($args[$meta_key]) ){
					$fields[$meta_key] = $args[$meta_key];
					unset($args[$meta_key]);
				}
			}

			// update user data
			$result = wp_update_user( $args, $wp_error );

			if ( !$result || is_wp_error( $result ) ) 
				return $wp_error;

			// Update user meta data
			foreach ($fields as $key => $value) {
				update_user_meta( $result, $key, $value );
			}

			// UPDATE RESUMES
			// There are some meta data that should be cloned to resume			
			$resume_metas = array(
				'et_profession_title' 	=> isset($fields['et_profession_title']) ? $fields['et_profession_title'] : false,
				'et_url' 				=> isset($args['user_url']) ? $args['user_url'] : false,
				'et_location' 			=> isset($fields['et_location']) ? $fields['et_location'] : false,
				'et_privacy'			=> isset($fields['et_privacy']) ? $fields['et_privacy'] : false
			);
			// get all resume belonged to user
			$resumes = get_posts( array(
				'post_type' 	=> 'resume',
				'post_status' 	=> 'any',
				'numberposts' 	=> -1,
				'author'   => $args['ID']
			) );
			// update resume meta data
			foreach ($resumes as $resume) {
				$args_privacy	=	array('ID' => $resume->ID) ;

				if(isset($args['display_name']) ) {
					$args_privacy['post_title']	= $args['display_name'];
				}

				if(isset($fields['et_privacy'])) {
					$args_privacy['et_privacy']	= $fields['et_privacy'];
				}

				if ( isset($args['display_name']) ||  isset($fields['et_privacy']) ) 
					JE_Resume::update( $args_privacy );
				

				foreach ($resume_metas as $key => $value) {
					if ( isset($fields[$key]) || $value ) update_post_meta( $resume->ID, $key, $value );
				}
			}
			// finish update resume meta

			// call action for further needs
			do_action('update_job_seeker', $result);
			// update_user_meta( $result , 'ads_text', $args['ads_text'] );
			// return result
			return $result;
		} catch (Exception $e) {
			if ($wp_error)
				return new WP_Error('update_job_seeker', $e->getMessage());
			else 
				return false;
		}
	}

	/**
	 * Convert from user to job seeker object
	 */
	static public function convert_from_user($user){
		if(!$user) return false;
		foreach (self::$fields as $field) {
			$user->$field = get_user_meta( $user->ID, $field, true );
		}
		unset ($user->data->user_pass);
		// bonus contact methods
		switch ($user->et_privacy) {
			case 'confidential':
				$user = self::confidential_user ($user);
				break;
			
			default:
				$user =  self::public_user ($user);
				break;
		}
		// $user->ads_text	=	get_user_meta( $user->ID, 'ads_text', true );
		$user	=	apply_filters( 'convert_from_user', $user );
		return $user->data;
		
	}

	static public function  confidential_user ($user) {
		global $current_user;
		/**
		 * shouldn't set confidential for owner
		*/
		if($current_user->ID == $user->ID || current_user_can( 'manage_options' ))  return self::public_user ($user);

		$accessible_list    =   JE_Job_Seeker::get_accessible_list($user->ID);
        if( in_array( $current_user->ID, $accessible_list)) return self::public_user ($user);

		$user->display_name	=	__("Anonymous", ET_DOMAIN);
		$img = et_get_resume_avatar($user->ID, 150);

		// trim content, get image src
		preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $img,$matches);
		$img_url = $matches[1];

		// generate avatars array
		$avatars = array();
		if (is_array( $user->et_avatar ))
		{ 
			foreach ((array)$user->et_avatar as $key => $value) {
				$avatars[$key][0] = $img_url;
			}
		} else {
			$keys = get_intermediate_image_sizes();
			foreach ($keys as $key) {
				$avatars[$key] = array( $img_url );
			}
		}

		$user->et_avatar = $avatars;

		$confidential_data = array('user_email' , 'user_nicename'  );
		foreach ($confidential_data as $value) {
			unset($user->data->$value);
		}

		return apply_filters( 'je_confidential_user', $user );
	}

	static public function public_user ($user) {

		$contacts = array('twitter', 'facebook', 'gplus', 'linkedin');
		foreach ($contacts as $contact) {
			$user->$contact = get_user_meta( $user->ID, $contact, true );
		}

		// get properly avatar when user has no avatar
		if ( empty($user->et_avatar['thumbnail']) || empty($user->et_avatar['thumbnail'][0])){
			// get default avatar
			$img = et_get_resume_avatar($user->ID, 150);

			// trim content, get image src
			preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $img,$matches);
			$img_url = $matches[1];

			// generate avatars array
			$avatars = array();
			if (is_array( $user->et_avatar ))
			{ 
				foreach ((array)$user->et_avatar as $key => $value) {
					$avatars[$key][0] = $img_url;
				}
			} else {
				$keys = get_intermediate_image_sizes();
				foreach ($keys as $key) {
					$avatars[$key] = array( $img_url );
				}
			}
			$user->et_avatar = $avatars;
		}

		return apply_filters('je_public_user', $user);
		
	}

	static function set_accessible_list ($jobseeker_ID, $access_ID) {
		//delete_user_meta( $jobseeker_ID, 'et_accessible_companies');
		$accessible_companies    =  get_user_meta ($jobseeker_ID, 'et_accessible_companies', true );
		if(!is_array($accessible_companies))	$accessible_companies	=	array();
		if(!in_array($access_ID, $accessible_companies)) {
			array_push($accessible_companies, $access_ID);
			update_user_meta( $jobseeker_ID, 'et_accessible_companies', $accessible_companies )	;
		}
        
	}
	
	static public function get_accessible_list ($jobseeker_ID) {
		$accessible_companies    =  get_user_meta ($jobseeker_ID, 'et_accessible_companies', true );
		if(!is_array($accessible_companies))	$accessible_companies	=	array();
		return $accessible_companies;
	}

	/**
	 * get a job seeker by given id
	 */
	static public function get($ID){
		$user = get_userdata($ID);
		return self::convert_from_user($user);
	}

	/**
	 * get a job seeker by given id
	 */
	static public function get_meta($id, $field){
		if ( in_array( $field, self::$fields ) ){
			return get_user_meta( $id, $field, true );
		} else 
			return false;
	}	

	/**
	 * get job seekers via function get_users
	 */
	static public function get_jobseekers($args){
		$args = wp_parse_args( $args, array('role' => 'jobseeker') );
		$users 		= get_users($args);
		$jobseekers = array();
		foreach ($users as $user) {
			$jobseekers[] = self::convert_from_user($user);
		}
		return $jobseekers;
	}

	public function after_register_user ($user) {
		if(!$user) return $user;
		if(isset($_REQUEST['role']) && $_REQUEST['role'] == 'jobseeker') {
			$fields = array();
			if(isset($_REQUEST['et_avatar'])) {
				$all_sizes	= get_intermediate_image_sizes();
				foreach ($all_sizes as $size) {
					$data[$size]	=  array($_REQUEST['et_avatar'], 300, 200  );
				}
				$_REQUEST['et_avatar']	=	$data;
			}
			foreach (self::$fields as $meta_key) {
				if ( isset($_REQUEST[$meta_key]) ){
					update_user_meta( $user, $meta_key, $_REQUEST[$meta_key] );
				}
			}
		}

		return $user;
	}

	public function user_response ($response, $user) {
		$roles	=	$user->roles;
		if(array_pop($roles) == 'jobseeker') {
			$response['role']		=	'jobseeker';
			$resume =   JE_Resume::get_resumes(array('author' => $user->ID,'post_status' => array('pending', 'publish')));
			if(isset($resume[0])) {
				$id =   $resume[0]->ID;
			}else {
				$id	=	JE_Resume::insert (array('post_title' => $user->user_login, 'post_author' => $user->ID));
			}
		   // wp_reset_query();
			$response['profile_url']	=	get_permalink($id);
			foreach (self::$fields as $meta_key) {
				if ( isset($args[$meta_key]) ){
					$response[$meta_key] = get_usermeta( $user->ID, $meta_key, true);
				}
			}
		}
		return $response;
	}

	public function je_filter_header_account_link ($link) {
		global $current_user;
		$roles	=	$current_user->roles;
		if(array_pop($roles) == 'jobseeker') {
			$resume =   JE_Resume::get_resumes(array('author' => $current_user->ID,'post_status' => array('pending', 'publish')));
			if(isset($resume[0])) {
				$id =   $resume[0]->ID;
			}else {
				$id	=	JE_Resume::insert (array('post_title' => $current_user->user_login, 'post_author' => $current_user->ID));
			}
			//wp_reset_query();

			return 	get_permalink($id);
		}
		return $link;
	}
}


/**
 * Jobseeker ajax requests
 */
class JE_JobSeeker_Ajax extends ET_Base{
	/**
	 * ajax action when create jobseeker
	*/
	const AJAX_CREATE 	= 'seeker_create';
	/**
	 * ajax action sync jobseeker data
	*/
	const AJAX_SYNC	  	= 'et_jobseeker_sync';
	/**
	 * ajax action contact jobseeker , send mail
	*/
	const AJAX_CONTACT  = 'et_contact_jobseeker';
	/**
	 * ajax action update jobseeker account infomation
	*/
	const AJAX_UPDATE_ACCOUNT = 'et_jobseeker_update_account';

	function __construct(){
		//$this->add_ajax(self::AJAX_CREATE, 			'create_jobseeker');
		$this->add_ajax(self::AJAX_SYNC, 			'jobseeker_sync', true , false);
		$this->add_ajax(self::AJAX_CONTACT, 		'contact_jobseeker');
		$this->add_ajax(self::AJAX_UPDATE_ACCOUNT, 	'current_jobseeker_update', true , false);

		$this->add_ajax('et_avatar_upload', 'upload_avatar', true, false);
	}

	protected function header () {
		header( 'HTTP/1.0 200 OK' );
		header( 'Content-type: application/json' );
	}

	/**
	 * contact jobseeker
	 */
	public function contact_jobseeker(){
		$args 		= $_POST['content'];

		parse_str($_POST['content'], $args);

		do_action("pre_contact_jobseeker",$args);
		try {
			if ( empty($args['sender_email']) || empty($args['receive']) || empty($args['sender_name']) )
				throw new Exception(__("Missing arguments", ET_DOMAIN));

			$job_option	=	ET_JobOptions::get_instance();
			$useCaptcha	=	$job_option->use_captcha () ;

			if($useCaptcha && !current_user_can('manage_options')) {
				$captcha	=	ET_GoogleCaptcha::getInstance();
				if( empty($args['recaptcha_challenge_field']) || !$captcha->checkCaptcha( $args['recaptcha_challenge_field'] , $args['recaptcha_response_field']  ) ) {
					throw new Exception(__("You enter an invalid captcha!", ET_DOMAIN), 400);
				}
			}

			$receiver 	= get_userdata($args['receive']);
			if (empty($receiver) && $receiver->user_email)
				throw new Exception(__("Can't find receiver", ET_DOMAIN));

			$headers 	= 	'MIME-Version: 1.0' . "\r\n";
			$headers 	.= 'Content-type: text/html; charset=utf-8' . "\r\n";
			$headers 	.= 	"From: ". $args['sender_email']." < ".$args['sender_name'] ."> \r\n";
			$headers 	.= 	'Reply-To: ' . $args['sender_email'] . " \r\n" .
							'X-Mailer: PHP/' . phpversion();

			$mail_template	=	JE_Resumes_Mailing::get_instance ();
			$message		=	$mail_template->get_template('contact');

			$option			=	JE_Resume_Options::get_instance();
			if($message != '' && $option->et_send_mail_contact ) {
				$mail_args 		=  array(
						'blogname'		=> get_bloginfo('blogname'),
						'display_name'	=> $receiver->display_name,
						'admin_email'	=> get_option('admin_email'),
						'user_email'	=> $receiver->user_email,
						// 'resume_link'	=> isset($resume->permalink) ? $resume->permalink :'',
						// 'profile_link'  => isset($resume->permalink) ? $resume->permalink :'',
						'contact_msg'	=> isset($args['message']) ? nl2br ($args['message']) :'' ,
						'contact_name' 	=> $args['sender_name'],
						'contact_email' => $args['sender_email']
					);

				//$message 	= $template;
				foreach ($mail_args as $key => $arg) {
					$message = str_replace("[$key]", $arg, $message);
				}
			} else {
				$message	=	$args['message'];
			}

			$result = wp_mail( $receiver->user_email,
					sprintf(__('Message from %s', ET_DOMAIN), $args['sender_name']),
					et_get_mail_header().$message.et_get_mail_footer(), $headers );

			// if sending success return response
			if ($result){
				wp_send_json(array(
					'success' 	=> true,
					'msg' 		=> __('Your message was sent successfully. You will be redirected to the profile page in 3 seconds.', ET_DOMAIN),
					'data' 		=> array(
						'params' => $args
					)
				));
				do_action("after_contact_jobseeker",$result, $args);
			}
			else  {
				throw new Exception(__("Your message can't be sent. You will be redirected to the profile page in 3 seconds.", ET_DOMAIN));
			}

		} catch (Exception $e) {
			wp_send_json(array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage(),
					'data' 		=> array(
						'params' => $args
					)
			));
		}

	}

	 /**
	 * AJAX: jobseeker sync
	 */
	public function jobseeker_sync () {
		$seeker    =   $_REQUEST['content'];

		switch ($_REQUEST['method']) {
			case 'read':
				$response	=	$this->get_jobseeker_data($seeker['id']);
				break;

			case 'update':
				$response	=	$this->update_jobseeker_data($seeker);
				break;

			// case 'insert':
			// 	$response	=	$this->create_jobseeker($seeker);
			// 	break;

			case 'delete':
				$response	=	$this->delete_jobseeker($seeker['id']);
				break;
			default:
				$response =	array('success' => false, 'msg' => __("Invalid method!", ET_DOMAIN) );
				break;
		}

		wp_send_json( $response );

	}

	public function current_jobseeker_update(){
		global $current_user;
		$args = $_POST['content'];

		$this->header();
		try {
			if ( !wp_check_password( $args['current_pass'], $current_user->user_pass, $current_user->ID ) )
				throw new Exception(__("Current password doesn't correct", ET_DOMAIN));

			$result = wp_update_user( $args );

			// return is wp_error, which contain message
			if (is_wp_error( $result )){
				throw new Exception( $result->get_error_message() );
			}
			// if update is failed, return msg
			else if (!$result) {
				throw new Exception(__("Cannot update information", ET_DOMAIN));
			}
			// if update is successfully, return msg
			$resp = array(
				'success' 	=> true,
				'msg' 		=> __('Information has been updated', ET_DOMAIN)
			);

			if (isset($args['user_pass'])){
				wp_logout();
				$resp['redirect'] = home_url();
			}
		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage()
			);
		}
		echo json_encode($resp);
		exit;
	}

	public function get_jobseeker_data ( $id ) {
		if(!1) { // can add permission check
			return array ('success' => false, 'msg' => __("You don't have permission to read this user info!", ET_DOMAIN));
		}

		$user	=	et_create_user_response ($id);
		if($user) {
			return array ('success' => true, 'data' => $user);
		} else {
			return array ('success' => false, 'msg' => __("User not exist!", ET_DOMAIN));
		}
	}

	/**
	 *
	*/
	public function update_jobseeker_data ($seeker) {
		global $user_ID;

		if(current_user_can('manage_options') || (isset($seeker['id']) && $user_ID == $seeker['id'] ) ) { // check edit user capabilities

			if(isset($seeker['et_accessible_companies']) &&  $seeker['et_accessible_companies'] == 'empty')
				$seeker['et_accessible_companies']	=	array();

			$user		=	JE_Job_Seeker::update ($seeker);
			$jobseeker  =	et_create_user_response($user);

			$jobseeker['profile_text']	= sprintf(__("Profile of %s", ET_DOMAIN), $jobseeker['display_name']);
			if($user) {
				return array ('success' => true, 'data' => $jobseeker, 'msg' => __("Update success!", ET_DOMAIN));
			} else {
				return array ('success' => false, 'msg' => __("Update fail!", ET_DOMAIN));
			}
		} else {
			return array ('success' => false, 'msg' => __("Permission denied!", ET_DOMAIN));
		}

	}

	public function upload_avatar(){
		//
		if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
		$uploadedfile = $_FILES['avatar'];

		try {
			if (empty($_FILES['avatar']) || empty($_POST['author']) )
				throw new Exception(__('Missing jobseeker data', ET_DOMAIN));

			$attach_id = et_process_file_upload($_FILES['avatar'], $_POST['author'], 0, array(
					'jpg|jpeg|jpe'	=> 'image/jpeg',
					'gif'			=> 'image/gif',
					'png'			=> 'image/png',
					'bmp'			=> 'image/bmp',
					'tif|tiff'		=> 'image/tiff'
				) );

			$avatar = et_get_attachment_data($attach_id);

			/**
			 * update new user logo
			*/
			if (isset($_POST['author'])){
				JE_Job_Seeker::update(array(
					'ID'		=> $_POST['author'],
					'et_avatar'	=> $avatar
				));
			}

			$resp = array(
				'success' 	=> true,
				'msg' 		=> '',
				'data' 		=> $avatar
				);
		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> __('Upload Failed! Maybe your image size is too large!')
				);
		}

		$this->header();
		echo json_encode($resp);
		exit;
	}

}
