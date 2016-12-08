<?php 
function je_cron_add_fourhourly ($schedules) {
	$schedules['je_auto_archive_job'] = array(
 		'interval' =>  3600*4 ,
 		'display' => 'Jobengine Auto Archive Job Expired'
 	);
 	return $schedules;
}

add_filter( 'cron_schedules',  'je_cron_add_fourhourly');

add_action('archive_expired_jobs_2', 'et_schedule_archive_expired_jobs');
/**
 * Handle all schedule jobs
 * @since 1.0
 */
function et_schedule_events(){

	wp_clear_scheduled_hook( 'archive_expired_jobs' );

	if ( !wp_next_scheduled('archive_expired_jobs_2') ){
		$tomorrow = strtotime( date( 'Y-m-d 00:00:00', strtotime('now')) );
		wp_schedule_event( $tomorrow , 'je_auto_archive_job', 'archive_expired_jobs_2');
	}

}
add_action('init', 'et_schedule_events', 1000);


function et_schedule_archive_expired_jobs () {
	global $wpdb, $et_global, $post;
	$current = date('Y-m-d H:i:s', current_time('timestamp')  );
	$sql = "SELECT DISTINCT ID FROM {$wpdb->posts} as p
			INNER JOIN {$wpdb->postmeta} as mt ON mt.post_id = p.ID AND mt.meta_key = 'et_expired_date'
			WHERE 	(p.post_type = 'job') 			AND
					(p.post_status = 'publish') 	AND
					(mt.meta_value < '{$current}') 	AND
					(mt.meta_value != '' ) 			AND
						p.ID NOT IN ( SELECT post_id
							FROM {$wpdb->postmeta} as c
							WHERE c.meta_key = 'et_template_id' AND c.meta_value  IN ('rss' , 'indeed' , 'simplyhired' , 'linkedin' ) )";

	$archived_jobs = $wpdb->get_results($sql);

	$count = 0;
	//$archived_jobs = $wpdb->get_results($sql);

	$ar	=	array();
	foreach ($archived_jobs as $key =>  $job) {
		// perform approval for found job
		wp_update_post(array('ID' => $job->ID , 'post_status' => 'archive' ));
		do_action ('et_change_job_status', $job->ID ,  'archive' , 'publish');
		$count++;
		$ar[]	=	  $job->ID;
		//update_option ('je_schedule_log', $ar);
	}
	//update_option ('je_schedule_log', $current );
	return $count;
}

/**
 * find and archive all expired job
 * @author toannm
 * @since 1.0
 */
function et_archive_expired_jobs ($paged = false) {
	global $wpdb, $et_global, $post;
	$paged	=	($paged -1) * 10;

	$current = date('Y-m-d H:i:s', current_time('timestamp') );
	$sql = "SELECT DISTINCT ID FROM {$wpdb->posts} as p
			INNER JOIN {$wpdb->postmeta} as mt ON mt.post_id = p.ID AND mt.meta_key = 'et_expired_date'
			WHERE 	(p.post_type = 'job') 			AND
					(p.post_status = 'publish') 	AND
					(mt.meta_value < '{$current}') 	AND
					(mt.meta_value != '' ) 			AND
						p.ID NOT IN ( SELECT post_id
							FROM {$wpdb->postmeta} as c
							WHERE c.meta_key = 'et_template_id' AND c.meta_value  IN ('rss' , 'indeed' , 'simplyhired' , 'linkedin' ) )

			LIMIT {$paged}, 10";

	$archived_jobs = $wpdb->get_results($sql);

	$count = 0;
	//$archived_jobs = $wpdb->get_results($sql);

	foreach ($archived_jobs as $job) {
		if ( /*wp_update_post( array( 'ID' => $job->ID, 'post_status' 	=> 'archive' ) )*/
			et_archive_job ($job->ID)

		 ) {
			$count++;
		}
	}

	return $count;
}

