<?php
class JE_Short_Code {
	function __construct() {
		// add short code render latest job
		add_shortcode ('latest_job' , array($this, 'latest_job') );
		//add_shortcode ('latest_resume' , array($this, 'latest_resume') );
		//add_shortcode( 'login_form' , array($this, 'login_form') );
	}

	/**
	 * function call back for short code lates_job
	*/
	function latest_job ( $atts ) {
		$a = shortcode_atts( array(
	        'showposts' 		=> 5,
	        'title' 		=> '',
	        'featured'		=> 0,
	        'post_type'		=> 'job',
	        'orderby'		=> 'date'
	    ), $atts );

		if($a['featured'])	{
			$a['meta_key']	=	'et_featured';
			$a['meta_value']	=	'1';
		}
		$latest	=	new WP_Query($a);

		if( $a['title'] != '') {
			echo '<h3 class="main-title">'. $a['title'] .'</h3>';
		}
		echo '<ul class="list-jobs job-account-list joblist-shortcode">';
		while ($latest->have_posts()) { $latest->the_post();
			global $job, $post;
			$job	= et_create_jobs_response($post);
			get_template_part( 'template' , 'job' );
		}
		echo '</ul>';

	}

	function login_form () {
		get_template_part( 'template/form', 'login' );
	}

}

new JE_Short_Code();
