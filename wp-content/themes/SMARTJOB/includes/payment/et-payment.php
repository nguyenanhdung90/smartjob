<?php
/**
# Version: this is the API version in the request.
# It is a mandatory parameter for each API request.
# The only supported value at this time is 2.3
*/

define('VERSION', '87.0');

// Ack related constants
define('ACK_SUCCESS', 'SUCCESS');
define('ACK_SUCCESS_WITH_WARNING', 'SUCCESSWITHWARNING');

//define ('PAYMENT_MODE', 1 );


abstract  class ET_Payment {
	
	
	protected  $_order;
	protected  $_message ;
	protected  $_submit_label;

	protected  $_errors   = array ();
	protected  $_settings = array ();
	
	//abstract function get_settings ( ) ; 
	/**
	 * setup settings for ET_Payment
	 */
	//abstract function set_settings ( $settings = array () ) ;
	/**
	 * retrieve supported payment gateways
	 */
	public static function get_support_payment_gateway () {
		
		$support =	array (
			'paypal'	=>	array ( 'label' =>'Paypal', 'active' => -1 , 
									'description' => __("Send your payment via Paypal.",ET_DOMAIN) ),
		
			'2checkout'	=>	array ( 'label' =>'2CheckOut', 'active' => -1,
									'description' => __("Send your payment via 2Checkout.",ET_DOMAIN)),
		
			// 'google_checkout'	=>	array ( 'label' =>'Google Checkout', 'active' => -1 ,
			// 						'description' => __("Pay using your Google Wallet.",ET_DOMAIN)),
		
			'cash'		=>	array ('label' => __('Cash', ET_DOMAIN),  'active' => -1 ,
									'description' => __("Send your cash payment to our bank account", ET_DOMAIN)
				)
		);
		return apply_filters('et_support_payment_gateway', $support );
		
	}
	public static function get_currency_list () {
		/* Paypal support currency
		 * 	- U.S. Dollar, Australian Dollar, Canadian Dollar, Hong Kong Dollar, Singapore Dollar,
		 *  - Taiwan New Dollar, New Zealand Dollar, Euro, Swiss Franc, Czech Koruna, Swedish Krona, 
		 *  - Danish Krone, Norwegian Krone, Hungarian Forint, Mexican Peso, Philippine Peso, 
		 *  - Malaysian Ringgit, Chinese RMB, Israeli New Shekel, Pounds Sterling, Brazilian Real, 
		 *  - Polish Zloty, Thai Baht, Japanese Yen.
		 */
		$currency	=	array (
			'AUD'	=> array (  'code'	=>	'AUD',	'alt'	=>	__("Australian Dollar",ET_DOMAIN),
								'label'	=>	'AUD',	'icon'	=> 	 'AUD',		'align'	=>	 'left'
							 ),
			'CAD'	=> array (	'code'	=>	'CAD',	'alt'	=>	__("Canadian Dollar",ET_DOMAIN),
								'label'	=>	'CAD',	'icon'	=> 	 'CAD',		'align'	=>	 'left'
							 ),
			'EUR'	=> array (	'code'	=>	'EUR',	'alt'	=>	__("Euro",ET_DOMAIN),
								'label'	=>	'EURO',	'icon'	=> 	 '&euro;',	'align'	=>	 'left' 
							 ),
			'USD'	=> array (	'code'	=>	'USD',	'alt'	=>	__("American Dollar",ET_DOMAIN),
						 		'label'	=>	'USD',	'icon'	=>	'$', 		'align'	=>	'left'	
						 	  ),
			'JPY'	=> array (	'code'	=>	'JPY',	'alt'	=>	__("Japanese Yen",ET_DOMAIN),
						 		'label'	=>	'JPY',	'icon'	=>	'&yen;',	'align'	=>	'left'	
						 	 ),
			'GBP'	=> array (	'code'	=>	'GBP',	'alt'	=>	__("British Pound",ET_DOMAIN),
						 		'label'	=>	'GBP',	'icon'	=>	'&pound;',	'align'	=>	'left'	
						 	 ),
			'NZD'	=> array (	'code'	=>	'NZD',	'alt'	=>	__("New Zealand Dollar",ET_DOMAIN),
						 		'label'	=>	'NZD',	'icon'	=>	'NZD',	'align'	=>	'left'	
						 	 ),
			'CHF'	=> array (	'code'	=>	'CHF',	'alt'	=>	__("Swiss Franc",ET_DOMAIN),
						 		'label'	=>	'CHF',	'icon'	=>	'CHF',	'align'	=>	'left'	
						 	 ),
			'HKD'	=> array (	'code'	=>	'HKD',	'alt'	=>	__("Hong Kong Dollar",ET_DOMAIN),
						 		'label'	=>	'HKD',	'icon'	=>	'HKD',	'align'	=>	'left'	
						 	 ),
			'SGD'	=> array (	'code'	=>	'SGD',	'alt'	=>	__("Singapore Dollar",ET_DOMAIN),
						 		'label'	=>	'SGD',	'icon'	=>	'SGD',	'align'	=>	'left'	
						 	 )			
				
		);
		return apply_filters('et_payment_currency_list',  $currency );
		
	}
	/*
	 * update current used code
	 */
	public static function set_currency ( $currency_code ) {
		
		$currency_list	=	self::get_currency_list();
		if(isset($currency_list[$currency_code])) {
			update_option ('et_current_currency', $currency_list[$currency_code] );
			return true;
		}
		return false;
	}
	/*
	 * get current used code
	 */
	public static function get_currency (  ) {
		$cur	=	 get_option('et_current_currency', true);
		if( $cur == '' || empty($cur) || !is_array( $cur )) {
			$cur 	=	 array (	'code'	=>	'USD',	'alt'	=>	__("American Dollar",ET_DOMAIN),
						 		'label'	=>	'USD',	'icon'	=>	'$', 		'align'	=>	'right'	
							  );
		}
		return $cur;
		
	}
	/*
	 * enable payment gateway
	 */
	public static function enable_gateway ( $gateway ) {
		
		$support_gateway	=	self::get_support_payment_gateway();
		
		// if the gateway doesnot supported return false
		if( !isset($support_gateway[$gateway] ) ) {
			return false;
		}

		$gateways	=	self::get_gateways( );
		// init array if gateways have not set
		if(!is_array( $gateways )) {
			$gateways	=	array ();
		}
		
		$gateways[$gateway]				=	$support_gateway[$gateway];
		$gateways[$gateway]['active']	=	1;
		unset($gateways[$gateway]['description']);

		update_option('et_payment_gateways', $gateways );

		return true;

	}
	/**
	 * disable a payment gateway
	 * @param string $gateway : gateway key
	 */
	public static function disable_gateway ( $gateway ) {

		$gateways	=	self::get_gateways( );

		if( isset( $gateways[$gateway] )) {
			unset($gateways[$gateway]);
			update_option('et_payment_gateways', $gateways );
		}
		return true;
	}
	/*
	 * get available payment gateways
	 */
	public static function get_gateways () {
		// get available payment gateway from db
		$gateways			=	get_option('et_payment_gateways', true);

		$support_gateway	=	self::get_support_payment_gateway();

		// init array if gateways have not set
		if( !is_array( $gateways ) || empty($gateways) ) {
			$gateways	=	array ();
			$gateways['cash']				=	$support_gateway['cash'];
			$gateways['cash']['active']		=	1;
		}

		foreach ($gateways as $key => $value) {
			if(!isset($support_gateway[$key])) return ;
			$gateways[$key]['description'] 	=	isset($support_gateway[$key]) ? $support_gateway[$key]['description'] : '';
			$gateways[$key]['label']		=	$support_gateway[$key]['label'];
		}
		return apply_filters ('et_get_payment_gateways', $gateways);
	}

