<?php 
/**
 * Template Name: User Dashboard
 */
if(isset($_GET['applicant_id']) && $_GET['applicant_id'] != '') {
	$attachment	=	get_children( array(
							'post_type' => 'attachment', 
							'post_parent' => $_GET['applicant_id'] ,
							'posts_per_page' => -1
						));

	$zipname = get_template_directory().'/file.zip';
	$zip = new ZipArchive();
	$zip->open($zipname, ZipArchive::CREATE);	

	foreach ($attachment as $key => $att) {	
		$file	=	get_attached_file($att->ID);
		$arr	=	explode('/', $file);
		$name	=	array_pop($arr);
		$zip->addFile($file , $name);  
	}

	$zip->close();


	header("Cache-Control: public");
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");

	// header('Content-Type: application/zip');
	header('Content-Disposition: attachment; filename=filename.zip');
	header('Content-Length: ' . filesize($zipname));
	
	ob_start();
    readfile($zipname);
    ob_end_flush();
	unlink($zipname);

}


global $current_user, $user_ID;

$job_opt 		= new ET_JobOptions () ;
$widgets	= $job_opt->get_dashboard_sidebar ();

get_header(); ?>

<div class="heading">
	<div class="main-center">
		<!-- <div class="technical logout f-right">
			<a href="<?php echo wp_logout_url( home_url() ) ?>"><?php _e('LOGOUT', ET_DOMAIN);?> <span class="icon" data-icon="Q"></span></a>
		</div> -->
		<h1 class="title"><?php _e("ACCOUNT",ET_DOMAIN);?></h1>		
	</div>
</div>

