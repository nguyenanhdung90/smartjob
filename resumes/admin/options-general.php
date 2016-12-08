<div class="et-main-main" id="setting-general">
	<div class="title font-quicksand"><?php _e('Turn the feature on', ET_DOMAIN) ?></div>
	<div class="desc">
		<?php _e("You can turn RESUME section off when you don't need it", ET_DOMAIN) ?>
		<div class="inner no-border btn-left">		
			<div class="payment">
				<div class="button-enable font-quicksand">
					<a href="#" data="et_resumes_status" title="Resume Status" class="toggle-button deactive <?php if ($options['et_resumes_status'] == 0) echo 'selected' ?>">
						<span><?php _e('Disable', ET_DOMAIN) ?></span>
					</a>
					<a href="#" data="et_resumes_status" title="Resume Status" class="toggle-button active <?php if ($options['et_resumes_status'] == 1) echo 'selected' ?>">
						<span><?php _e('Enable', ET_DOMAIN) ?></span>
					</a>
				</div>
			</div>
		</div>
	</div>

	<!-- resume headline  -->
	<div class="title font-quicksand"><?php _e('Resumes list headline', ET_DOMAIN) ?></div>
	<div class="desc">
		<?php _e("Enter your resumes list's headline", ET_DOMAIN) ?>
		<div class="editor form no-margin no-padding no-background">
			<div class="form-item">
				<?php wp_editor( $options['et_jobseeker_headline'], 'et_jobseeker_headline', je_editor_settings () ); ?>
			</div>
		</div>
	</div>

	<!-- Linked api key setting -->
	<div class="title font-quicksand"><?php _e('LinkedIn API Key', ET_DOMAIN) ?></div>
	<div class="desc">		
		<?php _e("Enter your LinkedIn API Key to allow jobseekers to import data from their LinkedIn profile", ET_DOMAIN) ?>
		<div class="form no-margin no-padding no-background">
			<div class="form-item">				
				<input id="jobseeker_api_linked" type="text" class="option-item bg-grey-input" name="et_jobseeker_api_linked" value ="<?php echo $options['et_jobseeker_api_linked'] ?>" />				
			</div>
		</div>
	</div>

	<div class="title font-quicksand"><?php _e('Login-to-View Resumes', ET_DOMAIN) ?></div>
	<div class="desc">
		<?php _e("Turn on to make resumes viewable to logged in users only", ET_DOMAIN) ?>
		<div class="inner no-border btn-left">		
			<div class="payment">
				<div class="button-enable font-quicksand">
					<a href="#" data="et_resumes_priavcy" title="Resume Privacy" class="toggle-button deactive <?php if ($options['et_resumes_priavcy'] == 0) echo 'selected' ?>">
						<span><?php _e('Disable', ET_DOMAIN) ?></span>
					</a>
					<a href="#" data="et_resumes_priavcy" title="Resume Privacy" class="toggle-button active <?php if ($options['et_resumes_priavcy'] == 1) echo 'selected' ?>">
						<span><?php _e('Enable', ET_DOMAIN) ?></span>
					</a>
				</div>
			</div>
		</div>
	</div>

</div>