<?php 
/**
 * Declare Section overview in admin panel
 * @since 1.0
 */
if(class_exists("JE_AdminSubMenu")) :
	class ET_MenuOverview extends JE_AdminSubMenu{

		/**
		 * Constructor for Overview menu
		 * @since 1.0
		 */
		function __construct(){
			parent::__construct( __('Overview', ET_DOMAIN), 
								__('OVERVIEW', ET_DOMAIN), 
								__('Manage how your job board looks and feels.', ET_DOMAIN), 
								'et-overview',
								'icon-menu-overview',
								5 ); // position 5

			// use this menu as default menu
			$this->add_action('et_admin_enqueue_styles-engine-settings', 'on_add_styles');
			$this->add_action('et_admin_enqueue_scripts-engine-settings', 'on_add_scripts');
			$this->add_action('wp_ajax_et_archive_expired_jobs', 'archive_job');

		}

		public function on_add_styles(){
			parent::on_add_styles();
		}

		public function on_add_scripts(){
			parent::on_add_scripts();
			wp_register_script( 'et-jquery' , 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
			$this->add_existed_script('et_underscore');
			$this->add_existed_script('et_backbone');
			$this->add_existed_script('job_engine');
			$this->add_existed_script('admin_scripts');
			$this->add_script( 'et_overview', get_bloginfo('template_url') . '/js/admin/overview.js', array('et-jquery','et_underscore', 'et_backbone', 'job_engine', 'admin_scripts') );
		}

		protected function localize_script($handle, $object_name, $l10n){
			wp_localize_script( $handle, $object_name, $l10n );
		}

		public function archive_job(){
			if (current_user_can( 'manage_options' )){
				$count = et_archive_expired_jobs( $_REQUEST['paged'] );
				$resp = array('success' => true, 'data' => array( 'count' => $count ));
			}else {
				$resp = array('success' => false, 'msg' => __("You don't have permission to perform this action"));
			}
			header( 'HTTP/1.0 200 OK' );
			header( 'Content-type: application/json' );
			echo json_encode( $resp );
			exit;
		}

		public function get_header(){
			?>
			<div class="et-main-header">
				<div class="title font-quicksand"><?php _e('Overview', ET_DOMAIN) ?></div>
				<div class="desc">
					<?php _e('what happened in', ET_DOMAIN) ?>
					<span class="select-style">
						<select id="time_limit" title="<?php _e('latest 30 days', ET_DOMAIN) ?>" arrow="▼">
							<option value="<?php echo 30*24*60*60 ?>" selected="selected"><?php _e('latest 30 days', ET_DOMAIN) ?></option>
							<option value="<?php echo 15*24*60*60 ?>"><?php _e('latest 15 days', ET_DOMAIN) ?></option>
							<option value="<?php echo 7*24*60*60 ?>"><?php _e('latest 7 days', ET_DOMAIN) ?></option>
							<option value="<?php echo 24*60*60 ?>"><?php _e('last days', ET_DOMAIN) ?></option>
							<option value="<?php echo 0 ?>"><?php _e('all time', ET_DOMAIN) ?></option>
						</select>
					</span>
				</div>
				<?php 
				//delete_transient('et_revenue');
				$revenue 	= et_get_revenue(30*24*60*60);
				$count 		= et_count_jobs(30*24*60*60);
				$appCount 	= wp_count_posts('application');
				?>
				<ul class="et-head-statistics">
					<li>
						<div class="icon-overview orange">
							<div class="icon" data-icon="^"></div>
						</div>
						<div class="info">
							<div id="stats_pending_jobs" class="number font-quicksand orange bg-none"><?php echo $count->pending ?></div>
							<div class="type"><?php _e('Pending Jobs', ET_DOMAIN) ?></div>
						</div>
					</li>
					<li>
						<div class="icon-overview blue">
							<div class="icon" data-icon="l"></div>
						</div>
						<div class="info">
							<div id="stats_active_jobs" class="number font-quicksand blue bg-none"><?php echo $count->publish ?></div>
							<div class="type"><?php _e('Posted Jobs', ET_DOMAIN) ?></div>
						</div>
					</li>
					<li>
						<div class="icon-overview green">
							<div class="icon" data-icon="%"></div>
						</div>
						<div class="info">
							<div id="stats_revenue" class="number font-quicksand green bg-none">
								<?php echo et_get_price_format($revenue, 'sup'); ?></div>
							<div class="type"><?php _e('Revenue Made', ET_DOMAIN) ?></div>
						</div>
					</li>
					<li>
						<div class="icon-overview yellow">
							<div class="icon" data-icon="I"></div>
						</div>
						<div class="info">
							<div id="stats_applications" class="number font-quicksand yellow bg-none"><?php echo $appCount->publish ?></div>
							<div class="type"><?php _e('Applications', ET_DOMAIN) ?></div>        					
						</div>
					</li>
					<?php 
					$num = et_expired_jobs_count();
					if ($num > 0){
					?>
					<li id="expired_jobs">
						<a class="icon-expired button" title="<?php _e('Click icon to archive jobs', ET_DOMAIN) ?>" id="archive" href="#">
							<span class="icon" data-icon="#"></span>
						</a>
						<div class="info">
							<div id="" class="number font-quicksand bg-none"><?php echo $num ?></div>
							<div class="type"><?php _e('Expired jobs', ET_DOMAIN) ?></div>        					
						</div>
					</li>
					<?php } ?>
				</ul>
			</div>
			<?php
		}

		/**
		 * 
		 */
		public function view(){
			$this->get_header();
			?>
			<div class="et-main-content">
						
				<div class="et-main-main no-margin clearfix overview list" style="overflow: hidden">
					<div class="title font-quicksand"><?php _e('Pending Jobs', ET_DOMAIN) ?></div>
					<?php global $post, $et_after_time;

					// $pending_jobs = et_query_jobs(array(
					// 	'post_status' 		=> array('pending'),
					// 	'posts_per_page' 	=> -1, // apply_filters('et_items_per_page', 10)
					// 	'orderby' 			=> 'date'
					// ));

					$args	=	array(
						'post_type' 		=> 'job',
						'post_status' 		=> array('pending'),
						'posts_per_page' 	=> -1,
						'meta_key'			=>	'et_job_paid',
					);
					add_filter('posts_orderby', 'et_filter_orderby');
					$pending_jobs	=	new WP_Query ( $args );
					remove_filter('posts_orderby', 'et_filter_orderby');
					$pending_jobs_data	=	array();
					if ( $pending_jobs->have_posts() ) :  ?>
					<ul class="list-inner list-payment pending-jobs">
						<?php while ( $pending_jobs->have_posts() ) : 
							$pending_jobs->the_post();
							global $post;
							
							$post->id = $post->ID;
							$pending_jobs_data[]	=	$post;
							 ?>
							<li data-id="<?php the_ID(); ?>">
								<div class="method">
									<a class="color-active act-approve" rel="<?php echo $post->ID ?>" href="#"><span class="icon" data-icon="3"></span></a>
									<a class="color-orange act-reject" rel="<?php echo $post->ID ?>" href="#"><span class="icon" data-icon="*"></span></a>
								</div>
								<a class="color-red error" href="#"><span class="icon" data-icon="!"></span></a>
								<div class="content" data-id="<?php echo $post->ID ?>">
									<a target="_blank" href="<?php the_permalink() ?>" class="job job-name"><?php the_title(); ?></a> <?php _e('at', ET_DOMAIN); ?> <a target="_blank" href="<?php echo get_author_posts_url($post->post_author) ?>" target="_blank" class="company"><?php echo get_the_author() ?></a>
								</div>
							</li>
						<?php endwhile; ?>
					</ul>
					<?php else:?>
						<p class="" ><?php _e('There is no pending job.', ET_DOMAIN) ?></p>
					<?php endif; ?>
					<script type="application/json" id="pending_jobs_data">
					<?php 
						echo json_encode( array_map( 'et_create_jobs_response' , $pending_jobs_data)  );
					?>
					</script>
					<!-- <a class="view-more" href="#">View all jobs</a> -->

					<?php 
					/** =======================================
					 * 	Payments
					 *	=======================================*/
					?>	

					<div class="title font-quicksand"><?php _e('Latest Payments', ET_DOMAIN)?></div>
					<?php  
					global $et_global;
					$payments = ET_JobOrder::get_orders(array(
							'post_type' 		=> 'order',
							'post_status'  		=> array('publish'),
							'posts_per_page' 	=> apply_filters('et_items_per_page', 10)
						));
					?>
					<?php if ( $payments->have_posts() ) : ?>
						<ul class="list-inner list-payment overview-payments">
							<?php while ( $payments->have_posts() ) :
								$payments->the_post();
								if ( empty( $post->post_parent ) ) continue;

								$job = get_post($post->post_parent);
								
							?>
							<li class="payment-item payment-item-<?php echo $post->ID ?>">
								<div class="content">
									<span class="price font-quicksand">
										<?php echo et_get_price_format( et_get_post_field($post->ID, 'order_total'), 'sup' ) ?></span>
									<?php if($job) { ?>
									<a target="_blank" href="<?php echo get_permalink($job->ID) ?>" class="job job-name"><?php echo $job->post_title ?></a> <?php _e('at', ET_DOMAIN); ?> <a target="_blank" href="<?php echo get_author_posts_url($job->post_author, $author_nicename = '') ?>" class="company"><?php echo get_the_author_meta('display_name',$job->post_author) ?></a>
									<?php } else { 
										$compnay_name	=	'<a target="_blank" href="'.get_author_posts_url($post->post_author).'" class="company">'.get_the_author_meta('display_name',$post->post_author) .'</a>';
										?>
									<span><?php printf (__("This job has been deleted by %s", ET_DOMAIN) , $compnay_name ); ?></span>
									<?php } ?>
								</div>
							</li>
							<?php endwhile; ?>
						</ul>
						<a class="view-more" href="<?php echo et_get_admin_page('et-payments') ?>"><?php _e('View all Payments', ET_DOMAIN)?></a>
					<?php else : ?>
						<p class="" ><?php _e('There are no payments at this period.', ET_DOMAIN) ?></p>
					<?php endif ;?>

					<?php 
					// ========================================
					// Companies 
					// ========================================
					?>

					<div class="title font-quicksand"><?php _e('Latest Companies', ET_DOMAIN)?></div>
					<?php 
					$companies = et_get_users_post_count();
					if ( !empty($companies) ) :
					?>
						<ul class="list-companies clearfix">
							<?php 
							foreach ((array)$companies as $company) : ?>
								<li>
									<a target="_blank" href="<?php echo get_author_posts_url($company->ID) ?>" class="companies"><?php echo $company->display_name ?></a> 
									<?php 
										echo '<span class="job-companies">'. sprintf( et_number( __('No job', ET_DOMAIN), __('%d job', ET_DOMAIN), __('%d jobs', ET_DOMAIN), $company->count ), $company->count) .' </span>'; ?>
									<!-- <span class="job-companies">2 job</span> -->
								</li>
							<?php endforeach; ?>
						</ul>
						<a class="view-more" href="<?php echo et_get_admin_page('et-companies') ?>"><?php _e('View all Companies', ET_DOMAIN) ?></a>
					<?php else :?>
						<p class="" ><?php _e('There are no registered companies.', ET_DOMAIN) ?></p>
					<?php endif;?>
				</div>
			</div>
			<?php
			$this->get_footer();
		}

		/**
		 * Overview footer
		 */
		public function get_footer(){
			echo et_template_modal_reject(); // insert modal reject job template
		}
	}
endif;
?>