<div class="wrapper account-jobs account-step">
	<?php if($current_user->role) ?>
	<div class="account-title">
		<div class="main-center clearfix">
			<ul class="account-menu font-quicksand">
				<?php do_action( 'je_before_company_info_tab') ?>
				<li><a href="<?php echo et_get_page_link('dashboard'); ?>" class="active"><?php _e('YOUR JOBS', ET_DOMAIN); ?></a></li>
				<li><a href="<?php echo et_get_page_link('profile'); ?>"><?php _e('COMPANY PROFILE', ET_DOMAIN); ?></a></li>
				<li><a href="<?php echo et_get_page_link('password'); ?>"><?php _e('PASSWORD', ET_DOMAIN); ?></a></li>
				<?php do_action( 'je_after_company_info_tab') ?>
			</ul>        
		</div>
	</div>
	
	<div class="main-center">
	<?php 
	$main_colum	=	'full-column';

	/**
	 * Display payment plans that company have left
	 */
	$purchase_plans = et_get_purchased_quantity($current_user->ID);

	$plans 			= et_get_payment_plans();

	$purchase_count = 0;
	foreach ($purchase_plans as $id => $quantity) {
		if(isset($plans[$id]))
			$purchase_count += $quantity;
	}
	$resume_view_duration	=	0;	
	if(function_exists('je_get_resume_view_duration')) {
		$resume_view_duration	=	je_get_resume_view_duration ($user_ID);
		if($resume_view_duration > time () )
			$resume_view_duration	=	$resume_view_duration - time();
	}

	if( !empty($widgets) || current_user_can('manage_options') || $purchase_count ) {
		$main_colum	=	'main-column';
	}
	?>
	
	<?php 
		$statuses = array(
			'archive' => 
				array( 
					'title' => __('ARCHIVED', ET_DOMAIN),
					'class' => 'expired'
				),
			'draft' => 
				array(
					'title' => __('DRAFT', ET_DOMAIN),
					'class' => 'pending'
				),
			'pending' => 
				array(
					'title' => __('PENDING', ET_DOMAIN),
					'class' => 'pending'
				),
			'publish' => 
				array(
					'title' => __('ACTIVE', ET_DOMAIN),
					'class' => 'active'
				),
			'reject' => 
				array( 
					'title' => __('REJECTED', ET_DOMAIN),
					'class' => 'pending'
				),
			);
		$queries = array('reject', 'pending',  'publish', 'draft', 'archive');
		?>
		<div class="<?php echo $main_colum ?> account-content">
			<ul class="job-account-list  account-job-applicant clearfix">
			<?php 
			global $current_user;
			$arrJobs = array();

			foreach ($queries as $status) :
				$query = new WP_Query( array(
					'author' 		=> $current_user->ID,
					'post_type' 		=> 'job',
					'post_status' 		=> $status,
					'posts_per_page' 	=> -1,
					//'suppress_filters'	=> 'false'
				));
				if ( $query->have_posts() ) : ?>
					<?php

						while ( $query->have_posts() ) : $query->the_post();
							global $post;
							$temp	=	$post;
							//$job		=	get_post( icl_object_id ($post->ID, 'job' , false, ICL_LANGUAGE_CODE )) ;
							$job 		= et_create_jobs_response($temp);
							$arrJobs[] 	= $job;
							$i	=	0;
						
						$application	=	get_children(array('post_parent' => $post->ID, 'post_type' => 'application'));
					?>
						<li class="acc-job-item job-item-<?php echo $status ?>">
							<div class="control f-right" data="<?php echo $post->ID ?>">
								<!-- pending job -->
								<?php if ( $temp->post_status != 'archive' && $temp->post_status != 'draft' ) { ?>

									<?php if($job['job_paid']) { //paid or free ?> 
										<a href="#" class="control-action action-edit tooltip" title="<?php _e('Edit',ET_DOMAIN);?>"><span class="icon" data-icon="p"></span></a>
									<?php } else { $i = 1; // unpaid ?>
										<a href="<?php echo et_get_page_link('post-a-job', array('job_id' => $temp->ID )) ?>" class="control-action action-repost tooltip" title="<?php _e('Choose another payment method',ET_DOMAIN);?>"><span class="icon" data-icon="1"></span></a>
									<?php } ?>
										<a href="#" class="control-action action-postview tooltip" title="<?php echo et_post_views($temp->ID) ?>"><span class="icon" data-icon="E"></span></a>
									<?php if ( !in_array( $temp->post_status, array('pending','reject') ) ) { // if the job is pending, prevent the user to archive it ?>
										<a href="#" class="control-action action-archive tooltip" title="<?php _e('Archive',ET_DOMAIN);?>"><span class="icon" data-icon="#"></span></a>
									<?php } ?>

								<!-- pending job -->
								
								<?php } else if ( $temp->post_status == 'draft' ){ ?>
								<!-- draft job -->
									<a href="<?php echo et_get_page_link('post-a-job', array('job_id' => $temp->ID )) ?>" class="control-action action-repost tooltip" title="<?php _e('Edit',ET_DOMAIN);?>">
										<span class="icon" data-icon="p"></span>
									</a>
									<a href="#" class="control-action action-remove tooltip color-pending" title="<?php _e('Permanently delete',ET_DOMAIN);?>">
										<span class="icon" data-icon="*"></span>
									</a>
									<!-- draft job -->
									<!-- archived job -->
								<?php } else { ?>
									<a href="<?php echo et_get_page_link('post-a-job', array('job_id' => $temp->ID )) ?>" class="control-action action-repost tooltip" title="<?php _e('Renew',ET_DOMAIN);?>">
										<span class="icon" data-icon="1"></span>
									</a>
									<a href="#" class="control-action action-remove tooltip color-pending" title="<?php _e('Permanently delete',ET_DOMAIN);?>">
										<span class="icon" data-icon="*"></span>
									</a>
								<?php } ?>
									<!-- archived job -->
							</div>
							<div class="job-status apps f-right color-<?php echo $statuses[$temp->post_status]['class'] ?>" data="<?php echo $temp->post_status ?>">
								<?php if($i == 0) { 
									echo $statuses[$temp->post_status]['title'];
								} else { 
									_e('UNPAID', ET_DOMAIN) ;
									$i =	0;
								} ?> <span>&bull;</span> 
							</div>
							<div class="title"> 
								<a href="<?php echo get_permalink($temp->ID); ?>" title="<?php echo get_the_title($temp->ID) ?>"><?php echo get_the_title($temp->ID) ?></a> <span class="date"><?php echo get_the_date() ?></span>
								<?php 
									$num_of_applier	=	count($application);

									if($num_of_applier == 1 ) {
									?>
										<span class="applier-more"><?php printf(__('( %s applicant )', ET_DOMAIN), count($application)) ?></span>
									<?php

									} else {
										if($num_of_applier > 1 ) {
										?>
											<span class="applier-more"><?php printf(__('( %s applicants )', ET_DOMAIN), count($application)) ?></span>
										<?php 
										}
									}
								?>

								
							</div>
							<?php if(!empty($application)) { ?>
								<ul class="list-applier-more">
								<?php foreach ($application as $key => $applicant) { 
									$emp_email	=	get_post_meta($applicant->ID,'et_emp_email', true);
									$jobseeker	=	get_user_by('email', $emp_email );
									if($jobseeker) {
										$post	=	get_posts(array('author' => $jobseeker->ID, 'post_type' => 'resume', 'post_status' => 'publish'));
										if(!$post) continue;
								?>	<li>
										<a href="<?php echo get_permalink($post[0]->ID) ?>"><span class="text"><?php echo $jobseeker->display_name ?></span> 
										<?php echo date('jS-M',  strtotime($applicant->post_date)) ?> / <?php echo $jobseeker->user_email ?></a>											
									</li>

								<?php 
									}else {
										$attachment	=	get_children( array(
															'post_type' => 'attachment', 
															'post_parent' => $applicant->ID,
															'posts_per_page' => -1
														));
								?>
									<li><a href="#"><span class="text"><?php echo get_post_meta($applicant->ID, 'et_emp_name', true );  ?></span> 
											<?php echo date('jS-M',  strtotime($applicant->post_date)) ?> / <?php echo get_post_meta($applicant->ID, 'et_emp_email', true); ?></a>
										<?php if(!empty($attachment)) { ?>
										<div class="input-file clearfix et_uploader applier-file">
										<a title="<?php _e('Download', ET_DOMAIN) ?>" href="?applicant_id=<?php echo $applicant->ID; ?>">
											<span class="btn-background border-radius button" style="z-index: 0;">	
												<span  class="icon-file"></span>
											</span>
										</a>
										</div>		
										<?php } ?>
									</li>
									<?php } 
								} ?>			
								</ul>
							<?php } ?>
						</li>
					<?php endwhile; ?>
			<?php endif;
			endforeach;
			?>
			</ul>
		</div>

		<?php 
		if( !empty($widgets) || current_user_can('manage_options') || $purchase_count ) {
		?>
			<div class="second-column widget-area padding-top30" id="static-text-sidebar">
				<div id="sidebar" class="user-dashboard-sidebar">
					<?php 
					
					je_user_package_data ();
					
					foreach ($widgets as $key => $value) { ?>
					<div class="widget widget-contact bg-grey-widget" id="<?php echo $key ?>">
						<div class="view">
							<?php echo $value ?>
						</div>
						<?php if(current_user_can('manage_options')) { ?>
						<div class="btn-widget edit-remove"> 
							<a href="#" class="bg-btn-action border-radius edit"><span class="icon" data-icon='p'></span></a> 
							<a href="#" class="bg-btn-action border-radius remove"><span class="icon" data-icon='#'></span></a> 
						</div>
						<?php } ?>
					</div>
					<?php } ?>
				</div>
				<?php if(current_user_can('manage_options')) { ?>
					<div class="widget widget-contact bg-grey-widget" id="widget-contact">
						<a href="#" class="add-more"><?php _e('Add a text widget +', ET_DOMAIN) ?> </a>
					</div>
				<?php } ?>
			</div>
		<?php } ?>

		<script type="application/json" id="job_list_data">
			<?php echo json_encode($arrJobs);?>
		</script>

		<script type="text/template" id="job_item_template">

			
			<# if ( id !== 0 ) { #>
				<div class="control f-right" data="{{id}}">
				<# if ( status !== "archive" ) { #>
					<a href="#" class="action-edit tooltip" title="<?php _e('Edit', ET_DOMAIN);?>"><span class="icon" data-icon="p"></span></a>
					<a href="#" class="control-action action-postview tooltip" title="<?php echo et_post_views($post->ID) ?>"><span class="icon" data-icon="E"></span></a>
					<# if ( status === 'publish' ) { #>
						<a href="#" class="action-archive tooltip" title="<?php _e('Archive', ET_DOMAIN);?>"><span class="icon" data-icon="#"></span></a>
					<# } #>

				<# } else { #>
					<a href="{{renew_url}}" class="action-repost tooltip" title="<?php _e('Renew', ET_DOMAIN);?>"><span class="icon" data-icon="1"></span></a>
					<a href="#" class="control-action action-remove tooltip color-pending" title="<?php _e('Permanently delete',ET_DOMAIN);?>">
						<span class="icon" data-icon="*"></span>
					</a>
				<# } #>
				</div>
			<# } #>
			<div class="job-status apps f-right color-{{ status }}">{{dashboardStatus}} <span>&bull;</span> </div>
			<div class="title"><a href="{{permalink }}"> {{title}}</a> <span class="date">{{date }}</span></div>
		</script>
	</div>
</div>

<?php get_footer(); ?>