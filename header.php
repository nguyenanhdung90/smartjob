<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?> >
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?> >
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?> >
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?> >
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<!-- Use the .htaccess and remove these lines to avoid edge case issues.
	More info: h5bp.com/i/378 -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
 	<meta name="viewport" content="width=device-width, initial-scale=1"  />
	 <title><?php wp_title( '|', true, 'right' );?></title>
	
	<meta name = "alexaVerifyID" content = "1qN4FGcM5bHSYuEmnRvYfcdrsBo" />
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php
	/*
	* Print the <title> tag based on what is being viewed.
	*/
	global $page, $paged, $current_user, $user_ID;
	?>
	<!-- <title><?php wp_title( '|', true, 'right' );?></title> -->
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<?php
		if ( is_singular() && get_option( 'thread_comments' ) )
			wp_enqueue_script( 'comment-reply' );

		$general_opts	= ET_GeneralOptions::get_instance();
		$favicon	= $general_opts->get_favicon();

		if($favicon){?>
			<link rel="shortcut icon" href="<?php echo $favicon[0];?>"/>
		<?php } ?>
	<!-- enqueue json library for ie 7 or below -->
	<!--[if LTE IE 7]>
		<?php wp_enqueue_script('et_json') ?>
	<![endif]-->
	<title><?php wp_title( '|', true, 'right' );?></title>
	<?php wp_head(); ?>
	<!--[if IE]>
		<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url')?>/css/custom-ie.css" charset="utf-8" />
	<![endif]-->

	<!--[if lte IE 8]>
		<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url')?>/css/custom-ie8.css" charset="utf-8" />
		<script src="<?php bloginfo('template_url')?>/js/cufon-yui.js" type="text/javascript"></script>
		<script src="<?php bloginfo('template_url')?>/js/Pictos_RIP_400.font.js" type="text/javascript"></script>
	<![endif]-->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-70168823-1', 'auto');
  ga('send', 'pageview');

</script>
<!-- Place this tag in your head or just before your close body tag. -->
<!-- Start Alexa Certify Javascript -->
<script type="text/javascript">
_atrk_opts = { atrk_acct:"sJaEm1akKd605T", domain:"smartjob.vn",dynamic: true};
(function() { var as = document.createElement('script'); as.type = 'text/javascript'; as.async = true; as.src = "https://d31qbv1cthcecs.cloudfront.net/atrk.js"; var s = document.getElementsByTagName('script')[0];s.parentNode.insertBefore(as, s); })();
</script>
<noscript><img src="https://d5nxst8fruw4z.cloudfront.net/atrk.gif?account=sJaEm1akKd605T" style="display:none" height="1" width="1" alt="" /></noscript>
<!-- End Alexa Certify Javascript -->  
<script src="https://apis.google.com/js/platform.js" async defer>
  {lang: 'vi'}
</script>

<!-- Start google map Javascript -->

<!-- end google map Javascript -->
</head>


<body <?php body_class()?> <?php if(is_home()) {?>style="margin-top:-2px"<?php }?><?php if(is_search()) {?>style="margin-top:-2px"<?php }?><?php if(is_archive()) { ?> style="margin-top:0px" <?php } ?>>
	<!-- Prompt IE 6 users to install Chrome Frame. Remove this if you support IE 6.
			 chromium.org/developers/how-tos/chrome-frame-getting-started -->
	<!--[if lt IE 7]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->
	<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v2.5&appId=726811420745180";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
	<header>
		<?php
		if( et_is_logged_in() ){ // insert current user data here for js
			$role	=	$current_user->roles;
			$user_role	=	array_pop($role);
			if( $user_role == 'company' || $user_role == 'administrator' )
				$user_data	=	et_create_companies_response($current_user);
			else
				$user_data	=	et_create_user_response($current_user);
		 ?>
			<script type="application/json" id="current_user_data">
				<?php echo json_encode( $user_data );?>
			</script>
		<?php }


		get_template_part( 'template/template', 'nav' );
		get_template_part( 'template/template' , 'breadcrumbs' );

		?>

		<div class="clear"></div>
	</header>