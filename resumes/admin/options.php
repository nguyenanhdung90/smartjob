<?php

class JE_Resume_Admin_Options extends JE_Resume_Admin_Menu{
	private $options;
	function __construct(){
		parent::__construct('resumes-options',  array(
			'menu_title'	=> __('Resumes', ET_DOMAIN),
			'page_title' 	=> __('RESUMES', ET_DOMAIN),
			'callback'   	=> array($this, 'menu_view'),
			'slug'			=> 'et-resumes',
			'page_subtitle'	=> __('Manage all options for resumes', ET_DOMAIN),
			'icon_class'	=> 'icon-resume' ,
			'pos'			=> 23

		));

		$this->add_ajax('et_update_option', 'on_update_option', true, false);
		$this->add_ajax('et_update_mail', 'on_update_mail', true, false);
		$this->add_ajax('et_reset_mail', 'on_reset_mail', true, false);

		$this->options	=	JE_Resume_Options::get_instance();
	}

	/**
	 * Adding scripts for option backend page
	 */
	public function on_add_scripts(){

		$this->add_default_script ();
		
		$this->add_script('jobseeker-options', TEMPLATEURL . '/resumes/admin/js/options.js', array('jquery', 'et_underscore','et_backbone') );
		$this->add_script('options-general', TEMPLATEURL . '/resumes/admin/js/options-general.js', array('jquery', 'et_underscore','et_backbone') );

		$this->add_existed_script('jquery-ui-sortable');
		$this->add_script('et_nestedsort', TEMPLATEURL . '/js/lib/jquery.nestedSortable.js', array('jquery', 'jquery-ui-sortable'));

		$this->add_script('jobseeker-options', TEMPLATEURL . '/resumes/admin/js/options.js', array('jquery', 'et_underscore' ,'et_backbone') );
		$this->add_script('jobseeker-content', TEMPLATEURL . '/resumes/admin/js/options-content.js', array('jquery', 'et_underscore','et_backbone', 'job_engine') );
		$this->add_script('jobseeker-mails', TEMPLATEURL . '/resumes/admin/js/options-mails.js', array('jquery', 'et_underscore' ,'et_backbone') );
		$this->add_script('jobseeker-payment', TEMPLATEURL . '/resumes/admin/js/options-payment.js', array('jquery', 'et_underscore' ,'et_backbone') );

		wp_localize_script( 
			'jobseeker-options', 
			'et_setting', 
			array(
				'del_parent_cat_msg' => __("You cannot delete a parent job category. Delete its sub-categories first.", ET_DOMAIN)
				)
		);

		
	}

	public function on_add_styles(){
		$this->add_existed_style( 'admin_styles' );
		//wp_enqueue_style( 'admin_styles' );
		$this->add_style( 'job-label', TEMPLATEURL . '/css/job-label.min.css' );
	}

	public function on_update_option(){
		$this->ajax_header();

		$args = $_POST['content'];
		if (isset($args['name']) && isset($args['value'])) {
			
			$this->options->$args['name']	=	$args['value'];
			$this->options->save ();

		}

		echo json_encode(array('success' => true, 'msg' => __('Options has been updated successfully!', ET_DOMAIN)));
		exit;
	}

	public function on_update_mail(){
		$this->ajax_header();

		$template = JE_Resumes_Mailing::get_instance();

		$args = $_POST['content'];
		if (isset($args['name']) && isset($args['value'])) {			
			//update_option( $args['name'], $args['value'] );
			$template->save_template($args['name'], $args['value']);
		}

		echo json_encode(array('success' => true, 'msg' => __('Mail has been updated successfully', ET_DOMAIN)));
		exit;
	}

	/**
	 * handle request reseting email template
	 */
	public function on_reset_mail(){
		$this->ajax_header();

		try {
			if (empty($_POST['content']['mail']))
				throw new Exception( __("Mail template can't be found1", ET_DOMAIN) );

			$name = $_POST['content']['mail'];
			$temps = JE_Resumes_Mailing::get_instance();

			
			$return = $temps->get_default_template($name);

			if ( $return == false ) 
				throw new Exception(__("Mail template can't be found2", ET_DOMAIN));
			else 
			{
				$temps->save_template($name,$return);
				echo json_encode( array(
					'success' 	=> true,
					'data'		=> array(
						'name' 		=> $name,
						'template' 	=> $return
					)
				) );
			}

		} catch (Exception $e) {
			echo json_encode(array(
				'success' => false,
				'msg' 		=> $e->getMessage()
			));
		}
		exit;
	}

