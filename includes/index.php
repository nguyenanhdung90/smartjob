<?php 
require_once dirname(__FILE__) . '/core/bootstrap.php';
require_once dirname(__FILE__) . '/options/class-ops-general.php';
require_once dirname(__FILE__) . '/options/general.php';
require_once dirname(__FILE__) . '/payment/payment-visitor.php';
require_once dirname(__FILE__) . '/makePOT/makepot.php';


function et_filter_authentication_placeholder ($content, $user_id) {
	$user 		=	new WP_User ($user_id);

	$content 	=	str_ireplace('[user_login]', $user->user_login, $content); 
	$content 	=	str_ireplace('[user_name]', $user->user_login, $content); 
	$content 	=	str_ireplace('[user_nicename]',ucfirst( $user->user_nicename ), $content);
	$content 	=	str_ireplace('[user_email]', $user->user_email, $content);
	$content 	=	str_ireplace('[display_name]', ucfirst( $user->display_name ), $content);
	$content 	=	str_ireplace('[company]', ucfirst( $user->display_name ) , $content);
	$content 	=	str_ireplace('[dashboard]', et_get_page_link('dashboard'), $content);

	return $content; 
}

function et_filter_job_placeholder ($content, $job_id ) {
	$job 	=	get_post ($job_id);	
	$content 	=	str_ireplace('[job_title]', $job->post_title, $content); 
	$content 	=	str_ireplace('[job_desc]', $job->post_content, $content); 
	$content 	=	str_ireplace('[job_excerpt]', $job->post_excerpt, $content);
	$content 	=	str_ireplace('[job_link]', get_permalink($job_id), $content); 
	$content 	=	str_ireplace('[dashboard]', et_get_page_link('dashboard'), $content);
	$content 	= apply_filters('et_filter_job_email', $content, $job_id);

	return $content;
}

/*
 * filter breadcrum add post type job
 */
add_filter ('et_breadcrumbs','et_job_breadcrums', 10 , 2);
function et_job_breadcrums ( $breadcrumb, $arg ) {
	global $post;

	extract($arg);
	if( is_single() && get_post_type() == 'job') {
		$job_cat = et_get_the_job_category($post->ID);
		if( !empty ($job_cat)) {

			$job_cat 	= 	$job_cat[0];
			$breadcrumb	.= et_get_job_category_parents( $job_cat , TRUE, ' ' . $delimiter . ' ');
		}
        if ($showCurrent == 1) $breadcrumb	.= $before . get_the_title() . $after;
	}

	if(is_attachment()) {
		$parent = get_post($post->post_parent);
		if(get_post_type($parent) == "job") {
			$job_cat = et_get_the_job_category($parent->ID);

			if( !empty ($job_cat)) {
				$job_cat 	= 	$job_cat[0];
				$breadcrumb	.= et_get_job_category_parents( $job_cat , TRUE, ' ' . $delimiter . ' ');
			}
			$breadcrumb	.= $before . get_the_title($parent->ID) . $after;
		}
		if ($showCurrent == 1) $breadcrumb	.= $before . get_the_title() . $after;
	}

	if( is_author() ) {
		$homeLink = get_bloginfo('url');
		$breadcrumb 	=	'';
		$breadcrumb	.=	 '<a class="home" href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';
	    global $author;
	    $userdata = get_userdata($author);
	    $breadcrumb	.= $before .sprintf(__('Jobs posted by  %s ', ET_DOMAIN) , $userdata->display_name )  . $after;
	}

	if( !$showCurrent ) $breadcrumb	=	trim ($breadcrumb, ' '. $delimiter. ' ');

	return $breadcrumb;

}

// filter job how to apply content
add_filter( 'et_job_apply_content', 'et_filter_job_content' );
// filter job content
add_filter('et_job_content', 'et_filter_job_content');
function et_filter_job_content ( $content ) {
	
	//$content	=	str_replace('</ul>', '</ol>', str_replace('<ul>', '<ol>', $content));
	$content	=	str_replace(array('</h1>','</h2>','</h3>','</h4>','</h5>'), '</h6>', 
								str_replace(array('<h1>','<h2>','<h3>','<h4>','<h5>'), '<h6>', $content));

	$pattern = "/<[^\/>]*>(&nbsp;)*([\s]?)*<\/[^>]*>/";  //use this pattern to remove any empty tag '<a target="_blank" rel="nofollow" href="$1">$3</a>'

	$content	=	 preg_replace($pattern, '', $content); 
	$link_pattern = "/<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/";

	$content	=	str_replace('<a', '<a target="_blank" rel="nofollow"', $content);
	$content	  =	 strip_tags( $content, '<p><a><ul><ol><li><h6><span><b><em><strong><br>');

	return $content;
}
/**
* A callback that will be called and passed an array of matched elements in the subject string. The callback should return the replacement string.
*You'll often need the callback function for a preg_replace_callback() in just one place. In this case you can use an anonymous function (since PHP 5.3.0)
or create_function() to declare an anonymous function as callback within the call to preg_replace_callback().
By doing it this way you have all information for the call in one place and do not clutter the function namespace with a callback function's name not used anywhere else.
 */
