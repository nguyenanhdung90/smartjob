<?php
global $et_mobile_path;
// $et_mobile_path = get_option('et_mobile_template_path');
$et_mobile_path = TEMPLATEPATH . '/mobile';

/**
 * Handle customization request in backend
 * @since 1.0
 */
function et_ajax_sync_customization(){
	global $et_global;
	$data = !empty($_REQUEST['content']['data']) ? $_REQUEST['content']['data'] : array();

	et_apply_customization($data);

	$general_opt	=	new ET_GeneralOptions();
	$general_opt->set_customization( $data );
	$general_opt->set_color_schemes( !empty($_REQUEST['content']['colors']) ? $_REQUEST['content']['colors'] : array() );
	$general_opt->update_option($et_global['db_prefix'] . 'choosen_color', !empty($_REQUEST['content']['choosenColor']) ? $_REQUEST['content']['choosenColor'] : 0);

	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	echo json_encode(array(
		'success' 	=> true,
		'code' 		=> 200,
		'msg' 		=> '',
		'data' 		=> $general_opt->get_customization()
		));
	exit;
}
add_action('wp_ajax_et-save-style', 'et_ajax_sync_customization');

/**
 *
 */
function et_ajax_preview_customization(){
	$data = !empty($_REQUEST['content']['data']) ? $_REQUEST['content']['data'] : array();

	et_apply_customization($data, true);

	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	echo json_encode(array(
		'success' 	=> true,
		'code' 		=> 200,
		'msg' 		=> '',
		'data' 		=> array(
				'url' 	=> add_query_arg( array('style_preview' => 'true' ), home_url() )
			)
		));
	exit;
}
add_action('wp_ajax_et-preview-style', 'et_ajax_preview_customization');

function et_ajax_update_custom_style(){
	$data 	= !empty($_REQUEST['content']['style']) ? $_REQUEST['content']['style'] : '';

	$option = new ET_GeneralOptions();
	$option->set_custom_style($data);

	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	echo json_encode(array(
		'success' 	=> true,
		'code' 		=> 200,
		'msg' 		=> '',
		'data' 		=> $option->et_get_current_customization()
		));
	exit;
}
add_action('wp_ajax_et-update-custom-style', 'et_ajax_update_custom_style');

/**
 * Get saving customization
 * @since 1.0
 */
function et_get_current_customization($type = false){
	$option = new ET_GeneralOptions();
	$values = $option->get_customization();
	if ($values == false){
		$values = array(
			'background' => '#fff',
			'header' => '#333',
			'text' => '#333',
			'heading' => '#333',
			'action' => '#F28C79',
			'font-text' => 'Arial, san-serif',
			'font-text-weight' => 'normal',
			'font-text-style' => 'normal',
			'font-text-size' => '14px',
			'font-heading' => 'Arial, san-serif',
			'font-heading-weight' => 'normal',
			'font-heading-style' => 'normal',
			'font-heading-size' => '12px',
			'font-links' => 'Arial, san-serif',
			'font-links-weight' => 'normal',
			'font-links-style' => 'normal',
			'font-links-size' => '12px',
			);
	}

	if ( $type == false ) return $values;
	else if ( array_key_exists($type, $values) ) return $values[$type];
	else return false;
}

/**
 * Jopress Page Redirect
 */
//add_action('template_redirect', 'hook_template_redirect');

