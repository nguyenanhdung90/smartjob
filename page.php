<?php
get_header();
?>
<?php if (have_posts()) {
	the_post();
	$column	= 'full-column';
	if(is_active_sidebar('sidebar-page')) $column	=	'main-column'; 
?>
<div class="wrapper clearfix">
	<div class="heading">
		<div class="main-center">
			<h1 class="title job-title" id="job_title"><?php the_title()?></h1>
		</div>
	</div>
	<div class="main-center">
		<div class="<?php echo $column; ?>">
			<div class="entry-blog tinymce-style">
		      	<?php the_content () ?>
		 	</div>
		</div>
		<?php if(is_active_sidebar('sidebar-page')) {  ?>
			<div id="sidebar-page" class="second-column f-right widget-area <?php if(current_user_can('manage_options') ) echo 'sortable' ?>">
			<?php dynamic_sidebar('sidebar-page');?>
			</div>
		<?php } ?>
	</div>
</div>
<?php }

get_footer();