	public function check_have_payment_activated(){

	}
	/**
	* get payment mode setting
	* return bool : true : test, false is real
	*/
	public static function get_payment_test_mode () {
		$opt	= get_option ('et_payment_mode', false);
		if(is_array($opt) || empty($opt)) 
			return false;
		else return $opt;
	}
	/**
	* set payment mode setting
	* return bool : true : test, false is real
	*/
	public static function set_payment_test_mode ($value) {
		return update_option ('et_payment_mode', $value);
	}
	
	/**
	 * @return settings array 
	 * @see ET_Payment::get_settings()
	 */
	function get_settings( ) {
		return $this->_settings;
	}
	
}

class ET_Paypal extends ET_Payment {
	
	private $_test_mod;
	private $_type;
	/**
	# Endpoint: this is the server URL which you have to connect for submitting your API request.
	*/
	private $_api_endpoint;
	/**
	# Version: this is the API version in the request.
	# It is a mandatory parameter for each API request.
	# The only supported value at this time is 2.3
	*/
	private $_version;
	/*
		PayPal URL. This is the URL that the buyer is
 	    first sent to to authorize payment with their paypal account
	    change the URL depending if you are testing on the sandbox
	    or going to the live PayPal site
	    For the sandbox, the URL is
	    https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=
	    For the live site, the URL is
	    https://www.paypal.com/webscr&cmd=_express-checkout&token=
	*/
	private $_paypal_url;	
	
	//private $_proxy;
	//private $_proxy_host;
	//private $_proxy_port;
	