function hook_template_redirect(){
	global $user_ID, $wp_query, $wp_rewrite;
	// no need to redirect when in admin
	if ( is_admin() ) return;

	/***
	  * Detect mobile and redirect to the correlative layout file
	  */
	global $isMobile;
	$detector = new ET_MobileDetect();
	$isMobile = apply_filters( 'et_is_mobile', ($detector->isMobile()) ? true : false );

	if ( $isMobile  ){
		$path 	= TEMPLATEPATH . '/mobile';
		$mobile_path = get_stylesheet_directory() . '/mobile';

		if( is_page_template( 'page-intro.php' ) ) {
			et_smart_require($mobile_path . '/page-intro.php', $path . '/page-intro.php');
			exit;
		}

		if( is_front_page() ) {
			et_smart_require($mobile_path . '/page.php', $path . '/page.php');
			exit;
		}

		if ( is_home() || is_index() ){
			et_smart_require($mobile_path . '/index.php', $path . '/index.php');
		}
		elseif ( is_singular('job'))
			et_smart_require($mobile_path . '/single-job.php', $path . '/single-job.php');
		elseif ( is_page_template('page-login.php') )
			et_smart_require($mobile_path . '/page-login.php', $path . '/page-login.php');
		elseif ( is_page_template('page-register.php') )
			et_smart_require($mobile_path . '/register.php', $path . '/register.php');
		elseif ( is_page_template('page-dashboard.php') )
			et_smart_require($mobile_path . '/dashboard.php', $path . '/dashboard.php');
		// elseif ( is_page_template('page-post-a-job.php') )
		// 	et_smart_require($mobile_path . '/post-a-job.php', $path . '/post-a-job.php');
		elseif ( is_author() )
			et_smart_require($mobile_path . '/companies.php', $path . '/companies.php');
		elseif ( is_404() )
			et_smart_require($mobile_path . '/404.php', $path . '/404.php');
		elseif ( is_post_type_archive('resume') ){
			et_smart_require($mobile_path . '/archive-resume.php', $path . '/archive-resume.php');
		}
		// elseif ( taxonomy_exists('job_type') )
		// 	et_smart_require($mobile_path . '/index.php', $path . '/index.php');
		else {
			wp_redirect( home_url() );
			// et_smart_require($mobile_path . '/unsupported.php', $path . '/unsupported.php');
			//wp_redirect( get_bloginfo('url'));
		}
		exit;
	}
	// cancel template redirect
	if ( is_single() || is_category()  || is_archive( )) return;

}

/**
 * 
 */
function et_smart_require($path, $original){
	if ( file_exists($path))
		require_once($path);
	else 
		require_once($original);
}


/**
 * Return the mobile template path for 'template_include' filter
 * @param string $template
 * @author James
 * @version 1.0
 * @copyright enginethemes.com team
 * @license enginethemes.com team
 */
function et_get_template_include( $template )
{
	global $et_mobile_path;
	$et_mobile_template_path = $et_mobile_path; //get_option('et_mobile_template_path');
	if(empty($et_mobile_template_path))
		return $template;
	else
		return  dirname( $template ) . '/' . $et_mobile_template_path . '/' . basename($template);
}


/**
 * Get mobile version header template
 * @author toannm
 * @param name of the custom header template
 * @version 1.0
 * @copyright enginethemes.com team
 * @license enginethemes.com team
 */
function et_get_mobile_header( $name = null ){
	global $et_mobile_path;

	do_action( 'get_header', $name );

	//$templates = array();
	$templates = $et_mobile_path . '/' . 'header.php';
	if ( isset($name) )
		$templates = $et_mobile_path . '/' . "header-{$name}.php";
	$templates = apply_filters( 'template_include', $templates );

	// Backward compat code will be removed in a future release

	if ('' == locate_template($templates, true))
		//load_template( ABSPATH . WPINC . '/theme-compat/header.php');
		load_template( $templates);
}


/**
 * Get mobile version header template
 * @author toannm
 * @param name of the custom header template
 * @version 1.0
 * @copyright enginethemes.com team
 * @license enginethemes.com team
 */
function et_get_mobile_footer( $name = null ) {
	global $et_mobile_path;

	do_action( 'get_footer', $name );

	//$templates = array();
	$templates = $et_mobile_path . '/' . 'footer.php';
	if ( isset($name) )
		$templates = $et_mobile_path . '/' . "footer-{$name}.php";
	$templates = apply_filters( 'template_include', $templates );
	//$templates = apply_filters( 'template_include', $templates );
	// Backward compat code will be removed in a future release
	if ('' == locate_template($templates, true))
		//load_template( ABSPATH . WPINC . '/theme-compat/footer.php');
		load_template($templates);
}

/**
 * Permanent redirect to mobile version, for test purpose
 * @author toannm
 * @version 1.0
 * @copyright enginethemes.com team
 * @license enginethemes.com team
 */
function et_enable_mobile_test( $enable = true ){
	global $et_mobile_path;
	$et_mobile = new ET_MobileDetect();
	if( !$et_mobile->isMobile() )
	{
		//update_option('et_mobile_template_path', $path);
		if( has_filter('template_include', 'et_template_include') )
			remove_filter('template_include', 'et_template_include');
		add_filter( 'template_include', 'et_get_template_include') ;
	}
}

/**
 *
 */
