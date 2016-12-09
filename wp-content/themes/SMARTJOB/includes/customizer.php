<?php 
define('CUSTOMIZE_DIR' , ET_CONTENT_DIR . '/css');
/**
 * Trigger the customization mode here
 * When administrator decide to customize something,
 * he trigger a link that activate "customization mode".
 *
 * When he finish customizing, he click on the close button
 * on customizer panel to close the "customization mode".
 */
function et_customizer_init(){

	$current_url = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	if ( isset($_REQUEST['activate']) && $_REQUEST['activate'] == 'customizer' ){
		setcookie('et-customizer', '1', time() + 3600, '/');
		wp_redirect(remove_query_arg('activate'));
		exit;
	} else if (isset($_REQUEST['deactivate']) && $_REQUEST['deactivate'] == 'customizer') {
		setcookie('et-customizer', '', time() - 3600, '/');
		wp_redirect(remove_query_arg('deactivate'));
		exit;
	}


	// check if customization is create or not
	if(!is_multisite() || (is_multisite() && get_current_blog_id() == 1) ) {
		if ( !file_exists( TEMPLATEPATH . '/css/customization.css' ) ){
			// save customization value into database
			$general_opt	=	ET_GeneralOptions::get_instance();
			$customization  = $general_opt->get_customization();
			$customization['pattern'] = "'" . $customization['pattern'] . "'";

			et_apply_customization($customization);
		}
	} else {
		$site_id	=	get_current_blog_id();
		if ( !file_exists( TEMPLATEPATH . '/css/customization_{$site_id}.css' ) ){
			$general_opt	=	new ET_GeneralOptions();
			$customization  = $general_opt->get_customization();

			if(isset($customization['pattern'])) {
				$customization['pattern'] = "'" . $customization['pattern'] . "'";
			}

			et_apply_customization($customization);
		}
	}
}
add_action('init', 'et_customizer_init');

function et_get_customize_css_path () {
	/**
	 * add multisite check for customize style
	*/
	if(is_multisite() && get_current_blog_id() != 1 ) {
		$blog_id	=	get_current_blog_id();
		$customize_css 	=	TEMPLATEURL . "/css/customization_$blog_id.css";
	} else {
		$customize_css 	=	TEMPLATEURL . "/css/customization.css";
	}
	return $customize_css;
}

function et_customizer_print_styles(){
	if ( current_user_can('manage_options') && !is_admin()){
		wp_enqueue_style('et_colorpicker');
		// include less
		?>
		<script type="text/javascript">
			var customizer = {};
			<?php
				$style 	= et_get_customization();
				$layout = et_get_layout();
				foreach ($style as $key => $value) {
					$variable = $key;
					//$variable = str_replace('-', '_', $key);
					if ( preg_match('/^rgb/', $value) ){
						preg_match('/rgb\(([0-9]+), ([0-9]+), ([0-9]+)\)/', $value, $matches);
						$val = rgb2html($matches[1],$matches[2],$matches[3]);
						echo "customizer['{$variable}'] = '{$val}';\n";
					} else {
						echo "customizer['{$variable}'] = '" . stripslashes($value) . "';\n";
					}
				}
				echo "customizer['layout'] = '{$layout}'";
			?>
		</script>
		<?php
	}
}

function et_customizer_print_scripts(){	
	if ( current_user_can('manage_options')&& !is_admin()){
		wp_enqueue_script('jquery-ui-widget');
		wp_enqueue_script('jquery-ui-slider');
		wp_register_script('et_customizer', TEMPLATEURL . '/js/customizer.js', array('jquery','underscore','backbone', 'job_engine'), false, true);
		wp_enqueue_script('et_colorpicker', false, array('jquery', 'underscore', 'backbone'));
		wp_enqueue_script('et_customizer');
		?>
		<link rel="stylesheet/less" type="txt/less" href="<?php echo TEMPLATEURL . '/css/define.less'?>">
		<?php
		wp_enqueue_script('less-js', TEMPLATEURL . '/js/less-1.3.1.min.js');
	}
}

