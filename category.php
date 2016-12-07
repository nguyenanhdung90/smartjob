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
			while ( have_posts() ) {
				global $post;

				the_post();
				$date		=	get_the_date('d S M Y');
				$date_arr	=	explode(' ', $date );

				$cat		=	wp_get_post_categories($post->ID);

				$cat		=	get_category($cat[0]);
			?>
			<li>
				<div class="thumbnail">
					<div class="img-thumb" style="border-radius:0px">
						<?php 
						if ( has_post_thumbnail() ) {
						$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
						}else{$ca= get_bloginfo('stylesheet_directory'); $large_image_url[0]='http://2.gravatar.com/avatar/80702f13faa0f502a4fa30b32323dd5a?s=96&amp;d=wavatar&amp;r=g';}
						?>
						<img style="border-radius:0px" width="96" height="96" class="avatar avatar-96 photo"  src="<?php echo  $large_image_url[0];?>" alt="">
					</div>
					<div class="author">
							<?php the_author()?>
						</div>
					<div class="join-date"><?php echo $date_arr[2]?> <?php echo $date_arr[0]?><sup><?php echo strtoupper($date_arr[1])?></sup>, <?php echo $date_arr[3]?></div>
				</div>
        		<div class="content">
	          		<div class="header font-quicksand">
	           			<a href="<?php echo get_category_link($cat)?>">
							<?php echo $cat->name ?>
	           			</a>
	           			<a href="<?php the_permalink()?>" class="comment">
	           				<span class="icon" data-icon="q"></span>
	           				<?php comments_number('0','1','%')?>
	           			</a>
	          		</div>
          			<h2 class="title">
           	 			<a href="<?php the_permalink()?>" title="<?php the_title()?>" ><?php the_title ()?></a>
          			</h2>
          			<div class="description">

						<?php the_excerpt() ?>

          			</div>
          			<div class="footer">
                		<a href="<?php the_permalink()?>" title="<?php printf(__("View post %s",ET_DOMAIN), get_the_title())?>">
		      	          	<?php _e("READ MORE",ET_DOMAIN);?> <span class="icon" data-icon="]"></span>
		      	        </a>
             		</div>
        		</div>
        	</li>
        	<?php } ?>
			</ul>
			<?php if ($wp_query->max_num_pages > 1 ){ ?>
				<div class="button-more">
					<button class="btn-background border-radius" id="load-more-post"><?php _e("Load More Articles",ET_DOMAIN);?></button>
					<input type="hidden" name="template" id="template" value="<?php echo $wp_query->query_vars['cat'] ?>"/>
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
<?php
get_footer();