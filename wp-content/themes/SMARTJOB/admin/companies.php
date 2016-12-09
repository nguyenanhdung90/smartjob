<?php 
/**
 * Menu Companies in 
 * @since 1.0
 */
if(class_exists("JE_AdminSubMenu")) :
	class ET_MenuCompanies extends JE_AdminSubMenu{

		/**
		 * Constructor for payment menu item
		 * @since 1.0
		 */
		function __construct(){
			parent::__construct( __('Companies', ET_DOMAIN), 
							__('COMPANIES', ET_DOMAIN), 
							__('Manage how your job board looks and feels.', ET_DOMAIN), 
								'et-companies',
							'icon-breaf',
							20);
		}

		public function on_add_scripts(){
			parent::on_add_scripts();
			$this->add_existed_script('et_underscore');
			$this->add_existed_script('et_backbone');
			$this->add_existed_script('job_engine');
			$this->add_existed_script('admin_scripts');
			$this->add_script('et_companies', get_bloginfo('template_url') . '/js/admin/companies.js', array('jquery', 'backbone', 'underscore', 'job_engine', 'admin_scripts'));
		}

		public function on_add_styles(){
			parent::on_add_styles();
		}

		public function get_header(){
			?>
			<div class="et-main-header">
				
				<div class="title font-quicksand"><?php _e('Companies', ET_DOMAIN); ?></div>
				<div class="desc"><?php _e('are nice people who post jobs on your website', ET_DOMAIN); ?></div>
				<ul class="et-head-statistics">
					<li>
						<div class="icon-overview">
						</div>
						<div class="info">
							<?php $count = et_count_companies_by_time(0); ?>
							<div class="number font-quicksand"><?php echo $count; ?></div>
							<div class="type"><?php _e('Total Companies', ET_DOMAIN); ?></div>
						<div>
					</li>
					<li>
						<div class="icon-overview">
						</div>
						<div class="info">
							<?php $count = et_count_companies_by_time(30*24*60*60); ?>
							<div class="number font-quicksand"><?php echo $count; ?></div>
							<div class="type"><?php _e('New companies last month', ET_DOMAIN); ?></div>
						<div>
					</li>
				</ul>
			</div>
			<?php
		}

		/**
		 * Render view for payment item 
		 * @since 1.0
		 */
		public function view(){
			global $wpdb;
			$this->get_header();
			$items_per_page = apply_filters('et_items_per_page', 10);

			// query companies
			$companies = et_get_users_post_count(array(
				'users_per_page' => $items_per_page
				));
			$total = $wpdb->get_var("SElECT FOUND_ROWS()");


			$pending_view	=	get_users (array(
										'meta_key' => 'je_resume_view_order_status' , 
										'meta_value' => 'pending' , 
									));
			?>
			<div class="et-main-content">
				<div class="search-box">
					<input type="text" id="search_company" class="bg-grey-input" placeholder="<?php _e('Search companies...', ET_DOMAIN) ?>" />
					<span class="icon" data-icon="s"></span>
				</div>
				
				<div class="et-main-main no-margin clearfix overview list">
					<?php if(!empty($pending_view)) { ?>
					<div class="title font-quicksand"><?php _e('Pending Users', ET_DOMAIN) ?></div>
					<div class="desc" >
						<?php _e("List of companies waiting for approval to access resume detail page", ET_DOMAIN); ?>
					<ul class="list-inner list-payment companies-list pending-view-resume"  style="margin-bottom : 50px; min-height : 0;">
						<?php foreach ($pending_view as $user) { ?>
							<li class="company-item" data-id="<?php echo $user->ID?>">
								<div class="method">
									<a class="approve_view" href="#" title="<?php _e("Approve", ET_DOMAIN); ?>"> <span class="icon" data-icon="3"></span></a>
									<a class="reject_view" href="#" title="<?php _e("Reject", ET_DOMAIN); ?>"><span class="icon" data-icon="D"></span></a>
								</div>
								<div class="content">
									<a href="<?php echo get_author_posts_url($user->ID) ?>" class="job"><?php echo $user->display_name ?></a> 
								</div>
							</li>
						<?php } ?>
					</ul>  
					</div>
					<script type="text/data" id="pending_view_resume_companies">
						<?php echo json_encode($pending_view) ?>
					</script>
					<?php } ?>
					<div class="title font-quicksand"><?php _e('All Companies', ET_DOMAIN) ?></div>
					<ul class="list-inner list-payment companies-list">
						<?php foreach ($companies as $company) { ?>
							<li class="company-item" data-id="<?php echo $company->ID?>">						
								<div class="content">
									<a href="<?php echo get_author_posts_url($company->ID) ?>" class="job"><?php echo $company->display_name ?></a> 
									<a href="<?php echo get_author_posts_url($company->ID) ?>" class="company"><?php printf( et_number( __('No job', ET_DOMAIN), __('%d job', ET_DOMAIN), __('%d jobs', ET_DOMAIN), $company->count ), $company->count)  ?></a>
								</div>
							</li>
						<?php } ?>
					</ul>
					<button class="et-button btn-button load-more" <?php if ( ceil( $total / $items_per_page) <= 1 ) echo 'style="display: none"' ?>>
						<?php _e('More companies', ET_DOMAIN) ?>
					</button>	        			
				</div>
			</div>
			<?php
			echo $this->get_footer();
		}
	}
endif;

?>