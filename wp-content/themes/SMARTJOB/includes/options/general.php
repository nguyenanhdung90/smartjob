<?php
// update general setting
add_action ('wp_ajax_et-update-general-setting', 'et_update_general_settings');
function et_update_general_settings () {
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	
	$option_name	=	'et_'.$_POST['option_name'];
	$value			=	$_POST['new_value'];
	//$type 			=	$_POST['type'];
	

	/**
	 * update google captcha api setting if option name is private key or public key
	*/
	if( $_POST['option_name'] == 'private_key' || $_POST['option_name'] == 'public_key' ) {
		$google_captcha	=	ET_GoogleCaptcha::get_api();
		$google_captcha[$_POST['option_name']] = $value;
		ET_GoogleCaptcha::set_api( $google_captcha );
		wp_send_json( array ('success' => true ) );
	}

	/**
	 * update general settings
	*/

	$general_opt	=	new ET_GeneralOptions();
	if($general_opt->save_settings($option_name, $value)) {
		echo json_encode(array (
			'success'	=> true
		));
	} else {
		echo json_encode(array (
			'success'	=> false
		));
	}
	
	exit;
}
/**
 * ajax set theme language
 */
add_action ('wp_ajax_et-change-language', 'et_ajax_change_language');
function et_ajax_change_language () {
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	
	$response	=	et_change_language ($_POST['new_value']) ;

	echo json_encode(array (
		'success'	=> $response
	));
	exit;
}
/**
 * get theme layout list
 */
function et_theme_layouts () {
	$layouts	=	array (
		'content-sidebar'	=>	array (
				'label'		=> __("Two columns with right sidebar",ET_DOMAIN),
				'value'		=> 'content-sidebar', 
				'thumbnail'	=>	'cl-left'
			),
		'sidebar-content'	=>	array (
				'label'	=> __("Two columns with left sidebar",ET_DOMAIN),
				'value'	=> 'sidebar-content', 
				'thumbnail'	=>	'cl-right'
			),
		'content'		=>	array (
				'label'	=> __("One column",ET_DOMAIN),
				'value'	=>	'content', 
				'thumbnail'	=>	'cl-one'
			)
	);
	return apply_filters('et_theme_layouts', $layouts );
}

/**
 * Adds theme layout classes to the array of body classes.
 */
function et_layout_classes( $existing_classes ) {
	$ge_opt			=	new ET_GeneralOptions();	
	$current_layout	=	$ge_opt->get_layout();

	if ( in_array( $current_layout, array( 'content-sidebar', 'sidebar-content' ) ) )
		$classes = array( 'two-column' );
	else
		$classes = array( 'one-column' );

	if ( 'content-sidebar' == $current_layout )
		$classes[] = 'right-sidebar';
	elseif ( 'sidebar-content' == $current_layout )
		$classes[] = 'left-sidebar';
	else
		$classes[] = $current_layout;
	
	$classes = apply_filters( 'et_layout_classes', $classes, $current_layout );

	return array_merge( $existing_classes, $classes );
}
add_filter( 'body_class', 'et_layout_classes' );

/**
 * add ajax change layout
 */

function et_change_layout () {
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	
	$layout	=	$_POST['layout'];
	$general_opts	=	new ET_GeneralOptions();
	$response		=	$general_opts->set_layout ($layout);
	echo json_encode(array ('success' => $response ));	
	exit;
}
add_action ('wp_ajax_et-change-layout', 'et_change_layout');

/**
 * display follow us html
 * @param array $args :
 * -	container_class	:	 ul class
 * -	rss_feed		:	 include rss or not
 */
