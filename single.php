<?php
get_header();
if(have_posts() ) {
	global $post;
	the_post();
	$date		=	get_the_date('d S M Y');
	$date_arr	=	explode(' ', $date );
	
	$cat		=	wp_get_post_categories($post->ID);
	if(isset($cat[0])) 
	$cat		=	get_category($cat[0]);

?>
<div class="wrapper clearfix">
	<div class="heading">
		<div class="main-center">
			<h1 class="title job-title" id="job_title"><?php _e("Blog",ET_DOMAIN);?></h1>
		</div>
	</div>
	<div class="main-center">
		<div class="main-column">
			<div class="entry-blog ">
        		<div style="padding-left:12px">
	          		<div class="header font-quicksand">
	           			<?php if(isset($cat->name ) )  { ?>
		           			<a href="<?php  echo get_category_link($cat)?>">
								<?php echo $cat->name ?>
		           			</a> 
		           		<?php } ?>
	           			<a href="<?php the_permalink()?>" class="comment">

	           				<?php //comments_number('0','1','%')?>
	           			</a>
	          		</div>
          			<h2 class="title" style=" font-size: 30px;font-weight: normal;line-height: 38px;">
           	 			<a href="<?php the_permalink()?>" title="<?php the_title()?>" ><?php the_title ()?></a>
          			</h2>
          			<div class="description tinymce-style" style="font-size: 15px;line-height:1.6;letter-spacing:1px">
		      	          <?php the_content('')?>
          			</div>
					<div class="header font-quicksand">
					<div class="author">						
						<?php //the_author()?>						
					</div>
					<div class="join-date"><?php //the_date(); ?></div>
					</div>
        		</div>
				<div style="margin-top: 27px;">
					<div class="header font-quicksand" style="border-top: 1px solid #8C9AA3;font-size:22px;padding-top:35px;padding-bottom:35px">
						May be interested posts:
					</div>
					<?php
					$categories = get_the_category($post->ID);
					if ($categories) 
					{
						$category_ids = array();
						foreach($categories as $individual_category) $category_ids[] = $individual_category->term_id;
						$args=array(
						'category__in' => $category_ids,
						'post__not_in' => array($post->ID),
						'showposts'=>5, // Number of related posts that will be shown.
						'caller_get_posts'=>1
						);
						$my_query = new wp_query($args);
						
						if( $my_query->have_posts() ) 
						{
							while ($my_query->have_posts()) 
							{
						$my_query->the_post();
						if ( has_post_thumbnail() ) {
							$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail' );							
						}else{
$ca= get_bloginfo('stylesheet_directory'); $large_image_url[0]='http://2.gravatar.com/avatar/80702f13faa0f502a4fa30b32323dd5a?s=96&amp;d=wavatar&amp;r=g';			
						}
					?>	
					<div style="padding: 4px;">
					    <a href="<?php the_permalink();?>" style="padding-right: 12px;">
						<img style="width:52px;height:auto" src="<?php echo  $large_image_url[0];?>">
						</a>
						<a style="color:#2b3942;font-weight:normal;font-size:18px" href="<?php the_permalink();?>"><?php the_title();?></a>
					</div>
					<?php
							}
					
						}
					}
					wp_reset_query();
					?>
				</div>
    
		 	</div>
		</div>

		<?php if(is_active_sidebar('sidebar-blog')) {  ?>
			<div id="sidebar-blog" class="second-column f-right widget-area <?php if(current_user_can('manage_options') ) echo 'sortable' ?>" style="margin-top:14px">
			<?php dynamic_sidebar('sidebar-blog');?>
			</div>
		<?php } ?>
	</div>
</div>
<?php }
get_footer();
