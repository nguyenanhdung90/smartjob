<?php
	$imgUrl	=	$et_global['imgUrl'];
	$jsUrl	=	$et_global['jsUrl'];
	$general_opts	=	new ET_GeneralOptions();
	$arrAuthors	= array();
	get_header();
?>

<script>
$( document ).ready(function() {
    search_index(1,'');
});
function search_index(page,search) {
	//var t0 = performance.now();
	    $('html,body').scrollTop(0);
	    $("#search_loading").show();
	    $("#result").hide();
if(search==""){var key = $("#search").val();}else {var key = search;$("#search").val(search);}

        var location = $("#job_location").val();
        $.post("<?php bloginfo('template_url')?>/search_ajax.php", {key: key,location: location, page:page}, function(result){
            //$("#result").html(result);
            document.getElementById("result").innerHTML=result;

			$("#result").show();
            $("#search_loading").hide();
        });

/* var t1 = performance.now();
console.log("Call to doSomething took " + (t1 - t0) + " milliseconds.") */
}

</script>
<div class="wrapper clearfix">

		<div class="header-filter top-70" id="header-filter" style="position:relative">
			<div class="main-center f-left-all" style="overflow: hidden;">
				<div class="keyword" style="font-size: 24px;">
					SmartJob is Smart Choice !
				</div>
			</div>
				
			<div class="main-center f-left-all">
			

						<div class="keyword">
							<input id="search" type="text" name="s" class="search-box job-searchbox input-search-box border-radius" placeholder="Enter a keyword ..." value="">
							<span class="icon" data-icon="s"></span>
						</div>
						<div class="form-control input-sm location" style="margin-right: 0px;">
							<select id="job_location" name="job_location2" class="search_province">
								<option value="2146">All</option>
								<option value="2147">Hà Nội</option>
								<option value="2148">Hồ Chí Minh</option>
								<option value="2149">Đà Nẵng</option>
								<option value="2150">Other</option>
							</select>
							<input type="button" id="search_submit" onclick="search_index(1,'')" value="Search">
						</div>
						
			
			
			</div>

			<div class="main-center f-left-all" style="overflow: hidden;">
				<div class="keyword" style="">
				   <span style="font-size:16px"> Popular keywords: </span>
				   <span class="morepolar"><a  href="#" onclick="search_index(1,'java')" class="a_morepola"> Java</a></span>
				   <span class="morepolar"><a  href="#" onclick="search_index(1,'php')" class="a_morepola"> Php</a></span>		 
				   <span class="morepolar"><a  href="#" onclick="search_index(1,'ios')" class="a_morepola"> IOS</a></span>		 
				   <span class="morepolar"><a  href="#" onclick="search_index(1,'android')" class="a_morepola"> Android</a></span>		  
				   <span class="morepolar"><a  href="#" onclick="search_index(1,'c#')" class="a_morepola"> C#</a></span>		  
				   <span class="morepolar"><a  href="#" onclick="search_index(1,'net')" class="a_morepola"> Net</a></span>
				   <span class="morepolar"><a  href="#" onclick="search_index(1,'oracle')" class="a_morepola"> Oracle</a></span>
				   <span class="morepolar"><a  href="#" onclick="search_index(1,'seo')" class="a_morepola"> SEO</a></span>
				   <span class="morepolar"><a href="#"  onclick="search_index(1,'mkt online')" class="a_morepola"> MKT Online</a></span>
				</div>
			</div>
		</div>


	<div class="main-center clearfix padding-top30">

		<div class="main-column">
		<!-- Pending Jobs -->
		<?php
		if(current_user_can ('manage_options')) {
			$args	=	array(
				'post_type' 		=> 'job',
				'post_status' 		=> array('pending'),
				'posts_per_page' 	=> -1,
				'meta_key'			=>	'et_job_paid',
				'orderby'			=>	'meta_value post_date',
				'order'				=>  'DESC'
			);
			add_filter('posts_orderby', 'et_filter_orderby');
			$pending_job	=	new WP_Query ( $args );
			remove_filter('posts_orderby', 'et_filter_orderby');
			?>
			<div id="pending_jobs_container" <?php if( !$pending_job->have_posts() ){ ?>style="display:none;"<?php }?> >
				<h3 class="main-title"><?php _e('PENDING JOBS', ET_DOMAIN )?></h3>
				<ul class="list-jobs pending-jobs job-account-list">
					<?php
					global $post;
					$pending_jobs	= array();
					while( $pending_job->have_posts() ) { $pending_job->the_post ();

						$job		= et_create_jobs_response($post);
						$pending_jobs[]	= $job;

						$job_cat 	= isset($job['categories'][0]) ? $job['categories'][0] : '';
						$job_type 	= isset($job['job_types'][0]) ? $job['job_types'][0] : '';
						$paid 		= ( $job['job_paid'] ) ? __('PAID', ET_DOMAIN) : __('UNPAID', ET_DOMAIN);
						if($job['job_paid'] == 2) $paid	= __('FREE', ET_DOMAIN);

						$company		= et_create_companies_response( $job['author_id'] );
						$company_logo	= $company['user_logo'];
						//$colors  	= 	et_get_job_type_colors();

						// add this company data to the array to pass to js
						if(!isset($arrAuthors[$company['id']])){
							$arrAuthors[$company['id']]	= array(
								'display_name'	=> $company['display_name'],
								'user_url'		=> $company['user_url'],
								'user_logo'		=> $company_logo
							);
						}
						?>
						<li class="job-item">
							<div class="thumb">
							<?php
								if (!empty($company_logo)){
									?>
									<h1><a data="<?php echo $company['ID'];?>" href="<?php echo $company['post_url'];?>"
										title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" id="job_author_name">
										<img src="<?php echo ( isset($company_logo['small_thumb']) && !empty($company_logo['small_thumb']) ) ? $company_logo['small_thumb'][0] : $company_logo['thumbnail'][0]; ?>" id="company_logo_thumb" data="<?php echo $company_logo['attach_id'];?>" />
									</a></h1>
									<?php
								}
							?>
							</div>

							<div class="content">
								<a class="title-link title" href="<?php the_permalink () ?>">
									<?php the_title () ?>
								</a>
								<div class="desc f-left-all">
									<div class="cat company_name a">
										<a data="<?php echo $company['ID'];?>" href="<?php echo $company['post_url'];?>" title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>">
											<?php echo $company['display_name'] ?>
										</a>
									</div>
									<?php if($job_type != '') { ?>
									<div class="job-type <?php echo 'color-' . $job_type['color'] ?>">
										<span class="flag"></span>
										<a href="<?php echo $job_type['url']; ?>" title="<?php printf(__('View all posted jobs in %s', ET_DOMAIN), $job_type['name']);?>">
											<?php echo $job_type['name'] ?>
										</a>
									</div>
									<?php } ?>
									<?php if ($job['location'] != '' ) { ?>
									<div><span class="icon" data-icon="@"></span><span class="job-location"><?php echo $job['location'] ?></span> </div>
									<?php } ?>
								</div>
								<div class="tech font-quicksand f-right actions">
									<a data="<?php echo $post->ID ?>" class="flag <?php echo strtolower($paid) ?>" href="#">
										<?php echo strtoupper($paid) ?><span class="icon" data-icon="%"></span>
										<a data="<?php echo $post->ID ?>" title="<?php _e('Edit', ET_DOMAIN) ?>" class="action-edit tooltip" href="#"><span class="icon" data-icon="p"></span></a></a>
									<a data="<?php echo $post->ID ?>" title="<?php _e('Approve', ET_DOMAIN) ?>" class="color-active action-approve tooltip" href="#"><span class="icon" data-icon="3"></span></a>
									<a data="<?php echo $post->ID ?>" title="<?php _e('Reject', ET_DOMAIN) ?>" class="color-pending action-reject tooltip" href="#"><span class="icon" data-icon="*"></span></a>
								</div>
							</div>
						</li>
						<?php
					}
					?>
				</ul>
			</div>

			<?php if(!empty($pending_jobs)){ ?>
				<script	type="application/json" id="pending_jobs_data">
					<?php echo json_encode($pending_jobs); ?>
				</script>
			<?php } ?>

		<!-- end pending jobs -->

			<?php
		}
		global $wp_query;
		wp_reset_query();

		?>

		<!-- latest job -->
		<div id="latest_jobs_container">
			<h3 class="main-title" style="border:none"><?php echo $list_title; ?></h3>
			<ul class="list-jobs lastest-jobs job-account-list">
					<div id="search_loading" style="overflow:hidden;width:130px;margin:0 auto">
					   <img style="margin-top:90px;width:25px" src="<?php bloginfo('template_url')?>/img/loading.gif">
					</div>
					
				<div id="result">

				</div>
			</ul>
			<script	type="application/json" id="latest_jobs_data">
				<?php echo json_encode(array(
						'status'	=> $list_status,
						'jobs'		=> $latest_jobs
					)); ?>
			</script>
		</div>
		<!-- end latest jobs -->

		<!-- this script passes the companies data for js usage -->
		<script type="application/json" id="companies_data">
			<?php echo json_encode($arrAuthors);?>
		</script>

	  	</div>

	  	<?php get_sidebar () ?>

	</div>
	<?php 
	if(is_search())
    {}
	else{
	if(is_active_sidebar('sidebar-home-bottom')) {  ?>
	<div class="main-center clearfix padding-top30">
		<div class="sidebar-home-bottom <?php if(current_user_can('manage_options') ) echo 'sortable' ?>" id="sidebar-home-bottom" >
			<?php dynamic_sidebar('sidebar-home-bottom'); ?>
		</div>
	</div>
	<?php } 
	}
	?>
</div>
<?php	get_footer(); ?>