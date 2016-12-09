<!DOCTYPE html>
<html>
<head>
	<title><?php echo bloginfo('name'). __(' - Mobile version',ET_DOMAIN); ?></title>
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
	<meta name = "alexaVerifyID" content = "1qN4FGcM5bHSYuEmnRvYfcdrsBo" />
	<?php
		global $et_global , $current_user;

		// mobile icon for Apple devices
		$general_opts	= new ET_GeneralOptions();
		$mobile_icon	= $general_opts->get_mobile_icon();

		$mobile_css		=	TEMPLATEURL . '/mobile/css/customization.css';

		$job_option 	=   ET_JobOptions::get_instance();
    	$useCaptcha 	=   $job_option->use_captcha () ;

		if( is_multisite() ) {
			$site_id	=	get_current_blog_id();
			if($site_id == 1) {
				$mobile_css		=	TEMPLATEURL . '/mobile/css/customization.css';
			}
			else {
				$mobile_css		=	TEMPLATEURL . '/mobile/css/customization_'.$site_id.'.css';
			}

		}
		if ($mobile_icon){ ?>
			<link rel="apple-touch-icon" href="<?php echo $mobile_icon[0];?>"/>
		<?php
		}
		else{ ?>
			<!-- Standard iPhone -->
			<link rel="apple-touch-icon" sizes="57x57" href="<?php echo $et_global['imgUrl'];?>apple-touch-icon-57x57.png" />
			<!-- Retina iPhone -->
			<link rel="apple-touch-icon" sizes="114x114" href="<?php echo $et_global['imgUrl'];?>apple-touch-icon-114x114.png" />
			<!-- Standard iPad -->
			<link rel="apple-touch-icon" sizes="72x72" href="<?php echo $et_global['imgUrl'];?>apple-touch-icon-72x72.png" />
			<!-- Retina iPad -->
			<link rel="apple-touch-icon" sizes="144x144" href="<?php echo $et_global['imgUrl'];?>apple-touch-icon-144x144.png" />
		<?php
		}
		$minify		=	false;
		$heading 	= et_get_current_customization('font-heading');
		$text 		= et_get_current_customization('font-text');
		$action 	= et_get_current_customization('font-action');
		$fonts 		= apply_filters('define_google_font',array(
						'quicksand' => array(
							'fontface' 	=> 'Quicksand, sans-serif',
							'link' 		=> 'Quicksand'
						),
						'ebgaramond' => array(
							'fontface' 	=> 'EB Garamond, serif',
							'link' 		=> 'EB+Garamond'
						),
						'imprima' => array(
							'fontface' 	=> 'Imprima, sans-serif',
							'link' 		=> 'Imprima'
						),
						'ubuntu' => array(
							'fontface' 	=> 'Ubuntu, sans-serif',
							'link' 		=> 'Ubuntu'
						),
						'adventpro' => array(
							'fontface' 	=> 'Advent Pro, sans-serif',
							'link' 		=> 'Advent+Pro'
						),
						'mavenpro' => array(
							'fontface' 	=> 'Maven Pro, sans-serif',
							'link' 		=> 'Maven+Pro'
						),
					));
		$home_url	=	home_url();
		$http		=	substr($home_url, 0,5);
		if($http != 'https') {
			$http	=	'http';
		}
		foreach ($fonts as $key => $font) {
			if ( $heading == $font['fontface'] || $text == $font['fontface'] || $action == $font['fontface'] ){
				echo "<link href='".$http."://fonts.googleapis.com/css?family=" . $font['link'] . "' rel='stylesheet' type='text/css'>";
			}
		}

	?>
	<link rel="stylesheet" href="<?php bloginfo('template_url')?>/css/fonts/pictos.css">
	<?php if( $minify ) { ?>
		<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/mobile-style.min.css">
		<link rel="stylesheet" href="<?php echo $mobile_css; ?>">
	<?php } else { ?>
		<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/reset.css">
		<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/themes/engine-themes.min.css">
		<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/jquery.mobile.structure-1.3.1.min.css">

		<!-- <link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/style.css"> -->
		<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/jquery.mobile-1.3.1.min.css" />
		<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/jquery.style.css">
		<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/custom.css">
		<link rel="stylesheet" href="<?php bloginfo('template_url')?>/mobile/css/job-label.css">
		<link rel="stylesheet" href="<?php echo $mobile_css; ?>">
	<?php } ?>

	<script type="text/javascript">
		var et_globals = {
			"ajaxURL"    	: "<?php echo admin_url('admin-ajax.php');?>",
			"homeURL"    	: "<?php echo home_url();?>",
			"imgURL"    	: "<?php echo TEMPLATEURL . '/img';?>",
			"jsURL"     	: "<?php echo TEMPLATEURL . '/js';?>",
			"dashboardURL"  : "<?php echo et_get_page_link('dashboard');?>",
			"logoutURL"    	: "<?php echo wp_logout_url( home_url() );?>",
			"routerRootCompanies" : "<?php echo et_get_page_link('companies');?>",
			"use_captcha"		 : parseInt("<?php echo $useCaptcha ?>"),
			"numofpost"			: parseInt("<?php echo get_option('posts_per_page'); ?>")
		};

		<?php if(is_page_template( 'page-jobseeker-signup.php' ) || ( is_single() && get_post_type() == 'resume') ) { ?>
			var et_resume = {
				'date_range_invalid' : "<?php _e('End date is invalid.', ET_DOMAIN) ?>",
				'position_invalid'	 : "<?php _e(' Please enter your job title.', ET_DOMAIN) ?>",
				'from_date_invalid'  : "<?php _e(' Please select start date.', ET_DOMAIN) ?>",
				'to_date_invalid'	 : "<?php _e(' Please select end date.', ET_DOMAIN) ?>",
				'school_name_invalid' : "<?php _e('Please enter your school name.', ET_DOMAIN) ?>",
				'company_name_invalid' : "<?php _e(' Please enter your company name.', ET_DOMAIN) ?>"
			};
		<?php  } ?>

	</script>

	<?php do_action('et_mobile_head'); ?>
	<!-- jquery mobile include jquery  -->
	<script src="<?php bloginfo('template_url')?>/mobile/js/jquery.mobile-1.3.1.min.js"></script>
	<script type="text/javascript" src="<?php echo FRAMEWORK_URL . '/js/lib/underscore-min.js'?>"></script>

	<?php if( $minify ) { ?>
		<script type="text/javascript" src="<?php bloginfo('template_url')?>/mobile/js/mobile-script.min.js"></script>
	<?php } else { ?>

		<!-- jquery upload file
		<script src="http://blueimp.github.io/jQuery-File-Upload/js/vendor/jquery.ui.widget.js"></script>
		<script src="http://blueimp.github.io/jQuery-File-Upload/js/jquery.iframe-transport.js"></script>
		<script src="http://blueimp.github.io/jQuery-File-Upload/js/jquery.fileupload.js"></script>

		// jquery upload file -->

		<script type="text/javascript" src="<?php bloginfo('template_url')?>/mobile/js/script.js"></script>
		<script type="text/javascript" src="<?php bloginfo('template_url')?>/mobile/js/mobile_script.js"></script>

		<script type="text/javascript" src="<?php bloginfo('template_url')?>/mobile/js/jobseeker-signup.js"></script>

		<script type="text/javascript" src="<?php bloginfo('template_url')?>/mobile/js/mobile-resume.js"></script>
	<?php }
	if(is_page_template( 'page-post-a-job.php' ) ) {
	?>
		<script type="text/javascript" src="<?php bloginfo('template_url')?>/mobile/js/post-job.js"></script>
	<?php
	}

	if ( is_singular() && get_option( 'thread_comments' ) ) {
	?>
		<script type="text/javascript" src="<?php echo includes_url( 'js/comment-reply.js' );?>"></script>
	<?php } ?>
	<!-- GOOGLE ANALYTICS -->
	<?php
		echo $general_opts->get_google_analytics();
		$customize		=	$general_opts->get_customization();
		$website_logo	= $general_opts->get_website_logo(array('120', '50'));
		$res_options 	= new JE_Resume_Options();
	?>
	<!-- GOOGLE ANALYTICS -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-70168823-1', 'auto');
  ga('send', 'pageview');