function et_template_frontend_job(){
	$strings = array(
			'edit' 		=> __('Edit', ET_DOMAIN),
			'featured' 	=> __('Hot', ET_DOMAIN),
			'approve' 	=> __('Approve', ET_DOMAIN),
			'reject' 	=> __('Reject', ET_DOMAIN),
			'archive' 	=> __('Archive', ET_DOMAIN),
			'view_by' 	=> __("View jobs posted by ", ET_DOMAIN),
			'remove_featured' => __('Remove Featured', ET_DOMAIN),
			'set_featured' => __('Set Featured', ET_DOMAIN),
			'paid' 		=> __('PAID', ET_DOMAIN),
			'unpaid' 	=> __('UNPAID', ET_DOMAIN),
			'free' 		=> __('FREE', ET_DOMAIN),			
			);
	$template = <<<TEMPLATE
	<div class='thumb'>
	<# if(author_job==='company'){#>
		<# if ( _.isObject(author_data['user_logo']) && ('small_thumb' in author_data['user_logo'] || 'thumbnail' in author_data['user_logo'] ) ) { #>
			<a data='{{ author_id }}' href='{{ author_data['post_url'] }}'
				id='job_author_name' class='thumb' title='{$strings['view_by']} {{ author_data['display_name'] }}' target='_blank'>
				<img src='{{ ('small_thumb' in author_data['user_logo'] ) ? author_data['user_logo']['small_thumb'][0] : author_data['user_logo']['thumbnail'][0] }}' />
			</a>
		<# } #>
	<# }else{#>	
	     <a id='job_author_name' title='{$strings['view_by']} {{name_company_editor}}' class='thumb' href='{{author_data['post_url']}}/?com_i={{id_com}}' target='_blank'>
		 <img src="{{logo_company_editor}}">
		</a>
		
	<#}#>
	</div>
	<div class='content'>
		<a class='title-link title' href='{{ permalink }}' target='_blank'> {{title }}</a>
		<a target="_blank" class="title-link title" href='{{ permalink }}'><span class="icon" data-icon="R"></span></a>
		<div class='tech font-quicksand f-right actions'>
			<# if ( featured === "1"  && status !== 'pending' && status !== 'draft'){ #>
				<span class='feature font-quicksand'>{$strings['featured']}</span>
			<# } #>

			<# if (typeof disableAction === 'undefined'){ #>
				<# if ( typeof actionid !== 'undefined' && actionid !== 0 && (status === 'publish' || status === 'reject' || status === 'archive' ) ) { #>
					<a data='{{ actionid }}' title='{{ featured === "1" ? '{$strings['remove_featured']}' : '{$strings['set_featured']}' }}' class='flag flag-feature {{ featured === "1" ? 'fea' : '' }} action-featured tooltip' href='#' >
						<span class='icon' data-icon='^'></span>
					</a>
					<a data='{{ actionid }}' title="{$strings['edit']}" class='action-edit tooltip' href='#'><span class='icon' data-icon='p'></a></span>
					<# if (status !== 'archive') { #>
						<a data='{{ actionid }}' title="{$strings['archive']}" class='action-archive tooltip' href='#'><span class='icon' data-icon='#'></span></a>
					<# } #>
				<# } if ( typeof actionid !== 'undefined' && actionid !== 0 && (status === 'pending' || status === 'draft') ) { #>
						<a data='{{ actionid }}' class='flag {{ (job_paid === "1") ? "paid" : "unpaid" }}' href='#'>
							{{ (job_paid === "1") ? "{$strings['paid']}" : ( (job_paid === "2") ? "{$strings['free']}" : "{$strings['unpaid']}") }}<span class='icon' data-icon='%'></span>
						</a>
						<a data='{{ actionid }}' title="{$strings['edit']}" class='action-edit tooltip' href='#'><span class='icon' data-icon='p'></span></a>
						<a data='{{ actionid }}title="{$strings['approve']}" class='color-active action-approve tooltip' href='#'><span class='icon' data-icon='3'></span></a>
						<a data='{{ actionid }}' title="{$strings['reject']}" class='color-pending action-reject tooltip' href='#'><span class='icon' data-icon='*'></span></a>
				<# } #>
			<# } #>
		</div>

		<div class='desc f-left-all'>
			<div class='cat company_name c'>
			<# if(author_job==='company'){#>
				<a data='{{ author_id }}' href='{{ author_data['post_url'] }}'
					id='job_author_name' title='{$strings['view_by']}{{ author_data["display_name"] }}' target='_blank'>
						{{ author_data['display_name'] }}
				</a>
			<#}else{#>	
			<a href='{{author_data['post_url']}}/?com_i={{id_com}}' target='_blank'>{{name_company_editor}}</a>
			<#}#>
			</div>
			<# if (typeof job_types[0] != 'undefined' ){ #>
			<div class='job-type color-<# if (typeof job_types[0].color != 'undefined') { #>{{job_types[0].color}} <# } #>'>
				<span class='flag'></span>
				<# _.each(job_types, function(type) { #>
					<a href='{{ type.url }}' rel='tag' target='_blank'>{{ type.name }}</a>
				<# }); #>
			</div>
			<# } #>
			<div><span class='icon' data-icon='@'></span><span class='job-location'>{{ location }}</span></div>
			<div><span class="ob-location" itemtype="" itemscope="" itemprop="jobLocation"><span class='icon'>{{mucluong}}:</span><span class='job-location' style="color:#ce534d">{{ money_mucluong}}</span></span></div>
		</div>
		<div class="decription_smartjob">
		 {{excerpt}} 
		</div>
		<div class="decription_smartjob">
			<# if(tag1 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag1}}" class="tag_smartjob_home" target='_blank'> {{tag1}}</a><# }#>
			<# if(tag2 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag2}}" class="tag_smartjob_home" target='_blank'> {{tag2}}</a><# }#>
			<# if(tag3 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag3}}}}" class="tag_smartjob_home" target='_blank'> {{tag3}}</a><# }#>
			<# if(tag4 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag4}}" class="tag_smartjob_home" target='_blank'> {{tag4}}</a><# }#>
			<# if(tag5 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag5}}" class="tag_smartjob_home" target='_blank'> {{tag5}}</a><# }#>
			<# if(tag6 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag6}}" class="tag_smartjob_home" target='_blank'> {{tag6}}</a><# }#>
			<# if(tag7 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag7}}" class="tag_smartjob_home" target='_blank'> {{tag7}}</a><# }#>
			<# if(tag8 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag8}}" class="tag_smartjob_home" target='_blank'> {{tag8}}</a><# }#>
			<# if(tag9 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag9}}" class="tag_smartjob_home" target='_blank'> {{tag9}}</a><# }#>
			<# if(tag10 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag10}}" class="tag_smartjob_home" target='_blank'> {{tag10}}</a><# }#>
			<# if(tag11 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag11}}" class="tag_smartjob_home" target='_blank'> {{tag11}}</a><# }#>
			<# if(tag12 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag12}}" class="tag_smartjob_home" target='_blank'> {{tag12}}</a><# }#>
			<# if(tag13 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag13}}" class="tag_smartjob_home" target='_blank'> {{tag13}}</a><# }#>
			<# if(tag14 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag14}}" class="tag_smartjob_home" target='_blank'> {{tag14}}</a><# }#>
			<# if(tag15 !== null ){#><a target="_blank" href="{{web_url}}/?s={{tag15}}" class="tag_smartjob_home" target='_blank'> {{tag15}}</a><# }#>
		</div>		
	</div>
TEMPLATE;
	$template = apply_filters('et_frontend_job_template', $template);
	return $template;
}