function et_follow_us ( $args	=	array () ) {
	/**
	 * add filter to control how follow us view
	*/
	$output	= apply_filters( 'et_follow_us', '' ) ;
	if($output != '' ) { 
		echo $output;
		return true;
	}
	
	$default	=	array (
		'container_class'	=>	'social-list',
		'rss_feed'			=> 	true
	);
	$args	=	wp_parse_args($args, $default);
	extract($args);
?>
	<div class="f-right f-left-all">
		<div class="follow font-quicksand"><?php _e("Follow Us",ET_DOMAIN);?></div>
			<ul class="<?php echo $container_class ?>">
			<?php 
				$general_opt	=	new ET_GeneralOptions();
				$fb_link	=	$general_opt->get_facebook_link();
				$twitter	=	$general_opt->get_twitter_account();
				$plus		=	$general_opt->get_google_plus() ;
				
				$rss		=	get_bloginfo('rss2_url');
				
			?>  
				<?php do_action('je_before_social_links'); ?>
				<?php if( $rss_feed ) { ?>
					<li><a href="<?php echo $rss ?>"><span class="icon-feed"></span></a></li>
				<?php }?>
				<?php
				if($fb_link != '') { ?>
					<li><a href="<?php echo $fb_link ?>" target="_blank"><span class="icon-facebook"></span></a></li>
				<?php }?>
				<?php if($twitter != '') {?>
					<li><a href="<?php echo $twitter?>" target="_blank"><span class="icon-twitter"></span></a></li>
				<?php } ?>
				<?php if($plus != '' ) {?>
					<li><a href="<?php echo $plus?>" target="_blank"><span class="icon-google"></span></a></li>
				<?php }?>
				<li><a href="https://www.linkedin.com/company/smartjob-jsc" target="_blank"><img src="<?php bloginfo('stylesheet_directory');?>/img/linked.png"></a></li>
				<li><a href="https://www.youtube.com/channel/UCAWge7PV9j77uvqocQElGoQ" target="_blank"><img src="<?php bloginfo('stylesheet_directory');?>/img/youtube_logo.png"></a></li>
				<?php do_action('je_after_social_links'); ?>
			</ul>
		</div>
	</div>
<?php 
}

