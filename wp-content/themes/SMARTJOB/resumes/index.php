<?php

define('RESUME_PATH', dirname(__FILE__));

if ( class_exists( "ET_Options" ) ) :
	class JE_Resume_Options extends ET_Options {
		static $instance 	= null;
		static $opt_keys	= null;
		function __construct() {
			self::$opt_keys	=	array (
				'et_pending_resume'				=> 1,
				'et_resumes_priavcy'			=> 0,
				'et_send_mail_approve' 			=> 1,
				'et_send_mail_reject' 			=> 1,
				'et_send_mail_apply'			=> 1,
				'et_send_mail_contact'			=> 1,
				'et_free_view_resume'			=> 0,
				'et_resumes_status'				=> 0,
				'et_jobseeker_headline'			=> __("<h6>SMARTJOB RESUME</h6> <p>A strong resume help you stand out from the crowd.<br>Learn how to craft one that makes you look your best.</p>", ET_DOMAIN),	
				'et_jobseeker_api_linked'		=> '',
				'has_setup_resume'				=> 0
			) ;
			parent::__construct('et_resume_opts');
			$this->options_arr	=	wp_parse_args( $this->options_arr, self::$opt_keys );
		}

		public static function get_instance () {
			if (empty(self::$instance)){
				self::$instance = new JE_Resume_Options();
			}
			return self::$instance;
		}

		public function __set( $option_name, $option_value ){
			$return = '';
			if ( isset (self::$opt_keys[$option_name] ) )  {
				$return = $this->$option_name;
				$this->options_arr[$option_name] = stripslashes(trim($option_value));

				if($option_name == 'et_resumes_status') {
					flush_rewrite_rules(  );
					$this->has_setup_resume = 1;
				}
			}
			return $return;
		}

		/**
		 *  return current option values of this object
		 */
		public function get_all_current_options () {
			return wp_parse_args( $this->options_arr, self::$opt_keys );
		}

		public function get_resume_status () {
			return $this->et_resumes_status;
		}

		public function get_linked_api () {
			return $this->et_jobseeker_api_linked;
		}

	}
endif;

try {
	if (is_admin()){
		// including

		require_once dirname(__FILE__).'/admin.php';

		new JE_Resume_Admin();

	} else {
		if(class_exists("JE_Resume_Options")) :
			$options	=	JE_Resume_Options::get_instance();
			if ( $options->get_resume_status() ){
				// including front
				require_once RESUME_PATH . '/front.php';
				new JE_Resume_Front();
			}
		endif;
	}

} catch (Exception $e) {

}

//}

add_action ('init', 'je_register_post_type');
function je_register_post_type () {
	register_post_type( 'resume_plan' );
}

function et_get_resume_plans(){
	// $cache = get_transient('et_payment_plans');
	// if ( $cache == false ){
	// 	$cache = et_refresh_payment_plans();
	// }

	return et_refresh_resume_plans();
}

function et_refresh_resume_plans(){
	global $et_global;
	$prefix 		= $et_global['db_prefix'];

	$extra_fields = apply_filters('et_payment_plan_fields', array() );

	$plans 		= get_posts(array(
		'post_type' 	=> 'resume_plan',
		'numberposts' 	=> -1,
		'orderby' 		=> 'menu_order date'
		));

	$cache  	= array();
	foreach ($plans as $plan) {

		$new_plan = new stdClass;
		$new_plan->ID 			= $plan->ID;
		$new_plan->title 		= $plan->post_title;
		$new_plan->description 	= $plan->post_content;
		$new_plan->price 		=  get_post_meta($plan->ID, $prefix . 'price', true);
		$new_plan->duration 	= get_post_meta($plan->ID, $prefix . 'duration', true);
		//$new_plan->featured = get_post_meta($plan->ID, $prefix . 'featured', true);
		//$new_plan->quantity = get_post_meta($plan->ID, $prefix . 'quantity', true);

		foreach ($extra_fields as $key) {
			$new_plan->$key = get_post_meta($plan->ID, $prefix . $key, true);
		}

		$cache[$plan->ID] = (array)$new_plan;
	}

	if ( !is_array($cache) ){
		$cache = false;
	}

	// set the cache for 365 days
	delete_transient('et_payment_plans');
	set_transient('et_payment_plans', $cache, 365 * 60 * 60 * 24 );
	return $cache;
}

function je_update_view_resume_duration ($user_ID, $duration) {
	$current_time	=	get_user_meta( $user_ID, 'je_view_resume_time', true );
	if($current_time < time () ) {
		$current_time	= time ();
	}

	update_user_meta( $user_ID, 'je_view_resume_time', $current_time + $duration * 24 * 60 * 60 );
}

function je_get_resume_view_duration ($user_ID = 0) {
	if($user_ID == 0 ) {
		global $user_ID;
	}
	return get_user_meta( $user_ID, 'je_view_resume_time', true );
}