	/**
	 * Displaying view for options page
	 */
	public function menu_view($args){
		// echo get_option('et_resume_status' , 0) ;
		$options = $this->options->get_all_current_options (); 
		?>
		<div class="et-main-header">
			<div class="title font-quicksand"><?php echo $args->menu_title ?></div>
			<div class="desc"><?php echo $args->page_subtitle ?></div>
		</div>
		<div class="et-main-content" id="resume_settings">
			<div class="et-main-left">
				<ul class="et-menu-content inner-menu">
					<li>
						<a href="#section/setting-general" class="section-link active">
							<span class="icon" data-icon="y"></span><?php _e("General",ET_DOMAIN);?>
						</a>
					</li>
					<li>
						<a href="#section/setting-content" class="section-link">
							<span class="icon" data-icon="l"></span><?php _e("Content",ET_DOMAIN);?>
						</a>
					</li>
					<li>
						<a href="#section/setting-payment" class="section-link">
							<span class="icon" data-icon="%"></span><?php _e("Payment",ET_DOMAIN);?>
						</a>
					</li>
					<li>
						<a href="#section/setting-mails" class="section-link">
							<span class="icon" data-icon="M"></span><?php _e("Mailing",ET_DOMAIN);?>
						</a>
					</li>
					
				</ul>
			</div>
			<div class="et-main-right">
				<?php include_once dirname(__FILE__) . "/options-general.php"; ?>
				<?php include_once dirname(__FILE__) . "/options-content.php"; ?>
				<?php include_once dirname(__FILE__) . "/options-mails.php"; ?>
				<?php include_once dirname(__FILE__) . "/options-payment.php"; ?>
			</div>
		</div>
		<?php
	}
}

function et_insert_resume_plan($args){

	if (empty($args['ID']) && empty($args['title'])) 
		return new WP_Error('pament_name_empty', __('Payment name cannot be empty', ET_DOMAIN));
	if(empty($args['price']) && !is_numeric($args['price']) )
		return new WP_Error('pament_name_empty', __('Payment price should be number format', ET_DOMAIN));
	if(empty($args['duration']) && !is_numeric($args['duration']))
		return new WP_Error('pament_name_empty', __('Payment duration should be number format', ET_DOMAIN));

	$backend_text	=	sprintf(__('%s to view jobseekers\' profiles in %s days',ET_DOMAIN), et_get_price_format($args['price']), $args['duration'] );

	$content = ($args['description'] != '' ) ? $args['description'] : sprintf(__("You will be able to view resume details in %s days.", ET_DOMAIN), $args['duration']);
	$post_id = wp_insert_post(array(
		'post_title' => $args['title'],
		'post_content' => $content,
		'post_type' => 'resume_plan',
		'post_status' => 'publish'
		), true);

	if ( is_wp_error($post_id) ) 
		return $post_id;

	$args['price']	=	number_format($args['price'], 2, '.', '');
	// insert additional information
	update_post_meta($post_id, 'et_price', (float)$args['price']);
	update_post_meta($post_id, 'et_duration', $args['duration']);
	// update_post_meta($post_id, 'et_featured', $args['featured']);
	// update_post_meta($post_id, 'et_quantity', $args['quantity']);
	et_refresh_resume_plans();
	return (object)array(
		'ID' 		=> $post_id,
		'title' 	=> $args['title'],
		'description' => $content,
		'price' 	=> $args['price'],
		'duration' 	=> $args['duration'],
		'backend_text' 	=> $backend_text
		// 'featured' 	=> $args['featured'],
		// 'quantity' 	=> $args['quantity']
	);
	
}

