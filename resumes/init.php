<?php
class JE_Resume_Init extends ET_Base{
	private static $options = null;
	function __construct(){
		// initialize resume, register post type, taxonomy ...
		new JE_Resume();

		// register role
		new JE_Job_Seeker();

		new JE_Resume_Ajax();
		new JE_JobSeeker_Ajax();

		$this->add_action ('wp_enqueue_scripts', 'on_add_scripts' );
		$this->add_action ('init', 'on_setup_permalink');
		$this->add_action('query_vars', 'on_add_query_vars');
		$this->add_action('et_approve_resume','et_approve_resume',10,2);
		$this->add_action('et_reject_resume','et_reject_resume',10,3);

        $this->add_filter('posts_distinct', 'custom_post_distinct');
        $this->add_filter('posts_where', 'custom_post_where');
        $this->add_filter('posts_join', 'custom_post_join');
        $this->add_filter ('et_admin_bar_menu', 'et_admin_bar_menu');

        $this->add_filter('je_wizard_step', 'je_wizard_step');
        $this->add_action('je_setup_wizard', 'resume_wizard');
        $this->add_filter('et_filter_setup_wizard_step', 'verify_setup_process');

        $this->add_action('et_after_register','et_user_register_mail', 10 , 2);

        self::$options	= new JE_Resume_Options();

	}


	/**
	 * Manually add permalink for jobseeker
	 */
	public function on_setup_permalink(){
		$rules 	= get_option( 'rewrite_rules' );

		// add rewrite rule for job seeker profile page
		$page = self::get_page_by_template('page-jobseeker-profile.php');
		if($page) {
			if ( !isset($rules[$page->post_name . '/([^/]+)$']) ){
				global $wp_rewrite;
				$page = self::get_page_by_template('page-jobseeker-profile.php');
				add_rewrite_rule( $page->post_name . '/([^/]+)$', 'index.php?page_id=' . $page->ID . '&jobseeker_name=$matches[1]', 'top' );

				$wp_rewrite->flush_rules();
			}
		}
	}

	/**
	 * Add query vars
	 */
	public function on_add_query_vars($vars){
		array_push($vars, 'jobseeker_name');
		array_push($vars, 'rq');
		// array_push($vars, 'location');
		return $vars;
	}

    public function custom_post_distinct($distinct){
        return "DISTINCT";
    }

    public function custom_post_join($join){
        global $wpdb, $wp_query;
        if (!empty($wp_query->query_vars['rq'])){
            $join .= " LEFT JOIN {$wpdb->postmeta} as resume_pm ON {$wpdb->posts}.ID = resume_pm.post_id ";
            $join .= " LEFT JOIN {$wpdb->term_relationships} as resume_tr ON {$wpdb->posts}.ID = resume_tr.object_id ";
            $join .= " LEFT JOIN {$wpdb->term_taxonomy} as resume_tt ON resume_tr.term_taxonomy_id = resume_tt.term_taxonomy_id ";
            $join .= " LEFT JOIN {$wpdb->terms} as resume_terms ON resume_tt.term_id = resume_terms.term_id ";
        }
        return $join;
    }

    public function custom_post_where($where){
    	global $wp_query;
        //$request = $_REQUEST['content'];
        $request = $wp_query->query_vars;
        if ( !isset($request['rq']) || empty($request['rq']) )
            return $where;

        // add custom query resume
        $key    = 'et_profession_title';
        $query  = $request['rq'];
        $where .= " AND ((resume_pm.meta_key = '$key' AND resume_pm.meta_value LIKE '%$query%') OR 
            ( resume_terms.name LIKE '%$query%' AND resume_tt.taxonomy = 'skill' ) )";