/**
 * Return view count display of
 * @param job id
 * @return int view count
 */
function et_post_views($id){
	$text_single = __('%d view',ET_DOMAIN);
	$text_plural = __('%d views',ET_DOMAIN);

	$view = (int)et_get_post_views($id);

	if ($view <= 1)
		return sprintf($text_single, number_format($view));
	else
		return sprintf($text_plural, number_format($view));
}

/**
 * Blocking specify browsers
 * @param array browsers name
 * @param string page that theme will redirect to
 * @return
 */
function et_block_ie($version, $page){
	$info = getBrowser();
	//if ( $info['name'] == 'Internet Explorer' && version_compare($version, $info['version'], '>=') && file_exists(TEMPLATEPATH . '/' . $page)){
		if (!is_page_template('page-unsupported.php')){
				// find a template "unsupported"
				// If template doesn't existed, create it
			?>
			<script type="text/javascript">
				var detectBrowser = function () {
					var isOpera = this.check(/opera/);
					var isIE = !isOpera && check(/msie/);
					var isIE8 = isIE && check(/msie 8/);
					var isIE7 = isIE && check(/msie 7/);
					var isIE6 = isIE && check(/msie 6/);

					if( ( isIE6 || isIE7 )  ) window.location	=	'<?php echo et_get_page_link("unsupported"); ?>';
				}

				var check  = function (r) {
					var ua = navigator.userAgent.toLowerCase();
					return r.test(ua);
				}
				detectBrowser ();

			</script>

			<?php

		}

}

