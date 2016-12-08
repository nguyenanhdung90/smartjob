<?php
et_get_mobile_header('mobile');
global $wp_query;
$queried_object =   $wp_query->queried_object;

?>
<div data-role="content" class="resume-contentpage" style="margin-bottom:0px">
	<h1 class="title-resume" style="font-size:25px"><?php single_cat_title( '', true ); ?></h1>
    <div class="list-blog inset-shadow" style="background-color:white;box-shadow: none;">
    	<ul>
            <?php while(have_posts()) { the_post(); 
                $date       =   get_the_date('d S M Y');
                $date_arr   =   explode(' ', $date );

                $cat        =   wp_get_post_categories($post->ID);

                $cat        =   get_category($cat[0]);
            ?>
            	<li>
                    <div class="blog-content">
                        <a href="<?php the_permalink(); ?>" class="blog-title" style="font-size:24px;padding-top: 11px;line-height: 31px;"><?php the_title(); ?></a>
						<?php 
						if ( has_post_thumbnail() ) {
						$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'medium' );
						}else{ $large_image_url[0]=get_bloginfo('stylesheet_directory').'/img/chuanhapanh.jpg';}
						?>
                        <a href="<?php the_permalink(); ?>" class="blog-title" title=""><img class="img_mobile_cate" src="<?php echo $large_image_url[0];?>" ></a>						
                        <div class="blog-text">
                            <?php the_excerpt(); ?>
                        </div>
						<div class="continue"><a href="<?php the_permalink(); ?>" style="color:white;text-decoration:none;font-weight:300">Continue reading</a></div>
						<div class="like_share_cate" >
							<div class="fb-like" data-href="https://www.facebook.com/smartjob.vn/?fref=ts" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div>
							<div class="fb-share-button" data-href="<?php the_permalink()?>" data-layout="button_count"></div>
						</div>
                    </div>
                </li>

            <?php } ?>
        </ul>
    </div>

    <div class="load-blog inset-shadow" style="background-color:white">
			<?php
			  if (function_exists(custom_pagination)) {
				custom_pagination($custom_query->max_num_pages,"",$paged);
			  }
			?>
			<?php wp_reset_postdata(); ?>			
    </div>
	<div style="overflow:hidden;background-color:white">
		<div style="font-size: 20px;font-weight: 500;text-align: center;padding-bottom: 17px;padding-top: 12px;">Search jobs with keywords :</div>
		<div class="popular_job"><a href="<?php bloginfo('url');?>/?s=java">java</a></div>
		<div class="popular_job"><a href="<?php bloginfo('url');?>/?s=php">php</a></div>
		<div class="popular_job"><a href="<?php bloginfo('url');?>/?s=ios">ios</a></div>
		<div class="popular_job"><a href="<?php bloginfo('url');?>/?s=android">android</a></div>
		<div class="popular_job"><a href="<?php bloginfo('url');?>/?s=c#">c#</a></div>
		<div class="popular_job"><a href="<?php bloginfo('url');?>/?s=net">net</a></div>	
		<div class="popular_job"><a href="<?php bloginfo('url');?>/?s=Oracle">Oracle</a></div>
		<div class="popular_job"><a href="<?php bloginfo('url');?>/?s=ASP.Net">ASP.Net</a></div>
		<div class="popular_job"><a href="<?php bloginfo('url');?>/?s=Objective">Objective C</a></div>
		<div class="popular_job"><a href="<?php bloginfo('url');?>/?s=C language">C language</a></div>
		<div class="popular_job"><a href="<?php bloginfo('url');?>/?s=HTML5">HTML5</a></div>
		<div class="popular_job"><a href="<?php bloginfo('url');?>/?s=Unity">Unity</a></div>
	</div>
	<div style="overflow:hidden;background-color:white">
	    <div style="font-size: 24px;font-weight: 500;text-align: center;padding-bottom: 17px;padding-top: 12px;">Our blog:</div>
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
		<div class="more_title_mobile_content"><a href="<?php echo get_category_link( $category->term_id );?>" class="more_title_mobile"><?php echo $category->cat_name; ?></a></div>
		<?php } ?>   
	</div>
</div><!-- /content -->

<?php et_get_mobile_footer('mobile'); ?>