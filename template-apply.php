<?php
global $job, $current_user, $user_ID;
// $job_data		=	et_create_jobs_response($post);
$apply_method	=	et_get_post_field( $job->ID, 'apply_method');

?>

<div class="form_container form_apply_job">	
	<!-- apply button -->
	<?php if( $job->post_status == 'publish' || $user_ID == $job->post_author ||
		current_user_can('edit_others_posts') ) {
	 ?>
	<div class="bg-job-frame job-apply submit-apply" style="float:none" id="job_action">
		<?php if (et_get_auto_email('remind')) { // remind job ?>
			<div class="f-right a-right">
				<span><?php _e("Don't have time now?",ET_DOMAIN);?></span>
				<br />
				<a href="#" class="reminder" title="<?php _e("Get a reminder in your inbox",ET_DOMAIN);?>"><?php _e("Get a reminder in your inbox",ET_DOMAIN);?></a>
			</div>
		<?php } ?>
			<div class="apply" id="how_to_apply">

				<?php if($apply_method != 'ishowtoapply') { // apply job by form ?> 
					<button title="<?php _e("Apply for this job",ET_DOMAIN); ?>" class="bg-btn-action border-radius btn-default btn-apply applyJob" id="apply2">
						<?php _e("APPLY FOR THIS JOB",ET_DOMAIN); ?>
						<span class="icon" data-icon="R"></span>
					</button> 

				<?php } else { // apply job by how to apply ?>
					<button title="<?php _e("APPLY FOR THIS JOB", ET_DOMAIN); ?>" class="bg-btn-action border-radius btn-default btn-apply applyJob" id="apply3">
						<?php _e("APPLY FOR THIS JOB",ET_DOMAIN); ?>
						<span class="icon" data-icon="O"></span>
					</button> 
				<?php } ?> 
			</div>  
	</div>
	<?php } ?>

	<!-- end apply button  -->

	

	<?php 

	// apply form
	// if resume feature is enabled
	$options = JE_Resume_Options::get_instance();
	if ($options->et_resumes_status) {
		// get apply template with resume in template/apply-resume.php
		get_template_part('template/apply' , 'resume');
	}  // end if
	// if resume feature is disabled
	else { 
		// get apply template with disable resume option in template/apply-noresume.php
		get_template_part('template/apply' , 'noresume');

	} // end else 



	if( $apply_method == 'ishowtoapply' || $user_ID == $job->post_author || current_user_can('manage_options'))  { ?>  
		<div class="bg-job-frame job-apply submit-apply job-howtoapply" id="job_howtoapply" style="display:none;float:none">
			<h5><?php _e("HOW TO APPLY FOR THIS JOB", ET_DOMAIN); ?></h5>
			<div class="description"><?php echo et_get_post_field($job->ID, 'applicant_detail'); ?></div>
			<a href="#" class="back-step icon" data-icon="D"></a>
		</div>
	<?php } ?>

	<!-- reminder job form -->
	<div class="bg-job-frame job-apply clearfix margin-top25" id="remind_form" style="display:none;float:none">

		<form class="form" id="reminderForm">                       
			<div class="form-item no-padding">
				<div class="width50 f-left">
					<div class="label">
						<h6><?php _e("Email Address",ET_DOMAIN);?></h6>
					</div>
					<div class="">
						<input name="share_email" id="share_mail" class="bg-default-input required email" type="email" />
					</div>
				</div>
			</div>
			<div class="form-item">
				<div class="label">
					<h6><?php _e("Notes",ET_DOMAIN);?></h6>
				</div>
				<div class="">
					<textarea name="share_note" id="share_note" class="bg-default-input mini required"></textarea>
				</div>
			</div>
			
			<div class="form-item">
				<button type="submit" id="remind" class="btn-background border-radius btn-default action share-job">
					<?php _e("Submit",ET_DOMAIN);?>
					<span class="icon" data-icon="p"></span>
				</button>
				<button type="button" class="btn-background border-radius btn-default cancel">
					<?php _e('Cancel',ET_DOMAIN);?>
					<span class="icon" data-icon="D"></span>
				</button>    
			</div>
			<input type="hidden" name="action" value="et_remind_job" />
			<input type="hidden" name="job_id" value="<?php echo $job->ID ?>"/>
		</form>
	</div>
	

	<?php  // is apply by profile success message
	if( $apply_method != 'ishowtoapply' || $user_ID == $job->post_author || current_user_can('manage_options'))  { ?> 
		<div class="bg-job-frame job-apply margin-top25" id="success-msg" style="display:none;float:none">
			<?php  _e('<span><strong>Congratulations!</strong></span><br /><span class="msg">Your application has been sent. Good luck!</span>', ET_DOMAIN); ?>
		</div>
	<?php } ?>
	
</div>