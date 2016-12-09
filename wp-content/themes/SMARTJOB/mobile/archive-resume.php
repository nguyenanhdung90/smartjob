<?php et_get_mobile_header('mobile');  
	global $et_global,$post,$wp_query;
	$arr             = array();
?>
<?php 
if(current_user_can( 'manage_options' ))
{
	?>
	<div data-role="search" class="search-area resume-search">
		<div class="search">
			<a href="#" class="icon ui-btn-s search-btn category-btn" data-icon="l"></a>
			<div class="search-text">
				<input type="text" name="rq" id="resume_search" class="" placeholder="<?php _e("Enter job titles or skills", ET_DOMAIN); ?>" >
				<span class="icon" data-icon="s"></span>
			</div>

			<div class="menu-filter">
				<div class="menu-filter-inner">
					<div class="icon-header">
						<a class="icon" data-icon="l"></a>
					</div>
					<div class="search">
						<input type="text" name="et_location" id="et_location" title="<?php _e("Enter the location...", ET_DOMAIN); ?>" placeholder="<?php _e("Enter the location...", ET_DOMAIN); ?>">
						<span class="icon" data-icon="@"></span>
					</div>
					<div class="tabs resume-tabs">
						<a class="ui-tabs ui-corner-left tab-active" id="resume-cat">
							<?php _e('Categories',ET_DOMAIN); ?>
						</a>
						<a class="ui-tabs ui-corner-right" id="available">
							<?php _e('Availables',ET_DOMAIN) ?>
						</a>						
					</div>

					<div class="content-tabs resume-contents">
						<div class="tab-cont resume-cat">
							<div class="list-categories">
								<a data="" class="ui-list ui-list-active ui-list-main"><?php _e('All categories',ET_DOMAIN); ?></a>
								<ul>
									<?php
										$tax_categories = JE_TaxFactory::get_instance('resume_category');
										$tax_categories->mobile_filter_list();
									?>
								</ul>
							</div>
						</div>
						<div class="tab-cont available">
							<div class="contact-type">
								<ul>
									<?php 
										$tax_available = JE_TaxFactory::get_instance('available');
										$availables = $tax_available->get_terms_in_order();
										$colors = $tax_available->get_color();
										foreach ($availables as $key => $value) { ?>
											<li><a data="<?php echo $value->slug; ?>" data-name="available" class="pick-param ui-list color-<?php echo $colors[$value->term_id]; ?>"><?php echo $value->name ; ?><span class="icon-label flag"></span></a></li>
									<?php }	?>
								</ul>
							</div>
						</div>	                		
					</div>

					<a href="#" class="ui-btn-s btn-grey filter-search-btn" id="apply_search_resume"> <?php _e('Search',ET_DOMAIN); ?> </a>
				</div>
			</div>
		</div>
	</div>

	<div data-role="content" id="page" class="ui-home resume-content-home">
		<?php 
		// echo '<pre>';
		// print_r($wp_query);
		// echo '</pre>';
		?>
		<ul class="listview resume-listview" data-role="listview">
			<?php
			//echo '<li class="list-divider">'.__("Réumes",ET_DOMAIN).'</li>';  
			if ( have_posts() ) {
				$page       = $wp_query->max_num_pages;
				$class_name = '';
				$first_post = $post->ID;
				$flag       = 0;
				$flag_title = 0;
				//echo '<li class="list-divider">'.__("Feature Jobs",ET_DOMAIN).'</li>';
				while (have_posts() ) { the_post();
					global $resume;
					$resume = JE_Resume::convert_from_post($post);

					load_template( apply_filters( 'et_mobile_template_resume', dirname(__FILE__) . '/mobile-template-resume.php'), false);
					?>
						
					<?php 
				}
			} ?>	
		</ul>
		<?php
			$cur_page = (get_query_var('paged')) ? get_query_var('paged') : 1;
			if ( isset($page) && $cur_page < $wp_query->max_num_pages ) { ?>
				<a href="#" class="btn-grey btn-wide btn-load-more ui-corner-all et_loadmore" id="loadmore_resume"><?php _e('Load More Resumes',ET_DOMAIN); ?></a>
		<?php } ?>
	</div><!-- /content -->
	<input type="hidden" id="cur_page_index" value="<?php echo $cur_page; ?>">
<?php 
}
else
{
?>
<div style="background-color:white;padding-top:12px;padding-bottom:9px">
   <h2 style="text-align: center;margin-top: 20px;font-size: 23px;">Steps for Job Seekers</h2>
   <p style="text-align: center;margin-top:9px">Creating a personal profile is very simple</p>
   <p style="text-align: center">and fast,please click on the create a resumer now.</p>
   <img style="width:100%;margin-top:9px" src="http://smartjob.vn/wp-content/themes/SMARTJOB/img/sodo-resume.png">
</div>
<div>
   <h2 style="text-align: center;margin-top: 20px;font-size: 25px;">Employers choose</h2>
   <h2 style="text-align: center;margin-top: 10px;font-size: 25px;">resume and contact</h2>
   <p style="text-align: center;margin-top:9px">Take a look at resumes</p>
   <p style="text-align: center">to choose the most suitable  resume with your</p>
   <p style="text-align: center">company,and you will have the information to contact</p>
   <img style="width:100%;margin-top:9px" src="http://smartjob.vn/wp-content/themes/SMARTJOB/img/search-comp.png">
</div>
<?php 
}
?>
<?php et_get_mobile_footer('mobile'); ?>