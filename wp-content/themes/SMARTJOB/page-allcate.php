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
			<h1 id="job_title" class="title job-title">Lastest our blogs</h1>
		</div>
	</div>
	<div class="main-center">
		<div id="entry-list" class="main-column">
			<ul class="entry-blog">
<?php 
// the query to set the posts per page to 3
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = array('paged' => $paged );
query_posts($args); ?>
<!-- the loop -->
<?php if ( have_posts() ) : while (have_posts()) : the_post();
				$cat		=	wp_get_post_categories($post->ID);
				$cat		=	get_category($cat[0]);
 ?>
            <li>
				<div class="thumbnail">
					<div class="img-thumb" style="border-radius:0px ">
											<?php 
						if ( has_post_thumbnail() ) {
						$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'medium' );
						}else{$ca= get_bloginfo('stylesheet_directory'); $large_image_url[0]=get_bloginfo('stylesheet_directory').'/img/chuanhapanh.jpg';}
						?>
						<a href="<?php the_permalink();?>">
						<img width="96" height="96" class="avatar avatar-96 photo" style="border-radius:0px " src="<?php echo  $large_image_url[0];?>" alt="">		
						</a>
					</div>
					<div class="author">
							 <?php the_author(); ?> 				</div>
					<div class="join-date"><?php the_time('l, F jS, Y') ?></div>
				</div>
        		<div class="content" style="min-height:176px">
	
          			<h2 class="title">
           	 			<a title="<?php the_title();?>" href="<?php the_permalink();?>" style="font-weight:normal"><?php the_title();?></a>
          			</h2>
					<div class="header font-quicksand">
	           			<a href="<?php echo get_category_link($cat)?>" style="color:#666679;font-size:14px">
							<?php echo $cat->name ?>
	           			</a>
	          		</div>
          			<div class="description">

						<p> <?php the_excerpt(); ?> </p>

          			</div>
          			<div class="footer">
                		<a title="view more <?php the_title();?>" href="<?php the_permalink();?>" style="color:#2a4560">
		      	          	READ MORE <span data-icon="]" class="icon"></span>
		      	        </a>
             		</div>
        		</div>
        	</li>
<?php endwhile; ?>
<!-- pagination -->
			<?php
			  if (function_exists(custom_pagination)) {
				custom_pagination($custom_query->max_num_pages,"",$paged);
			  }
			?>
			  <?php wp_reset_postdata(); ?>
<?php else : ?>
<!-- No posts found -->
<?php endif; ?>						
        					</ul>
								</div>
					<div class="second-column widget-area " id="sidebar-blog">
			<aside class="widget widget_miw_multi_image_widget" id="miw_multi_image_widget-4"><div class="widget-title" style="margin-top: 12px; font-weight: 500; font-size: 21px;background-color:#2a4560;color:white;padding-top:7px;padding-bottom:7px"> Chuyên mục Blog</div>         

            <div class="miw-container">
				<ul class="miw miw-linear">
				 <?php 
				$args2 = array(
					'type'                     => 'post',
					//'child_of'                 => $category[0]->category_parent,
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
						  //echo $category->parent;
						  if ($category->parent==0)
						  {	
				?>
					<li style="text-align:left " class="miw-loop">
					<a href="<?php echo get_category_link( $category->term_id );?>"  style="font-weight: 400; font-size: 17px;">  <?php echo $category->cat_name; ?></a>
					<?php
					$term_id = $category->term_id;
					$taxonomy_name = 'category';
					$termchildren = get_term_children( $term_id, $taxonomy_name );

					echo '<ul style="margin-left:16px;">';
					foreach ( $termchildren as $child ) {
					$term = get_term_by( 'id', $child, $taxonomy_name );
					echo '<li><a href="' . get_term_link( $child, $taxonomy_name ) . '" style="font-weight:300;border-bottom:1px dotted #ededed ">- ' . $term->name . '</a></li>';
					}
					echo '</ul>';	
					?>
					</li>  
				<?php 
						  }
					  }				
				?>                                            
				</ul>    
            </div>
            </aside>
		</div>
		
	</div>
</div>
<?php
get_footer();
?>