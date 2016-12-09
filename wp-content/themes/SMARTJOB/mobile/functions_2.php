<?php

function je_mobile_post_job ($request) {
     global $user_ID;
    // $response   =   ce_mobile_process_post_ad () ;

    $request['post_author'] =   $user_ID;
    //process location
    $request    =   je_process_job_location ($request);
    // process free post job
    $request    =   je_process_job_paid ($request);

    $request    =   apply_filters( 'je_process_job_tax', $request ) ;

    if( !isset($request['job_package']) ) {
    	return array ('success' => false, 'msg' => __("You must select a plan to post job.", ET_DOMAIN) );
	}

    $request['featured'] = (int)et_get_post_field( $request['job_package'], 'featured' );

    if( isset($request['is_free']) && $request['is_free'] ) {
        $response     =    je_limit_free_plan ();
        if($response) return $response;
    }

    $job_option	=	ET_JobOptions::get_instance();
	$useCaptcha	=	$job_option->use_captcha () ;
	if ( $useCaptcha && !current_user_can("manage_options") ) {
		$captcha	=	ET_GoogleCaptcha::getInstance();
		if( !$captcha->checkCaptcha( $request['recaptcha_challenge_field'] , $request['recaptcha_response_field']  ) ) {
			return array ('success' => false, 'msg' => __("You enter an invalid captcha!", ET_DOMAIN) );
		}
	}

    //exit;
    // process insert job
    $job_id =   et_insert_job($request);

    $author_data	= array(
			'ID'			=> $user_ID,
			'user_url' 		=> $request['user_url'],
			'display_name'	=> $request['display_name'],
			'recent_job_location' =>
					array (
						'location' 			=> $request['location'],
						'full_location'  	=> $request['full_location'],
						'location_lat' 		=> $request['location_lat'],
						'location_lng' 		=> $request['location_lng']
					),
			'apply_method'					=> $request['apply_method'],
			'apply_email'					=> $request['apply_email'],
			'applicant_detail'				=> $request['applicant_detail']
		);

    et_update_user( $author_data );

    // process post free or use package
    if( !is_wp_error($job_id) ) {
        if(isset($request['is_free']) && $request['is_free']) {
            $response['success']        =   true;
            $response['success_url']    =   et_get_page_link('process-payment', array ('paymentType' => 'free'));
            et_write_session('job_id' , $job_id) ;
            return $response;
        }

        if( isset($request['is_use_package']) && $request['is_use_package']) {
            $response['success']        =   true;
            $response['success_url']    =   et_get_page_link('process-payment', array ('paymentType' => 'usePackage'));
            et_write_session('job_id' , $job_id) ;
            return $response;
        }

        //$adID           =   $return->ID;
        $response['success']    =   true;
        $response['success_url']    = et_get_page_link('post-a-job' , array ('post_id' => $job_id ));
        return $response;
    }else {
        $response['success']    =   false;
        $response['msg']        =   $job_id->get_error_message();
    }

    return $response;
}

/**
 * Handle mobile here
 */
add_filter('template_include', 'et_template_mobile');
function et_template_mobile($template){
	global $user_ID, $wp_query, $wp_rewrite;
	$new_template = $template;

	// no need to redirect when in admin
	if ( is_admin() ) return $template;

	/***
	  * Detect mobile and redirect to the correlative layout file
	  */
	global $isMobile;
	$detector = new ET_MobileDetect();
	$isMobile = apply_filters( 'et_is_mobile', ($detector->isMobile()) ? true : false );

	if ( $isMobile  ) {

		$res_options 	= new JE_Resume_Options();
		if( is_page_template("page-jobseeker-signup.php") && !$res_options->get_resume_status() ){
			wp_redirect( home_url() ); exit;
		}

		$filename 		= basename($template);

		$child_path		= get_stylesheet_directory() . '/mobile' . '/' . $filename;
		$parent_path 	= get_template_directory() . '/mobile' . '/' . $filename;

		if ( file_exists($child_path) ){
			$new_template = $child_path;
		} else if ( file_exists( $parent_path )){
			$new_template = $parent_path;
		} else {
			$new_template = get_template_directory() . '/mobile/unsupported.php';
		}

		// some special page which are existed in main template
		if(!in_array($filename, array('header-mobile.php' , 'footer-mobile.php')) ) {
			if (is_page_template('page-login.php')){
				$new_template = get_template_directory() . '/mobile/page-login.php';
			} else if (is_page_template('page-register.php')){
				$new_template = get_template_directory() . '/mobile/page-register.php';
			}
		}
	}

	return $new_template;
}

/**
 * Mobile template for job items
 */