function et_update_resume_payment_plan ($args = array()){
	global $et_global;
	if (empty($args['post_title'])) 
		return new WP_Error('pament_name_empty', __('Payment name cannot be empty', ET_DOMAIN));

	if(empty($args['price']) && !is_numeric($args['price']) )
		return new WP_Error('pament_name_empty', __('Payment price should be number format', ET_DOMAIN));

	if(empty($args['duration']) && !is_numeric($args['duration']))
		return new WP_Error('pament_name_empty', __('Payment duration should be number format', ET_DOMAIN));

	if ( isset( $args['post_title'] ) ){
		$backend_text	=	sprintf(__('%s to view jobseekers\' profiles in %s days',ET_DOMAIN), et_get_price_format($args['price']), $args['duration'] );

		$content = ($args['description'] != '' ) ? $args['description'] : sprintf(__("You will be able to view resume details in %s days.", ET_DOMAIN), $args['duration']);
		$args['post_content']	=	$content;
	}

	$post_id = wp_update_post($args);

	if ( is_wp_error($post_id) ) 
		return $post_id;

	$prefix = $et_global['db_prefix'];

	if ( isset($args['price']) ){
		$args['price']	=	number_format($args['price'], 2, '.', '');
		update_post_meta($post_id, $prefix.'price', (float)$args['price']);
	}
	if ( isset($args['duration'])) {
		update_post_meta($post_id, $prefix.'duration', (int)$args['duration']);
	}
	

	// refresh cache
	et_refresh_resume_plans();

	return (object)array(
		'ID' 		=> $post_id,
		'title' 		=> $args['post_title'],
		'description' => $args['post_content'],
		'price' 	=> $args['price'],
		'duration' 	=> $args['duration']
	);
}

function et_sync_resume_plan(){
	$method = $_REQUEST['method'];
	try {
		switch ($method) {
			case 'add':
				$args = $_REQUEST['content'];				
				// insert
				$plan = et_insert_resume_plan($args);
				if ( is_wp_error($plan) )
					throw new Exception($plan->get_error_message());

				$html = "<li class='item'> 
							<span>{$plan->backend_text}</span>   
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
						'paymentPlan' => et_create_resume_plan_response($plan)
						)
					);

				// return 
				$plans = et_refresh_resume_plans();
				break;

			case 'delete': 
				$args = $_REQUEST['content'];
				$result = et_delete_resume_plan($args['id']);
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
				$plan = et_update_resume_payment_plan( $args );
				
				// create a response
				if ( is_wp_error ($plan) )
					throw new Exception($plan->get_error_message());

				$response = array(
					'success' => true,
					'code' => 200,
					'msg' => __('Payment Saved!', ET_DOMAIN),
					'data' => array(
						'paymentPlan' => et_create_resume_plan_response($plan)
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

add_action ('wp_ajax_et_sync_resume_plan', 'et_sync_resume_plan');


function et_create_resume_plan_response($plan){
	$plan = (array)$plan;
	return array(
		'id' 			=> $plan['ID'],
		'title' 		=> $plan['title'],
		'description' 	=> $plan['description'],
		'price' 		=> $plan['price'],
		'duration' 		=> $plan['duration'],
		//'featured' 		=> $plan['featured'],
		//'quantity' 		=> empty($plan['quantity']) ? "" : $plan['quantity'],
		'price_format' 	=> et_get_price_format($plan['price']),
		'backend_text' 	=> sprintf(__('%s to view jobseekers\' profiles in %s days',ET_DOMAIN), et_get_price_format($plan['price']), $plan['duration'] )
	);
}


// sort handle resume plan
function et_ajax_resume_plan_sorting(){
	parse_str( $_REQUEST['content']['order'] , $sort_order);

	// update new order
	global $wpdb;
	$sql = "UPDATE {$wpdb->posts} SET menu_order = CASE ID ";
	foreach ($sort_order['payment'] as $index => $id) {
		$sql .= " WHEN {$id} THEN {$index} ";
	}
	$sql .= " END WHERE ID IN (" . implode(',', $sort_order['payment']) . ")";

	$result = $wpdb->query( $sql );
	et_refresh_resume_plans();

	echo json_encode(array(
		'success' 	=> $result,
		'msg' 		=> __('Payment plans have been sorted', ET_DOMAIN)
	));
	exit;
}
add_action('wp_ajax_et_sort_resume_plan', 'et_ajax_resume_plan_sorting');



function et_delete_resume_plan($id){
	if ( current_user_can('manage_options') ){
		$result = wp_delete_post($id, true);
		et_refresh_resume_plans();
		if ( $result !== false ){
			return true;
		}else {
			return new WP_Error( 'not_successful', __('An error has occurred, please try again!', ET_DOMAIN) );
		}
		
	}else {
		return new WP_Error( 'no_permission', __('You have no permission to perform this action', ET_DOMAIN) );
	}
}
?>