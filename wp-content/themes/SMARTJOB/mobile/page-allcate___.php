<?php
/**
 * Template Name: all category
 */

?>
<?php
get_header();
?>
<div class="wrapper clearfix">
	<div class="heading">
		<div class="main-center">
			<h1 id="job_title" class="title job-title">OUR BLOG</h1>
		</div>
	</div>
	<div class="main-center">
		<div id="entry-list" class="main-column">
			<ul class="entry-blog">
<?php 
// the query to set the posts per page to 3
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = array('posts_per_page' => 6, 'paged' => $paged );
query_posts($args); ?>
<!-- the loop -->
<?php if ( have_posts() ) : while (have_posts()) : the_post(); ?>
            <li>
				<div class="thumbnail">
					<div class="img-thumb" style="border-radius:0px ">
											<?php 
						if ( has_post_thumbnail() ) {
						$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
						}else{$ca= get_bloginfo('stylesheet_directory'); $large_image_url[0]='http://2.gravatar.com/avatar/80702f13faa0f502a4fa30b32323dd5a?s=96&amp;d=wavatar&amp;r=g';}
						?>
						<img width="96" height="96" class="avatar avatar-96 photo" style="border-radius:0px " src="<?php echo  $large_image_url[0];?>" alt="">		
						</div>
					<div class="author">
							 <?php the_author(); ?> 				</div>
					<div class="join-date"><?php the_time('l, F jS, Y') ?></div>
				</div>
        		<div class="content">
	
          			<h2 class="title">
           	 			<a title="<?php the_title();?>" href="<?php the_permalink();?>"><?php the_title();?></a>
          			</h2>
          			<div class="description">

						<p> <?php the_excerpt(); ?> </p>

          			</div>
          			<div class="footer">
                		<a title="View post Những bài học đắt giá từ hợp tác kinh doanh với bạn thân" href="<?php the_permalink();?>">
		      	          	READ MORE <span data-icon="]" class="icon"></span>
		      	        </a>
             		</div>
        		</div>
        	</li>
<?php endwhile; ?>
<!-- pagination -->
<span><?php previous_posts_link(); ?></span>
<span style="margin-left:12px"><?php next_posts_link(); ?></span>
<?php else : ?>
<!-- No posts found -->
<?php endif; ?>						
        					</ul>
								</div>
					<div class="second-column widget-area " id="sidebar-blog">
			<aside class="widget widget_miw_multi_image_widget" id="miw_multi_image_widget-4"><div class="widget-title" style="margin-top:12px"> Chuyên mục Smartjob</div>         

            <div class="miw-container">
<ul class="miw miw-linear">
			 <?php 
			$args2 = array(
				'type'                     => 'post',
				'child_of'                 => $category[0]->category_parent,
				'parent'                   => '',
				'orderby'                  => 'name',
				'order'                    => 'ASC',
				'hide_empty'               => 1,
				'hierarchical'             => 1,
				'exclude'                  => '',
				'include'                  => '',
				'number'                   => '',
				'taxonomy'                 => 'category',
				'pad_counts'               => false 
			); 
				  $categories = get_categories($args2); 
				  foreach ($categories as $category) 
				  {
			?>
<li style="text-align:center" class="miw-loop">
<a href="<?php echo get_category_link( $category->term_id );?>">  <?php echo $category->cat_name; ?></a>
</li>  
  	<?php } ?>                                            
</ul>    
            </div>
            </aside>
		</div>
		
	</div>
</div>
<?php
get_footer();
?>