/**
 * Detect user's browser and version
 * @return array browser info
 */
function getBrowser()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";
    $ub = "MSIE";
    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }

    // Next get the name of the useragent yes separately and for good reason.
    if (preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }
    elseif (preg_match('/Firefox/i',$u_agent))
    {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    }
    elseif (preg_match('/Chrome/i',$u_agent))
    {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    }
    elseif (preg_match('/Safari/i',$u_agent))
    {
        $bname = 'Apple Safari';
        $ub = "Safari";
    }
    elseif (preg_match('/Opera/i',$u_agent))
    {
        $bname = 'Opera';
        $ub = "Opera";
    }
    elseif (preg_match('/Netscape/i',$u_agent))
    {
        $bname = 'Netscape';
        $ub = "Netscape";
    }

    // Finally get the correct version number.
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
    // See how many we have.
    $i = count($matches['browser']);
    if( isset($matches['version'] ) ) {
	    if ($i != 1) {
	        //we will have two since we are not using 'other' argument yet
	        //see if version is before or after the name
	        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
	            $version= isset($matches['version'][0]) ? $matches['version'][0] : '4.0';
	        }
	        else {
	            $version= isset($matches['version'][1]) ? $matches['version'][1] : '4.0';
	        }
	    }
	    else {
	        $version= isset($matches['version'][0]) ? $matches['version'][0] : '4.0';
	    }
	}else {
		$version= '4.0';
	}
    if($ub == "MSIE") {
    	preg_match('/(MSIE) [0-9.]*;/', $u_agent, $matches);
    	$version	=	isset($matches[0]) ? $matches[0] : '1.0';
    	$version	=	str_replace(array('MSIE', ';', ' '), '', $version);
    }

    // Check if we have a number.
    if ($version==null || $version=="") {$version="?";}

    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
}

/**
 * Checking current menu items
 */
function et_is_job_menu(){
	return is_post_type_archive('job') || get_post_type() == 'job' || is_home();
}

function et_is_company_menu(){
	return is_page_template('page-companies.php') || is_author();
}

