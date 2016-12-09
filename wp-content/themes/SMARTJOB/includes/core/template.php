<?php
/**
 * template.php
 * Handle all actions relate to redirection, page template, ...
 * @since 1.0
 */

/**
 * Register page template which will be create when the theme's installed
 * @since 1.0
 */
function et_register_page_template( array $pages ){
	global $et_global;
	$et_global['page_templates'] =  $et_global['page_templates'] + $pages;
}

/**
 * Verify if any page template is missing
 * @since 1.0
 */
function et_verify_pages(){
	global $et_global;

	$missing = array();

	foreach ((array)$et_global['page_templates'] as $slug => $name) {
		if ( !is_string( $name ) ) continue;

		$pages = get_posts( array('post_type' => 'page' , 'meta_key' => '_wp_page_template' ,'meta_value' => 'page-'.$slug.'.php' ) );

		if ( empty($pages) ){
			$missing[$slug] = $name;
		}
	}
	if ( empty( $missing ) ){
		delete_transient($missing);
	} else {
		set_transient('missing_pages', $missing);
	}
	return $missing;
}

/**
 * Create templates files registered when theme active
 * @since 1.0
 */
function et_generate_templates(){
	global $et_global;

	foreach ((array)$et_global['page_templates'] as $slug => $name) {
		if ( !is_string( $name ) ) continue;

		$pages = get_posts( array('post_type' => 'page' , 'numberposts' => 1, 'meta_key' => '_wp_page_template' ,'meta_value' => 'page-'.$slug.'.php' ) );

		if ( empty($pages) ){
			// insert page template
			$id = wp_insert_post(array(
				'post_title' => $name,
				'post_content' => '',
				'post_type' => 'page',
				'post_status' => 'publish'
			));
			// insert meta data
			update_post_meta($id, '_wp_page_template', 'page-' . $slug . '.php');
		}elseif( is_array($pages) ) {
			$page = array_shift($pages);
		}
	}
}

/**
 *
 * Get the login/register page link. If the login/register page doesn't exist, it will create a new page.
 * @param int $page_type: login or register
 * @return $link
 * @author Dakachi
 * @version 1.0
 * @copyright enginethemes.com team
 * @package white panda
 *
 *
 */
function et_get_page_link($pages, $params = array() , $create = true) {

    $page_args = array(
        'post_title' => '',
        'post_content' => __('Please fill out the form below ', ET_DOMAIN) ,
        'post_type' => 'page',
        'post_status' => 'publish'
    );

    if (is_array($pages)) {

        // page data is array (using this for insert page content purpose)
        $page_type = $pages['page_type'];
        $page_args = wp_parse_args($pages, $page_args);
    } else {

        // pages is page_type string (using this only insert a page template)
        $page_type = $pages;
        $page_args['post_title'] = $page_type;
    }

    /**
     * get page template link option and will return if it not empty
     */
    $link = get_option($page_type, '');
    if ($link) {
        $return = add_query_arg($params, $link);
        return apply_filters('et_get_page_link', $return, $page_type, $params);
    }

    // find post template
    $pages = get_pages(array(
        'meta_key' => '_wp_page_template',
        'meta_value' => 'page-' . $page_type . '.php',
        'numberposts' => 1
    ));

    // page not existed
    if (empty($pages) || !is_array($pages)) {

        // return false if set create is false and doesnot generate page
        if (!$create) return false;

        // insert page
        $id = wp_insert_post($page_args);

        if ($id) {

            // update page template option
            update_post_meta($id, '_wp_page_template', 'page-' . $page_type . '.php');
        }
    } else {

        // page exists
        $page = array_shift($pages);
        $id = $page->ID;
    }

    $return = get_permalink($id);

    /**
     * update transient page link
     */
    update_option('page-' . $page_type . '.php', $return);

    if (!empty($params) && is_array($params)) {
        $return = add_query_arg($params, $return);
    }

    return apply_filters('et_get_page_link', $return, $page_type, $params);
}

/**
 * find and return template page object
 * @param file name
 * @since 1.0
 */
function et_get_page_template( $filename ){
	// find post template
	$pages = get_posts( array('post_type' => 'page' , 'meta_key' => '_wp_page_template' ,'meta_value' => $filename . '.php' ) );

	if ( empty($pages) || !is_array($pages) ) return false;

	$resp = false;
	foreach ((array)$pages as $page) {
		$resp = $page;
		break;
	}
	return $resp;
}


/**
 * site breadcrumbs
 * @param array $args :
 * - showOnhome	: show breadcrum in front page or home page
 * - delimiter 	: string
 * - home 		: home label
 * - showCurrent: show current page label
 * - before    : before current
 * - after     : after current
 * @return $string
 * @author dakachi
 * @since 1.0
 */