function je_filter_get_archive_job ($where) {
	global $wpdb;
	$current = date('Y-m-d H:i:s', current_time('timestamp') );
	$where .= " AND {$wpdb->postmeta}.meta_value != '' AND {$wpdb->postmeta}.meta_value  <  '" . $current . "'";
	//echo $where;
	return $where;
}
/**
 * get number of expired job
 */
function et_expired_jobs_count(){
	global $wpdb, $et_global, $post;

	$current = date('Y-m-d H:i:s', current_time('timestamp') );

	$sql = "SELECT DISTINCT ID FROM {$wpdb->posts} as p
			INNER JOIN {$wpdb->postmeta} as mt ON mt.post_id = p.ID AND mt.meta_key = 'et_expired_date'
			WHERE 	(p.post_type = 'job') 			AND
					(p.post_status = 'publish') 	AND
					(mt.meta_value < '{$current}') 	AND
					(mt.meta_value != '' ) 			AND
						p.ID IN ( SELECT post_id
							FROM {$wpdb->postmeta} as c
							WHERE c.meta_key = 'et_template_id' AND c.meta_value NOT IN ('rss' , 'indeed' , 'simplyhired' , 'linkedin' ) ) 	";

	$archived_jobs = $wpdb->get_results($sql);

	return count($archived_jobs);

}

function et_posts_where_outdate_job($where){
	global $wpdb, $et_global;
	$current = date('Y-m-d H:i:s', current_time('timestamp') );
	$where .= " AND ({$wpdb->postmeta}.meta_value < '{$current}') ";
	return $where;
}

// for test only
add_action('template_redirect', 'et_action_archive_jobs');
function et_action_archive_jobs(){
	if (isset($_GET['do'] ) && $_GET['do'] == 'archive_jobs'){
		$count = et_archive_expired_jobs();
		echo sprintf('%d %s', $count, $count > 1 ? 'jobs have been archived' : 'job has been archived');
		exit;
	}
}

class JE_FreeVisitor extends ET_PaymentVisitor {
	protected $_payment_type = 'free';
	//function __construct () {}
	function setup_checkout (ET_Order $order) { /* do nothing  */}
	function do_checkout (ET_Order $order) {
		/**
		 * check session
		*/
		$session	=	et_read_session();
		$job_id	=	isset( $session['job_id'] ) ? $session['job_id'] : '';

		if( $job_id ) { // job id existed
			$post			=	get_post($job_id);

			if($post->post_type == 'job') {
				$job_package	=	et_get_post_field($job_id, 'job_package');
				$job			=	et_create_jobs_response ($post);

				//if(isset($plans[$job_package]['price']) && $plans[$job_package]['price']==0) {
				global $user_ID;
				et_write_session ('job_id' , $job_id ) ;
				if( $user_ID == $job['author_id']  || current_user_can('manage_options')) { // check permission
					$payment_return			=	array ('ACK' => true, 'payment_type' =>  'free');
					$job_opts	=	new ET_JobOptions();
					if($job_opts->use_pending())
						wp_update_post(array ('ID' => $job_id, 'post_status' => 'pending'));
					else
						wp_update_post(array ('ID' => $job_id, 'post_status' => 'publish'));
					return $payment_return;
				}
				et_update_post_field ($job_id, 'job_paid' , 2 );
			}

		}

		return array ('ACK' => false, 'payment_type' =>  'free' , 'msg'		=> __("Invalid Job ID", ET_DOMAIN)	) ;

	}
}
/**
 * Class JE_UsePackageVisitor
 * Process job order when user submit by use package
*/
class JE_UsePackageVisitor extends ET_PaymentVisitor  {
	protected $_payment_type = 'use_package';
	//function __construct () {}
	function setup_checkout (ET_Order $order) { /* do nothing  */}
	function do_checkout ( ET_Order $order ) {
		/**
		 * check session
		*/
		$session	=	et_read_session();
		$job_id	=	isset( $session['job_id'] ) ? $session['job_id'] : '';

		if( $job_id ) { // job id existed

			$post			=	get_post($job_id);
			if($post->post_type == 'job') {
				$job			=	et_create_jobs_response ($post);

				/**
				 * get user's number of available job
				*/
				$plans			=	et_get_payment_plans();
				$purchase_plan  = 	et_get_purchased_quantity($job['author_id']);

				/**
				 * check user plan is available or not
				*/
				if ( isset($purchase_plan[$job['job_package']]) > 0 ) {
					$payment_return			=	array ('ACK' => true, 'payment_type' =>  'usePackage');

					$user_paid	=	je_get_current_user_order ($job['author_id']);

					$current_order		=	(isset($user_paid[$job['job_package']] ) ? $user_paid[$job['job_package']] : 0 );

					$order	=	get_post ($current_order);
					if(is_wp_error( $order )) {
						return array ('ACK' => false, 'payment_type' =>  'usePackage' , 'msg'	=> __("Invalid Job ID", ET_DOMAIN)	) ;
					}

					/**
					 * check jobpackage change or not
					*/
					je_update_job_old_order ( $post , $job['job_package'] ) ;

					update_post_meta( $job_id, 'je_job_package_order', $current_order );

					//je_update_current_user_order ( $job->post_author, $jobpackage , $order_id );

					/**
					 * update job
					*/
					$job_opts	=	new ET_JobOptions();
					if( $job_opts->use_pending() || $order->post_status != 'publish' ) {
						wp_update_post(array ('ID' => $job_id, 'post_status' => 'pending'));

					} else {
						wp_update_post(array ('ID' => $job_id, 'post_status' => 'publish'));
						//do_action( 'je_approve_job', $post );
					}

					if( $order->post_status == 'publish')
						et_update_post_field ($job_id, 'job_paid' , 1 );
					else
						et_update_post_field ($job_id, 'job_paid' , 0 );

					$quantity 	= et_use_company_plans( $post->post_author , $job['job_package'] ) ;

					return $payment_return;
				}
			}

		}

		return array ('ACK' => false, 'payment_type' =>  'usePackage' , 'msg'	=> __("Invalid Job ID", ET_DOMAIN)	) ;
	}
}

