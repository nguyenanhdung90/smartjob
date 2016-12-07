<?php
global $wp_query;

get_header();
?>
<div class="wrapper clearfix">
	<div class="heading">
		<div class="main-center">
			<h1 class="title job-title" id="job_title"><?php _e("OUR BLOG",ET_DOMAIN);?></h1>
		</div>
	</div>
	<div class="main-center" >

		<div class="main-column" id="entry-list">
			<ul class="entry-blog" >
			<?php
			if( have_posts() ) {
			while (have_posts() ) {
				global $post;
				the_post();

				$cat		=	wp_get_post_categories($post->ID);

				$cat		=	get_category($cat[0]);
			?>
			<li>
				<div class="thumbnail font-quicksand">
					<div class="img-thumb">
						<?php echo get_avatar($post->post_author)?>
					</div>
					<div class="author">
							<?php the_author()?>
						</div>
					<div class="join-date"><?php echo get_the_date(); ?></div>
				</div>
        		<div class="content">
	          		<div class="header font-quicksand">
	           			<a href="<?php echo get_category_link($cat)?>">
							<?php echo $cat->name ?>
	           			</a> 
	           			<a href="<?php the_permalink()?>" class="comment">
	           				<span class="icon" data-icon="q"> <?php comments_number('0','1','%')?></span>
	           			</a>
	          		</div>
          			<h2 class="title">
           	 			<a href="<?php the_permalink()?>" title="<?php the_title()?>" ><?php the_title ()?></a>
          			</h2>
          			<div class="description">

						<?php the_excerpt() ?>

          			</div>
          			<div class="footer font-quicksand">
                		<a href="<?php the_permalink()?>" title="<?php printf(__("View post %s",ET_DOMAIN), get_the_title())?>">
		      	          	<?php _e("READ MORE",ET_DOMAIN);?> <span class="icon" data-icon="]"></span>
		      	        </a>
             		</div>
        		</div>
        	</li>
        	<?php } ?>
			</ul>
			<?php if ($wp_query->max_num_pages > 1 ){
					$query_vars	=	$wp_query->query_vars;
					$year		=	isset( $query_vars['year']) ? 'year='. $query_vars['year'] : '';
					$m			=	isset( $query_vars['m']) ?  '&monthnum='.$query_vars['monthnum'] : '';
					$day		=	isset( $query_vars['day']) ? '&day='.$query_vars['day'] : '';
					$date_value	=	$year.$m.$day;
				?>
				<div class="button-more">
					<button class="btn-background border-radius" id="load-more-post"><?php _e("Load More Articles",ET_DOMAIN);?></button>
					<input type="hidden" name="date" id="template" value="<?php echo $date_value ?>"/>
				</div>
			<?php }?>
			<?php }?>
		</div>

		<?php if(is_active_sidebar('sidebar-blog')) {  ?>
			<div id="sidebar-blog" class="second-column widget-area <?php if(current_user_can('manage_options') ) echo 'sortable' ?>">
			<?php dynamic_sidebar('sidebar-blog');?>
			</div>
		<?php } ?>

	</div>
</div>

<?php get_footer(); ?>
