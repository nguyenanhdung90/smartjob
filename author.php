<?php get_header(); ?>

<div class="wrapper clearfix content-container">
	<?php
	global $et_global;
	$company_id		= get_query_var('author');
	$company		= et_create_companies_response( $company_id );
	$company_logo	= $company['user_logo'];global $wpdb;
	?>
	<div class="heading">
		<div class="main-center">
		<?php
		if(isset($_GET["com_i"]))
		{
			//$rows=$wpdb->get_results( "SELECT logo,decription,display_name FROM wp_post_company where ID ='".$_GET["com_i"]."' " );
			$rows=$wpdb->get_results( "SELECT decription,display_name,user_email,user_url,logo FROM wp_post_company where ID ='".$_GET["com_i"]."' " );
			$rowimage=$rows[0]->logo;$rowimage=unserialize($rowimage);$rowimage=$rowimage['company-logo'][0];
		?>
		<h1 class="main-column uppercase"><?php  echo $rows[0]->display_name;?></h1>
		<?php 
		}
		else
		{
		?>
		<h1 class="main-column uppercase"><?php printf( __('%s', ET_DOMAIN), $company['display_name'] ) ?></h1>
		<?php
		}
		?>
			
		</div>
	</div>
	<div class="account-title">
		<div class="main-center clearfix">
			<?php $count = et_get_job_count(array('post_author' => $company_id)); ?>
			<div class="main-column job-status">
				<?php
				printf( et_number( __("%d active job by %s", ET_DOMAIN), __("%d active job by %s", ET_DOMAIN), __("%d active jobs by %s", ET_DOMAIN), $count['publish'] ), $count['publish'], $company['display_name'] ); 
				?>
			</div>
		</div>
	</div>

	<div class="main-center clearfix">

		<div class="main-column main-left" id="job_list_container">
		<?php if(isset($_GET["com_i"]))
		{//truong hop la do editor chinh  sua va them
		//$rows=$wpdb->get_results( "SELECT decription FROM wp_post_company where ID ='".$_GET["com_i"]."' " );
		?>
		    <ul class="list-jobs" style="padding-top:14px;padding-bottom:14px">
                <?php echo $rows[0]->decription; ?>
			</ul>
			 <ul class="list-jobs" style="padding-top:14px;padding-bottom:14px">
			 <span style="font-size:26px">Công việc liên quan:</span>
			 </ul>
			<ul class="list-jobs">
			<?php 
			$author= get_the_author_id();
			define("POST_PER_PAGE_AUTHOR",20);
			$wpdb->get_results( "SELECT ID FROM wp_posts where post_type ='job'  and post_author='".$author."' and post_status='publish' and company_editor_id='".$_GET["com_i"]."' " );
			$de= $wpdb->num_rows ;$dee=$de/POST_PER_PAGE_AUTHOR;$_SESSION['coun_page']=(int)$dee+1; unset($dee);if($_GET["page"]=="")$count_page=1;else $count_page=$_GET["page"];
			$start=POST_PER_PAGE_AUTHOR*($count_page-1);$end=POST_PER_PAGE_AUTHOR*$count_page;
			$string_query="SELECT * FROM wp_posts  where post_type ='job'  and post_author='".$author."' and post_status='publish' and company_editor_id='".$_GET["com_i"]."' ORDER BY post_date DESC   LIMIT ".$end." OFFSET ".$start."  ";
			$fivesdrafts = $wpdb->get_results($string_query);
			foreach ( $fivesdrafts as $fivesdraft ) 
			{		
			?>		
			
				<li class="job-item" style="display: block;">
					<div class="thumb">
						<a href="" class="thumb" title="View jobs posted by" id="job_author_name">
							 <img src="<?php echo $rowimage;?>">
						</a>
					</div>
					<div class="content null">
						<a target="_blank" href="<?php bloginfo('url');?>/job/<?php echo $fivesdraft->post_name; ?>" class="title-link title"> <?php echo $fivesdraft->post_title; ?></a>
						<a href="<?php bloginfo('url');?>/job/<?php echo $fivesdraft->post_name; ?>" class="title-link title" target="_blank"><span data-icon="R" class="icon"></span></a>
						<div class="tech font-quicksand f-right actions">
						</div>
						<div class="desc f-left-all">
							<div class="cat company_name c">
								<a target="_blank" href="http://smartjob.vn/company/seo02//?com_i=83"><?php  echo $rows[0]->display_name;?></a>
							</div>
							<div class="job-type color-26 ">
								<span class="flag"></span>
						<?php
						$string_que="SELECT term_id FROM wp_term_taxonomy WHERE wp_term_taxonomy.term_id in(SELECT term_taxonomy_id FROM wp_term_relationships WHERE wp_term_relationships.object_id='".$fivesdraft->ID."' ) and taxonomy ='job_type' ";
						$tags_=$wpdb->get_results($string_que);
						foreach ( $tags_ as $tag_ ) 
		             	{	
						   $tags_name=$wpdb->get_results( "SELECT name FROM wp_terms where term_id ='".$tag_->term_id."'  " );
						   foreach ( $tags_name as $tag_name )
						   {
							   ?>
								<a target="_blank" rel="tag" href="<?php bloginfo('url');?>/job-type/<?php echo  $tag_name->name;?>"><?php   echo  $tag_name->name;?></a>
			    		<?php
						   }
						}
						?>		
							</div>
							<div><span data-icon="@" class="icon"></span>
							<span class="job-location">
							<?php 
								$diadiem=$wpdb->get_results( "SELECT meta_value FROM wp_postmeta where post_id ='".$fivesdraft->ID."'  and meta_key='et_full_location'  " ); 
								echo $diadiem[0]->meta_value;
							?>
							</span></div>
							<div><span itemprop="jobLocation" itemscope="" itemtype="" class="ob-location"><span class="icon">Salary:</span>
							<span style="color:#ce534d" class="job-location">
							<?php 
							$diadiem=$wpdb->get_results( "SELECT meta_value FROM wp_postmeta where post_id ='".$fivesdraft->ID."'  and meta_key='cfield-592'  " ); 
							echo $diadiem[0]->meta_value;
							?>							
							</span>
							</span></div>
						</div>
						<div class="decription_smartjob">
						<?php $bien=  mb_substr($fivesdraft->post_content,0,300);$bien=strip_tags($bien);echo $bien;?> 
						<a href="<?php bloginfo('url');?>/job/<?php echo $fivesdraft->post_name; ?>" target="_blank" >more</a> 
						</div>
						<div class="decription_smartjob">
						<?php
						$string_que="SELECT term_id FROM wp_term_taxonomy WHERE wp_term_taxonomy.term_id in(SELECT term_taxonomy_id FROM wp_term_relationships WHERE wp_term_relationships.object_id='".$fivesdraft->ID."' ) and taxonomy ='post_tag' ";
						$tags_=$wpdb->get_results($string_que);
						foreach ( $tags_ as $tag_ ) 
		             	{	
						   $tags_name=$wpdb->get_results( "SELECT name FROM wp_terms where term_id ='".$tag_->term_id."'  " );
						   foreach ( $tags_name as $tag_name )
						   {
							   ?>
							   <a class="tag_smartjob_home" href="<?php bloginfo('url');?>/?s=<?php echo $tag_name->name;?> " target="_blank"> <?php   echo  $tag_name->name;?></a>							 
							  <?php
						   }
						}
						?>
							
						</div>		
					</div>
				</li>
				<?php 
				}
				?>
				<li class="job-item" style="display: block;">
				<?php 
				if($_SESSION['coun_page']>1)
				{
					for ($x = 1; $x <= $_SESSION['coun_page']; $x++) 
					{
				?>
				    <a href="<?php echo '?com_i='.$_GET["com_i"].'&page='.$x; ?>"><?php echo $x;?></a>
				<?php 
				    }
				}
				?>
				</li>
			</ul>
			
		<?php 
		}
		else
		{// truong hop la cong ty company
		$rows=$wpdb->get_results( "SELECT decription FROM wp_post_company where users_id ='".$company_id."' " );
		?>
			<ul class="list-jobs" style="padding-top:14px;padding-bottom:14px">
			<?php echo $rows[0]->decription; ?>
			</ul>
			<ul class="list-jobs" style="padding-top:14px;padding-bottom:14px">
			 <span style="font-size:26px">Công việc liên quan:</span>
			 </ul>
			<?php
			wp_reset_query();
			global $wp_query, $disable_actions;
			$disable_actions = true;
			$job_list = array();
			?>
			<ul class="list-jobs">
				<?php
				if ( have_posts() ) :
					while (have_posts()) : the_post();
						global $job;
						$job		= et_create_jobs_response($post);
						$latest_jobs[]	= $job;

						$template_job	= apply_filters( 'et_template_job', '');
						if( $template_job != '' )
						{load_template( $template_job , false);}
						else {
							get_template_part( 'template' , 'joba' );
						}
					endwhile; // end while have_posts()
				endif; // end if have_posts ?>
			</ul>
			<?php if ( $wp_query->max_num_pages > 1 ) {?>
			<div class="button-more">
				<button class="btn-background border-radius"><?php _e('Load More Jobs', ET_DOMAIN );?></button>
			</div>
			<?php } ?>
		<?php 
		}
		?>

		</div>

		<?php 
		if(isset($_GET["com_i"]))
		{
			
			
		?>
		<div id="sidebar-company" class="second-column widget-area sortable ui-sortable">
			<aside class="widget company-profile bg-grey-widget margin-top15 %2$s" id="%1$s">			
				<div class="thumbs">					
					<a class="thumb" title="View posted jobs by  <?php echo $rows[0]->display_name;?>" href="" id="job_author_thumb">
						<img  id="company_logo_thumb" src="<?php echo $rowimage;?>">
					</a>
				</div>
				<div class="title company_name">
					<a title="View jobs posted <?php echo $rows[0]->display_name;?> " href="" class="name job_author_link" id="job_author_name">
					  <?php echo $rows[0]->display_name;?>				
					</a>
				</div>
				<div class="info icon-default">				
					<a href=" <?php echo $rows[0]->user_url;?>	" rel="nofollow" target="_blank" id="job_author_url"> <?php echo $rows[0]->user_url;?></a>	
					
				</div>	
                <?php if($rows[0]->user_email!=""){?>				
				<div class="info icon-default">				
					<a href=" #" rel="nofollow" target="_blank" id="job_author_url"> <?php echo $rows[0]->user_email;?></a>						
				</div>
                <?php }?>
			</aside>		
		</div>
        <?php		
		}
		else
		{
		get_sidebar();
		}	
		?>

	</div>

	<script type="application/json" id="jobs_list_data">
		<?php echo json_encode($job_list);?>
	</script>

	<script type="application/json" id="author_data">
		<?php
			echo json_encode(array(
				'display_name'	=> $company['display_name'],
				'user_url'		=> $company['user_url'],
				'user_logo'		=> $company_logo
			)
		);
		?>
	</script>

</div>

<?php get_footer(); ?>