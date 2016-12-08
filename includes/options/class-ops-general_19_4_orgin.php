<?php
if( class_exists('ET_Options')) :
/**
 * class ET_GeneralOptions extended ET_Options
 * control general options on site
 * 	-	site title
 * 	-	site desc
 * 	-	brand : logo, mobile icon
 * 	-	layout 
 * 	-	languages
 * @author dakachi
 *
 */
class ET_GeneralOptions extends ET_Options 
{
	protected $opt_keys ;
	private $prefix;

	public static $instance =  null;
	
	public function __construct() {
		global $et_global;
		$this->opt_keys	=	array (
			'et_site_title' 	=>  __("Site title",ET_DOMAIN),
			'et_site_desc'		=>	__("Site description",ET_DOMAIN),
			'et_site_demon'		=>	__("Site demonstration",ET_DOMAIN),
			'et_website_logo'	=>	__("Site logo",ET_DOMAIN),
			'et_mobile_icon'	=>	__("Site mobile icon",ET_DOMAIN),
			'et_default_logo'	=> __('Default company logo',ET_DOMAIN),
			'et_language'		=>	__("Site language", ET_DOMAIN),
			'et_layout'			=>	__("Site layout",ET_DOMAIN),
			'et_facebook_link'	=>	__("Facebook URL",ET_DOMAIN),
			'et_twitter_account'=>	__("Twitter Account",ET_DOMAIN),
			'et_google_plus'	=>	__("Google Plus URL",ET_DOMAIN),
			'et_customization' 	=>  __('Site Customization', ET_DOMAIN),
			'et_copyright'		=>	__("Copyright information",ET_DOMAIN),
			'et_google_analytics'	=>	__("Google analytics", ET_DOMAIN)
		);
		$this->prefix = $et_global['db_prefix'];
		parent::__construct('et_general_opts');
	}
	
	public static function get_instance() {
		if(self::$instance == null) {
			self::$instance	=	new ET_GeneralOptions ();
		}
		return self::$instance;
	}

	public function save_settings ( $key, $new_value) {
		
		$new_value	=	stripcslashes($new_value);
		//use this pattern to remove any empty tag '
		$pattern = "/<[^\/>]*>(&nbsp;)*([\s]?)*<\/[^>]*>/";  
	
		$new_value	=	 preg_replace($pattern, '', $new_value); 
		$new_value	= 	trim($new_value);

		if( !isset($this->opt_keys[$key])) 
			return false;
		switch ($key) {
			case 'et_site_title':
				return $this->set_site_title($new_value);
				break;
			
			case 'et_site_desc':
				return $this->set_site_desc($new_value);
				break;
			
			case 'et_site_demon':
				return $this->set_site_demonstration($new_value);
				break;
			
			case 'et_google_plus':
				return $this->set_google_plus($new_value);
				break;
			
			case 'et_twitter_account':
				return $this->set_twitter_account($new_value);
				break;
			
			case 'et_facebook_link':
				return $this->set_facebook_link($new_value);
				break;
			
			default:
				;
			break;
		}
		
		$this->update_option( $key, stripcslashes($new_value) );
		return ($new_value != '');
	}
	
	public function get_site_desc () {
		return $this->get_option('et_site_desc');
	}
	
	public function set_site_desc ( $new_value ) {
		update_option ('blogdescription', $new_value);
		return $this->update_option('et_site_desc', $new_value);
		
	}
	
	public function get_site_title () {
		return get_bloginfo('name');
	}
	
