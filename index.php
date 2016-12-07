<?php
	$imgUrl	=	$et_global['imgUrl'];
	$jsUrl	=	$et_global['jsUrl'];
	$general_opts	=	new ET_GeneralOptions();
	$arrAuthors	= array();

	get_header();
?>

<div class="wrapper clearfix">
<?php
	get_template_part( 'template/template' , 'search' );

	$return_txt = trim(str_replace("&nbsp;", "", strip_tags($general_opts->get_site_demonstration())));
	if( $return_txt != '') { ?>
		<div class="header-content">
			<?php do_action ('et_before_site_demonstation'); ?>
			<div class="main-center">
				<div class= "desc headline">
					<?php echo apply_filters('et_jobengine_demonstration', $general_opts->get_site_demonstration () ); ?>
				</div>
			</div>
			<?php do_action ('et_after_site_demonstation'); ?>
		</div>
<?php
	}
?>

	<div class="main-center clearfix padding-top30">
		 <?php
			if(is_active_sidebar('sidebar-home-top')) { ?>
				<div class="sidebar-home-top <?php if(current_user_can('manage_options') ) echo 'sortable' ?>" id="sidebar-home-top" >
					<?php
						dynamic_sidebar('sidebar-home-top');

					?>
				</div>
		<?php } ?>
		<div class="main-column">
		<!-- Pending Jobs -->
		<?php
		if(current_user_can ('manage_options')) {
			$args	=	array(
				'post_type' 		=> 'job',
				'post_status' 		=> array('pending'),
				'posts_per_page' 	=> -1,
				'meta_key'			=>	'et_job_paid',
				'orderby'			=>	'meta_value post_date',
				'order'				=>  'DESC'
			);
			add_filter('posts_orderby', 'et_filter_orderby');
			$pending_job	=	new WP_Query ( $args );
			remove_filter('posts_orderby', 'et_filter_orderby');
			?>
			<div id="pending_jobs_container" <?php if( !$pending_job->have_posts() ){ ?>style="display:none;"<?php }?> >
				<h3 class="main-title"><?php _e('PENDING JOBS', ET_DOMAIN )?></h3>
				<ul class="list-jobs pending-jobs job-account-list">
					<?php
					global $post;

					$pending_jobs	= array();

					while( $pending_job->have_posts() ) { $pending_job->the_post ();

						$job		= et_create_jobs_response($post);
						$pending_jobs[]	= $job;

						$job_cat 	= isset($job['categories'][0]) ? $job['categories'][0] : '';
						$job_type 	= isset($job['job_types'][0]) ? $job['job_types'][0] : '';
						$paid 		= ( $job['job_paid'] ) ? __('PAID', ET_DOMAIN) : __('UNPAID', ET_DOMAIN);
						if($job['job_paid'] == 2) $paid	= __('FREE', ET_DOMAIN);

						$company		= et_create_companies_response( $job['author_id'] );
						$company_logo	= $company['user_logo'];
						//$colors  	= 	et_get_job_type_colors();

						// add this company data to the array to pass to js
						if(!isset($arrAuthors[$company['id']])){
							$arrAuthors[$company['id']]	= array(
								'display_name'	=> $company['display_name'],
								'user_url'		=> $company['user_url'],
								'user_logo'		=> $company_logo
							);
						}
						?>
						<li class="job-item">
							<div class="thumb">
							<?php
								if (!empty($company_logo)){
									?>
									<a data="<?php echo $company['ID'];?>" href="<?php echo $company['post_url'];?>"
										title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" id="job_author_name">
										<img src="<?php echo ( isset($company_logo['small_thumb']) && !empty($company_logo['small_thumb']) ) ? $company_logo['small_thumb'][0] : $company_logo['thumbnail'][0]; ?>" id="company_logo_thumb" data="<?php echo $company_logo['attach_id'];?>" />
									</a>
									<?php
								}
							?>
							</div>

							<div class="content">
								<a class="title-link title" href="<?php the_permalink () ?>">
									<?php the_title () ?>
								</a>
								<div class="desc f-left-all">
									<div class="cat company_name a">
										<a data="<?php echo $company['ID'];?>" href="<?php echo $company['post_url'];?>" title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>">
											<?php echo $company['display_name'] ?>
										</a>
									</div>
									<?php if($job_type != '') { ?>
									<div class="job-type <?php echo 'color-' . $job_type['color'] ?>">
										<span class="flag"></span>
										<a href="<?php echo $job_type['url']; ?>" title="<?php printf(__('View all posted jobs in %s', ET_DOMAIN), $job_type['name']);?>">
											<?php echo $job_type['name'] ?>
										</a>
									</div>
									<?php } ?>
									<?php if ($job['location'] != '' ) { ?>
									<div><span class="icon" data-icon="@"></span><span class="job-location"><?php echo $job['location'] ?></span> </div>
									<?php } ?>
								</div>
								<div class="tech font-quicksand f-right actions">
									<a data="<?php echo $post->ID ?>" class="flag <?php echo strtolower($paid) ?>" href="#">
										<?php echo strtoupper($paid) ?><span class="icon" data-icon="%"></span>
										<a data="<?php echo $post->ID ?>" title="<?php _e('Edit', ET_DOMAIN) ?>" class="action-edit tooltip" href="#"><span class="icon" data-icon="p"></span></a></a>
									<a data="<?php echo $post->ID ?>" title="<?php _e('Approve', ET_DOMAIN) ?>" class="color-active action-approve tooltip" href="#"><span class="icon" data-icon="3"></span></a>
									<a data="<?php echo $post->ID ?>" title="<?php _e('Reject', ET_DOMAIN) ?>" class="color-pending action-reject tooltip" href="#"><span class="icon" data-icon="*"></span></a>
								</div>
							</div>
						</li>
						<?php
					}
					?>
				</ul>
			</div>

			<?php if(!empty($pending_jobs)){ ?>
				<script	type="application/json" id="pending_jobs_data">
					<?php echo json_encode($pending_jobs); ?>
				</script>
			<?php } ?>

		<!-- end pending jobs -->

			<?php
		}
		global $wp_query;
		wp_reset_query();

		// initial status
		$list_status	= get_query_var('status');
		$list_status	= (empty($list_status)) ? 'publish' : $list_status;
		if ('publish' == $list_status ){
			$list_title		= __('LATEST JOBS',ET_DOMAIN);
			$list_status	= 'publish';
		}
		else{
			$list_title		= et_get_job_status_labels( explode(',', $list_status) );
			$list_status	= 'other';
		}
		?>

		<!-- latest job -->
		<div id="latest_jobs_container">
			<h3 class="main-title"><?php echo $list_title; ?></h3>
			<ul class="list-jobs lastest-jobs job-account-list">
			<?php
				global $wp_query;
				$latest_jobs	= array();
				while ( have_posts() ) { the_post ();
					global $job;
					$job			= et_create_jobs_response($post);
					$latest_jobs[]	= $job;

					$template_job	= apply_filters( 'et_template_job', '');
					if( $template_job != '' )
						load_template( $template_job , false);
					else {
						get_template_part( 'template' , 'job' );
					}
				}
				// if no jobs found, display a message
				// do_action( 'je_after_job_list' , $wp_query );
				if ( !have_posts() ){
				?>
					<li class="no-job-found hide-nojob"><?php _e('Oops! Sorry, no jobs found', ET_DOMAIN) ?></li>
				<?php
				}
			?>
			</ul>

			<div id ="button-more" class="button-more <?php if ( $wp_query->max_num_pages <= 1 ) { echo 'out'; } ?>" <?php if ( $wp_query->max_num_pages <= 1 ) { echo 'style="display:none"'; }?>>
			  	<button class="btn-background border-radius"><?php _e('Load More Jobs', ET_DOMAIN) ?></button>
			</div>

			<script	type="application/json" id="latest_jobs_data">
				<?php echo json_encode(array(
						'status'	=> $list_status,
						'jobs'		=> $latest_jobs
					)); ?>
			</script>
		</div>
		<!-- end latest jobs -->

		<!-- this script passes the companies data for js usage -->
		<script type="application/json" id="companies_data">
			<?php echo json_encode($arrAuthors);?>
		</script>

	  	</div>

	  	<?php get_sidebar () ?>

	</div>

	<?php if(is_active_sidebar('sidebar-home-bottom')) {  ?>
	<div class="main-center clearfix padding-top30">
		<div class="sidebar-home-bottom <?php if(current_user_can('manage_options') ) echo 'sortable' ?>" id="sidebar-home-bottom" >
			<?php dynamic_sidebar('sidebar-home-bottom'); ?>
		</div>
	</div>
	<?php } ?>
</div>
<?php	get_footer(); ?>