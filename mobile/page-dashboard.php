<?php et_get_mobile_header('mobile'); ?>
<div data-role="content" class="post-content resume-content-home">
	<h1 class="post-title job-title">
		<?php _e("ACCOUNT", ET_DOMAIN); ?>
<!-- 		<span class="post-title-right"><a href="#">Logout</a></span> -->
	</h1>
	<ul class="listview" data-role="listview" id="job-content">
	<?php
	global $current_user;
	$statuses = array(
			'archive' =>
				array(
					'title' => __('ARCHIVED', ET_DOMAIN),
					'class' => 'expired'
				),
			'draft' =>
				array(
					'title' => __('DRAFT', ET_DOMAIN),
					'class' => 'pending'
				),
			'pending' =>
				array(
					'title' => __('PENDING', ET_DOMAIN),
					'class' => 'pending'
				),
			'publish' =>
				array(
					'title' => __('ACTIVE', ET_DOMAIN),
					'class' => 'active'
				),
			'reject' =>
				array(
					'title' => __('REJECTED', ET_DOMAIN),
					'class' => 'pending'
				),
			);
	$queries = array('reject', 'pending',  'publish', 'draft', 'archive');
	foreach ($queries as $status) :
		$query = new WP_Query( array(
			'author' 			=> $current_user->ID,
			'post_type' 		=> 'job',
			'post_status' 		=> $status,
			'posts_per_page' 	=> -1,
			//'suppress_filters'	=> 'false'
		));
		if ( $query->have_posts() ) {
			$page       = $wp_query->max_num_pages;
			$class_name = '';
			$first_post = $post->ID;
			$flag       = 0;
			$flag_title = 0;
			//echo '<li class="list-divider">'.__("Feature Jobs",ET_DOMAIN).'</li>';
			echo '<li class="list-divider">'.sprintf(__("%s JOBS",ET_DOMAIN) , $statuses[$status]['title'] ).'</li>';
			while ($query->have_posts() ) { $query->the_post();
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
			}
		}

		endforeach;
	?>
	</ul>


	<form action="" method="post">
		<div class="content-field f-padding">
			<div class="input-button">
				<input type="button" class="et_logout" value="<?php _e('Logout', ET_DOMAIN) ?>">
			</div>
		</div>
	</form>
</div><!-- /content -->

<?php et_get_mobile_footer('mobile'); ?>