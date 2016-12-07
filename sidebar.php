<?php 
if ( is_home() || is_search() || is_post_type_archive('job') || is_tax('job_category') || is_tax('job_type') || apply_filters( 'je_need_index_sidebar', false ) ){ 
// index sidebar
?>
	<div class="widget-area second-column">

		<?php if(current_user_can ('edit_others_posts')) { ?>

			<aside class="widget widget-select widget_archive content-dot" id="archives-2">
				<h3 class="widget-title"><?php _e('JOB STATUS', ET_DOMAIN) ?></h3>
				<?php $query_var = get_query_var('post_status'); 
				if ( !is_array($query_var) ){
					$query_var  = explode(',', $query_var);
				}
				?>
				<ul class="category-lists filter-jobstatus filter-joblist" id="status_filter">
					<?php $post_count = wp_count_posts('job'); ?>
					<li class="status-item status-reject" id="filter_reject"><a class="<?php if ( in_array('reject', $query_var) ) echo " active "; ?>" data="reject" href="#"><?php _e('Rejected Jobs', ET_DOMAIN) ?><span class="count"><?php echo $post_count->reject ?></span></a></li>
					<li class="status-item status-archive" id="filter_archive"><a class="<?php if ( in_array('archive', $query_var) ) echo " active "; ?>" data="archive" href="#"><?php _e('Archived Jobs', ET_DOMAIN) ?><span class="count"><?php echo $post_count->archive ?></span></a></li>
				</ul>
			</aside>  
		<?php }?>

		<div id="sidebar-main" class="widget <?php if(current_user_can('manage_options') ) echo 'sortable' ?>">
			<?php	
				if( is_active_sidebar ('sidebar-main')) {
					dynamic_sidebar ('sidebar-main');
				} else {
					if(current_user_can('manage_options')) _e("This sidebar is not active. Please go to the Widgets setting and add the widget to your sidebar.", ET_DOMAIN);
				}
			?>
			
		</div>

	</div>
<?php }

if( is_singular('job') ) 
{  // single job sidebar
?>
	<div class="second-column widget-area <?php if(current_user_can('manage_options') ) echo 'sortable' ?>" id="sidebar-job-detail">
				
	<?php 
		if(is_active_sidebar('sidebar-job-detail')) { 
			dynamic_sidebar('sidebar-job-detail');
		}  
		else 
		{
			//JE_Company_Profile ();
	?>
    <?php 
	$rf= get_the_author_meta('roles',$post->post_author);
    if($rf[0]=="editor"||$rf[0]=="administrator")
	{
		global $wpdb;
		$rows=$wpdb->get_results( "SELECT company_editor_id FROM wp_posts where ID ='".$post->ID."' " );
		$rows=$rows[0]->company_editor_id;$id_wp_post=$rows;
		$rows=$wpdb->get_results( "SELECT user_email,user_url,logo,display_name,decription FROM wp_post_company where ID ='".$rows."' " );$name_compnay_editor=$rows[0]->display_name;$web_url_compnany=$rows[0]->user_url;$mail_company=$rows[0]->user_email;$decript_com=$rows[0]->decription;
		$rows=$rows[0]->logo;$rows=unserialize($rows);$rows=$rows['company-logo'][0];
		$company		= et_create_companies_response( $post->post_author );
    ?>
			<div id="sidebar-job-detail" class="second-column widget-area sortable ui-sortable">			
				<aside class="widget company-profile bg-grey-widget margin-top15 %2$s" id="%1$s">			
					<div class="thumbs">					
						<a target="_blank" class="thumb" title="View posted jobs by <?php echo $name_compnay_editor;?>" href="<?php echo $company['post_url'] ?>/?com_i=<?php echo $id_wp_post;?>" id="job_author_thumb">
							<img data="929" id="company_logo_thumb" src="<?php echo $rows;?>">
						</a>
					</div>
					<div class="title company_name">
						<a target="_blank" title="View jobs posted by <?php echo $name_compnay_editor;	?>" href="<?php echo $company['post_url'] ?>/?com_i=<?php echo $id_wp_post;unset($id_wp_post);?>" class="name job_author_link" id="job_author_name" style="font-weight: normal; font-size: 17px;">
						  <?php echo $name_compnay_editor;	?>				</a>
					</div>					
					<div class="title company_name">
						<a title="View jobs posted by <?php echo $mail_company;	?>" href="<?php bloginfo('url');?>" class="name job_author_link" id="job_author_name" style="font-weight:normal">
						 <?php echo $mail_company;	?></a>
					</div>
					<div class="info icon-default">			
							<a href="<?php echo $web_url_compnany; ?>" rel="nofollow" target="_blank" id="job_author_url" style="font-weight:normal"><?php echo $web_url_compnany; ?></a>							
					</div>				
					<div class="info icon-default" style="text-align: justify; font-size: 16px;">			
							<?php echo $decript_com;?>							
					</div>				
				</aside>				
			</div>
	<?php 
	}
	else
	{
	JE_Company_Profile ();	
	}
	?>
	<?php
		}
	?>				

	</div>
<?php
}

if (is_page_template('page-companies.php')) { // companies list sidebar
?>
	<div class="second-column widget-area <?php if(current_user_can('manage_options') ) echo 'sortable' ?>" id="sidebar-companies">
				
		<?php 
		if(is_active_sidebar('sidebar-companies')) { // companies sidebar 
			dynamic_sidebar('sidebar-companies');
		} else {
			JE_Company_Count ();
		} ?>
		
	</div>
<?php
}
if(is_author()) { // author sidebar
?>
	<div class="second-column widget-area <?php if(current_user_can('manage_options') ) echo 'sortable' ?>" id="sidebar-company">
		
	<?php 


		if(is_active_sidebar('sidebar-company')) {
			dynamic_sidebar('sidebar-company');
		} else {
			JE_Company_Profile ();
		}

		if(current_user_can( 'manage_options' )) {
			je_user_package_data (get_query_var('author'));
		}
	?>
		
	</div>
<?php 
}