/**
 * class JE_Payment_Factory
 * generate a payment visitor to process order by $paymentType
*/
class JE_Payment_Factory extends ET_Payment_Factory {
	function __construct () {
		// dont know what i can do here
	}

	public static function createPaymentVisitor ($paymentType , $order) {

		switch ( $paymentType ) {
			case 'CASH' : // return cash visitor
				$class	= 	new ET_CashVisitor ($order);
				break;
				break;
			case 'PAYPAL' :
				$class	=	new ET_PaypalVisitor ($order);
				break;
			case 'AUTHORIZE' :
				$class	=	new ET_AuthorizeVisitor($order);
				break;
			case '2CHECKOUT' :
				$class	=	new ET_2COVisitor($order);
				break;
			case 'FREE' :
				return new JE_FreeVisitor ($order);
				break;
			case 'USEPACKAGE' :
				return new JE_UsePackageVisitor ($order);
				break;
			default : $class	=	new ET_InvalidVisitor ($order);
		}

		return apply_filters( 'et_factory_build_payment_visitor', $class , $paymentType,  $order  );
	}
}

define ('ET_SESSION_COOKIE', '_et_session');
class ET_Session {
	protected $_session_id;
	protected $_expired_time;
	protected $_exp_variant;
	protected $_session_data;
	protected static $instance;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct () {

		if ( isset( $_COOKIE[ET_SESSION_COOKIE] ) ) {
			$cookie 		= stripslashes( $_COOKIE[ET_SESSION_COOKIE] );
			$cookie_data 	= explode( '||', $cookie );

			$this->_session_id 		= $cookie_data[0];
			$this->_expired_time 	= $cookie_data[1];

			// Update the session expiration if we're past the variant time
			if ( time() > $this->_expired_time ) {
				$this->set_expiration();
				$this->_session_id = $this->regenerate_id(true);
				update_option( "_et_session_expires_{$this->_session_id}", $this->_expired_time );
			}
		} else {
			$this->_session_id = $this->generate_id();
			$this->set_expiration();
		}

		$this->read_data();

		$this->set_cookie();
	}

