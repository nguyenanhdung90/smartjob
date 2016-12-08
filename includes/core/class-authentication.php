<?php 

/**
 * 
 */
class ET_UserField {
	var $name;
	var $title;
	var $description;
	var $type;
	var $input_type;
	var $roles;

	public static $prefix = 'et_';
	protected $metakey;

	function __construct($name, $args){
		$args = wp_parse_args($args, array(
			'title' 			=> '',
			'description' 		=> '',
			'type' 				=> '',
			'roles' 			=> 'all',
			'input_type' 		=> 'text',
			'display_profile' 	=> true
			) );

		$this->name 		= $name;
		$this->title 		= $args['title'];
		$this->description 	= $args['description'];
		$this->type 		= $args['type'];
		$this->roles 		= $args['roles'];
		$this->display_profile = $args['display_profile'];
		$this->metakey 		= self::$prefix . $name;
	}

	function __get($name){
		$fields = array( 'name', 'title', 'metakey', 'roles', 'metakey');
		if ( in_array($name, $fields) )
			return $this->$name;
		else 
			return false;
	}

	/**
	 * Check if this field is available for given role
	 * @since 1.0
	 */
	public function has_role($role = ''){
		global $current_user;

		if ( $role == '' && !empty($current_user->roles[0] ))
			$role = $current_user->roles[0];

		if ( !is_array($this->roles) && $this->roles == 'all' ){
			return true;
		}else if( is_array($this->roles) && in_array($role, $this->roles) ) {
			return true;
		}else 
			return false;
	}

	/**
	 * 
	 */
	public function get_profile_field( $user ){
		$label = '<label for="">' . $this->title . '</label>';
		$input = $this->get_profile_input( $user );
		$html = "<tr>
			<th>{$label}</th>
			<td>
				{$input}
			</td>
			</tr>";

		return apply_filters('et_profile_field_' . $this->name, $html);
	}

	public function get_profile_input( $user ){
		$html = '';
		$value = esc_attr( get_the_author_meta( $this->metakey, $user->ID ) );
		switch ( $this->input_type ){
			default:
			case 'text':
				$html = '<input type="text" name="'.$this->metakey.'" id="'.$this->metakey.'" value="'.$value.'" class="regular-text" /><br />';
				break;
			case 'textarea':
				$html = '<textarea name="'.$this->metakey.'">'.$value.'</textarea>';
		}
		return apply_filters('et_profile_input_' . $this->name, $html);
	}
}

class ET_UserField_Factory {
	var $fields = array();

	function __construct(){
		add_action( 'show_user_profile', array($this, 'display_fields') );
		add_action( 'edit_user_profile', array($this, 'display_fields') );
		add_action( 'personal_options_update', array($this, 'save_profile_fields') );
		add_action( 'edit_user_profile_update', array($this, 'save_profile_fields') );
	}

	/**
	 * Return registerd fields
	 * @since 1.0
	 */
	public function get_fields($role = 'all'){
		$return = array();
		foreach ($this->fields as $key => $field) {
			if ( $field->has_role($role) )
			$return[$key] = (object)array(
				'name' => $field->name,
				'title' => $field->description,
				'type' => $field->type,
				'roles' => $field->roles,
				'metakey' => $field->metakey
				);
		}
		return $return;
	}

	public function has_field($field, $role = ''){
		return isset($this->fields[$field]);
	}

	/**
	 * Register new field
	 * @since 1.0
	 */
	public function register($name, $args){
		$field = new ET_UserField($name, $args);
		$this->fields[$name] = & $field;
	}

	/**
	 * Register existed field
	 * @since 1.0
	 */
	public function unregister($name){
		if ( !empty($this->fields[$name]) )
			unset($this->fields[$name]);
	}

	public function display_fields( $user ){
		global $current_user;
		if ( empty($current_user->roles[0]) ) return;

		$current_role = $current_user->roles[0];

		echo '<table class="form-table">';
		foreach ($this->fields as $key => $field) {
			if ( $field->has_role($user->roles[0]) && $field->display_profile ){
				echo $field->get_profile_field($user);
			}
		}
		echo '</table>';
	}

	public function save_profile_fields($user_id){
		if ( !current_user_can( 'edit_user', $user_id ) )
			return false;

		foreach ($this->fields as $key => $field) {
			$metakey = $field->metakey;
			if ( isset( $_POST[$metakey] ) )
				update_user_meta($user_id, $metakey, $_POST[$metakey]);
		}
	}

