<?php
//include "../../../wp-config.php";
require('../../../wp-config.php');
global $wpdb;
$key = $_POST['key'];$key=str_replace("'","''",$key);
$location = $_POST['location'];$location =(int)$location ;
//if($location=='2146')$sql_location='';else $sql_location=" and wp_postmeta.meta_value = $location ";
$page = $_POST['page'];
$start_number=($page-1)*10;
$number=$page*10;

$start = microtime(true);


if($location=='2146')
{
$sql_total = "SELECT COUNT(*)
FROM wp_postmeta RIGHT JOIN wp_posts
ON wp_postmeta.post_id = wp_posts.ID where(wp_postmeta.meta_key ='cfield-2146' $sql_location and 
( wp_posts.post_title LIKE '%$key%' or wp_posts.post_content LIKE '%$key%' ) 
and  wp_posts.post_status='publish' and wp_posts.post_type='job' )  
 ";
$total= $wpdb->get_var($sql_total);

if($total%10==0)$number_page=$total/10;else{$d=$total/10;$number_page=(int)$d +1;}
	$time_elapsed_secs = microtime(true) - $start;
echo "<p>Total  :";
echo $total;
echo " jobs | Time execute database: ".$time_elapsed_secs." s</p>";

$sql_search = "SELECT wp_posts.ID,wp_posts.company_editor_id,wp_posts.post_author,wp_posts.post_title, wp_postmeta.meta_key, wp_posts.post_name,wp_posts.post_content, wp_postmeta.meta_value
FROM wp_postmeta LEFT JOIN wp_posts
ON wp_postmeta.post_id = wp_posts.ID where(  wp_postmeta.meta_key ='et_featured'  and ( wp_posts.post_title LIKE '%$key%' or wp_posts.post_content LIKE '%$key%' )  and  wp_posts.post_status='publish' and  wp_posts.post_type='job' )  
ORDER BY wp_postmeta.meta_value DESC , wp_posts.ID DESC  LIMIT $start_number,10 ";	
}

else 
{
	$sql_location=" and wp_postmeta.meta_value = $location ";
$sql_total = "SELECT COUNT(*)
FROM wp_postmeta RIGHT JOIN wp_posts
ON wp_postmeta.post_id = wp_posts.ID where(wp_postmeta.meta_key ='cfield-2146' $sql_location and 
( wp_posts.post_title LIKE '%$key%' or wp_posts.post_content LIKE '%$key%' ) 
and  wp_posts.post_status='publish' and wp_posts.post_type='job' )  
 ";
$total= $wpdb->get_var($sql_total);

if($total%10==0)$number_page=$total/10;else{$d=$total/10;$number_page=(int)$d +1;}
	$time_elapsed_secs = microtime(true) - $start;
echo "<p>Total  :";
echo $total;
echo " jobs | Time execute database: ".$time_elapsed_secs." s</p>";	
	
	
$sql_search = "SELECT wp_posts.ID,wp_posts.company_editor_id,wp_posts.post_author,wp_posts.post_title, wp_postmeta.meta_key, wp_posts.post_name,wp_posts.post_content, wp_postmeta.meta_value
FROM wp_postmeta LEFT JOIN wp_posts
ON wp_postmeta.post_id = wp_posts.ID where(  wp_postmeta.meta_key ='cfield-2146' $sql_location  and 
( wp_posts.post_title LIKE '%$key%' or wp_posts.post_content LIKE '%$key%' ) 
 and  wp_posts.post_status='publish' and  wp_posts.post_type='job'  )  
ORDER BY wp_posts.post_modified_gmt  DESC , wp_posts.ID DESC LIMIT $start_number,10 ";	
}
$result = $wpdb->get_results($sql_search);

