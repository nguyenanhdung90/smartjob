<?php 
/**
 * Declare Section payment in admin panel
 * @since 1.0
 */
if ( class_exists("JE_AdminSubMenu") ) :
	class ET_MenuWizard extends JE_AdminSubMenu{

		/**
		 * Constructor for payment menu item
		 * @since 1.0
		 */
		function __construct(){
			parent::__construct( __('Setup wizard', ET_DOMAIN), 
								__('SETUP WIZARD', ET_DOMAIN),
								__('Step-by-step guide to configure your website', ET_DOMAIN),
								'et-wizard',
								'icon-help',
								25);

			$this->add_action('admin_init', 'remove_notices');
			$this->add_action('et_admin_localize_scripts', 'localize_script');
		}

		public function remove_notices(){
			if (isset($_GET['page']) && $_GET['page'] == 'et-wizard'){
				global $et_master;			
				$et_master->wizard_status = 1;
			}
		}

		public function on_add_scripts(){
			parent::on_add_scripts();

			$this->add_existed_script('jquery');
			
			$this->add_existed_script('jquery-ui-sortable');
			
			//$this->add_script('et_jquery_ui_nestedsort', get_bloginfo('template_url') . '/js/lib/jquery-ui-for-sortable.js', array('jquery'));
			$this->add_script('et_nestedsort', TEMPLATEURL . '/js/lib/jquery.nestedSortable.js' , array('jquery' , 'jquery-ui-sortable'));
			//$this->add_existed_script('jquery-textarea-autosize');
			$this->add_existed_script('jquery_validator');

			$this->add_existed_script('plupload-all');
			$this->add_existed_script('et_underscore');
			$this->add_existed_script('et_backbone');
			$this->add_existed_script('job_engine');
			$this->add_existed_script('admin_scripts');

			$this->add_script('et_wizard', get_bloginfo('template_url') . '/js/admin/wizard.js', array('jquery','et_underscore','et_backbone', 'job_engine', 'admin_scripts'));
			$this->add_script('et_setting_jobs', get_bloginfo('template_url') . '/js/admin/content-job.js', array('jquery','et_underscore','et_backbone', 'job_engine', 'admin_scripts'));
		}

		public function on_add_styles(){
			parent::on_add_styles();
			$this->add_style('job_styles' , TEMPLATEURL . '/css/job-label.min.css');
			$this->add_style('job_slider' , TEMPLATEURL . '/css/ui-slider.css');
		}

		public function localize_script ($slug) {
			if($slug == 'et-wizard') {
				wp_localize_script( 
					'et_wizard', 
					'et_wizard', 
					array(
						'insert_sample_data' => __("Insert sample data", ET_DOMAIN),
						'delete_sample_data' => __("Delete sample data", ET_DOMAIN)
						)
					);

				wp_localize_script( 
					'et_wizard', 
					'et_setting', 
					array(
						'payment_plan_error_msg' => __("Input is invalid. Please check again.", ET_DOMAIN),
						'del_parent_cat_msg' => __("You cannot delete a parent job category. Delete its sub-categories first.", ET_DOMAIN)
						)
					);
			}
		}

		public function get_header(){
			?>
			<div class="et-main-header">
				<div class="title font-quicksand"><?php echo $this->page_title ?></div>
				<div class="desc"><?php echo $this->page_subtitle ?></div>
				<?php 
					$step = et_verify_setup_process(true); 
					$background_style	=	apply_filters( 'et_wizard_background_class', 'wizards' );
				?>
				<div class="<?php echo $background_style; ?> wizard-step<?php echo $step ?>" ></div>
			</div>
			<?php
		}

		/**
		 * Render view for payment item 
		 * @since 1.0
		 */
		public function view(){
			$this->get_header();
			$sub_section = (int)et_verify_setup_process(true) ;
			//echo $sub_section;
			$wizard_step	=	et_get_wizard_step ();

			$section	=	isset($wizard_step[$sub_section]) ? $wizard_step[$sub_section]['section'] : 'branding';
			?>
			<style>.et-main-main .desc .form .form-item span.notice {color : #E0040F;}</style>
			<div class="et-main-content">
				<div class="et-main-left">
					<ul class="et-menu-content inner-menu wizard-steps">
						<?php foreach ($wizard_step as $key => $value) { 
							$step	=	$key +1;
							?>
						<li>
							<a href="#wizard-<?php echo $value['section'] ?>" menu-data="<?php echo $value['section'] ?>" class="<?php if ( $sub_section == $key ) echo 'active'  ?>">
								<span class="icon" data-icon="<?php echo $value['icon'] ?>" ></span><?php if($step < count ($wizard_step) ) printf(__("Step %s:",ET_DOMAIN), $step );?> <?php echo $value['label'];?>
							</a>
						</li>
						<?php } ?>
					</ul>
				</div>
				<div class="settings-content">
					<?php require_once 'wizard-branding.php' ;?>
					<?php require_once 'wizard-content.php' ;?>
					<?php require_once 'wizard-payment.php' ;?>
					<?php do_action ('je_setup_wizard', $section ) ?>
					<?php require_once 'wizard-sample-data.php';?>
				</div>
			</div>
			<?php
			echo $this->get_footer();
		}

		function et_wizard_nexstep_button ($section ) {
			$wizard_step	=	et_get_wizard_step ();
			
			$length	=	count($wizard_step);
			if($section == $length) {
				return ;
			}
			
			if( ($section + 1) == $length ) { // finish wizard
			?>
				<button class="et-button btn-button next-step" href="<?php echo $wizard_step[$section++]['section'] ?> " >
					<?php _e('Finish Wizard', ET_DOMAIN) ?>
				</button>
			<?php 
			} else {// go to next step ?>
				<button class="et-button btn-button next-step" href="<?php echo $wizard_step[$section++]['section'] ?> " >
					<?php _e('Go to the next step', ET_DOMAIN) ?>
				</button>
				<?php 
			}
		}
	}
endif;