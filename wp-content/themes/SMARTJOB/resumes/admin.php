<?php 
require_once RESUME_PATH . '/init.php';

class JE_Resume_Admin extends JE_Resume_Init{

	function __construct(){
		parent::__construct();

		// include admin menus
		new JE_Resume_Admin_Options();
		new JE_Tax_Base_Action ();
		new JE_Jobseeker_Available_Ajax ();
		new JE_Jobseeker_Position_Ajax();

		$this->add_action ('admin_enqueue_scripts', 'enqueue_wizard_js' );

		$this->add_ajax('resume_view_setup_payment' , 'setup_payment' , true , false);

	}

	function enqueue_wizard_js () {
		if(isset($_GET['page']) && ($_GET['page'] == 'et-wizard' ||  $_GET['page'] == 'et-resumes') ) {

			//$this->add_script('jobseeker-options', TEMPLATEURL . '/resumes/admin/js/options.js', array('jquery', 'et_underscore','et_backbone', 'js-editor') );

			// $this->add_script('jobseeker-tax', TEMPLATEURL . '/resumes/admin/js/model.js', array('jquery', 'et_underscore','et_backbone', 'job_engine') );
			$this->add_script('jobseeker-content', TEMPLATEURL . '/resumes/admin/js/options-content.js', array('jquery', 'et_underscore','et_backbone', 'job_engine') );

			wp_localize_script( 'jobseeker-content', 'et_options', array(
				'ajax_action' => 'et_update_option',
				'ajax_mail_action' => 'et_update_mail',
				'empty_title'		=> __("Oops, empty title! Double click to change", ET_DOMAIN)
			) );
		}
	}

	public function setup_payment () {
		header( 'HTTP/1.0 200 OK' );
		header( 'Content-type: application/json' );
		global $user_ID , $current_user;
		// remember to check isset or empty here
		$pages = get_posts( array('post_type' => 'page' , 'meta_key' => '_wp_page_template' ,'meta_value' => 'page-upgrade-account.php', 'numberposts' => 1 ) );
		$page = array_shift( $pages );

		$jobID			= $page->ID;
		$authorID		= $user_ID;
		$packageID		= isset($_POST['packageID']) ? $_POST['packageID'] : '';
		$paymentType	= isset($_POST['paymentType']) ? $_POST['paymentType'] : '';

		// package data invalid
		if( $packageID == '' || get_post_type ( $packageID ) != 'resume_plan' ) {
			$package_error	=	__("Invalid resume package ID!",ET_DOMAIN);
			$errors[] =	$package_error;
		}
		// input data error
		if( !empty( $errors )) {
			header( 'HTTP/1.0 200 OK' );
			header( 'Content-type: application/json' );
			
			$response	=	array( 
				'success'	=>  false,
				'errors'		=>	$errors
			);
			
			echo json_encode($response);
			
			exit;
		}
	////////////////////////////////////////////////
	////////////// process payment//////////////////
	////////////////////////////////////////////////
	
	$order_data		=	array (
		'payer'				=>	 $authorID,
		//'currency'			=>	 trim(ET_Payment::get_currency()),
		'total'				=>	 '',
		'status'			=>	 'pending',
		'payment'			=>	 $paymentType,
		'paid_date'			=>	 '',
		'payment_plan' 		=> 	$packageID , 
		'post_parent'		=> 	$jobID ,
		'order_name'		=> __("Upgrade account", ET_DOMAIN)
	) ;
	/**
	 * filter order data
	*/
	$order_data	=	apply_filters( 'je_payment_setup_order_data', $order_data );

	$plans		=	et_get_resume_plans();
	$plan		=	$plans[$packageID];
	$plan['ID']	=	$jobID;

	$role	=	$current_user->roles;
	$role	=	array_pop($role);
	if($role == 'jobseeker' ) {
		$location	= get_user_meta( $user_ID,  'et_location', true );
		$ship	=	array('street_address' =>  $location ? $location: __("No location", ET_DOMAIN)) ;
	} else {
		$company_location	=	et_get_user_field ($user_ID,'recent_job_location');
		$ship	=	array( 'street_address' => isset($company_location['full_location']) ? $company_location['full_location'] : '' );
	}
	// insert order into database
	$order		=	new ET_JobOrder( $order_data );

	$order->add_product ($plan);
	
	// save the plans to company's storage
	//$count = et_update_company_plans( $authorID, $packageID);
	
	
	$order_data				=	$order->generate_data_to_pay ();
	
	et_write_session ('order_id', $order_data['ID']);
	et_write_session ('job_id', $jobID);
	// $_SESSION['order_id']	=	$order_data['ID'];
	// $_SESSION['job_id']		=	$jobID;

	$arg	=	array (
		'return' => et_get_page_link('process-payment'), 
		'cancel' => et_get_page_link('process-payment')
	);
	/**
	 * process payment
	*/
	$paymentType	=	strtoupper( $paymentType );	
	/**
	 * factory create payment visitor
	*/
	$visitor		=	JE_Payment_Factory::createPaymentVisitor( $paymentType, $order );
	
	$visitor->set_settings ($arg);
	$nvp	=	$order->accept( $visitor );	
	if($nvp['ACK']) {
		$response	= array(
			'success'		=>	$nvp['ACK'],
			'data'			=>  $nvp,
			'paymentType'	=>	'paypal'
		);
	} else {
		$response	= array(
			'success'		=>	false,
			'paymentType'	=>	$paymentType,
			'msg'			=> __("Invalid payment gateway",ET_DOMAIN)
		);
	}

	$response	=	apply_filters('je_payment_setup', $response, $paymentType, $order );
	
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	
	echo json_encode($response);
		exit;

	}
}