	public function save_fields($user_id, $fields){
		$default = array('user_url', 'display_name');
		$extra = array();
		try {
			
			foreach ($fields as $key => $value) {
				$this->save_field($user_id, $key, $value);
			}

		} catch (Exception $e) {
			return false;
		}
	}

	public function save_field($user_id, $key, $value){
		if ( isset($this->fields[$key]) ){
			$metakey	= $this->fields[$key]->metakey;
			if( !empty($metakey) ){
				update_user_meta($user_id, $this->fields[$key]->metakey, $value);
			}
		}
	}
}

global $et_global;
$et_global['user_fields'] = new ET_UserField_Factory();

/**
 * Register a user field
 * @param $name : the name of the field, must contain alphabel and number character only
 * @param $args : array of arguments
 *  	- title : title of the field
 * 		- description : description if nessesary
 * 		- type : value type
 * 		- role : role that this field applied for
 */
function et_register_user_field( $name, $args ){
	global $et_global;
	$user_fields = $et_global['user_fields'];
	$args = wp_parse_args($args, array(
		'title' 		=> '',
		'description' 	=> '',
		'type' 			=> 'int',
		'role' 			=> 'all',
		'display_profile' => true
		));
	$user_fields->register($name, $args);
}

/**
 * Return user extra information by his role
 * @param $role 
 *
 * @since 1.0
 */
function et_get_user_fields_by_role($role = 'all'){
	global $et_global;
	$user_fields = $et_global['user_fields'];

}

/**
 * Return user's field
 * @param $user_id user ID
 * @param $field field name to get
 * @since 1.0
 */
function et_get_user_field($user_id, $field){
	$prefix = ET_UserField::$prefix;
	return get_the_author_meta( $prefix . $field, $user_id );
}
/**
 * Return current user's field
 * @param $field field name to get
 * @since 1.0
 */
function et_get_current_user_field($field){
	global $current_user;
	if ( !empty($current_user->ID) )
		return et_get_user_field($current_user->ID, $field);
	else 
		return false;
}

/**
 * Log a user in in via user information.
 * @param $username username to log in
 * @param $password password
 * @param $remember remember log in for later access
 * @param $secure_cookie Whether to use secure cookie.
 * @return WP_User on success or WP_Error on failure
 * 
 * @since 1.0
 */
function et_login( $username, $password, $remember = false, $secure_cookie = false ){
	global $current_user;

	// check users if he is member of this blog
	$user = get_user_by('login', $username);
	if ( !$user || !is_user_member_of_blog( $user->ID ) )
		return new WP_Error('login_failed', "Login failed");

	$creds['user_login'] = $username;
	$creds['user_password'] = $password;
	$creds['remember'] = true;
	
	//$result = &wp_signon( $creds, $secure_cookie );
	$result = wp_signon( $creds, $secure_cookie );
	
	if ( $result instanceof WP_User )
		$current_user = $result;
	
	return $result;
}

/**
 * Perform log user in via email
 * @param $email user's email
 * @param $password password for log-in
 * @param @remember allow auto log for next time 
 * @param @secure_cookie ...
 * 
 * @since 1.0
 */
function et_login_by_email( $email, $password, $remember = false, $secure_cookie = false ){
	$user = get_user_by('email', $email);
	if ( $user != false )
		return et_login($user->user_login, $password, $remember, $secure_cookie);
	else 
		return new WP_Error(403, __('This email address was not found.', ET_DOMAIN));
}

/**
 * Register user by given user data
 * @param array $user information of new user:
 * 	- username : new user name
 * 	- password : new password
 * 	- email : email
 * @since 1.0
 *
 */
function et_register( $userdata, $role = 'subscriber', $auto_login = false ){
	extract($userdata);
	if (!preg_match("/^[a-zA-Z0-9_]+$/", $userdata['user_login'])){
		return new WP_Error('username_invalid', __('Username is invalid', ET_DOMAIN));
	}
	$userdata['role']	= $role;
	$result = wp_insert_user( $userdata );
	
	// if creating user false
	if ( $result instanceof WP_Error ){
		return $result;
	}

	do_action('et_after_register', $result , $role );

	// auto login
	if ( $auto_login ) {
		et_login($user_login , $user_pass, true);
	}
	
	// then return user id
	return $result;
}

/**
 * Confirm password reset and return validation key
 *
 * @since 1.0
 */
