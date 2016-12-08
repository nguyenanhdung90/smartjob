<div class="et-main-main clearfix  inner-content" id="setting-job"  <?php if ($sub_section != 'job') echo 'style="display:none"' ?>>
	<?php require_once 'content-jobs.php';?>
	<div class="title font-quicksand"><?php _e("Pending jobs",ET_DOMAIN);?></div>
	<div class="desc">
	 	<?php _e("Enabling this will make every new job post pending until you review and approve it manually.",ET_DOMAIN);?>			
		<?php /*<!-- <a class="find-out font-quicksand" href="#">
	 		<?php _e("Find out more",ET_DOMAIN);?> <span class="icon" data-icon="i"></span>
	 	</a> --> */ ?>
		<div class="inner no-border btn-left">
			<div class="payment">
				<div class="button-enable font-quicksand">
					<?php et_display_enable_disable_button('pending_job', __("Pending job",ET_DOMAIN), 'pending_job'); ?>
				</div>
			</div>
		</div>	        				
	</div>	

	<!--
	/**
	*pendding job when employer edit job
	*@ 2.9.2
	**/ 
	!-->
	<div class="title font-quicksand"><?php _e("Pending jobs after being editted",ET_DOMAIN);?></div>
	<div class="desc">
	 	<?php _e("If you enable this option, the jobs will be pended after employer edit them.",ET_DOMAIN);?>			
		<?php /*<!-- <a class="find-out font-quicksand" href="#">
	 		<?php _e("Find out more",ET_DOMAIN);?> <span class="icon" data-icon="i"></span>
	 	</a> --> */ ?>
		<div class="inner no-border btn-left">
			<div class="payment">
				<div class="button-enable font-quicksand">
					<?php et_display_enable_disable_button('pending_job_edit', __("Pending job",ET_DOMAIN), 'pending_job_edit'); ?>
				</div>
			</div>
		</div>	        				
	</div>	


	<div class="title font-quicksand"><?php _e("New Post Alert",ET_DOMAIN);?></div>
	<div class="desc">
	 	<?php _e("Enter the email address where you want to get notified when there is a new job post.",ET_DOMAIN);?>			
		<?php /*<!-- <a class="find-out font-quicksand" href="#">
	 		<?php _e("Find out more",ET_DOMAIN);?> <span class="icon" data-icon="i"></span>
	 	</a> --> */ ?>
		<div class="form no-margin no-padding no-background">
			<div class="form-item">
				<div class="form-item notification-field">
					<input class="bg-grey-input" type="text" name="job_notification_email" id="job_notification_email" value="<?php echo get_option('et_job_notification_mail' , ''); ?>" />
				</div>
			</div>
		</div>	        				
	</div>
	
</div>