<?php
//wp_redirect( home_url() );
et_get_mobile_header('mobile'); ?>

<div data-role="content" class="resume-contentpage">

	<?php
		if(!is_user_logged_in()) {
			get_template_part( 'mobile/template/jobseeker' , 'register' );
		}
		get_template_part( 'mobile/template/resume' , 'form' );
	?>

</div>

<?php et_get_mobile_footer('mobile'); ?>