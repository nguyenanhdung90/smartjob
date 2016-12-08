<?php
global $job, $current_user, $user_ID;
?>
<div class="bg-job-frame job-apply clearfix margin-top25" id="apply_form" style="display:none">
	<form class="form" id="applicationForm">
		<div class="form-item no-padding">
			<div class="width50 f-left">
				<div class="label">
					<h6><?php _e("Full name",ET_DOMAIN);?></h6>
				</div>
				<div class="">
					<input name="apply_name" id="apply_name" type="text" class="bg-default-input" value="<?php if(isset($_COOKIE['seeker_name'])) echo $_COOKIE['seeker_name'] ; ?>"/>
				</div>
			</div>
			<div class="width50 f-right">
				<div class="label">
					<h6><?php _e("Email Address",ET_DOMAIN);?></h6>
				</div>
				<div class="">
					<input name="apply_email" id="apply_email" type="email" class="bg-default-input" value="<?php if(isset($_COOKIE['seeker_email'])) echo $_COOKIE['seeker_email'] ; ?>"/>
				</div>
			</div>
		</div>

		<?php $uploaderID = 'apply_docs';?>
		<div class="form-item" id="<?php echo $uploaderID;?>_container">
			<div class="label">
				<h6><?php _e("Attachments",ET_DOMAIN);?></h6>
				<ul class="list-file" id="<?php echo $uploaderID;?>_file_list">
				</ul>
			</div>
			<div class="input-file">
				<span class="btn-background border-radius button" id="<?php echo $uploaderID;?>_browse_button">
					<?php _e("Browse...",ET_DOMAIN);?>
					<span class="icon" data-icon="o"></span>
				</span>
				<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( $uploaderID . '_et_uploader' ); ?>"></span>
			</div>
		</div>

		<div class="form-item">
			<div class="label">
				<h6><?php _e("Notes",ET_DOMAIN);?></h6>
			</div>
			<div class="">
				<textarea name="apply_note" id="apply_note" class="bg-default-input mini required"></textarea>
			</div>
		</div>

		<?php do_action ('je_apply_job_form') ?>

		<div class="form-item">
			<button type="submit" id="apply" class="btn-background border-radius btn-default action">
				<?php _e("APPLY FOR THIS JOB",ET_DOMAIN);?>
				<span class="icon" data-icon="p"></span>
			</button>
			<button type="button" class="btn-background border-radius btn-default cancel">
				<?php _e('Cancel',ET_DOMAIN);?>
				<span class="icon" data-icon="D"></span>
			</button>
		</div>
		<input type="hidden" name="action" value="et_apply_job" />
		<input type="hidden" name="job_id" value="<?php echo $job->ID ?>"/>
	</form>
</div>