        return $where;
    }

	static public function get_page_by_template($page){

		$pages = get_pages(array(
			'post_type' => 'page',
			'meta_key' 	=> '_wp_page_template',
			'meta_value' => $page
		));

		// if no page found, return false
		if ( empty($pages) ) return false;

		// otherwise, return the first row
		foreach ((array)$pages as $page) {
			return $page;
		}
	}

	function et_admin_bar_menu ($menu_bar) {
		$menu_bar['resume']	=	array ('section' => 'et-resumes',	 'title' => __("Resumes", ET_DOMAIN) );
		return $menu_bar;
	}

	function je_wizard_step ($wizard_step) {
		$temp	=	array_pop($wizard_step);
		
		$resume_step	=	array('section' => 'resume', 'label' => __("Resumes", ET_DOMAIN), 'icon' => 'N');

		$wizard_step[]	=	$resume_step;
		$wizard_step[]	=	$temp;

		return $wizard_step;

	}

	function verify_setup_process ($finish) {
		// content
		$available_tax		=	JE_TaxFactory::get_instance('available');
		$availables			=	$available_tax->get_terms(); 
		$position_tax		=	JE_TaxFactory::get_instance('resume_category');
		$resume_categories	=	$position_tax->get_terms();
		$resume_setup		=	self::$options->has_setup_resume ;
		$resume_status		=	self::$options->get_resume_status() ;

		// //have not setup resume yet
		// if(!$resume_setup) {
		// 	return $finish;
		// }
		
		if( (count($availables) == 0 || count($resume_categories) == 0 ) && $resume_status == 1)
			return $finish; 
		$finish	=	$finish + 1;
		return $finish;

	}

	function resume_wizard ($section) {
	?>
		<div class="et-main-main clearfix  inner-content" id="wizard-resume"  <?php if ($section != 'resume') echo 'style="display:none"' ?>>
		<?php 
			$available_tax	=	JE_TaxFactory::get_instance('available');
			$availables		=	$available_tax->get_terms_in_order(); 
			$position_tax	=	JE_TaxFactory::get_instance('resume_category');
			$resume_categories	=	$position_tax->get_terms_in_order();

			$options	=	self::$options->get_all_current_options();
		?>
		<div id="resume_content">
			<div class="title font-quicksand"><?php _e('Turn the feature on', ET_DOMAIN) ?></div>
			<div class="desc">
				<?php _e("You can turn RESUME section off when you don't need it", ET_DOMAIN) ?>
				<div class="inner no-border btn-left">		
					<div class="payment">
						<div class="button-enable font-quicksand">
							<a href="#" data="et_resumes_status" title="Resume Status" class="toggle-button deactive <?php if ($options['et_resumes_status'] == 0) echo 'selected' ?>">
								<span><?php _e('Disable', ET_DOMAIN) ?></span>
							</a>
							<a href="#" data="et_resumes_status" title="Resume Status" class="toggle-button active <?php if ($options['et_resumes_status'] == 1) echo 'selected' ?>">
								<span><?php _e('Enable', ET_DOMAIN) ?></span>
							</a>
						</div>
					</div>
				</div>
			</div>
			<div id="wizard-settings-resume" <?php if($options['et_resumes_status'] == 0) echo "style='display:none;'" ?> >
				<div class="title font-quicksand">
				<?php
					$title_available  = $available_tax->get_title();		
				?>	<div class="title-main" title="<?php _e("Double click to edit", ET_DOMAIN); ?>">
					<?php echo ($title_available) ? $title_available : __("Oops, empty title! Double click to change", ET_DOMAIN); ?>
					</div>
					<input style="display:none;"  data-tax="available" type="text" value="<?php echo $title_available; ?>" placeholder="<?php _e("e.g. AVAILABLE or CONTRACT TYPE", ET_DOMAIN); ?>" />
					<span class="icon btn-edit" data-icon='p'></span>
				</div>
				<div class="desc">
					<?php _e("Available (e.g., full-time, part-time, contractual) are used by employer to filter resume posts.",ET_DOMAIN);?> 
					<div class="types-list-container" id="job-available">
						
						<?php 
						$available_tax->print_backend_terms();
						?>
						
						<?php $available_tax->print_confirm_list(); ?>
					</div>
				</div>

				<div class="title font-quicksand">		
				<?php 
					
					$title_position  = $position_tax->get_title();		
					
				?>
					<div class="title-main">
				 		<?php echo ($title_position) ? $title_position : __("Oops, empty title! Double click to change", ET_DOMAIN);?>
				 	</div>
					<input style="display:none;"  data-tax="resume_category" type="text" value="<?php echo $title_position;?>" placeholder="<?php _e("e.g. Fields and industries", ET_DOMAIN); ?>"/>
					<span class="icon btn-edit" data-icon='p'></span>
				</div>
				<div class="desc">
					<?php _e("You can define this list to categorize resumes the way you want, for example: fields and industries, expected positions...",ET_DOMAIN);?> 
					
					<div class="cat-list-container" id="job-position" >
						
							<?php $position_tax->print_backend_terms(); ?>
						
							<?php $position_tax->print_confirm_list(); ?>
					</div>
					
				</div>
			</div>
		</div>
			<?php et_wizard_nexstep_button (4); ?>
		</div>
	<?php
	}

	function et_approve_resume($resume, $jobseeker ){	
		$et_mail_approve_status	= self::$options->et_send_mail_approve;		
		if($et_mail_approve_status == 1){			
			$mail_template = JE_Resumes_Mailing::get_instance();
			$mail_approve = array(
	            'to'        => $jobseeker->user_email,
	            'subject'   => sprintf(__("Your profile in %s has been approved",ET_DOMAIN), get_option('blogname') ),
	            'message'   => et_get_mail_header() . $mail_template->make_message('approve', $resume, $jobseeker) . et_get_mail_footer(),
	            'headers'   => 'MIME-Version: 1.0' . "\r\n" . 
	                        'Content-type: text/html; charset=utf-8' . "\r\n" .
	                        "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n"
	            );			
			wp_mail( $mail_approve['to'], $mail_approve['subject'], $mail_approve['message'], $mail_approve['headers'] );
		}

	}

	function et_reject_resume($resume, $jobseeker , $args){	
		$et_mail_reject_status	= self::$options->et_send_mail_reject;		
		if($et_mail_reject_status == 1){
			$args 	=  array(
					'blogname'		=> get_bloginfo('blogname'),
					'display_name'	=> $jobseeker->display_name,
					'seeker_name'	=> $jobseeker->display_name,
					'seeker_mail'	=> $jobseeker->user_email,
					'admin_email'	=> get_option('admin_email'),
					'user_email'	=> $jobseeker->user_email,
					'resume_link'	=> isset($resume->permalink) ? $resume->permalink :'',
					'profile_link'  => isset($resume->permalink) ? $resume->permalink :'',
					'reason'	=>  isset($args['reason']) ? $args['reason'] :''

					);

			$mail_template = JE_Resumes_Mailing::get_instance();
			$template 	= $mail_template->get_template('reject');
			$message 	= $template;
			foreach ($args as $key => $arg) {
				$message = str_replace("[$key]", $arg, $message);
			}
			$message = stripslashes(html_entity_decode($message));

			$mail_eject = array(
	            'to'        => $jobseeker->user_email,
	            'subject'   => sprintf(__("Your profile in %s has been rejected",ET_DOMAIN),  get_option('blogname') ),
	            'message'   => et_get_mail_header() . $message . et_get_mail_footer(),
	            'headers'   => 'MIME-Version: 1.0' . "\r\n" . 
	                        'Content-type: text/html; charset=utf-8' . "\r\n" .
	                        "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n"
	       	);
			wp_mail( $mail_eject['to'], $mail_eject['subject'], $mail_eject['message'], $mail_eject['headers'] );
		}

	}

	function et_user_register_mail ( $user_id, $role ) {
		if($role != 'jobseeker')  return ;
		//if(!self::$options->et_send_apply_mail) return;
		$jobseeker	=	get_userdata( $user_id );
		$jobseeker	=	JE_Job_Seeker::convert_from_user ($jobseeker);
		
		$mail_template  = JE_Resumes_Mailing::get_instance();
		$template 		= $mail_template->get_template('register');
		$message 		= $template;

		$mail_register = array(
	            'to'        => $jobseeker->user_email,
	            'subject'   => sprintf(__("Welcome to %s ",ET_DOMAIN),get_option('blogname')),
	            'message'   => et_get_mail_header() . $mail_template->make_message('register',null,$jobseeker) . et_get_mail_footer(),
	            'headers'   => 'MIME-Version: 1.0' . "\r\n" . 
	                        'Content-type: text/html; charset=utf-8' . "\r\n" .
	                        "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n"
	            );
		wp_mail( $mail_register['to'], $mail_register['subject'], $mail_register['message'], $mail_register['headers'] );

	}

}

