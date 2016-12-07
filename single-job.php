<?php

global $et_global, $post, $user_ID;

$imgUrl	=	$et_global['imgUrl'];
$jsUrl	=	$et_global['jsUrl'];
$job      = $post;
get_header ();
?>
<div class="wrapper content-container" id="single-job">
<style type="text/css">
	.plupload	 {
		width: 200px !important;
		height: 100px !important;
	}
</style>
<?php
if(have_posts()) { the_post ();
	$et_revision_id = get_post_meta($post->ID,'et_revision_id', true);
	if($user_ID == (int)$post->post_author && $et_revision_id){
		$revision = get_post($et_revision_id);
		if($revision){
			$post = $revision;
			setup_postdata( $post );
		}

	}


	$job_data	=	et_create_jobs_response($post);

	$job_cats  	= $job_data['categories'];
	// get all job types
	$job_types		=	$job_data['job_types'];
	$job_location	= 	$job_data['location'];
	$job_full_location 	= $job_data['full_location'];

	$company		= et_create_companies_response( $post->post_author );
	$company_logo	= $company['user_logo'];

	$expire = $job_data['expired_date'];

	if(current_user_can('edit_others_posts')) {
	?>
		<div class="heading-message message" <?php if ($post->post_status ==  'publish' ) { echo 'style ="display:none;"' ; }?>>
			<div class="main-center">
				<div class="text">
				<?php
				$statuses	= array(
					'draft'		=> __('NOT READY',ET_DOMAIN),
					'pending'	=> __('PENDING',ET_DOMAIN),
					'archive'	=> __('ARCHIVED',ET_DOMAIN),
					'reject'	=> __('REJECTED',ET_DOMAIN),
					'publish'	=> __('ACTIVE', ET_DOMAIN)
				);
				if($post->post_status == 'pending')
					_e("THIS JOB IS PENDING. YOU CAN APPROVE OR REJECT IT.",ET_DOMAIN);
				else
					printf(__("THIS JOB IS %s.",ET_DOMAIN), $statuses[$post->post_status]);
				?>
				</div>
				<div class="arrow"></div>
			</div>
		</div>
		<?php }?>

		<div class="heading">
			<div class="main-center">
				<?php if(current_user_can('edit_others_posts') || $user_ID == $job->post_author) {   ?>
				<div class="technical font-quicksand f-right job-controls">

					<!-- admin action -->
					<?php if (current_user_can ('edit_others_posts') ) {?>
					<div class="f-right" id="adminAction" <?php if ($post->post_status == 'publish' ) { echo 'style ="display:none;"' ; }?> >
						<a href="#" class="color-active" id="approveJob">
							<span data-icon="3" class="icon"></span>
							<?php _e("APPROVE",ET_DOMAIN);?>
						</a>
						<a rel="modal-box" href="#modal_reject_job" class="color-pending">
							<span data-icon="*" class="icon"></span><?php _e("REJECT",ET_DOMAIN);?>
						</a>
					</div>
					<?php } ?>
					<!-- admin action -->

					<a rel="modal-box" href="#modal_edit_job" class="color-edit">
						<span data-icon="p" class="icon"></span><?php _e("EDIT THIS JOB",ET_DOMAIN);?>
					</a>
				</div>
				<?php } ?>

				<h1 data="<?php echo $job->ID;?>" class="title job-title" id="job_title" style="color:#F0111B;font-weight:normal;font-size:25px"><?php the_title()?>
					<?php if($job_data['post_views'] > 0) { ?>
					<span class="vcount">(<?php if($job_data['post_views'] == 1) _e("1 view", ET_DOMAIN); else printf(__("%d views", ET_DOMAIN), $job_data['post_views'])  ?>)</span>
					<?php } ?>
				</h1>
			</div>
		</div>

		<div class="heading-info clearfix mapoff">
			<div class="main-center">
				<div class="info f-left f-left-all">
					<div class="company job-info"  itemtype="http://schema.org/JobPosting" itemscope="">
								<div class="thumb_logo">
						<?php
						$rf= get_the_author_meta('roles',$post->post_author);
						if($rf[0]=="company")
						{
							if (!empty($company_logo)){
								?>

									<a id="job_author_thumb" class="thumb job_author_link" href="<?php echo $company['post_url'] ?>" 
										title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>">
										<img style="width:auto;height:34px" src="<?php echo $company_logo['thumbnail'][0]; ?>" id="company_logo_thumb" data="<?php echo (isset($company_logo['attach_id'])) ? $company_logo['attach_id'] : '';?>" />
									</a>
								
								<?php
							}
						}
						else
						{			
							global $wpdb;
							$rows=$wpdb->get_results( "SELECT company_editor_id FROM wp_posts where ID ='".$post->ID."' " );
							$rows_company_editor=$rows[0]->company_editor_id;
							$rows=$wpdb->get_results( "SELECT ID,logo,display_name FROM wp_post_company where ID ='".$rows_company_editor."' " );$dc1=$rows[0]->ID;
							$rows_logo=$rows[0]->logo;$rows_logo=unserialize($rows_logo);$rows_logo=$rows_logo['company-logo'][0];
						?>
									<a id="job_author_thumb" class="thumb job_author_link" href="<?php echo $company['post_url'].'?com_i='.$dc1;unset($dc1);?>" 
										title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>">
										<img style="width:auto;height:34px" src="<?php echo $rows_logo;?>" id="company_logo_thumb" data="<?php echo (isset($company_logo['attach_id'])) ? $company_logo['attach_id'] : '';?>" />
									</a>
						<?php
						}
					    ?>
                                </div>
						<!-- Job author, type, location, posted date -->
						<div class="company-name">
						<?php 
							if($rf[0]=="company")
							{
						?>						
							<a  href="<?php echo get_author_posts_url($company['ID'])?>" data="<?php echo $company['ID'];?>"
								title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" class="name job_author_link" id="job_author_name">
							  <?php echo $company['display_name']?>
							</a>
						<?php 
							}
							else
							{
					//	$rows=$wpdb->get_results( "SELECT ID,display_name FROM wp_post_company where ID ='".$rows_company_editor."' " );
						//$rows=$rows[0]->display_name;
						?>
							<a  href="<?php echo get_author_posts_url($company['ID']).'?com_i='.$rows[0]->ID;?>" data="<?php echo $company['ID'];?>"
								title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" class="name job_author_link" id="job_author_name">
							<?php echo $rows[0]->display_name; ?>
							</a>
						<?php					
							}			
						?>
						</div>

						<!-- job type -->
						<div id="job_type" class="job-type">
							<?php if( !empty($job_types) ) {
								foreach($job_types as $job_type){
								?>
								<input class="job-type-slug" type="hidden" value="<?php echo $job_type['slug']; ?>"/>
								<a class="<?php echo 'color-' . $job_type['color']; ?>" href="<?php echo $job_type['url'] ?>" title="<?php printf(__('View posted jobs in %s ', ET_DOMAIN), $job_type['name']) ?>">
									<span class="flag"></span>
									<?php echo $job_type['name'] ?>
								</a>
								<?php 
									break;
								}
							}?>
						</div>
						<!-- end job type -->

						<?php if($job_location != '') { ?>
							<span class="icon location" data-icon="@"></span>
							<?php
							$tooltip 	= '';
							if($job_location != __('Anywhere', ET_DOMAIN) && $job_data['location_lat'] != '' && $job_data['location_lng'] != '') {
								$tooltip = __('View map', ET_DOMAIN);
							}
							?>
							<div title="<?php echo $tooltip ?>" class="job-location" id="job_location">
								<span itemprop="jobLocation" itemscope itemtype="http://schema.org/Place" class="ob-location">
			         				<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
			         				<span itemprop="addressLocality"><?php echo $job_location;echo "v"; ?></span>
			          				</span>
			        			</span>

							</div>							
							<div title="<?php echo $tooltip ?>" class="job-location" id="job_location">
								<span itemprop="jobLocation" itemscope itemtype="http://schema.org/Place" class="ob-location" style="color:#F0111B">
			         	        (View Map)
			        			</span>

							</div>
							<input type="hidden" name="jobFullLocation" value="<?php echo $job_full_location ?>" >
							<input type="hidden" name="jobLocLat" value="<?php echo $job_data['location_lat'] ?>" >
							<input type="hidden" name="jobLocLng" value="<?php echo $job_data['location_lng'] ?>" >
						<?php } ?>

						<span class="icon date" data-icon="\"></span>
						<div class="date">
							<span itemprop="datePosted "><?php the_date () ?> </span>
						</div>
					</div>

				</div>

				<!-- social share -->
				<?php get_template_part( 'template/single' , 'social' ); ?>
				<!-- end social share -->

				<div class="clear"></div>

				<!-- job map -->
				<div id="jmap" class="<?php if ($job_location == __('Anywhere', ET_DOMAIN)) echo 'anywhere '; ?>heading-map hide">
				</div>
			</div>
		</div>

		<div class="main-center padding-top16">

			<div class="main-column">
				<div class="job-detail tinymce-style" style="font-size: 16px;">
					<?php do_action( 'je_before_job_description', $job);?>
					<div class="description" id="job_description" itemprop="description"style="background-color:white;box-shadow:none;margin-top:14px;padding-top:0px">
						<div style="padding-bottom:12px">
							<?php
							$posttags = get_the_tags();
							if ($posttags) 
							{
								foreach($posttags as $tag)
								{
							?>					
									<a target="_blank" href="<?php bloginfo('url');echo '/?s='.$tag->name ;?> " class="tag_smartjob_home" ><?php echo $tag->name . ' ';  ?></a>					
							<?php
								}
							}
							?>
						</div>
					<?php
						/**
						 * job description
						*/
						the_content();
					?>
					</div>
					<?php
					// action for plugin job fields
					do_action( 'je_single_job_fields', $job);

					do_action( 'je_after_job_description', $job);
					?>
				</div>
				<?php
				/**
				 *  apply template
				*/
				//include(locate_template('template-apply.php'));
				get_template_part('template-apply');
				?>	
				<div id="latest_jobs_container" style="background-color: white; margin-top: 19px;" >
					<ul class="list-jobs lastest-jobs job-account-list">
					<li class="" itemscope="" style="border-bottom:1px solid white" >
						<div class="">					
							<h2 class="title-job" itemprop="title">
								More Jobs for You
							</h2>						
						</div>
					</li>
					<?php
					$posttags = get_the_tags();
					if ($posttags) 
					{
						$dem=1;
						foreach($posttags as $tag)
						{
							$args = array(
								'orderby'        => 'date',
								'order'=> 'DESC',
								'posts_per_page' => '5',
								'post_type' => 'job',
								'tag_id' => $tag->term_id,
								'post__not_in'=>array($post->ID),
							);
							$the_query = new WP_Query( $args );
							if ( $the_query->have_posts() ) : while ( $the_query->have_posts() ) : $the_query->the_post(); 
							$rf= get_the_author_meta('roles',$post->post_author);
					?>	
						<li itemtype="http://schema.org/JobPosting" itemscope="" class="job-item" style="border-bottom:1px solid white">
							<div class="thumb" style="height:auto">
							<?php  	
							if($rf[0]=="company")
							{
								$rr=get_the_author_meta('et_user_logo',$post->post_author);
								
						    ?>
								<a target="_blank" class="thumb" title="View posted jobs by companry" href="<?php echo get_author_posts_url( $post->post_author );?>" data-id="12" id="job_author_thumb-12">
								    <img data-attachid="555" id="company_logo_thumb-555" src="<?php print_r($rr[thumbnail][0]);?>">
								</a>
							<?php 
							}
							else
							{
							global $wpdb;
							$rows=$wpdb->get_results( "SELECT company_editor_id FROM wp_posts where ID ='".$post->ID."' " );
							$rows=$rows[0]->company_editor_id;	
							$rows=$wpdb->get_results( "SELECT ID,display_name,logo FROM wp_post_company where ID ='".$rows."' " );
							$rows_logo=$rows[0]->logo;$rows_logo=unserialize($rows_logo);$rows_logo=$rows_logo['company-logo'][0];
							?>
								<a target="_blank" class="thumb" title="View posted jobs by " href="<?php echo get_author_posts_url( $post->post_author ).'/?com_i='.$rows[0]->ID;?>" data-id="12" id="job_author_thumb-12">
								    <img data-attachid="555" id="company_logo_thumb-555" src="<?php echo $rows_logo;?>">
								</a>
							<?php 
							}
							?>
							</div>
							<div class="content">
					
								<h2 itemprop="title" class="title-job">
									<a target="_blank" title="View more details of <?php the_title(); ?>" href="<?php the_permalink();?>" class="title-link title">
									<?php the_title(); ?>	</a>
								</h2>						
								<a title="View more details in new window tab" href="http://smartjob.vn/job/tuyen-02-nhan-vien-oracle-database/" class="title-link title new-tab-icon" target="_blank">
								    <span data-icon="R" class="icon"></span>
								</a>
							    <div class="desc f-left-all">
									<div class="cat company_name c">
									<?php 
									if($rf[0]=="company")
									{
									?>
										<a target="_blank" title="View posted jobs by <?php the_title(); ?>" href="<?php echo get_author_posts_url( $post->post_author );?>" data-id="12">
										<?php echo get_the_author();?></a>
									<?php 
									}
									else
									{
									?>
										<a target="_blank" title="View posted jobs by <?php the_title(); ?>" href="<?php echo get_author_posts_url( $post->post_author ).'/?com_i='.$rows[0]->ID;?>" data-id="12">
										<?php echo $rows[0]->display_name;?></a>
									<?php
									}
									?>
									</div>
									<div class="job-type color-26" itemprop="employmentType">
										 <span class="flag"></span>
										<a title="View all posted jobs in Fulltime" href="#">
											<?php
                                                $fulltime=get_the_terms( $post->ID, 'job_type' );echo $fulltime[0]->name;
											?>					</a>
									</div>		
									<div>
										<span class="icon" data-icon="@"></span>
										<span itemprop="jobLocation" itemscope itemtype="http://schema.org/Place" class="ob-location">
											<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
											<span itemprop="addressLocality">
											<?php
												$key_1_value = get_post_meta( get_the_ID(), 'et_full_location', true );
												if ( ! empty( $key_1_value ) ) {echo $key_1_value;}
											?></span>
											</span>
										</span>

									</div>		
									<div>
										<span class="ob-location" itemtype="http://schema.org/Place" itemscope="" itemprop="jobLocation">
											<?php 
											$fields = JEP_Field::get_all_fields();
											foreach ($fields as $field) {
											$value = get_post_meta( get_the_ID(), 'cfield-'. $field->ID, true );
											?>
											<span itemprop="addressLocality"><?php echo $field->name;?>:</span>
											<span itemprop="addressLocality" style="color:#F0111B"><?php echo $value;?> </span>
											<?php
											}
											?>
										</span>
									</div>
							    </div>
								<div class="decription_smartjob">
								<?php echo get_excerpt(250);?>                                     
								</div>
								<div class="decription_smartjob">
									<?php
									$posttags = get_the_tags();
									if ($posttags) 
									{
										foreach($posttags as $tag) 
										{
									?>
									<a target="_blank" href="<?php bloginfo('url');echo '/?s='.$tag->name ;?> " class="tag_smartjob_home" ><?php echo $tag->name . ' ';  ?></a>
									<?php
							         	}
									}
									?>						
								</div>
							</div>
						</li>
																		
					<?php
						   $dem++;
						   endwhile; 
						   wp_reset_postdata();
						   else : 
						   //_e( 'Sorry, no posts matched your criteria.' );
						   endif;
						   if($dem>4)break;
						}
					}
					?>
					</ul>
			    </div>				
			</div>
			<?php get_sidebar() ?>
			<div class="clearfix"></div>			

			<!-- inject job data here for bootstrapping model -->
			<script type="application/json" id="job_data">
				<?php echo json_encode( $job_data ); ?>
			</script>
			<script type="application/json" id="company_data">
				<?php echo json_encode( $company ); ?>
			</script>

			<script type="text/template" id="apply_button">
				<button title="<?php _e('Apply for this job',ET_DOMAIN); ?>" class="bg-btn-action border-radius btn-default btn-apply applyJob" id="apply2">
					<?php _e("APPLY FOR THIS JOB",ET_DOMAIN); ?>
					<span class="icon" data-icon="R"></span>
				</button>
			</script>
			<script type="text/template" id="how_to_apply_button">
				<button title="<?php _e('HOW TO APPLY', ET_DOMAIN); ?>" class="bg-btn-action border-radius btn-default btn-apply applyJob" id="apply3">
					<?php _e("HOW TO APPLY",ET_DOMAIN); ?>
					<span class="icon" data-icon="O"></span>
				</button>
			</script>
			<script type="text/template" id="apply_detail">
				<h5><?php _e("HOW TO APPLY FOR THIS JOB", ET_DOMAIN); ?></h5>
				<div class="description">{{applicant_detail}}</div>
				<a href="#" class="back-step icon" data-icon="D"></a>
			</script>
		</div>
<?php }?>
</div>

<?php get_footer(); ?>