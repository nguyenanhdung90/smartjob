<?php
require_once RESUME_PATH . '/resumes.php';
require_once RESUME_PATH . '/job_seekers.php';
require_once RESUME_PATH . '/helper.php';
require_once RESUME_PATH . '/init.php';
require_once RESUME_PATH . '/template.php';
require_once RESUME_PATH . '/widgets.php';

class JE_Resume_Front extends JE_Resume_Init{

	public static $options ;

	function __construct(){
		parent::__construct();
		$this->add_action( 'init' , 'init_cookie_rand_resumes' );
		$this->add_action('wp_footer', 'wp_footer_add_template');
		$this->add_action( 'wp_print_styles', 'enqueue_style');
		$this->add_action('pre_get_posts', 'pre_get_posts');
		$this->add_action('template_redirect', 'redirect_theme');

		$this->add_filter('et_custom_breadcrumbs', 'edit_breadcrumbs', 10, 2);
		$this->add_filter( 'je_editor_theme_advanced_buttons1', 'filter_editor_button');
		$this->add_action('je_insert_resume' , 'new_resume_alert');
		self::$options	=	JE_Resume_Options::get_instance();

		$this->add_action( 'et_mobile_footer' , 'et_resume_template' );
	}

	public function init_cookie_rand_resumes () {
		$order_by	=	array ('date', 'author', 'title', 'name', 'ID');
		$rand	=	$order_by[array_rand($order_by)];
		setcookie('rand_sort_resume', $rand, 60*10 , '/' );
	}

	public function enqueue_style () {
		wp_enqueue_style( 'jobseeker_style' , TEMPLATEURL.'/css/custom-jseeker.css');
	}

