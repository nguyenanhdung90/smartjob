<?php 
global $job;
//$colours  		= et_get_job_type_colors();

?>
<li data-icon="false" class="list-item">
	<span class="arrow-right"></span>
	<a data-ajax="false" href="<?php the_permalink() ?>" >
		<p class="name"><?php the_title(); ?></p>
		<?php $rf= get_the_author_meta('roles',$post->post_author);
		if($rf[0]=="company")
		{
		?>
			<p class="list-function"> 
			<span class="postions" style="font-size:15px"><?php echo get_the_author_meta('display_name'); ?></span>
			</p>
		<?php 
		}
		else
		{
			global $wpdb;
		    $rows=$wpdb->get_results( "SELECT company_editor_id FROM wp_posts where ID ='".$post->ID."' " );
			$rows=$rows[0]->company_editor_id;
			$rows=$wpdb->get_results( "SELECT ID,display_name FROM wp_post_company where ID ='".$rows."' " );
		?>
			<p class="list-function"> 
			<span class="postions" style="font-size:15px"><?php echo $rows[0]->display_name; ?></span>
			</p>
		<?php
		}
		?>
    	<p class="list-function">    	
			<?php if ($job['location'] != '') { ?>
    			<span class="locations" style="font-size:18px" ><span class="icon" data-icon="@"> </span><?php echo $job['location']; ?></span>
			<?php } ?>
    	</p>
		<?php 

		$value = get_post_meta( get_the_ID(), 'cfield-592', true );
		?>
		<p class="list-function ui-li-desc">
		<span itemprop="addressLocality" style="font-size:16px">Salary:</span>
		<span itemprop="addressLocality" style="color:#F0111B;font-size:16px"><?php echo $value;?> </span>
		</p>
		<?php

		?>
		<p class="list-function"> 
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
		</p>
    </a>
</li>