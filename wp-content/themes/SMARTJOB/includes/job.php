<?php
if ( class_exists("ET_TaxType") ) :
	class JE_JobType extends ET_TaxType {

	    protected $_tax_name    = 'job_type';
	    protected $_order       = 'et_jobtype_order';
	    protected $_transient   = 'job_types';
	    protected $_tax_label   =  'Job Type';
	    protected $_color       =  'job_type_colors';

	    function __construct () {
	    	//parent::construct();
	    }

	    function get_color () {
	        // this function should be override if tax have color
	        $options = new ET_GeneralOptions();
	        return (array) get_option( $this->prefix .  $this->_color , $options->get_job_type_colors() );
	    }

	    function get_term_link ($term) {
	        return get_term_link( $term,$this->_tax_name );
	    }

	    function print_filter_list ($query_obj , $args) {
	    	$colours =	$this->get_color ();
	    	$types	=	$this->get_terms_in_order();
	    	extract($args);
	    	?>
	    	<ul class="job-filter filter-jobtype filter-joblist tax-filter"  data-tax='job_type'>
			<?php
				foreach ($types as $type) {
					if($hide_empty && $type->count <= 0 ) continue;
				 ?>
					<li>
						<a data-slug="<?php echo $type->slug ?>" href="<?php echo $this->get_term_link($type) ?>" class="color-<?php if(isset($colours[$type->term_id])) echo $colours[$type->term_id] ?> <?php if( $query_obj && $query_obj->term_id == $type->term_id ) echo 'active'; ?>">
						<div class="name"><?php echo $type->name ?></div>
						<span class="icon-label flag"></span>
						</a>
					</li>
				<?php } ?>
			</ul>
	    	<?php
	    }
	}
endif;
if ( class_exists("JE_JobType") ) :
	class JE_JobType_Ajax extends JE_JobType {
	    function __construct () {
	        add_action ('wp_ajax_et_sort_job_type', array(&$this, 'sort_terms'));
	        add_action ('wp_ajax_et_sync_job_type', array(&$this, 'sync_term'));
	        add_action ('wp_ajax_et_update_job_type_color', array(&$this, 'update_tax_color'));
	    }

	}
endif;



if ( class_exists("ET_TaxCategory") ) :
	class JE_JobCategory extends ET_TaxCategory {
	    protected $_tax_name    = 'job_category';
	    protected $_order       = 'et_category_order';
	    protected $_transient   = 'job_categories';
	    protected $_tax_label   =  'Job Category';


	    function get_term_link ($term) {
	        return get_term_link( $term,$this->_tax_name );
	    }

	    function print_filter_list () {

	    }

	}
endif;
if ( class_exists("JE_JobCategory") ):
	class JE_JobCategory_Ajax extends JE_JobCategory {
	    function __construct () {
	        add_action ('wp_ajax_et_sort_job_category', array(&$this, 'sort_terms'));
	        add_action ('wp_ajax_et_sync_job_category', array(&$this, 'sync_term'));
	    }
	}
endif;

if (!function_exists('et_ajax_job_handler')){
	function et_ajax_job_handler() {
		$method		= $_REQUEST['method'];
		$args		= $_REQUEST['content'];

		// validate here, later

		header( 'HTTP/1.0 200 OK' );
		header( 'Content-type: application/json' );

		$response	= array();

		switch($method){
			case 'delete' :
				$response =  et_job_handler_delete_post($args['id']);
				break;
			case 'read':
				$response =  et_job_handler_read_post($args['id'], $method);
				break;
			case 'create':
			case 'update':
				$response =  et_job_handler_creat_update_post($args, $method);
				break;
			case 'toggleFeature':
				$response =  et_job_handler_toggle_feature($args['id']);
				break;
			case 'reviewJob' :
				$response =  et_job_handler_review_job($args);
				break;
		} // end switch

		echo json_encode($response);
		exit;
	}
}
add_action( 'wp_ajax_et_job_sync', 'et_ajax_job_handler' );


/** function for et_ajax

*/
function et_job_handler_delete_post($post_id){
	try {
		$result = wp_trash_post( $post_id , true );
		$msg = __('The job has been deleted!', ET_DOMAIN);
		$response	=	array('success' 	=> true ,'msg' => $msg);

	} catch (Exception $e) {
		$response = array(
			'success' 	=> false,
			'code' 		=> $e->getCode(),
			'msg'	 	=> $e->getMessage(),
			'method'	=> $method,
		);
	}
	return $response;
}
/*
funtion et_jobhandler for methos reviewJob
*/
function et_job_handler_review_job($args){
	try {
		switch ($args['status']) {
			case 'publish':
				$result = et_approve_job( $args['id'] );
				$msg = __('The job has been approved!', ET_DOMAIN);
				break;

			case 'reject':
				$args = wp_parse_args($args, array('reason' => '', 'refund' => 0));
				$result = et_reject_job( $args['id'], $args['reason'], $args['refund'] );
				$msg = __('The job has been rejected!', ET_DOMAIN);
				break;

			case 'trash':
				$result = et_archive_job( $args['id'] );
				$msg = __('The job has been archived!', ET_DOMAIN);
				break;


			default:
				throw new Exception(__('Invalid input!', ET_DOMAIN), 400);
				break;
		}

		if ( is_wp_error($result) )
			throw new Exception($result->get_error_message(), 400);
		else {

			$post = get_post($args['id']);
			$job		= et_create_jobs_response( $post , true );
			$payment	= et_get_newest_order_by_job($post->ID);
			$author		= et_create_companies_response($job['author_id']);

			$response = array(
				'success' 	=> true,
				'code' 		=> 200,
				'msg'	 	=> $msg,
				'data' 		=> array(
					'job'		=> $job,
					'payment'	=> et_build_order_response($payment),
					'author'	=> $author
					),
				'method'	=> $args['status'] . 'Job',
				'rejectMessage' => empty($args['reason']) ? "" : $args['reason']
				);
		}
	} catch (Exception $e) {
		$response = array(
			'success' 	=> false,
			'code' 		=> $e->getCode(),
			'msg'	 	=> $e->getMessage(),// $result->get_error_message(),
			'data' 		=> array(),
			'method'	=> $args['status'] . 'Job',
			);
	}
	// activate action save job
	do_action('je_save_job', $job['ID']);
	return  $response;
}
/* function et_job_handler for method read
*/
function et_job_handler_read_post($post_id, $method){
	try{
		$post	= get_post($post_id);
		$job	= et_create_jobs_response( $post );
		$author	= et_create_companies_response($job['author_id']);
		$response = array(
			'success' 	=> true,
			'code' 		=> 200,
			'msg'	 	=> 'Job fetched',
			'data' 		=> array(
				'job' 		=> $job,
				'author'	=> $author
			),
			'method'	=> $method
		);
	}
	catch (Exception $e) {
		$response = array(
			'success' 	=> false,
			'code' 		=> $e->getCode(),
			'msg'	 	=> $e->getMessage(),
			'method'	=> $method,
			);
	}
	return  $response;
}

function je_process_job_location ($job_data) {
	if ($job_data['full_location'] == ''){
		$job_data['full_location'] 	= __('Anywhere', ET_DOMAIN);
		$job_data['location'] 		= __('Anywhere', ET_DOMAIN);
		$job_data['location_lat']	=	'';
		$job_data['location_lng']	=	'';
	}
	else {
		$max = 30;
		if (strlen($job_data['full_location']) > $max){
			$components = array_reverse(explode(',', $job_data['full_location']));
			$len = count($components);
			if (strlen($components[0]) + (isset($components[1]) ? strlen($components[1]) : 0 ) > $max)
				$job_data['location'] = $components[0];
			else
				$job_data['location'] = $components[1] . ', ' .$components[0];

		}else {
			$job_data['location'] = $job_data['full_location'];
		}
	}
	return $job_data;
}