/**
 * Class template for Admin menus
 */
abstract class JE_Resume_Admin_Menu extends ET_Base{

	abstract public function menu_view($args);
	abstract public function on_add_scripts();
	abstract public function on_add_styles();

	function __construct($menu_name, $args = array()){
		parent::__construct();
		$this->menu_name = $menu_name;
		$this->menu_args = wp_parse_args( $args, array(
			'menu_title' => 'Menu title',
			'page_title' => 'Menu title',
			'slug' 			=> 'menu-slug',
			'callback' 	=> array($this, 'menu_view')
			
		) );

		// actions
		$this->add_action('et_admin_menu', 'add_option_page');
		$this->add_action('et_admin_enqueue_scripts-' . $this->menu_args['slug'], 'on_add_scripts');
		$this->add_action('et_admin_enqueue_styles-' . $this->menu_args['slug'], 'on_add_styles');
	}

	public function add_option_page(){
		// default args
		et_register_menu_section($this->menu_name, $this->menu_args);
	}

	public function add_default_script () {
		$this->add_existed_script('jquery');
		$this->add_existed_script('et_underscore');
		$this->add_existed_script('et_backbone');		
		$this->add_existed_script('job_engine');

		// wp_enqueue_script('tiny_mce');
		// wp_enqueue_script('js-editor');

		wp_localize_script( 'job_engine', 'et_views', array(
			'loadingImg' 		=> '<img class="loading loading-wheel" src="'.TEMPLATEURL . '/img/loading.gif" alt="'.__('Loading...', ET_DOMAIN).'">',
			'loadingTxt' 		=> __('Loading...', ET_DOMAIN),
			'loadingFinish' 	=> '<span class="icon loading" data-icon="3"></span>'
		) );

		// // script for tinymce
		// wp_localize_script( 'js-editor', 'et_editor', array (
		// 	'jsURL' 			=> TEMPLATEURL . '/js/', 
		// 	'skin'				=> 'black',
		// 	'onchange_callback' => 'tiny_site_desc_callback',
		// 	'je_plugins'				=> apply_filters( 'je_editor_plugins', "spellchecker,paste,etHeading,etLink,autolink,inlinepopups,wordcount" ),
		// 	'theme_advanced_buttons1'	=> apply_filters( 'je_editor_theme_advanced_buttons1', "bold,|,italic,|,et_heading,|,etlink,|,numlist,|,spellchecker" ),
		// 	'theme_advanced_buttons2'	=> apply_filters( 'je_editor_theme_advanced_buttons2', "" ),
		// 	'theme_advanced_buttons3'	=> apply_filters( 'je_editor_theme_advanced_buttons3', "" ),
		// 	'theme_advanced_buttons3'	=> apply_filters( 'je_editor_theme_advanced_buttons4', "" )
		// ) );

	}
}

require_once RESUME_PATH . '/resumes.php';
require_once RESUME_PATH . '/job_seekers.php';
require_once RESUME_PATH . '/helper.php';
require_once RESUME_PATH . '/template.php';
require_once RESUME_PATH . '/widgets.php';
require_once RESUME_PATH . '/admin/options.php';