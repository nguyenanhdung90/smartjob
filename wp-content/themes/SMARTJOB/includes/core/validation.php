<?php
class ET_Validator{
	var $default_validators;
	var $extra_validators;

	function __construct(){
		add_action('wp_ajax_nopriv_et_username_check_used', array($this, 'checkUserNameIsUsed'));
		add_action('wp_ajax_et_username_check_used', array($this, 'checkUserNameIsUsed'));

		add_action('wp_ajax_nopriv_et_email_check_used', array($this, 'checkEmailIsUsed'));
		add_action('wp_ajax_et_email_check_used', array($this, 'checkEmailIsUsed'));
	}

	function checkUserNameIsUsed () {
		header( 'HTTP/1.0 200 OK' );
		header( 'Content-type: application/json' );
		$user	=	get_user_by( 'login', $_REQUEST['user_name'] );
		$used	=	true;
		if($user) {
			$used	=	false;
		}
		echo json_encode($used);
		exit;
	}

	function checkEmailIsUsed () {
		header( 'HTTP/1.0 200 OK' );
		header( 'Content-type: application/json' );
		$user	=	get_user_by( 'email', $_REQUEST['user_email'] );
		$used	=	true;
		if($user) {
			$used	=	false;
		}
		echo json_encode($used);
		exit;
	}

	function register_validator($type, $callback){
		if ( !isset($this->extra_validators[$type]) )
			$this->extra_validators[$type] = $callback;
	}

	/**
	 * 
	 */
	function unregister_validator($type){
		if ( isset($this->extra_validators[$type]) )
			unset($this->extra_validators[$type]);
	}

	/**
	 * 
	 */
	function validate($type, $value){
		$intpattern 	= "/^[0-9]+$/";
		$emailpattern 	= "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.([a-zA-Z.]{2,5})*$/";
		$decimalpattern = "/^[0-9]+.?[0-9]*$/";

		switch ($type) {
			case 'int':
				if ( preg_match($intpattern, $value) )
					return true;
				break;

			case 'email':
				if ( preg_match($emailpattern, $value) )
					return true;
				break;
			case 'url' :
			case 'link' :
				$url_pattern =	"/[http|https]:\/\/+([a-zA-Z0-9])*/";
				return preg_match($url_pattern, $value);
				break;
			case 'decimal':
				if ( preg_match($decimalpattern, $value) )
					return true;
			
			default:
				if ( isset( $this->extra_validators[$type]) && is_callable($this->extra_validators[$type]) )
					return call_user_func($this->extra_validators[$type], $value);
				else 
					return false;
				break;
		}

		return false;
	}
}
global $et_global;
$et_global['validator'] = new ET_Validator();

/**
 * Register a custom validator
 * @param $type name of custom validator
 * @param $callback the function name that validate values
 * @since 1.0
 */
function et_register_validator($type, $callback){
	global $et_global;
	$validator = $et_global['validator'];
	$validator->register_validator($type, $callback);
}

/**
 * Validate a value
 * @param $type name of validation
 * @param $value value for validation
 * @since 1.0
 */
function et_validate($type, $value=''){
	global $et_global;
	$validator = $et_global['validator'];
	return $validator->validate( $type, $value );
}

?>