	public function set_site_title ($new_value) {
		update_option('blogname', $new_value );
		return	$this->update_option('et_site_title', $new_value);
		
	}
	public function set_site_demonstration ( $new_value ) {
		//use this pattern to remove any empty tag '
		$pattern = "/<[^\/>]*>(&nbsp;)*([\s]?)*<\/[^>]*>/";  
	
		$new_value	=	 preg_replace($pattern, '', $new_value); 
		return	$this->update_option('et_site_demon', trim($new_value));
		
	}
	/**
	 * get jobengine site desmontration text
	*/
	public function get_site_demonstration () {
		return apply_filters('et_get_site_demonstration',$this->get_option ('et_site_demon'));
	}
	/**
	 * get site logo setting
	 */
	public function get_website_logo ($size = false) {
		$logo_id = $this->get_website_logo_id();
		if ($logo_id) {
			if(!$size) 
				return wp_get_attachment_image_src( $this->get_website_logo_id(), 'full' );
			return 
				wp_get_attachment_image_src( $this->get_website_logo_id(), $size );
		}
			
		else
			return array( TEMPLATEURL . '/img/website_logo.png', 200, 70);
	}

	public function get_website_logo_id () {
		return $this->get_option('et_website_logo');
	}

	/**
	 * set site logo setting
	 * @param string $new_logo
	 */
	public function set_website_logo ($new_logo) {
		return $this->update_option('et_website_logo', $new_logo);

	}
	/**
	 *	get mobile icon setting
	 */
	public function get_mobile_icon () {
		$icon_id = $this->get_mobile_icon_id();
		if ($icon_id)
			return wp_get_attachment_image_src( $this->get_mobile_icon_id(), 'thumbnail' );
		else
			return array( TEMPLATEURL . '/img/mobile_icon.png', 144, 144);
	}

	public function get_favicon () {
		$icon_id = $this->get_mobile_icon_id();
		if ($icon_id)
			return wp_get_attachment_image_src( $this->get_mobile_icon_id(), 'small_thumb' );
		else
			return array( TEMPLATEURL . '/img/mobile_icon.png', 16, 16);
	}

	public function get_mobile_icon_id () {
		return $this->get_option('et_mobile_icon');
	}

	/**
	 * set mobile icon
	 * @param string, int $new_icon : icon attachment id
	 */
	public function set_mobile_icon ($new_icon){
		return $this->update_option('et_mobile_icon', $new_icon);
	}
	/**
	 *	get default company logo setting
	 */
	public function get_default_logo () {
		$default_logo = $this->get_default_logo_id();
		if ($default_logo)
			return wp_get_attachment_image_src( $this->get_default_logo_id(), 'company-logo' );
		else
			return array( TEMPLATEURL . '/img/default_logo.jpg', 200, 200);
	}

	public function get_default_logo_id () {
		return $this->get_option('et_default_logo');
	}

	/**
	 * set default company logo
	 * @param string, int $new_logo : logo attachment id
	 */
	public function set_default_logo ($new_logo){
		return $this->update_option('et_default_logo', $new_logo);
	}
	/**
	 * set language setting
	 * @param string $new_lang : language file name
	 */
	public function set_language ( $new_lang ) {
		return $this->update_option('et_language', $new_lang );
	}
	/**
	 * get site language setting
	 */
	public function get_language ( ) {
		return $this->get_option('et_language');
	}
	/**
	 * set site layout option
	 * @param string $new_layout
	 */
	public function set_layout ( $new_layout) {
		return $this->update_option ('et_layout', $new_layout);
	}
	/**
	 * get site layout setting
	 */
	public function get_layout () {
		$layout	=	 $this->get_option('et_layout', false);
		if( empty ($layout)) return 'content-sidebar';
		return $layout;
	}
	/*
	 * get and set facebook page link
	 */
	public function set_facebook_link ( $new_link) {
		if( self::validate('url', $new_link) || $new_link == '' )
			return $this->update_option('et_facebook_link', $new_link);
		return false;
	}
	public function get_facebook_link () {
		return $this->get_option('et_facebook_link');
	}
	/**
	 * set twitter account
	 */
	public function set_twitter_account	( $new_account ) {
		if( self::validate('url', $new_account) || $new_account == '' )
			return $this->update_option('et_twitter_account', $new_account);
		return false;
	}
	/**
	 * get twitter account
	 */
	public function get_twitter_account () {
		return $this->get_option('et_twitter_account');
	}
	/**
	 * set and get google plus link page
	 */
	public function set_google_plus ($new_value) {
		if( self::validate('url', $new_value) || $new_value == '')
			return $this->update_option('et_google_plus', $new_value);
		return false;
	}
	/**
	 * get google plus page link
	 */
	public function get_google_plus () {
		return $this->get_option('et_google_plus');
	}