function je_process_job_paid ($job_data){

	$plans			=	et_get_payment_plans();
	$purchase_plan  = 	et_get_purchased_quantity($job_data['post_author']);
	//if(!isset($job_data['id']) )
	if( (isset($plans[$job_data['job_package']]['price']) && $plans[$job_data['job_package']]['price'] == 0) ){

		$job_data['is_free'] 	=  true ;
		$job_data['job_paid']	= 2;

	} elseif ( isset($purchase_plan[$job_data['job_package']]) && $purchase_plan[$job_data['job_package']] > 0 ) {

		$job_data['is_use_package'] =  true ;
		$job_data['is_free'] 	=  false ;
		//$job_data['job_paid'] = 0;
		/**
		 * check user have paid for package or not
		*/
		$user_paid	=	je_get_current_user_order ($job_data['post_author']);

		$current_order		=	(isset($user_paid[$job_data['job_package']] ) ? $user_paid[$job_data['job_package']] : 0 );

		$order	=	get_post ($current_order);
		if( !$order ) {
			$job_data['job_paid'] = 0;
			//$job_data['is_use_package'] = false;
		} else {
			if( $order->post_status != 'publish' )
				//$job_data['job_paid'] = 1;
				if(isset($job_data['id'])) {
					$job_order	=	get_post_meta( $job_data['id'], 'je_job_package_order', true )	;
					if( $job_order == $current_order )  $job_data['is_use_package'] = false;
				}
				$job_data['job_paid'] = 0;

		}

	} else {
		if(!isset($job_data['id'])) {
			$job_data['is_free']	=	false;
			$job_data['is_use_package'] =  false ;
			$job_data['job_paid'] 	= 	0;
		}
	}

	return $job_data;
}

function je_process_job_tax ($job_data) {
	$arrTypes	=	array();
	$arrCat		=	array();
	if (isset($job_data['job_types']) && !empty($job_data['job_types']) ){

		$arrTypes[]	=	$job_data['job_types'][0]['slug'];
		// $arrTypes[]	=	$job_data['job_types'][1]['slug'];

		$job_data['job_type']	=  $arrTypes;
	}

	if (isset($job_data['categories']) && !empty($job_data['categories'])){

		$arrCat[]	= $job_data['categories'][0]['slug'];

		$job_data['category']	=  $arrCat;
	}

	return apply_filters( 'je_process_job_tax', $job_data );
}

function je_limit_free_plan () {

	if(!current_user_can( 'manage_options' ) && get_theme_mod( 'je_limit_free_plan', '' ) ) {
		global $user_ID;
		$used_free	=	get_user_meta( $user_ID , 'je_free_plan_used' , true );
		if( !$used_free ) $used_free = 0;
		if( get_theme_mod( 'je_limit_free_plan', '' ) <= $used_free ) {
			return array(
					'success' => false,
					'msg' => __("You have reached the maximum number of Free posts. Please select another plan.", ET_DOMAIN) 
				);
		}

		$used_free++;
		update_user_meta( $user_ID , 'je_free_plan_used' , $used_free );
	}
	return false;
}

/** funtin job_handler for methos create or uptate
*/
function et_job_handler_creat_update_post($args, $method){
	try {
		global $user_ID, $current_user;

		if( ( current_user_can('edit_posts') && $args['author_id'] == $user_ID) || current_user_can('edit_others_posts') || !isset($args['id']) ) {
			$job_data	= array();

			$args['applicant_detail']	= apply_filters('et_job_apply_content', $args['applicant_detail'] );
			$args['content']			= trim(apply_filters('et_job_content', $args['content'] ) );
			$roles		=	$current_user->roles;
			$user_role	=	array_pop($roles);

			if($user_role == 'jobseeker') {
				throw new Exception(__("You need an employer account to post a job.", ET_DOMAIN), 201);
			}

			if( ($args['apply_method'] == 'ishowtoapply' && $args['applicant_detail'] == '')
				|| $args['title'] == "" || $args['content'] == "" ) {
				throw new Exception(__("Please fill in all required fields!", ET_DOMAIN), 400);
			}

			$job_option	=	ET_JobOptions::get_instance();
			$useCaptcha	=	$job_option->use_captcha () ;
			// echo 1;
			if($useCaptcha && $method == 'create' && !current_user_can("manage_options")) {
				$captcha	=	ET_GoogleCaptcha::getInstance();
				if( !$captcha->checkCaptcha( $args['recaptcha_challenge_field'] , $args['recaptcha_response_field']  ) ) {
					throw new Exception(__("You enter an invalid captcha!", ET_DOMAIN), 400);
				}
			}


			/*// check location gecode by google is exist
			if($args['location'] =="" || $args['location'] == '' || $args['location'] == '') {
				throw new Exception(__("Location incorrect!", ET_DOMAIN), 400);
			}*/

			$key_map	= array(
				'title'				=> 'post_title',
				'author_id'			=> 'post_author',
				'job_package'		=> 'job_package',
				'content'			=> 'post_content',
				'location'			=> 'location',
				'full_location'		=> 'full_location',
				'status'			=> 'post_status',
				'featured'			=> 'featured',
				'location_lat'		=> 'location_lat',
				'location_lng'		=> 'location_lng',
				'apply_method'  	=> 'apply_method',
				'apply_email'		=> 'apply_email',
				'applicant_detail' 	=> 'applicant_detail',
				'is_free'			=> 'is_free',
				'is_use_package'	=> 'is_use_package' ,
				'id'				=> 'id',
				'author_data'		=> 'author_data',
				'job_types'			=> 'job_types',
				'categories'		=> 'categories'
			);



			foreach($args as $key=>$val){
				if(isset($key_map[$key]) && !empty($key_map[$key])){
					$job_data[$key_map[$key]]	= $val;
				}
			}

			$job_data	=	je_process_job_location ($job_data);
			$job_data	=	je_process_job_tax ($job_data);


			// set featured for job, depend on the plan
			if( !isset($job_data['featured']) || !is_numeric($job_data['featured']) ){
				$job_data['featured'] = (int)et_get_post_field( $job_data['job_package'], 'featured' );
			}

			$job_data	=	je_process_job_paid ($job_data);

			// the job model has an ID & this is the "update" handler
			if(isset($job_data['id']) && !empty($job_data['id']) && is_numeric($job_data['id'])) {

				// set the job ID
				$job_data['ID']	= $job_data['id'];
				// get job details
				$job			= get_post($job_data['ID']);
				// admin edit other account
				if( current_user_can('edit_others_posts') ) {
					$job_id	= et_update_job($job_data);
					if( isset($job_data['post_status']) && $job_data['post_status'] == 'publish' && $job->post_status != 'publish') {
						et_approve_job ($job_id);
					}
				} else {
					// is this the job author?
					if( $user_ID == $job->post_author) {
						$job_opts 	=	new ET_JobOptions ();

						// if the job has one of these statuses, update it to pending (keep it the same if 'draft' or 'archive')
						if( in_array( $job->post_status, array( 'reject', 'pending') ) ) {
							if( $job_opts->use_pending() || et_get_post_field($job->ID, 'job_paid') != 1) {
								$job_data['post_status']	= 'pending';
							} else {
								$job_data['post_status']	= 'publish';
								et_approve_job ($job->ID);
							}
						}else if($job->post_status == 'publish'){
							// danng
							// pending job has edited by company.
							if($job_opts->use_pending_job_edit() ){
								$job_data['post_status'] = 'pending';
							}
							// end danng
						}

						$job_id = et_update_job($job_data);
					}
					else{
						throw new Exception("You are not the job author", 401);
					}
				}
			} else{
				// the job hasn't got an ID & this is a "create" attempt
				// the status is pending only when the job is free
				$job_data['post_status']	= 'draft';
				// $job_data['language_code']	= ICL_LANGUAGE_CODE;

				//$job_data	=	apply_filters( 'icl_pre_save_pro_translation' , $job_data  );

				//$job_data['job_paid']		= ($job_data['is_free']) ? 2 : 0;
				$job_id = et_insert_job($job_data);

				//include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );


			}

			if ( !is_wp_error($job_id) ){
				$post	= get_post($job_id);
				et_update_post_field( $job_id, 'template_id', 'normal' );
				if(isset($args['apply_method']) && !empty($args['apply_method']) )
					$apply_method = $args['apply_method'];
				else
					$apply_method		=	trim(et_get_user_field ($user_ID,'apply_method'));
				et_update_post_field( $job_id, 'apply_method', $apply_method );

				// activate action save job
				do_action('je_save_job', $job_id);

				$job	= et_create_jobs_response( $post , true );
				$response = array(
					'success' 	=> true,
					'code' 		=> 200,
					'data' 		=> array(
						'job'	=> $job,
						//'count' => isset($quantity) ? $quantity : ''
						//'author' => $authorData
					),
					'method'	=> $method
				);

				if(isset($job_data['is_free']) && $job_data['is_free']) {
					$response['success_url']	=	et_get_page_link('process-payment', array ('paymentType' => 'free'));
					et_write_session('job_id' , $job_id) ;

					/**
					 * update use used free
					*/
					$out_of_free	=	je_limit_free_plan ();
					if($out_of_free) {
						throw new Exception( $out_of_free['msg'] );
					}
				}

				if(isset($job_data['is_use_package']) && $job_data['is_use_package']) {
					$response['success_url']	=	et_get_page_link('process-payment', array ('paymentType' => 'usePackage'));
					et_write_session('job_id' , $job_id) ;
				}

				if($method == 'update'){
					$response['msg']	= __('Your job has been saved successfully!', ET_DOMAIN);
				}
				elseif ($method == 'create'){
					$response['msg']	= __('Your job has been submitted successfully!', ET_DOMAIN);
				}

				if(isset($job_data['author_data']) && !empty($job_data['author_data'])) {
					// get author data from request
					$data			= $job_data['author_data'];
					$author_data	= array(
						'ID'			=> $job_data['post_author'],
						'user_url' 		=> $data['user_url'],
						'display_name'	=> $data['display_name'],
						'recent_job_location' =>
								array (
									'location' 			=> $job_data['location'],
									'full_location'  	=> $job_data['full_location'],
									'location_lat' 		=> $job_data['location_lat'],
									'location_lng' 		=> $job_data['location_lng']
								),
						'apply_method'					=> empty($job_data['apply_method']) ? trim(et_get_user_field ($user_ID,'apply_method')) : $job_data['apply_method'],
						'apply_email'					=> $job_data['apply_email'],
						'applicant_detail'				=> $job_data['applicant_detail'],

					);

					// does the user have enough cappability?
					if( current_user_can('edit_users') || current_user_can('administrator') || $job_data['post_author'] == $user_ID) {
						// update the company data
						$author_id	= et_update_user( $author_data );
						// insert these data in response
						$response['data']['author']	= et_create_companies_response($author_id , true);
						//$authorData = et_create_companies_response($author_id);
					}
					else {
						throw new Exception("You do not have permission to change this company's information!",401);
					}
				} else {
				 	//$authorData = et_create_companies_response($job_data['post_author']);
				 	$response['data']['author']	= et_create_companies_response($job_data['post_author']);
				}

			}
			else {
				throw new Exception($job_id->get_error_message(), 400);
			}
		}
		else{
			// user does not have permission to edit job
			throw new Exception("Permission denied", 401);
		}
	} catch (Exception $e) {
		$response = array(
			'success' 	=> false,
			'code' 		=> $e->getCode(),
			'msg'	 	=> $e->getMessage(),
			'method'	=> $method,
			);
	}

	return  $response;
}
/* fruntion job_handler for methos toglerFeature*/
function et_job_handler_toggle_feature($post_id){
	$result = et_toggle_job_feature( $post_id );
	$post = get_post($post_id);

	if ( is_wp_error($result) )
		$response = array(
			'success' 	=> false,
			'code' 		=> 200,
			'msg'	 	=> $result->get_error_message(),
			'data' 		=> array(),
			'method'	=> 'toggleFeature'
			);
	else {

		$job	= et_create_jobs_response($post , true );
		$author	= et_create_companies_response($job['author_id']);
		// activate action save job
		do_action('je_save_job', $job['ID']);
		$response = array(
			'success' 	=> true,
			'code' 		=> 200,
			'msg'	 	=> __('The job has been changed successfully!', ET_DOMAIN),
			'data' 		=> array(
				'job'		=> $job,
				'author'	=> $author
				),
			'method'	=> 'toggleFeature'
			);
	}
	return  $response;
}
/**
 * Geting jobs information via giving conditions
 *
 * @since 1.0
 */


