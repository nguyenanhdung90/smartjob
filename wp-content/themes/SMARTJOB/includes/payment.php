<?php
function et_ajax_payment_process() {
	global $user_ID;
	// remember to check isset or empty here
	$jobID			= isset($_POST['jobID']) ? $_POST['jobID'] : '';
	$authorID		= isset($_POST['authorID']) ? $_POST['authorID'] : $user_ID;
	$packageID		= isset($_POST['packageID']) ? $_POST['packageID'] : '';
	$paymentType	= isset($_POST['paymentType']) ? $_POST['paymentType'] : '';

	$job_error		=	'';
	$author_error	=	'';
	$package_error	=	'';
	$errors			=	array ();
	// job id invalid
	if ( $jobID == '' || get_post_type($jobID) != 'job' ) {
		$job_error	=	__("Invalid job ID!",ET_DOMAIN);
		$errors[]	=	$job_error;
	} else {
		// author does not authorize job
		$job	=	get_post($jobID);

		if( $authorID != $job->post_author && !current_user_can('manage_options' )) {
			$author_error	=	__("Job author information is incorrect!",ET_DOMAIN);
			$errors[]	=	$author_error;
		}
	}
	// package data invalid
	if( $packageID == '' || get_post_type ( $packageID ) != 'payment_plan' ) {
		$package_error	=	__("Invalid job package ID!",ET_DOMAIN);
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
		'status'			=>	 'draft',
		'payment'			=>	 $paymentType,
		'paid_date'			=>	 '',
		'payment_plan' 		=> 	$packageID ,
		'post_parent'		=> 	$jobID
	) ;
	/**
	 * filter order data
	*/
	$order_data	=	apply_filters( 'je_payment_setup_order_data', $order_data );

	$plans		=	et_get_payment_plans();
	$plan		=	$plans[$packageID];
	$plan['ID']	=	$jobID;

	//wp_update_post(array ('ID' => $jobID, 'post_status' => 'pending'));
	$company_location	=	et_get_user_field ($user_ID,'recent_job_location');
	$ship	=	array( 'street_address' => isset($company_location['full_location']) ? $company_location['full_location'] : __("No location", ET_DOMAIN));

	// insert order into database
	$order		=	new ET_JobOrder( $order_data , $ship );

	$order->add_product ($plan);

	$order_data				=	$order->generate_data_to_pay ();

	et_write_session ('order_id', $order_data['ID']);
	et_write_session ('job_id', $jobID);

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
			'paymentType'	=>	$paymentType
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
add_action( 'wp_ajax_et_payment_process', 'et_ajax_payment_process' );

/**
 * Handle payment sorting request
 */
function et_ajax_payment_sorting(){
	parse_str( $_REQUEST['content']['order'] , $sort_order);

	// update new order
	global $wpdb;
	$sql = "UPDATE {$wpdb->posts} SET menu_order = CASE ID ";
	foreach ($sort_order['payment'] as $index => $id) {
		$sql .= " WHEN {$id} THEN {$index} ";
	}
	$sql .= " END WHERE ID IN (" . implode(',', $sort_order['payment']) . ")";

	$result = $wpdb->query( $sql );
	et_refresh_payment_plans();

	echo json_encode(array(
		'success' 	=> $result,
		'msg' 		=> __('Payment plans have been sorted', ET_DOMAIN)
	));
	exit;
}
add_action('wp_ajax_et_sort_payment_plan', 'et_ajax_payment_sorting');

/*
 * ajax change default currency
 */
add_action('wp_ajax_et-change-currency', 'et_ajax_change_currency');
function et_ajax_change_currency () {
	$currency =	$_POST['new_value'];

	$response =	et_set_currency ($currency);

	header( "Content-Type: application/json" );
	echo json_encode( $response );

	exit;
}

/*
 * ajax save payment settings
 */
add_action ('wp_ajax_et-save-payment-setting', 'et_save_payment_setting');
function et_save_payment_setting () {

	$name	=	strtoupper($_POST['name']);
	$value	=	$_POST['value'];
	$response	=	et_update_payment_setting ($name, $value);
	header( "Content-Type: application/json" );
	echo json_encode( $response );
	exit;
}
/**
 * get current company list job in package
*/
function je_get_current_user_order ($company_id)  {
	$result	=	get_option( 'je_company_current_order', array() );
	$orders	=	get_user_meta($company_id, 'je_company_current_order' , true );
	if(empty($orders))
		return $result;
	return $orders;
}
/**
 * set group job by package for company
 * packageID : true will reset a group by package to empty array
*/
function je_update_current_user_order ($company_id, $packageID , $order_id  ) {
	$group				=	je_get_current_user_order ($company_id) ;
	$group[$packageID]	=	$order_id;
	return	update_user_meta ($company_id, 'je_company_current_order', $group);
}


/**
 * check for plan have paid or not
*/
function je_get_company_paid_plans ($company_id) {
	$result	=	get_option( 'je_company_paid_plans', array() );
	$orders	=	get_user_meta ($company_id, 'je_company_paid_plans', true);
	if(empty($orders)) return $result;
	return $orders;

}

function je_update_company_paid_plans ($company_id, $plan_id, $paid = 0 ) {
	$plan			=	je_get_company_paid_plans ($company_id);
	$plan[$plan_id]	=	$paid;
	update_user_meta ($company_id, 'je_company_paid_plans' , $plan);
	// update_option( 'je_company_paid_plans', $plan );
}

/**
 * Update company purchased plans
 */
function et_update_company_plans($company_id, $plan_id, $subtract = true ){
	//$company = get_userdata( $company_id );
	$company_plans = get_user_meta( $company_id, 'et_payment_plans', true );

	// refresh plans if company doesn't have any plans
	if (empty($company_plans)) $company_plans = array();

	// get the plan
	$plans		= et_get_payment_plans();
	if (empty($plans[$plan_id])) return false; // if plan doesn't exist, return false
	if (isset($plans[$plan_id]['price']) && $plans[$plan_id]['price'] = 0) return 0; // if plan is free, doesn't count

	// get quantity
	$quantity 	= empty($plans[$plan_id]['quantity']) ? 1 : $plans[$plan_id]['quantity'];
	$quantity 	= $subtract == true ? $quantity - 1 : $company_plans[$plan_id] + 1;

	if($quantity >=  $plans[$plan_id]['quantity'] ) $quantity = 0;
	// update to company storage
	//$company_plans[$plan_id] = empty($company_plans[$plan_id]) ? 0 : $company_plans[$plan_id];
	$company_plans[$plan_id] = $quantity;

	foreach ($company_plans as $key => $value) {
		if($value == 0) unset($company_plans[$key]);
	}
	// update to database
	update_user_meta( $company_id, 'et_payment_plans', $company_plans );

	///delete_user_meta( $company_id, 'et_payment_plans' ) ;
	// return the quantity that company has left
	return $quantity;
}

/**
 * Return purchased
 */
function et_get_purchased_quantity($company_id){
	return (array)get_user_meta( $company_id, 'et_payment_plans', true );
}

/**
 * Subtract payment plans in company's storage
 */
function et_use_company_plans($company_id, $plan_id){
	//$company = get_userdata( $company_id );
	$company_plans = get_user_meta( $company_id, 'et_payment_plans', true );

	// update to company storage
	if (isset($company_plans[$plan_id]) && $company_plans[$plan_id] > 0) {
		$company_plans[$plan_id] = intval($company_plans[$plan_id]) - 1;
	}else {
		return false;
	}

	// update to database
	update_user_meta( $company_id, 'et_payment_plans', $company_plans );
	//delete_user_meta(  $company_id, 'et_payment_plans' );
	return $company_plans[$plan_id];
}

/**
 * get payment plan
 */
function et_query_payment_plans($args = array(), $return_type = OBJECT_K){
	global $et_global;
	$prefix 		= $et_global['db_prefix'];

	$extra_fields = apply_filters('et_payment_plan_fields', array() );

	$plans 		= get_posts($args);
	$return  	= array();
	foreach ($plans as $plan) {
		$new_plan = new stdClass;
		$new_plan->ID = $plan->ID;
		$new_plan->title = $plan->post_title;
		$new_plan->description = $plan->post_content;
		$new_plan->price =  get_post_meta($plan->ID, $prefix . 'price', true);
		$new_plan->duration = get_post_meta($plan->ID, $prefix . 'duration', true);

		foreach ($extra_fields as $key) {
			$new_plan->$key = get_post_meta($plan->ID, $prefix . $key, true);
		}

		switch ($return_type) {
			default: 
			case OBJECT_K:
				$return[] = $new_plan;
				break;
			case ARRAY_N:
				$return[] = (array)$new_plan;
				break;
			case ARRAY_K:
				$return[$plan->ID] = (array)$new_plan;
				break;
		}
	}

	return $return;
}

/**
 * Retrieve Revenue from database
 *
 * @param $within in milisecond
 * @since 1.0
 */
function et_get_revenue($within = 0, $status="publish"){
	global $et_global;
	// fetch revenue from server cached
	$revenue = get_transient( $et_global['db_prefix'] . 'revenue');

	// if revenue was cached in server, get it directly from database
	if ( $revenue == false || empty($revenue[$within]) ){
		$revenue[$within] = et_refresh_revenue($within, $status);
	}
	return $revenue[$within];
}

/**
 * Cached Revenue value
 *
 * @param $within in milisecond
 * @since 1.0
 */
function et_refresh_revenue($within = 0, $status="publish"){
	global $wpdb, $et_global;

	$now 	= strtotime('now');
	$range 	= date('Y-m-d H:i:s', $now - $within);
	$range_sql = $within == 0 ? "" : "AND post_date >= '{$range}'";

	$sql = "SELECT ROUND(SUM(meta_value) , 2) AS revenue FROM {$wpdb->postmeta} meta 
			INNER JOIN {$wpdb->posts} AS posts ON posts.ID = meta.post_id 
			WHERE meta_key = '{$et_global['db_prefix']}order_total' {$range_sql} AND posts.post_status = '".$status."'";

	$revenue 	= $wpdb->get_var($sql);

	$new_revenue 			= get_transient( $et_global['db_prefix'] . 'revenue');
	$new_revenue[$within] 	= empty($revenue) ? $revenue : 0;

	set_transient($et_global['db_prefix'] . 'revenue', $new_revenue, 3600);

	return floatval($revenue);
}

add_filter('et_payment_currency_list', 'et_filter_payment_currency_list');
function et_filter_payment_currency_list ($default) {
	$option 		=	new ET_JobOptions();
	
	return array_merge( $option->get_currency_list(), $default);
}
/**
 * add authorize checkout button
*/
add_action ('before_je_payment_button', 'et_add_authorize_button');
function et_add_authorize_button ($payment_gateways) {

	if(!isset($payment_gateways['authorize']))  return;
	$authorize	=	$payment_gateways['authorize'];
	if( !isset($authorize['active']) || $authorize['active'] == -1) return ;
?>
	<li class="clearfix">
		<div class="f-left">
			<div class="title"><?php echo $authorize['label']?></div>
			<?php if(isset($authorize['description'])) {?>
			<div class="desc"><?php echo $authorize['description'] ?></div>
			<?php }?>
		</div>
		<div class="btn-select f-right">
			<button class="bg-btn-hyperlink border-radius select_payment" data-gateway="authorize" ><?php _e('Select', ET_DOMAIN );?></button>
		</div>

	</li>
<?php
}
/**
 * add authorize setup payment
*/
add_action ('je_payment_setup', 'et_authorize_response', 10, 3);
function et_authorize_response ( $response , $paymentType, $order) {
	if( $paymentType == 'AUTHORIZE') {

		$authorize	=	new ET_AuthorizeVisitor ( $order );
		$authorize->set_settings (array (
			'return' => et_get_page_link('process-payment'),
			'cancel' => et_get_page_link('process-payment')
		));

		$nvp		=	$order->accept ($authorize);
		$response	=	array(
			'success'	=>	$nvp['ACK'],
			'data'		=>	$nvp,
			'paymentType'	=>	'authorize'

		);

	}
	return $response;
}
/**
 * process payment return by authorize
*/
add_filter ('je_payment_process', 'je_authorize_process', 10, 2);
function je_authorize_process ( $paymentReturn, $order ) {

	if(isset($_REQUEST['x_response_code']) && isset($_REQUEST['x_MD5_Hash'])) {
		$authorize		=	new ET_AuthorizeVisitor ($order) ;
		$paymentReturn	=	$order->accept ($authorize)	;
	}
	return $paymentReturn;

}

/**
 * add authorize setting
*/
add_action  ('je_payment_settings', 'et_setup_authorize');
function et_setup_authorize () {
	$authorize	=	ET_Authorize::get_api ();
?>
	<div class="item">
		<div class="payment">
			<a class="icon" data-icon="y" href="#"></a>
			<div class="button-enable font-quicksand">
				<?php et_display_enable_disable_button('authorize', 'Authorize.Net')?>
			</div>
			<span class="message"></span>
			<?php _e("Authorize.Net",ET_DOMAIN);?>
		</div>
		<div class="form payment-setting">
			<div class="form-item">
				<div class="label">
					<?php _e("Your Authorize API Login ",ET_DOMAIN);?> 

				</div>
				<input class="bg-grey-input <?php if($authorize['x_login'] == '') echo 'color-error' ?>" name="x_login" type="text" value="<?php echo $authorize['x_login'] ?>" />
				<span class="icon <?php if($authorize['x_login'] == '') echo 'color-error' ?>" data-icon="<?php  data_icon($authorize['x_login']) ?>"></span>
			</div>
			<div class="form-item">
				<div class="label">
					<?php _e("Your Authorize API Transaction Key ",ET_DOMAIN);?>

				</div>
				<input class="bg-grey-input <?php if($authorize['x_transaction_key'] == '') echo 'color-error' ?>" type="text" name="x_transaction_key" value="<?php echo $authorize['x_transaction_key'] ?>" />
				<span class="icon <?php if($authorize['x_transaction_key'] == '') echo 'color-error' ?>" data-icon="<?php  data_icon($authorize['x_transaction_key']) ?>"></span>
			</div>
			<div class="form-item">
				<div class="label">
					<?php _e("Your Authorize API MD5 Hash ",ET_DOMAIN);?>

				</div>
				<input class="bg-grey-input <?php if($authorize['x_MD5_hash'] == '') echo 'color-error' ?>" type="text" name="x_MD5_hash" value="<?php echo $authorize['x_MD5_hash'] ?>" />
				<span class="icon <?php if($authorize['x_MD5_hash'] == '') echo 'color-error' ?>" data-icon="<?php  data_icon($authorize['x_MD5_hash']) ?>"></span>
			</div>
			<?php _e("Note: Authorized.Net only supports USD at the moment.", ET_DOMAIN); ?>
		</div>
	</div>
	<?php
}