function et_customizer_save(){
	if ( !current_user_can('manage_options') ) return;

	try {
		$customization = $_REQUEST['content']['customization'];

		if( empty($customization['font-text']) )
			$customization['font-text'] = 'Arial, san-serif';
		if( empty($customization['font-heading']) )
			$customization['font-heading'] = 'Arial, san-serif';

		// create css style from less file
		$clone = $customization;
		$clone['pattern'] = "'" . $clone['pattern'] . "'";
		et_apply_customization($clone);

		// set new layout
		et_set_layout( empty($customization['layout']) ? 'content-sidebar' : $customization['layout'] );

		// save customization value into database
		$general_opt	=	new ET_GeneralOptions();
		$general_opt->set_customization( $customization );

		$resp = array(
			'success' 	=> true,
			'code' 		=> 200,
			'msg' 		=> __("Changes are saved successfully.", ET_DOMAIN),
			'data' 		=> $general_opt->get_customization()
		);
	} catch (Exception $e) {
		$resp = array(
			'success' 	=> false,
			'code' 		=> true,
			'msg' 		=> sprintf(__("Something went wrong! System cause following error <br/> %s", ET_DOMAIN) , $e->getMessage() )
		);
	}

	wp_send_json($resp);

}


/**
 * Apple customization from user and create css file
 * @since 1.0
 * @param options
 */
function et_apply_customization($options = array(), $preview = false){
	$default = array(
		'background' 	=> '#ffffff',
		'header' 		=> '#4B4B4B',
		'heading' 		=> '#4B4B4B',
		'text' 			=> '#555555',
		'footer' 		=> '#E0E0E0',
		'action' 		=> '#E87863',
		'pattern' 		=> "'" . TEMPLATEURL . "/img/pattern.png'",
		'font-text' 	=> 'Arial, san-serif',
		'font-text-size' 	=> '14px',
		'font-heading' 		=> 'Arial, san-serif',
		'font-heading-size' 	=> '12px',
	);

	$options 	= wp_parse_args($options, $default);
	$keys 		= array_keys($default);

	foreach ($options as $key => $value) {
		if (!in_array($key,$keys)){
			unset($options[$key]);
		}
	}
	$less = TEMPLATEPATH . '/css/customization.less';
	$css = TEMPLATEPATH . '/css/customization.css';

	$mobile_less	=	TEMPLATEPATH . '/css/customization-mobile.less';
	$mobile_css		=	TEMPLATEPATH . '/mobile/css/customization.css';

	if( is_multisite() ) {
		$site_id	=	get_current_blog_id();
		if($site_id == 1) {
			$css = TEMPLATEPATH . '/css/customization.css';
			$mobile_css		=	TEMPLATEPATH . '/mobile/css/customization.css';
		}
		else {
			$css = TEMPLATEPATH . "/css/customization_$site_id.css";
			$mobile_css		=	TEMPLATEPATH . "/mobile/css/customization_$site_id.css";
		}
	}

	et_less2css( $less, $css, $options );
	et_less2css( $mobile_less, $mobile_css, $options );
}

/**
 * Show off the customizer pannel
 */