	/**
	 * set and get google plus link page
	 */
	public function set_google_analytics ($new_value) {
		$new_value	= stripcslashes($new_value);
		return $this->update_option('et_google_analytics', $new_value);
	}
	/**
	 * get google plus page link
	 */
	public function get_google_analytics () {
		return $this->get_option('et_google_analytics');
	}

	/**
	 * copyright
	 * @param unknown_type $new_value
	 */
	public function set_copyright ( $new_value ) {
		return $this->update_option('et_copy_right', $new_value);
	}
	/**
	 * get copyright
	 */
	public function get_copyright () {
		$copy = $this->get_option('et_copyright');
		return empty($copy) ? "&copy; copyright <a href='http://www.enginethemes.com' target='_blank'>EngineTheme</a>" : $copy;
	}
	
	/**
	 * Get customization profile 
	 */
	public function get_customization(){
		return $this->get_option('et_customization');
	}

	/**
	 * Update customization profile
	 */
	public function set_customization(array $arr){
		$this->update_option($this->prefix . 'customization', $arr);
	}

	/**
	 * Update color schemes in customization menu
	 */
	public function set_color_schemes(array $opt){
		$this->update_option($this->prefix .'color_schemes', $opt);
	}

	/**
	 * Retrieve Color schemes in customization
	 */
	public function get_color_schemes(){
		return $this->get_option( $this->prefix . 'color_schemes');
	}

	/**
	 * Get choosen color index in customization
	 */
	public function get_choosen_color(){
		return $this->get_option($this->prefix .'choosen_color');
	}
	/**
	 * Update new choosen color index in customization
	 */
	public function set_choosen_color(int $index){
		$this->update_option($this->prefix . 'choosen_color', $index );
	}

	/**
	 * Retrieve job type colors list
	 */
	public function get_job_type_colors(){
		return $this->get_option($this->prefix . 'job_type_colors');
	}
	/**
	 * Update job type colors list
	 */
	public function set_job_type_colors($opt){
		$this->update_option($this->prefix . 'job_type_colors', $opt);
	}

	/**
	 * Retrieve job type colors list
	 */
	public function get_custom_style(){
		return $this->get_option($this->prefix . 'custom_style');
	}

	public function set_custom_style($opt){
		return $this->update_option($this->prefix . 'custom_style', $opt);
	}

	/**
	 * setup wizard process
	 */
	public function get_setup_process(){
		return get_option('et_setup_process', false );
		//return $this->get_option($this->prefix . 'setup_process');
	}

	/**
	 * 
	 */
	public function set_setup_process($value){
		return update_option( $this->prefix . 'setup_process', $value );
		//$this->update_option($this->prefix . 'setup_process', $value);
	}
}

class ET_JobEngineMailTemplate extends ET_Options 
{
	private $prefix;
	protected $opt_keys;
	
	public function __construct() {
		global $et_global;
		$this->opt_keys	=	array (
			'et_register_mail' 				=>	 __("Register Email template",ET_DOMAIN),
			'et_forgot_pass_mail'			=> 	 __("Forgot-password Email template",ET_DOMAIN),
			'et_reset_pass_mail'			=>	 __("Reset-password Email template",ET_DOMAIN),
			'et_apply_mail'					=>	 __("Apply-for-job Email template",ET_DOMAIN),
			'et_remind_mail'				=>	 __("Remind-job Email template",ET_DOMAIN),
			'et_approve_mail'				=>	 __("Approve-job Email template",ET_DOMAIN),
			'et_reject_mail'				=>	 __("Reject-job Email template",ET_DOMAIN),
			'et_archive_mail'				=>	 __("Archive-job Email template",ET_DOMAIN),
			'et_cash_notification_mail'		=>	 __("Cash notification mail",ET_DOMAIN)
		);
		$this->prefix = $et_global['db_prefix'];
		parent::__construct('et_jobengine_mailtemplate');
	}
	/**
	 * update mail template settings
	 * @param string $mail : mail type
	 * @param string $value : new mail value
	 */
	public function update_mail_template ( $mail, $value ) {
		$value		=	stripcslashes($value);
		$key		=	$this->prefix.$mail;
		
		$opt_key	=	$this->opt_keys;
		//echo $key;
		if(isset($opt_key[$key])) {
			return $this->update_option($key, $value);
		}
		return false;
	}

