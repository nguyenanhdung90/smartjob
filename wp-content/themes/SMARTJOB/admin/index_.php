<?php

/**
 * Admin menu in Job Engine
 */
if ( class_exists("ET_EngineAdminMenu") ):
	class JE_AdminMenu extends ET_EngineAdminMenu{
		public function __construct(){
			parent::__construct();
			$this->add_action('admin_init', 'deregister_scripts');
		}

		public function deregister_scripts(){
			global $wp_scripts;
			$common = array('admin-bar', 'common');
			if ( isset($_GET['page']) && strpos($_GET['page'], 'et') !== false ){
				foreach ($wp_scripts->queue as $key) {
					if (!in_array($key, $common))
						wp_dequeue_script( $key );
				}
			}
		}
	}
endif;

/**
 * one section for all
 */
if( class_exists("ET_EngineAdminSection")) :
	class JE_AdminSubMenu extends ET_EngineAdminSection{

		function __construct($menu_title, $page_title, $page_subtitle, $slug, $icon_class = 'icon-gear', $pos = 5){
			parent::__construct($menu_title, $page_title, $page_subtitle, $slug, $icon_class, $pos);
		}

		public function on_add_styles(){
			$this->add_existed_style( 'admin_styles' );
		}

		public function on_add_scripts(){
			//wp_deregister_script( 'jquery' ); // deregisters the default WordPress jQuery & enqueue the new one later
			//wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
			$this->add_existed_script('jquery');
			$this->add_existed_script('et_underscore');
			$this->add_existed_script('et_backbone');
		}

		public function view(){

		}
	}
endif;
/**
 * Register scripts use in Job Engine
 */
add_action('et_admin_enqueue_scripts', 'je_localize_scripts');
function je_localize_scripts(){
	// register some default scripts
	wp_register_script( 'job_engine', get_bloginfo('template_url') . '/js/job_engine.js', array('jquery','et_underscore', 'et_backbone') );
	wp_register_script( 'admin_scripts', get_bloginfo('template_url') . '/js/admin/back.js', array('jquery', 'et_underscore', 'et_backbone',  'job_engine'));
	// wp_register_script( 'tiny_mce', TEMPLATEURL . '/js/lib/tiny_mce/tiny_mce.js', array('jquery') );
	// wp_register_script( 'js_editor', TEMPLATEURL . '/js/editor.js', array('jquery') );
	wp_register_script( 'et_nestedSortable', TEMPLATEURL . '/js/lib/jquery.nestedSortable.js', array('jquery') );

	// register 
	wp_localize_script( 'job_engine', 'et_globals', array(
		'ajaxURL' 			=> admin_url('admin-ajax.php'),
		'routerRoot' 		=> add_query_arg('page', 'engine-settings', admin_url('admin.php')),
		'tooltip' 			=> array(
			'deletePlan' 	=> __('Delete', ET_DOMAIN) ,
			'editPlan' 		=> __('Edit', ET_DOMAIN)
			),
		'imgURL'			=>	TEMPLATEURL.'/img/',
		'confirm_delete_category' 	=> __('Are you sure you want to delete this category?', ET_DOMAIN),
		'confirm_delete_jobtype' 	=> __('Are you sure you want to delete this job type?', ET_DOMAIN),
		'confirm_delete_plan' 		=> __('Are you sure you want to delete this payment plan?', ET_DOMAIN),
		'confirm_reject_job' 		=> __('Are you sure you want to reject this job?', ET_DOMAIN),
		'plupload_config'	=> array(
			'max_file_size' 		=> '3mb',
			'url' 					=> admin_url('admin-ajax.php'),
			'flash_swf_url' 		=> includes_url('js/plupload/plupload.flash.swf'),
			'silverlight_xap_url'	=> includes_url('js/plupload/plupload.silverlight.xap'),
			'filters' 				=> array( array( 'title' => __('Image Files'), 'extensions' => 'jpg,jpeg,gif,png' ) ),
		),
		'loadingImg' 		=> '<img class="loading loading-wheel" src="'.TEMPLATEURL . '/img/loading.gif" alt="'.__('Loading...', ET_DOMAIN).'">',
		'loading' 		=> __('Loading', ET_DOMAIN)
	)  );

	wp_localize_script( 'admin_scripts', 'et_views', array(
		'loadingImg' 		=> '<img class="loading loading-wheel" src="'.TEMPLATEURL . '/img/loading.gif" alt="'.__('Loading...', ET_DOMAIN).'">',
		'loadingTxt' 		=> __('Loading...', ET_DOMAIN),
		'loadingFinish' 	=> '<span class="icon loading" data-icon="3"></span>'
	) );

	// wp_localize_script( 'js_editor', 'et_editor', array (
	// 	'jsURL' 			=> TEMPLATEURL . '/js/',
	// 	'skin'				=> 'silver',
	// 	'onchange_callback' => 'tiny_site_desc_callback',
	// 	'je_plugins'				=> apply_filters( 'je_editor_plugins', "spellchecker,paste,etHeading,etLink,autolink,inlinepopups,wordcount" ),
	// 	'theme_advanced_buttons1'	=> apply_filters( 'je_editor_theme_advanced_buttons1', "bold,|,italic,|,et_heading,|,etlink,|,numlist,|,spellchecker" ),
	// 	'theme_advanced_buttons2'	=> apply_filters( 'je_editor_theme_advanced_buttons2', "" ),
	// 	'theme_advanced_buttons3'	=> apply_filters( 'je_editor_theme_advanced_buttons3', "" ),
	// 	'theme_advanced_buttons3'	=> apply_filters( 'je_editor_theme_advanced_buttons4', "" )
	// ) );
}

