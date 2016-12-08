<?php 
/**
 * Declare Section payment in admin panel
 * @since 1.0
 */
class ET_MenuCustomization extends ET_EngineAdminSection{

	/**
	 * Constructor for payment menu item
	 * @since 1.0
	 */
	function __construct(){
		parent::__construct( __('CUSTOMIZE', ET_DOMAIN), __('CUSTOMIZE', ET_DOMAIN), __('Change the appearance of your website', ET_DOMAIN), 'icon-gear' );
	}

	/**
	 * Render view for payment item 
	 * @since 1.0
	 */
	public function view(){
		global $et_global;

		$general_opt	=	ET_GeneralOptions::get_instance();

		$this->get_header();
		$sub_section = empty($_REQUEST['subSection']) ? '' : $_REQUEST['subSection'];
		?>
		<div class="et-main-content">
			<div class="et-main-left">
				<ul class="et-menu-content inner-menu">
					<li><a href="#customize-branding" menu-data="branding" class="<?php echo in_array($sub_section, array('layout', 'style')) ? '' : "active"; ?>"><span class="icon" data-icon="b"></span><?php _e('Branding', ET_DOMAIN);?> </a></li>
					<li><a href="#customize-layout" menu-data="layout" class="<?php echo $sub_section == 'layout' ? "active" : ''; ?>"><span class="icon" data-icon="l"></span> <?php _e('Layout', ET_DOMAIN);?> </a></li>
					<li><a href="#customize-style" menu-data="style" class="<?php echo $sub_section == 'style' ? "active" : ''; ?>"><span class="icon" data-icon="p"></span><?php _e('Style', ET_DOMAIN);?> </a></li>	        				
				</ul>
			</div>
			<div id="customize-branding" <?php if ( in_array($sub_section, array('layout', 'style')) ) echo 'style="display: none"' ?> class="et-main-main inner-content branding clearfix subcontent-branding">
				<div class="title font-quicksand"><?php _e('Upload Logo', ET_DOMAIN );?></div>
				<div class="desc">
					<?php _e('Your logo should be in PNG, GIF or JPG format, within 150x50px  and less than 200Kb ', ET_DOMAIN);?>
					<div class="customization-info">
						<?php $uploaderID = 'website_logo';?>
						<div class="input-file upload-logo" id="<?php echo $uploaderID;?>_container">
							<?php 
							$website_logo = $general_opt->get_website_logo();
							if ($website_logo){ ?>
								<div class="left clearfix">
									<div class="image" id="<?php echo $uploaderID;?>_thumbnail">
										<img src="<?php echo $website_logo[0];?>"/>
									</div>
								</div>
							<?php } ?>
							<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( $uploaderID . '_et_uploader' ); ?>"></span>
							<span class="bg-grey-button button btn-button" id="<?php echo $uploaderID;?>_browse_button">
								<?php _e('Browse', ET_DOMAIN);?>
								<span class="icon" data-icon="o"></span>
							</span>
						</div>
					</div>
				</div>

				<div class="title font-quicksand margin-top30"><?php _e('Upload Mobile Icon', ET_DOMAIN);?></div>
				<div class="desc">
					<?php _e('This icon will be used as launcher icons for iPhone and Android smartphones and also as the website favicon. The image dimensions should be <strong>57x57px</strong>.', ET_DOMAIN);?>
					<div class="customization-info">
						<?php $uploaderID = 'mobile_icon';?>
						<div class="input-file  mobile-logo" id="<?php echo $uploaderID;?>_container">
							<?php 
							$mobile_icon = $general_opt->get_mobile_icon();
							if ($mobile_icon){
								?>
								<div class="left clearfix">
									<div class="image" id="<?php echo $uploaderID;?>_thumbnail">
										<img src="<?php echo $mobile_icon[0];?>"/>
									</div>
								</div>
							<?php } ?>
							<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( $uploaderID . '_et_uploader' ); ?>"></span>
							<span class="bg-grey-button button btn-button" id="<?php echo $uploaderID;?>_browse_button">
								<?php _e('Browse', ET_DOMAIN);?>
								<span class="icon" data-icon="o"></span>
							</span>
						</div>
					</div>
				</div>

				<div class="title font-quicksand margin-top30"><?php _e('Upload Default Company Logo', ET_DOMAIN);?></div>
				<div class="desc">
					<?php _e('This logo will be used as the default logo for any company posting job on your website without uploading their own logos. The logo should be a square image.', ET_DOMAIN);?>
					<div class="customization-info">
						<?php $uploaderID = 'default_logo';?>
						<div class="input-file  default-logo" id="<?php echo $uploaderID;?>_container">
							<?php 
							$default_logo = $general_opt->get_default_logo();
							if ($default_logo){
								?>
								<div class="left clearfix">
									<div class="image" id="<?php echo $uploaderID;?>_thumbnail">
										<img src="<?php echo $default_logo[0];?>"/>
									</div>
								</div>
							<?php } ?>
							<span class="et_ajaxnonce" id="<?php echo wp_create_nonce( $uploaderID . '_et_uploader' ); ?>"></span>
							<span class="bg-grey-button button btn-button" id="<?php echo $uploaderID;?>_browse_button">
								<?php _e('Browse', ET_DOMAIN);?>
								<span class="icon" data-icon="o"></span>
							</span>
						</div>
					</div>
				</div>
				
			</div>

			<!-- *********************************
				********  layout setting *********
				********************************** -->

			<div id="customize-layout" style="<?php echo $sub_section != 'layout' ? "display: none" : '' ?>" class="et-main-main inner-content clearfix subcontent-layout">
				<div class="title font-quicksand"><?php _e('Columns', ET_DOMAIN);?></div>
				<div class="desc">
					<?php
					_e('How many columns and where the sidebar should be.', ET_DOMAIN);
					$layouts		=	et_theme_layouts ();
					$used_layout	=	$general_opt->get_layout();
					?>
					<ul class="list-column-style">
					<?php foreach ($layouts as $key => $layout ) {
						$active	=	'';
						if( $used_layout ==  $key ) $active = 'active';
					?>
						<li>
							<a href="#<?php echo $key?>" rel="<?php echo $key?>" class="<?php echo $active ?>">
								<div class="<?php echo $layout['thumbnail'] ?>"></div>
								<span><?php echo $layout['label']?></span>
							</a>		        					
						</li>
						
					<?php }?>	        				
					</ul>
				</div>
				
			</div>

			<!-- *********************************
				***  Customize Style Section *****
				********************************** -->
			<div id="customize-style" style="<?php echo $sub_section != 'style' ? "display: none" : '' ?>" class="et-main-main inner-content clearfix subcontent-style">
				<!-- <div class="title font-quicksand">Select a Style</div>
				<div class="desc">
					<?php // _e('JobEngine comes by default with few styles you can choose from. Styles have different spacing and shapes leading to a different personality for your job board. <a class="find-out font-quicksand" href="#">Find out more <span class="icon" data-icon="i"></span></a>', ET_DOMAIN);?>
					<?php _e('JobEngine comes by default with a few styles you can choose from. You can also use one of these styles to create a unique personality for job board.', ET_DOMAIN);?>
					<ul class="list-template-thumb">
						<li>
							<a href="#" class="active">
								<div class="thumb">
								</div>
								<div class="name">Default Style</div>
								<div class="size">239kb</div>
							</a>		        					
						</li>
						<li>
							<a href="#">
								<div class="thumb">
								</div>
								<div class="name">Default Style</div>
								<div class="size">239kb</div>
							</a>	        					
						</li>
						<li>
							<a href="#">
								<div class="thumb">
								</div>
								<div class="name">Default Style</div>
								<div class="size">239kb</div>
							</a>	        					
						</li>		        				
						<li>
							<a href="#">
								<div class="thumb">
								</div>
								<div class="name">Default Style</div>
								<div class="size">239kb</div>
							</a>	        					
						</li>	
					</ul>
				</div> -->
				<!-- <div class="clear"></div> -->
				<?php 
					global $et_global;
					$style 			= 	$general_opt->get_customization();
					$style = wp_parse_args($style, array(
						'background' => '#ffffff',
						'header' 	=> '#4F4F4F',
						'heading' 	=> '#333333',
						'text' 		=> '#446f9f',
						'action' 	=> '#e64b21',
						'font-heading' 			=> '',
						'font-heading-weight' 	=> '',
						'font-heading-style' 	=> '',
						'font-heading-size' 	=> '14px',
						'font-text' 			=> '',
						'font-text-weight' 		=> '',
						'font-text-style' 		=> '',
						'font-text-size' 		=> '12px',
						'font-action' 			=> '',
						'font-action-weight' 	=> '',
						'font-action-style' 	=> '',
						'font-action-size' 		=> '12px',
						));
					$style['font-heading-size-num'] = preg_replace("/([0-9]+)+[em,px,%]*/", "$1", $style['font-heading-size']);
					$style['font-text-size-num'] 	= preg_replace("/([0-9]+)+[em,px,%]*/", "$1", $style['font-text-size']);
					$style['font-action-size-num'] 	= preg_replace("/([0-9]+)+[em,px,%]*/", "$1", $style['font-action-size']);
					$style['font-heading-size-num'] = is_numeric($style['font-heading-size-num']) ? $style['font-heading-size-num'] : 18;
					$style['font-text-size-num'] = is_numeric($style['font-text-size-num']) ? $style['font-text-size-num'] : 13;
					$style['font-action-size-num'] = is_numeric($style['font-action-size-num']) ? $style['font-action-size-num'] : 13;
					$colors 		=  	$general_opt->get_color_schemes();
					$choosen_color 	= 	$general_opt->get_choosen_color();

					// if colors doesn't exist set default colors
					if ( !is_array($colors) ) $colors = array();
					// $colors[0] = array('#fff', '#4F4F4F', '#333333', '#446f9f', '#e64b21');
					$colors[0] = array('#fcfcfc', '#464646', '#3595c7', '#555555', '#e87863');

				?>
				<form action="" id="change_style">
					<div class="title font-quicksand"><?php _e('Color Schemes', ET_DOMAIN) ?></div>
					<div class="desc"><?php _e('Choose one of our color schemes.', ET_DOMAIN) ?>
						<ul class="list-color-schemes">
							<li>
								<div class="<?php if ( $choosen_color == 0 ) echo 'active' ?>">
									<div class="cinner color-scheme">
										<div title="<?php _e('Background Color', ET_DOMAIN) ?>" class="color-item color-background" style="background-color:<?php echo $colors[0][0] ?>"></div>
										<div title="<?php _e('Header Color', ET_DOMAIN) ?>" class="color-item color-header" style="background-color: <?php echo $colors[0][1] ?>"></div>
										<div title="<?php _e('Hyperlink Text Color', ET_DOMAIN) ?>" class="color-item color-heading" style="background-color:<?php echo $colors[0][2] ?>"></div>
										<div title="<?php _e('Body Text Color', ET_DOMAIN) ?>" class="color-item color-text" style="background-color:<?php echo $colors[0][3] ?>"></div>
										<div title="<?php _e('Button Color', ET_DOMAIN) ?>" class="color-item color-action" style="background-color:<?php echo $colors[0][4] ?>"></div>
									</div>
								</div>
							</li>
							<?php
								for($i = 1; $i < 4; $i++){
									?>
									<li>
										<div class="<?php if ( $choosen_color == $i ) echo 'active' ?>">
											<div class="cinner color-scheme">
												<div title="<?php _e('Background Color', ET_DOMAIN) ?>" class="color-item color-background" style="background-color:<?php echo $colors[$i][0] ?>"><div class="overlay"><a href="#" class="modify" data-icon="p" ><?php _e('Change', ET_DOMAIN) ?></a></div></div>
												<div title="<?php _e('Header Color', ET_DOMAIN) ?>" class="color-item color-header" style="background-color: <?php echo $colors[$i][1] ?>"><div class="overlay"><a href="#" class="modify" data-icon="p" ><?php _e('Change', ET_DOMAIN) ?></a></div></div>
												<div title="<?php _e('Hyperlink Text Color', ET_DOMAIN) ?>" class="color-item color-heading" style="background-color:<?php echo $colors[$i][2] ?>"><div class="overlay"><a href="#" class="modify" data-icon="p" ><?php _e('Change', ET_DOMAIN) ?></a></div></div>
												<div title="<?php _e('Body Text Color', ET_DOMAIN) ?>" class="color-item color-text" style="background-color:<?php echo $colors[$i][3] ?>"><div class="overlay"><a href="#" class="modify" data-icon="p" ><?php _e('Change', ET_DOMAIN) ?></a></div></div>
												<div title="<?php _e('Button Color', ET_DOMAIN) ?>" class="color-item color-action" style="background-color:<?php echo $colors[$i][4] ?>"><div class="overlay"><a href="#" class="modify" data-icon="p" ><?php _e('Change', ET_DOMAIN) ?></a></div></div>
											</div>
										</div>
									</li>
									<?php
								}
							?>
						</ul>
					</div>

					<div class="title font-quicksand"><?php _e('Typography', ET_DOMAIN) ?></div>
					<div class="desc">
						<?php _e('Change the fonts, sizes and colors for the text on your website', ET_DOMAIN) ?>
						<?php $fonts = array(
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
						) ?>
						<ul class="list-font-style">
							<li>
	        					<div class="ftitle font-quicksand"><?php _e('Heading', ET_DOMAIN) ?></div>
	        					<div class="fcontent">
	        						<div class="select-style et-button-select">
        								<select name="font-heading-style" class="font-heading">
											<?php foreach ($fonts as $key => $font) {?>
												<option <?php if ( $style['font-heading'] == $font['fontface'] ) echo 'selected="selected"' ?> value="<?php echo $font['fontface'] ?>"><?php echo $font['name'] ?></option>
											<?php } ?>
										</select>
        							</div>
	        						<div class="slide">
										<div class="slider-area-1" data="<?php echo $style['font-heading-size-num'] ?>">
											<div class="slider">
												<div class="notification-value"></div>
											</div>
											<div class="pad-line"></div>
											<input class="font-heading-size" type="hidden" name="font-heading-size" value="<?php echo $style['font-heading-size'] ?>" >
										</div>
			        				</div> <!-- end slide -->
	        					</div>
	        				</li>
	        				<li>
	        					<div class="ftitle font-quicksand"><?php _e('Text', ET_DOMAIN) ?></div>
	        					<div class="fcontent">
	        						<div class="select-style et-button-select">
        								<select name="font-text" class="font-text">
											<?php foreach ($fonts as $key => $font) {?>
												<option <?php if ( $style['font-text'] == $font['fontface'] ) echo 'selected="selected"' ?> value="<?php echo $font['fontface'] ?>"><?php echo $font['name'] ?></option>
											<?php } ?>
										</select>
        							</div>
				        			<div class="slide">
										<div class="slider-area-2" data="<?php echo $style['font-text-size-num']; ?>">
											<div class="slider">
												<div class="notification-value"></div>
											</div>
											<div class="pad-line"></div>
											<input class="font-text-size" type="hidden" name="font-text-size" value="<?php echo $style['font-text-size'] ?>" >
											<input class="font-action-size" type="hidden" name="font-action-size" value="<?php echo $style['font-action-size'] ?>" >
										</div>
			        				</div> <!-- end slide -->
	        					</div>
	        				</li>
						</ul>
						<div class="clear"></div>
						<div class="btn-language padding-top10 f-left-all">
							<button class="primary-button" id="save_style"><?php _e('Save', ET_DOMAIN)?></button>
							<a href="#" class="backend-button" id="preview_style"><?php _e('Preview', ET_DOMAIN)?></a>
						</div>
					</div>

					<div class="clear"></div>
					<div class="title font-quicksand"><?php _e('Custom style', ET_DOMAIN) ?></div>
					<div class="desc"><?php _e('Place your custom style (css) into this box', ET_DOMAIN) ?>
						 <br>
						<?php ?>
						<div class="form no-margin no-padding no-background">
							<div class="form-item">
								<textarea name="custom_style" class="autosize" id="" cols="30" rows="10"><?php echo $general_opt->get_custom_style() ?></textarea>	
							</div>
						</div>
					</div>
				</form>
			</div>

		</div>
		<?php
		echo $this->get_footer();
	}
}

/**
 * Register menu setting
 */
// function et_register_menu_customization(){
// 	// register payment menu item
// 	et_register_menu_section('ET_MenuCustomization');
// }
// add_action('et_admin_menu', 'et_register_menu_customization');