class ET_User_Role extends ET_Base{
	protected $name = '';
	protected $display_name = '';
	protected $caps = array();
	protected $fields = array();
	static $instance = null;

	function __construct($name, $display_name, $caps, $fields){
		
	}

	static function get_instance(){

	}

	public function register_user(){
		if (get_role($this->name)) return;

		// do filter
		$caps = apply_filters( $this->name . '_caps' , array(
			'read' => true
			) );

		add_role($this->name, $this->display_name, $caps);
	}

	public function p_insert($args, $wp_error = false){
		try {
			if (empty($args['username']))
				throw new Exception(__('Missing username', ET_DOMAIN));

			$args = wp_parse_args( $args, array( 'role' => $this->name ) );
			//$args = apply_filters( 'insert_' . $this->name, $args );

			$fields = array();
			// get fields
			foreach ($fields as $key => $field) {
				if (in_array($key, $this->fields)){
					$fields[$key] = $args[$key];
					unset($args[$key]);
				}
			}

			// insert user object
			$result = wp_insert_user( $args );

			// update field
			foreach ($fields as $key => $field) {
				update_user_meta( $result, $key, $field );
			}

			// call action
			do_action('insert_' . $this->name, $result);

			// insert new user
			return $result;
		} catch (Exception $e) {
			// if ($wp_error)
			// 	return new WP_Error('add_job_seeker_username', __('Missing User Name', ET_DOMAIN));
			// else 
				return false;
		}
	}