	public function on_add_scripts () {

		// if(is_page_template( 'page-instro.php' )) return ;
		$this->add_existed_script('jquery-ui-autocomplete');
		$this->add_existed_script('jquery-textarea-autosize');

		$this->add_script('et_jobseeker', get_bloginfo('template_url') . '/resumes/js/jobseeker.js',array('jquery','et-underscore', 'et-backbone',  'job_engine', 'front'));
		$this->add_script('et_resume', get_bloginfo('template_url') . '/resumes/js/resumes.js',array('jquery','et-underscore', 'et-backbone',  'job_engine'));

		wp_localize_script( 'et_resume', 'et_resume', array(
			'no_link'			=> __("No link specified", ET_DOMAIN),
			'no_location'		=> __("No location specified", ET_DOMAIN),
			'no_resume_found'	=> __("Oops! Sorry, no resumes found.", ET_DOMAIN),
			'pending_message'	=> __('THIS RESUME IS WAITING FOR APPROVAL TO PUBLISH.', ET_DOMAIN),
			'reject_message'	=> __('THIS RESUME IS REJECTED.', ET_DOMAIN),
			'date_range_invalid' => __("End date is invalid.", ET_DOMAIN),
			'position_invalid'	=>  __(" Please enter your job title.", ET_DOMAIN),
			'from_date_invalid' => __(' Please select start date.', ET_DOMAIN),
			'to_date_invalid'	=> __(' Please select end date.', ET_DOMAIN),
			'school_name_invalid'			=> __(" Please enter your school name.", ET_DOMAIN),
			'company_name_invalid'			=> __(" Please enter your company name.", ET_DOMAIN),
			'duplicate_skills' 				=> __(' You have already added this skill. Please enter another or select from the suggestions.', ET_DOMAIN),
			'duplicate_resume_category' 	=> __('You have already selected this category. Please choose another from the list.', ET_DOMAIN),
			'is_free_view'					=> (self::$options->et_free_view_resume == 0 ) ? true : false,
			'resumes_privacy'				=> (self::$options->et_resumes_priavcy== 1 ) ? true : false,
			//'use_captcha'					=> $useCaptcha
		));
		// check if current user is admin or author of resume,
		// if yes, allow him to edit job
		if (is_singular( 'resume' ) ){
			// get the jobseeker first
			global $current_user, $post;
			$jobseeker 			= get_userdata($post->post_author);

			$authorise 			= current_user_can( 'manage_options' ) || $current_user->ID == $jobseeker->ID;
			// enqueue jquery validate
			$this->add_existed_script( 'jquery_validator' );

			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('front');

			if ( $authorise ){
				$this->add_existed_script( 'plupload-all' );

				$this->add_script( 'et_jobseeker_profile_manage', TEMPLATEURL . '/resumes/js/single-resume.js',array('jquery','et-underscore', 'et-backbone',  
				'job_engine'));

				// localize scripts
				wp_localize_script( 'et_jobseeker_profile_manage', 'et_resume_profile', array(
					// 'duplicate_skills' 			=> __('This skills is selected, please enter another skill', ET_DOMAIN),
					// 'duplicate_resume_category' 	=> __('This resume category is selected, please choose another resume category', ET_DOMAIN),
					'err_linked_url' 			=> __('Please enter valid linkedin profile address', ET_DOMAIN),
					'err_twitter_url' 			=> __('Please enter valid twitter address', ET_DOMAIN),
					'err_facebook_url' 			=> __('Please enter valid facebook address', ET_DOMAIN),
					'err_google_url' 			=> __('Please enter valid google plus address', ET_DOMAIN),
					'err_personal_weburl' 		=> __('Please enter valid personal website address', ET_DOMAIN),
					'personal_website'			=> __('Personal Website',ET_DOMAIN),
					'google_plus'				=> __('Google Plus',ET_DOMAIN),
					) );
			}

			if (isset($_GET['action'])){
				// script for sending message
				$this->add_script( 'et_jobseeker_profile', TEMPLATEURL . '/resumes/js/page-profile.js',array('jquery','et-underscore', 'et-backbone',
					'job_engine'));
				wp_localize_script( 'et_jobseeker_profile', 'et_profile', array(
					'err_name_required' 	=> __('Name is required', ET_DOMAIN),
					'err_email_required' 	=> __('Name is required', ET_DOMAIN),
					'err_message_required' 	=> __('Message is required', ET_DOMAIN)
				) );
			}

		}

		else if(is_page_template( 'page-jobseeker-signup.php' )){
			$this->add_script( 'et_signup', TEMPLATEURL . '/resumes/js/signup.js',array('jquery','et-underscore', 'et-backbone',
			'job_engine', 'front'));
		}

		else if ( is_post_type_archive('resume') || is_page_template( 'page-resume-index.php' ) ){
			$this->add_script( 'et_archive_resume', TEMPLATEURL . '/resumes/js/archive-resume.js', array('jquery', 'et-underscore', 'et-backbone', 'job_engine', 'front') );
		}

		else if (is_singular( 'job' )){
			$this->add_script( 'et_jobseeker_apply', TEMPLATEURL . '/resumes/js/single-job.js', array('jquery', 'et-underscore', 'et-backbone',  'job_engine') );
		}

		else if (is_page_template('page-jobseeker-account.php')){
			$this->add_existed_script('jquery_validator');
			//$this->add_existed_script('front');
			$this->add_script('et_jobseeker_account', TEMPLATEURL . '/resumes/js/page-account.js', array('jquery', 'et-backbone', 'et-underscore', 'job_engine'));

			wp_localize_script( 'et_jobseeker_account', 'et_account', array(
				'err_email_user_email' 			=> __('Email is invalid', ET_DOMAIN),
				'err_required_user_pass_again' 	=> __('Please confirm password', ET_DOMAIN),
				'err_equalto_user_pass_again' 	=> __('Confirmed password doesnot matched', ET_DOMAIN),
				'err_required_current_pass' 	=> __('Please enter your current password', ET_DOMAIN)
			) );
		}

		if(is_page_template( 'page-upgrade-account.php' )) {

			if(current_user_can( 'manage_options' )) {
				$this->add_existed_script ('js-editor');
				$this->add_existed_script ('widget-sidebar');
			}

			$this->add_script ('et_upgrade', TEMPLATEURL . '/resumes/js/upgrade.js', array('jquery', 'et-backbone', 'et-underscore', 'job_engine', 'front'));

			$_2co_api	=	ET_2CO::get_api();
			if($_2co_api['use_direct']) {
				wp_enqueue_script( '2co_direct_script', ET_2CO::$direct_script);
			}

		}

		$this->localize_script();
	}