	function reset_mail_template ( $mail) {
		$new_value	=	'';
		switch ($mail) {
			case 'et_register_mail':
				return $this->set_register_mail ( $new_value, true );

			case 'et_forgot_pass_mail':
				return $this->set_forgot_pass_mail ( $new_value, true );
				

			case 'et_reset_pass_mail':
				return $this->set_reset_pass_mail ( $new_value, true );

			case 'et_apply_mail':
				return $this->set_apply_mail( $new_value, true );

			case 'et_remind_mail':
				return $this->set_remind_mail ( $new_value, true );

			case 'et_approve_mail':
				return $this->set_apply_mail ( $new_value, true );

			case 'et_reject_mail':
				return $this->set_reject_mail ( $new_value, true );

			case 'et_archive_mail':
				return $this->set_archive_mail ( $new_value, true );

			case 'et_cash_notification_mail' :
				return $this->set_cash_notification_mail ($new_value, true );

			default:
				return false;
		}
	}

	public function set_cash_notification_mail ($new_value , $default ) {
		if($default) {
			$new_value	=		__("<p>Dear [display_name],</p><p>[cash_message]</p><p>Sincerely,<br/> [blogname].</p>", ET_DOMAIN);
		}
		$this->update_option('et_cash_notification_mail', $new_value);
		return $new_value;
	}
	public function get_cash_notification_mail () {
		$default	=	__("<p>Dear [display_name],</p><p>[cash_message]</p><p>Sincerely,<br/> [blogname].</p>", ET_DOMAIN);
		return stripslashes(( $this->get_option('et_cash_notification_mail', $default) ));
	}

	public function get_register_mail ( ) {
		//$default	=	__('<p>Hello [display_name],</p><p>You have just registered an account;in [blogname] successfully.</p><p>Here is your account information:</p><ol><li>Username: [user_login]</li><li>Email: [user_email]</li></ol><p>Thanks and welcome you to [blogname].</p>', ET_DOMAIN);
		$default	=	__("<p>Hello [display_name],</p><p>You have successfully registered an account with&nbsp;&nbsp;[blogname].&nbsp;Here is your account information:</p><ol><li>Username: [user_login]</li><li>Email: [user_email]</li></ol><p>Thank you and welcome to [blogname].</p>", ET_DOMAIN);
		return stripslashes(( $this->get_option('et_register_mail', $default) ));
	}
	
	public function set_register_mail ( $new_value, $default ) {
		if($default) {
			$new_value	=	__("<p>Hello [display_name],</p><p>You have successfully registered an account with&nbsp;&nbsp;[blogname].&nbsp;Here is your account information:</p><ol><li>Username: [user_login]</li><li>Email: [user_email]</li></ol><p>Thank you and welcome to [blogname].</p>", ET_DOMAIN);
		}
		$this->update_option('et_register_mail', $new_value);
		return $new_value;
	}
	