function et_preg_replace_callback ( $match ) {
	$match	=	$match[0];
	preg_match("/target=([^\>]*)\"/",  $match, $mat);
	$match	=	preg_replace("/target=([^\>]*)\"/", '', $match);
	$match	=	preg_replace("/rel=([^\>]*)\"/", '', $match);

	$match	=	str_replace('<a', '<a target="_blank" rel="nofollow"', $match);

	return $match;
}

add_action ('et_insert_job','et_update_admin_new_feeds');
add_action ('et_update_job','et_update_admin_new_feeds');

function et_update_admin_new_feeds ($job_id) {
	global $user_ID;
	$users	=	get_users(array('role' => 'administrator'));

	foreach ($users as $user) {
		if($user->ID == $user_ID ) break;

		$feeds	=	et_get_user_new_feeds($user->ID);
		if(!in_array($job_id, $feeds)) {
			$feeds[]	=	$job_id;
		}
		et_update_user_new_feeds( $user->ID, $feeds );
	}
}

/**
 * Retrieve term parents with separator.
 *
 * @param int $id term ID.
 * @param string $taxonomy Taxonomy.
 * @param bool $link Optional, default is false. Whether to format with link.
 * @param string $separator Optional, default is '/'. How to separate terms.
 * @param bool $nicename Optional, default is false. Whether to use nice name for display.
 * @param array $visited Optional. Already linked to terms to prevent duplicates.
 * @return string
 */
function et_get_term_parents( $id, $taxonomy	=	'category', $link = false, $separator = '/', $nicename = false, $visited = array() ) {
	$chain = '';

	$parent = &get_term( $id, $taxonomy );

	if ( is_wp_error( $parent ) )
		return $parent;

	if ( $nicename )
		$name = $parent->slug;
	else
		$name = $parent->name;

	if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
		$visited[] = $parent->parent;
		$chain .= et_get_term_parent( $parent->parent,$taxonomy , $link, $separator, $nicename, $visited );
	}

	if ( $link )
		$chain .= '<a href="' . esc_url( get_term_link( $parent, $taxonomy ) ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" , ET_DOMAIN ), $parent->name ) ) . '">'.$name.'</a>' . $separator;
	else
		$chain .= $name.$separator;
	return $chain;
}

/*
 * filter nav menu object to add class current item to menu
 */
add_filter('wp_nav_menu_objects', 'et_filter_nav_menu_objects');
function et_filter_nav_menu_objects ( $sort_items ) {

	if(is_singular('post') ) {
		global $post;
		foreach ($sort_items as $value) {
			if( $value->object == 'post' && $value->object_id == $post->ID ) {
				return $sort_items;
			}
		}
		foreach ($sort_items as $value) {
			if(in_array('current-post-ancestor', $value->classes)   ) {
				$value->classes[]	=	'current-menu-item';
				return $sort_items;
			}
		}
		return $sort_items;
	}

	if(is_category()) {
		foreach ($sort_items as $value) {
			if(in_array('current-category-ancestor', $value->classes) && !in_array('current-category-parent', $value->classes)) {
				$value->classes[]	=	'current-menu-item';
				return $sort_items;
			}
		}
	}
	return $sort_items;

}
/**
 * filter comment default fields trigger by hook comment default field call in initialize theme
 * @param array $fields
 * @author dakachi
 */

function et_comment_default_fields ( $fields ) {

	$fields['author'] = '<div class="form-item ">
							<label>'.__("Your Name",ET_DOMAIN).'</label>
							<div class="input290">
								<input type="text" id="author" name="author" value=""  class="text" title="Your Name"/>
								<span class="require">*</span>
							</div>
						</div>';
	$fields['email']  = '<div class="form-item ">
							<label>'.__("Your Email",ET_DOMAIN).'</label>
							<div class="input290">
								<input type="text" id="email" name="email" value="" class="text" title="Your Email"/>
								<span class="require">*</span>
							</div>
						</div>';
	$fields['url']    = '<div class="form-item">
							<label>'.__("Your Website",ET_DOMAIN).'</label>
							<div class="input">
								<input type="text" name="url" value="" class="text" title="Your Website"/>
							</div>
						</div>';
	return $fields;
}
add_filter('comment_form_default_fields', 'et_comment_default_fields');

