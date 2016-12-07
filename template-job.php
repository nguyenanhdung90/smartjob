<?php
global $post, $job;

$job_cat 	= isset($job['categories'][0]) ? $job['categories'][0] : '';
$job_type 	= isset($job['job_types'][0]) ? $job['job_types'][0] : '';

$company		= et_create_companies_response( $job['author_id'] );
$company_logo	= $company['user_logo'];

// add this company data to the array to pass to js
if(!isset($arrAuthors[$company['id']])){
	$arrAuthors[$company['id']]	= array(
		'display_name'	=> $company['display_name'],
		'user_url'		=> $company['user_url'],
		'user_logo'		=> $company_logo
	);
}
?>
<li class="job-item" itemscope itemtype="http://schema.org/JobPosting">
	<div class="thumb" style="height:auto">
	<?php //$rf= get_the_author_meta('roles',$post->post_author);print_r($rf[0]);?>
	<?php
     	$rf= get_the_author_meta('roles',$post->post_author);
		if($rf[0]=="company")
		{
			if (!empty($company_logo)){
				?>
				<a id="job_author_thumb-<?php echo $company['ID'];?>" data-id="<?php echo $company['ID'];?>" href="<?php echo $company['post_url'];?>" 
					title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" class="thumb" target="_blank">
					<img src="<?php echo ( isset($company_logo['small_thumb']) && !empty($company_logo['small_thumb']) ) ? $company_logo['small_thumb'][0] : $company_logo['thumbnail'][0]; ?>" id="company_logo_thumb-<?php echo $company_logo['attach_id'];?>" data-attachid="<?php echo $company_logo['attach_id'];?>" />
				</a>
				<?php
			}
		}
		else
		{			
			global $wpdb;
		    $rows=$wpdb->get_results( "SELECT company_editor_id FROM wp_posts where ID ='".$post->ID."' " );
			$rows=$rows[0]->company_editor_id;
			$rows=$wpdb->get_results( "SELECT ID,logo FROM wp_post_company where ID ='".$rows."' " );$com_id=$rows[0]->ID;
			$rows=$rows[0]->logo;$rows=unserialize($rows);$rows=$rows['company-logo'][0];
	?>
             <a target="_blank" class="thumb" title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" href="<?php echo $company['post_url'].'/?com_i='.$com_id;unset($com_id)?>" data-id="<?php echo $company['ID'];?>" id="job_author_thumb-<?php echo $company['ID'];?>">
				<img data-attachid="0" id="company_logo_thumb-0" src="<?php echo $rows;?>">
			</a>
	<?php
		}
	?>
	</div>

	<div class="content">
		<h2 class="title-job"  itemprop="title">
		<a class="title-link title"  href="<?php the_permalink() ?>" title="<?php printf(__('View more details of %s', ET_DOMAIN), get_the_title())?>" target="_blank">
			<?php the_title();  ?>
		</a>
		</h2>
		<a target="_blank" class="title-link title new-tab-icon"  href="<?php the_permalink() ?>" title="<?php printf(__('View more details in new window tab', ET_DOMAIN), get_the_title())?>">
			<span class="icon" data-icon="R"></span>
		</a>

		<div class="desc f-left-all" style="top:57px">
			<div class="cat company_name c">
			<?php 
				if($rf[0]=="company")
		        {
			?>
				<a data-id="<?php echo $company['ID'];?>" href="<?php echo $company['post_url'];?>" title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" target="_blank" >
					<?php echo $company['display_name'] ?>
				</a>
			<?php 
			    }
				else
				{
			global $wpdb;
		    $rows=$wpdb->get_results( "SELECT company_editor_id FROM wp_posts where ID ='".$post->ID."' " );
			$rows=$rows[0]->company_editor_id;
			$rows=$wpdb->get_results( "SELECT ID,display_name FROM wp_post_company where ID ='".$rows."' " );
			//$rows=$rows[0]->display_name;
			?>
				<a  data-id="<?php echo $company['ID'];?>" href="<?php echo $company['post_url'].'?com_i='.$rows[0]->ID;?>" title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" target="_blank"  >
					<?php echo $rows[0]->display_name; ?>
				</a>
			<?php					
				}			
			?>
			</div>
			<?php if ($job_type != '') { ?>
				<div itemprop="employmentType" class="job-type <?php echo 'color-' . $job_type['color'] ?>">
					<span class="flag"></span>
					<a href="<?php echo $job_type['url']; ?>" title="<?php printf(__('View all posted jobs in %s', ET_DOMAIN), $job_type['name']);?>" >
						<?php echo $job_type['name'] ?>
					</a>
				</div>
			<?php } ?>

			<?php if ($job['location'] != '') { ?>
				<div>
					<span class="icon" data-icon="@"></span>
					<span itemprop="jobLocation" itemscope itemtype="http://schema.org/Place" class="ob-location">
         				<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
         				<span itemprop="addressLocality" ><?php echo $job['location'] ?></span>
          				</span>
        			</span>

				</div>
			<?php } ?>
			<div>
					<span class="ob-location" itemtype="http://schema.org/Place" itemscope="" itemprop="jobLocation">
						<?php 
						$fields = JEP_Field::get_all_fields();
						foreach ($fields as $field) {
						$value = get_post_meta( get_the_ID(), 'cfield-'. $field->ID, true );
						?>
						<span itemprop="addressLocality" ><?php echo $field->name;?>:</span>
						<span itemprop="addressLocality" style="color:#F0111B"><?php echo $value;?> </span>
						<?php
						}
						?>
          			</span>

			</div>
		</div>
		<div class="decription_smartjob" style="margin-top: 26px;">
			<?php //echo excerpt(90); 
			echo get_excerpt(250);
			?> 
		</div>
		<div class="decription_smartjob">
		<?php
		$posttags = get_the_tags();
		if ($posttags) {
		foreach($posttags as $tag) {
		?>
		<a target="_blank" href="<?php bloginfo('url');echo '/?s='.$tag->name ;?> " class="tag_smartjob_home" ><?php echo $tag->name . ' ';  ?></a>
		<?php
		}
		}
		?>		
		</div>
		<div class="tech f-right actions">
			<?php
				$feature 		 =	'';
				$set_feature =  __('Make this job featured', ET_DOMAIN) ;
				if( $job['featured']) {
					$feature = 'flag-feature';
					$set_feature = __('Unset featured status',ET_DOMAIN);
			?>
				<span class="feature font-quicksand"><?php _e('Hot', ET_DOMAIN) ?></span>
			<?php } ?>
			<?php global $disable_actions;
			// some pages don't need front end actions
			if (!isset($disable_actions) || !$disable_actions) { ?>
				<?php if( current_user_can('manage_options')) { ?>
					<a data-post-ID="<?php echo $post->ID ?>" title="<?php echo $set_feature ?>" class="action-featured flag <?php echo $feature ?> tooltip" href="#"><span class="icon" data-icon="^"></span></a>
					<a data-post-ID="<?php echo $post->ID ?>" class="action-edit tooltip" title="<?php _e('Edit',ET_DOMAIN) ?>" href="#"><span class="icon" data-icon="p"></span></a>
					<a data-post-ID="<?php echo $post->ID ?>" class="action-archive tooltip" title="<?php _e('Archive',ET_DOMAIN) ?>" href="#"><span class="icon" data-icon="#"></span></a>
				<?php } ?>
			<?php } ?>
		</div>

	</div>
</li>