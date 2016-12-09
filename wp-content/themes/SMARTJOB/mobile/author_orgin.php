<?php et_get_mobile_header('mobile'); ?>
<div data-role="content" class="post-content resume-content-home">
	<?php  
		global $et_global,$post,$wp_query;
		$company_id      = get_query_var('author');	
		$company         = et_create_companies_response( $company_id );
		$company_logo    = $company['user_logo']['company-logo'][0];
		if ( empty($company_logo) ){
			$general_opt  = new ET_GeneralOptions();
			$temp = $general_opt->get_website_logo();
			$company_logo = $temp[0];
		}
		//et_get_job_count(array('post_author' => $company_id));
	$de=get_the_author_ID();
	$user_info = get_userdata($de);
	var_dump($user_info->roles);
	?>
	<h1 class="post-title job-title">
		<?php echo $company['display_name'];?>
		<a href="#" class="post-title-link icon" data-icon="A"></a>
	</h1>
	<div class="company-info inset-shadow">
		<div class="company-detail">
			<?php if ( !empty($company['user_url']) ) : ?>
				<div class="content-info">
					<a class="list-link job-employer" rel="nofollow" target="_blank" href="<?php echo $company['user_url']; ?>"><?php echo $company['user_url']; ?></a>
					<a data-icon="A" class="post-title-link icon ui-link" href="<?php echo $company['user_url']; ?>" rel="nofollow" target="_blank"></a>
				</div>
			<?php endif; ?>
			<div class="content-info">
				<a class="list-link job-loc" href=""> 
					<?php echo _n( sprintf('%d active job',$wp_query->found_posts), sprintf('%d active jobs', $wp_query->found_posts), $wp_query->found_posts ); ?>
				</a>
			</div>
		</div>
	</div>
	
	<ul class="listview" data-role="listview" id="job-content">
	<?php
		if ( have_posts() ) {
			$page       = $wp_query->max_num_pages;
			$class_name = '';
			$first_post = $post->ID;
			$flag       = 0;
			$flag_title = 0;
			while (have_posts() ) { the_post();
				//print_r( $job_type );
				$featured = et_get_post_field( $post->ID, 'featured' );//echo $featured;	
				global $job;
				$job = et_create_jobs_response($post);

				if ($flag_title == 0 && $featured == 1) {
				 	echo '<li class="list-divider">'.__("Featured Jobs",ET_DOMAIN).'</li>';
					$flag_title = 1;
				}
				else if ($featured == $flag ) {
					$flag = 1;
					echo '<li class="list-divider">'.__("Jobs",ET_DOMAIN).'</li>';
				}

				$template_job	= apply_filters( 'et_mobile_template_job', '');
				if( $template_job != '' )
					load_template( $template_job , false);
				else {
					get_template_part( 'mobile/mobile' , 'template-job' );
				}

				// load_template( apply_filters( 'et_mobile_template_job', dirname(__FILE__) . '/mobile-template-job.php'), false);
			}
		}
	?>
	</ul>
	<?php  
		$max_page_company = $wp_query->max_num_pages;
		$cur_page = (get_query_var('paged')) ? get_query_var('paged') : 1;
		if ($max_page_company > 1) {	?>
			<a href="#" class="btn-grey btn-wide btn-load-more ui-corner-all" id="lm_com_job"><?php _e('Load More Jobs',ET_DOMAIN); ?></a>
<?php	}	?>
	<input type="hidden" id="company" value="<?php echo $company_id; ?>">
	<input type="hidden" id="max_page_com" value="<?php echo $max_page_company ;?>">
	<input type="hidden" id="cur_page" value="<?php echo $cur_page; ?>">
</div><!-- /content -->
<?php et_get_mobile_footer('mobile'); ?> 