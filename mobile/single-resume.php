<?php
global $post , $user_ID , $resume , $jobseeker;

$resume_options	=	JE_Resume_Options::get_instance();
if( !$user_ID && $resume_options->et_resumes_priavcy ) {
	$redirect_link	=	et_get_page_link('login' , array ('redirect_url' => get_permalink( $post->ID )));
	wp_redirect( $redirect_link );
}

et_get_mobile_header('mobile');


if(have_posts()) {
	the_post ();

	$jobseeker 	= get_userdata($post->post_author);
	$jobseeker 	= JE_Job_Seeker::convert_from_user($jobseeker);
	$resume 	= JE_Resume::convert_from_post($post);


if( 	isset($_GET['edit']) &&  $_GET['edit'] != '' && $resume->ID == $_GET['edit']
			&& ( current_user_can( 'manage_options' ) ||  $resume->post_author == $user_ID )
	) {
	// template edit resume
	get_template_part( 'mobile/template/resume' , 'editform' );

} else {

	if ( $jobseeker->et_contact || !isset($_GET['action']) || $_GET['action'] != 'send_message' ){
?>
	<div data-role="content" class="resume-contentpage">
		<h1 class="title-resume">
			<?php printf( __("Profile of %s",ET_DOMAIN), $jobseeker->display_name); ?>

			<?php if(current_user_can( 'manage_options' ) ||  $resume->post_author == $user_ID) { ?>
				<span class="step-number"><a href="<?php echo add_query_arg(array ('edit' => $post->ID) ); ?>" ><?php _e("Edit", ET_DOMAIN); ?> </a></span>
			<?php } ?>

		</h1>
		<div class="infor-resume inset-shadow clearfix">
			<div class="thumb-img">
	    		<?php echo et_get_resume_avatar($jobseeker->ID, 50); ?>
	    	</div>
	    	<div class="intro-text">
	    		<h1><?php the_title(); ?></h1>
	    		<p class="positions"><?php echo $jobseeker->et_profession_title ?></p>
	    	</div>
		</div>
		<!-- jobseeker location -->
		<?php if ( $resume->et_location != '') { ?>
			<div class="content-info">
				<span class="arrow-right"></span>
				<a class="list-link job-loc" href="<?php echo home_url(); ?>?post_type=resume&location=<?php echo $resume->et_location; ?>" rel="external" data-transition="slide" id="com_location">
					<span class="icon-locations"></span><?php echo $jobseeker->et_location; ?>
				</a>
			</div>
		<?php } ?>

		<!-- user url -->
		<?php if (!empty($resume->et_url)){ ?>
			<div class="content-info">
				<a class="list-link job-loc" href="<?php echo $resume->et_url ?>" rel="external" data-transition="slide">
					<span class="link-website"><span class="icon-link-website"></span><?php echo $resume->et_url ?></span>
				</a>
				<span class="arrow-right"></span>
			</div>
		<?php } ?>

		<!-- jobseeker available for -->
		<?php if (!empty($resume->available) ) {
			$values = array_map('et_mobile_resume_taxo_values',$resume->available); ?>
			<div class="content-info">
				<span class="arrow-right"></span>
				<a class="list-link job-loc" href="<?php echo home_url(); ?>?post_type=resume&available=<?php echo implode(',', $values); ?>" rel="external" data-transition="slide" id="com_location">
					<span class="icon-flags"></span><?php echo implode(', ', $values) ?>
				</a>
			</div>
		<?php } ?>

		<!-- jobseeker about  -->
		<?php $content = get_the_content( );
		if($content != '') {
		?>
		<div class="content-info content-text">
			<h1><?php _e('ABOUT ME', ET_DOMAIN) ?></h1>
			<?php echo get_the_content(); ?>
		</div>
		<?php } ?>
		<?php if (!empty($resume->et_education)) { ?>
			<div class="content-info content-text content-timeline">
				<h1 class="line"><?php _e('EDUCATION', ET_DOMAIN) ?></h1>
				<?php foreach ($resume->et_education as $key => $item) { ?>
					<div class="line-stand">
						<span class="dotted"></span>
						<div class="intro">
							<span class="year"><?php echo $item['from']['display'] ?> - <?php echo $item['to']['display'] ?></span><br />
							<span class="name"><?php echo $item['name'] ?></span>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php } ?>

		<!-- jobseeker experience -->
		<?php if (!empty($resume->et_experience)) { ?>
			<div class="content-info content-text content-timeline">
				<h1 class="line"><?php _e('WORK EXPERIENCE', ET_DOMAIN) ?></h1>
				<?php foreach ($resume->et_experience as $key => $item) { ?>
					<div class="line-stand">
						<span class="dotted"></span>
						<div class="intro">
							<span class="year"><?php echo $item['from']['display'] ?> - <?php echo $item['to']['display'] ?></span><br />
							<span class="name"><?php echo $item['name'] ?></span>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php } ?>

		<!-- jobseeker skill  -->
		<?php if (!empty($resume->skill)) { ?>
		<div class="content-info content-text skill">
			<h1 class="line"><?php _e('Skill', ET_DOMAIN) ?></h1>
			<?php $skills = array_map('et_mobile_resume_taxo_values', $resume->skill) ?>
			<p>
			<?php echo implode('</p><p>', $skills) ?>
			</p>
		</div>
		<?php }
		 do_action('je_resume_show_fields_on_detail',$resume);
		// contact jobseeker button
		if( !$jobseeker->et_contact ) { ?>
		<div class="content-info content-text">
			<a data-ajax="false" href="<?php echo add_query_arg('action', 'send_message') ?>" data-role="button" class="btn_contact"><?php _e("CONTACT", ET_DOMAIN); ?></a>
		</div>
		<?php } ?>

	</div><!-- /content -->

	<div class="share-social">
		<h1><?php _e('Share',ET_DOMAIN); ?></h1>
		<ul>
			<li>
				<a href="http://twitter.com/home?status=<?php the_title(); ?> - <?php the_permalink(); ?>" class="ui-link">
					<span class="icon-tw"></span><?php _e('Tweet this profile',ET_DOMAIN); ?>
				</a>
			</li>
			<li>
				<a href="http://www.facebook.com/sharer.php?u=<?php the_permalink();?>&t=<?php the_title(); ?>" class="ui-link">
				<span class="icon-fb"></span><?php _e('Share on Facebook',ET_DOMAIN); ?>
				</a>
			</li>
			<li>
				<a href="mailto:type email address here?subject=share this post from <?php echo bloginfo('name'); ?>&body=<?php the_title(); ?>&#32;&#32;<?php the_permalink(); ?>" class="ui-link">
					<span class="icon-mail"></span><?php _e('Send via Email',ET_DOMAIN); ?>
				</a>
			</li>
		</ul>
	</div>
	<?php }  // end if not action "sending message"
	// sending message
	else { ?>
		<div data-role="content" class="post-content">
			<h1 class="post-title job-title">
				<?php
					printf( __('Message %s', ET_DOMAIN), $jobseeker->display_name);
				?>
			</h1>
			<?php
			$sender = array(
				'name' => empty($current_user->display_name) ? "" : $current_user->display_name,
				'email' => empty($current_user->user_email) ? "" : $current_user->user_email
				);
			?>
			<form action="" id="jobseeker_message" data-ajax="false" method="post">

				<input type="hidden" name="receive" value="<?php echo $resume->post_author ?>">
				<div class="content-field inset-shadow">
					<h3><?php _e('Your name',ET_DOMAIN); ?></h3>
					<div class="input-text">
						<input type="text" name="sender_name" autocomplete="off" value="<?php echo $sender['name'] ?>" required >
					</div>
					<h3><?php _e('Email address',ET_DOMAIN); ?></h3>
					<div class="input-text">
						<input type="email" name="sender_email" autocomplete="off" value="<?php echo $sender['email'] ?>" required="email" class="email" >
					</div>
					<h3><?php _e('Message',ET_DOMAIN); ?></h3>
					<div class="input-text">
						<textarea name="message" id="" cols="30" rows="10" required></textarea>
					</div>
				</div>
				<?php do_action( 'je_mobile_captcha' ); ?>
				<div class="content-field f-padding">
					<div class="input-button">
						<input type="submit" class="send" value="<?php _e('Send',ET_DOMAIN); ?>">
					</div>
					<div class="clearfix"></div>
				</div>
				<input type="hidden" id="cancel_url" value="<?php echo remove_query_arg( 'action' ) ?>">

			</form>
			<div data-role="popup" class="msg-success msg-pop" id="msg_pop">
				<p><p>
			</div>
		</div><!-- /content -->
	<?php }
} ?>
<?php } // end if have posts ?>
<?php et_get_mobile_footer('mobile'); ?>