</script>
<!-- Start Alexa Certify Javascript -->
<script type="text/javascript">
_atrk_opts = { atrk_acct:"sJaEm1akKd605T", domain:"smartjob.vn",dynamic: true};
(function() { var as = document.createElement('script'); as.type = 'text/javascript'; as.async = true; as.src = "https://d31qbv1cthcecs.cloudfront.net/atrk.js"; var s = document.getElementsByTagName('script')[0];s.parentNode.insertBefore(as, s); })();
</script>
<noscript><img src="https://d5nxst8fruw4z.cloudfront.net/atrk.gif?account=sJaEm1akKd605T" style="display:none" height="1" width="1" alt="" /></noscript>
<!-- End Alexa Certify Javascript -->  
<!-- Piwik 
<script type="text/javascript">
  var paq = paq || [];
  _paq.push(["setDomains", ["*.smartjob.vn"]]);
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//analytics.hotlink.com.vn/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 3]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<noscript><p><img src="//analytics.hotlink.com.vn/piwik.php?idsite=3" style="border:0;" alt="" /></p></noscript>
End Piwik Code -->
<!-- 
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v2.5&appId=726811420745180";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>-->  
</head>
<style type="text/css">
	/*.ui-header, .page-resume .job-navbar li .ui-btn.ui-btn-active .ui-btn-inner {
		background: <?php echo $customize['header']; ?>;
	}*/
