<?php
if(class_exists("JE_AdminSubMenu")) :
	class ET_MenuPayment extends JE_AdminSubMenu{

		/**
		 * Constructor for payment menu item
		 * @since 1.0
		 */
		function __construct(){
			parent::__construct(
						__('Payments', ET_DOMAIN), 
						__('PAYMENTS', ET_DOMAIN),
						__('Probably the best thing in the world.', ET_DOMAIN),
						'et-payments',
						 'icon-payment' ,
						15
					);		
			
		}

		public function on_add_scripts(){
			parent::on_add_scripts();
			$this->add_existed_script('et_underscore');
			$this->add_existed_script('et_backbone');
			$this->add_existed_script('job_engine');
			$this->add_existed_script('admin_scripts');
			$this->add_script('et_payment', get_bloginfo('template_url') . '/js/admin/payments.js', array('jquery', 'et_underscore','et_backbone',  'job_engine', 'admin_scripts'));
		}

		public function on_add_styles(){
			parent::on_add_styles();
		}

		public function get_header(){
			$count 		= et_count_posts_by_time('order');
			$revenue 	= et_get_revenue(30*24*60*60);
			$pending_revenue 	= et_get_revenue(30*24*60*60, 'pending');
			$total	=	0;
			foreach ($count as $value) { 
				$total += $value ;
			}
			$total	=	number_format($total,0,'.','.');
			?>
			<div class="et-main-header">
				
				<div class="title font-quicksand"><?php _e('Payments', ET_DOMAIN) ?></div>
				<div class="desc">
					<?php _e('Probably the best thing in the world.', ET_DOMAIN) ?>
				</div>			
				<ul class="et-head-statistics">
	        			<li>
	        				<div class="icon-overview orange">
	        					<div class="icon" data-icon="^"></div>
	        				</div>
	        				<div class="info">
		        				<div class="number font-quicksand orange bg-none"><?php echo $count->pending ?></div>
		        				<div class="type"><?php _e("Pending",ET_DOMAIN);?></div>
	        				</div>
	        			</li>
	        			<li>
	        				<div class="icon-overview grey">
	        					<div class="icon" data-icon="%"></div>
	        				</div>
	        				<div class="info">
		        				<div class="number font-quicksand grey bg-none"><?php echo et_get_price_format($pending_revenue, 'sup'); ?></div>
		        				<div class="type"><?php _e("Unpaid",ET_DOMAIN);?></div>
		        			</div>
	        			</li>
	        			<li>
	        				<div class="icon-overview green">
	        					<div class="icon" data-icon="%"></div>
	        				</div>        				
	        				<div class="info">
		        				<div class="number font-quicksand green bg-none"><?php echo et_get_price_format($revenue, 'sup'); ?></div>
		        				<div class="type"><?php _e("Revenue Made",ET_DOMAIN);?></div>
	        				</div>
	        			</li>
	        		</ul>
			</div>
			<?php
		}
		function view() {
			
			$this->get_header();
			$payment_gate	=	ET_Payment::get_support_payment_gateway();
			$orders			=	ET_JobOrder::get_orders(array ( 
																'post_status' => array ('pending','publish', 'draft'),
																'payment' => array_keys( $payment_gate )
															)
														);
			$currency		=	ET_Payment::get_currency_list();
		?>
			<div class="et-main-content">
		        <div class="search-box">
		        	<input type="text" class="bg-grey-input search-jobs" value="" placeholder="<?php _e("Search jobs...", ET_DOMAIN) ?>" />
		        	<span class="icon" data-icon="s"></span>
		        </div>
		        <div class="et-main-left">
		        	<div class="title font-quicksand"><?php _e("Payment Gateway",ET_DOMAIN);?></div>
	        		<ul class="et-menu-content processor-list">
	        			<li><a href="all" rel="all" class="active"><?php _e('All', ET_DOMAIN) ?></a></li>
	        			<?php foreach ($payment_gate as $key => $value) { ?>
	        			<li><a rel="<?php echo $key?>" href="#<?php echo $key ?>"><?php echo $value['label']?></a></li>
	        			<?php }?>
	        		</ul>
		        </div>
		        <div id="payments_list" class="et-main-main clearfix list">
		        	<div class="title font-quicksand"><?php  _e('Latest Payments', ET_DOMAIN) ?></div>
		        	<?php if($orders->have_posts()) {?>
		        	<ul class="list-inner list-payment">
		        	<?php 
		        	$plans   = et_get_payment_plans();
		        	while($orders->have_posts()) { $orders->the_post(); 
		        		global $post;
		        		$order		=	new ET_JobOrder(get_the_ID ());
		        		$order_data	=	$order->get_order_data();
		        		//$job_id		=	$order_data['job_id'];
		        		$job 		= 	get_post($post->post_parent);

		        		if(!isset( $currency[$order_data['currency']])) {
		        			$icon	=	$order_data['currency'];
		        		} else {
		        			$icon	=	$currency[$order_data['currency']]['icon'];
		        		}
		        		
		        		if (!empty($order_data['payment_plan']) && is_numeric($order_data['payment_plan']))
		        			$payment_plan_id = $order_data['payment_plan'];
		        		else 
		        			$payment_plan_id = get_post_meta( $order_data['job_id'], 'et_job_package', true );
		        	?>
	        			<li>

	        				<div class="method"><?php echo $payment_gate[$order_data['payment']]['label']?></div>
	        				<div class="content">
	        					<?php if($post->post_status == 'pending') { ?> 
	        						<a title="<?php _e("Pending", ET_DOMAIN); ?>" class="color-red error" href="#"><span class="icon" data-icon="!"></span></a>
	        					<?php } elseif($post->post_status == 'publish') { ?> 
	        						<a title="<?php _e("Confirmed", ET_DOMAIN); ?>" class="color-green" href="#"><span class="icon" data-icon="2"></span></a>
	        					<?php }else {
	        					?> 
	        						<a title="<?php _e("Failed", ET_DOMAIN); ?>" class="color" style="color :grey;" href="#"><span class="icon" data-icon="*"></span></a>
	        					<?php 
	        					} ?>
	        					<span class="price font-quicksand">
	        						<?php echo et_get_price_format($order_data['total'], 'sup'); ?>
	        					</span>

	        					<?php if( $job ) { 
	        						if($job->post_type == 'job') {
			        					?>
											<a target="_blank" href="<?php echo get_permalink($job->ID) ?>" class="job job-name"><?php echo $job->post_title ?></a>
										<?php
										echo '(' . get_the_title ($payment_plan_id ). ')' ;
			        					 _e('at', ET_DOMAIN); 
	        						} else {
	        							printf(__("%s by", ET_DOMAIN), $job->post_title .'(' . get_the_title ($payment_plan_id ). ')' );
	        						} ?> 

	        					<a target="_blank" href="<?php echo get_author_posts_url($job->post_author, $author_nicename = '') ?>" class="company"><?php echo get_the_author_meta('display_name',$job->post_author) ?></a>
								
								<?php 
								} else { 
									$compnay_name	=	'<a target="_blank" href="'.get_author_posts_url($post->post_author).'" class="company">'.get_the_author_meta('display_name',$post->post_author) .'</a>';
								?>
									<span><?php printf (__("This job has been deleted by %s", ET_DOMAIN) , $compnay_name ); ?></span>
								<?php } ?>
		 						
	        				</div>
	        			</li>
	        		<?php }?>
		        	</ul>
		        	<?php } else { _e('There are no payments yet.', ET_DOMAIN) ;}?>
		        	<?php if($orders->max_num_pages > 1) {?>
		        	<button class="et-button btn-button" id="load-more">
		        		<?php _e("Load more",ET_DOMAIN);?>
					</button>	        			
					<?php }?>
		        </div>
		    </div>	
		        
		<?php 
			$this->get_footer();
		}
	}
