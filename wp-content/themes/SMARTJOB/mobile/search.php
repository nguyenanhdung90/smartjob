<?php et_get_mobile_header('mobile');  
	global $et_global,$post,$wp_query;
	$arr             = array();
	$job_type_color  = et_get_job_types();
	$colours         = et_get_job_type_colors();
	$enable_featured = et_is_enable_feature();
?>
<style type="text/css">
.search-results
{
padding-top:0px!important;
}
.search-results a.icon{color:white!important;}
.search-results .ui-btn-s{padding:12px 8px 14px 12px;}
</style>
<div data-role="search" class="search-area">
	<div class="search">
		<a href="#" class="icon ui-btn-s search-btn  category-btn" data-icon="l"></a>
		<div class="search-text">
			<input type="text" name="search" id="txt_search" class="txt_search" placeholder="<?php _e("Enter job titles", ET_DOMAIN); ?>" >
			<span class="icon" data-icon="s"></span>
		</div>

		<div class="menu-filter">
			<div class="menu-filter-inner">
				<div class="icon-header">
					<a class="icon" data-icon="l"></a>
				</div>
	            <div class="search">
					<input type="text" name="search" id="search_location" title="<?php _e("Enter the location...", ET_DOMAIN); ?>" placeholder="<?php _e("Enter the location...", ET_DOMAIN); ?>">
					<span class="icon" data-icon="@"></span>
				</div>
				<div class="tabs job-tabs">
					<a class="ui-tabs ui-corner-left tab-active" id="cat">
						<?php _e('Categories',ET_DOMAIN); ?>
					</a>
					<a class="ui-tabs ui-corner-right" id="job-type" >
						<?php _e('Contract types',ET_DOMAIN) ?>
					</a>
				</div>

	            <div class="content-tabs job-contents">
	            	<div class="tab-cont cat">
	            		<div class="list-categories">
	            			<a data="" class="ui-list ui-list-active ui-list-main"><?php _e('All categories',ET_DOMAIN); ?></a>
	            			<ul>
	            				<?php et_template_front_category_mobile(); ?>
	            			</ul>
	            		</div>
	            	</div>
	            	<div class="tab-cont job-type">
	            		<div class="contact-type">
	                		<ul>
	            				<?php
	            					foreach ($job_type_color as $key => $value) { ?>
	            						<li><a data="<?php echo $value->slug; ?>" class="ui-list color-<?php if(isset($colours[$value->term_id])) echo $colours[$value->term_id]; ?>"><?php echo $value->name ; ?><span class="icon-label flag"></span></a></li>
	            				<?php }	?>

	            			</ul>
	            		</div>
	            	</div>
	            </div>

	            <a href="#" class="ui-btn-s btn-grey filter-search-btn" id="et_search_cat"> <?php _e('Search',ET_DOMAIN); ?> </a>
	        </div>
		</div>
	</div>
</div>

<?php if (is_home() && is_active_sidebar( 'top_mobile' ) ) : ?>
	<div class="top-sidebar">
		<?php dynamic_sidebar( 'top_mobile' ); ?>
	</div>
<?php endif; ?>

<div data-role="content" id="page" class="ui-home resume-content-home">
	<ul class="listview" data-role="listview" id="job-content">
		<?php
		if ( have_posts() ) {
			$page       = $wp_query->max_num_pages;
			$class_name = '';
			$first_post = $post->ID;
			$flag       = 0;
			$flag_title = 0;
			//echo '<li class="list-divider">'.__("Feature Jobs",ET_DOMAIN).'</li>';
			while (have_posts() ) { the_post();
				//print_r( $job_type );
				$featured = et_get_post_field( $post->ID, 'featured' );//echo $featured;
				global $job;
				$job = et_create_jobs_response($post);
			
				$template_job	= apply_filters( 'et_mobile_template_job', '');
				if( $template_job != '' )
					load_template( $template_job , false);
				else {
					get_template_part( 'mobile/mobile' , 'template-job' );
				}

				// load_template( apply_filters( 'et_mobile_template_job', dirname(__FILE__) . '/mobile-template-job.php'), false);
			}
		} ?>
	</ul>
	<?php
		$cur_page = (get_query_var('paged')) ? get_query_var('paged') : 1;
	?>
		<a <?php if ( isset($page) && $cur_page >= $wp_query->max_num_pages ) { echo "style='display:none;'"; } ?> href="#" class="btn-grey btn-wide btn-load-more ui-corner-all et_loadmore" id="et_loadmore"><?php _e('Load More Jobs',ET_DOMAIN); ?></a>
		<div class="outblog_mobile" >
			<div class="tieude_mobile_outblog" style="">
				<a href="#">Out blog</a>
			</div>
			<?php 
			$args = array(
			'post_type' => 'post',
			'showposts'=>3,
				);
			$query = new WP_Query( $args );while ( $query->have_posts() ) 
			{
			$query->the_post();
			?>
				<div class="out_blog_new">
					<a href="<?php the_permalink(); ?>" class="font-roboto"><?php the_title();?></a>
					<p class="expert_mobile font-roboto"><?php echo excerpt(30) ;?></p>
				</div>
			<?php 
			}
			wp_reset_postdata();
			?>
		</div>
</div><!-- /content -->
<input type="hidden" id="cur_page_index" value="<?php echo $cur_page; ?>">
<?php et_get_mobile_footer('mobile'); ?>