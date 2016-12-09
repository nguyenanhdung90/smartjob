<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<!-- Use the .htaccess and remove these lines to avoid edge case issues.
			 More info: h5bp.com/i/378 -->
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<!-- <meta name="viewport" content="width=device-width" /> -->
<meta name="description" content="<?php echo bloginfo('description')?>" />
<meta name="keywords" content="Job, Jobs, company, employer, employee" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged, $current_user, $user_ID;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', ET_DOMAIN ), max( $paged, $page ) );

	?></title>
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<!--[if lte IE 8]> <link rel="stylesheet" type="text/css" href="css/lib/ie.css" charset="utf-8" /> <![endif]-->
<?php wp_head(); ?>
</head>
<body <?php body_class()?>>

	<div class="head404">
		<div class="main-center a-center"><?php _e('Error #404', ET_DOMAIN);?></div>
	</div>
	<div class="header404">
		<div class="main-center a-center"><?php _e('Page Not found :(',ET_DOMAIN); ?></div>
	</div>
	<div class="main-center a-center main404">
		<p>
		<?php  
			_e('Sorry, but the page you were trying to view does not exist.</p><p>It looks like this was the result of either:</p><p>- a mistyped address<br />	- an out-of-date link', ET_DOMAIN);
		?>
		</p>
		<p><?php _e('Other things to try:', ET_DOMAIN);?></p>
		<p><?php _e('Search', ET_DOMAIN);?> <strong id="google-url"><?php echo home_url(); ?></strong>:</p>
		<div class="input-text search404">
			<form action="http://google.com/search" target="_blank" method="get" id="google-search">
				<input type="hidden" name="q" id="q" value="site:<?php echo home_url(); ?> " />
			</form>		
			<span class="icon" data-icon="s"></span>
		</div>
	</div>
	<div class="main-center a-center">
		<a href="<?php echo home_url(); ?>" class="backhome-btn">
			<?php _e('Return to Home', ET_DOMAIN);?>
		</a>
	</div>

  	<footer class="bg-footer footer404">
		<div class="main-center">
			<div class="f-left f-left-all">
			<?php 
				$general_opt	=	ET_GeneralOptions::get_instance();
				$copyright		=	$general_opt->get_copyright();
				if( $copyright != '') {
			?>
				<div class="copyright"><?php echo $copyright; ?></div>
			<?php }?>
				<?php 
					if(has_nav_menu('et_footer'))
						wp_nav_menu(array (
								'theme_location' => 'et_footer',
								'container' => 'ul',
								'menu_class'	=> 'menu-bottom'
							))?>
			</div>
			<div class="f-right f-left-all">				
				<?php et_follow_us() ?>
			</div>
		</div>
	</footer>
<?php wp_footer(); ?>
</body>
</html>