function et_ajax_fetch_jobs(){
	$start	=	 time();
	$response = array();

	try {
		global $post, $et_global;

		// refine meta query
		$request = $_REQUEST['content'];
		if ( isset($request['meta_query']) ){
			foreach ((array)$request['meta_query'] as $index => $meta) {
				if ( isset($meta['key']) )
					$request['meta_query'][$index]['key'] = $et_global['db_prefix'] . $meta['key'];
			}
		}

		if ( !empty($request['status'] ) ){
			$request['post_status'] = $request['status'];
			unset($request['status']);
		}

		if(isset($request['post_status'])){
			$arrStatuses	= (is_array($request['post_status'])) ? $request['post_status'] : explode(',', $request['post_status']);
		}
		else{
			$arrStatuses	= array('publish');
		}
		$list_title		= et_get_job_status_labels($arrStatuses);

		if ( !empty($request['job_type']) && is_array( $request['job_type'] ) )
			$request['job_type'] = implode(',', $request['job_type']);
		if ( !empty($request['job_category']) && is_array( $request['job_category'] ) )
			$request['job_category'] = implode(',', $request['job_category']);

		add_filter('posts_orderby','et_filter_orderby');
		$request 	= apply_filters( 'et_fetch_jobs', $request );
		$query 		= et_query_jobs( $request );

		remove_filter('posts_orderby', 'et_filter_orderby');
		$jobs		= array();
		$authors	= array();

		if ($query->have_posts()) {
			while($query->have_posts()){
				$query->the_post();

				$job = et_create_jobs_response($post);

				if(!isset($authors[$job['author_id']])){

					// add to author array
					$authors[$job['author_id']]	= et_create_companies_response($job['author_id']);
				}

				// add to job array
				$jobs[] = $job;
			}
		}

		$response = array(
			'status' => true,
			'code' => 200,
			'data' => array(
				'list_title'	=> $list_title,
				'jobs'			=> $jobs,
				'authors'		=> $authors,
				'total_pages'	=> $query->max_num_pages,
				'jobs_count'	=> $query->post_count,
				'found_jobs'	=> $query->found_posts,
				'paged'			=> $query->query_vars['paged'] ? $query->query_vars['paged'] : 1
			)
		);

		$response	=	apply_filters( 'je_fetch_jobs', $response , $request );

	} catch (Exception $e) {
		$response = array(
			'status' => false,
			'code' 	=> 400,
			'msg' 	=> __("An error has occurred!", ET_DOMAIN)
		);
	}
	wp_send_json($response);
}

add_action( 'wp_ajax_et_fetch_jobs', 'et_ajax_fetch_jobs' );
add_action( 'wp_ajax_nopriv_et_fetch_jobs', 'et_ajax_fetch_jobs' );

//update_option('default_job_type', 3);
/**
 * Handle ajax request for job category syncing
 * @since 1.0
 */
function et_ajax_category_handle(){
	try {
		// return false if request method is empty
		if ( empty($_REQUEST['method']) ) throw new Exception(__('There is an error occurred', ET_DOMAIN), 400);

		$method = empty( $_REQUEST['method'] ) ? '' : $_REQUEST['method'] ;
		$data 	= $_REQUEST['content'];

		switch ($method) {
			case 'delete':
				// delete term with wp_delete_term
				if ($data['default_cat']){
					wp_delete_term($data['id'], 'job_category', array( 'default' => $data['default_cat'] )); // get_option('default_job_category')));

					// raise an action after delete job category
					do_action('et_delete_job_category');

					$resp = build_success_ajax_response(array(), __('Job category has been deleted!', ET_DOMAIN) );
				} else {
					$resp = build_error_ajax_response(null, '');
				}
				break;

			case 'create' :
				$args = array();
				if ( !empty($data['parent']) )
					$args['parent'] = $data['parent'];

				// insert new term
				$result = wp_insert_term( $data['name'] , 'job_category', $args);

				// if term can't be inserted throw an error
				if ( is_wp_error($result) )  throw new Exception(__('Error when trying to add new job category!', ET_DOMAIN), 400);

				// build response for client
				$resp = build_success_ajax_response( array(
						'id' => $result['term_id'],
						'name' => $data['name']
					), __('Job category has been inserted', ET_DOMAIN) );
				break;

			case 'update' :
				$args = $data;
				unset($args['id']);

				$result = wp_update_term($data['id'],'job_category', $args );

				// build response for client
				$resp = build_success_ajax_response( array(
						'id' 		=> $result['term_id'],
						'name' 		=> $data['name']
					), __('Job category has been updated', ET_DOMAIN) );
				break;

			default:
				throw new Exception(__('There is an error occurred', ET_DOMAIN), 400);
				break;
		}
		// refresh sorted job categories
		et_refresh_job_categories();
	} catch (Exception $e) {
		$resp = build_error_ajax_response(array(), $e->getMessage() );
	}
	echo json_encode( $resp );
	exit;
}
add_action( 'wp_ajax_et_sync_jobcategory', 'et_ajax_category_handle' );

/**
 * Handle ajax request for job type syncing
 * @since 1.0
 */