foreach ($result as $value) {
	// muc luong
	$post_id=$value->ID;
	$sql_wp_post_meta="SELECT * FROM wp_postmeta WHERE post_id=$post_id and meta_key = 'cfield-592' ";
	$result_post_meta = $wpdb->get_results($sql_wp_post_meta);
	if(isset($result_post_meta[0]->meta_value))$salary=$result_post_meta[0]->meta_value;else $salary="";
	
	// hot job
	$sql_wp_post_meta="SELECT meta_value FROM wp_postmeta WHERE post_id=$post_id and meta_key = 'et_featured' ";
	$result_post_meta = $wpdb->get_results($sql_wp_post_meta);
	$feature=$result_post_meta[0]->meta_value;
	
    // job type full time	
	$sql_wp_post_meta="
	SELECT wp_term_taxonomy.term_id FROM wp_term_relationships RIGHT JOIN wp_term_taxonomy ON wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id
	where( wp_term_relationships.object_id = $post_id and  wp_term_taxonomy.taxonomy = 'job_type' )
	";	
	$result_post_meta = $wpdb->get_results($sql_wp_post_meta);
	$term_id=$result_post_meta[0]->term_id;
	
	// tag job
	$sql_tag="
	SELECT wp_term_taxonomy.term_id FROM wp_term_relationships RIGHT JOIN wp_term_taxonomy ON wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id
	where( wp_term_relationships.object_id = $post_id and  wp_term_taxonomy.taxonomy = 'post_tag' )
	";		
	$result_job_tag = $wpdb->get_results($sql_tag);
	$term_name_array=array();
	foreach($result_job_tag as $value_term){
		$term_id=$value_term->term_id;
		$sql_name_terms="SELECT name FROM wp_terms WHERE term_id=$term_id  ";
		$result_term_name = $wpdb->get_results($sql_name_terms);
		$term_name_array[]= $result_term_name[0]->name;
	}
	
	$sql_wp_post_meta="SELECT name,slug FROM wp_terms WHERE term_id= $term_id ";
	$job_type2 = $wpdb->get_results($sql_wp_post_meta);
	$job_type=$job_type2[0]->name;
	//$job_slug=$job_type2[0]->slug;

	// company name

	if($value->company_editor_id!=null && $value->company_editor_id!=0)
	{
		$company_editor_id=$value->company_editor_id;
		$sql="SELECT display_name FROM wp_post_company WHERE ID = $company_editor_id ";
		$result = $wpdb->get_results($sql);
		$company=$result[0]->display_name;
		// logo company
		$sql="SELECT logo FROM wp_post_company WHERE ID = $company_editor_id ";
		$result = $wpdb->get_results($sql);
		//var_dump($result[0]->logo);
 		$d=unserialize ($result[0]->logo);
		$logo=$d['company-logo'][0];
		//echo $logo; 
	}
	else{
		$company_editor_id="";
		$author=$value->post_author;
		$sql="SELECT display_name FROM wp_users WHERE ID = $author ";
		$result = $wpdb->get_results($sql);
		$company=$result[0]->display_name;
		// logo company
		$sql="SELECT meta_value FROM wp_usermeta WHERE meta_key = 'et_user_logo' and user_id=$author ";
		$result = $wpdb->get_results($sql);
		$d=unserialize ($result[0]->meta_value);
		$logo=$d['company-logo'][0];
	}
	$author=$value->post_author;
	$sql="SELECT user_nicename FROM wp_users WHERE ID = $author ";
	$result = $wpdb->get_results($sql);
	$user_nicename=$result[0]->user_nicename;
	

	
?>
				<li class="job-item" itemscope="" itemtype="http://schema.org/JobPosting">
				<div class="thumb" style="height:auto">
					<a target="_blank" class="thumb" title="View posted jobs by <?php echo $user_nicename;?>" href="<?php bloginfo('url');?>/company/<?php echo $user_nicename;?>/?com_i=<?php echo $company_editor_id;?>" data-id="98" >
						<img data-attachid="0" id="company_logo_thumb-0" src="<?php if(isset($logo))echo $logo; ?>">
					</a>
				</div>

				<div class="content">
					<h2 class="title-job" itemprop="title">
					<a class="title-link title" href="<?php bloginfo('url');?>/job/<?php echo $value->post_name;?>" title="<?php echo $value->post_title;?>" target="_blank">
						<?php echo $value->post_title;?>
					</a>
					</h2>
					<a target="_blank" class="title-link title new-tab-icon" href="<?php bloginfo('url');?>/job/<?php echo $value->post_name;?>" title="View more details in new window tab">
						<span class="icon" data-icon="R"></span>
					</a>

					<div class="desc f-left-all" style="top:57px">
						    <div class="cat company_name c">
								<a data-id="98" href="<?php bloginfo('url');?>/company/<?php echo $user_nicename;?>/?com_i=<?php echo $company_editor_id;?>" title="View posted jobs by <?php echo $user_nicename;?>" target="_blank">
								<?php echo $company;?></a>
							</div>
							<div itemprop="employmentType" class="job-type color-26">
								<span class="flag"></span>
								<a href="#" title="View all posted jobs in Fulltime">
									<?php echo $job_type;?>					
								</a>
							</div>						
							<div>
								<span class="icon" data-icon="@"></span>
								<span itemprop="jobLocation" itemscope="" itemtype="http://schema.org/Place" class="ob-location">
									<span itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress">
									<span itemprop="addressLocality">
									<?php 
									if($location !='2146'){
										$lotion=$value->meta_value;
									  }
									  else
									  {
										 $id=$value->ID;
										 $sql_search = "SELECT meta_value from wp_postmeta where post_id=$id and meta_key='cfield-2146' "; 
										 $result = $wpdb->get_results($sql_search);
										 $lotion=$result[0]->meta_value;
									  }
									  if($lotion=='2147')$location_view='Hà Nội';
									  elseif($lotion=='2148')$location_view='Hồ Chí Minh';
									  elseif($lotion=='2149')$location_view='Đà Nẵng';
									  elseif($lotion=='2150')$location_view='Other';
									  echo $location_view;									  
									?>
									</span>
									</span>
								</span>
							</div>
							<div>
								<span class="ob-location" itemtype="http://schema.org/Place" itemscope="" itemprop="jobLocation">
									<span itemprop="addressLocality">Salary:</span>
									<span itemprop="addressLocality" style="color:#F0111B"><?php echo $salary;?></span>
								</span>
							</div>
					</div>
					<div class="decription_smartjob" style="margin-top: 26px;">						
						<?php $text= substr($value->post_content, 0, 250);echo strip_tags($text)."...";?>
                        <a href="<?php bloginfo('url');?>/job/<?php echo $value->post_name;?>">more</a> 
					</div>
					<div class="decription_smartjob">
					<?php
foreach($term_name_array as $tag_name){					
					?>
<a onclick="search_index(1,'<?php echo $tag_name; ?>')" href="#" class="tag_smartjob_home"><?php echo $tag_name; ?></a>
<?php }?>			
					</div>
					<?php if($feature){?>
					<div class="tech f-right actions">
						<span class="feature font-quicksand">Hot</span>
					</div>
                    <?php }?>
				</div>
				</li>
<?php
}