	public function p_update($args){
		try {
			if (empty($args['ID'])) return false;

			$args = wp_parse_args( $args, array( 'role' => $this->name ) );
			//$args = apply_filters( 'insert_' . $this->name, $args );

			$fields = array();
			// get fields
			foreach ($fields as $key => $field) {
				if (in_array($key, $this->fields)){
					$fields[$key] = $args[$key];
					unset($args[$key]);
				}
			}

			// insert user object
			$result = wp_update_user( $args );

			// update field
			foreach ($fields as $key => $field) {
				update_user_meta( $result, $key, $field );
			}

			// call action
			do_action('insert_' . $this->name, $result);

			// insert new user
			return $result;
		} catch (Exception $e) {
			
		}
	}

	public function convert_from_user($user){
		foreach ($this->fields as $field) {
			$user->$field = get_user_meta( $user->ID, $field );
		}
	}

	static public function query($args){

	}

}

/**
 * 
 */
class JE_Resumes_Mailing extends ET_Base{

	var $templates 	= array('register', 'apply','approve','reject' , 'contact');
	var $available 	= array();
	public $defaults ;

	static $option_key 	= 'et_resumes_mails';

	static $instance 	= null;

	public function __construct(){
		//$this->templates 	= apply_filters( 'resume_mailing_template', array('register', 'apply') );
		$defaults	=	array(
			'register' 	=> __("<p>Hello [display_name],</p><p>You have successfully registered an account with  [blogname].</p> <p>Here is your account information: <br /> Username: [user_login] <br />Email: [user_email] <br /> </p> <p>Thank you and welcome to [blogname]. </p>", ET_DOMAIN),
			
			'apply' 	=> __("<p>Dear [display_name],</p><p>Your job offer for [job_title] posted in [blogname] has a new application.</p><p>Here are the applicants information:<br />Email address: [seeker_mail]<br />Note: [seeker_note]<br /><a href='[profile_link]'>Click here </a>to view the applicant's profile.</p> <p>And here is the link to your job offer: [job_link].</p><p>Sincerely<br />[blogname]</p>", ET_DOMAIN),

			'approve' 	=> __("<p>Dear [display_name], </p> <p>Your professional profile in [blogname] has been approved and is displayed in our jobseeker section now. You can follow this link: [profile_link] to view your profile or make further changes.</p><p>Sincerely,<br />[blogname]</p>", ET_DOMAIN),

			'reject' 	=> __("<p>Dear [display_name],</p> <p>Your professional profile in [blogname] has been rejected.<br /> Noted reason: [reason] </p><p>Please contact the administrators via [admin_email] for more information, or go to your profile at [profile_link] to make proper changes and submit it again.</p><p>Sincerely,<br />[blogname]</p>", ET_DOMAIN) ,
			
			'contact'	=> __("<p>Hi [display_name],</p><p>You have a new message from [contact_name]:</p><p>[contact_msg]</p><p>Sincerely, <br/>[blogname]</p>", ET_DOMAIN)
						
		);
		$this->defaults 	= $defaults;
		$this->available 	= array_keys($this->defaults);
		$this->templates 	= (array)get_option(self::$option_key, array());
	}