function et_change_branding(){
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	$res	= array(
		'success'	=> false,
		'msg'		=> __('There is an error occurred', ET_DOMAIN ),
		'code'		=> 400,
	);
	
	// check fileID
	if(!isset($_POST['fileID']) || empty($_POST['fileID']) || !isset($_POST['imgType']) || empty($_POST['imgType']) ){
		$res['msg']	= __('Missing image ID', ET_DOMAIN );
	}
	else {
		$fileID		= $_POST["fileID"];
		$imgType	= $_POST['imgType'];
			
		// check ajax nonce
		if ( !check_ajax_referer( $imgType . '_et_uploader', '_ajax_nonce', false ) ){
			$res['msg']	= __('Security error!', ET_DOMAIN );
		}
		elseif(isset($_FILES[$fileID])){

			// handle file upload
			$attach_id	=	et_process_file_upload( $_FILES[$fileID], 0, 0, array(
								'jpg|jpeg|jpe'	=> 'image/jpeg',
								'gif'			=> 'image/gif',
								'png'			=> 'image/png',
								'bmp'			=> 'image/bmp',
								'tif|tiff'		=> 'image/tiff'
								)
							);

			if ( !is_wp_error($attach_id) ){

				try {
					$attach_data	= et_get_attachment_data($attach_id);
					$general_opts	= new ET_GeneralOptions();
					$setter			= 'set_' . $imgType;

					// save this setting to theme options
					$general_opts->$setter($attach_id);

					$res	= array(
						'success'	=> true,
						'msg'		=> __('Branding image has been uploaded successfully', ET_DOMAIN ),
						'data'		=> $attach_data
					);
				}
				catch (Exception $e) {
					$res['msg']	= __( 'Error when updating settings.', ET_DOMAIN );
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
	echo json_encode($res);
	exit;
}
add_action ('wp_ajax_et-change-branding', 'et_change_branding');

/**
 * 
 */
function et_ajax_verify_setup_process(){
	try {
		if ( !isset($_REQUEST['content']['step']) )
			throw new Exception("Error Processing Request", 400);
		$step 		= $_REQUEST['content']['step'];
		$method 	= $_REQUEST['method'];

		$data 		= et_get_wizard_step ();
		$step_number	=	0;
		foreach ($data as $key => $value) {
			if($value['section'] == $step) {
				$step_number	=	$key;
				break;
			}
		}

		$processing = et_verify_setup_process(true); // et_get_setup_process();
		//echo $step;
		
		if ( $method == 'next-step') {
			if ($processing != false && ($processing + 1) > $step_number )
				$resp = array(
					'success' 	=> true,
					'msg' 		=> '',
					'code' 		=> 200,
					'data' 		=> array(
						'finish' => $processing,
						'step' 		=> $step,
						'finishNumber' 	=> $processing,
						'stepNumber' 	=> $step_number
					)
				);
			else 
				throw new Exception(__('You have not completed all required fields yet.', ET_DOMAIN));
		}else if ( $processing == count ($data) ){
				$resp = array(
					'success' 	=> true,
					'msg' 		=> __('Yay! You have completed the setup wizard. Your website is ready now!', ET_DOMAIN),
					'code' 		=> 200,
					'data' 		=> array(
						'finish' 		=> $processing,
						'step' 			=> $step,
						'finishNumber' 	=> $data[$processing],
						'stepNumber' 			=> $data[$step]
					)
				);
		}
		else 
			throw new Exception(__('You have not completed all required fields yet.', ET_DOMAIN));

	} catch (Exception $e) {
		$resp = array(
			'success' 	=> false,
			'msg' 		=> $e->getMessage(),
			'code' 		=> 400,
			'data' 		=> array(
				'finish' => $processing,
				'step' 		=> $step
			)
		);
	}
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	echo json_encode($resp);
	exit;
	
}
add_action ('wp_ajax_et_verify_setup_process', 'et_ajax_verify_setup_process');

function et_wizard_nexstep_button ($section ) {
	$wizard_step	=	et_get_wizard_step ();
	
	$length	=	count($wizard_step);
	if($section == $length) {
		return ;
	}
	
	if( ($section + 1) == $length ) { // finish wizard
	?>
		<button class="et-button btn-button next-step" href="<?php echo $wizard_step[$section++]['section'] ?>" >
			<?php _e('Finish Wizard', ET_DOMAIN) ?>
		</button>
	<?php 
	} else {// go to next step ?>
		<button class="et-button btn-button next-step" href="<?php echo $wizard_step[$section++]['section'] ?>" >
			<?php _e('Go to the next step', ET_DOMAIN) ?>
		</button>
		<?php 
	}
}

function et_get_wizard_step () {
	return apply_filters ('je_wizard_step', array(
				0 => array('section' => 'branding', 'label' => __('Branding', ET_DOMAIN) , 'icon' => 'b'),
				1 => array('section' => 'content', 'label' => __('Content', ET_DOMAIN) , 'icon' => 'F'),
				2 => array('section' => 'payment', 'label' => __('Payment', ET_DOMAIN) , 'icon' => '%'),
				3 => array('section' => 'sample-data', 'label' => __('Insert Sample Data', ET_DOMAIN) , 'icon' => 'c') )
	) ;
}


function et_verify_setup_process($number = false){
	$opt 		= new ET_GeneralOptions();
	$step 		= et_get_wizard_step();
	$finish = 0;

	// branding
	if (!$opt->get_site_title() /*|| !$opt->get_site_desc() */)
		return $number == true ? $finish : $step[$finish]['section'];	
	$finish = 1;

	// content
	$job_categories = get_terms('job_category', array('hide_empty' => false));
	$job_types 		= get_terms('job_type', array('hide_empty' => false));
	if ( count($job_categories) == 0 || count($job_types) == 0 )
		return $number == true ? $finish : $step[$finish]['section'];	
	$finish = 2;

	// payment
	$gateways 	= et_get_enable_gateways();
	$has_gateways = false;
	foreach ((array)$gateways as $gateway) {
		if ( isset($gateway['active']) && $gateway['active'] == 1 ){
			$has_gateways = true;
			break;
		}
	}
	$plans = get_posts(array('post_type' => 'payment_plan'));
	if ( count($plans) > 0 && $has_gateways ){
		$finish = 3;
	} else {
		return $number == true ? $finish : $step[$finish]['section'];;
	}

	$finish	=	apply_filters( 'et_filter_setup_wizard_step', $finish );

	$return = $number == true ? $finish : $step[$finish]['section'];	
	
	return	$return ;

}

/**
 * Get process of setup wizard
 * @return name of the step that is currently uncompleted
 */
function et_get_setup_process($return_number = false){
	$opt 		= new ET_GeneralOptions();
	$process 	= $opt->get_setup_process();

	$step 	= false;
	$break 	= false;
	$num 	= 0;
	$data = array(
		1 => 'branding',
		2 => 'content',
		3 => 'payment',
		4 => 'sample-data'
	);

	foreach ((array)$process as $name => $steps) {
		foreach ((array)$steps as $option) {
			if ( $option == false ){
				$break = true;
				break;
			}
		}
		if ( $break ) break;
		$num++;
	}

	if ( $return_number )
		return $num;
	return $data[$num];
}

/*
 * ajax insert sample data : Jobtype, Job categories, Jobs Infomation
 */
add_action ('wp_ajax_et-insert-sample-data', 'et_insert_sample_data');
function et_insert_sample_data(){

	$response = array('success' => false, 'data' => "", 'updated_op' => get_option('option_sample_data'));
	if ( !$response['updated_op'] ) {
		update_option( 'option_sample_data', true);
		$response['updated_op'] = true;
		require_once TEMPLATEPATH . '/includes/importer.php';
		$import_xml = new JE_Import_XML();
		$import_xml->dispatch();
		
		$response['success'] = true;
	}
	header( "Content-Type: application/json" );
	echo json_encode( $response );
	exit;
}

/*
 * ajax delete sample data : Jobtype, Job categories, Jobs Infomation
 */
add_action ('wp_ajax_et-delete-sample-data', 'et_delete_sample_data');
function et_delete_sample_data(){
	$response = array('success' => false, 'data' => '', 'updated_op' => get_option('option_sample_data'));
	if ( $response['updated_op'] ) {
		delete_option( 'option_sample_data');
		$response['updated_op'] = false;
		require_once TEMPLATEPATH . '/includes/importer.php';
		$import_xml = new JE_Import_XML();
		$import_xml->depatch();
		$response['success'] = true;
	}
	header( "Content-Type: application/json" ); 
	echo json_encode( $response );
	exit;
}

/**
 * Get 
 * @return 
 */
function et_get_current_setup_process(){
	$step = et_get_setup_process(true);
	$data = array('branding','content','payment');
	return $data[$step];
}

/**
 * Mark finish the processing of setup wizard
 * @param $step the name of the step
 * @param $option the name of the option in step
 *
 */
function et_mark_setup_done($step, $option){
	global $et_global;
	$opt 		= new ET_GeneralOptions();	
	$process 	= get_option( $et_global['db_prefix'] . 'setup_process' );// $opt->get_setup_process();

	// initialize options 
	if ( empty($process) && !is_array($process) ){
		$process = array(
			'branding' => array( 
				'logo' => false,
				'icon' => false,
				'title' => false,
				'desc' 	=> false
			),
			'content' => array(
				'job_type' => false,
				'job_category' => false
			),
			'payment' => array(
				'currency' 	=> false,
				'paypal' 	=> false,
				'checkout' 	=> false,
				'cash' 		=> false,
				'plans' 	=> false
			)
		);
	}

	if ( isset($process[$step][$option]) ){
		$process[$step][$option] = true;
	}

	update_option( $et_global['db_prefix'] . 'setup_process', $process );
	
}

/**
 * Get and return customization values for 
 * @since 1.0
 */
function et_get_customization(){
	$general_opt	=	new ET_GeneralOptions();
	$style 			= 	$general_opt->get_customization();
	$style = wp_parse_args($style, array(
		'background' => '#ffffff',
		'header' 	=> '#4F4F4F',
		'heading' 	=> '#333333',
		'footer' 	=> '#F2F2F2',
		'text' 		=> '#446f9f',
		'action' 	=> '#e64b21',
		'pattern' 	=> '',
		'font-heading' 			=> '',
		'font-heading-weight' 	=> '',
		'font-heading-style' 	=> '',
		'font-heading-size' 	=> '14px',
		'font-text' 			=> '',
		'font-text-weight' 		=> '',
		'font-text-style' 		=> '',
		'font-text-size' 		=> '12px',
		));
	return $style;
}

/**
 * get and return layout
 * @since 1.0
 */
function et_get_layout(){
	$general_opt	=	new ET_GeneralOptions();
	return $general_opt->get_layout();
}

/**
 * 
 */
function et_set_layout($new_layout){
	$general_opt	=	new ET_GeneralOptions();
	return $general_opt->set_layout($new_layout);
}