/**
 * Register styles use in Job Engine
 */
add_action('init', 'je_enqueue_styles');
function je_enqueue_styles(){
	wp_register_style( 'admin_styles', TEMPLATEURL . '/css/admin.css' );
}

add_filter('et_admin_enqueue_styles_list', 'et_admin_addition_styles');
function et_admin_addition_styles($styles){
	$styles[] = 'admin_styles';
	$styles[] = 'farbtastic';
	$styles[] = 'et_colorpicker';
	$styles[] = 'job_styles';
	$styles[] = 'job_slider';
	return $styles;
}

/**
 * return admin page url
 */
function et_get_admin_page($slug){
	return add_query_arg( 'page', $slug, admin_url( 'admin.php' ) );
}

/**
 * Backend template helper
 */
function et_backend_job_category($parent = 0, $categories = false){
	if ( !$categories )
		$categories = et_get_job_categories_in_order();
		// $categories = get_terms('job_category',array(
		// 	'hide_empty' => false
		// 	));
	foreach ($categories as $job_cat) {
		if ( $job_cat->parent == $parent ){
		?>
		<li class="category-item" id="cat_<?php echo $job_cat->term_id ?>">
			<div class="container">
				<div class="sort-handle"></div>
				<div class="controls controls-2">
					<a class="button act-open-form" rel="<?php echo $job_cat->term_id ?>"  title="<?php _e('Add sub category for this category', ET_DOMAIN) ?>">
						<span class="icon" data-icon="+"></span>
					</a>
					<a class="button act-del" rel="<?php echo $job_cat->term_id ?>">
						<span class="icon" data-icon="*"></span>
					</a>
				</div>
				<div class="input-form input-form-2">
					<input class="bg-grey-input cat-name" rel="<?php echo $job_cat->term_id ?>" type="text" value="<?php echo $job_cat->name ?>" />
				</div>
			</div>
			<ul>
				<?php et_backend_job_category($job_cat->term_id, $categories); ?>
			</ul>
		</li>
		<?php
		} // end if
	} // end foreach
}


function et_job_cat_options($tax, $cats = array(), $parent = false, $level = 0){
	// re get categories if it empty
	if (empty($cats))
		$cats = array();

	// echo
	foreach ($cats as $cat) {
		if ( ($parent == false && !$cat->parent) || $parent == $cat->parent ){
			// seting spacing
			$space = '';
			for ($i = 0; $i < $level; $i++ )
				$space .= '--';

			// display option tag
			echo '<option value="' . $cat->term_id . '">' . $space . $cat->name . '</option>';
			et_job_cat_options($tax, $cats, $cat->term_id, $level + 1);
		}
	}
}

