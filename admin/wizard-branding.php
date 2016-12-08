<div class="et-main-main clearfix inner-content" id="wizard-branding" <?php if ($section != 'branding') echo 'style="display:none"' ?>>
<?php 
	$general_opt=	new ET_GeneralOptions();
	$site_title	=	$general_opt->get_site_title();
	$site_desc	=	$general_opt->get_site_desc();
	$copyright	=	$general_opt->get_copyright();
	$twitter	=	$general_opt->get_twitter_account();
	$site_demon	=	$general_opt->get_site_demonstration ();
	$facebook	=	$general_opt->get_facebook_link();
	$google		=	$general_opt->get_google_plus();
	$validator	=	new ET_Validator();
?>
	<div class="title font-quicksand"><?php _e('Upload Logo', ET_DOMAIN );?></div>
	<div class="desc">
		<?php _e('Your logo should be in PNG, GIF or JPG format, within <strong>150x50px</strong> and less than <strong>200Kb</strong>', ET_DOMAIN);?>
		<div class="customization-info">
			<?php $uploaderID = 'website_logo';?>
			<div class="input-file upload-logo" id="<?php echo $uploaderID;?>_container">
				<?php 
				$website_logo = $general_opt->get_website_logo();
				
					?>
					<div class="left clearfix">						
						<div class="image" id="<?php echo $uploaderID;?>_thumbnail">
							<?php if ($website_logo){ ?>
								<img src="<?php echo $website_logo[0];?>"/>
							<?php } ?>
						</div>
					</div>
				<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( $uploaderID . '_et_uploader' ); ?>"></span>
				<span class="bg-grey-button button btn-button" id="<?php echo $uploaderID;?>_browse_button">
					<?php _e('Browse', ET_DOMAIN);?>
					<span class="icon" data-icon="o"></span>
				</span>
			</div>
		</div>
		<div style="clear:left"></div>
	</div>

	<div class="title font-quicksand margin-top30"><?php _e('Upload Mobile Icon', ET_DOMAIN);?></div>
	<div class="desc">
		<?php // _e('The icon must be <strong>45 x 45</strong> px and will be used for users who save your website on their smartphones screens. <a class="find-out font-quicksand" href="#">Find out more <span class="icon" data-icon="i"></span></a>', ET_DOMAIN);?>
		<?php _e('This icon will be used as a launcher icon for iPhone and Android smartphones and also as the website favicon. The image dimensions should be <strong>57x57px</strong>.', ET_DOMAIN);?>
		<div class="customization-info">
			<?php $uploaderID = 'mobile_icon';?>
			<div class="input-file  mobile-logo" id="<?php echo $uploaderID;?>_container">
				<?php 
				$mobile_icon = $general_opt->get_mobile_icon();
					?>
					<div class="left clearfix">
						<div class="image" id="<?php echo $uploaderID;?>_thumbnail">
							<?php if ($mobile_icon){ ?>
								<img src="<?php echo $mobile_icon[0];?>"/>
							<?php } ?>
						</div>
					</div>
				<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( $uploaderID . '_et_uploader' ); ?>"></span>
				<span class="bg-grey-button button btn-button" id="<?php echo $uploaderID;?>_browse_button">
					<?php _e('Browse', ET_DOMAIN);?>
					<span class="icon" data-icon="o"></span>
				</span>
			</div>
		</div>
		<div style="clear:left"></div>
	</div>

	<div class="title font-quicksand"><?php _e("Website title",ET_DOMAIN);?></div>
	<div class="desc">
		<?php _e("Enter your website title ",ET_DOMAIN);?>
		<div class="form no-margin no-padding no-background">
			<div class="form-item">
				<input class="bg-grey-input <?php if($site_title == '') echo 'color-error' ?>" type="text" value="<?php echo $site_title?>" id="site_title" name="site_title" />
				<span class="icon  <?php if($site_title == '') echo 'color-error' ?>" data-icon="<?php data_icon($site_title) ?>"></span>
			</div>
		</div>
	</div>
	<div class="title font-quicksand"><?php _e("Website description",ET_DOMAIN);?></div>
	<div class="desc">
		<?php _e("This description will appear next to your website logo in the header.",ET_DOMAIN);?>
		<div class="form no-margin no-padding no-background">
			<div class="form-item">
				<input class="bg-grey-input <?php if($site_desc == '') echo 'color-error' ?>" type="text" value="<?php echo $site_desc?>" id="site_desc" name="site_desc" />
				<span class="icon  <?php if($site_desc == '') echo 'color-error' ?>" data-icon="<?php data_icon($site_desc) ?>"></span>
			</div>
		</div>
	</div>
	<?php et_wizard_nexstep_button (1); ?>
</div>