function et_breadcrumbs ( $args = array () ) {
	$default	=	array (
		'showOnHome'	=> 0,
		'delimiter'		=>	'&raquo;',
		'home'			=>	__('Home', ET_DOMAIN),
		'showCurrent'	=>	'1',
		'before'		=>	'<span class="current">',
		'after'			=>	'</span>',
		'paged'			=>  false
	) ;
	$args	=	wp_parse_args( $args, $default );
	$args	=	apply_filters( 'et_breadcrumbs_args', $args );
	extract( $args );
	/*
	$showOnHome = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
  	$delimiter = '&#8250;'; // delimiter between crumbs
  	$home = 'Home'; // text for the 'Home' link
  	$showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
  	$before = '<span class="current">'; // tag before the current crumb
  	$after = '</span>'; // tag after the current crumb
	*/
	global $post;
	$homeLink = home_url();
	$breadcrumbs	=	'';
	if (is_home() || is_front_page()) {

	   if ($showOnHome == 1 ) $breadcrumbs	.= '<div id="crumbs"><a href="' . $homeLink . '">' . $home . '</a></div>';

	} else {

	    $breadcrumbs	.=	 '<a class="home" href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';

		if ( is_category() ) {
			global $wp_query;
			$cat_obj = $wp_query->get_queried_object();
			$thisCat = $cat_obj->term_id;
			$thisCat = get_category($thisCat);
			$parentCat = get_category($thisCat->parent);
			if ($thisCat->parent != 0) $breadcrumbs	.= ( get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' '));
			$breadcrumbs	.= $before  . single_cat_title('', false) .  $after;

		} elseif ( is_day() ) {
			$breadcrumbs	.= '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
			$breadcrumbs	.= '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
			$breadcrumbs	.= $before . get_the_time('d') . $after;

		} elseif ( is_month() ) {
			$breadcrumbs	.= '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
			$breadcrumbs	.= $before . get_the_time('F') . $after;

		} elseif ( is_year() ) {
			$breadcrumbs	.= $before . get_the_time('Y') . $after;
		} elseif ( is_single() && !is_attachment() ) {
			if ( get_post_type() == 'post' ) {
		        $cat = get_the_category(); $cat = $cat[0];
		        $breadcrumbs	.= get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
		        if ($showCurrent == 1) $breadcrumbs	.= $before . get_the_title() . $after;
		    }

		}  elseif ( is_attachment() ) {
			$parent = get_post($post->post_parent);
			if(get_post_type($parent) == "post") {
				$cat = get_the_category($parent->ID); $cat = $cat[0];
				$breadcrumbs	.= get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
			    $breadcrumbs	.= '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a> ' . $delimiter . ' ';
			}
			if ($showCurrent == 1) $breadcrumbs	.= $before . get_the_title() . $after;

		} elseif ( is_page() && !$post->post_parent ) {
		   	if ($showCurrent == 1) $breadcrumbs	.= $before . get_the_title() . $after;

		} elseif ( is_page() && $post->post_parent ) {
			$parent_id  = $post->post_parent;
			$page_breadcrumbs = array();
		    while ($parent_id) {
		        $page = get_page($parent_id);
		        $page_breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
		        $parent_id  = $page->post_parent;
		    }
		    $page_breadcrumbs = array_reverse($page_breadcrumbs);

		    foreach ($page_breadcrumbs as $crumb) $breadcrumbs	.= $crumb . ' ' . $delimiter . ' ';
			if ($showCurrent == 1) $breadcrumbs	.= $before . get_the_title() . $after;

		} elseif ( is_search() ) {
		      	$breadcrumbs	.= $before .sprintf(__('Search results for "%s "', ET_DOMAIN) , get_search_query()) . $after;

		} elseif ( is_tag() ) {
			$breadcrumbs	.= $before .sprintf(__('Posts tagged  "%s "', ET_DOMAIN) , single_tag_title('', false)) . $after;

		} elseif ( is_author() ) {
		    global $author;
		    $userdata = get_userdata($author);
		    $breadcrumbs	.= $before .sprintf(__('Articles posted by  %s ', ET_DOMAIN) , $userdata->display_name )  . $after;

		} elseif ( is_404() ) {
			$breadcrumbs	.= $before . __('Error 404', ET_DOMAIN) . $after;
		}
		$breadcrumbs	=	apply_filters('et_breadcrumbs', $breadcrumbs , $args);
		if ( $paged &&  get_query_var('paged') ) {
	      	if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) $breadcrumbs	.= ' (';
	      	$breadcrumbs	.= __('Page', ET_DOMAIN) . ' ' . get_query_var('paged');
	      	if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) $breadcrumbs	.= ')';
	    }

	}

	if( !$showCurrent ) $breadcrumbs	=	trim ($breadcrumbs, ' '. $delimiter. ' ');

	$breadcrumbs = apply_filters('et_custom_breadcrumbs', $breadcrumbs , $args);

	return $breadcrumbs;
}