	public function on_add_styles(){

	}
	/**
	 * localize script
	*/
	public function localize_script () {
		wp_localize_script( 'et_signup','et_signup', array( 'email_exist'	 	=> __("This email already exists! <a href='#'' class='forgot-pass-link'>Forgot password</a>", ET_DOMAIN), 
															// 'add_another_skill' => __("add another skill", ET_DOMAIN)	,
															'username_exist'	=> __("This username has been used!", ET_DOMAIN),
															// 'duplicate_skills' 			=> __(' You have already added this skill. Please enter another or select from the suggestions.', ET_DOMAIN),
															// 'duplicate_resume_category' 	=> __('You have already selected this category. Please choose another from the list.', ET_DOMAIN)
														) 
								);
		wp_localize_script( 'et_upgrade', 'et_upgrade', array(
					'notice_step_not_allowed'	=> __('You need to finish the previous step first!', ET_DOMAIN),
					'button_submit'				=> __('SUBMIT',ET_DOMAIN),
					'button_continue'			=> __('CONTINUE',ET_DOMAIN),
					'reg_user_name'				=> __("Your username must not contain special characters", ET_DOMAIN),
					'error_msg'					=> __("Please fill out all required fields.", ET_DOMAIN)	) ) ;



	}

	public function wp_footer_add_template () {
		wp_reset_query();
		//$name = get_query_var( 'jobseeker_name' );

		//$is_profile = !empty($name);
		if( is_singular( 'resume' ) || is_page_template('page-jobseeker-signup.php') || is_archive('resume') || is_page_template( 'page-resume-index.php' ) ) {
	 		je_eduction_template ();

			// get top 20 popular skills
			$skills = get_terms('skill', array( 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => false, 'number' => 200, 'fields' => 'names' )); 
			?>
				<script type="text/data" id="data_skills">
				<?php echo json_encode($skills) ?>
				</script>
			<?php
			je_block_resume_message ();
		}
		/**
		 * add linkleIn Script
		*/
		$use_linkle		=	apply_filters( 'je_is_use_linkleIn_signup', true );
		$options		=	JE_Resume_Options::get_instance();
		$linkedin_api 	=	$options->get_linked_api();

		//$http	=	et_get_http();
		if($use_linkle && is_page_template('page-jobseeker-signup.php') && $linkedin_api != '') {
			echo 	'<script type="text/javascript" src="//platform.linkedin.com/in.js">
					  	api_key: '.$linkedin_api.'
					  	scope: r_fullprofile r_emailaddress
					 </script>';
		}

		if(is_page_template( 'page-upgrade-account.php' )) {
		?>
			<script type="text/data" id="package_plans">
			<?php echo json_encode(et_get_resume_plans()) ?>
			</script>

		<?php
		}

	}

	public function edit_breadcrumbs($breadcrums, $args){
		$new = array('<a href="' . get_bloginfo('url') . '">' . $args['home'] . '</a>');

		// Resumes index page
		if ( is_post_type_archive( 'resume' ) ) {
			$new[] = '<span>' . __('Resumes', ET_DOMAIN) . '</span>';
		}
		// Send message to jobseeker page
		else if (is_singular( 'resume' ) && isset($_GET['action']) && $_GET['action'] == 'send_message') {
			global $post;
			$new[] = "<a href='" . get_post_type_archive_link( 'resume' ) . "'>" . __('Resumes', ET_DOMAIN) . "</a>";
			$new[] = "<a href='" . get_permalink( $post->ID ) . "'>" . $post->post_title . "</a>";
			$new[] = '<span>' . __('Send a message') . '</span>';
		}
		// resume detail page
		else if (is_singular( 'resume' ) ){
			global $post;
			$new[] = "<a href='" . get_post_type_archive_link( 'resume' ) . "'>" . __('Resumes', ET_DOMAIN) . "</a>";
			$new[] = '<span id="breadcrums_name">' . $post->post_title . '</span>';
		}
		// sign up page
		else if ( is_page_template( 'page-jobseeker-signup.php' ) ){
			global $post;
			$new[] = "<a href='" . get_post_type_archive_link( 'resume' ) . "'>" . __('Resumes', ET_DOMAIN) . "</a>";
			$new[] = '<span>' . $post->post_title . '</span>';
		} else if(is_page_template('page-jobseeker-account.php')) {
			global $post ;

			$new[] = "<a href='" . get_post_type_archive_link( 'resume' ) . "'>" . __('Resumes', ET_DOMAIN) . "</a>";
			$new[] = '<span>'.__("Profile Settings", ET_DOMAIN).'</span>';
		} else {
			return $breadcrums;
		}

		$breadcrums_text = implode( $args['delimiter'], $new );

		return $breadcrums_text;
	}

