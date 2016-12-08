<?php et_get_mobile_header('mobile');wpb_set_post_views(get_the_ID());
if(have_posts()) { the_post ();
	global $post;
	$date       =   get_the_date('d S M Y');
    $date_arr   =   explode(' ', $date );

    $cat        =   wp_get_post_categories($post->ID);

    $cat        =   get_category($cat[0]);
?>
<style type="text/css">
.blog-content{text-align:justify;padding-top:12px;background-color:white}
.blog-content img{max-width:100%;height:auto;}
</style>
<div data-role="content" class="resume-contentpage" style="margin-bottom:0px">
	<h1 class="title-resume"><?php _e( $cat->name, ET_DOMAIN); ?></h1>
    <div class="blog-content">
    	<h2 class="blog-title"><?php the_title(); ?>(<?php echo wpb_get_post_views(get_the_ID()); ?>)</h2>
        <div class="blog-text" style="padding:1px 20px;font-weight: 500;">
        	<?php the_content(); ?>
        </div>
    </div>
	<div class="infor-resume inset-shadow clearfix" style="background-color:white;box-shadow:none">
    	<div>
    		<h1 style='font-size:16px;font-weight:500;font-family:"Roboto",Arial,Helvetica,sans-serif;line-height:32px' ><?php the_author(); ?>-<?php the_date(); ?></h1>
    		<p class="blog-date">
                <span style='font-size:16px;font-weight:500;font-family:"Roboto",Arial,Helvetica,sans-serif;' >
                    <a href="<?php echo get_category_link($cat)?>" style="color:#F0111B;">
                        <?php echo $cat->name ?>
                    </a> 
                </span>
            </p>
    	</div>
	</div>
	<div style="padding: 13px;background-color:white"><label>Tags:</label>
	<?php
	if(get_the_tags())		{					
	foreach (get_the_tags() as $tag)
	{
	?>
	<a href="<?php echo get_tag_link($tag->term_id);?>" style="padding: 11px;color:#F01130;text-decoration:none;line-height:22px"><?php echo  $tag->name;?></a>,
	<?php }}?>
	</div>
	<!--
    <div class="blog-content">
		<div style="margin-bottom:4px;padding-left: 10px;">
			<div class="fb-like" data-href="<?php //the_permalink()?>https://www.facebook.com/smartjob.vn?fref=ts" data-layout="box_count" data-action="like" data-show-faces="true" data-share="false"></div>
			<div class="fb-share-button" data-href="<?php //the_permalink()?>" data-layout="box_count"></div>
		</div>
		<div style="margin-bottom:1px">
	       	<div class="fb-comments" data-href="<?php //the_permalink()?>" data-width="100%" data-numposts="4"></div>
		</div>
    </div>    
	-->
	<div class="blog-content" style="padding-bottom: 9px;">
	    <h2 class="blog-title" style="color: #F0111B;"> May be interested posts:</h2>
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
		?>	
    	<h2 class="blog-title"><a href="<?php the_permalink();?>" style="text-decoration:none"> <?php the_title();?></a></h2>
		<?php
				}		
			}
		}
		wp_reset_query();
		?>
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
<?php
}
et_get_mobile_footer('mobile'); ?>