function et_create_reset_password_code($user_id){
	global $wpdb;

	$activation_key = &wp_generate_password( 20, false );
	$result = $wpdb->update( $wpdb->users, array('user_activation_key' => $activation_key ), array('ID' => $user_id), array('%s'), array('%d') );
	update_user_meta( $user_id, 'et_activation_key' , $activation_key );

	do_action('et_after_create_reset_pass_code', $user_id, $activation_key);

	return $activation_key;
}


/**
 * Handles sending password retrieval email to user.
 *
 * @uses $wpdb WordPress Database object
 *
 * @return bool|WP_Error True: when finish. WP_Error on error
 */

function et_retrieve_password() {


	global $wpdb, $current_site,$wp_hasher;

	$errors = new WP_Error();

	if ( empty( $_POST['user_login'] ) ) {
		$errors->add('empty_username', __('<strong>ERROR</strong>: Enter username or email address.', ET_DOMAIN));
	} else if ( strpos( $_POST['user_login'], '@' ) ) {
		$user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
		if ( empty( $user_data ) )
			$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.', ET_DOMAIN));
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_user_by('login', $login);
	}

	do_action('lostpassword_post');

	if ( $errors->get_error_code() )
		return $errors;

	if ( !$user_data ) {
		$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or email address.', ET_DOMAIN));
		return $errors;
	}

	// redefining user_login ensures we return the right case in the email
	$user_login = $user_data->user_login;
	$user_email = $user_data->user_email;

	do_action('retreive_password', $user_login);  // Misspelled and deprecated
	do_action('retrieve_password', $user_login);

	$allow = apply_filters('allow_password_reset', true, $user_data->ID);

	if ( ! $allow )
		return new WP_Error('no_password_reset', __('Password reset is not allowed for this user', ET_DOMAIN));
	else if ( is_wp_error($allow) )
		return $allow;
 
	$key = wp_generate_password( 20, false );

	/**
	 * Fires when a password reset key is generated.
	 *
	 * @since 2.5.0
	 *
	 * @param string $user_login The username for the user.
	 * @param string $key        The generated password reset key.
	 */

	do_action( 'retrieve_password_key', $user_login, $key ); 


	// Now insert the key, hashed, into the DB.
	if ( empty( $wp_hasher ) ) {
		require_once ABSPATH . 'wp-includes/class-phpass.php';
		$wp_hasher = new PasswordHash( 8, true );

	}
	$hashed = $wp_hasher->HashPassword( $key );
	$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

	
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	$headers .= "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n";
		
	$message = __('Someone requested that the password be reset for the following account:', ET_DOMAIN) . "\r\n\r\n";
	$message .= network_home_url( '/' ) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s', ET_DOMAIN), $user_login) . "\r\n\r\n";
	$message .= __('If this was a mistake, just ignore this email and nothing will happen.',ET_DOMAIN) . "\r\n\r\n";
	$message .= __('To reset your password, visit the following link:', ET_DOMAIN) . "\r\n\r\n";
	$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";
	$site = apply_filters('et_reset_password_link',  network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login'), $key, $user_login );
	$message .= '<' . $site . ">\r\n"; 

	if ( is_multisite() )
		$blogname = $GLOBALS['current_site']->site_name;
	else		
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$title 		= sprintf( __('[%s] Password Reset', ET_DOMAIN), $blogname );

	$title 		= apply_filters('et_retrieve_password_title', $title);
	$message 	= apply_filters('et_retrieve_password_message', $message, $key, $user_data);

	if ( $message && !wp_mail($user_email, $title, $message ,$headers ) )
		wp_die( __('The email could not be sent.', ET_DOMAIN) . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...', ET_DOMAIN) );

	return true;
}

/**
 * Retrieves a user row based on password reset key and login
 *
 * A key is considered 'expired' if it exactly matches the value of the
 * user_activation_key field, rather than being matched after going through the
 * hashing process. This field is now hashed; old values are no longer accepted
 * but have a different WP_Error code so good user feedback can be provided.
 *
 * @global wpdb $wpdb WordPress database object for queries.
 *
 * @param string $key       Hash to validate sending user's password.
 * @param string $login     The user login.
 * @return WP_User|WP_Error WP_User object on success, WP_Error object for invalid or expired keys.
 */
function et_check_password_reset_key($key, $login) {
	global $wpdb, $wp_hasher;

	$key = preg_replace('/[^a-z0-9]/i', '', $key);

	if ( empty( $key ) || !is_string( $key ) )
		return new WP_Error('invalid_key', __('Invalid key'));

	if ( empty($login) || !is_string($login) )
		return new WP_Error('invalid_key', __('Invalid key'));

	$row = $wpdb->get_row( $wpdb->prepare( "SELECT ID, user_activation_key FROM $wpdb->users WHERE user_login = %s", $login ) );
	if ( ! $row )
		return new WP_Error('invalid_key', __('Invalid key'));

	if ( empty( $wp_hasher ) ) {
		require_once ABSPATH . WPINC . '/class-phpass.php';
		$wp_hasher = new PasswordHash( 8, true );
	}

	if ( $wp_hasher->CheckPassword( $key, $row->user_activation_key ) )
		return get_userdata( $row->ID );

	if ( $key === $row->user_activation_key ) {
		$return = new WP_Error( 'expired_key', __( 'Invalid key' ) );
		$user_id = $row->ID;

		/**
		 * Filter the return value of check_password_reset_key() when an
		 * old-style key is used (plain-text key was stored in the database).
		 *
		 * @since 3.7.0
		 *
		 * @param WP_Error $return  A WP_Error object denoting an expired key.
		 *                          Return a WP_User object to validate the key.
		 * @param int      $user_id The matched user ID.
		 */
		return apply_filters( 'password_reset_key_expired', $return, $user_id );
	}

	return new WP_Error( 'invalid_key', __( 'Invalid key' ) );
}

/**
 * Handles resetting the user's password.
 *
 * @param object $user The user
 * @param string $new_pass New password for the user in plaintext
 */
function et_reset_password( $user, $new_pass ) {
	/**
	 * Fires before the user's password is reset.
	 *
	 * @since 1.5.0
	 *
	 * @param object $user     The user.
	 * @param string $new_pass New user password.
	 */
	do_action( 'password_reset', $user, $new_pass );

	wp_set_password( $new_pass, $user->ID );
	update_user_option( $user->ID, 'default_password_nag', false, true );

	wp_password_change_notification( $user );
}


/**
 * Save update information of user
 *
 * @since 1.0
 */
function et_update_user($fields){
	global $et_global;
	$factory = $et_global['user_fields'];

	try {
		if ( isset($fields['ID']) ){
			$extra_field = array();
			foreach ($fields as $key => $value) {
				if ( $factory->has_field($key) ){
					$extra_field[$key] = $value;
					unset($fields[$key]);
				}
			}

			// update user
			$user_id = wp_update_user($fields);

			// update extra fields
			$factory->save_fields($user_id, $extra_field);

			return $user_id;
		}
		else {
			return false;
		}
	} catch (Exception $e) {
		return false;
	}
		
}

/**
 * return the default company data to response to client
 * @param  [int/WP_User] $user user ID or WP_USER object
 * @return [type]       array of user data
 * @since  1.0
 */
function et_create_user_response($user){
	if ( is_numeric( $user ) ){
		$user	= get_userdata( (int)$user );
	}
	if ( empty( $user->ID ) ){
	 	return;
	}
	else{
		$user_logo	= et_get_user_logo( $user->ID );

		$user_response	=	array(
			'id' 			=> $user->ID,
			'ID' 			=> $user->ID,
			//'user_email' 	=> $user->user_email,
			'display_name' 	=> $user->display_name,
			'user_url' 		=> $user->user_url,
			//'login_name' 	=> $user->user_login,
			'post_url'		=> get_author_posts_url( $user->ID ),
			'user_logo'		=> $user_logo,
			'description'	  => apply_filters('et_author_description', get_the_author_meta('description', $user->ID )),
		) ;
		return apply_filters('et_user_response' , $user_response , $user );
	}
}

function et_get_user_logo ($user){
	$general_opt = new ET_GeneralOptions();
	$default_logo	= $general_opt->get_default_logo();
	$default_user_logo = array(
		'small_thumb'	=> $default_logo,
		'company-logo'	=> $default_logo,
		'thumbnail'		=> $default_logo,
		'attach_id'		=> 0
	);

	if ( is_numeric( $user ) ){
		$user	= get_userdata( (int)$user );
	}
	if ( empty( $user->ID ) ){
	 	return $default_user_logo;
	}
	else{
		$user_logo	= et_get_user_field( $user->ID, 'user_logo' );
		if ( empty($user_logo) ){
			return $default_user_logo;
		}
		return $user_logo;
	}
}

/**
 * Return true if there is someone logged in, otherwise return false
 *
 * @since 1.0
 */
function et_is_logged_in(){
	global $current_user;
	return $current_user->ID > 0;
}
?>