function et_ajax_job_type_handle(){
	try {
		// return false if request method is empty
		if ( empty($_REQUEST['method']) ) throw new Exception(__('There is an error occurred', ET_DOMAIN), 400);

		$method = empty( $_REQUEST['method'] ) ? '' : $_REQUEST['method'] ;
		$data 	= $_REQUEST['content'];

		switch ($method) {
			case 'delete':
				// delete term with wp_delete_term
				$default_type = $data['default_type'];
				if ($data['default_type']){
					wp_delete_term($data['id'], 'job_type', array( 'default' => $data['default_type']));

					// raise an action after delete job category
					do_action('et_delete_job_type');

					$resp = build_success_ajax_response( null, __('Job type has been deleted!', ET_DOMAIN) );
				} else {
					$resp = build_error_ajax_response(null, __("Default job type hasn't been set", ET_DOMAIN));
				}
				
				break;

			case 'create' :
				// insert new term
				$result = wp_insert_term( $data['name'] , 'job_type');

				// if term can't be inserted throw an error
				if ( is_wp_error($result) )  throw new Exception(__('Error when trying to add new job type!', ET_DOMAIN), 400);

				// apply color to job type
				if ( !empty($result['term_id']) && $data['color'] )
					et_update_job_type_color( $result['term_id'], $data['color'] );

				// build response for client
				$resp = build_success_ajax_response( array( 
						'id' => $result['term_id'],
						'name' => $data['name'],
						'color' => !empty($data['color']) ? $data['color'] : 0
					), __('Job type has been inserted!', ET_DOMAIN) );
				break;

			case 'update':
				$result = wp_update_term( $data['id'], 'job_type', array(
					'name' => $data['name']
					) );
				if ( !is_wp_error($result) ){
					// build response for client
					$resp = build_success_ajax_response( array( 
							'id' => $result['term_id'],
							'name' => $data['name']
						), __('Job type has been inserted!', ET_DOMAIN) );
				}
				else throw new Exception( __('Error when trying to update job type!', ET_DOMAIN) );
				break;

			default:
				throw new Exception(__('There is an error occurred', ET_DOMAIN), 400);
				break;
		}
		// refresh sorted job types
		et_refresh_job_types();
	} catch (Exception $e) {
		$resp = build_error_ajax_response( array(), $e->getMessage() );
	}

	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	echo json_encode( $resp );
	exit;
}
add_action( 'wp_ajax_et_sync_jobtype', 'et_ajax_job_type_handle' );

/**
 *
 */
function et_ajax_get_statistic(){
	global $post, $et_after_time;
	$resp 	= array();
	$within = $_REQUEST['within'] ;
	try {
		if ( !is_numeric($within) )
			throw new Exception( __('Invalid input!', ET_DOMAIN) );

		// get statistic
		$jobs_count 	= et_count_jobs($within);
		$app_count 		= et_count_applications($within);
		$revenue 		= et_get_revenue($within);

		// get pending jobs
		$et_after_time = $within;
		$query_jobs = et_query_jobs(array(
			'post_status' 		=> 'pending',
			'posts_per_page' 	=> apply_filters('overview_posts_per_page', 5)
			));

		$pending_jobs = array();
		while ($query_jobs->have_posts()) {
			$query_jobs->the_post();
			$pending_jobs[] = et_create_jobs_response( $post );
		}

		$resp = build_success_ajax_response( array(
					'statistic' => array(
						'pending_jobs' 	=> empty($jobs_count->pending) ? 0 : $jobs_count->pending ,
						'active_jobs' 	=> empty($jobs_count->publish) ? 0 : $jobs_count->publish,
						'revenue' 		=> empty($revenue) ? 0 : $revenue,
						'applications' 	=> empty($app_count->publish) ? 0 : $app_count->publish
						),
					'pending_jobs' 	=> $pending_jobs,
					//'payments' 		=> $payments
					), __('Data is fetched successfully', ET_DOMAIN) );
	} catch (Exception $e) {
		$resp = build_error_ajax_response(array(), $e->getMessage );
	}

	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	echo json_encode( $resp );
	exit;
}
add_action( 'wp_ajax_et_change_stats_time_limit', 'et_ajax_get_statistic' );

function et_update_job_type_colors(){
	$resp = array();
	if ( !empty($_REQUEST['content']['term_id']) && !empty($_REQUEST['content']['color']) ){
		et_update_job_type_color($_REQUEST['content']['term_id'], $_REQUEST['content']['color']);
		$resp = array(
			'success' 	=> true,
			'msg' 		=> __("Job type's color has been updated", ET_DOMAIN)
			);
	}
	else {
		$resp = array(
				'success' 	=> false,
				'msg' 		=> __("An error has occurred!", ET_DOMAIN)
				);
	}

	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	echo json_encode( $resp );
	exit;
}
add_action( 'wp_ajax_et_update_type_color', 'et_update_job_type_colors' ) ;

// =============================================
// ============== functions ====================
// =============================================

/**
 * Add expiration date for job when it's publish
 */
function et_insert_expired_date($job_id){
	if(get_post_type( $job_id ) != 'job')  return ;

	$template_id	= et_get_post_field ( $job_id ,'template_id' ) ;
	if(in_array($template_id, array('rss', 'indeed' , 'simplyhired', 'linked'))) return ;

	$plan_id 		= et_get_post_field( $job_id, 'job_package' );

	$duration 		= (int)et_get_post_field( $plan_id, 'duration' );
	$old_expiration = et_get_post_field( $job_id, 'expired_date' );

	if ( empty($old_expiration) || current_time('timestamp') > strtotime( $old_expiration ) ){
		$expired_date 	= date('Y-m-d H:i:s' , strtotime( "+{$duration} days" ));
		et_update_post_field( $job_id, 'expired_date', $expired_date );
	}

}
//add_action( 'save_post', 'et_insert_expired_date');

/**
 * update job expired date when change status to publish
*/
function et_update_expired_date ($new_status, $old_status, $post) {

	if(get_post_type( $post->ID ) != 'job')  return ;

	$template_id	= et_get_post_field ($post->ID ,'template_id' ) ;
	if( in_array($template_id, array('rss', 'indeed' , 'simplyhired', 'linked')) ) return ;

	$plan_id 		= et_get_post_field( $post->ID , 'job_package' );

	$duration 		= (int)et_get_post_field( $plan_id, 'duration' );

	$old_expiration = et_get_post_field( $post->ID , 'expired_date' );

	if($new_status == 'pending') {
		if( $old_status == "archive" || $old_status == "draft" ){ // force update expired date if job is change from draft or archive to publish

			$expired_date 	= date('Y-m-d H:i:s' , strtotime( "+{$duration} days" ));
			et_update_post_field( $post->ID , 'expired_date', '' );

		}
	} elseif( $new_status == 'publish' ) {
		if( $old_status == "archive" || $old_status == "draft" ){ // force update expired date if job is change from draft or archive to publish

			//$expired_date 	= date('Y-m-d H:i:s' , strtotime( "+{$duration} days" ));
			$current        = date('Y-m-d H:i:s', current_time('timestamp')  );
			$expired_time   = strtotime($current) + $duration*60*60*24;
			$expired_date   = date("Y-m-d H:i:s",$expired_time);


			et_update_post_field( $post->ID , 'expired_date', $expired_date );

		} else { // update expired date when the expired date less then current time

			if ( empty($old_expiration) || current_time('timestamp') > strtotime( $old_expiration ) ){
				//$expired_date 	= date('Y-m-d H:i:s' , strtotime( "+{$duration} days" ));
				$current        = date('Y-m-d H:i:s', current_time('timestamp')  );
				$expired_time   = strtotime($current) + $duration*60*60*24;
				$expired_date   = date("Y-m-d H:i:s",$expired_time);

				et_update_post_field( $post->ID, 'expired_date', $expired_date );
			}
		}

	}
}
add_action('transition_post_status','et_update_expired_date', 10, 3);


/**
 *
 */
function et_filter_by_date($where){
	$within = $_REQUEST['within'] ;

	$now 	= strtotime('now');
	$range 	= date('Y-m-d H:i:s', $now - $within);

	// if within is set as 0, count all post in database
	$range_sql = $within == 0 ? "" : "AND post_date >= '{$range}'";

	$where .= $range_sql;
	return $where;
}

/**
 * Build general success response for ajax request
 * @param $data returned data
 * @param $msg returned message
 * @param $code returned code
 * @since 1.0
 */