	public function get_forgot_pass_mail ( ) {
		//$default	=	__('<p>Hello [display_name],</p><p>You have just sent a request for recovering your password in [blogname]. if this was not your request, please ignore this email address, otherwise, please click on the following URL to create your new password:</p><p>[activate_url]</p><p>Regards,<br />[blogname]</p>', ET_DOMAIN );
		$default	=	__("<p>Hello [display_name],</p><p>You have just sent a request to recover the password associated with your account in [blogname]. If you did not make this request, please ignore this email; otherwise, click the link below to create your new password:</p><p>[activate_url]</p><p>Regards,<br />[blogname]</p>", ET_DOMAIN);
		return stripslashes(( $this->get_option('et_forgot_pass_mail', $default) ));
	}
	
	public function set_forgot_pass_mail ( $new_value, $default ) {
		if($default) {
			$new_value	=	__("<p>Hello [display_name],</p><p>You have just sent a request to recover the password associated with your account in [blogname]. If you did not make this request, please ignore this email; otherwise, click the link below to create your new password:</p><p>[activate_url]</p><p>Regards,<br />[blogname]</p>", ET_DOMAIN);
		}
		$this->update_option('et_forgot_pass_mail', $new_value);
		return $new_value;
	}
	
	public function get_reset_pass_mail ( ) {
		//$default	=	__('<p>Hello [display_name],</p><p>You have just changed your password successfully. You can now log into our website at [site_url].</p><p><span>Sincerely,<br /></span>[blogname]</p>', ET_DOMAIN );
		$default	=	__("<p>Hello [display_name],</p><p>You have successfully changed your password. Click this link&nbsp;[site_url] to login to your [blogname]'s account.</p><p>Sincerely,<br />[blogname]</p>", ET_DOMAIN);
		return stripslashes(( $this->get_option('et_reset_pass_mail', $default) ));
	}
	
