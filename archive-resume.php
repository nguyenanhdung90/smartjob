<?php

global $current_user, $wp_query;
get_header(); ?>

<div class="heading">
	<div class="main-center">
		<h1 class="title"><?php// _e("Resumes",ET_DOMAIN);?></h1>
	</div>
</div>

<div class="wrapper jobseeker" style="padding-bottom:0px">
<!--
	<div class="heading" style="margin-top: 24px;">
				<div class="main-center">
					<h1 style="font-weight: 500;" class="title">Resume</h1>
				</div>
	</div> -->
	<div class="header-content">
		<div class="main-center" style="overflow:hidden">
			<div class="desc headline" >
			    <h2 style="text-align: center; font-weight: normal; color: rgb(51, 51, 51); margin-bottom: 14px; letter-spacing: 0px;">Steps for Job Seekers</h2>
				<h6 style="text-align: center; color: rgb(51, 51, 51); font-weight: normal; font-size: 16px; margin: 0px auto; line-height: 24px; width: 345px;">Creating a personal profile is very simple</h6>
				<h6 style="text-align: center; color: rgb(51, 51, 51); font-weight: normal; font-size: 16px; margin: 0px auto; line-height: 24px; width: 403px;">and fast,please click on the create a resumer now.</h6>
				<h6 style="text-align:center;margin-top:12px;margin-bottom:29px"><img style="max-width:100%" src="<?php bloginfo('stylesheet_directory');?>/img/sodo-resume.png"></h6>
			</div>
			<!--
			<div class="desc headline" >
			<a href="http://smartjob.vn/create-a-resume/">
			<img src="<?php // bloginfo('stylesheet_directory');?>/img/company_seeker.jpg">
			</a>
			</div>
			-->
			<!--
			<div class="desc headline" style="float:left;padding-left:52px">
                            <p style="color:#221f20;font-size:18px;font-weight:500" >If you are company</p>	 
							<p style="color:#221f20;font-size:18px;font-weight:500" >Please contact with us to get more infomation about resumes.</p>	
							<p style="color:#f0111b;font-size:15px;font-weight:500">(+84) 04 – 62944447<br>contact@smartjob.vn</p>
				<?php
				//$option	=	JE_Resume_Options::get_instance ();
				//printf( apply_filters('je_resume_headline', $option->et_jobseeker_headline ) ); ?>
			</div>
			-->

			<!--
			<div class="desc headline" style="float:left;padding-left:52px">
                            <p style="color:#221f20;font-size:18px;font-weight:500" >If you are Jobseeker</p>	 
							<p style="color:#221f20;font-size:18px;font-weight:500" >Please click to create your resumes <a href="http://smartjob.vn/create-a-resume/"> Create your resume </a>.</p>							
			</div>
			-->
		</div>
	</div>
	<div class="header-content" style="background-color: #f8f8f8;padding-bottom:35px">
		<div class="main-center" style="overflow:hidden">
			<div class="desc headline" >
			    <h2 style="text-align: center; font-weight: normal; color: rgb(51, 51, 51); letter-spacing: 0px;margin-bottom:11px">Employers choose  resume and contact</h2>
				<h6 style="text-align: center; color: rgb(51, 51, 51); font-weight: normal; font-size: 16px;">Take a look  at resumes to choose the most suitable resume with your company,and you will have the information to contact.</h6>
				<h6 style="text-align:center;margin-top:12px"><img style="max-width:100%" src="<?php bloginfo('stylesheet_directory');?>/img/search-comp.png"></h6>
			</div>
	
		</div>
	</div>
	<?php if(current_user_can( 'manage_options' )) { ?>	
	<div id="archive_resumes" class="main-center clearfix padding-top30">

		<div class="main-column resume-page">
		<!-- Pending Resumes -->
			<?php
				$pending_resume	=	JE_Resume::query_resumes( array('post_status' => array('pending'), 'showposts' => -1));

				if(!empty($pending_resume)) { ?>
				<h3 class="main-title"><?php _e("PENDING RESUMES", ET_DOMAIN); ?></h3>
				<div id="pending_resumes">
				<ul class="list-jobs job-account-list pending-resumes">
					<?php
					$pending_data	=	array();
					foreach ($pending_resume as $key => $res) {
						$resume 						= 	JE_Resume::convert_from_post($res);
						$jobseeker 						= 	get_userdata($res->post_author);
						$jobseeker 						=	JE_Job_Seeker::convert_from_user($jobseeker);
						if(!$jobseeker)	continue;
						$resume->author					=	($jobseeker->display_name ? $jobseeker->display_name : $jobseeker->user_login);
						$pending_data[]					=	$resume;
					?>
						<li id= "<?php echo strtotime($res->post_date);?>">
							<div class="thumb">
								<a class="resume-title" href="<?php echo get_permalink($res->ID); ?>" title="<?php echo ($jobseeker->display_name ? $jobseeker->display_name : $jobseeker->user_login );  ?>">
									<?php echo et_get_resume_avatar($resume->post_author, 28); ?>
								</a>
							</div>
							<div class="content">
								<h6 class="title">
									<a href="<?php echo get_permalink($res->ID); ?>" class="title resume-title" title="<?php echo ($jobseeker->display_name ? $jobseeker->display_name : $jobseeker->user_login );  ?> ">
										<?php echo ($jobseeker->display_name ? $jobseeker->display_name : $jobseeker->user_login );  ?>
									</a>
								 	<a href="#" class="professtional"><?php echo $jobseeker->et_profession_title; ?></a>
								</h6>
								<div class="desc f-left-all">
									<div>
										<span class="icon" data-icon="@"></span>
										<?php echo !empty($resume->et_location) ? $resume->et_location : __('No location', ET_DOMAIN); ?>
									</div>
									<div class="link-website">
										<span class="icon" data-icon="G"></span>
										<?php
										if(empty($resume->et_url)){
											echo'<span>';
											_e('No link specified', ET_DOMAIN);
											echo'</span>';
										} else {?>
											<a rel="nofollow" href="<?php echo $resume->et_url;?>" target="_blank" rel="nofollow" class="website"><?php echo $resume->et_url  ?></a>

										<?php }?>
									</div>
								</div>
								<div class="tech f-right actions">
									<a class="color-active action-approve tooltip" title="<?php  _e("Approve", ET_DOMAIN); ?>" href="#"><span class="icon" data-icon="3"></span></a>
									<a class="color-pending action-reject tooltip" title="<?php _e("Reject", ET_DOMAIN); ?>" href="#"><span class="icon" data-icon="*"></span></a>
								</div>
							</div>
						</li>

					<?php } ?>

				</ul>
				</div>
					<script	type="application/json" id="pending_resume_data">
						<?php echo json_encode($pending_data); ?>
					</script>
				<?php

				}
				wp_reset_query();
			?>

					<h3 class="main-title"><?php _e("LATEST RESUMES", ET_DOMAIN); ?></h3>

					<div id="resumes">
					<ul class="list-jobs job-account-list list-job-resume ">
						<?php
						global $post, $wp_query;
						$latest_resume 	=	array();
						if (have_posts()){

						while(have_posts()){
							the_post();
							$resume 			= JE_Resume::convert_from_post($post);
							$jobseeker 			= get_userdata($post->post_author);
							// print_r ($jobseeker);
							$jobseeker 			= JE_Job_Seeker::convert_from_user($jobseeker);
							if(!$jobseeker) continue;
							$resume->author		= ($jobseeker->display_name ? $jobseeker->display_name : $jobseeker->user_login )	;
							$latest_resume[]	= $resume;
							$date =  $post->post_date;
							?>
						<li class="resume-item" id ="<?php echo strtotime($date);?>" >

							<div class="thumb">
								<a class="resume-title" href="<?php the_permalink() ?>" title="<?php echo ($jobseeker->display_name ? $jobseeker->display_name : $jobseeker->user_login );  ?>">
									<?php echo et_get_resume_avatar($resume->post_author, 28); ?>
								</a>
							</div>
							<div class="content">
								<h6 class="title">
									<a href="<?php the_permalink() ?>" class="title resume-title" title="<?php echo ($jobseeker->display_name ? $jobseeker->display_name : $jobseeker->user_login );  ?> ">
										<?php echo ($jobseeker->display_name ? $jobseeker->display_name : $jobseeker->user_login );  ?>
									</a>
								 	<a href="#" class="professtional"><?php echo $jobseeker->et_profession_title ?></a>
								</h6>
								<div class="desc f-left-all">
									<div>
										<span class="icon" data-icon="@"></span>
										<?php echo !empty($jobseeker->et_location) ? $jobseeker->et_location : __('No location', ET_DOMAIN); ?>
									</div>
									<div class="link-website">
										<span class="icon" data-icon="G"></span>
										<?php 
										if(empty($resume->et_url)){
											echo'<span>';
											_e('No link specified', ET_DOMAIN);
											echo'</span>';
										} else {?>
											<a rel="nofollow" href="<?php echo $resume->et_url;?>" target="_blank" rel="nofollow" class="website"><?php echo $resume->et_url  ?></a>

										<?php }?>

									</div>
								</div>
								<?php /* if(current_user_can('manage_options')) { ?>
								<div class="tech f-right actions">
									<a class="color-pending action-trash tooltip" title="<?php _e("Reject", ET_DOMAIN); ?>" href="#"><span class="icon" data-icon="*"></span></a>
								</div>
								<?php } */ ?>
							</div>
						</li>

						<?php } 
					} else {
						?>
						<li class="no-job-found"><?php _e("", ET_DOMAIN); ?></li>
						<?php
					}// end while have posts ?>

					</ul>
					</div>


				<?php if ($wp_query->max_num_pages > 1){ ?>
				<div class="button-more">
			 		 <button class="btn-background border-radius"><?php _e('Load more resumes', ET_DOMAIN); ?></button>
				</div>

				<?php } 

				?>
				<script	type="application/json" id="latest_resume_data">
					<?php echo json_encode($latest_resume); ?>
				</script>
			</div>

			<div class="widget-area second-column">
				<div id="sidebar-resume" class="widget <?php if(current_user_can('manage_options') ) echo 'sortable ui-sortable'; ?>">
					<?php
						if(is_active_sidebar('sidebar-resume')) {
							dynamic_sidebar('sidebar-resume');
						}
					?>
				</div>

			</div>

	  	</div>
		<?php } ?>	
		

	</div>
</div>

<?php get_footer(); ?>