/**
 * function echo template for modal edit job
*/
if (!function_exists('je_modal_edit_job_template')) {
function je_modal_edit_job_template () {
	$job_types		= et_get_job_types ();
?>
	<!-- modal: Edit a job -->
	<div class="modal-job" id="modal_edit_job">
		<div class="edit-job-inner">
			<div class="title font-quicksand"><?php _e('EDIT THIS JOB', ET_DOMAIN) ?></div>
			<div class="modal-form">
				<form id="job_form">
					<div class="content">
						<div class="toggle-content login clearfix">
							<div class="form">
								<div id="job-details">
									<div class="form-label font-quicksand">
										<?php _e('JOB DETAILS', ET_DOMAIN) ?>

										<?php if(current_user_can('manage_options')){
										$job_statues	= array(
											'publish'	=> __('Active', ET_DOMAIN),
											'pending'	=> __('Pending', ET_DOMAIN),
											'reject'	=> __('Rejected', ET_DOMAIN),
											'archive'	=> __('Archived', ET_DOMAIN),
										);
										?>
										<div class="form-item edit-status f-right clearfix">
											<div class="f-right">
												<div class="select-style btn-background border-radius">
													<select name="job_status" id="job_status">
													<?php foreach($job_statues as $key => $val){ ?>
														<option value="<?php echo $key ?>"><?php echo $val;?></option>
													<?php }?>
													</select>
												</div>
											</div>
										</div>
									<?php }?>
									</div>

									<div class="form-item">
										<div class="label">
											<h6><?php _e('Job Title', ET_DOMAIN) ?></h6>
										</div>
										<div class="">
											<input type="text" id="title" name="title" class="bg-default-input"/>
										</div>
									</div>
									<div class="form-item">
										<div class="label">
											<h6><?php _e('Job Description', ET_DOMAIN) ?></h6>
										</div>
										<div class="job_description">
											<?php
												wp_editor( '' ,'content' , je_job_editor_settings ()  );
											// ?>
											<!-- <textarea id="content" name="content" class="bg-default-input" ></textarea> -->
										</div>
									</div>
									<div class="form-item">
										<div class="label">
											<h6><?php _e('Job Location', ET_DOMAIN) ?></h6>
										</div>
										<div class="">
											<div>
												<input class="bg-default-input" name="full_location" id="full_location" type="text" />
												<input type="hidden" name="location" id="location" value="" />
												<input type="hidden" name="location_lat" id="location_lat" value="" />
												<input type="hidden" name="location_lng" id="location_lng" value="" />
												<div class="address-note">
													<?php _e('Examples: "Melbourne VIC", "Seattle", "Anywhere"', ET_DOMAIN) ?>
													<!--
													<?php _e('Display location as', ET_DOMAIN) ?>: <span title="<?php _e('Edit location', ET_DOMAIN) ?>" id="add_sample">""</span>
													<input style="display:none" type="text" maxlength="50" id="add_sample_input" value="">
													-->
												</div>
											</div>
											<div class="maps">
												<div class="map-inner" id="map" style="width : 520px;height : 250px"></div>
											</div>
										</div>
									</div>
									<!-- How to apply -->
									<div class="form-item">
										<div class="label">
											<h6><?php _e('How To Apply', ET_DOMAIN );?></h6>
											<?php //_e('Select the correct type for your job', ET_DOMAIN );?>
										</div>
										<div class="apply">
											<input type="hidden" id="apply_method" value="">
											<input type="radio" name="apply_method" id="isapplywithprofile" value="isapplywithprofile">
											<label class="font-quicksand" for="isapplywithprofile">
												<?php _e("Allow job seeker to submit their cover letter and resume directly", ET_DOMAIN);?>
											</label>
											<div class="email_apply">
												<span class=""><?php _e("Send applications to this email address:", ET_DOMAIN); ?></span>&nbsp;
												<input class="bg-default-input application-email" type="text" name="apply_email" id="apply_email" /> 
												<span class="example"><?php _e("e.g. 'application@demo.com'", ET_DOMAIN); ?></span>
											</div>
											<input type="radio" name="apply_method" id="ishowtoapply" value="ishowtoapply">
											<label class="font-quicksand" for="ishowtoapply" ><?php _e("Or, provide application details below", ET_DOMAIN);?></label>
											<div class="applicant_detail">
												<?php wp_editor( '' ,'applicant_detail' , je_editor_settings ()  );  ?>
												<!-- <textarea name="applicant_detail" id="applicant_detail"></textarea> -->
											</div>

										</div>
									</div>

									<div class="form-item clearfix">
										<div class="width50 f-left">
											<div class="label">
												<h6><?php _e('Contract Type', ET_DOMAIN) ?></h6>
											</div>
											<div class="select-style btn-background border-radius">
												<?php et_job_type_select('job_types'); ?>
											</div>
										</div>
										<div class="width50 f-right">
											<div class="label">
												<h6><?php _e('Job Category', ET_DOMAIN) ?></h6>
											</div>
											<div class="select-style btn-background border-radius">
													<?php et_job_cat_select ('categories') ?>
											</div>
										</div>
									</div>
									<!-- CUSTOM FIELDS -->
									<?php do_action('et_edit_job_fields') ?>

								</div>
								<div id="company-details">
									<div class="form-label padding-top30 font-quicksand">
										<?php _e('Company Details', ET_DOMAIN) ?>
									</div>
									<div class="form-item f-left-all">
										<div class="input-file logo f-left" id="user_logo_container">
											<div class="label">
												<h6><?php _e('Company logo', ET_DOMAIN) ?></h6>
												<span class="et-browse-file f-right" id="user_logo_browse_button" ><?php _e('Browse', ET_DOMAIN );?></span>
											</div>
											<div id="user_logo_thumbnail" class="thumbs"></div>
											<span class="et_ajaxnonce" id="<?php echo wp_create_nonce('user_logo_et_uploader'); ?>"></span>
										    <div class="filelist"></div>
										</div>
										<div class="info">
											<div class="label">
												<h6><?php _e('Company Name',ET_DOMAIN) ?></h6>
											</div>
											<div class="">
												<input type="text" id="display_name" name="display_name" class="bg-default-input"/>
											</div>
											<div class="label">
												<h6><?php _e('Website',ET_DOMAIN) ?></h6>
											</div>
											<div class="">
												<input type="url" id="user_url" name="user_url" class="bg-default-input"/>
											</div>
										</div>
									</div>
								</div>
							</div>

						</div>
					</div>
					<div class="footer font-quicksand">
						 <div class="button">
							<input id="submit-form" type="submit" class="bg-btn-action border-radius" value="<?php _e('SAVE CHANGE',ET_DOMAIN) ?>" name="">
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="modal-close"></div>
	</div>
	<!-- end modal: Edit a job -->
	<?php
}
}