</style>
<body class="body-mobile">
	<div data-role="page" id="main_page_load" <?php body_class( 'mobile page-resume ui-page-active' ) ?>>
	<div data-role="header" class="header-bar">
		<div>

		<h1 class="logo">
			<a href="<?php echo home_url(); ?>" rel="external" data-ajax="false" > 
				<img class="mobile-logo" alt="<?php echo get_bloginfo('name'); ?>" src="<?php echo $website_logo[0];?>" /> 
			</a>
		</h1>
		<?php
			if ( !is_user_logged_in() ) {
				echo '<a data-ajax="false" href="'.et_get_page_link('login').'" class="ui-btn-s icon ui-btn-right" data-role="none" data-icon="y"></a>';
			?>
				<div class="wrapper-btn-login">
					<a data-ajax="false" href="#" class="ui-btn-s icon ui-btn-right click-menu-mobile" data-role="none" data-icon="y"></a>
					<ul class="menu-mobile-wrapper">
						<li><a href="<?php echo et_get_page_link('login'); ?>" ><?php _e("Login", ET_DOMAIN); ?><span class="arrow-right"></span></a></li>
						<li><a data-ajax="false" href="<?php echo et_get_page_link('post-a-job'); ?>"><?php _e("Post a job", ET_DOMAIN); ?><span class="arrow-right"></span></a></li>
						<?php
						if($res_options->get_resume_status()){ ?>
							<li><a data-ajax="false" href="<?php echo et_get_page_link('jobseeker-signup'); ?>"><?php _e("Create a resume", ET_DOMAIN); ?><span class="arrow-right"></span></a></li>
						<?php } ?>
					</ul>
				</div>
			<?php
			}else{
				$role	=	$current_user->roles;
				$role	=	array_pop($role);
				?>
					<div class="wrapper-btn-login">
						<a data-ajax="false" href="#" class="ui-btn-s icon ui-btn-right click-menu-mobile" data-role="none" data-icon="y"></a>
						<ul class="menu-mobile-wrapper">
							<?php if($role == 'company' || current_user_can('manage_options') ) { ?>
							<li><a data-ajax="false" href="<?php echo et_get_page_link('post-a-job'); ?>"><?php _e("Post a job", ET_DOMAIN); ?><span class="arrow-right"></span></a></li>
							<?php } ?>
							<li>
								<a href="<?php echo apply_filters ('je_filter_header_account_link', et_get_page_link('dashboard') ); ?>">
									<?php 
									if($role == 'company' || current_user_can('manage_options') ) {
										_e("Dashboard", ET_DOMAIN); 
									} else {
										_e("Review your resume", ET_DOMAIN); 
									}
									?>
									<span class="arrow-right"></span>
								</a>
							</li>

							<li><a class="et_logout" href="#"><?php _e("Logout", ET_DOMAIN); ?><span class="arrow-right"></span></a></li>
						</ul>
					</div>

				<?php 
			}
		?>
		</div>
	</div><!-- /header -->

	<?php if (!is_category() && (is_home() || is_archive() ) && function_exists('et_is_resume_menu') ){ ?>
	<div data-role="navbar" class="job-navbar">
	    <ul>
	        <li>
	        	<a data-ajax=""  href="<?php if( get_option( 'page_on_front' ) ) echo get_post_type_archive_link( 'job' ); else echo home_url(); ?>" class="font-quicksand  <?php if (is_home() || (is_post_type_archive('job') || is_author()  )) echo ' ui-btn-active ui-state-persist' ?>">
	        	<?php _e('JOBS', ET_DOMAIN) ?>
		        </a>
		    </li>
	        <li>
	        	<a data-ajax=""  href="<?php echo get_post_type_archive_link( 'resume' ) ?>" class="font-quicksand <?php if (is_post_type_archive('resume') ) echo ' ui-btn-active ui-state-persist' ?>">
	        		<?php _e('RESUMES', ET_DOMAIN) ?>
	        	</a>
	        </li>
	    </ul>
    </div><!-- /navbar -->
    <?php } ?>
