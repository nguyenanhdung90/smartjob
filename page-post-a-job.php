<?php 
/**
 * Template Name: Post a Job
 */

global $current_user, $wp_rewrite;
$general_opt	= new ET_GeneralOptions();

$job	= get_query_var('job_id');

if (!!$job){
	$job	= get_post($job);
	if ( !isset($job->ID) || !isset($current_user->ID) || $job->post_author != $current_user->ID ){

		// not the job author, redirect to this page without var
		wp_redirect( et_get_page_link('post-a-job') );
		exit;
	}

	$job	= et_create_jobs_response($job);
}

$job_opt 		= new ET_JobOptions () ;
$contact_widget	= $job_opt->get_post_job_sidebar ();
$term_of_use	= et_get_page_link('terms-of-use' , array () , false);
if( isset($current_user->ID)) {
	$recent_location  	=	 et_get_user_field ($current_user->ID,'recent_job_location');
	$full_location 		=	isset($recent_location['full_location']) ? $recent_location['full_location'] : '' ;
	$location 			=	isset($recent_location['location']) ? $recent_location['location'] : '';
	$location_lat		=	isset($recent_location['location_lat']) ? $recent_location['location_lat'] : '';
	$location_lng 		=	isset($recent_location['location_lng']) ? $recent_location['location_lng'] : '';
	$company			=	et_create_companies_response($current_user->ID);
	$apply_method		=	$company['apply_method'];
	$apply_email		=	$company['apply_email'];
	$applicant_detail	=	$company['applicant_detail'];
} else {
	$apply_method		=	'isapplywithprofile';
	$apply_email		=	'';
	$applicant_detail	=	'';
	$full_location 		=	 '';
	$location 			=	 '';
	$location_lat		=	 '';
	$location_lng 		=	 '';
}
remove_action( 'je_after_register_form' , 'render_captcha' );
get_header(); ?>

<div class="wrapper">
	<div class="heading">
		<div class="main-center">
			<h1 class="title"><span class="icon" data-icon="W"></span>
				<?php
					if(!$job) {
						_e('Post a Job', ET_DOMAIN);
					}
					else {
						_e('Renew this Job', ET_DOMAIN);
					}
				?>
			</h1>
		</div>
	</div>

	<div class="main-center margin-top25 clearfix">
	<?php
		$main_colum	=	'full-column';
		if( !empty($contact_widget) || current_user_can('manage_options') ) {
			$main_colum	=	'main-column';
		}
	?>
		<div class="<?php echo $main_colum ?>" id="post_job">

			<?php if(!!$job){ // add the existed job data here for js ?>
				<script type="application/json" id="job_data">
					<?php echo json_encode($job);?>
				</script>
			<?php }?>

			<div class="post-a-job">

				<?php
					$steps = array('1','2','3','4');
					get_template_part('template/step','1');

					if ( !et_is_logged_in() )
						get_template_part('template/step','2');

					get_template_part('template/step','3');
					get_template_part('template/step','4');
				?>

			</div>

		</div>
		<?php if( !empty($contact_widget) || current_user_can('manage_options') ) {	?>
			<div class="second-column widget-area" id="static-text-sidebar">
				<div id="sidebar" class="post-job-sidebar">

					<?php
					global $user_ID;
					je_user_package_data ($user_ID , 'job');

					foreach ($contact_widget as $key => $value) { ?>
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
	</div>
</div>

<?php get_footer(); ?>