function et_customizer_panel(){
	if ( current_user_can('manage_options') ){
		$style 		= et_get_customization();
		$layout 	= et_get_layout();
		?>
		<div id="customizer" class="customizer-panel">
			<div class="close-panel"><a href="<?php echo add_query_arg('deactivate', 'customizer'); ?>" class=""><span>*</span></a></div> 
			<form action="" id="f_customizer">
				<div class="section">
					<div class="custom-head">
						<span class="spacer"></span><h3><?php _e('Color Schemes', ET_DOMAIN) ?></h3><span class="spacer"></span>
					</div>
					<div class="section-content">
						<ul class="blocks-grid">
							<li class="clr-block scheme-item" data="" style="background: #505050"></li>
							<li class="clr-block scheme-item" data="" style="background: #29435E"></li>
							<li class="clr-block scheme-item" data="" style="background: #46433A"></li>
							<li class="clr-block scheme-item" data="" style="background: #626266"></li>
							<li class="clr-block scheme-item" data="" style="background: #224743"></li>
							<li class="clr-block scheme-item" data="" style="background: #9A2620"></li>
							<li class="clr-block scheme-item" data="" style="background: #252D3B"></li>
							<li class="clr-block scheme-item" data="" style="background: #240B19"></li>
						</ul>
					</div>
				</div>
				<div class="section">
					<div class="custom-head">
						<span class="spacer"></span><h3><?php _e('Page Options', ET_DOMAIN) ?></h3><span class="spacer"></span>
					</div>
					<div class="section-content" style="display: none">
						<h4><?php _e('Layout Style', ET_DOMAIN) ?></h4>
						<ul class="block-layout">
							<li class="<?php if ($layout == 'sidebar-content') echo 'current' ?>">
								<a class="l-sidebar layout-item" rel="two-column left-sidebar" data="sidebar-content" href="" title="<?php _e('Left Sidebar', ET_DOMAIN) ?>"><span></span></a>
							</li>
							<li class="<?php if ($layout == 'content-sidebar') echo 'current' ?>">
								<a class="r-sidebar layout-item" rel="two-column right-sidebar" data="content-sidebar" href="" title="<?php _e('Right Sidebar', ET_DOMAIN) ?>"><span></span></a>
							</li>
							<li class="<?php if ($layout == 'content') echo 'current' ?>">
								<a class="no-sidebar layout-item" rel="one-column" data="content" href="" title="<?php _e('One column', ET_DOMAIN) ?>"><span></span></a>
							</li>
						</ul>
						<h4><?php _e('Background patterns', ET_DOMAIN) ?></h4>
						<ul class="blocks-grid">
							<li class="clr-block pattern-item pattern-0 <?php if ($style['pattern'] == (TEMPLATEURL . '/img/pattern.png')) echo 'current' ?>" data="<?php echo TEMPLATEURL . '/img/pattern.png' ?>"></li>
							<li class="clr-block pattern-item pattern-1 <?php if ($style['pattern'] == (TEMPLATEURL . '/img/pattern1.png')) echo 'current' ?>" data="<?php echo TEMPLATEURL . '/img/pattern1.png' ?>"></li>
							<li class="clr-block pattern-item pattern-2 <?php if ($style['pattern'] == (TEMPLATEURL . '/img/pattern2.png')) echo 'current' ?>" data="<?php echo TEMPLATEURL . '/img/pattern2.png' ?>"></li>
							<li class="clr-block pattern-item pattern-3 <?php if ($style['pattern'] == (TEMPLATEURL . '/img/pattern3.png')) echo 'current' ?>" data="<?php echo TEMPLATEURL . '/img/pattern3.png' ?>"></li>
							<li class="clr-block pattern-item pattern-4 <?php if ($style['pattern'] == (TEMPLATEURL . '/img/pattern4.png')) echo 'current' ?>" data="<?php echo TEMPLATEURL . '/img/pattern4.png' ?>"></li>
							<li class="clr-block pattern-item pattern-5 <?php if ($style['pattern'] == (TEMPLATEURL . '/img/pattern5.png')) echo 'current' ?>" data="<?php echo TEMPLATEURL . '/img/pattern5.png' ?>"></li>
							<li class="clr-block pattern-item pattern-6 <?php if ($style['pattern'] == (TEMPLATEURL . '/img/pattern6.png')) echo 'current' ?>" data="<?php echo TEMPLATEURL . '/img/pattern6.png' ?>"></li>
							<li class="clr-block pattern-item pattern-7 <?php if ($style['pattern'] == (TEMPLATEURL . '/img/pattern7.png')) echo 'current' ?>" data="<?php echo TEMPLATEURL . '/img/pattern7.png' ?>"></li>
						</ul>
						<h4><?php _e('Colors', ET_DOMAIN) ?></h4>
						<ul class="blocks-list">
							<li>
								<div class="picker-trigger clr-block" data="header" style="background: <?php echo $style['header'] ?>"></div>
								<span class="block-label"><?php _e('Header Background', ET_DOMAIN) ?></span>
							</li>
							<li>
								<div class="picker-trigger clr-block" data="background" style="background: <?php echo $style['background'] ?>"></div>
								<span class="block-label"><?php _e('Page Background', ET_DOMAIN) ?></span>
							</li>
							<li>
								<div class="picker-trigger clr-block" data="footer" style="background: <?php echo $style['footer'] ?>"></div>
								<span class="block-label"><?php _e('Footer Background', ET_DOMAIN) ?></span>
							</li>
							<li>
								<div class="picker-trigger clr-block" data="action" style="background: <?php echo $style['action'] ?>"></div>
								<span class="block-label"><?php _e('Hyperlink', ET_DOMAIN) ?></span>
							</li>
						</ul>
					</div>
				</div>
				<div class="section">
					<div class="custom-head">
						<span class="spacer"></span><h3><?php _e('Content Options', ET_DOMAIN) ?></h3><span class="spacer"></span>
					</div>
					<div class="section-content" style="display: none">
						<?php $fonts = apply_filters ('et_customize_fonts_face',array(
							'arial' 		=> array( 'fontface' => 'Arial, san-serif', 'name' => 'Arial' ),
							'helvetica' 	=> array( 'fontface' => 'Helvetica, san-serif', 'name' => 'Helvetica' ),
							'georgia'		=> array( 'fontface' => 'Georgia, serif', 'name' => 'Georgia' ),
							'times' 		=> array( 'fontface' => 'Times New Roman, serif', 'name' => 'Times New Roman' ),
							'quicksand'		=> array( 'fontface' => 'Quicksand, sans-serif', 'name' => 'Quicksand' ),
							'ebgaramond'	=> array( 'fontface' => 'EB Garamond, serif', 'name' => 'EB Garamond' ),
							'imprima' 		=> array( 'fontface' => 'Imprima, sans-serif', 'name' => 'Imprima' ),
							'ubuntu' 		=> array( 'fontface' => 'Ubuntu, sans-serif', 'name' => 'Ubuntu' ),
							'adventpro' 	=> array( 'fontface' => 'Advent Pro, sans-serif', 'name' => 'Advent Pro' ),
							'mavenpro' 		=> array( 'fontface' => 'Maven Pro, sans-serif', 'name' => 'Maven Pro' ) 
						)); ?>
						<div class="block-select">
							<label for=""><?php _e('Heading', ET_DOMAIN) ?></label>
							<div class="select-wrap">
								<div>
									<select class="fontchoose" name="font-heading">
										<?php foreach ($fonts as $key => $font) { ?>
											<option <?php if ( $style['font-heading'] == $font['fontface'] ) echo 'selected="selected"' ?> value="<?php echo $font['fontface'] ?>"><?php echo $font['name'] ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
						<div class="slider-wrap">
							<div class="slider heading-size" data-min="18" data-max="29" data-value="<?php echo str_replace( 'px', '', $style['font-heading-size'] ) ?>">
								<input type="hidden" name="font-heading-size">
							</div>
						</div>
						<div class="block-select">
							<label for=""><?php _e('Content', ET_DOMAIN) ?></label>
							<div class="select-wrap">
								<div>
									<select class="fontchoose" name="font-text" id="">
										<?php foreach ($fonts as $key => $font) {?>
											<option <?php if ( $style['font-text'] == $font['fontface'] ) echo 'selected="selected"' ?> value="<?php echo $font['fontface'] ?>"><?php echo $font['name'] ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
						<div class="slider-wrap">
							<div class="slider text-size" data-min="12" data-max="14" data-value="<?php echo str_replace( 'px', '', $style['font-text-size'] ) ?>">
								<input type="hidden" name="font-text-size">
							</div>
						</div>
					</div>
				</div>
				<button type="button" class="btn blue-btn" id="save_customizer" title="<?php _e('Save', ET_DOMAIN) ?>"><span><?php _e('Save', ET_DOMAIN) ?></span></button>
				<button type="button" class="btn blue-btn" id="reset_customizer" title="<?php _e('Reset', ET_DOMAIN) ?>"><span><?php _e('Reset', ET_DOMAIN) ?></span></button>
			</form>
		</div> <?php
	}
}



function rgb2html($r, $g=-1, $b=-1)
{
	if (is_array($r) && sizeof($r) == 3)
		list($r, $g, $b) = $r;

	$r = intval($r); $g = intval($g);
	$b = intval($b);

	$r = dechex($r<0?0:($r>255?255:$r));
	$g = dechex($g<0?0:($g>255?255:$g));
	$b = dechex($b<0?0:($b>255?255:$b));

	$color = (strlen($r) < 2?'0':'').$r;
	$color .= (strlen($g) < 2?'0':'').$g;
	$color .= (strlen($b) < 2?'0':'').$b;
	return '#'.$color;
}
?>