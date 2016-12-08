<div class="et-main-main" id="setting-payment" style="display: none">	
	<div class="inner">	
		<div class="title font-quicksand"><?php _e("Pay-to-View Resumes",ET_DOMAIN);?></div>
		<div class="desc">
		 	<?php _e("Enabling this will require employers to upgrade their account to view the resume detail page.",ET_DOMAIN);?>
			<div class="inner no-border btn-left">
				<div class="payment">					
					<div class="button-enable font-quicksand">
					<a href="#" data="et_free_view_resume" title="Resume Status" class="toggle-button deactive <?php if ($options['et_free_view_resume'] == 0) echo 'selected' ?>">
						<span><?php _e('Disable', ET_DOMAIN) ?></span>
					</a>
					<a href="#" data="et_free_view_resume" title="Resume Status" class="toggle-button active <?php if ($options['et_free_view_resume'] == 1) echo 'selected' ?>">
						<span><?php _e('Enable', ET_DOMAIN) ?></span>
					</a>					
					</div>
				</div>
			</div>	        				
		</div>	
		<?php require_once 'options-payment-plans.php'; ?>
	</div>
</div>
