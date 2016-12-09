<?php
/*
* Step Job Info
 */
global $current_user,$job, $steps, $apply_method, $applicant_detail, $full_location,$location, $location_lat,$location_lng, $apply_email,$term_of_use;

?>
<div class="step <?php if(!!$job) echo 'completed';?>" id='step_job'>
	<div class="toggle-title f-left-all <?php if(!!$job) echo 'toggle-complete';?>">
		<div class="icon-border"><?php echo array_shift($steps) ?></div>
		<span class="icon" data-icon="2"></span>
		<span><?php _e('Describe your job and your company', ET_DOMAIN );?></span>
	</div>
	<div class="toggle-content login clearfix" style="display: none">
		<div class="form">
			<form id="job_form" method="post" enctype="multipart/form-data" novalidate="novalidate" autocomplete="on">
				<div id="job_info">
					<div class="form-item">
						<div class="label">
							<h6 class="font-quicksand"><?php _e('JOB TITLE', ET_DOMAIN );?></h6>
							<?php _e('Enter a short title for your job', ET_DOMAIN );?>
						</div>
						<div>
							<input class="bg-default-input" tabindex="1" name="title" id="title" type="text" value="<?php if(isset($job['title'])) echo esc_attr($job['title']);?>" />
						</div>
					</div>
					<div class="form-item">
						<div class="label">
							<h6 class="font-quicksand"><?php _e('JOB DESCRIPTION', ET_DOMAIN );?></h6>
							<?php _e('Describe your job in a few paragraphs ', ET_DOMAIN );?>
						</div>
						<div class="job_description">
							<?php
								if(isset($job['content'])) $content	=	$job['content']; else $content	= '' ;
									wp_editor( $content ,'content' , je_job_editor_settings () );
							// ?>
							<!-- <textarea class="bg-default-input tinymce" tabindex="2" name="content" id="content"><?php if(isset($job['content'])) echo $job['content']; else echo ' '; ?></textarea> -->
						</div>
					</div>
					<div class="form-item">
						<div class="label">
							<h6 class="font-quicksand"><?php _e('JOB LOCATION', ET_DOMAIN );?></h6>
							<?php _e('Enter a city and country or leave it blank', ET_DOMAIN );?>
						</div>
						<div>
							<div class="address">
								<?php
									if(isset($job['full_location'])) $full_location 	=	 $job['full_location'];
									if(isset($job['location'])) 	$location 			=	 $job['location'];
									if(isset($job['location_lat'])) $location_lat		=	 $job['location_lat'];
									if(isset($job['location_lng'])) $location_lng 		=	 $job['location_lng'];
								?>
								<input class="bg-default-input" name="full_location" tabindex="3" id="full_location" type="text" value="<?php  echo esc_attr($full_location); ?>"/>
								<input type="hidden" name="location" id="location" value="<?php echo esc_attr($location); ?>" />
								<input type="hidden" name="location_lat" id="location_lat" value="<?php echo esc_attr($location_lat);?>" />
								<input type="hidden" name="location_lng" id="location_lng" value="<?php  echo esc_attr($location_lng); ?>" />
								<div class="address-note">
								<?php _e('Examples: "Melbourne VIC", "Seattle", "Anywhere"', ET_DOMAIN) ?>
								</div>
							</div>
							<div class="maps">
								<div class="map-inner" id="map"></div>
							</div>
						</div>
					</div>
					<!-- How to apply -->
					<div class="form-item">
						<div class="label">
							<h6 class="font-quicksand"><?php _e('HOW TO APPLY', ET_DOMAIN );?></h6>
							<?php _e('Select how you would want jobseekers to submit their applications', ET_DOMAIN );?>
						</div>
						<div class="apply">
							<input type="hidden" id="apply_method" value="">
							<input type="radio" name="apply_method" id="isapplywithprofile" value="isapplywithprofile" <?php if($apply_method != 'ishowtoapply') echo 'checked' ?> />
							<label class="font-quicksand" for="isapplywithprofile">
								<?php _e("Allow job seekers to submit their cover letter and resume directly", ET_DOMAIN);?>
							</label>
							<div class="email_apply">
								<span class=""><?php _e("Send applications to this email address:", ET_DOMAIN); ?></span>&nbsp;
								<input class="bg-default-input application-email" type="text" name="apply_email" id="apply_email" value="<?php echo esc_attr($apply_email); ?>"/> <br />
								<span class="example"><?php _e("e.g. 'application@demo.com'", ET_DOMAIN); ?></span>
							</div>

							<input type="radio" name="apply_method" id="ishowtoapply" value="ishowtoapply" <?php if( $apply_method == 'ishowtoapply') echo 'checked' ?> />
							<label class="font-quicksand" for="ishowtoapply" ><?php _e("Job seekers must follow the application steps below", ET_DOMAIN);?></label>
							<div class="applicant_detail">
								<?php  wp_editor( $applicant_detail, 'applicant_detail', je_job_editor_settings () ) ?>
							</div>

						</div>
					</div>
					<!-- END How to apply -->
					<div class="form-item">
						<div class="label">
							<h6 class="font-quicksand"><?php _e('CONTRACT TYPE', ET_DOMAIN );?></h6>
							<?php _e('Select the correct type for your job', ET_DOMAIN );?>
						</div>
						<div class="select-style btn-background border-radius">
							<?php et_job_type_select('job_types'); ?>
						</div>
					</div>
					<div class="form-item">
						<div class="label">
							<h6 class="font-quicksand"><?php _e('JOB CATEGORY', ET_DOMAIN );?></h6>
							<?php _e('Select a category for your job', ET_DOMAIN );?>
						</div>
						<div class="select-style btn-background border-radius">
							<?php et_job_cat_select ('categories') ?>
						</div>
					</div>

					<!-- CUSTOM FIELD -->
					<?php do_action('et_post_job_fields') ?>

				</div>

				<div id="company_info">
					<div class="form-item">
						<div class="label">
							<h6 class="font-quicksand"><?php _e('COMPANY NAME', ET_DOMAIN );?></h6>
							<?php _e('Enter your company name', ET_DOMAIN );?>
						</div>
						<div>
							<input class="bg-default-input" tabindex="6" name="display_name" id="display_name" type="text" value="<?php if ( is_user_logged_in() ) { echo esc_attr($current_user->display_name); }?>"/>
						</div>
					</div>
					<div class="form-item">
						<div class="label">
							<h6 class="font-quicksand"><?php _e('COMPANY WEBSITE', ET_DOMAIN );?></h6>
							<?php _e('Enter your company website', ET_DOMAIN );?>
						</div>
						<div>
							<input class="bg-default-input" tabindex="7" name="user_url" id="user_url" type="text" value="<?php if ( is_user_logged_in() ) { echo esc_url($current_user->user_url); }?>" />
						</div>
					</div>

					<?php do_action('je_post_job_author_info_meta'); ?>

					<?php $uploaderID = 'user_logo';?>
					<div class="form-item" id="<?php echo $uploaderID;?>_container">
						<div class="label">
							<h6 class="font-quicksand"><?php _e('COMPANY LOGO', ET_DOMAIN );?></h6>
							<?php _e('Upload your company logo', ET_DOMAIN );?>
						</div>
						<div>
							<span class="company-thumbs" id="<?php echo $uploaderID;?>_thumbnail">
							<?php
								if ( is_user_logged_in() ) {
									$user_logo	= et_get_company_logo( $current_user->ID );
									if (!empty($user_logo)){
										?>
										<img src="<?php echo esc_url($user_logo['company-logo'][0]); ?>" id="<?php echo $uploaderID;?>_thumb" data="<?php echo esc_attr($user_logo['attach_id']);?>" />
										<?php
									}
								}
							?>
							</span>
						</div>
						<div class="input-file clearfix et_uploader">
							<span class="btn-background border-radius button" id="<?php echo $uploaderID;?>_browse_button" tabindex="8" >
								<?php _e('Browse...', ET_DOMAIN );?>
								<span class="icon" data-icon="o"></span>
							</span>
							<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( $uploaderID . '_et_uploader' ); ?>"></span>
						    <div class="clearfix"></div>
						    <div class="filelist"></div>
						</div>
					</div>
				</div>

				<!-- render captcha !-->
				<?php do_action('je_post_job_after_author_info') ?>
				<?php if($term_of_use){?>
				<div class="form-item clearfix form-item-type">
					<div class="label">&nbsp;</div>
					<div>
						<input name="register_term" class="required not_empty" id="term_of_post" type="checkbox" />
						<label for="term_of_post"><?php printf(__("I agree with <a href='%s' target='_blank' > the Terms of use </a>", ET_DOMAIN), et_get_page_link('terms-of-use') ); ?> </label>

					</div>
				</div>
				<?php }?>
				<div class="form-item clearfix">
					<div class="label">&nbsp;</div>
					<div class="btn-select">
						<button class="bg-btn-action border-radius" tabindex="9" type="submit" id="submit_job"><?php _e('CONTINUE', ET_DOMAIN );?></button>
					</div>

					<div class="btn-cancel">
						<a href="<?php echo et_get_page_link('post-a-job') ?>" class="btn-background border-radius button" id="indeed_search">
							<?php _e("CANCEL", ET_DOMAIN); ?>
						</a>
					</div>

				</div>
			</form>
		</div>
	</div>
</div>