function build_success_ajax_response($data, $msg = '', $code = 200){
	return array(
		'success' 	=> true,
		'code' 		=> $code,
		'msg' 		=> $msg,
		'data' 		=> $data
		);
}

/**
 * Build general error response for ajax request
 * @param $data returned data
 * @param $msg returned message
 * @param $code returned code
 * @since 1.0
 */
function build_error_ajax_response($data, $msg = '',$code = 400){
	return array(
		'success' 	=> false,
		'code' 		=> $code,
		'msg' 		=> $msg,
		'data' 		=> $data
		);
}

/**
 * Insert job data into database
 * @param $args agrument
 * @since 1.0
 */
function et_insert_job( $args = array() ){
	$args['post_title']	= wp_strip_all_tags($args['post_title']);
	$args = wp_parse_args($args, array(
		'post_title' 		=> '',
		'post_content' 		=> '',
		'location' 			=> '',
		'job_type' 			=> '',
		'post_type' 		=> 'job',
		'category' 			=> '',
		'post_status' 		=> 'draft',
		'featured'			=>	0,
		'job_paid'			=>  0
	));

	try {

		wp_kses_post($args['post_content']);

		$args['post_content']	=	apply_filters('et_job_content', $args['post_content']);

		//echo 1;
		if( empty($args['job_type']) )
			throw new Exception( __('Job type is invalid', ET_DOMAIN), 400);
		if(empty($args['category']))
			throw new Exception( __('Job category is invalid', ET_DOMAIN), 400);

		foreach ($args['job_type'] as $key => $job_type) {
			if(!term_exists( $job_type, 'job_type')) {
				$term	=	get_term_by( 'id' , $job_type , 'job_type');
				if($term) {
					$args['job_type'][$key]	=	$term->slug;
					continue;
				}
				throw new Exception( __('Job type is invalid', ET_DOMAIN), 400);
			}
		}

		foreach ( $args['category'] as $key => $job_category ) {
			if(!term_exists( $job_category, 'job_category')) {
				$term	=	get_term_by( 'id' , $job_category , 'job_category');
				if( $term  ) {
					$args['category'][$key]	=	$term->slug;
					continue;
				}
				// get_term_by( $field, $value, $taxonomy, $output, $filter );
				throw new Exception( __('Job category is invalid', ET_DOMAIN), 400);
			}
		}

		$post_id = et_insert_post($args);

		if ( !($post_id instanceof WP_Error) ){
			foreach ($args['job_type'] as $key => $job_type)
				$terms = wp_set_object_terms($post_id, $job_type, 'job_type');

			foreach ($args['category'] as $key => $job_category)
				$terms = wp_set_object_terms($post_id, $job_category, 'job_category');

		}
		else
			throw new Exception( $post_id->get_error_message(), 400);

	} catch (Exception $e) {
		return new WP_Error($e->getCode(), $e->getMessage());
	}

	do_action('et_insert_job', $post_id);

	return $post_id;
}

/**
 * Update job data
 * @param
 * @since 1.0
 */
function et_update_job( $args = array() ){
	if ( empty($args['ID']) )
		return new WP_Error('400', __('Invalid Job ID',ET_DOMAIN));
	try {
		if(isset($args['post_content'])) {
			wp_kses_post($args['post_content']);
			$args['post_content']	=	apply_filters('et_job_content', $args['post_content']);
		}
		if(isset($args['post_status'])) {
			if(get_post_status ($args['ID']) != $args['post_status'] )
				do_action ('et_change_job_status', $args['ID'], $args['post_status'] );
		}

		if(empty($args['job_type']))
			throw new Exception( __('Job type is invalid', ET_DOMAIN), 400);
		if(empty($args['category']))
			throw new Exception( __('Job category is invalid', ET_DOMAIN), 400);

		foreach ($args['job_type'] as $key => $job_type) {
			if(!term_exists( $job_type, 'job_type')) {
				throw new Exception( __('Job type is invalid', ET_DOMAIN), 400);
			}
		}

		foreach ($args['category'] as $key => $job_category) {
			if(!term_exists($job_category, 'job_category')) {
				throw new Exception( __('Job category is invalid', ET_DOMAIN), 400);
			}
		}


		// change the created date when the old status is archive
		$post = get_post($args['ID']);
		if ( $post->post_status == 'archive' ){
			$args['post_date'] 		= current_time('mysql');
			$args['post_date_gmt'] 	= '';
		}



		// update job
		$post_id = et_update_post($args);

		if ( !($post_id instanceof WP_Error) ){
			foreach ($args['job_type'] as $key => $job_type)
				$terms = wp_set_object_terms( $post_id, $job_type, 'job_type');

			foreach ($args['category'] as $key => $job_category)
				$terms = wp_set_object_terms($post_id, $job_category, 'job_category');

		}
		else
			throw new Exception( $post_id->get_error_message(), 400);

	} catch (Exception $e) {
		return new WP_Error($e->getCode(), $e->getMessage());
	}

	do_action('et_update_job', $post_id);

	return $post_id;
}

/**
 * Create job response for ajax request
 * @since 1.0
 */
function et_create_jobs_response($post , $flush = false ){
	// default job
	$apply_email	=	trim(et_get_post_field($post->ID, 'apply_email'));
	$author			=	get_userdata( $post->post_author);

	//$job	=	wp_cache_get( $post->ID, 'je_job' );
	//if($flush || !$job ) {
		$job = array(
			'ID' 			=> $post->ID,
			'id' 			=> $post->ID,
			'title' 		=> $post->post_title,
			'content' 		=> apply_filters( 'the_content', $post->post_content ),
			'excerpt' 		=> empty($post->post_excerpt) ? apply_filters('the_excerpt', $post->post_content): $post->post_excerpt ,
			'categories' 	=> array(),
			'job_types' 	=> array(),
			'permalink' 	=> get_permalink($post->ID),
			'actionid' 		=> 0,
			'location' 		=> et_get_post_field($post->ID, 'location'),
			'full_location'	=> et_get_post_field($post->ID, 'full_location'),
			'location_lat'	=> et_get_post_field($post->ID, 'location_lat'),
			'location_lng'	=> et_get_post_field($post->ID, 'location_lng'),
			'status' 		=> $post->post_status,
			'date' 			=> date( get_option('date_format'), strtotime($post->post_date) ),
			'post_date'		=> $post->post_date,
			'author_id'		=> $post->post_author,
			'author_url' 	=> get_author_posts_url($post->post_author),
			'author' 		=> get_the_author_meta('display_name',$post->post_author),
			'renew_url'			=> et_get_page_link('post-a-job', array('job_id' => $post->ID )),
			'template_id' 		=> et_get_post_field($post->ID, 'template_id') ,
			'apply_method'		=> et_get_post_field($post->ID, 'apply_method') ,
			//'apply_email'		=>  ($apply_email != '') ? $apply_email : $author->user_email,
			'applicant_detail'	=> et_get_post_field($post->ID, 'applicant_detail') ,
			'expired_date' 		=> et_get_post_field($post->ID, 'expired_date')
		);

		if ( current_user_can('administrator') ){
			$job['actionid'] = $post->ID;
			$job['ID'] = $post->ID;
		}

		$categories = wp_get_post_terms($post->ID, 'job_category');
		foreach ((array)$categories as $cat) {
			$job['categories'][] = array(
				'term_id' => $cat->term_id,
				'slug'	=> $cat->slug,
				'name' => $cat->name,
				'url' => get_term_link($cat, 'job_category')
				);
		}

		$job_types = wp_get_post_terms($post->ID, 'job_type');
		$colors 	= et_get_job_type_colors();
		foreach ((array)$job_types as $type) {
			$job['job_types'][] = array(
				'term_id' => $type->term_id,
				'slug'	=> $type->slug,
				'name' => $type->name,
				'url' => get_term_link($type, 'job_type'),
				'color' => isset($colors[$type->term_id]) ? $colors[$type->term_id] : '13'
				);
		}

		// add some additional fields
		$fields = et_get_post_type_fields('job');
		foreach ((array)$fields as $name => $field) {
			if(isset($job[$name])) continue;
			$value = get_post_meta($post->ID, $field->metakey, true);
			$job[$name] = $value;
		}
		$job['apply_email']	=	($apply_email != '') ? $apply_email : et_get_user_field ($post->post_author,'apply_email');

		$job	=	apply_filters('et_jobs_ajax_response', $job);
		//wp_cache_set( $post->ID, $job, 'je_job', 15*24*60*60 );
	//}
	return $job;
}
	   add_filter ('et_jobs_ajax_response' , 'filter_job_response');
		function filter_job_response ($job2) 
		{
			global $wpdb,$post;
			//$data_edit=$wpdb->get_results( "SELECT * FROM wp_posts WHERE ID= '".$post->ID."' " );
			$job2['excerpt'] =get_excerpt(250);			
			$fields = JEP_Field::get_all_fields();
			$job2['mucluong'] =$fields[0]->name;
			$job2['money_mucluong'] =get_post_meta( get_the_ID(), 'cfield-'. $fields[0]->ID, true );	
			$job2['web_url']= get_bloginfo('url');
            $tag_load=get_the_tags();
            $job2['tag1']=$tag_load[0]->name;$job2['tag2']=$tag_load[1]->name;$job2['tag3']	=$tag_load[2]->name;$job2['tag4']=$tag_load[3]->name;$job2['tag5']=$tag_load[4]->name;	
            $job2['tag6']=$tag_load[5]->name;$job2['tag7']=$tag_load[6]->name;$job2['tag8']	=$tag_load[7]->name;$job2['tag9']=$tag_load[8]->name;$job2['tag10']=$tag_load[9]->name;	
            $job2['tag11']=$tag_load[10]->name;$job2['tag12']=$tag_load[11]->name;$job2['tag13']=$tag_load[12]->name;$job2['tag14']=$tag_load[13]->name;$job2['tag15']=$tag_load[14]->name;	
			$job2['author_job']=get_the_author_meta('roles',$post->post_author);$job2['author_job']=$job2['author_job'][0];
			$rows=$wpdb->get_results( "SELECT company_editor_id FROM wp_posts where ID ='".$post->ID."' " );
			$rows=$rows[0]->company_editor_id;
			$job2['id_com']=$rows;
			$rows=$wpdb->get_results( "SELECT display_name,logo FROM wp_post_company where ID ='".$rows."' " );
			$job2['name_company_editor']=$rows[0]->display_name;
			$rows=$rows[0]->logo;$rows=unserialize($rows);
			$job2['logo_company_editor']=$rows['company-logo'][0];
			$job2['get_id']=$_GET["com_i"];
			return $job2;
	   }