/**
 * Adding more fields into edit user fields
 */
if (  class_exists( "ET_Base") ):
	class JE_Backend_Company extends ET_Base{

		public function __construct(){
			// display field inputs
			$this->add_action('show_user_profile', 'company_fields');
			$this->add_action('edit_user_profile', 'company_fields');

			// save action
			$this->add_action('personal_options_update', 'save_company_info');
			$this->add_action('edit_user_profile_update', 'save_company_info');

			$this->add_action('user_edit_form_tag', 'add_form_tag');
		}

		/**
		 * add extra fields on company fields
		 */
		public function company_fields($user){
			if ($user->roles[0] != 'company') return;

			$logo = et_get_company_logo($user->ID);
			?>
			<h3><?php _e('Logo (for company only)', ET_DOMAIN) ?></h3>
			<p><?php _e('Current logo:', ET_DOMAIN) ?></p>
			<div class="user-logo">
				<img src="<?php echo $logo['company-logo'][0] ?>" alt="logo">
			</div>
			<p><?php _e('Upload new logo', ET_DOMAIN) ?></p>
			<input type="file" name="company_logo" id="company_logo">
			<?php
		}

		public function add_form_tag(){
			echo 'enctype="multipart/form-data"';
		}

		public function save_company_info($user){
			if ( isset($_FILES['company_logo']) && !empty($_FILES['company_logo']['size'])) {
				$attach_id = et_process_file_upload($_FILES['company_logo'], $user, 0, array(
					'jpg|jpeg|jpe'	=> 'image/jpeg',
					'gif'			=> 'image/gif',
					'png'			=> 'image/png',
					'bmp'			=> 'image/bmp',
					'tif|tiff'		=> 'image/tiff'
				) );

				$user_logo	= et_get_attachment_data($attach_id);
				et_update_user(array(
					'ID'		=> $user,
					'user_logo'	=> $user_logo
				));
				// flush user data cache
				et_create_companies_response ($user, true );
			}
		}
	}
		/**
	 * Adding job fields into post job page in Admin
	 */
	class JE_Job_Edit extends ET_Base{

		const NONCE = 'job_meta_box';

		public function __construct(){
			$this->add_action( 'add_meta_boxes', 'add_job_meta_boxes' );
			$this->add_action( 'save_post', 'save_job' );
			if ( (basename($_SERVER['SCRIPT_FILENAME']) == 'post.php' && isset($_GET['action']) && $_GET['action'] == 'edit') || (basename($_SERVER['SCRIPT_FILENAME']) == 'post-new.php' && (isset($_GET['post_type']) && $_GET['post_type'] == 'job' )) )
				$this->add_action( 'admin_enqueue_scripts', 'add_custom_scripts' );
		}

		/**
		 * Render job information use in Jobengine
		 */
		public function add_job_meta_boxes(){
			add_meta_box('et_job', __('Job information', ET_DOMAIN), array($this, 'job_meta_boxes'), 'job', 'normal', 'high');
		}

		public function add_custom_scripts(){
			global $wp_scripts, $post;
			$ui = $wp_scripts->query('jquery-ui-core');

			$home_url	=	home_url();
			$http		=	substr($home_url, 0,5);
			if($http != 'https') {
				$http	=	'http';
			}

	        $url = $http."://code.jquery.com/ui/{$ui->ver}/themes/smoothness/jquery-ui.css";
	        wp_enqueue_style('jquery-ui-redmond', $url, false, $ui->ver);

			$this->add_existed_script('jquery-ui-autocomplete');
			$this->add_existed_script('jquery-ui-datepicker');
			$this->add_existed_style('jquery-ui-datepicker');
			$this->add_existed_style('google_map_api');
			$this->add_script('et-googlemap-api', $http.'://maps.googleapis.com/maps/api/js?sensor=true');
			$this->add_script('et-gmaps', get_bloginfo( 'template_url') . '/js/lib/gmaps.js');

			// script for admin post job page
			if ( ($post->post_type == 'job' && isset($_GET['action']) && $_GET['action'] == 'edit') || 
				isset($_GET['post_type']) && $_GET['post_type'] == 'job' && basename($_SERVER['SCRIPT_FILENAME']) == 'post-new.php' ){
				$this->add_script('et-admin-post-job', get_bloginfo( 'template_url') . '/js/admin/edit_job.js', array('jquery', 'jquery-ui-autocomplete'));
				wp_localize_script( 'et-admin-post-job', 'et_data', array(
					'dateFormat' => $this->convert_php_date_format(get_option('date_format'))
					) );
			}
		}

		private function convert_php_date_format($df){
			$replace = array(
				'd' => 'dd', // two digi date
				'j' => 'd', // no leading zero date
				'm' => 'mm', // two digi month
				'n' => 'm', // no leading zero month
				'l' => 'DD', // date name long
				'D' => 'D', // date name short
				'F' => 'MM', // month name long
				'M' => 'M', // month name shỏrt
				'Y' => 'yy', // 4 digits year
				'y' => 'y',
			);
			$return = str_replace( array_keys($replace) , array_values($replace), $df);
			return $return;
		}

		public function job_meta_boxes($post){
			$job = et_create_jobs_response($post);
			?>
			<style>
				.et-field{
					width: 400px;
				}
			</style>
			<input type="hidden" name="_et_nonce" value="<?php echo wp_create_nonce( self::NONCE ) ?>">

			<p>
				<label for=""><strong><?php _e('Payment plan', ET_DOMAIN) ?></strong></label> <br>
				<?php
				$plans = et_get_payment_plans();
				?>

				<?php
				foreach ($plans as $plan) {
					$str 	= array(
						sprintf( __('display as %s in %d day', ET_DOMAIN), $plan['featured'] == 1 ? __('featured', ET_DOMAIN) : __('normal', ET_DOMAIN), $plan['duration']),
						sprintf( __('display as %s in %d days', ET_DOMAIN), $plan['featured'] == 1 ? __('featured', ET_DOMAIN) : __('normal', ET_DOMAIN), $plan['duration'])
					);
					$display = $plan['duration'] > 1 ? $str[1] : $str[0];
					$checked = $job['job_package'] == $plan['ID'] ? 'checked="checked"' : '';
					echo '<p><input data-duration="'.$plan['duration'].'" class="job-package" type="radio" id="et_job_package_'.$plan['ID'].'" name="et_job_package" value="' . $plan['ID'] . '" ' . $checked . '> <label for="et_job_package_'.$plan['ID'].'"><strong>' . $plan['title'] . '</strong> - ' . $display . '</label></p>';
				}
				?>
			</p>

			<p>
				<input type="hidden" name="et_featured" value="0">
				<input id="et_featured" type="checkbox" name="et_featured" value="1" <?php if ($job['featured'] == 1) echo 'checked="checked"' ?>> <label for="et_featured"><strong><?php _e('Display as featured job', ET_DOMAIN) ?></strong></label>
			</p>

			<p>
				<label for=""><strong><?php _e('Expired in (yyyy-mm-dd)', ET_DOMAIN) ?></strong> </label> <br>
				<input type="text" name="et_expired_date" id="et_date" class="et-field" value="<?php echo !empty($job['expired_date']) ? date( get_option('date_format'), strtotime($job['expired_date'])) : '' ?>">
			</p>

			<p>
				<label for=""><strong><?php _e('Location', ET_DOMAIN) ?></strong></label> <br>
				<input type="text" id="et_location" name="et_location" class="et-field" value="<?php echo isset($job['location']) ? $job['location'] : '' ?>">
				<input type="hidden" id="et_lat" name="et_lat" value="<?php echo isset($job['location_lat']) ? $job['location_lat'] : '' ?>">
				<input type="hidden" id="et_lng" name="et_lng" value="<?php echo isset($job['location_lng']) ? $job['location_lng'] : '' ?>">
			</p>

			<p>
				<label for=""><strong><?php _e('How to apply', ET_DOMAIN) ?></strong></label> <br>
				<input type="radio" name="et_apply_method" value="isapplywithprofile" <?php if ( empty($job['apply_method']) || $job['apply_method'] == 'isapplywithprofile' ) echo 'checked="checked"' ?>> <?php _e('By email below', ET_DOMAIN) ?><br>
				<input type="text" name="et_apply_email" class="et-field" value="<?php echo $job['apply_email'] ?>"> <br>
			</p>

			<p>
				<input type="radio" name="et_apply_method" value="ishowtoapply" <?php if (isset($job['apply_method']) && $job['apply_method'] == 'ishowtoapply') echo 'checked="checked"' ?>> <?php _e('By description below') ?><br>
				<textarea name="et_applicant_detail" cols="30" rows="10" class="et-field"><?php echo $job['applicant_detail'] ?></textarea>
			</p>

			<p>
				<?php $user = get_user_by( 'id', $post->post_author ); ?>
				<label for=""><strong><?php _e('Company', ET_DOMAIN) ?> </strong></label> <br>
				<input type="text" id="et_company" name="et_author_name" class="et-field" value="<?php echo $user->display_name ?>">
				<input type="hidden" name="et_author" value="<?php echo $post->post_author ?>"> 
				<?php 
				// print users list
				$users = get_users(array(
					'roles' => array('administrator', 'company')
					));
				$template = array();
				foreach ($users as $user) {
					$template[] = array(
						'value' => $user->ID,
						'label' => $user->display_name
						);
				}
				?>
				<script type="text/template" id="et_companies">
					<?php echo json_encode($template); ?>
				</script>
			</p>
			<?php

			do_action('et_job_meta_box', $post);
		}

		// handle save job action
		public function save_job($post_id){
			// verify nonce
			if (!isset($_POST['_et_nonce']) || !wp_verify_nonce( $_POST['_et_nonce'], self::NONCE )) return;
			unset($_POST['_et_nonce']);

			// cancel if user isn't admin
			if (!current_user_can( 'manage_options' ) ) return;

			// cancel if current post isn't job
			if (!isset($_POST['post_type']) || $_POST['post_type'] != 'job') return;

			// save location
			$this->update_job_meta($post_id, 'et_location', 		$_POST['et_location']);
			$this->update_job_meta($post_id, 'et_full_location', 		$_POST['et_location']);
			$this->update_job_meta($post_id, 'et_apply_method', 	$_POST['et_apply_method']);
			$this->update_job_meta($post_id, 'et_apply_email', 	$_POST['et_apply_email']);
			$this->update_job_meta($post_id, 'et_applicant_detail', 	$_POST['et_applicant_detail']);

			if(isset( $_POST['et_job_package'] )) {
				$this->update_job_meta($post_id, 'et_job_package', $_POST['et_job_package']  );	
			}

			$this->update_job_meta($post_id, 'et_featured', $_POST['et_featured']); // feature
			$this->update_job_meta($post_id, 'et_location_lng', $_POST['et_lng']); // feature
			$this->update_job_meta($post_id, 'et_location_lat', $_POST['et_lat']); // feature

			if ( empty($_POST['et_expired_date']) )
				{//$this->update_job_meta($post_id, 'et_expired_date', date('Y-m-d h:i:s', strtotime("+30 days"))); // save expired date
				}
			else
				$this->update_job_meta($post_id, 'et_expired_date', date('Y-m-d H:i:s', strtotime($_POST['et_expired_date']))); // save expired date

			//
			$plans = et_get_payment_plans();
			if ( isset( $_POST['et_job_package']) && isset($plans[$_POST['et_job_package']])){
				$plan = $plans[$_POST['et_job_package']];
				// $this->update_job_meta($post_id, 'et_expired_date', mktime(date('H'),date('i'),date('s'), date('n'), intval(date('n') + $plan['duration'])));
				// $this->update_job_meta($post_id, 'et_featured', $plan['featured']);
			}


			// verify author id and assign
			wp_update_post( array('ID' => $post_id, 'post_author' => $_POST['et_author'] ) );
			// flush job data cache
			et_create_jobs_response ( get_post($post_id) , true );

		}

		//
		protected function update_job_meta($id, $meta_key, $meta_value){
			return update_post_meta( $id, $meta_key, $meta_value );
		}
	}
endif;

add_action('admin_init', 'je_initialize_backend_company');
function je_initialize_backend_company(){
	new JE_Backend_Company();
	new JE_Job_Edit();
}


?>