	public function read_data () {
		if(!get_option( "_et_session_{$this->_session_id}", '' )) return false;
		$this->_session_data = unserialize(get_option( "_et_session_{$this->_session_id}", '' ) );
		return (array)$this->_session_data;
	}

	/**
	 * Write the data from the current session to the data storage system.
	 */
	public function write_data($key, $value ) {
		$option_key = "_et_session_{$this->_session_id}";
		if ( false === get_option( $option_key ) ) {
			$this->_session_data	=	array($key => $value );
			add_option( "_et_session_{$this->_session_id}", serialize($this->_session_data ), '', 'no' );
			add_option( "_et_session_expires_{$this->_session_id}", $this->_expired_time, '', 'no' );
		} else {
			$this->_session_data[$key]	= $value;
			update_option( "_et_session_{$this->_session_id}", serialize($this->_session_data) );
		}

	}
	/**
	 * set exprire time
	*/
	protected function set_expiration() {
		$this->_exp_variant 	= time() + (int) apply_filters( 'et_session_expiration_variant', 24 * 60 );
		$this->_expired_time 	= time() + (int) apply_filters( 'et_session_expiration', 20 * 60  );
	}

	/**
	 * Set the session cookie
	 */
	protected function set_cookie() {
		setcookie( ET_SESSION_COOKIE, $this->_session_id . '||' . $this->_expired_time , $this->_expired_time, '/' );
	}

	protected function generate_id() {
		require_once( ABSPATH . 'wp-includes/class-phpass.php');
		$hasher = new PasswordHash( 8, false );

		return md5( $hasher->get_random_bytes( 32 ) );
	}

	public function regenerate_id( $delete_old = false ) {
		if ( $delete_old ) {
			delete_option( "_et_session_{$this->_session_id}" );
		}

		$this->_session_id = $this->generate_id();

		$this->set_cookie();
	}

	public function unset_session ($key = null) {
		delete_option( "_et_session_{$this->_session_id}" );
	}
}

/**
 * Clean up expired sessions by removing data and their expiration entries from
 * the WordPress options table.
 *
 * This method should never be called directly and should instead be triggered as part
 * of a scheduled task or cron job.
 */
function et_session_cleanup() {
	global $wpdb;

	if ( defined( 'WP_SETUP_CONFIG' ) ) {
		return;
	}

	if ( ! defined( 'WP_INSTALLING' ) ) {
		$expiration_keys = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '_et_session_expires_%'" );

		$now = time();
		$expired_sessions = array();

		foreach( $expiration_keys as $expiration ) {
			// If the session has expired
			if ( $now > intval( $expiration->option_value ) ) {
				// Get the session ID by parsing the option_name
				$session_id = substr( $expiration->option_name, 20 );

				$expired_sessions[] = $expiration->option_name;
				$expired_sessions[] = "_et_session_$session_id";
			}
		}

		// Delete all expired sessions in a single query
		if ( ! empty( $expired_sessions ) ) {
			$option_names = implode( "','", $expired_sessions );
			$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name IN ('$option_names')" );
		}
	}

	// Allow other plugins to hook in to the garbage collection process.
	do_action( 'et_session_cleanup' );
}
add_action( 'et_session_garbage_collection', 'et_session_cleanup' );

/**
 * Register the garbage collector as a twice daily event.
 */
function et_session_register_garbage_collection() {
	if ( ! wp_next_scheduled( 'et_session_garbage_collection' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'et_session_garbage_collection' );
	}
}
add_action( 'wp', 'et_session_register_garbage_collection' );

function et_write_session ($key, $value) {
	$et_session	=	ET_Session::get_instance();
	return $et_session->write_data ($key, $value);
}

function et_read_session () {
	$et_session	=	ET_Session::get_instance();
	return $et_session->read_data ();
}

function et_destroy_session ($key = null) {
	$et_session	=	ET_Session::get_instance();
	$et_session->unset_session($key);
}

?>