/**
 * Query jobs and return query object
 *
 * @since 1.0
 * @return WP_Query object
 */
function et_query_jobs($args){
	global $et_global;
	$args = wp_parse_args($args, array(
		'post_type' => 'job',
		'post_status' => array('publish'),
		'meta_key' => $et_global['db_prefix'] . 'featured',
		'orderby' => 'meta_value post_date',
		'order' => 'DESC'
		));
	$args = apply_filters('et_query_jobs_args', $args);

	return new WP_Query($args);
}

/**
 * query jobs and return jobs object
 *
 * @since 1.0
 * @return std_class object
 */
function et_get_jobs($args ){
	global $et_global;
	$args = wp_parse_args($args, array(
		'post_type' => 'job',
		'post_status' => array('publish','private'),
		'meta_key' => $et_global['db_prefix'] . 'featured',
		'orderby' => 'meta_value post_date',
		'order' => 'DESC'
		));
	$args = apply_filters('et_get_jobs_args', $args);

	// get jobs via wordpress function
	$jobs = get_posts($args);

	// add some additional fields
	$fields = et_get_post_type_fields('job');
	foreach ($jobs as $job) {
		foreach ($fields as $name => $field) {
			$value = get_post_meta($job->ID, $field->metakey, true);
			$job->$name = $value;
		}
	}

	return $jobs;
}

/**
 * Get job types list
 * @param $args same as arg in function get_terms
 * @since 1.0
 */
function et_get_job_types( $args = array() ){
	$args = wp_parse_args($args, array('orderby' => 'id', 'order' => 'ASC','hide_empty' => false ) );
	return get_terms('job_type', $args);
}

/**
 * Get job categories list
 * @param $args same as arg in function get_terms
 * @since 1.0
 */
function et_get_job_categories( $args = array() ){
	$args = wp_parse_args($args, array('orderby' => 'id', 'order' => 'ASC','hide_empty' => false ,'pad_counts' => true) );
	return get_terms('job_category', $args);
}
/**
 * Saving sorted categories to wordpress transient
 * @since 1.0
 */
function et_refresh_job_categories($args = array()){
	$ordered 	= array();
	$args		= wp_parse_args( $args, array('orderby' => 'id', 'order' => 'ASC','hide_empty' => false ,'pad_counts' => true) );
	$categories = get_terms('job_category', $args);
	$order 		= (array)get_option('et_category_order');
	if ($order) {
		foreach ($order as $pos) {
			foreach ($categories as $key => $cat) {
				if ($cat->term_id == $pos['item_id']){
					$ordered[] = $cat;
					unset($categories[$key]);
				}
			}
		}
		if (count($categories) > 0)
			foreach ($categories as $cat) {
				$ordered[] = $cat;
			}
		set_transient('job_categories', $ordered);
	}else {
		set_transient('job_categories', $categories);
	}
}
/**
 * Saveing sorted job types
 */
function et_refresh_job_types($args = array()){
	$ordered 	= array();
	$args		= wp_parse_args( $args, array('orderby' => 'id', 'order' => 'ASC','hide_empty' => false ,'pad_counts' => true) );
	$categories = get_terms('job_type', $args );
	$order 		= get_option('et_jobtype_order');

	if ($order) {
		foreach ($order as $pos) {
			foreach ($categories as $key => $cat) {
				if ($cat->term_id == $pos['item_id']){
					$ordered[] = $cat;
					unset($categories[$key]);
				}
			}
		}
		if (count($categories) > 0)
			foreach ($categories as $cat) {
				$ordered[] = $cat;
			}
		set_transient('job_types', $ordered);
	}else {
		set_transient('job_types', $categories);
	}
}
/**
 * get sorted categories from transient
 * @return array sorted categories
 */
function et_get_job_categories_in_order( $args = array()){
	et_refresh_job_categories($args);
	if ( get_transient('job_categories') == false )
		et_refresh_job_categories($args);

	return get_transient('job_categories');
}
/**
 * get sorted categories from transient
 * @return array sorted categories
 */
function et_get_job_types_in_order($args = array()){
	//et_refresh_job_types($args);
	if ( get_transient('job_types') == false )
		et_refresh_job_types($args);

	return get_transient('job_types');
}
/**
 * Retrieve job types
 *
 * @uses $post
 *
 * @param int $job_id Optional, default to current job ID. The job ID.
 * @return array
 */
function et_get_the_job_type ( $job_id = 0 ) {
	$colors 	= et_get_job_type_colors();	
	$job_types	= get_the_terms($job_id,'job_type');
	
	if ( !$job_types || is_wp_error($job_types) ){
		$job_types = array();
	}
	else{
		// add color & url to each job_type
		foreach($job_types as $type){
			$type->url		= get_term_link($type, 'job_type');
			$type->color	= isset($colors[$type->term_id]) ? $colors[$type->term_id] : 1;
		}	
	}
	$job_types = array_values( $job_types );

	// Filter name is plural because we return alot of categories (possibly more than #13237) not just one
	return apply_filters( 'et_get_the_job_type', $job_types );
}

/**
 * Retrieve job categories
 *
 * @uses $post
 *
 * @param int $job_id Optional, default to current job ID. The job ID.
 * @return array
 */
function et_get_the_job_category ( $job_id = 0 ) {
	
	$job_categories	=	 get_the_terms($job_id,'job_category');
	if ( ! $job_categories )
		$job_categories = array();

	$job_categories = array_values( $job_categories );

	// Filter name is plural because we return alot of categories (possibly more than #13237) not just one
	return apply_filters( 'et_get_the_job_category', $job_categories );
}
/**
 * Retrieve job category parents with separator.
 *
 * @param int $id Job Category ID.
 * @param bool $link Optional, default is false. Whether to format with link.
 * @param string $separator Optional, default is '/'. How to separate job categories.
 * @param bool $nicename Optional, default is false. Whether to use nice name for display.
 * @param array $visited Optional. Already linked to job categories to prevent duplicates.
 * @return string
 */
