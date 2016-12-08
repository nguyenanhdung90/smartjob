<?php
global $wp_query;

get_header();
?>
<div class="wrapper clearfix">
	<div class="heading" style="margin-top: 70px;">
		<div class="main-center">
			<h1 class="title job-title" id="job_title" style="font-size: 22px; font-weight: 500;"><?php single_cat_title( '', true ); ?></h1>
		</div>
	</div>
	<div class="main-center" >
		<div class="main-column" id="entry-list">
			<ul class="entry-blog" >
			<?php
			$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
			if( have_posts() ) {
			while ( have_posts() ) {
				global $post;

				the_post();
				$date		=	get_the_date('d S M Y');
				$date_arr	=	explode(' ', $date );


			?>
			<li>
				<div class="thumbnail">
					<div class="img-thumb" style="border-radius:0px">
						<?php 
						if ( has_post_thumbnail() ) {
						$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
						}else{ $large_image_url[0]=get_bloginfo('stylesheet_directory').'/img/chuanhapanh.jpg';}
						?>
						<img style="border-radius:0px" width="96" height="96" class="avatar avatar-96 photo"  src="<?php echo  $large_image_url[0];?>" alt="">
					</div>
					<div class="author">
							<?php the_author()?>
						</div>
					<div class="join-date"><?php echo $date_arr[2]?> <?php echo $date_arr[0]?><sup><?php echo strtoupper($date_arr[1])?></sup>, <?php echo $date_arr[3]?></div>
				</div>
        		<div class="content" style="min-height:176px">

          			<h2 class="title">
           	 			<a href="<?php the_permalink()?>" title="<?php the_title()?>" style="font-weight:normal "><?php the_title ()?></a>
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
			<?php
			  if (function_exists(custom_pagination)) {
				custom_pagination($custom_query->max_num_pages,"",$paged);
			  }
			?>
			  <?php wp_reset_postdata(); ?>
			<?php }?>
		</div>
		<div class="second-column widget-area " id="sidebar-blog">
				<aside class="widget widget_miw_multi_image_widget" id="miw_multi_image_widget-4"><div class="widget-title" style="margin-top: 12px; font-weight: 500; font-size: 21px;"> Chuyên mục Blog</div>         
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
											if ($category->parent==0)
						                    {	
									?>
									<li style="text-align:left" class="miw-loop">
									<a href="<?php echo get_category_link( $category->term_id );?>"  style="font-weight: 500; font-size: 19px;">  <?php echo $category->cat_name; ?></a>
									<?php
									$term_id = $category->term_id;
									$taxonomy_name = 'category';
									$termchildren = get_term_children( $term_id, $taxonomy_name );

									echo '<ul style="margin-left:16px;">';
									foreach ( $termchildren as $child ) {
									$term = get_term_by( 'id', $child, $taxonomy_name );
									echo '<li><a href="' . get_term_link( $child, $taxonomy_name ) . '">- ' . $term->name . '</a></li>';
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