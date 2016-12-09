<div class="main-header bg-main-header" id="header_top">
	<div class="main-center">

		<!-- left content on header -->
		<div class="f-left f-left-all">

			<?php
			global $page, $paged, $current_user, $user_ID;
			$site_description = get_bloginfo( 'description', 'display' );
			$general_opts	= new ET_GeneralOptions();
			$website_logo	= $general_opts->get_website_logo();
			?>
			<!-- fix logo middle -->
			<table class="fix-logo"><tr><td>
				<a href="<?php echo home_url()?>" class="logo"><img src="<?php echo $website_logo[0];?>" alt="<?php echo $general_opts->get_site_title();  ?>" /></a>	
			</td></tr></table>

			<div class="slogan"><?php echo $site_description; ?></div>
		</div>

		<!-- right content on header -->
		<div class="header-technical f-right f-left-all">
			<div class="category">
				<?php
					je_header_menu ();
				?>

			</div>

			<div class="ver-line"></div>
			<?php
				$roles		=	$current_user->roles;
				$user_role	=	array_pop ($roles);
				//if($role == 'jobseeker')

				if ( function_exists('et_is_resume_menu') && et_is_resume_menu() && !is_user_logged_in() ) { ?>

				<div class="post-job">

					<a href="<?php echo et_get_page_link( array('page_type' => 'jobseeker-signup' , 'post_title' => 'Create a Resume' ) ); ?>" class="bg-btn-action btn-header border-radius" title="<?php _e('Create a Resume', ET_DOMAIN)?>">
						<?php _e('CREATE A RESUME', ET_DOMAIN );?><span class="icon f-right" data-icon="W"></span>
					</a>
				</div>
				<div class="ver-line"></div>

				<?php  } else {

					if( $user_role == 'company' || !is_user_logged_in() || current_user_can('manage_options') ) {
					 ?>
					<div class="post-job">
						<a href="<?php echo et_get_page_link('post-a-job')?>" class="bg-btn-action btn-header border-radius" title="<?php _e('Post a Job', ET_DOMAIN)?>">
							<?php _e('POST A JOB', ET_DOMAIN );?><span class="icon f-right" data-icon="W"></span>
						</a>

					</div>
					<div class="ver-line"></div>
			<?php } else {

						if ($user_role == 'jobseeker' && function_exists('et_is_resume_menu') ) {
						?>
							<div class="post-job">
								<a href="<?php echo apply_filters ('je_filter_header_account_link', et_get_page_link('dashboard') ); ?>" class="bg-btn-action btn-header border-radius" title="<?php _e('Create a Resume', ET_DOMAIN)?>">
									<?php _e('REVIEW YOUR RESUME', ET_DOMAIN );?><span class="icon f-right" data-icon="W"></span>
								</a>

							</div>
							<div class="ver-line"></div>
					<?php
						}
					}
				} ?>



			<div class="account">
				<ul class="menu-header-top">
					<?php if ( et_is_logged_in() ){
						$roles	=	$current_user->roles;
						$role	=	array_pop($roles);
					 ?>
						<li <?php if(is_page_template('page-dashboard.php') || is_page_template('page-profile.php') || is_page_template('page-password.php')){ ?> class="selected" <?php } ?>>
							<a href="<?php echo apply_filters ('je_filter_header_account_link', et_get_page_link('dashboard') ) ; ?>" class="bg-btn-header btn-header" title="<?php echo $role == 'jobseeker' ? __('My profile', ET_DOMAIN) : __("Account",ET_DOMAIN);?>">
								<span class="icon" data-icon="U"></span>
							</a>
						</li>
						<li>
							<a id="requestLogout" href="<?php echo wp_logout_url( home_url() ); ?>" class="bg-btn-header btn-header" title="<?php _e('Logout', ET_DOMAIN);?>">
								<span class="icon" data-icon="Q"></span>
							</a>
						</li>
					<?php } else { ?>
						<li>
							<a id="requestLogin" class="login-modal bg-btn-header btn-header" rel="modal-box" href="#login" title="<?php _e('Login', ET_DOMAIN);?>">
								<span class="icon" data-icon="U" rel=""></span>
							</a>
						</li>
					<?php } ?>
				</ul>
			</div>

		</div>

	</div>
</div>