	public function pre_get_posts($query){
		global $wp_query;
		if (!$query->is_main_query()) return $query;

		if (isset($query->query_vars['location']) && $query->query_vars['location'] && is_post_type_archive( 'resume' ) ){
			$meta_query = empty($query->query_vars['meta_query']) ? array() : $query->query_vars['meta_query'];
			$meta_query = wp_parse_args( $meta_query, array(
					'relation' => 'AND',
					array(
						'key' 	=> 'et_location',
						'value' => $query->query_vars['location'],
						'compare' => 'LIKE'
					) 
				)
			);
			$query->query_vars['meta_query'] = $meta_query;
			unset($query->query_vars['location']);
		}

		if ( !empty($query->query_vars['status']) && current_user_can('manage_options') ){
			$query->set('post_status', $query->query_vars['status']);
		}

		// allow people view publish jobs in archive only
		if ( (is_home() || is_tax('job_type') || is_tax('job_category') || is_author() || is_post_type_archive('resume') ) && ( empty($query->query_vars['post_status']) )){
			$query->set('post_status', array('publish'));
		}

		if( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'resume' ) {
			if( isset($_COOKIE['rand_sort_resume']) ) {
				$rand	=	$_COOKIE['rand_sort_resume'];
			} else {
				$order_by	=	array ('date', 'author', 'title', 'name', 'ID');
				$rand	=	$order_by[array_rand($order_by)];
			}
			$query->set('orderby', $rand );
			$query->set('ignore_sticky_posts', 1);
		}

		return $query;
	}

	public function redirect_theme() {
		// if (is_page_template('page-jobseeker-account.php') && !current_user_can('jobseeker')){
		// 	wp_redirect( et_get_page_link('jobseeker-signup') );
		// 	exit;
		// } 

		if (is_page_template('page-jobseeker-signup.php') && is_user_logged_in()){
			global $current_user, $user_ID;
			$roles		=	$current_user->roles;
			$user_role	=	array_pop($roles);
			if($user_role == 'jobseeker') {
				$jobseeker	=	et_create_user_response($user_ID);
				wp_redirect($jobseeker['profile_url']);

			} elseif ($user_role == 'administrator' || $user_role == 'company') {
				wp_redirect(et_get_page_link('dashboard'));
			}else {
				wp_redirect(home_url());
			}
		}

		if(is_page_template( 'page-post-a-job.php' )) {
			global $current_user, $user_ID;
			$roles		=	$current_user->roles;
			$user_role	=	array_pop($roles);

			if($user_role == 'jobseeker') {
				wp_redirect(home_url());

			}
		}

		global $user_ID;
		$current_user_can	=	true;
		$duration	=	je_get_resume_view_duration ($user_ID);
		if( $duration < time() ) $current_user_can	=	false;
		/**
		 * control single resume view
		*/
		if(is_singular( 'resume' )) {

			if( !current_user_can( 'manage_options' ) && self::$options->et_free_view_resume  ) {
				global $post;
				$accessible_list    =   JE_Job_Seeker::get_accessible_list($post->post_author);
				if( $post->post_author != $user_ID && !$current_user_can && !in_array( $user_ID, $accessible_list)) {
					$redirect_link	=	 et_get_page_link (array(
															'page_type' 	=> 'upgrade-account' , 
															'post_title' 	=> __("Upgrade account", ET_DOMAIN),
															'post_content'	=> __("Jobseeker profile is not free to view, you should upgrade your account to access resume profile.", ET_DOMAIN)
														), array('resume_id' => $post->ID)
									);

					wp_redirect( $redirect_link, $status = 302 );
				}

			}

		}


	}

	function filter_editor_button ($button) {
		if(is_page_template( 'page-upgrade-account.php' ) || is_page_template( 'page-post-a-job.php' ) ) 
			return "bold,|,italic,|,et_heading,|,etlink,|,numlist,|, bullist";
		return $button;
	}