endif;

add_action ('wp_ajax_et-filter-job-processor', 'et_fitler_job_processor');
function et_fitler_job_processor () {
	
	$job_title	=	$_POST['job'];
	$gateway 	=	$_POST['payment'];
	$page 		=	isset($_POST['page']) ? $_POST['page'] : 1;

	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );

	if( $job_title != '') {
		
		$query_search =	et_query_jobs (
								array (
									'post_status' => array('pending', 'publish', 'archive', 'expired'),
									's'	=>	$job_title
								)
						) ;
		$job_title	=	array ();
		while ($query_search->have_posts()) {
			$query_search->the_post ();
			$job_order_arr 	=	et_get_post_field (get_the_ID (), 'job_order');

			if(!empty($job_order_arr)) {
				$job_title	=	array_merge($job_title, $job_order_arr);
			}
		}
		// search job empty return no data
		if(empty($job_title) ) {
			echo json_encode(array (
	        	'data'		=>	'',
	        	'success'	=>	 false,
	        	'msg'		=>	__('There are no payments yet.', ET_DOMAIN)
        	));
        	exit;
		}
	}

	$payment_gate	=	ET_Payment::get_support_payment_gateway();
	$currency		=	ET_Payment::get_currency_list();
	if( $gateway == "" || $gateway == 'all')  {
		$gateway 	=	array_keys($payment_gate);
	}
	$args	=	array (
		'payment'		=>	$gateway,
		'post_status'	=>	array ('pending', 'publish'),
		'post__in'		=>	$job_title,
		'paged' 		=>  $page 
	);
	
	$query	=	ET_JobOrder::get_orders ($args);
	$data 	=	'';
	$plans   = et_get_payment_plans();
	if($query->have_posts()) {
		while($query->have_posts()) { 

			$query->the_post(); 
			global $post;
    		$order	=	new ET_JobOrder(get_the_ID ());
    		$order_data	=	$order->get_order_data();
    		$job_id		=	$order_data['job_id'];
    		$plan		=	'';

    		if (!empty($order_data['payment_plan']) && is_numeric($order_data['payment_plan']))
    			$payment_plan_id = $order_data['payment_plan'];
    		else 
    			$payment_plan_id = get_post_meta( $order_data['job_id'], 'et_job_package', true );

    		//if (!empty($plans[$payment_plan_id]['title']))
			$plan	=	 '(' . get_the_title ($payment_plan_id) . ') ' ;
			
			if($post->post_status == 'pending') 
				$status	=	'<a title="'.__("Pending", ET_DOMAIN).'" class="color-red error" href="#"><span class="icon" data-icon="!"></span></a>';
			elseif($post->post_status == 'publish') 
				$status	=	'<a title="'.__("Confirmed", ET_DOMAIN).'" class="color-green" href="#"><span class="icon" data-icon="2"></span></a>';
			else  
				$status	=	'<a title="'.__("Failed", ET_DOMAIN).'" class="color" style="color :grey;" href="#"><span class="icon" data-icon="*"></span></a>';

	 		$data 		.= '
				<li title="'.$post->post_status.'">
					<div class="method">'. $payment_gate[$order_data['payment']]['label'].'</div>
					<div class="content">'.$status.'
						<span class="price font-quicksand">
						<sup>'. $currency[$order_data['currency']]['icon'].'</sup>'.$order_data['total'].'
						</span>
						<a href="'. get_permalink($job_id) .'" target="_blank" class="job">
							'. get_the_title($job_id) .'
						</a> '.$plan.__("at",ET_DOMAIN).' 
						<a href="'.get_author_posts_url($post->post_author).'" class="company" target="_blank">
							'. get_the_author_meta ('display_name').'
						</a>
					</div>
				</li>';
        }       
        echo json_encode(array (
        	'data'		=>	$data,
        	'success'	=>	 true,
        	'msg'		=>	'',
        	'total'		=>  $query->max_num_pages 
        ))	;
	} else {
	 		echo json_encode(array (
        	'data'		=>	$data,
        	'success'	=>	 false,
        	'msg'		=>	__('There are no payments yet.', ET_DOMAIN)
        ))	;
	}
	exit;
}
