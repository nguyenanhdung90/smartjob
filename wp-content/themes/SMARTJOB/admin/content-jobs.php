<?php 
	$job_types		=	et_get_job_types_in_order(); //et_get_job_types();
	$job_categories	=	et_get_job_categories();
?>
	<div class="title font-quicksand"><?php _e("Job Types",ET_DOMAIN);?></div>
	<div class="desc" id="job-content">
		<?php _e("Job types (e.g., full-time, part-time, contractual) are used by job seekers to filter job posts.",ET_DOMAIN);?> 
		<!-- <a class="find-out font-quicksand" href="#">
			<?php _e("Find out more",ET_DOMAIN);?> 
			<span class="icon" data-icon="i"></span>
		</a> -->
		<div class="types-list-container" id="job-types">
			<?php 
				$job_type	=	new JE_JobType ();
				$job_type->print_backend_terms();
				$job_type->print_confirm_list ();
			 ?>
			
		</div>
	</div>

	<div class="title font-quicksand"><?php _e("Job Categories",ET_DOMAIN);?></div>
	<div class="desc">
		<?php _e("Job seekers can filter their job searches by Job Categories",ET_DOMAIN);?> 
		<?php /*
		<!-- <a class="find-out font-quicksand" href="#">
			<?php _e("Find out more",ET_DOMAIN);?> <span class="icon" data-icon="i"></span>
		</a> -->
		*/ ?>
		<div class="cat-list-container" id="job-categories">
			<?php 
				$job_category	=	new JE_JobCategory ();
				$job_category->print_backend_terms ();
				$job_category->print_confirm_list ();
			 ?>
		</div>
		
	</div>