<?php 
/**
 * Template Name: Job Seeker Account
 */
global $user_ID, $current_user, $wp_query;
if( !function_exists( 'et_is_resume_menu') || !is_user_logged_in() || current_user_can( 'manage_options' )) {
	wp_redirect( home_url(  ) );
}


get_header(); 

$resume_option	=	new JE_Resume_Options();

$ower_resume	=	JE_Resume::get_resumes(array('author'=> $user_ID, 'post_status' => array('publish', 'pending') ));
$jobseeker		=	JE_Job_Seeker::convert_from_user($current_user);
$accessible_list =	$jobseeker->et_accessible_companies;

$ower_resume	=	$ower_resume[0];

?>

<div class="heading">
	<div class="main-center">
		<h1 class="title"><?php _e("Profile Settings",ET_DOMAIN);?></h1>
	</div>
</div>
<div class="tab-account">
	<div class="main-center">
		<ul class="tabs">
			<li rel="jse-account" class="account active"><?php _e("ACCOUNT", ET_DOMAIN); ?><span class="arrow-up"></span></li>
			<li rel="jse-privacy" class="privacy"><?php _e("PRIVACY", ET_DOMAIN); ?><span class="arrow-up"></span></li>
		</ul>
		<a href="<?php echo get_permalink( $ower_resume->ID ) ?>" title="<?php __("View your Resume", ET_DOMAIN) ?>" class="view-profile"><?php _e("View your Resume", ET_DOMAIN); ?></a>
	</div>
</div>

<div class="wrapper jobseeker" >

	<div class="main-center">
		<div class="jse-account active content-tab" id="jse-account">
			<div class="jse-account-form jse-form" >
				<form action="" id="update_account">
					<input type="hidden" name="ID" value="<?php echo $current_user->ID ?>">
					<div class="jse-input">
						<label><?php _e('Email Address', ET_DOMAIN) ?></label>
						<input type="text" name="user_email" class="bg-default-input" value="<?php echo $current_user->user_email ?>"  />
					</div>
					<div class="jse-input">
						<label><?php _e('Password', ET_DOMAIN); ?></label>
						<input type="password" id="user_pass" name="user_pass" class="bg-default-input"  />
					</div>
					<div class="jse-input">
						<label><?php _e('Retype your password', ET_DOMAIN) ?></label>
						<input type="password" name="user_pass_again" class="bg-default-input"  />
					</div>
					<div class="space-sep"></div>
					<div class="jse-input">
						<label><?php _e('To save these settings, please enter your current password', ET_DOMAIN) ?></label>
						<input type="password" name="current_pass" class="bg-default-input"  />
					</div>
					<div class="jse-submit">
						<input class="bg-btn-action border-radius" type="submit" value="<?php _e('SAVE CHANGE', ET_DOMAIN) ?>"  />
					</div>
				</form>
			</div>
		</div><!--account-->

		<div class="jse-privacy content-tab" id="jse-privacy">
			<!-- contact setting -->
			<div class="jse-contact" >
				<?php $blog_name	=	get_bloginfo( 'name' ); ?>
				<h1><?php printf(__("Receive email from %s", ET_DOMAIN),$blog_name ); ?></h1>
				<p><?php printf(__("When you turn this on, employers can email you through the 'Contact' form in your resume.", ET_DOMAIN), $blog_name) ; ?></p>
				<div class="jse-button clearfix">
					<div class="toggle-button jse-button-enable <?php if( !$jobseeker->et_contact ) echo 'active' ; ?> ">
						<span><?php _e("ENABLE", ET_DOMAIN); ?></span>
					</div>
					<div class="toggle-button jse-button-disable <?php if( $jobseeker->et_contact ) echo 'active' ; ?>">
						<span><?php _e("DISABLE", ET_DOMAIN); ?></span>
					</div>
				</div>
			</div>

			<div class="confidential">
				<h1><?php _e("Confidential Mode", ET_DOMAIN); ?></h1>
				<p><?php _e("When you turn this on, your profile still appears in the listings but your name, picture, social links, and work experience will be hidden from the employers.<br/> Only companies that receive your application will be able to view your full profile.", ET_DOMAIN); ?></p>
				<div class="jse-button clearfix">
					<div class="toggle-button jse-button-enable <?php if($jobseeker->et_privacy == 'confidential') echo 'active' ; ?> ">
						<span><?php _e("ENABLE", ET_DOMAIN); ?></span>
					</div>
					<div class="toggle-button jse-button-disable <?php if($jobseeker->et_privacy != 'confidential') echo 'active' ; ?>">
						<span><?php _e("DISABLE", ET_DOMAIN); ?></span>
					</div>
				</div>
				<div id="list-accessible" <?php if($jobseeker->et_privacy != 'confidential') echo 'style="display: none;"' ; ?>>
					<h1><?php _e("Privileged Companies", ET_DOMAIN); ?></h1>
					<p><?php _e("These companies are currently able to view your full profile. You can choose to remove them from the list.", ET_DOMAIN); ?></p>
					<ul class="jse-list-company">
						<?php

						if(empty($accessible_list)) {
							_e("<h3>You have not allowed any company to view your profile.</h3>", ET_DOMAIN);
						}else 
						foreach ($accessible_list  as $key => $value) {
							$company	=	et_create_companies_response($value);
						?>
						<li data-company="<?php echo $value ?>">
							<div class="jse-remove">
								<a href="#" title="<?php printf(__("Remove %s from your accessible list", ET_DOMAIN) , $company['display_name']) ?>"><?php _e("Remove", ET_DOMAIN); ?><span class="icon" data-icon="#"></span></a>
							</div>
							<div class="thumb">
								<a href="<?php echo $company['post_url'] ?>" ><img src="<?php echo $company['user_logo']['small_thumb'][0] ?>" /></a>
							</div>
							<div class="content">
								<a class="title" href="<?php echo $company['post_url'] ?>"><?php echo $company['display_name'] ?></a>
								<div class="location">
									<a href="<?php echo $company['post_url'] ?>"><span class="icon" data-icon="@"></span><?php echo $company['recent_location']['full_location'] ?></a>
								</div>

							</div>
						</li>
						<?php } ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<script	type="application/json" id="current_user_data">
	<?php echo json_encode($jobseeker); ?>
</script>

<?php get_footer(); ?>