	function __construct() {
		
		$mode	=	ET_Payment::get_payment_test_mode();
		$api	=	self::get_api ();
		extract($api);
		// init api setting
		$this->_api_username	=	trim($api_username);
		//$this->_api_password	=	trim($api_password);
		//$this->_api_signature	=	trim($api_signature);
		
		$this->_api_endpoint	=	'https://api-3t.sandbox.paypal.com/nvp';
		$this->_version			=	87.0; 
		
		$this->_proxy			=	false;
		$this->_test_mod		=	$mode;
		
		if( $this->_test_mod ) { // in test mode : enpoint url is sandbox
			
			$this->_paypal_url	=	'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_xclick&rm=2&business='	;
			
		}else { // in realtime
			
			$this->_paypal_url	=	'https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&rm=2&business=';
			
		}
		
		/*$default_settings	=	array (
			'return'	=> 	'http://localhost',
			'cancel'	=>	'http://localhost'
		);
		
		$settings			=	wp_parse_args( $settings, $default_settings );	
		$this->_settings	=	$settings;*/
	}
	
	/**
	 * Function to perform the API call 3rd-Party Cart parameter 
	 * @param string  $nvpstr
	 * @param string  $payment_type
	 */
	function set_checkout ( $nvpstr, $payment_type ) {
		
		$payment_type	=	strtoupper( $payment_type );
		
		if($payment_type == 'SIMPLEPAYPAL') {
			
			// 2checkout url for direct purchase link using the 3rd-Party Cart parameters
			$paypal	=	$this->_paypal_url.$this->_api_username.$nvpstr;
			
			return $paypal;
			
		}else {
			return false;
		}
	}
	
	
	/**
	 * @return settings array 
	 * @see ET_Payment::get_settings()
	 */
	function get_settings( ) {
		return $this->_settings;
	}
	/**
	 * get paypal checkout url
	 * return string : url
	 */
	function get_paypal_url () {
		return $this->_paypal_url;
	}
	
	static function set_api ( $api = array () ) {
		update_option('et_paypal_api', $api );
		if(!self::is_enable()) {
			$gateways	=	self::get_gateways();
			if( isset($gateways['paypal']['active'])  && $gateways['paypal']['active'] != -1 ) {
				ET_Payment::disable_gateway('paypal');
				return __('Your Paypal was disabled because of invalid settings!', ET_DOMAIN);
			}
		}
		return true;
	}
	
	static function get_api () {
		
		$api	= (array)get_option('et_paypal_api', true);
		if(!isset($api['api_username'])) $api['api_username']	=	'';
		if(!isset($api['api_password'])) $api['api_password']	=	'';
		if(!isset($api['api_signature'])) $api['api_signature']	=	'';
		return $api;
		
	}
	
	// function accept visitor
	function accept ( ET_PaymentVisitor $visitor ) {
		$visitor->visitSimplePaypal($this);
	}
	/**
	 * check paypal api setting available or not
	 */
	public static function is_enable() {
		$api	=	self::get_api();
		if( isset($api['api_username']) && $api['api_username'] != '') 
			return true;
		return false;
	}
}

/** =====================================
 * 	Some function relate to payments
 * 	===================================== */

/**
 * 
 */
function et_get_default_currency( $object = OBJECT ){
	switch ($object) {
		case OBJECT:
			return (object)ET_Payment::get_currency();
		break;
		
		case ARRAY_A:
			return ET_Payment::get_currency();
		break;	
		
		default:
			return ET_Payment::get_currency();
		break;
	}
		
}

/**
 * 
 */
function et_get_price_format( $amount , $style = ''){
	$currency = et_get_default_currency();
	$format = '%1$s';

	switch ($style) {
		case 'sup':
			$format = '<sup>%1$s</sup>';
			break;

		case 'sub':
			$format = '<sub>%1$s</sub>';
			break;
		
		default:
			$format = '%1$s';
			break;
	}
	
	if ( $currency->align == "left") 
		$format = $format . '%2$s';
	else 
		$format = '%2$s' . $format;
	return sprintf( $format, $currency->icon, number_format($amount, 2) );
}

/**
 * 
 */
function et_set_default_currency($code){
	return ET_Payment::set_currency($code);
}

/**
 * get supported payment gateways
 */
function et_get_support_gateways () {
	$support	=	ET_Payment::get_support_payment_gateway();
	return $support;
}
function et_get_enable_gateways () {
	$enable		=	ET_Payment::get_gateways();
	return $enable;
}
/**
 * get support currency list
 */
function et_get_currency_list () {
	$list	=	ET_Payment::get_currency_list();
	return $list ;
}
/**
 * set currency
 * @param string $currency : currency code
 */
function et_set_currency ( $currency ) {
	return ET_Payment::set_currency($currency);
}