function et_template_mobile_job(){
	$variables = array();
	$template = <<<TEMPLATE
	<li data-icon="false" class="list-item">
		<span class="arrow-right"></span>
		<a data-ajax="false" href="{{permalink }}" >
			<p class="name">
				{{ title }}
			</p>
			<p class="list-function">
				<span class="postions">{{author}}</span>
				<# if ( job_types.length > 0 ) { #>
					<span class="type-job color-<# if (typeof job_types[0].color != 'undefined') { #>{{job_types[0].color}} <# } #>">
						<span class="flags flag<# if (typeof job_types[0].color != 'undefined') { #> {{job_types[0].color}} <# } #>"></span>
						<# _.each(job_types, function(type) { #>
							{{type.name}}
						<# }); #>
					</span>
				<# } #>
				<# if ( location != '' ) { #>
					<span class="locations"><span class="icon" data-icon="@"> </span>{{location}}</span>
				<# } #>
			</p>
		</a>
		<div class="mblDomButtonGrayArrow arrow">
			<div></div>
		</div>
	</li>
TEMPLATE;

	$template = apply_filters('et_mobile_job_template', $template);
	return $template;
}


/**
 * Mobile template for resume items
 */
function et_template_mobile_resume(){
	$variables = array();
	$template = <<<TEMPLATE
	<li class="resume-item" data-icon="false" class="clearfix"><span class="arrow-right"></span>
	<a href="{{permalink}}" data-transition="slide">
		<span class="thumb-img">
			<img src="{{jobseeker_data.et_avatar.thumbnail[0] }}">
		</span>
		<span class="intro-text">
		<span class="fix-middle">
			<h1>{{ post_title }}</h1>
			<p class="positions">{{et_profession_title}}</p>
			<p class="locations"><span class="icon-locations"></span>{{et_location}}</p>
		</span>
		</span>
	</a>
</li>
TEMPLATE;

	$template = apply_filters('et_mobile_job_template', $template);
	return $template;
}

/**
 *
 */
function et_mobile_resume_taxo_values($cat, $key = 'name'){
	return $cat->$key;
}


//add_filter ('option_page_on_front', 'filter_on_front_page') ;
function filter_on_front_page ($page_on_front) {
	global $isMobile;
	$detector = new ET_MobileDetect();
	$isMobile = apply_filters( 'et_is_mobile', ($detector->isMobile()) ? true : false );

	if ( $isMobile && $page_on_front ){ 
		return '';
	}
	return $page_on_front;
}

/*
 * load more post action
 */
add_action ('wp_ajax_et-mobile-load-more-post', 'et_mobile_load_more_post');
add_action ('wp_ajax_nopriv_et-mobile-load-more-post', 'et_mobile_load_more_post');
function et_mobile_load_more_post () {

	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );

	$page 		=	isset($_POST['page']) ? $_POST['page'] : 1;
	$template	=	isset($_POST['template']) ? $_POST['template'] : 'category';

	if( $template == 'date' ) {
		$query	=	new WP_Query( $_POST['template_value'].'&post_status=publish&paged='.$page);
	} else {
		$term	=	get_term_children($_POST['template_value'], 'category');
		$term[]	=	$_POST['template_value'];
		$term  	=	implode($term, ',');
		$args	=	array (
			'post_status'	=>	 'publish',
			'post_type'		=>	 'post',
			'paged' 		=> 	 $page ,
			'cat'			=>	 $term
		);
		$query	=	new WP_Query($args);
	}

	$data 	=	'';

	if($query->have_posts()) {
		while($query->have_posts()) {

			$query->the_post();
			global $post;
			$date		=	get_the_date('d S M Y');
			$date_arr	=	explode(' ', $date );

			$cat		=	wp_get_post_categories($post->ID);

			$cat		=	get_category($cat[0]);

	 		$data 		.= '<li>
                    <div class="infor-resume clearfix" style="border-bottom:none !important;">
                    	<span class="arrow-right"></span>
                        <div class="thumb-img" style="margin-left: 0px !important;">
                            <a href="'.get_author_posts_url($post->post_author).'">'.get_avatar( $post->post_author, 50 ).'</a>
                        </div>
                        <div class="intro-text">
                            <h1>'.get_the_author().'</h1>
                            <p class="blog-date">
                            '.get_the_date().' ,
                            	<span>
                                    <a href="'.get_category_link( $cat ).'" class="ui-link">
                                        '.$cat->name.'                                   </a> 
                                </span>&nbsp; &nbsp;
	                            <span class="blog-count-cmt">
	                            	<span class="icon" data-icon="q"></span>'.get_comments_number().'
	                            </span>
                            </p>
                        </div>
                    </div>
                    <div class="blog-content">
                        <a href="'.get_permalink().'" class="blog-title">
							'.get_the_title().'
                        </a>
                        <div class="blog-text">
                            '.get_the_excerpt().'
                        </div>
                    </div>
                </li>';
        }
        echo json_encode(array (
        	'data'		=>	$data,
        	'success'	=>	 true,
        	'msg'		=>	'',
        	'total'		=>  $query->max_num_pages
        ))	;
	} else {
	 		echo json_encode(array (
        	'data'		=>	$data,
        	'success'	=>	 false,
        	'msg'		=>	__('There is no posts yet.', ET_DOMAIN)
        ))	;
	}
	exit;
}

