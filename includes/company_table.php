<?php 
add_action( 'admin_menu', 'register_company_page' );
function register_company_page(){
add_menu_page( 'All Company', 'Company', 'read_private_pages', 'customcompany', 'my_custom_menu_company', 'dashicons-admin-post', 6 ); 	
	
//add_menu_page( 'All Company', 'Company', 'administrator', 'customcompany', 'my_custom_menu_company', 'dashicons-admin-post', 6 ); 	
}

function my_custom_menu_company(){
	//$myrows = $wpdb->get_results( "SELECT ID FROM wp_post_company" );//$wpdb->flush();
	 define("POST_PER_PAGE",20);
if(isset($_POST['submit_compnay']))	
{
	global $wpdb;$d_tempora=$_POST['text_search'];
	$wpdb->get_results( "SELECT ID FROM wp_post_company where display_name LIKE  '%$d_tempora%' " );
	$de= $wpdb->num_rows ;$dee=$de/POST_PER_PAGE;$_SESSION['coun_page']=(int)$dee+1; unset($dee);if($_GET["page_company"]=="")$count_page=1;else $count_page=$_GET["page_company"];
	$start=POST_PER_PAGE*($count_page-1);$end=POST_PER_PAGE*$count_page;
	$string_query="SELECT * FROM wp_post_company  where display_name LIKE  '%$d_tempora%' ORDER BY user_registered DESC LIMIT ".$end." OFFSET ".$start." ";//echo $string_query;
	$myrows = $wpdb->get_results($string_query);unset($d_tempora);//$wpdb->flush();
}
else
{
	global $wpdb;
	$wpdb->get_results( 'SELECT ID FROM wp_post_company' );
	$de= $wpdb->num_rows ;$dee=$de/POST_PER_PAGE;$_SESSION['coun_page']=(int)$dee+1; unset($dee);if($_GET["page_company"]=="")$count_page=1;else $count_page=$_GET["page_company"];
	$start=POST_PER_PAGE*($count_page-1);$end=POST_PER_PAGE*$count_page;
	$string_query="SELECT * FROM wp_post_company  ORDER BY user_registered DESC   LIMIT ".$end." OFFSET ".$start."  ";//echo $string_query;
	$myrows = $wpdb->get_results($string_query);//$wpdb->flush();
}
unset($sccess_destroy);
if(isset($_GET["destroy"]))
{
	if($_GET["destroy"]!="")
	{
		global $wpdb;
		$wpdb->get_results( "SELECT ID FROM wp_post_company where ID ='".$_GET["destroy"]."' and manager != 'company'  " );
		if($wpdb->num_rows >0)
		{
			$wpdb->delete( 'wp_post_company', array( 'ID' => $_GET["destroy"] ) );
			global $wpdb;
			$wpdb->get_results( 'SELECT ID FROM wp_post_company' );
			$de= $wpdb->num_rows ;$dee=$de/POST_PER_PAGE;$_SESSION['coun_page']=(int)$dee+1; unset($dee);if($_GET["page_company"]=="")$count_page=1;else $count_page=$_GET["page_company"];
			$start=POST_PER_PAGE*($count_page-1);$end=POST_PER_PAGE*$count_page;
			$string_query="SELECT * FROM wp_post_company where display_name LIKE  '%%'  LIMIT ".$end." OFFSET ".$start." ";//echo $string_query;
			$myrows = $wpdb->get_results($string_query);//$wpdb->flush();
			$sccess_destroy="Xóa thành công";
		}
	}
}


?>
<div tabindex="0" aria-label="Main content" id="wpbody-content">
	<div class="wrap">
	    <h2>All company <a class="add-new-h2" href="admin.php?page=add-new-company-page">Add New</a></h2><br>
		<h2 style="color:red"><?php if(isset($sccess_destroy))echo $sccess_destroy;?></h2>
		<ul class="subsubsub">
			<li class="all"><a class="current" href="#">All <span class="count">(<?php echo $de;?>)</span></a> </li>
		</ul>
	<form method="post" action="admin.php?page=customcompany" id="posts-filter">
			<p class="search-box">
				<label for="post-search-input" class="screen-reader-text">Search Company:</label>
				<input type="search" value="" name="text_search" id="post-search-input">
				<input type="submit" value="Search Posts" name="submit_compnay" class="button" id="search-submit">
			</p>	
		<div class="tablenav top">
			<div class="tablenav-pages"><span class="displaying-num"><?php echo $de;?> items</span>
				<span class="pagination-links">
					<?php for ($x = 1; $x <= $_SESSION['coun_page']; $x++) {?>
					<a href="<?php echo bloginfo('url'); ?>/wp-admin/admin.php?page=customcompany&page_company=<?php echo $x;?>" title="Go to the previous page" class="prev-page disabled"><?php echo $x;?></a>
					<?php }?>
				</span>
			</div>		
			<br class="clear">
		</div>
			<table class="wp-list-table widefat fixed striped posts">
				<thead>
					<tr><th style="" class="manage-column column-title sortable desc" id="title" scope="col"><a href="#"><span>Title</span><span class="sorting-indicator"></span></a></th><th style="" class="manage-column column-author" id="author" scope="col">Author</th><th style="" class="manage-column column-categories" id="categories" scope="col">Email</th><th style="" class="manage-column column-tags" id="tags" scope="col">Web site</th>
						<th style="" class="manage-column column-comments num sortable desc" id="comments" scope="col">Logo</th><th style="" class="manage-column column-date sortable asc" id="date" scope="col"><a href=""><!--http://ahbrand.vn/wp-admin/edit.php?orderby=date&amp;order=desc--><span>Date</span><span class="sorting-indicator"></span></a></th>	
					</tr>
				</thead>
					<tbody id="the-list">
					<?php foreach ( $myrows as $data_company ) 
					{
					//	echo $data_company->ID;
					?>	
						<tr class="iedit author-self level-0 post-1644 type-post status-publish format-standard has-post-thumbnail hentry category-tin-tuc" id="post-1644">
							<td class="post-title page-title column-title"><strong><a title="<?php echo $data_company->display_name;?>" href="admin.php?page=add-new-company-page&gate=<?php echo $data_company->ID;?>&manager=<?php echo $data_company->manager;?>&userid=<?php echo $data_company->users_id;?>" class="row-title"><?php echo $data_company->display_name;?></a></strong>
								<div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
								<div class="row-actions">
									<span class="edit"><a title="Edit this item" href="admin.php?page=add-new-company-page&gate=<?php echo $data_company->ID;?>&manager=<?php echo $data_company->manager;?>">Edit</a> | </span>				
									<span class="trash"><a href="admin.php?page=customcompany&destroy=<?php echo $data_company->ID;?>" title="Move this item to the Trash" class="submitdelete">Trash</a> | </span>
									<span class="view"><a rel="permalink" title="<?php echo $data_company->display_name;?>" href="admin.php?page=add-new-company-page&gate=<?php echo $data_company->ID;?>&manager=<?php echo $data_company->manager;?>">View</a></span> 
								</div>
							</td>			
							<td class="author column-author"><a href="#"><?php echo $data_company->manager;?></a></td>
							<td class="categories column-categories"><a href="#"><?php echo $data_company->user_email;?></a></td>
							<td class="tags column-tags"><?php echo $data_company->user_url;?></td>			
							<td class="comments column-comments"><div class="post-com-count-wrapper">
							<img src="<?php if($data_company->logo!=""){$tam=unserialize($data_company->logo);echo $tam['company-logo'][0];}?>" style="width: 70px; height: auto;">	</div>
							</td>
							<td class="date column-date"><abbr title="2015/09/17 8:15:43 am"><?php echo $data_company->user_registered;?></abbr></td>		
						</tr>
					<?php }?>		
					</tbody>
				<tfoot>
					<tr>		
						<th style="" class="manage-column column-title sortable desc" scope="col"><a href="#"><span>Title</span><span class="sorting-indicator"></span></a></th><th style="" class="manage-column column-author" scope="col">Author</th><th style="" class="manage-column column-categories" scope="col">Email</th><th style="" class="manage-column column-tags" scope="col">Web site</th>
						<th style="" class="manage-column column-comments num sortable desc" scope="col">Logo</th>
						<th style="" class="manage-column column-date sortable asc" scope="col"><a href="#"><span>Date</span><span class="sorting-indicator"></span></a></th>
					</tr>
				</tfoot>
			</table>
			<div class="tablenav bottom">
				<div class="alignleft actions">
				</div>
				<div class="tablenav-pages"><span class="displaying-num"><?php echo $de;?> items</span>
					<br class="clear">
					<?php for ($x = 1; $x <= $_SESSION['coun_page']; $x++) {?>
						<a href="<?php echo bloginfo('url'); ?>/wp-admin/admin.php?page=customcompany&page_company=<?php echo $x;?>" title="Go to the previous page" class="prev-page disabled"><?php echo $x;?></a>
					<?php }?>
				</div>
	</form>
		    <br class="clear">
		</div>
	<div class="clear"></div>
	</div>
</div>
<?php }