function et_get_job_category_parents( $id, $link = false, $separator = '/', $nicename = false, $visited = array() ) {
	$chain = '';
	$parent = get_term( $id, 'job_category' );
	
	if ( is_wp_error( $parent ) )
		return $parent;

	if ( $nicename )
		$name = $parent->slug;
	else
		$name = $parent->name;

	if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
		$visited[] = $parent->parent;
		$chain .= et_get_job_category_parents( $parent->parent, $link, $separator, $nicename, $visited );
	}
	
	if ( $link )
		$chain .= '<a href="' . esc_url( get_term_link( $parent, 'job_category' ) ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s", ET_DOMAIN ), $parent->name ) ) . '">'.$name.'</a>' . $separator;
	else
		$chain .= $name.$separator;
	return $chain;
}
/**
 * echo job category select option
 * @param $parent : int categories parent
 * @param $level  : job categories level
 * @param $selected : job categories slug be seleted when rendered
*/
function et_job_categories_option_list ( $parent =	0, $level = 0 , $selected = '') {
	$cats = et_get_job_categories(  array('parent' => $parent ));
	$delimeter	=	'';
	for ($i=0; $i<$level; $i ++) {
		$delimeter	.=	'&nbsp;&nbsp;';
	}
	if ( !empty($cats) ){
		foreach ($cats as $cat) {
			if( $cat->slug == $selected ) {
				$selected = 'selected="selected"';
			}
			echo '<option value="' . $cat->term_id . '" '.$selected.'>' .$delimeter. $cat->name . '</option>';
			et_job_categories_option_list ( $cat->term_id , $level + 1, $selected);
		}
	}
}
/**
 * display select box of job category
 */
function et_job_cat_select($name, $label = '', $args = array()){
	$cats = et_get_job_categories_in_order();
	$args = wp_parse_args( $args, array(
		'class' => '',
		'id' 	=> $name,
		'tabindex'	=> '5'
		) );
	?>
	<select name="<?php echo $name ?>" id="<?php echo $args['id'] ?>" class="<?php echo $args['class'] ?>" tabindex="<?php echo $args['tabindex'] ?>">
		<?php if($label) { ?><option value="0"><?php echo $label ?></option> <?php } ?>
		<?php et_job_cat_children_options('job_category', $cats); ?>
	</select>
	<?php
}
/**
 *  Display select box of job types
 */
function et_job_type_select($name, $label = '', $args = array()){
	$cats = et_get_job_types_in_order();
	$args = wp_parse_args( $args, array(
		'class' => '',
		'id' 	=> $name,
		'tabindex'	=>	'4' 
		) );
	?>
	<select name="<?php echo $name ?>" id="<?php echo $args['id'] ?>" class="<?php echo $args['class'] ?>" tabindex="<?php echo $args['tabindex'] ?>">
		<?php if($label) { ?><option value="0"><?php echo $label ?></option> <?php } ?>
		<?php et_job_cat_children_options('job_type', $cats); ?>
	</select> 
	<?php 
}

function et_job_cat_children_options($tax, $cats = array(), $parent = false, $level = 0){
		// re get categories if it empty
		if (empty($cats))
			$cats = array();

		// echo 
		foreach ($cats as $cat) {
			if ( ($parent == false && !$cat->parent) || $parent == $cat->parent ){
				// seting spacing
				$space = '';
				for ($i = 0; $i < $level; $i++ )
					$space .= '&nbsp;&nbsp;';

				$current 	= get_query_var( $tax );
				$selected 	= $current == $cat->slug ? 'selected="selected"' : '';
				global $current_filter;
				if (empty($current_filter)) $current_filter = array();
				if ( $current == $cat->slug )
					$current_filter[$tax] = $cat->name;

				// display option tag
				echo '<option value="' . $cat->slug . '" '. $selected .' rel="' . $cat->name . '">' . $space . $cat->name . '</option>';
				et_job_cat_children_options($tax, $cats, $cat->term_id, $level + 1);
			}
		} 
	}

/**
 * Return all job type count in database
 * 
 * @param an array contains count condition (post_author, post_status, category)
 * @since 1.0
 */
function et_get_job_count( $args = array() ){
	global $wpdb;
	$args = wp_parse_args($args, array(
		'post_author' => 0,
		'post_status' => 'publish',
		'category' => 0
	));

	// if user is specified
	$where_user = '';
	if ( $args['post_author'] != 0 ){
		$where_user = apply_filters('et_get_job_count_where', "AND (post_author = {$args['post_author']})");
	}

	// query from database
	$result = $wpdb->get_results(" SELECT post_status as type, COUNT(ID) as count FROM {$wpdb->posts} WHERE post_type = 'job' {$where_user} GROUP BY post_status ");

	$counts = array('publish' => 0, 'pending' => 0);

	foreach ((array)$result	as $value) {
		if ( isset($value->type) and isset($value->count) ){
			$counts[$value->type] = $value->count;
		}
	}
	return $counts;
}
/**
 *  retrieve job category link
 *	@param : object | int | string $job_cat	
 *	@return : string $url
*/
function et_get_job_category_link ($job_cat) {
	 if ( !is_object($job_cat) ) {
        if ( is_int($job_cat) ) {
                $job_cat = &get_term($job_cat, 'job_category');
        } else {
                $job_cat = &get_term_by('slug', $job_cat, 'job_category');
        }
    }
    return get_term_link( $job_cat, 'job_category' );
}
/**
 *  retrieve job type link
 *	@param : object | int | string $job_type	
 *	@return : string $url
*/
function et_get_job_type_link ($job_type) {
	 if ( !is_object($job_type) ) {
        if ( is_int($job_type) ) {
                $job_type = &get_term($job_type, 'job_type');
        } else {
                $job_type = &get_term_by('slug', $job_type, 'job_type');
        }
    }
    return get_term_link( $job_type, 'job_type' );
}

/**
 * Perform an status change for given job
 *
 * @param int job ID
 * @param string new status (draft, pending, publish, reject)
 * @since 1.0
 */
function et_change_job_status( $job_id, $new_status ){
	global $current_user;
	// return failure if current logged user doesn't have permission
	if ( !et_can_change_status($job_id, $new_status) )
		return new WP_Error('not_permission', __('You don\'t have permission to perform this action', ET_DOMAIN) );

	// get job via ID
	$job = get_post($job_id);

	// incase no job found, return failure
	if ( !$job )
		return new WP_Error('no_job_found', __('No job was found', ET_DOMAIN) );

	if ( !in_array($new_status, array('publish', 'draft', 'pending', 'reject', 'archive', 'trash') ) )
		return new WP_Error('input_invalid', __('Invalid input!', ET_DOMAIN));

	$old_status	=	get_post_status( $job_id );
	// perform approval for found job
	wp_update_post(array('ID' => $job_id, 'post_status' => $new_status));
	do_action ('et_change_job_status', $job_id, $new_status , $old_status);
	
	if ( $new_status == 'publish' )
		do_action('publish_post', $job_id);

	return true;
}

/**
 * Check if current user can edit his job
 *
 * @param Job ID
 * @since 1.0
 */
function et_can_edit_job($job_id){
	global $current_user;

	if ( current_user_can('administrator') )
		return true;

	// get job via ID
	$job = get_post($job_id);

	// incase no job found, return failure
	if ( !$job )
		return false;

	if ( $job->post_author != $current_user->ID )
		return false;

	return true;
}

/**
 * Check if current users can change his job's statuses
 *
 * @param Job ID
 * @since 1.0
 */
function et_can_change_status($job_id, $status){
	global $current_user;

	if ( current_user_can('administrator') )
		return true;

	// refuse user if they change their job status 
	// to any status which different with statuses below
	if ( !in_array( $status, array('pending', 'archive') ) ) 
		return false;

	// get job via ID
	$job = get_post($job_id);

	// incase no job found, return failure
	if ( !$job )
		return false;

	if ( $job->post_author != $current_user->ID )
		return false;

	return true;
}

/**
 * Perform approval for given job
 * 
 * @param int job ID
 * @since 1.0
 */
function et_approve_job($job_id){
	global $et_global;
	// update new status
	$result 	= et_change_job_status($job_id, 'publish');
	$job_paid	= et_get_post_field($job_id, 'job_paid');

	$job	=	get_post($job_id);
	// mark job as paid
	if ( $result && 2 != $job_paid){
		et_update_post_field($job_id, 'job_paid', 1);

		$jobpackage	=	et_get_post_field( $job_id, 'job_package' );
		
		je_update_company_paid_plans ($job->post_author, $jobpackage , true );
	}

	do_action ('je_approve_job' , $job );
	
	// return result
	return $result;
}