	public function set_reset_pass_mail ( $new_value, $default ) {
		if($default) {
			$new_value	=	__("<p>Hello [display_name],</p><p>You have successfully changed your password. Click this link&nbsp;[site_url] to login to your [blogname]'s account.</p><p>Sincerely,<br />[blogname]</p>", ET_DOMAIN);
		}
		$this->update_option('et_reset_pass_mail', $new_value);
		return $new_value;
	}
	/**
	 * get apply job mail template, this mail will be send to companies when 
	 * a job seeker apply their jobs
	 */
	public function get_apply_mail ( ) {
		$default	=	__("<p>Dear [display_name],</p><p>Your job offer for [job_title] posted in [blogname] has a new application.</p><p>Here are the applicant's information:</p><ol><li>Name: &nbsp;[seeker_name]</li><li>Email address: [seeker_mail]</li><li>Note: [seeker_note]</li></ol><p>The files of the applicant are also attached in this email.</p><p>And here is the link to your job offer: [job_link].</p><p>Sincerely,<br />[blogname]</p>", ET_DOMAIN);
		return stripslashes(( $this->get_option('et_apply_mail', $default) ));
	}
	/**
	 * set apply job mail template
	 * @param $new_value : string new mail template value
	 * @param $default : bool if true, mail template will be reset to default
	 */
	public function set_apply_mail ( $new_value, $default ) {
		if($default) {
			$new_value	=	__("<p>Dear [display_name],</p><p>Your job offer for [job_title] posted in [blogname] has a new application.</p><p>Here are the applicant's information:</p><ol><li>Name: &nbsp;[seeker_name]</li><li>Email address: [seeker_mail]</li><li>Note: [seeker_note]</li></ol><p>The files of the applicant are also attached in this email.</p><p>And here is the link to your job offer: [job_link].</p><p>Sincerely,<br />[blogname]</p>", ET_DOMAIN);
		}
		$this->update_option('et_apply_mail', $new_value);
		return $new_value;
	}
	/**
	 * get remind job mail template, this mail template will be used to send 
	 * when job seeker remind a job
	 */
	public function get_remind_mail ( ) {
		$default	=	__('<p>Hello [seeker_email],</p><p>You have just saved a job in [blogname] for later viewing. Here are the job information:</p><ol><li>Job title: [job_title]</li>	<li>Job link: [job_link]</li><li>Company name: [company]</li><li>Your note: [remind_note]</li></ol><p>Regards,<br />[blogname]</p>', ET_DOMAIN);
		return stripslashes(( $this->get_option('et_remind_mail', $default)));
	}
	/**
	 * set remind job mail template
	 * @param $new_value : string new mail template value
	 * @param $default : bool if true, mail template will be reset to default
	 */
	public function set_remind_mail ( $new_value, $default ) {
		if($default) {
			$new_value	=	__('<p>Hello [seeker_email],</p><p>You have just saved a job in [blogname] for later viewing. Here are the job information:</p><ol><li>Job title: [job_title]</li>	<li>Job link: [job_link]</li><li>Company name: [company]</li><li>Your note: [remind_note]</li></ol><p>Regards,<br />[blogname]</p>', ET_DOMAIN);
		}
		$this->update_option('et_remind_mail', $new_value);
		return $new_value;
	}
	/**
	 * get approve job mail template, this mail template will be sent to 
	 * companies when their jobs approved
	 */
	public function get_approve_mail ( ) {
		$default	=	__('<p>Dear [display_name],</p><p>Your job [job_title] posted in [blogname] has been approved.</p><p>You can follow this link: [job_link] to view your job offer.</p><p>Sincerely,<br />[blogname]</p>', ET_DOMAIN);
		return stripslashes(( $this->get_option('et_approve_mail', $default) ));
	}
	/**
	 * set approve mail template
	 * @param $new_value : string new mail template value
	 * @param $default : bool if true, mail template will be reset to default
	 */
	public function set_approve_mail ( $new_value, $default ) {
		if($default) {
			$new_value	=	__('<p>Dear [display_name],</p><p>Your job [job_title] posted in [blogname] has been approved.</p><p>You can follow this link: [job_link] to view your job offer.</p><p>Sincerely,<br />[blogname]</p>', ET_DOMAIN);
		}
		$this->update_option('et_approve_mail', $new_value);
		return $new_value;
	}
	/**
	 * get archive job mail template, this mail template will be sent to 
	 * companies when their jobs expired or archived
	 */
	public function get_archive_mail ( ) {
		$default	=	__('<p>Dear [display_name],</p><p>Your job: [job_title] in [blogname] has been archived due to expiration or manual administrative action.</p><p>If you want to continue displaying this job in our website, please go to your dashboard at [dashboard] to renew your job offer.</p><p>Sincerely,<br />[blogname]</p>', ET_DOMAIN);
		return stripslashes(( $this->get_option('et_archive_mail', $default)));
	}
	/**
	 * set archive job mail template
	 * @param $new_value : string new mail template value
	 * @param $default : bool if true, mail template will be reset to default
	 */
	public function set_archive_mail ( $new_value, $default ) {
		if($default) {
			$new_value	=	__('<p>Dear [display_name],</p><p>Your job: [job_title] in [blogname] has been archived due to expiration or manual administrative action.</p><p>If you want to continue displaying this job in our website, please go to your dashboard at [dashboard] to renew your job offer.</p><p>Sincerely,<br />[blogname]</p>', ET_DOMAIN);
		}
		$this->update_option('et_archive_mail', $new_value);
		return $new_value;
	}
	/**
	 * get reject job mail template, this mail template will be sent to companies
	 * when their job rejected by site
	 */
	public function get_reject_mail ( ) {
		$default	=	__('<p>Dear [display_name],</p><p>Your job [job_title] posted in [blogname] has been rejected. Noted reason: [reason]</p><p>Please contact the administrators via [admin_email] for more information, or go to your dashboard at [dashboard] to edit your job offer and post it again.</p><p>Sincerely,<br />[blogname]</p>', ET_DOMAIN);
		return stripslashes(( $this->get_option('et_reject_mail', $default)));
	}
	/**
	 * set reject mail template
	 * @param $new_value : string new mail template value
	 * @param $default : bool if true, mail template will be reset to default
	 */
	public function set_reject_mail ( $new_value, $default ) {
		if($default) {
			$new_value	=	__('<p>Dear [display_name],</p><p>Your job [job_title] posted in [blogname] has been rejected. Noted reason: [reason]</p><p>Please contact the administrators via [admin_email] for more information, or go to your dashboard at [dashboard] to edit your job offer and post it again.</p><p>Sincerely,<br />[blogname]</p>', ET_DOMAIN);
		}
		$this->update_option('et_reject_mail', $new_value);
		return $new_value;
	}
	
	
	
}

