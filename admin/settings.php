<?php 
/**
 * Declare Section payment in admin panel
 * @since 1.0
 */
if(class_exists("JE_AdminSubMenu")) :
	class ET_MenuSettings extends JE_AdminSubMenu{

		/**
		 * Constructor for payment menu item
		 * @since 1.0
		 */
		function __construct(){
			parent::__construct( __('Settings', ET_DOMAIN), 
								__('SETTINGS', ET_DOMAIN), 
								__('Manage how your job board looks and feels.', ET_DOMAIN),
								'et-setting',
								'icon-gear',
								10 );
			$this->add_action('et_admin_localize_scripts', 'localize_script');
			/**
			 * ajax call back update limit free plan
			*/
			$this->add_action('wp_ajax_je-update-limit-free-plan' , 'update_limit_free_plan');

			new JE_JobType_Ajax ();
			new JE_JobCategory_Ajax ();
		}

		public function localize_script ($slug) {
			if($slug == 'et-setting') {
				wp_localize_script( 
					'et_setting', 
					'et_setting', 
					array(
						'payment_plan_error_msg' => __("Input is invalid. Please check again.", ET_DOMAIN),
						'del_parent_cat_msg' => __("You cannot delete a parent job category. Delete its sub-categories first.", ET_DOMAIN)
						)
					);
			}
		}

		function update_limit_free_plan () {
			if(current_user_can( 'manage_options' ) ) {
				set_theme_mod( 'je_limit_free_plan' , $_REQUEST['key'] );
				wp_send_json( array('success' => true ) );
			}
		}

		public function on_add_scripts(){
			parent::on_add_scripts();

			// $this->add_existed_script('tiny_mce');
			// $this->add_existed_script('js_editor');
			wp_enqueue_script( 'jquery' );
			$this->add_existed_script('et_underscore');
			$this->add_existed_script('et_backbone');
			$this->add_existed_script('job_engine');
			$this->add_existed_script('admin_scripts');

			$this->add_existed_script('jquery-ui-sortable');

			$this->add_existed_script('plupload-all');
			$this->add_existed_script('jquery-textarea-autosize');
			$this->add_existed_script('jquery_validator');

			//$this->add_script('et_jquery_ui_nestedsort', get_bloginfo('template_url') . '/js/lib/jquery-ui-for-sortable.js');
			$this->add_script('et_nestedsort', TEMPLATEURL . '/js/lib/jquery.nestedSortable.js' , array('jquery' , 'jquery-ui-sortable'));

			$this->add_script( 'et_setting', get_bloginfo('template_url') . '/js/admin/settings.js');
			$this->add_script( 'et_setting_jobs', get_bloginfo('template_url') . '/js/admin/content-job.js' );

			
		}

		public function on_add_styles(){
			parent::on_add_styles();
			$this->add_style('job_styles' , TEMPLATEURL . '/css/job-label.min.css');
			$this->add_style('job_slider' , TEMPLATEURL . '/css/ui-slider.css');
		}

		/**
		 * Refresh cache for payment plan
		 * @since 1.0
		 */
		static function refresh_payment_plans(){
			global $et_global;
			$prefix 		= $et_global['db_prefix'];

			$extra_fields = apply_filters('et_payment_plan_fields', array() );

			$plans 		= get_posts(array(
				'post_type' 	=> 'payment_plan',
				'numberposts' 	=> -1,
				'order' 		=> 'ASC'
				));

			$cache  	= array();
			foreach ($plans as $plan) {
				
				$new_plan = new stdClass;
				$new_plan->ID = $plan->ID;
				$new_plan->title = $plan->post_title;
				$new_plan->description = $plan->post_content;
				$new_plan->price =  get_post_meta($plan->ID, $prefix . 'price', true);
				$new_plan->duration = get_post_meta($plan->ID, $prefix . 'duration', true);
				$new_plan->featured = get_post_meta($plan->ID, $prefix . 'featured', true);
				$new_plan->quantity = get_post_meta($plan->ID, $prefix . 'quantity', true);

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

		/**
		 * Refresh cache for payment plan
		 * @since 1.0
		 */
		static function get_payment_plans(){
			$cache = get_transient('et_payment_plans');
			if ( $cache == false ){
				$cache = self::refresh_payment_plans();
			}

			return $cache;
		}

		public function get_header(){
			?>
			<div class="et-main-header">
				<div class="title font-quicksand"><?php _e("Settings", ET_DOMAIN); ?></div>
				<div class="desc"><?php echo $this->page_subtitle ?></div>
			</div>
			<?php
		}
		/**
		 * Render view for payment item 
		 * @since 1.0
		 */
		public function view(){
			$this->get_header();
			$sub_section = empty($_REQUEST['subSection']) ? '' : $_REQUEST['subSection'];
			?>
			<style>.et-main-main .desc .form .form-item span.notice {color : #E0040F;}</style>
			<div class="et-main-content">
				<div class="et-main-left">
					<ul class="et-menu-content inner-menu">
						<li>
							<a href="#setting-general" menu-data="general" class="<?php if ( $sub_section == '' || $sub_section == 'general') echo 'active'  ?>">
								<span class="icon" data-icon="y"></span><?php _e("General",ET_DOMAIN);?>
							</a>
						</li>
						<li>
							<a href="#customize-branding" menu-data="branding" class="<?php if ($sub_section == 'branding') echo 'active' ?>">
								<span class="icon" data-icon="b"></span><?php _e("Branding",ET_DOMAIN);?>
							</a>
						</li>
						<li>
							<a href="#setting-job" menu-data="job" class="<?php if ($sub_section == 'job') echo 'active' ?>">
								<span class="icon" data-icon="l"></span><?php _e("Jobs",ET_DOMAIN);?>
							</a>
						</li>

						<li>
							<a href="#setting-social" menu-data="job" class="<?php if ($sub_section == 'social') echo 'active' ?>">
								<span class="icon" data-icon="B"></span><?php _e("Social",ET_DOMAIN);?>
							</a>
						</li>

						<li>
							<a href="#setting-payment"  menu-data="payment" class="<?php if ($sub_section == 'payment') echo 'active' ?>">
								<span class="icon" data-icon="%"></span><?php _e("Payment",ET_DOMAIN);?>
							</a>
						</li>
						<li>
							<a href="#setting-mail-template"  menu-data="mail-template" class="<?php if ($sub_section == 'mail-template') echo 'active' ?>">
								<span class="icon" data-icon="M"></span><?php _e("Mailing",ET_DOMAIN);?>
							</a>
						</li>
						<li>
							<a href="#setting-language" menu-data="language" class="<?php if ($sub_section == 'language') echo 'active' ?>">
								<span class="icon" data-icon="G"></span><?php _e("Language",ET_DOMAIN);?>
							</a>
						</li>
						
						<li>
							<a href="#setting-update" menu-data="update" class="<?php if ($sub_section == 'update') echo 'active' ?>">
								<span class="icon" data-icon="~"></span><?php _e("Update",ET_DOMAIN);?>
							</a>
						</li>
					</ul>
				</div>
				<div class="settings-content">
					<?php require_once 'setting-general.php';?>
					<?php require_once 'setting-language.php';?>
					<?php require_once 'setting-job.php';?>
					<?php require_once 'setting-social.php';?>
					<?php require_once 'setting-payment.php';?>
					<?php require_once 'setting-mail-template.php' ?>
					<?php require_once 'setting-branding.php'  ?> 
					<?php require_once 'setting-update.php'  ?> 
				</div>
			</div>
			<?php
			echo $this->get_footer();
		}
	}
endif;
/**
 * Register menu setting
*/
// ====================================
// Function about payments
// ====================================


/**
 * return the payment plans in database
 * @since 1.0
 */
function et_refresh_payment_plans(){
	global $et_global;
	$prefix 		= $et_global['db_prefix'];

	$extra_fields = apply_filters('et_payment_plan_fields', array() );

	$plans 		= get_posts(array(
		'post_type' 	=> 'payment_plan',
		'numberposts' 	=> -1,
		'orderby' 		=> 'menu_order date'
		));

	$cache  	= array();
	foreach ($plans as $plan) {
		
		$new_plan = new stdClass;
		$new_plan->ID = $plan->ID;
		$new_plan->title = $plan->post_title;
		$new_plan->description = $plan->post_content;
		$new_plan->price =  get_post_meta($plan->ID, $prefix . 'price', true);
		$new_plan->duration = get_post_meta($plan->ID, $prefix . 'duration', true);
		$new_plan->featured = get_post_meta($plan->ID, $prefix . 'featured', true);
		$new_plan->quantity = get_post_meta($plan->ID, $prefix . 'quantity', true);

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

function et_query_payment_plan_order($orderby){
	return 'menu_order ASC';
}

function et_create_payment_plan_response($plan){
	$plan = (array)$plan;
	return array(
		'id' 			=> $plan['ID'],
		'title' 		=> $plan['title'],
		'description' 	=> $plan['description'],
		'price' 		=> $plan['price'],
		'duration' 		=> $plan['duration'],
		'featured' 		=> $plan['featured'],
		'quantity' 		=> empty($plan['quantity']) ? "" : $plan['quantity'],
		'price_format' 	=> et_get_price_format($plan['price']),
		'backend_text' 	=> sprintf(__('%s for %s days',ET_DOMAIN), et_get_price_format($plan['price']), $plan['duration'] )
		);
}

/**
 * Perform a refresh payment plans cached
 * @since 1.0
 */
function et_get_payment_plans(){
	// $cache = get_transient('et_payment_plans');
	// if ( $cache == false ){
	// 	$cache = et_refresh_payment_plans();
	// }

	return et_refresh_payment_plans();
}



/** ================================================
 *  Define ajax call for change payment request
 * 	================================================ */
/**
 * Handle ajax request for payment plan
 * @since 1.0
 */
function et_ajax_sync_payment_plan(){
	$method = $_REQUEST['method'];
	try {
		switch ($method) {
			case 'add':
				$args = $_REQUEST['content'];
				// insert
				$plan = et_insert_payment_plan($args['title'],number_format($args['price'], 2, '.', ''),$args['duration'],$args['featured'], $args['quantity']);

				if ( is_wp_error($plan) )
					throw new Exception($plan->get_error_message());

				$html = "<li class='item'> 
							<span>{$args['title']}</span> \${$args['price']} for {$args['duration']} days  
							<div class='actions'> 
								<a href='#' title='" . __('Edit', ET_DOMAIN) . "' class='icon act-edit' rel='{$plan->ID}' data-icon='y'></a> 
								<a href='#' title='" . __('Delete', ET_DOMAIN) . "' class='icon act-del' rel='{$plan->ID}' data-icon='D'></a> 
							</div> 
						</li>";

				$response = array(
					'success' => true,
					'code' => 200,
					'msg' => __('Payment Added!', ET_DOMAIN),
					'data' => array(
						'paymentPlan' => et_create_payment_plan_response($plan)
						)
					);

				// return 
				$plans = et_refresh_payment_plans();
				break;

			case 'delete': 
				$args = $_REQUEST['content'];
				$result = et_delete_payment_plan($args['id']);

				if ( is_wp_error($result) ) throw new Exception(__("Can't delete payment plan", ET_DOMAIN));

				$response = array(
					'success' => true,
					'code' => 200,
					'msg' => __('Payment plan has been deleted', ET_DOMAIN),
					'data' => array()
				);
				break;
			case 'update':
				$args = $_REQUEST['content'];
				$args['ID'] = $args['id'];
				if ( isset($args['title']) ){
					$args['post_title'] = $args['title'];
					unset($args['title']);
				}

				// update payment plan to database
				$args['price']	=	number_format($args['price'], 2, '.', '') ;
				$plan = et_update_payment_plan( $args );

				// create a response
				$response = array(
					'success' => true,
					'code' => 200,
					'msg' => __('Payment Saved!', ET_DOMAIN),
					'data' => array(
						'paymentPlan' => et_create_payment_plan_response($plan)
						)
					);
				et_refresh_payment_plans();
				break;
			
			default:
				throw new Exception( __('An error has been occurred', ET_DOMAIN) );
				break;
		}
	} catch (Exception $e) {
		$response = array(
			'success' => false,
			'code' => 400,
			'msg' => $e->getMessage(),
			'data' => array()
		);
	}

	header( 'HTTP/1.0 200 OK' );
	header( "Content-Type: application/json" ); 
	echo json_encode( $response );
	exit;
}

add_action('wp_ajax_et_sync_paymentplan', 'et_ajax_sync_payment_plan');

/**
 * Delete a existed payment plan from database
 *
 * @since 1.0
 */
function et_delete_payment_plan($id){
	if ( current_user_can('manage_options') ){
		$result = wp_delete_post($id, true);
		et_refresh_payment_plans();
		if ( $result !== false ){
			return true;
		}else {
			return new WP_Error( 'not_successful', __('An error has occurred, please try again!', ET_DOMAIN) );
		}
		
	}else {
		return new WP_Error( 'no_permission', __('You have no permission to perform this action', ET_DOMAIN) );
	}
}

/**
 * Update or add a payment plan into database
 * @since 1.0
 */
function et_update_payment_plan($args = array()){
	global $et_global;

	if (empty($args['ID']) && empty($args['title'])) 
		return new WP_Error('pament_name_empty', __('Payment name cannot be empty', ET_DOMAIN));

	if ( isset( $args['post_title'] ) ){
		// setup the post content
		$featured_text = $args['featured'] ? 'featured' : 'normal';
		$content_plural = sprintf( __('Your job will be displayed as %s, on top of other jobs for %d days.', ET_DOMAIN), $featured_text, $args['duration'] );
		$content_single = sprintf( __('Your job will be displayed as %s, on top of other jobs for %d day.', ET_DOMAIN), $featured_text, $args['duration'] );
		$args['post_content'] = $args['duration'] == 1 ? $content_single : $content_plural;
	}

	$post_id = wp_update_post($args);

	if ( is_wp_error($post_id) ) 
		return $post_id;

	$prefix = $et_global['db_prefix'];

	if ( isset($args['price']) ){
		update_post_meta($post_id, $prefix.'price', (float)$args['price']);
	}
	if ( isset($args['duration'])) {
		update_post_meta($post_id, $prefix.'duration', (int)$args['duration']);
	}
	if ( isset($args['featured'])) {
		update_post_meta($post_id, $prefix.'featured', (int)$args['featured']);
	}
	if ( isset($args['quantity'])) {
		update_post_meta($post_id, $prefix.'quantity', (int)$args['quantity']);
	}


	// refresh cache
	et_refresh_payment_plans();

	return (object)array(
			'ID' 		=> $post_id,
			'title' 		=> $args['post_title'],
			'description' => $args['post_content'],
			'price' 	=> $args['price'],
			'duration' 	=> $args['duration'],
			'featured' 	=> $args['featured'],
			'quantity' 	=> $args['quantity'],
		);
}

/**
 * insert new payment plan
 */
function et_insert_payment_plan($name, $price, $duration = 30, $featured = false, $quantity = 1){
	$args = array(
		'payment_name' 		=> $name,
		'payment_price' 	=> $price,
		'payment_duration' 	=> $duration,
		'payment_featured' 	=> $featured == true ? 1 : 0,
		'payment_quantity' 	=> $quantity
		);

	// validate
	if ( $args['payment_name'] == '' )
		return new WP_Error('pament_name_empty', __('Payment name cannot be empty', ET_DOMAIN));

	// setup the post content
	$featured_text = $args['payment_featured'] ? __('featured', ET_DOMAIN) : __('normal', ET_DOMAIN);
	if ($args['payment_featured']){
		$content_plural = sprintf( __('Your job will be displayed as %s, on top of other jobs for %d days.', ET_DOMAIN), $featured_text, $args['payment_duration'] );
		$content_single = sprintf( __('Your job will be displayed as %s, on top of other jobs for %d day.', ET_DOMAIN), $featured_text, $args['payment_duration'] );
	} else {
		$content_plural = sprintf( __('Your job will be displayed as %s for %d days.', ET_DOMAIN), $featured_text, $args['payment_duration'] );
		$content_single = sprintf( __('Your job will be displayed as %s for %d day.', ET_DOMAIN), $featured_text, $args['payment_duration'] );
	}

	$args['payment_content'] = $args['payment_duration'] == 1 ? $content_single : $content_plural;

	// insert to database 
	$post_id = wp_insert_post(array(
		'post_title' => $args['payment_name'],
		'post_content' => $args['payment_content'],
		'post_type' => 'payment_plan',
		'post_status' => 'publish'
		), true);

	if ( is_wp_error($post_id) ) 
		return $post_id;

	// insert additional information
	update_post_meta($post_id, 'et_price', (float)$args['payment_price']);
	update_post_meta($post_id, 'et_duration', $args['payment_duration']);
	update_post_meta($post_id, 'et_featured', $args['payment_featured']);
	update_post_meta($post_id, 'et_quantity', $args['payment_quantity']);

	// refresh cache
	et_refresh_payment_plans();

	return (object)array(
			'ID' 		=> $post_id,
			'title' 		=> $args['payment_name'],
			'description' => $args['payment_content'],
			'price' 	=> $args['payment_price'],
			'duration' 	=> $args['payment_duration'],
			'featured' 	=> $args['payment_featured'],
			'quantity' 	=> $args['payment_quantity']
		);
}
/*
 * display enable/disable payment gateway button
 */

function et_display_enable_disable_button ( $option, $label, $option_type = "payment" ) {
	$enable	=	false;
	$job_option	=	new ET_JobOptions();
	switch ($option_type) {
		case 'payment':
			$payment_gateways	=	et_get_enable_gateways();
			if( !is_array($payment_gateways)) {
				$payment_gateways	=	array ();
			}
			
			if( isset ($payment_gateways[$option]) && $payment_gateways[$option]['active'] != -1 ) {
				$enable	=	true;
			}
		break;
		case 'payment_test_mode':			
			if( et_get_payment_test_mode () ) $enable	=	true ;
			
		break;

		case 'pending_job':					
			if( $job_option->use_pending() ) $enable	=	true ;
		break;

		case 'fb_login':					
			if( $job_option->get_fb_login() ) $enable	=	true ;
		break;
		
		case 'tw_login':					
			if( $job_option->get_tw_login() ) $enable	=	true ;
		break;

		
		
		case 'use_captcha':			
			if( $job_option->use_captcha() ) $enable	=	true ;
		break;

		case 'pending_job_edit':
			if( $job_option->use_pending_job_edit() ) $enable	=	true ;

		break;
	}
	
	if( $enable ) {
	?>
		<a href="#" rel="<?php echo $option?>" title="<?php echo $label ?>" class="deactive">
			<span><?php _e("Disable", ET_DOMAIN);?></span>
		</a>
		<a href="#" rel="<?php echo $option?>" title="<?php echo $label ?>" class="active selected">
			<span><?php _e("Enable", ET_DOMAIN);?></span>
		</a>
	<?php 
	} else { // disable
	?>
		<a href="#" rel="<?php echo $option?>" title="<?php echo $label ?>" class="deactive selected">
			<span><?php _e("Disable", ET_DOMAIN);?></span>
		</a>
		<a href="#" rel="<?php echo $option?>" title="<?php echo $label ?>" class="active">
			<span><?php _e("Enable", ET_DOMAIN);?></span>
		</a>
	<?php 
	}
}
/*
 * ajax disable payment gateway
 */
add_action('wp_ajax_et-disable-option', 'et_disable_option');
function et_disable_option () {
	header( "Content-Type: application/json" ); 
	$response	=	array (
		'success'	=> 	true,
		'msg'		=>	'disable'
	);
	
	$gateway	=	strtoupper($_POST['gateway']);
	$job_options	=	new ET_JobOptions();

	switch ($gateway) {
		case 'PAYMENT_TEST_MODE' :
			et_set_payment_test_mode (0);
		break;
		case 'PENDING_JOB' :
			
			$job_options->set_use_pending(0);
		break;

		case 'PENDING_JOB_EDIT':
			$job_options->set_use_pending_job_edit(0);
			break;

		case 'FB_LOGIN':
			$job_options->set_fb_login( 0);
			break;

		case 'TW_LOGIN':
			$job_options->set_tw_login( 0);
			break;

		case 'USE_CAPTCHA' :			
			$job_options->set_use_captcha(0);			
			break;
		case 'MAIL_APPLY':
		case 'MAIL_REMIND':
		case 'MAIL_APPROVE':
		case 'MAIL_ARCHIVE':
		case 'MAIL_REJECT':
		case 'CASH_NOTICE' : 
			$key 		= substr($_POST['gateway'], 5);
			$return = et_set_auto_email($key);
			$response['msg'] = $key . ' is disabled ';
		break;
		case 'PAYPAL':
		case '2CHECKOUT':
		case 'GOOGLE_CHECKOUT' :
		case 'CASH' :
		default : 
			$return		=	et_disable_gateway($_POST['gateway']);
			if( !$return ) {
				$response['success']	=	false;
				$response['msg']		=	__('Please fill in required field.', ET_DOMAIN);
			}
		break;
	}
	 
	echo json_encode( $response );
	exit;
}

/*
 * ajax enable payment gateway
 */
add_action('wp_ajax_et-enable-option', 'et_enable_option');
function et_enable_option () {
	
	header( "Content-Type: application/json" ); 
	$response	=	array (
		'success'	=> 	true,
		'msg'		=>	'enable'
	);
	
	$gateway	=	strtoupper($_POST['gateway']);
	$job_options	=	new ET_JobOptions();

	switch ($gateway) {
		case 'PAYMENT_TEST_MODE' :
			et_set_payment_test_mode (1);
		break;
		case 'PENDING_JOB' :			
			$job_options->set_use_pending(1);			
			break;

		// job will be pendding after employser edit job.
		case 'PENDING_JOB_EDIT':
			$job_options->set_use_pending_job_edit(1);
			break;

		case 'FB_LOGIN':
			$job_options->set_fb_login(1);
			break;

		case 'TW_LOGIN':
			$job_options->set_tw_login(1);
			break;

		case 'USE_CAPTCHA' :			
			$job_options->set_use_captcha(1);
			break;

		case 'MAIL_APPLY':
		case 'MAIL_REMIND':
		case 'MAIL_APPROVE':
		case 'MAIL_ARCHIVE':
		case 'MAIL_REJECT':
		case 'MAIL_CASH_NOTICE' : 
			$key 		= substr($_POST['gateway'], 5);
			$return = et_set_auto_email($key);
			$response['msg'] = $key . ' is enabled ';
		break;
		case 'PAYPAL':
		case '2CHECKOUT':
		case 'GOOGLE_CHECKOUT' :
		case 'CASH' :
		default:
			$return		=	et_enable_gateway($_POST['gateway']);
			if( !$return ) {
				$response['success']	=	false;
				$response['msg']		=	__('Please fill in required field.', ET_DOMAIN);
			}
		break;
	}
	 
	echo json_encode( $response );
	exit;
}

/**
 * get auto notification email by giving email type
 */
function et_get_auto_email($type = ''){
	$options 	= get_option('et_auto_email', array('apply' => 1, 'approve' => 1, 'remind' => 1, 'archive' => 1, 'reject' => 1, 'cash_notice' => 1));
	if(!isset($options['cash_notice'])) $options['cash_notice']	=	1;
	return $options[$type];
}

/**
 * get auto notification emails
 */

function et_get_auto_emails(){
	$options 	= get_option('et_auto_email', array('apply' => 1, 'approve' => 1, 'remind' => 1, 'archive' => 1, 'reject' => 1, 'cash_notice' => 1));
	if(!isset($options['cash_notice'])) $options['cash_notice']	=	1;
	return $options;
}

/**
 * set an automatically sending feature for a email template
 */
function et_set_auto_email($type, $value = false){
	$options 	= get_option('et_auto_email', array('apply' => 1, 'approve' => 1, 'remind' => 1, 'archive' => 1, 'reject' => 1, 'cash_notice' => 1));
	$key 		= $type;
	if ($value !== false)
		$options[$key] = $value;
	else {
		if (isset($options[$key]) && $options[$key] == 1)
			$options[$key] = 0;
		else 
			$options[$key] = 1;
	}
	return update_option( 'et_auto_email', $options );
}

add_action ('wp_ajax_et-update-mail-template', 'et_update_mail_template');
function et_update_mail_template () {
	header( "Content-Type: application/json" ); 

	$mail 	=	isset($_POST['type']) ? $_POST['type'] : '';
	$value 	=	isset($_POST['data']) ? $_POST['data'] : '';

	$mail_opt 	=	new ET_JobEngineMailTemplate () ;
	$response 	=	array ('success' => false);
	if( $mail_opt->update_mail_template ($mail, stripcslashes ($value) ) ){
		$response['success'] =	true;
	}	
	echo json_encode($response);
	exit;
 }
add_action ('wp_ajax_et-set-default-mail-template', 'et_set_default_mail_template');
function et_set_default_mail_template () {
	header( "Content-Type: application/json" ); 

	$mail 	=	isset($_POST['type']) ? $_POST['type'] : '';

	$mail_opt 	=	new ET_JobEngineMailTemplate () ;
	$response 	=	array ('success' => false);
	$return 	=	 $mail_opt->reset_mail_template ('et_'.$mail);

	if( $return != 1 ){
		$response['success'] =	true;
		$response['msg'] 	=	$return;
	}	
	echo json_encode($response);
	exit;
 }

add_action ('wp_ajax_et-add-new-currency', 'et_add_new_currency');
function et_add_new_currency () {
	header( "Content-Type: application/json" ); 
	$opt	=	new ET_JobOptions();
	
	$text	=	$_POST['text'];
	$code	=	$_POST['code'];
	$icon	=	$_POST['icon'];
	$align	=	$_POST['align'];
	
	$response 	=	array('success' => false);
	
	if( $text != '' && $code != '' && $icon != '' && ($align =='left' || $align=='right')) {
		$new_cur	=	array (
			'alt'	=>	$text,
			'label' =>	$code,
			'icon'	=>	$icon,
			'align'	=>	$align,
			'code'	=>	$code
		);
		$opt->add_currency ($code, $new_cur);
		ET_Payment::set_currency($code);
		$response['success']	=	true;
	} 
	
	echo json_encode($response);
	exit;
}
function data_icon ( $data , $type = 'text' ) {
    if( $data == '' )
        echo '!';
    else {
    	if($type == 'text') echo 3;
    	if($type == 'link') {
    		$validator	=	new ET_Validator();
    		if($validator->validate('link', $data))  echo 3;
    		else echo '!';
    	}
    	if($type == 'email') {
    		$validator	=	new ET_Validator();
    		if($validator->validate('email', $data))  echo 3;
    		else echo '!';
    	}
    }
}

/**
 * Handle when people sort categories by drag and drop
 */
function et_sort_job_category(){
	$resp = array();
	if (!$_POST['content']['order']) 
		return false;

	// update parent
	if (isset($_POST['content']['id']) && isset($_POST['content']['parent'])){
		$parent = $_POST['content']['parent'] ? $_POST['content']['parent'] : 0;
		wp_update_term($_POST['content']['id'], 'job_category', array('parent' => $parent));
	}

	// update order
	$order = $_POST['content']['order'];
	update_option('et_category_order', $order);	

	et_refresh_job_categories();

	$resp = array(
		'success' 	=> true,
		'msg' 		=> ''
	);

	header( "Content-Type: application/json" );
	echo json_encode($resp);
	exit;
}
//add_action ('wp_ajax_et_sort_job_category', 'et_sort_job_category');


function et_sort_job_types(){
	$resp = array();
	if (!$_POST['content']['order']) 
		return false;

	// update order
	$order = $_POST['content']['order'];
	update_option('et_jobtype_order', $order);	

	et_refresh_job_types();

	$resp = array(
		'success' 	=> true,
		'msg' 		=> ''
	);

	header( "Content-Type: application/json" );
	echo json_encode($resp);
	exit;
}
//add_action ('wp_ajax_et_sort_job_types', 'et_sort_job_types');
add_action('wp_ajax_et-update-nofication-mail', 'et_update_job_notification_mail');
function et_update_job_notification_mail () {
	try {
		if (!isset($_POST['key'])) throw new Exception(__('Key is invalid', ET_DOMAIN));
			update_option('et_job_notification_mail', trim(stripslashes($_POST['key'])));
			$resp = array(
				'success' 	=> true,
				'msg' 		=> ''
			);
	} catch (Exception $e) {
		$resp = array(
			'success' 	=> false,
			'msg' 		=> $e->getMessage()
		);
	}
	wp_send_json($resp);
}

/**
* @since version 2.9.3
* save value input of social
*/
add_action('wp_ajax_et-social-save','et_social_save');
function et_social_save(){
	if( !current_user_can('manage_options') )
		wp_send_json(array('success' => false, 'msg' => __('Action fail',ET_DOMAIN)));

	$name = $_REQUEST['name'];
	$value = $_REQUEST['value'];
	$res = array('success'=> false,'msg'=> __('save not successful'));
	$result = false;
	switch ($name) {
		case 'fb_app_id':
			$result = ET_FaceAuth::save_app_id($value);
			break;
		case 'twitter_key':
			$result = ET_TwitterAuth::save_twitter_key($value);
			break;
		case 'twitter_secret':
			$result = ET_TwitterAuth::save_twitter_secret($value);
		
		default:
			# code...
			break;
	}
	if($result)
		$res = array('success'=> true,'msg'=> __('save successful'));
	wp_send_json($res);

}
?>