/*
 * load more post action
 */
add_action ('wp_ajax_et-load-more-post', 'et_load_more_post');
add_action ('wp_ajax_nopriv_et-load-more-post', 'et_load_more_post');
function et_load_more_post () {

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

	 		$data 		.= '
				<li>
					<div class="thumbnail font-quicksand">
					<div class="img-thumb">
						<a href="'. get_author_posts_url($post->post_author).'">
							'. get_avatar($post->post_author).'
						</a>
					</div>
					<div class="author">
						<a  title="'.sprintf(__("View all posts by %s ",ET_DOMAIN), get_the_author ()).'" 
							href="'. get_author_posts_url($post->post_author) .'">
							'.get_the_author().'
						</a>
						</div>
					<div class="join-date">'. $date_arr[2].' '. $date_arr[0].'<sup>'. strtoupper($date_arr[1]).'</sup>, '. $date_arr[3].'</div>
				</div>
        		<div class="content">
	          		<div class="header font-quicksand">
	           			<a href="'. get_category_link($cat).'">
							'.$cat->name .'
	           			</a>
	           			<a href="'.get_permalink().'" class="comment">
	           				<span class="icon" data-icon="q"> '.get_comments_number().' </span>
	           			</a>
	          		</div>
          			<h2 class="title">
           	 			<a href="'. get_permalink().'" title="'. get_the_title().'" >'. get_the_title ().'</a>
          			</h2>
          			<div class="description">

							'. get_the_excerpt() .'

          			</div>
          			<div class="footer font-quicksand">
                		<a href="'.get_permalink().'" title="'.sprintf(__("View post %s",ET_DOMAIN), get_the_title()).'">
		      	          	'.__("READ MORE",ET_DOMAIN).' <span class="icon" data-icon="]"></span>
		      	        </a>
             		</div>
        		</div>
				</li>';
        }
        wp_send_json (array (
        	'data'		=>	$data,
        	'success'	=>	 true,
        	'msg'		=>	'',
        	'total'		=>  $query->max_num_pages 
        ))	;
	} else {
	 		wp_send_json(array (
        	'data'		=>	$data,
        	'success'	=>	 false,
        	'msg'		=>	__('There is no posts yet.', ET_DOMAIN)
        ))	;
	}

}

add_action('wp_ajax_et-save-job-static-text', 'et_save_job_static_text');
function et_save_job_static_text () {

	$sidebar	=	trim($_POST['sidebar']);
	$id			=	trim ($_POST['id']);
	$data		=	stripcslashes($_POST['html']);
	$data		=	str_ireplace('&lt;', '<', $data);
	$data		=	str_ireplace('&gt;', '>', $data);

	$job_opt 	=	new ET_JobOptions();
	$response 	=	array (
		'success'	=>	true,
		'msg'		=>	__('Error!', ET_DOMAIN),
		'sidebar'	=>	$sidebar,
		'id'		=> $id
	);
	switch ($sidebar) {
		case 'post-job-sidebar':

			$widget	=	$job_opt->get_post_job_sidebar();

			if( isset( $widget[$id]) ) {
				$widget[$id]	=	$data;
			} else {
				$id 			=	$job_opt->generate_post_job_widget_id ();
				$widget[$id]	=	$data;
			};
			$response['id']		=	$id;
			$response['msg']	=	$data;

			$job_opt->set_post_job_sidebar($widget);

		break;
		case 'user-dashboard-sidebar':
			$widget	=	$job_opt->get_dashboard_sidebar();

			if( isset( $widget[$id]) ) {
				$widget[$id]	=	$data;
			} else {
				$id 			=	$job_opt->generate_dashboard_widget_id ();
				$widget[$id]	=	$data;
			};

			$response['id']		=	$id;
			$response['msg']	=	$data;
			$job_opt->set_dashboard_sidebar( $widget );
			break;
		case 'upgrade-account-sidebar':
			$widget	=	$job_opt->get_upgrade_account_sidebar();

			if( isset( $widget[$id]) ) {
				$widget[$id]	=	$data;
			} else {
				$id 			=	$job_opt->generate_dashboard_widget_id ();
				$widget[$id]	=	$data;
			};

			$response['id']		=	$id;
			$response['msg']	=	$data;
			$job_opt->set_upgrade_account_sidebar( $widget );
			break;

		default:
			$response['success'] = false;
		break;
	}
	wp_send_json($response);

}
add_action ('wp_ajax_et-remove-job-static-text', 'et_remove_job_static_text');
function et_remove_job_static_text () {

	$sidebar	=	trim($_POST['sidebar']);
	$id			=	trim ($_POST['id']);

	$response 	=	array (
		'success'	=>	true,
		'msg'		=>	__('Error!', ET_DOMAIN),
		'sidebar'	=>	$sidebar,
		'id'		=> $id
	);

	$job_opt 	=	new ET_JobOptions();

	switch ($sidebar) {
		case 'post-job-sidebar':

			$widget	=	$job_opt->get_post_job_sidebar();

			if( isset( $widget[$id]) ) {
				unset($widget[$id])	;
			} else {
				$response['success'] = false;
			}

			$job_opt->set_post_job_sidebar($widget);

		break;
		case 'user-dashboard-sidebar':
			$widget	=	$job_opt->get_dashboard_sidebar();

			if( isset( $widget[$id]) ) {
				unset($widget[$id])	;
			}else {
				$response['success'] = false;
			}

			$job_opt->set_dashboard_sidebar( $widget );
			break;
		case 'upgrade-account-sidebar':
			$widget	=	$job_opt->get_upgrade_account_sidebar();
			if( isset( $widget[$id]) ) {
				unset($widget[$id])	;
			}else {
				$response['success'] = false;
			}

			$job_opt->set_upgrade_account_sidebar( $widget );
			break;

		default:
			$response['success'] = false;
			break;
	}

	wp_send_json($response);

}
//////////////////////////////////////////////////////
 /* 		authentication mail filter				*/