endif;
if(class_exists("ET_GeneralOptions")) :
	class ET_JobOptions extends ET_GeneralOptions
	{
		public static $instance	=	null;
		public static function get_instance () {
			if(self::$instance == null ) {
				self::$instance	=	new ET_JobOptions ();
			}
			return self::$instance;
		}

		function __construct() {
			$this->opt_keys	=	array (
				'et_pending_job'			=> __("Pending Job",ET_DOMAIN),
				'et_featured_job'			=>	__("Featured Job",ET_DOMAIN),
				'et_post_job_contact_widget' => __('Contact Widget', ET_DOMAIN),		
				'et_post_job_payment_follow_widget' => __('Payment Follow Widget', ET_DOMAIN),
				'et_upgrade_account_widget'	=>  __("Upgrade account", ET_DOMAIN),
				'et_use_captcha'			=>  __("Use captcha", ET_DOMAIN),
				'et_fb_login'			=> __("Login by Facebook"),
				'et_tw_login'			=> __("Login by Twitter"),
			) ;
			parent::__construct('et_job_opts');
		}
		/**
		 * if jobengine use pending job, it will return true. 
		 * if it set to be false, company post job will not be pending 
		 */
		public function use_pending () {
			return $this->get_option('et_pending_job', true);
		}
		/**
		 * set pending job option
		 * @param bool $value : true or false
		 */
		public function set_use_pending ( $value ) {
			return $this->update_option('et_pending_job', $value );
		}

		/**
		 * if jobengine use pending job, it will return true. 
		 * if it set to be false, company post job will not be pending 
		 */
		public function use_pending_job_edit () {
			return $this->get_option('et_pending_job_edit', false);
		}
		/**
		 * set pending job option
		 * @param bool $value : true or false
		 */
		public function set_use_pending_job_edit ( $value ) {
			return $this->update_option('et_pending_job_edit', $value );
		}

		public function use_captcha () {
			return $this->get_option('et_use_captcha', false);
		}

		public function set_use_captcha ($value) {
			$google_captcha	=	ET_GoogleCaptcha::get_api();
			if(empty($google_captcha['private_key']) || empty($google_captcha['public_key']))
				$value = 0;
			return $this->update_option('et_use_captcha', $value );
		}

		/**
		* set fb login status
		*/
		function set_fb_login($value){
			$app_id = ET_FaceAuth::get_app_id();
			if(empty($app_id))
				$value = 0;
			
			$this->update_option('et_fb_login',$value);
		}
		function get_fb_login(){		
			return $this->get_option('et_fb_login',false);
		}

		/**
		* set twiter login status
		*/
		function set_tw_login($value){
			$app_key 	= ET_TwitterAuth::get_twitter_key();
			$secret_key = ET_TwitterAuth::get_twitter_secret();

			if( empty($app_key) || empty($secret_key) )
				$value = 0;
			
			$this->update_option('et_tw_login',$value);
		}

		function get_tw_login(){
			return $this->get_option('et_tw_login',false);
		}

		/**
		 * if job engine use featured job, the option featured job will be use to upgrade 
		 * job to be featured
		 */
		public function use_feature () {
			return true;
			return $this->get_option('et_featured_job', true);
		}
		/**
		 * set use feature option
		 * @param bool $new_value
		 */
		public function set_use_feature ($new_value) {
			return $this->update_option('et_featured_job', $new_value);
		}
		/**
		* post job page have a sidebar column, this function set a contact widget html value
		* @param string html : html string
		*/
		public function set_contact_widget ($new_value) {
			return $this->update_option('et_post_job_contact_widget', $new_value);
		}

		public function get_contact_widget () {
			$default 	=	__('You can modify this text by clicking the edit button below or delete this box using the delete button', ET_DOMAIN);

			return $this->get_option('et_post_job_contact_widget',$default);
		}
		/**
		*	post job page have a sidebar column, this function set payment follow html widget
		*/
		public function set_payment_follow_widget ($new_value) {
			return $this->update_option('et_post_job_payment_follow_widget', $new_value);
		}
		public function get_payment_follow_widget () {
			global $et_global;
			
			$default 	=	__('You can modify this text by clicking the edit button below or delete this box using the delete button', ET_DOMAIN);
			return $this->get_option ('et_post_job_payment_follow_widget', $default);
		}
		
		public function get_post_job_sidebar () {
			return $this->get_option ('et_post_job_sidebar',
					array (
						'post_job_wid_1' => __('You can modify this text by clicking the edit button below or delete this box using the delete button', ET_DOMAIN))
					);
		}
		
		public function set_post_job_sidebar ( $new_value ) {
			return $this->update_option('et_post_job_sidebar', $new_value);
		}

		public function get_upgrade_account_sidebar () {
			return $this->get_option ('et_upgrade_account_widget',
					array (
						'post_job_wid_1' => __('You can modify this text by clicking the edit button below or delete this box using the delete button', ET_DOMAIN))
					);
		}
		
		public function set_upgrade_account_sidebar ( $new_value ) {
			return $this->update_option('et_upgrade_account_widget', $new_value);
		}
		
		public function generate_post_job_widget_id ( ) {
			$id_arr		=	array ();
			$sidebar	=	$this->get_post_job_sidebar();
			foreach ($sidebar as $key => $value) {
				$id_arr[]	=	intval(str_ireplace('post_job_wid_', '', $key));
			}
			if ( empty($id_arr) ) {
				$id	=	1;
			} else {
				$id	=	$this->current_id_number($id_arr);
			}
			$id_arr[]	=	$id;
			
			$this->update_option('et_post_job_widget_id', $id_arr);
			
			$base_id	=	'post_job_wid_';
			
			return $base_id.$id;
		}
		
		public function get_dashboard_sidebar () {
			return $this->get_option ('et_dashboard_sidebar', 
					array ('dashboard_wid_1' => __('You can modify this text by clicking the edit button below or delete this box using the delete button', ET_DOMAIN)));
		}
		
		public function set_dashboard_sidebar ( $new_value ) {
			return $this->update_option('et_dashboard_sidebar', $new_value);
		}
		
		public function generate_dashboard_widget_id ( ) {
			
			$id_arr		=	array ();
			$sidebar	=	$this->get_dashboard_sidebar();
			foreach ($sidebar as $key => $value) {
				$id_arr[]	=	intval(str_ireplace('dashboard_wid_', '', $key));
			}
			if ( empty($id_arr) ) {
				$id	=	1;
			} else {
				$id	=	$this->current_id_number($id_arr);
			}
			$id_arr[]	=	$id;
			
			$this->update_option('et_dashboard_widget_id', $id_arr);
			
			$base_id	=	'dashboard_wid_';
			
			return $base_id.$id;
		}
		
		protected function current_id_number ( $a	=	array () ) {
			$max =	max( $a );
			$min =	min ($a) ;
			
			if( $max == $min && $max > 1 ) {
				return $max -1;
			} else {
				return $max +1;
			}
			for( $i= 1; $i <= $max +1 ; $i++ ){
				if( !in_array($i, $a )){
					
					return $i;
				}
			}
			return 1;
		}
		public function add_currency ($code, $value) {			
			$currency_list			=	$this->get_option('et_currency_list', array ());$currency_list =array ();
			$currency_list[$code]	=	$value;
			$this->update_option('et_currency_list', $currency_list);
		}
		public function get_currency_list () {
			return 	$this->get_option('et_currency_list', array ());
		}
	}
endif;