function je_update_job_old_order ($job, $current_jobpackage) {
	$job_id	=	$job->ID;
	$old_job_order	=	get_post_meta( $job_id, 'je_job_package_order', true );
	if($old_job_order) {
		$order	=	get_post( $old_job_order );
		if($order->post_status != 'publish') {
			$old_package	=	get_post_meta ($old_job_order, 'et_order_plan_id' , true);
			if( $old_package != $current_jobpackage )
				et_update_company_plans( $job->post_author , $old_package, false );

			if( $order->post_parent == $job_id ) {
				$other_jobs	=	query_posts( array(
											'post_type' => 'job' , 
											'meta_key' => 'je_job_package_order' , 
											'meta_value' => $old_job_order, 
											'post__not_in' => array($job_id) , 
											'showposts' => 1 )
										);

				foreach ((array)$other_jobs as $other_job) {
					$temp = (array)$order;
					$temp['post_parent'] = $other_job->ID;
					wp_update_post($temp);
				}
			}
		}
	}
}

/**
 * update order after approve job
*/
function je_update_order_after_approve_job ($job) {
	// search payment of this job and mask it as publish
	$orders = get_posts(array(
		'post_type' 		=> 'order',
		'post_status'  		=> array('pending'),
		'post_parent' 		=> $job->ID,
		'posts_per_page' 	=> 1,
		'orderby' 			=> 'post_date',
		'order' 			=> 'DESC'
	));
	/**
	 * update job order data
	*/
	foreach ((array)$orders as $order) {
		$temp = (array)$order;
		$temp['post_status'] = 'publish';
		wp_update_post($temp);
	}

	//$group_job	=	je_get_job_in_package ( $job->post_author );
	$order		=	get_post_meta( $job->ID, 'je_job_package_order', true );

	wp_update_post( array('ID' => $order , 'post_status' => 'publish') );

	$jobpackage	=	et_get_post_field( $job->ID, 'job_package' );

	/**
	 * update company paid plan for check next post job action
	*/
	je_update_company_paid_plans ($job->post_author, $jobpackage , 1 );
	
	et_update_post_field ($job->ID, 'job_paid', 1);

	$jobs	=	get_posts (array(
		'post_type' 		=> 'job',
		'post_status'  		=> array('pending'),
		'meta_value'		=> $order,
		'meta_key'			=> 'je_job_package_order',
		'posts_per_page' 	=> -1,
		'orderby' 			=> 'post_date',
		'order' 			=> 'DESC'
	));
	
	$job_opts	=	new ET_JobOptions();
	$pending	=	$job_opts->use_pending() ;

	if(!$pending) // not pending : update all job in group to publish
		foreach ((array)$jobs as $order) {
			wp_update_post( array('ID' => $order->ID , 'post_status' => 'publish') );
			et_update_post_field ($order->ID, 'job_paid',1);
		}
	else  // if use pending, just change job to paid, and keep pending
		foreach ((array)$jobs as $order) {				
			//wp_update_post( array('ID' => $value , 'post_status' => 'publish') );
			et_update_post_field ($order->ID, 'job_paid', 1 );
			
		}

}

add_action( 'je_approve_job', 'je_update_order_after_approve_job'  );


/**
 * Perform rejection for given job
 *
 * @param int job id
 * @since 1.0
 */
function et_reject_job($job_id, $reason = '', $refund = 0){
	// change status
	$result = et_change_job_status($job_id, 'reject');

	// send email to 
	$job = get_post($job_id);
	$company = get_user_by('id', $job->post_author);

	// if user is a company
	if ( user_can( $company, 'company') ){
		// $mail['message']	=	et_get_mail_header(). $mail['message'].et_get_mail_footer();

		$instance	=	JE_Mailing::get_instance();
		$instance->reject_job ( $company , $job, $reason );
	}
	return $result;
}

/**
 * Archive a job
 *
 * @param int job id
 * @since 1.0
 */
function et_archive_job($job_id){
	return et_change_job_status($job_id, 'archive');	
}

/**
 *  Toggle featured status for given job
 *
 * @param int job ID
 * @since 1.0
 */
function et_toggle_job_feature($job_id){
	global $current_user;
	// return failure if current logged user doesn't have permission
	if ( !current_user_can('administrator') )
		return new WP_Error('not_permission', __('You don\'t have permission to perform this action', ET_DOMAIN) );

	$featured = et_get_post_field( $job_id, 'featured' );
	$new = $featured;
	if ( $featured )
		$new = 0;
	else 
		$new = 1;

	et_update_post_field($job_id, 'featured', $new);
	return true;
}

/**
 * get user new feeds
 * @param int $user_id
 */
function et_get_user_new_feeds ($user_id = 0) {
	if( $user_id == 0 ) {
		global $user_ID;
		$user_id	=	$user_ID;
	}
	$feeds	=	get_user_meta($user_id, 'et_user_new_feeds',true );

	if( !is_array($feeds) )
		$feeds	=	array ();

	return $feeds;
}
/**
 * update user new feeds
 * @param int $user_id : user id
 * @param array $new_feeds : feed id
 */
function et_update_user_new_feeds ($user_id, $new_feeds =	array()) {
	update_user_meta($user_id, 'et_user_new_feeds', $new_feeds );
}
/**
 * delete user new feeds data
 * @param unknown_type $user_id
 * @throws Exception
 */
function et_delete_user_new_feeds ($user_id = 0) {
	if($user_id == 0 ){
		global $user_ID;
		$user_id = $user_ID;
	}
	et_update_user_new_feeds($user_id, array ());
}
/**
 * Count jobs within amount of time
 *
 * @param $within in milisecond
 */
function et_count_posts_by_time($post_type, $within = 0){
	global $wpdb, $wp_post_statuses;

	$now 	= strtotime('now');
	$range 	= date('Y-m-d H:i:s', $now - $within);

	// if within is set as 0, count all post in database
	$range_sql = $within == 0 ? "" : "AND post_date >= '{$range}'";

	$sql 		= "SELECT post_status, COUNT(ID) as count FROM {$wpdb->posts} WHERE post_type = '{$post_type}' {$range_sql} GROUP BY post_status";
	$rows 		= $wpdb->get_results($sql);
	$return 	= array();
	$statuses 	= array_keys( $wp_post_statuses );

	foreach ($rows as $row) {
		$return[$row->post_status] = $row->count;
	}
	foreach ($statuses as $status) {
		if ( empty($return[$status]) ){
			$return[$status] = 0;
		}
	}

	return (object)$return;
}

/**
 * Count jobs within amount of time
 *
 * @param $within in milisecond
 */
function et_count_jobs($within = 0){
	return et_count_posts_by_time('job', $within);
}

/**
 * Count applications within amount of time
 *
 * @param $within in milisecond
 */
function et_count_applications($within = 0){
	return et_count_posts_by_time('application', $within);
}

/**
 * Check if featured system are enable or not
 *
 * @return bool
 */
function et_is_enable_feature(){
 	$job_option = new ET_JobOptions();
 	return $job_option->use_feature();
}

/**
 * return post job url link
 *
 * @since 1.0
 */
function et_get_post_job_url( $url, $pagename = '', $params = array() ){
	global $wp_rewrite;

	if ( !$wp_rewrite->using_permalinks() ) return $url;

	if ( ($pagename == 'page-post-a-job' || $pagename == 'post-a-job') 
		AND !empty($params['job_id']) ){
		$url = strtok($url, '?');
		$url .= $params['job_id'];
	}

	return $url;
}

add_filter('et_get_page_link', 'et_get_post_job_url', 1, 3);

/**
 * return job type's colors in database
 */
function et_get_job_type_colors(){
	// $options = new ET_GeneralOptions();
	// return $options->get_job_type_colors();
	$job_type	=	new JE_JobType ();
	return $job_type->get_color ();
}

/**
 * Update job type's color
 */
function et_update_job_type_color($term_id, $color){
	$options = new ET_GeneralOptions();
	$colors = $options->get_job_type_colors();

	$colors[$term_id] = $color;
	$options->set_job_type_colors($colors);
}

function et_get_job_status_labels($statuses=array('publish')){
	$status_labels	= array(

		'reject'	=> __('Rejected jobs',ET_DOMAIN)
	);
	$archive	=	'';
	$reject		=	'';
	foreach ($statuses as $value) {
		if($value == 'publish')	return __('LATEST JOBS',ET_DOMAIN);

		if($value == 'archive') {
			$archive = __('Archived jobs',ET_DOMAIN);

		}
		if($value == 'reject') {
			$reject = __('Rejected jobs',ET_DOMAIN);
		}
	}
	if( $archive != '' && $reject != '' ) {
		return __('Archived & rejected jobs',ET_DOMAIN);
	} else {
		if($archive != '')
			return $archive;
		else return $reject;
	}


}
