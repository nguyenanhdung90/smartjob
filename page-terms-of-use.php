<?php
/**
 *	Template Name: Term of use
 */
get_header();
?>
<?php if (have_posts()) {
	the_post();
?>
<div class="wrapper clearfix">
	<div class="heading">
		<div class="main-center">
			<h1 class="title job-title" id="job_title"><?php the_title()?></h1>
		</div>
	</div>
	<div class="main-center">
		<div class="full-column">
			<div class="entry-blog tinymce-style">
		      	<?php the_content () ?>
		 	</div>
		</div>
	</div>
</div>
<?php }

get_footer();