	static public function get_instance(){
		if (empty(self::$instance)){
			self::$instance = new JE_Resumes_Mailing();
		} 
		return self::$instance;
	}

	public function in_template($name){	
		return in_array($name, $this->available);
	}

	public function get_default_template($name){
		if ( !isset($this->defaults[$name]) ) 
			return false;
		else 
			return $this->defaults[$name];
	}

	// return the template by giving template's name
	public function get_template($name){
		// return the right template
		if ( !empty( $this->templates[$name] )  ){
			return $this->templates[$name];
		} 
		// if cannot find the right template, return the default one
		else if ( !empty($this->defaults[$name]) ){
			return $this->defaults[$name];
		} else {
			return '';
		}
	}

	// saving the template by giving name
	public function save_template($name, $value){
		if (!$this->in_template($name)) return false;

		$this->templates[$name] = $value;
		update_option( self::$option_key, $this->templates );
	}

	public function save_templates($array){
		foreach ($this->templates as $name => $value) {
			if (!$this->in_template($name)) continue;

			$this->templates[$name] = $value;
		}
		update_option( self::$option_key, $this->templates );
	}

	public function make_message_old($name, $args){
		$args 		= wp_parse_args( $args, array(
			'blogname' => get_bloginfo('blogname')
		) );
		
		$template 	= $this->get_template($name);
		$message 	= $template;
		foreach ($args as $key => $arg) {
			$message = str_replace("[$key]", $arg, $message);
		}
		return stripslashes(html_entity_decode($message));
	}

	public function make_message($name, $resume, $jobseeker){
		$args 	=  array(
					'blogname'		=> get_bloginfo('blogname'),
					'display_name'	=> isset($jobseeker->display_name) ? $jobseeker->display_name : '',
					'seeker_name'	=> isset($jobseeker->display_name) ? $jobseeker->display_name : '',
					'seeker_mail'	=> isset($jobseeker->user_email) ? $jobseeker->user_email: '',
					'admin_email'	=> get_option('admin_email'),
					'user_email'	=> isset($jobseeker->user_email) ? $jobseeker->user_email : '',
					'resume_link'	=> isset($resume->permalink) ? $resume->permalink :'',
					'profile_link'  => isset($resume->permalink) ? $resume->permalink :'',
					'user_login'	=> isset($jobseeker->user_login) ? $jobseeker->user_login : '',

					);
		$short_code = apply_filters( 'et_add_shortcode_resume_mail_template', $args );

		$template 	= $this->get_template($name);
		$message 	= $template;
		foreach ($short_code as $key => $arg) {
			$message = str_replace("[$key]", $arg, $message);
		}
		return stripslashes(html_entity_decode($message));
	}
		
}