/**
 * enable a gateway
 * @param string $gateway
 * @param string $label
 */
function et_enable_gateway ($gateway) {
	$a	=	strtoupper($gateway);
	$available	=	true;
	switch ($a) {
		case 'PAYPAL':
			 if(!ET_Paypal::is_enable( ) )
			 	 $available	=	 false ;
		break;
		
		case '2CHECKOUT':
			 if(!ET_2CO::is_enable( ) )
			 	 $available	= false ;
		break;
		// case 'GOOGLE_CHECKOUT' :
		// 	if(!ET_GoogleCheckout::is_enable( ) )
		// 	 	$available	= false ;
		// break;
		default:
			if( !ET_Cash::is_enable()) 
				$available	= false ;
		break;
	}
	$available		=	apply_filters('et_enable_gateway', $available, $gateway );
	
	if($available) {
		return ET_Payment::enable_gateway($gateway);
	} else return false;
}

/**
 * disable a gateway
 * @param string $gateway
 */
function et_disable_gateway ($gateway) {
	return ET_Payment::disable_gateway($gateway);
}
/**
 * update payment mode
 * @param bool 
*/
function et_set_payment_test_mode ($value) {
	ET_Payment::set_payment_test_mode ($value);
}
/**
 * get payment test mode
 * @return bool
*/
function et_get_payment_test_mode () {
	return ET_Payment::get_payment_test_mode ();
}
/**
 * update payment setting
 * @param name : string api key
 * @param value : string api value
*/
function et_update_payment_setting ($name, $value) {

	$paypal_api	=	ET_Paypal::get_api ();
	$_2co_api	=	ET_2CO::get_api();
	//$google		=	ET_GoogleCheckout::get_api();
	$value		=	trim($value);
	$msg		=	'';
	switch ($name) {
		case 'PAYPAL-APIUSERNAME':
			$validator	=	new ET_Validator ();
			if( $value != '' && !$validator->validate('email', $value) ) {
				$msg	=	__('Invalid input!', ET_DOMAIN);
				break;
			}
			$paypal_api['api_username']	=	$value;
			$msg	=	ET_Paypal::set_api( $paypal_api );
			break;

		case '2CHECKOUT-SID' :
			$_2co_api['sid']	=	$value;
			$msg	=	ET_2CO::set_api($_2co_api);
			break;
			
		case '2CHECKOUT-SECRETKEY':
			$_2co_api['secret_key']	=	$value;
			$msg	=	ET_2CO::set_api($_2co_api);
			break;
		case '2CO_USE_DIRECT' :
			$_2co_api['use_direct']	=	$value;
			$msg	=	ET_2CO::set_api($_2co_api);
			break;
		//comment from 2.9.3 	
		// case 'GOOGLE-MERCHANT-ID' :
		// 	$google['merchant_id']	=	$value;
		// 	$msg					=	ET_GoogleCheckout::set_api($google);
		// 	break;
			
		// case 'GOOGLE-MERCHANT-KEY' :
		// 	$google['merchant_key']	=	$value;
		// 	$msg					=	ET_GoogleCheckout::set_api($google);
		// 	break;
			 	
		case 'CASH-MESSAGE' :
			$msg	=	ET_Cash::set_message($value);
			break;	
			
		default:
			$response	=	false; 		
		break;
	}

	$msg	=	apply_filters( 'et_update_payment_setting', $msg, $name, $value);
	if( is_string( $msg ) ) {
		$response	=	array ('success' => false, 'msg' => $msg);
	} else {
		$response	=	array ('success' => true, 'msg' => $msg);
	}

	return $response;
}


/**
 * display currency code with the additional label
 * @param string $label : the string will be followed by an currency icon
 * @param string  $currency : currency code type
 * @param string $before_icon : could be html tag before currency icon
 * @param string $after_icon : could be html tag after currency icon
 */
function et_display_currency ( $label, $currency = array (), $before_icon = '', $after_icon ='' , $echo =true) {
	
	if( !is_array( $currency ) || !isset($currency['icon'])) {
		echo $label;
		return false;
	}
	$string 	=	'';
	if( isset( $currency['icon']) ) {
		
		$icon 	=	$currency['icon'];
		if( $icon != '' ) { // currency has an icon
			if( isset($currency['align']) &&  $currency['align'] == 'left')
				$string	=	 $before_icon. $icon. $after_icon . $label ;
			else 	
				$string	= $label. $before_icon. $icon.$after_icon ;
		} else { //currency does not have icon or icon = ''
			if ( $label != $currency['label']) { //
				$string	= $label. $before_icon. $currency['label'].$after_icon ;
			}else {
				$string	= $label;
			}
		}
			
	}
	if( $echo ) {
		echo $string;
	}else 
		return $string;
}