<div class="et-main-main" id="setting-content" style="display: none">
<?php 
	$available_tax	=	JE_TaxFactory::get_instance('available');
	$availables		=	$available_tax->get_terms_in_order(); 
	$position_tax	=	JE_TaxFactory::get_instance('resume_category');
	$resume_categories	=	$position_tax->get_terms_in_order();
?>
<div id="resume_content" >
	<div class="title font-quicksand">
	<?php
		$title_available  = $available_tax->get_title();		
	?>	<div class="title-main" title="<?php _e("Double click to edit", ET_DOMAIN); ?>">
		<?php echo ($title_available) ? $title_available : __("Oops, empty title! Double click to change", ET_DOMAIN); ?>
		</div>
		<input style="display:none;"  data-tax="available" type="text" value="<?php echo $title_available; ?>" placeholder="<?php _e("e.g. AVAILABLE or CONTRACT TYPE", ET_DOMAIN); ?>" />
		<span class="icon btn-edit" data-icon='p'></span>
	</div>

		

	<div class="desc">
		<?php _e("Create a list of employment contracts (e.g. Full-time, Freelance) that employers can use to filter resume posts.",ET_DOMAIN);?> 
		<div class="types-list-container" id="job-available">
			<!-- <ul class="list-job-input list-tax jobtype-sortable tax-sortable"> -->
			<?php 
			$available_tax->print_backend_terms();
			?>
			<?php $available_tax->print_confirm_list(); ?>
		</div>
	</div>

	<div class="title font-quicksand">		
	<?php 
		
		$title_position  = $position_tax->get_title();		
		
	?>
		<div class="title-main" title="<?php _e("Double click to edit", ET_DOMAIN); ?>">
	 		<?php echo ($title_position) ? $title_position : __("Oops, empty title! Double click to change", ET_DOMAIN);?>
	 	</div>

		<input style="display:none;"  data-tax="resume_category" type="text" value="<?php echo $title_position;?>" placeholder="<?php _e("e.g. Fields and industries", ET_DOMAIN); ?>"/>
		<span class="icon btn-edit" data-icon='p'></span>
	</div>
	<div class="desc">
		<?php _e("Create a list to categorize resumes the way you want, e.g. Fields and Industries, Expected Positions",ET_DOMAIN);?> 
		<?php /*
		<!-- <a class="find-out font-quicksand" href="#">
			<?php _e("Find out more",ET_DOMAIN);?> <span class="icon" data-icon="i"></span>
		</a> -->
		*/ ?>
		<div class="cat-list-container" id="job-position" >
			<!-- <ul class="list-job-input list-tax category list-job-categories cat-sortable tax-sortable" data-tax="resume_category"> -->
				<?php $position_tax->print_backend_terms(); ?>
			<!-- </ul>
			<ul class="list-job-input category add-category ">
				<li class="tax-item">
					<form class="new_tax" action="" data-tax='resume_category'>
						<div class="controls controls-2">
							<div class="button">
								<span class="icon" data-icon="+"></span>
							</div>
						</div>
						<div class="input-form input-form-2 color-default">
							<input class="bg-grey-input" placeholder="<?php _e('Add another category', ET_DOMAIN) ?>" type="text" />
						</div>
					</form>
				</li>
			</ul> -->
		</div>
		<?php $position_tax->print_confirm_list(); ?>
	</div>
	<div class="title font-quicksand"><?php _e('Pending resumes', ET_DOMAIN) ?></div>
	<div class="desc">
		<?php _e('Enabling this will make every new resume post pending until you review and approve it manually.',ET_DOMAIN);?>
		<div class="inner no-border btn-left">
			<div class="payment">	
				<div class="button-enable font-quicksand">
					<a href="#" data="et_pending_resume" title="Pending Resume Status" class="toggle-button deactive <?php if ($options['et_pending_resume'] == 0) echo 'selected' ?>">
						<span><?php _e('Disable', ET_DOMAIN) ?></span>
					</a>
					<a href="#" data="et_pending_resume" title="Pending Resume Status" class="toggle-button active <?php if ($options['et_pending_resume'] == 1) echo 'selected' ?>">
						<span><?php _e('Enable', ET_DOMAIN) ?></span>
					</a>
				</div>
			</div>
		</div>
	</div>	

</div>
</div>