	function new_resume_alert ($id) {
		/**
		 * new job notification
		*/
		$notification_mail = get_option('et_job_notification_mail', '');
		if($notification_mail != '') {
			$message = sprintf(__("Hi! <p>%s has a new resume. Click <a href='%s'>here</a> to see the details.</p> <p>Best regards.</p>", ET_DOMAIN),
								get_option('blogname') , get_permalink($id) );
			wp_mail( $notification_mail,
						__("New Resume Alert", ET_DOMAIN),
						$message );
		}
	}
	/**
	* hook action footer for resume page.
	*  @since 2.9.8
	* move from mobile/functions.php file.
	*/
	function et_resume_template () {?>
		<script type="text/template" id="education_template">
			<div class="education element">
				<div class="input-text-remind">
	                <input type="text" class="name"   value="" placeholder="<?php _e('School name', ET_DOMAIN); ?>">
	            </div>
	            <div class="input-text-remind">
	                <input type="text" class="degree"  value="" placeholder="<?php _e('Degree', ET_DOMAIN); ?>">
	            </div>
	            <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
	            <div class="date-select">
	            <?php
	                JE_Helper::monthSelectBox('fromMonth' , false, array('class' => 'month fromMonth' ,  ) );
	                JE_Helper::yearSelectBox('fromYear' , false, array('class' => 'year fromYear' ,  ) );
	            ?>
	            </div>
	            <div class="clear" style="clear:both; height:18px; overflow:hidden;"><?php _e("to", ET_DOMAIN); ?></div>
	            <div class="date-select">
	            <?php
	                JE_Helper::monthSelectBox('toMonth' , false, array('class' => 'month toMonth'  ) );
	                JE_Helper::yearSelectBox('toYear' , false, array('class' => 'year toYear' ,  ) );
	            ?>
	            </div>
			    <div class="ui-checkbox signup">
			        <label for="education-{{ i }}" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-checkbox-off">
			        	<?php _e("I currently study here", ET_DOMAIN); ?>
			        </label>
			        <input type="checkbox" name="education-{{ i }}" id="education-{{ i }}" data-enhanced="true" class="curr">
			        <!-- <span class="icon icon-track" data-icon="#"></span> -->
			    </div>
			</div>
		</script>

		<script type="text/template" id="exp_template">
			<div class="experience element">
	    		<div class="input-text-remind">
	                    <input type="text" required class="name"  value="" placeholder="<?php _e('Company name', ET_DOMAIN); ?>">
	                </div>
	                <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
	                <div class="input-text-remind">
	                    <input type="text" required class="position"  value="" placeholder="<?php _e('Position', ET_DOMAIN); ?>">
	                </div>
	                <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
	                <div class="date-select">
	                <?php
	                    JE_Helper::monthSelectBox('fromMonth' , false, array('class' => 'month fromMonth'  ) );
	                    JE_Helper::yearSelectBox('fromYear' , false, array('class' => 'year fromYear' ) );
	                ?>
	                </div>
	                <div class="clear" style="clear:both; height:18px; overflow:hidden;"><?php _e("to", ET_DOMAIN); ?></div>
	                <div class="date-select">
	                <?php
	                    JE_Helper::monthSelectBox('toMonth' , false, array('class' => 'month toMonth'  ) );
	                    JE_Helper::yearSelectBox('toYear' , false, array('class' => 'year toYear'  ) );
	                ?>
	                </div>
	            <div class="ui-checkbox signup">
	                <label for="experience-{{ i }}" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-checkbox-off">
	                	<?php _e("I currently work here", ET_DOMAIN); ?>
	                </label>
	                <input type="checkbox" name="checkbox-enhanced" id="experience-{{i}}" data-enhanced="true" class="curr">
	                <!-- <span class="icon icon-track" data-icon="#"></span> -->
	            </div>
	        </div>
	    </script>
	    <script type="text/template" id="skill_template">
	    	<li class="element" ><span class="icon icon-track" data-icon="#"></span><span class="text">{{val}}</span><input class="skill" type="hidden" value="{{val}}" ></li>
	    </script>
	<?php
	}

}
