<?php 
/**
 * Template Name: Change Password
 */

global $current_user;
get_header(); ?>

<div class="heading">
	<div class="main-center">
		<!-- <div class="technical logout f-right">
			<a href="<?php echo wp_logout_url( home_url() ) ?>"><?php _e('LOGOUT', ET_DOMAIN);?> <span class="icon" data-icon="Q"></span></a>
		</div> -->
		<h1 class="title"><?php _e("Account",ET_DOMAIN);?></h1>
	</div>
</div>

<div id="page_change_password" class="wrapper account-jobs account-step">
	<div class="account-title">
		<div class="main-center clearfix">
			<ul class="account-menu font-quicksand">
				<?php do_action( 'je_before_company_info_tab') ?>
				<li><a href="<?php echo et_get_page_link('dashboard'); ?>"><?php _e('YOUR JOBS', ET_DOMAIN); ?></a></li>
				<li><a href="<?php echo et_get_page_link('profile'); ?>"><?php _e('COMPANY PROFILE', ET_DOMAIN); ?></a></li>
				<li><a href="<?php echo et_get_page_link('password'); ?>" class="active"><?php _e('PASSWORD', ET_DOMAIN); ?></a></li>
				<?php do_action( 'je_after_company_info_tab') ?>
			</ul>
		</div>
	</div>

	<?php
	global $current_user, $wp_query;
	?>

	<div class="main-center">
		<div class="full-column account-content">
			<div class="form-account">
				<form id="change_password" action="">
					<input type="hidden" name="id" value="<?php echo $current_user->ID ?>">
					<?php et_the_form( array(
						'user_old_pass' => array(
							'name' => 'user_old_pass',
							'input_id'	=> 'user_old_pass',
							'title' => __('Old Password', ET_DOMAIN),
							'input_class' => 'not_empty',
							'type' => 'password',
							),
						'user_pass' => array(
							'name' => 'user_pass',
							'input_id'	=> 'user_pass',
							'title' => __('New Password', ET_DOMAIN),
							'input_class' => 'not_empty is_pass',
							'type' => 'password',
							),
						'user_pass_again' => array(
							'name' => 'user_pass_again',
							'input_id'	=> 'user_pass_again',
							'title' => __('Retype New Password', ET_DOMAIN),
							'input_class' => 'not_empty is_pass_again',
							'type' => 'password',
							)
						)); ?>
					<div class="line-hr"></div>
					<div class="form-item">
						<input id="submit_profile" class="bg-btn-action border-radius" type="submit" value="<?php _e('SAVE CHANGE', ET_DOMAIN); ?>" />
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>