/**
 *
 */
function et_color_hex2arr($hex = ''){
	if ( strlen( $hex ) != 6 ) return array('r' => 0, 'g' => 0, 'b' => 0);
	return array(
		'r' =>  hexdec(substr($hex, 0 , 2)),
		'g' =>  hexdec(substr($hex, 2 , 2)),
		'b' =>  hexdec(substr($hex, 4 , 2))
		);
}

function et_color_arr2hex( $array = array() ){
	$array = wp_parse_args( $array, array('r' => 0, 'g' => 0, 'b' => 0) );

	return str_pad(dechex( (int)$array['r'] ), 2 , '0', STR_PAD_LEFT) .
			str_pad(dechex( (int)$array['g'] ), 2 , '0', STR_PAD_LEFT) .
			str_pad(dechex( (int)$array['b'] ), 2 , '0', STR_PAD_LEFT) ;
}

/**
 *
 */
function et_hsl2rgb($hue, $saturation, $lightness){
	if ( $saturation > 1 ) $saturation /= 100;
	if ( $lightness > 1 ) $lightness /= 100;
	$altHue = $hue/60;
	$roundedHue = floor( $altHue );
	$c = ( 1 - abs( 2*$lightness - 1) ) * $saturation; //$saturation * $lightness;
	$x = $c*(1 - abs( fmod($altHue, 2) - 1 ));
	$color = array(
		'r' => 0,
		'g' => 0,
		'b' => 0
		);
	$result = array(
		'r' => 0,
		'g' => 0,
		'b' => 0
		);

	switch ($roundedHue) {
		case 0:
			$color['r'] = $c;
			$color['g'] = $x;
			$color['b'] = 0;
			break;

		case 1:
			$color['r'] = $x;
			$color['g'] = $c;
			$color['b'] = 0;
			break;

		case 2:
			$color['r'] = 0;
			$color['g'] = $c;
			$color['b'] = $x;
			break;

		case 3:
			$color['r'] = 0;
			$color['g'] = $x;
			$color['b'] = $c;
			break;

		case 4:
			$color['r'] = $x;
			$color['g'] = 0;
			$color['b'] = $c;
			break;

		case 5:
			$color['r'] = $c;
			$color['g'] = 0;
			$color['b'] = $x;
			break;

		default:
			$color['r'] = 0;
			$color['g'] = 0;
			$color['b'] = 0;
			break;
	}

	$m = $lightness - ($c / 2);

	$result = array(
		'r' => floor( ($color['r'] + $m)*255 ),
		'g' => floor( ($color['g'] + $m)*255 ),
		'b' => floor( ($color['b'] + $m)*255 )
		);

	return et_color_arr_to_hex($result);
}

function et_rgb2hsl(array $rgb){
	$rgb = array_values($rgb);
    $clrR = ($rgb[0] / 255);
    $clrG = ($rgb[1] / 255);
    $clrB = ($rgb[2] / 255);

    $clrMin = min($clrR, $clrG, $clrB);
    $clrMax = max($clrR, $clrG, $clrB);

    $c = $clrMax - $clrMin;

    $lightness = ($clrMax + $clrMin) / 2;

	if ( $c == 0) $altHue = 0;

	if ( $clrMax == $clrR ){
		$altHue = fmod((( $clrG - $clrB ) / $c ), 6);
	} elseif ( $clrMax == $clrG ){
		$altHue = (($clrB - $clrR ) / $c ) + 2;
	}else {
		$altHue = (($clrR - $clrG) / $c )+4;
	}

	$hue = 60 * $altHue;
	$saturation = $c / (1 - (abs(2*$lightness) - 1) );

    return array( round($hue), round($saturation * 100), round( $lightness * 100 ) );
}

/**
 *
 */
function et_generate_colors_abc($hexcolor){
	$rgb = et_color_hex2arr($hexcolor);
	$hsl = et_rgb2hsl( $rgb );
	$result = array();

	for($i = -25; $i < 25; $i = $i + 5 ){
		$hsl['l'] += $i;
		$result[] = et_hsl2rgb( $hsl );
	}

	return $result;
}
/**
 * check http setting and return
*/
function et_get_http () {
	if(!defined('TEMPLATEURL')) {
		$home_url	=	TEMPLATEURL;
	} else {
		$home_url	=	home_url();
	}
	$http		=	substr($home_url, 0,5);
	if($http != 'https') {
		$http	=	'http';
	}
	return $http;
}
?>
