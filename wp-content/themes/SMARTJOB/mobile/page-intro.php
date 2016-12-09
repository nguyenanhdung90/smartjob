<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>" />
        <!-- Use the .htaccess and remove these lines to avoid edge case issues.
                     More info: h5bp.com/i/378 -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <!--    <meta name="viewport" content="width=device-width, initial-scale=1"  /> -->
        <meta name="description" content="<?php echo get_bloginfo( 'description') ?>" />
        <meta name="keywords" content="Job, Jobs, company, employer, employee" />
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <title><?php 
            /*
             * Print the <title> tag based on what is being viewed.
             */
            global $page, $paged, $current_user, $user_ID;

            wp_title( '|', true, 'right' );
            // Add the blog description for the home/front page.
            $site_description = get_bloginfo( 'description', 'display' );

        ?></title>
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
        <?php
            $general_opts   = new ET_GeneralOptions();
            $favicon    = $general_opts->get_favicon();
            $website_logo   = $general_opts->get_website_logo();
            if($favicon){
            ?>
                <link rel="shortcut icon" href="<?php echo $favicon[0];?>"/>
        <?php } ?>
        <!-- enqueue json library for ie 7 or below -->
        <!--[if LTE IE 7]>
            <?php wp_enqueue_script('et_json') ?>
        <![endif]-->
        <?php wp_head(); ?>

        <!--[if IE]>
            <link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url')?>/css/custom-ie.css" charset="utf-8" />
        <![endif]-->

        <!--[if lte IE 8]>
            <link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url')?>/css/custom-ie8.css" charset="utf-8" />
            <script src="<?php bloginfo('template_url')?>/js/cufon-yui.js" type="text/javascript"></script>
            <script src="<?php bloginfo('template_url')?>/js/Pictos_RIP_400.font.js" type="text/javascript"></script>
        <![endif]-->
        <style type="text/css">

        </style>
    </head>
    <?php if(!is_user_logged_in()) {
        $job_link   =  et_get_page_link('post-a-job');
        $job_title  =  __("POST A JOB", ET_DOMAIN) ;

        $resume_link  = et_get_page_link('jobseeker-signup');
        $resume_title = __("CREATE A RESUME", ET_DOMAIN);

    }else {
        global $current_user;
        $roles  =   $current_user->roles;
        $role   =   array_pop ($roles);
        if($role != 'jobseeker') {

            $job_link   =  et_get_page_link('post-a-job');
            $job_title  =  __("POST A JOB", ET_DOMAIN) ;

            $resume_link  = get_post_type_archive_link('resume');
            $resume_title = __("SEARCH A RESUME", ET_DOMAIN);

        } else {

            $job_link   =  get_post_type_archive_link('job');
            $job_title  =  __("SEARCH A JOB", ET_DOMAIN) ;

            $resume_link  = et_get_page_link('jobseeker-signup');
            $resume_title = __("REVIEW YOUR RESUME", ET_DOMAIN);
        }

    }

    $job_link   =   get_post_type_archive_link('job');
    if(function_exists( 'et_is_resume_menu' ))
        $job_title  =  __("FIND A JOB", ET_DOMAIN) ;
    else 
        $job_title  =  __("GO TO JOBLIST", ET_DOMAIN) ;

    $resume_link  = get_post_type_archive_link('resume');
    $resume_title = __("SEARCH A RESUME", ET_DOMAIN);

    //if( function_exists( 'et_is_resume_menu' ) )
     ?>

    <body data-page="main" <?php body_class(); ?>>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->
        <!-- Preloading -->

        <div class="mask-color">
            <div id="preview-area">
                <div class="logo-image-preloading"><img src="<?php echo TEMPLATEURL; ?>/img/preloading-logo.png" alt="EngineTheme"></div>
                <div class="spinner">
                  <div class="bounce1"></div>
                  <div class="bounce2"></div>
                  <div class="bounce3"></div>
                </div>
            </div>
            <div class="page-main"></div>
            <div class="page-left"></div>
            <div class="page-right"></div>
        </div>
        <!-- Preloading / End -->
        <!-- Header -->
        <header data-size="small">
            <div class="container">
                <div class="logo">
                    <a href="<?php echo home_url()?>" class="logo">
                        <img src="<?php echo $website_logo[0];?>" alt="<?php echo $general_opts->get_site_title();  ?>" />
                    </a>
                </div>

                <div class="btn-group">
                    <a data-ajax="false" href="<?php echo $job_link ?>" class="btn btn-red"><?php echo $job_title; ?></a>
                    <?php if(function_exists( 'et_is_resume_menu' )) { ?>
                    <span class="or">or</span>
                    <a data-ajax="false" href="<?php echo $resume_link; ?>" class="btn btn-blue"><?php echo $resume_title; ?></a>
                    <?php } ?>
                </div>
            </div>
        </header>
        <!-- Header / End -->

        <!-- Slider -->
        <div class="slider-wrapper">
            <div class="container">
                <div class="clearfix" style="height:30px;"></div>
                <div class="logo">
                    <a href="<?php echo home_url()?>" class="logo">
                        <img src="<?php echo $website_logo[0];?>" alt="<?php echo $general_opts->get_site_title();  ?>" />
                    </a>
                </div>
               <!--  <nav class="menu">
                    <?php
                        je_header_menu ();
                     ?>
                </nav> -->

                <div class="center">
                    <h1 class="top">
                    <?php $des  =   $general_opts->get_site_demonstration ();

                    if( $des != '' ) { ?>
                        <span><?php _e("JOBENGINE DEMONSTRATION", ET_DOMAIN); ?></span><br />
                        <?php echo $des; ?>
                    <?php } ?>
                    </h1>

                    <div class="btn-group">
                        <a data-ajax="false" href="<?php echo $job_link ?>" class="btn btn-red"><?php echo $job_title; ?></a>
                        <?php if(function_exists( 'et_is_resume_menu' )) { ?>
                        <span class="or">or</span>
                        <a data-ajax="false" href="<?php echo $resume_link; ?>" class="btn btn-blue"><?php echo $resume_title; ?></a>
                        <?php } ?>
                    </div>

                </div>
            </div>
        </div>
        <!-- Slider / End -->

        <!-- Page content -->
        <?php
        if(have_posts()) { the_post();
            global $post;
            if($post->post_content != ''){
                the_content ();
            }  else {
            ?>
                <div class="wrapper">
                    <div class="container">
                        <div class="wrapper-timeline">
                            <h2 class="title-top">Bussiness Feature</h2>
                            <div class="box-wrapper">
                                <span class="line"></span>
                                <div class="content">
                                    <span class="icon " data-icon="O">
                                    <h2 class="title"></span>Job Management</h2>
                                    <p>Sort your listings by location,
                                    job type and category.</p>
                                </div>
                            </div>
                            <div class="box-wrapper">
                                <span class="line"></span>
                                <div class="content">
                                    <span class="icon " data-icon="e"></span>
                                    <h2 class="title">Ajax Job Filters</h2>
                                    <p>Refine your search criteria without reloading the page.</p>
                                </div>
                            </div>
                            <div class="box-wrapper">
                                <span class="line"></span>
                                <div class="content">
                                    <span class="icon " data-icon="'"></span>
                                    <h2 class="title"> Apply Online</h2>
                                    <p>Upload a CV and send applications directly from the site.</p>
                                </div>
                            </div>
                            <div class="box-wrapper">
                                <span class="line"></span>
                                <div class="content">
                                    <span class="icon" data-icon="M">
                                    <h2 class="title">Email Templates</h2>
                                    <p>Send automated messages to advertisers and jobseekers.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
        }
        ?>
        <!-- Page content / End -->

        <script >
            jQuery(document).ready(function($) {
                $('.mask-color').fadeOut(1700);
                var height = $('.slider-wrapper').height();
                $(window).scroll(function(){
                    var st = $(this).scrollTop();
                    if ( st > height){
                            $('header').stop().css({'display':'block'});
                    }  else  {
                            $('header').stop().css({'display':'none'});
                    }
                });

            });

        </script>

    <div class="ui-footer">
        <?php
            $general_opt    =   new ET_GeneralOptions();
            $copyright      =   $general_opt->get_copyright();
            if( $copyright != '') {
                echo '<p><strong>'.$copyright.'</strong></p>';
            }
        if(has_nav_menu( 'et_mobile_footer' ))
            wp_nav_menu(array (
                    'theme_location' => 'et_mobile_footer',
                    'container' => 'ul',
                    'menu_class'    => 'menu-bottom',
            )); ?>
    </div>

    </div>
    <?php wp_footer() ?>
</body>
</html>