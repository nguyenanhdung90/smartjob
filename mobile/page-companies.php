<?php et_get_mobile_header('mobile'); 
	global $wpdb;
	$companies 			= et_get_companies();
	$companies_count 	= count($companies);
	$alphabet_list 		= et_get_companies_in_alphabet();
?>
<style type="text/css">
	.company-section {
		border-bottom: 1px solid #CCC;
		padding: 10px 20px;
	}
	.company-section li {
		padding-top: 5px;
	}
</style>
<div data-role="content" class="post-content resume-contentpage">
    <h1 class="title-resume">
        <?php 
        	if($companies_count > 1 )  printf( __("%d Active Companies", ET_DOMAIN) , $companies_count ); else printf( __("%d Active Company", ET_DOMAIN) , $companies_count ); ?>
    </h1>
    
    <div class="companies-container" >
		<?php 
		
		if ( $companies_count > 0 ){
			?>
			<ul class="list-company" style="margin-top: 10px; ">
				<?php foreach ($alphabet_list as $letter => $companies) { ?>
					<?php if ( !empty($companies) ){ ?>
					<li class="company-section" data-char="<?php echo trim($letter) ?>">
						<div class="title"><?php echo $letter == 'numbers' ? '0-9' : $letter; ?> </div>
						<ul style="padding:10px;">
							<?php foreach ((array)$companies as $company) { ?>
								<li>
									<a class="company-item" href="<?php echo get_author_posts_url($company->ID, $company->user_login) ?>" title="<?php echo $company->display_name ?>"><?php echo $company->display_name ?>
									</a>
								</li>
							<?php }  ?>
						</ul>
					</li>
					<?php } ?>
				<?php } ?>
			</ul>
		<?php 
		}
		?>
	</div>
   
</div>

<?php et_get_mobile_footer('mobile'); ?> 