function je_header_menu () {
	global $current_user, $user_ID;
	$sel = '';
	// check if which page the user is in
	if( is_page_template('page-companies.php') || is_author() ){
		$sel = 'company';
	}
	elseif( is_page_template('page-dashboard.php') || is_page_template('page-profile.php') || is_page_template('page-password.php')) {
		$sel = 'dashboard';
	}
	else if( is_post_type_archive('job') || get_post_type() == 'job' || is_home() ){
		$sel = 'job';
		et_delete_user_new_feeds($user_ID);
	}

	$job_selected		=	'';
	$company_selected	=	'';
	if($sel=='job')			$job_selected		=	'current-menu-item';
	if( $sel=='company') 	$company_selected	=	'current-menu-item';

	$notice = '';
	$new_feeds	=	count(et_get_user_new_feeds($current_user->ID)) ;
	if( $new_feeds > 0 ) {
		$notice	=	'<dd><a href="#">'. $new_feeds .'</a></dd>';
	}


	$default_menu_items = apply_filters( 'default_menu_items', array(
		array(
			'id' 				=> 'home-menu',
			'href' 				=> get_post_type_archive_link( 'job' ),
			'checking_callback'	=> 'et_is_job_menu',
			'label' 			=> __('JOBS', ET_DOMAIN),
			'link_attr' 		=> array('title' => __('Jobs', ET_DOMAIN)),
		), array(
			'id' 				=> 'company-menu',
			'href' 				=> et_get_page_link('companies'),
			'checking_callback'	=> 'et_is_company_menu',
			'label' 			=> __('COMPANIES', ET_DOMAIN),
			'link_attr' 		=> array('title' => __('Companies', ET_DOMAIN)),
		)));

	// prepare default menu items html
	$default_mi_html = '';
	foreach ($default_menu_items as $item) {
		if ( !isset($item['id']) || !isset($item['href']) || !isset($item['checking_callback']) || 
			!isset($item['label']) || !isset($item['link_attr']) )
			continue;

		// build link attributes
		$link_attrs = '';
		foreach ((array)$item['link_attr'] as $key => $value) {
			$link_attrs .= " $key='$value' ";
		}

		// build link html
		$a = "<a href='{$item['href']}' {$link_attrs} >{$item['label']}</a>";

		// current
		$current = call_user_func_array($item['checking_callback'], array());

		$subitem	=	'';
		if(isset($item['sub'])) {
			foreach ($item['sub']  as $sub) {
				$sublink_attrs = '';
				foreach ((array)$sub['link_attr'] as $key => $value) {
					$sublink_attrs .= " $key='$value' ";
				}

				// build link html
				$suba = "<a href='{$sub['href']}' {$sublink_attrs} >{$sub['label']}</a>";
				$subitem .= "<li id='{$sub['id']}' >{$suba} </li>";
			}
		}
		$subitem = '<ul class="sub-menu">'.$subitem. '</ul>';

		// add to default menu html
		if ($current)
			$default_mi_html .= "<li class='current-menu-item' id='{$item['id']}'>{$a}{$subitem}</li>";
		else
			$default_mi_html .= "<li id='{$item['id']}'>{$a}{$subitem} </li>";

	}
	$menu	=	wp_nav_menu(array (
			'items_wrap'	=>	'<ul class="menu-header-top">'.$default_mi_html.'%3$s</ul>',
			'theme_location'=> 'et_top',
			'echo'	=> false
	));
	if( has_nav_menu('et_top') && $menu != '') {
		echo $menu;
	}
	else {
		echo "<ul class='menu-header-top'>$default_mi_html</ul>";
	}
}

add_filter ('et_jobengine_demonstration', 'do_shortcode');
add_filter ('je_resume_headline', 'do_shortcode');

