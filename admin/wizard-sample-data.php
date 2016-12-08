<div class="et-main-main clearfix inner-content" id="wizard-sample-data" <?php if ($section != 'sample-data') echo 'style="display:none"' ?>>
	<div class="title font-quicksand"><?php _e('Install sample data', ET_DOMAIN );?></div>
	<div class="desc">
		<?php _e("Insert our sample data to see how your website works. We highly recommend using this function only when you have not posted your own data yet.",ET_DOMAIN);?>
		<div class="btn-language padding-top10 f-left-all">
		<?php  
			$sample_data_op = get_option('option_sample_data');
			if (!$sample_data_op) {
				echo '<button class="primary-button" id="install_sample_data">'.__("Install sample data", ET_DOMAIN).'</button>';
			}
			else{
				echo '<button class="primary-button" id="delete_sample_data">'.__("Delete sample data", ET_DOMAIN).'</button>';
			}
		?>
		</div>
	</div>
</div>
<?php  
	// $sample_data_op = get_option('option_sample_data');
	// if (!$sample_data_op) {
	// 	echo '<input type="button" id="sample_data" value="'.__('Insert Sample Data',ET_DOMAIN).'">';
	// }else{
	// 	echo '<input type="button" id="delete_sample_data" value="'.__('Delete Sample Data',ET_DOMAIN).'">';
	// }
?>