?>
<style>
.pagenation{
	border: 1px solid #cccc;
	padding: 5px;
}
.active{
	background:#2A4560;
	color:white;
}
</style>
<div style="margin-top:10px">
<?php 
   if($number_page < 10){ 
   for($i = 1; $i <= $number_page; $i++){
?>
<button class="pagenation <?php if($i==$page)echo "active";?>" type="button" onclick="search_index(<?php echo $i;?>,'')"><?php echo $i;?></button>
<?php 
   }
   }else{
	   if($page==1 || $page == 2 || $page == 3 || $page==4 || $page==5 )
	   {
		   for($i = 1; $i <= 6; $i++)
		   {
?>
<button class="pagenation <?php if($i==$page)echo "active";?>" type="button" onclick="search_index(<?php echo $i;?>,'')"><?php echo $i;?></button>
<?php			
		   }
		   ?>
<button class="pagenation" type="button" >...</button>		   
<button class="pagenation" type="button" onclick="search_index(<?php echo $number_page;?>,'')" ><?php echo $number_page;?></button>		   
		   <?php
	   }
	   elseif($page==$number_page || $page==($number_page-1) || $page==($number_page-2) || $page==($number_page-3) || $page==($number_page-4) || $page==($number_page-5) )
	   {
?>
<button class="pagenation" type="button" onclick="search_index(1,'')">1</button>
<button class="pagenation" type="button" >...</button>
<?php   
		   for($i = $number_page-6; $i <= $number_page; $i++)
		   {
?>
<button class="pagenation <?php if($i==$page)echo "active";?>" type="button" onclick="search_index(<?php echo $i;?>,'')"><?php echo $i;?></button>
<?php   
		   }
	   }
	   else
	   {
?>
<button class="pagenation" type="button" onclick="search_index(1,'')">1</button>
<button class="pagenation" type="button" >...</button>
<?php
		   for($i = $page-2; $i <= $page+2; $i++)
		   {
?>
<button class="pagenation <?php if($i==$page)echo "active";?>" type="button" onclick="search_index(<?php echo $i;?>,'')"><?php echo $i;?></button>
<?php 
		   }
?>
<button class="pagenation" type="button" >...</button>		   
<button class="pagenation" type="button" onclick="search_index(<?php echo $number_page;?>,'')" ><?php echo $number_page;?></button>		   
<?php	   
	   }
?>
<?php
   }
?>
</div>