add_shortcode( 'img', 'je_image_shortcode' );
function je_image_shortcode ($atts , $content ) {
	extract( shortcode_atts( array(
		'class' => '',
		'id' => '',
		'alt' => ''
	), $atts ) );
	return "<img id='{$id}' class='{$class}' alt='{$alt}' src='{$content}'' />" ;
}

function je_job_editor_settings() {
	return apply_filters( 'je_job_editor_settings', array(
		'quicktags'  => false,
		'media_buttons' => false,
		'wpautop'	=> false,
		'teeny'		=> true,
		'tabindex'	=>	'2',
		'teeny'		=> false,
		'tinymce'   => array(
			'remove_redundant_brs' => true,
			'cleanup' => true,
			'content_css'	=> get_template_directory_uri() . '/js/lib/tiny_mce/content.css',
			'height'   => 250,
			'autoresize_min_height'=> 250,
			'autoresize_max_height'=> 550,
			'toolbar1' => 'et_heading,|,bold,|,italic,|,underline,|,link,unlink,|,bullist,numlist,|,alignleft, aligncenter, alignright',
			'toolbar2' => '',
			'toolbar3' => '',
			'theme_advanced_statusbar_location' => 'none',
			'theme_advanced_resizing'	=> true ,
			'setup' =>  "function(ed){
				ed.onChange.add(function(ed, l) {
					var content	= ed.getContent();
					if(ed.isDirty() || content === '' ){
						ed.save();
						jQuery(ed.getElement()).blur(); // trigger change event for textarea
					}

				});

				// We set a tabindex value to the iframe instead of the initial textarea
				ed.onInit.add(function() {
					var editorId = ed.editorId,
						textarea = jQuery('#'+editorId);
					jQuery('#'+editorId+'_ifr').attr('tabindex', textarea.attr('tabindex'));
					textarea.attr('tabindex', null);
				});
			}"
		)
	));
}

function je_editor_settings($args = array()){
	 return apply_filters( 'je_editor_settings', array(
		'quicktags'  => false,
		'media_buttons' => false,
		'wpautop'	=> false,
		'teeny'		=> false,
		'tinymce'   => array(
			'content_css'	=> get_template_directory_uri() . '/js/lib/tiny_mce/content.css',
			'height'   => 200,
			'autoresize_min_height'=> 200,
			'autoresize_max_height'=> 350,
			'toolbar1' => 'et_heading,|,bold,|,italic,|,underline,|,link,unlink,|,bullist,numlist,|,alignleft, aligncenter, alignright',
			'toolbar2' => '',
			'toolbar3' => '',
			'theme_advanced_statusbar_location' => 'none',
			'setup' =>  "function(ed){
				ed.onChange.add(function(ed, l) {
					var content	= ed.getContent();
					if(ed.isDirty() || content === '' ){
						ed.save();
						jQuery(ed.getElement()).blur(); // trigger change event for textarea
					}

				});

				// We set a tabindex value to the iframe instead of the initial textarea
				ed.onInit.add(function() {
					var editorId = ed.editorId,
						textarea = jQuery('#'+editorId);
					jQuery('#'+editorId+'_ifr').attr('tabindex', textarea.attr('tabindex'));
					textarea.attr('tabindex', null);
				});
			}"
		)
	));
}

function je_tinymce_add_plugins($plugin_array){
 
	$autoresize = get_template_directory_uri() . '/js/lib/tiny_mce/plugins/autoresize/editor_plugin.js';
	$et_heading = get_template_directory_uri() . '/js/lib/tiny_mce/plugins/et_heading/editor_plugin.js';
	$et_link	= get_template_directory_uri() . '/js/lib/tiny_mce/plugins/et_link/editor_plugin.js';

	$plugin_array['etHeading']	= $et_heading;
	$plugin_array['etLink']		= $et_link;

    return $plugin_array;
}
add_filter('mce_external_plugins','je_tinymce_add_plugins');


/**
 * add action to control not admin add action filter link query
*/

function je_tinymce_filter_link_query ( $query ) {
	if(current_user_can( 'manage_options' ) ) return $query;
	if( current_user_can( 'company' ) || current_user_can( 'jobseeker' ) ) {
		global $user_ID;
		$query['post_type']		=	'job';
		$query['post_status']	=	'publish';
		$query['post_author']	=	$user_ID;
	}
	return $query;
}
add_filter( 'wp_link_query_args', 'je_tinymce_filter_link_query' );