/**
 * depend on YOAST SEO add meta to mobile header
*/
add_action( 'et_mobile_head' ,  'et_mobile_seo_yoast' );
function et_mobile_seo_yoast() {
	if(class_exists('WPSEO_Frontend')) {
		// et_mobile_header
		try {
			$seo_yoast	=	new WPSEO_Frontend();
			$seo_yoast->head();
		} catch (Exception $e) {
		}
	}
}

add_action( 'et_mobile_head', 'add_linklein_api' );
function add_linklein_api () {
	/**
	 * add linkleIn Script
	*/
	$use_linkle		=	apply_filters( 'je_is_use_linkleIn_signup', true );
	$options		=	JE_Resume_Options::get_instance();
	$linkedin_api 	=	$options->get_linked_api();

	//$http	=	et_get_http();
	if($use_linkle && is_page_template('page-jobseeker-signup.php') && $linkedin_api != '') {
		echo 	'<script type="text/javascript" src="http://platform.linkedin.com/in.js">
				  	api_key: '.$linkedin_api.'
				  	scope: r_fullprofile r_emailaddress
				 </script>';
	}
}
/**
* move to resume/front.php file(don't use this funtion)
* @since 2.9.8
*/
//add_action( 'et_mobile_footer' , 'et_resume_template' );
function et_resume_template () {
?>
	<script type="text/template" id="education_template">
		<div class="education element">
			<div class="input-text-remind">
                <input type="text" class="name"   value="" placeholder="<?php _e('School name', ET_DOMAIN); ?>">
            </div>
            <div class="input-text-remind">
                <input type="text" class="degree"  value="" placeholder="<?php _e('Degree', ET_DOMAIN); ?>">
            </div>
            <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
            <div class="date-select">
            <?php
                JE_Helper::monthSelectBox('fromMonth' , false, array('class' => 'month fromMonth' ,  ) );
                JE_Helper::yearSelectBox('fromYear' , false, array('class' => 'year fromYear' ,  ) );
            ?>
            </div>
            <div class="clear" style="clear:both; height:18px; overflow:hidden;"><?php _e("to", ET_DOMAIN); ?></div>
            <div class="date-select">
            <?php
                JE_Helper::monthSelectBox('toMonth' , false, array('class' => 'month toMonth'  ) );
                JE_Helper::yearSelectBox('toYear' , false, array('class' => 'year toYear' ,  ) );
            ?>
            </div>
		    <div class="ui-checkbox signup">
		        <label for="education-{{ i }}" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-checkbox-off">
		        	<?php _e("I currently study here", ET_DOMAIN); ?>
		        </label>
		        <input type="checkbox" name="education-{{ i }}" id="education-{{ i }}" data-enhanced="true" class="curr">
		        <!-- <span class="icon icon-track" data-icon="#"></span> -->
		    </div>
		</div>
	</script>

	<script type="text/template" id="exp_template">
		<div class="experience element">
    		<div class="input-text-remind">
                    <input type="text" required class="name"  value="" placeholder="<?php _e('Company name', ET_DOMAIN); ?>">
                </div>
                <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
                <div class="input-text-remind">
                    <input type="text" required class="position"  value="" placeholder="<?php _e('Position', ET_DOMAIN); ?>">
                </div>
                <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
                <div class="date-select">
                <?php
                    JE_Helper::monthSelectBox('fromMonth' , false, array('class' => 'month fromMonth'  ) );
                    JE_Helper::yearSelectBox('fromYear' , false, array('class' => 'year fromYear' ) );
                ?>
                </div>
                <div class="clear" style="clear:both; height:18px; overflow:hidden;"><?php _e("to", ET_DOMAIN); ?></div>
                <div class="date-select">
                <?php
                    JE_Helper::monthSelectBox('toMonth' , false, array('class' => 'month toMonth'  ) );
                    JE_Helper::yearSelectBox('toYear' , false, array('class' => 'year toYear'  ) );
                ?>
                </div>
            <div class="ui-checkbox signup">
                <label for="experience-{{ i }}" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-checkbox-off">
                	<?php _e("I currently work here", ET_DOMAIN); ?>
                </label>
                <input type="checkbox" name="checkbox-enhanced" id="experience-{{i}}" data-enhanced="true" class="curr">
                <!-- <span class="icon icon-track" data-icon="#"></span> -->
            </div>
        </div>
    </script>
    <script type="text/template" id="skill_template">
    	<li class="element" ><span class="icon icon-track" data-icon="#"></span><span class="text">{{val}}</span><input class="skill" type="hidden" value="{{val}}" ></li>
    </script>
<?php
}
/**
* register new sidebar for mobile version.
* @since  2.9.8
*/
function je_add_mobile_sidebar() {
    register_sidebar( array(
        'name' => __( 'Mobile Sidebar', ET_DOMAIN ),
        'id' => 'top_mobile',
        'description' => __( 'Widgets in this area will be shown on all posts and pages.', ET_DOMAIN ),
        'before_title' => '<h5>',
        'after_title' => '</h5>',
        'before_widget' =>'',
        'after_widget' =>'',
    ) );
}
add_action( 'widgets_init', 'je_add_mobile_sidebar', 12 );
?>