//////////////////////////////////////////////////////

add_action ('et_after_register','et_user_register_mail', 10 , 2);
function et_user_register_mail( $user_id, $role ) {
	if($role != 'company') return ;

	$instance	=	JE_Mailing::get_instance();
	$instance->company_register ($user_id, $role );
	
}


add_filter('et_retrieve_password_message', 'et_filter_retrieve_password_message',10,3);
function et_filter_retrieve_password_message ( $message , $active_key , $user_data ) {

	$user_login 	=   $user_data->user_login;
	$mail_opt		=	new ET_JobEngineMailTemplate();
	$forgot_message	=	$mail_opt->get_forgot_pass_mail();

	$activate_url	= apply_filters('et_reset_password_link',  network_site_url("wp-login.php?action=rp&key=$active_key&login=" . rawurlencode($user_login), 'login'), $active_key, $user_login );

	$forgot_message	=	et_filter_authentication_placeholder ( $forgot_message, $user_data->ID );
	$forgot_message	=	str_ireplace('[activate_url]', $activate_url, $forgot_message);

	return $forgot_message;
}

add_action ('et_password_reset', 'et_password_reset_mail',10,2);
function et_password_reset_mail ( $user, $new_pass ) {

	$instance	=	JE_Mailing::get_instance();
	$instance->password_reset_mail ($user, $new_pass );

}

//////////////////////////////////////////////////////
 /* 		job mail filter				*/
//////////////////////////////////////////////////////
add_action ('et_change_job_status', 'et_change_job_status_mail',10,2);
function et_change_job_status_mail ( $job_id, $status) {
	$instance	=	JE_Mailing::get_instance();
	$instance->change_job_status ($job_id, $status );
}




add_filter('et_retrieve_password_message', 'je_retrieve_password_message');
function je_retrieve_password_message ($message) {
	return et_get_mail_header().$message.et_get_mail_footer();
}


add_action ('et_cash_checkout', 'je_email_cash_message');
function je_email_cash_message ($cash_message) {
	global $current_user, $user_ID;
	$auto_email = et_get_auto_emails();
	if($auto_email['cash_notice']) {
		// get cash notification mail template and filter placeholder
		$mail_opt	=	new ET_JobEngineMailTemplate();
		$message	=	$mail_opt->get_cash_notification_mail ();
		$message	=	str_ireplace('[cash_message]',$cash_message, $message );
		$message	=	et_filter_authentication_placeholder ($message, $user_ID);

		$session 	=	et_read_session ();
		if(isset($session['job_id'])) {
			$message	=	et_filter_job_placeholder ($message, $session['job_id']);
		}
		// sent cash notification to user
		wp_mail ($current_user->data->user_email, __("Cash payment notification", ET_DOMAIN), et_get_mail_header(). $message .et_get_mail_footer () )  ;
	}
}


