<div class="et-main-main clearfix inner-content" id="setting-update" <?php if ($sub_section != 'update') echo 'style="display:none"' ?>>
<?php 
?>
	<div class="desc">
		<?php _e('Enter your license key on Enginethemes.com', ET_DOMAIN) ?>:
		<div class="form no-margin no-padding no-background">
			<div class="form-item license-field">
				<input class="bg-grey-input" type="text" placeholder="<?php _e('Enter license key', ET_DOMAIN) ?>" value="<?php echo get_option('et_license_key', '') ?>" id="license_key" name="license_key">
			</div>
		</div>
	</div>
</div>

