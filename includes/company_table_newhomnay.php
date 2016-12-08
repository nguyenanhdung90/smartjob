<?php
add_action('admin_menu', 'register_my_custom_submenu_page');
function register_my_custom_submenu_page() 
{
	add_submenu_page( 'customcompany', 'Add New Company', 'Add new company ', 'read_private_pages', 'add-new-company-page', 'add_new_company_callback' );
}
function add_new_company_callback() 
{	
	if(!isset($_GET['gate']))
	{
		if(isset($_POST['publish_company']))
		{
			if($_POST['post_title_company']=="")
			{
				$name_com="Bạn  chưa nhập thông tin công ty";
			}
			else
			{
				global $wpdb;$tam=date('Y-m-d h:i:s');$current_user= wp_get_current_user();
			    $ta="";
			    if($_POST['logo_com']!=""){$ta=pn_get_attachment_id_from_url($_POST['logo_com']);$ta=et_get_attachment_data($ta);$ta=serialize($ta);}
				$wpdb->query("INSERT INTO  wp_post_company 
				(display_name,users_id,user_email,user_url,user_registered,logo,decription,manager)
				VALUES ('".$_POST['post_title_company']."','".$current_user->ID."','".$_POST['mail_compan']."','".$_POST['url_com']."','".$tam."','".$ta."','".$_POST['content_compan']."','".$current_user->roles[0]."')"); 
				unset($tam);unset($current_user);unset($ta);$sucess="Thêm thành công"; 
			}
		}		
	}
	else
	{		
		if($_GET['manager']=="company")
		{
			if(isset($_POST['publish_company']))
			{
				if($_POST['post_title_company']=="")
				{
					$name_com="Bạn  chưa nhập thông tin công ty";
				}
				else
				{
					global $wpdb;$current_user= wp_get_current_user();
					$ta="";
					if($_POST['logo_com']!=""){$ta=pn_get_attachment_id_from_url($_POST['logo_com']);$ta=et_get_attachment_data($ta);$ta=serialize($ta);}
					$wpdb->query("UPDATE   wp_post_company SET display_name='".$_POST['post_title_company']."',user_email='".$_POST['mail_compan']."',
					user_url='".$_POST['url_com']."',logo='".$ta."',decription='".$_POST['content_compan']."'
					WHERE ID='".$_GET['gate']."'"); 
					$wpdb->query("UPDATE   wp_users SET display_name='".$_POST['post_title_company']."',user_email='".$_POST['mail_compan']."',
					user_url='".$_POST['url_com']."' WHERE ID='".$_GET['userid']."'"); 					
					$wpdb->query("UPDATE   wp_usermeta SET meta_value='".$ta."' WHERE user_id='".$_GET['userid']."' and  meta_key='et_user_logo'"); 					
					unset($current_user);unset($ta);$sucess="Sửa thành công com"; 
				}
			}			
		}
		else
		{
			if(isset($_POST['publish_company']))
			{
				if($_POST['post_title_company']=="")
				{
					$name_com="Bạn  chưa nhập thông tin công ty";
				}
				else
				{
					global $wpdb;$current_user= wp_get_current_user();
					$ta="";
					if($_POST['logo_com']!=""){$ta=pn_get_attachment_id_from_url($_POST['logo_com']);$ta=et_get_attachment_data($ta);$ta=serialize($ta);}
					$wpdb->query("UPDATE   wp_post_company SET display_name='".$_POST['post_title_company']."',user_email='".$_POST['mail_compan']."',
					user_url='".$_POST['url_com']."',logo='".$ta."',decription='".$_POST['content_compan']."'
					WHERE ID='".$_GET['gate']."'"); 
					unset($current_user);unset($ta);$sucess="Sửa thành công"; 
				}
			}			
		}
		global $wpdb;
		$data_edit=$wpdb->get_results( "SELECT * FROM wp_post_company WHERE ID= '".$_GET['gate']."' " );
		$name_company=$data_edit[0]->display_name;
		$content_company=$data_edit[0]->decription;
		$Website_company=$data_edit[0]->user_url;
		$mail_company=$data_edit[0]->user_email;
		$logo_company=$data_edit[0]->logo;$logo_company=unserialize($logo_company);	$logo_company=$logo_company['large'][0];

	}
?>	
<!-- -------------------------------bat dau script cua uplaoad anh------------------------------------------------->

<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 wp-toolbar"  lang="en-US">
<![endif]-->
<!--[if !(IE 8) ]><!-->
<!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<script type="text/javascript">
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
var ajaxurl = '/wp-admin/admin-ajax.php',
	pagenow = 'post',
	typenow = 'post',
	adminpage = 'post-new-php',
	thousandsSeparator = ',',
	decimalPoint = '.',
	isRtl = 0;
</script>
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<style type="text/css">
img.wp-smiley,
img.emoji {
	display: inline !important;
	border: none !important;
	box-shadow: none !important;
	height: 1em !important;
	width: 1em !important;
	margin: 0 .07em !important;
	vertical-align: -0.1em !important;
	background: none !important;
	padding: 0 !important;
}
</style>
<link rel='stylesheet' href='http://smartjob.vn/wp-admin/load-styles.php?c=1&amp;dir=ltr&amp;load=dashicons,admin-bar,buttons,media-views,wp-admin,wp-auth-check&amp;ver=4.3.1' type='text/css' media='all' />
<link rel='stylesheet' id='open-sans-css'  href='https://fonts.googleapis.com/css?family=Open+Sans%3A300italic%2C400italic%2C600italic%2C300%2C400%2C600&#038;subset=latin%2Clatin-ext&#038;ver=4.3.1' type='text/css' media='all' />
<link rel='stylesheet' id='thickbox-css'  href='http://smartjob.vn/wp-includes/js/thickbox/thickbox.css?ver=4.3.1' type='text/css' media='all' />
<link rel='stylesheet' id='mediaelement-css'  href='http://smartjob.vn/wp-includes/js/mediaelement/mediaelementplayer.min.css?ver=2.17.0' type='text/css' media='all' />
<link rel='stylesheet' id='wp-mediaelement-css'  href='http://smartjob.vn/wp-includes/js/mediaelement/wp-mediaelement.css?ver=4.3.1' type='text/css' media='all' />
<link rel='stylesheet' id='imgareaselect-css'  href='http://smartjob.vn/wp-includes/js/imgareaselect/imgareaselect.css?ver=0.9.8' type='text/css' media='all' />
<!--[if lte IE 7]>
<link rel='stylesheet' id='ie-css'  href='http://smartjob.vn/wp-admin/css/ie.min.css?ver=4.3.1' type='text/css' media='all' />
<![endif]-->
<link rel='stylesheet' id='miw_customcss-css'  href='http://smartjob.vn/wp-content/plugins/multi-image-widget/assets/css/miw_admin.css?ver=4.3.1' type='text/css' media='all' />
<link rel='stylesheet' id='engine_styles-css'  href='http://smartjob.vn/wp-content/themes/SMARTJOB/includes/core/css/engine.css?ver=4.3.1' type='text/css' media='all' />
<link rel='stylesheet' id='et_colorpicker-css'  href='http://smartjob.vn/wp-content/themes/SMARTJOB/includes/core/js/lib/css/colorpicker.css?ver=4.3.1' type='text/css' media='all' />
		<script type="text/javascript">
			window._wpemojiSettings = {"baseUrl":"http:\/\/s.w.org\/images\/core\/emoji\/72x72\/","ext":".png","source":{"concatemoji":"http:\/\/smartjob.vn\/wp-includes\/js\/wp-emoji-release.min.js?ver=4.3.1"}};
			!function(a,b,c){function d(a){var c=b.createElement("canvas"),d=c.getContext&&c.getContext("2d");return d&&d.fillText?(d.textBaseline="top",d.font="600 32px Arial","flag"===a?(d.fillText(String.fromCharCode(55356,56812,55356,56807),0,0),c.toDataURL().length>3e3):(d.fillText(String.fromCharCode(55357,56835),0,0),0!==d.getImageData(16,16,1,1).data[0])):!1}function e(a){var c=b.createElement("script");c.src=a,c.type="text/javascript",b.getElementsByTagName("head")[0].appendChild(c)}var f,g;c.supports={simple:d("simple"),flag:d("flag")},c.DOMReady=!1,c.readyCallback=function(){c.DOMReady=!0},c.supports.simple&&c.supports.flag||(g=function(){c.readyCallback()},b.addEventListener?(b.addEventListener("DOMContentLoaded",g,!1),a.addEventListener("load",g,!1)):(a.attachEvent("onload",g),b.attachEvent("onreadystatechange",function(){"complete"===b.readyState&&c.readyCallback()})),f=c.source||{},f.concatemoji?e(f.concatemoji):f.wpemoji&&f.twemoji&&(e(f.twemoji),e(f.wpemoji)))}(window,document,window._wpemojiSettings);
		</script>
		
<script type='text/javascript'>
/* <![CDATA[ */
var userSettings = {"url":"\/","uid":"1","time":"1452564278","secure":""};/* ]]> */
</script>
<script type='text/javascript' src='http://smartjob.vn/wp-admin/load-scripts.php?c=1&amp;load%5B%5D=jquery-core,jquery-migrate,utils,plupload&amp;ver=4.3.1'></script>
<!--[if lt IE 8]>
<script type='text/javascript' src='http://smartjob.vn/wp-includes/js/json2.min.js?ver=2011-02-23'></script>
<![endif]-->
	  
		<script>
			function aioseop_show_pointer( handle, value ) {
				if ( typeof( jQuery ) != 'undefined' ) {
					var p_edge = 'bottom';
					var p_align = 'center';
					if ( typeof( jQuery( value.pointer_target ).pointer) != 'undefined' ) {
						if ( typeof( value.pointer_edge ) != 'undefined' ) p_edge = value.pointer_edge;
						if ( typeof( value.pointer_align ) != 'undefined' ) p_align = value.pointer_align;
						jQuery(value.pointer_target).pointer({
									content    : value.pointer_text,
									position: {
										edge: p_edge,
										align: p_align
									},
									close  : function() {
										jQuery.post( ajaxurl, {
											pointer: handle,
											action: 'dismiss-wp-pointer'
										});
									}
								}).pointer('open');
					}
				}
			}
					</script>
			<link id="wp-admin-canonical" rel="canonical" href="http://smartjob.vn/wp-admin/post-new.php" />
	<script>
		if ( window.history.replaceState ) {
			window.history.replaceState( null, null, document.getElementById( 'wp-admin-canonical' ).href + window.location.hash );
		}
	</script>
<script type="text/javascript">var _wpColorScheme = {"icons":{"base":"#999","focus":"#00a0d2","current":"#fff"}};</script>
<script type="text/javascript" src="http://smartjob.vn/wp-content/plugins/all-in-one-seo-pack/quickedit_functions.js" ></script><style>
		.aioseop_edit_button {
			margin: 0 0 0 5px;
			opacity: 0.6;
			width: 12px;
		}
		.aioseop_edit_link {
			display: inline-block;
			position: absolute;
		}
		.aioseop_mpc_SEO_admin_options_edit img {
			margin: 3px 2px;
			opacity: 0.7;
		}
		.aioseop_mpc_admin_meta_options {
			float: left;
			display: block;
			opacity: 1;
			max-height: 75px;
			overflow: hidden;
			width: 100%;
		}
		.aioseop_mpc_admin_meta_options.editing {
			max-height: initial;
			overflow: visible;
		}
		.aioseop_mpc_admin_meta_content {
			float:left;
			width: 100%;
			margin: 0 0 10px 0;
		}
		td.seotitle.column-seotitle,
		td.seodesc.column-seodesc,
		td.seokeywords.column-seokeywords {
			overflow: visible;
		}
		@media screen and (max-width: 782px) {
			body.wp-admin th.column-seotitle, th.column-seodesc, th.column-seokeywords, td.seotitle.column-seotitle, td.seodesc.column-seodesc, td.seokeywords.column-seokeywords {
			  display: none;
			}
		}
		</style>
		<script type='text/javascript' src='http://smartjob.vn/wp-includes/js/tw-sack.min.js?ver=1.6.1'></script>
</head>

<script type="text/javascript">
	document.body.className = document.body.className.replace('no-js','js');
</script>

	<script type="text/javascript">
		(function() {
			var request, b = document.body, c = 'className', cs = 'customize-support', rcs = new RegExp('(^|\\s+)(no-)?'+cs+'(\\s+|$)');

			request = true;

			b[c] = b[c].replace( rcs, ' ' );
			b[c] += ( window.postMessage && request ? ' ' : ' no-' ) + cs;
		}());
	</script>
<script type='text/javascript' src='http://smartjob.vn/wp-content/themes/SMARTJOB/epanel/js/checkbox.js?ver=4.3.1'></script>
<script type='text/javascript' src='http://smartjob.vn/wp-content/themes/SMARTJOB/epanel/js/functions-init.js?ver=4.3.1'></script>
<script type='text/javascript' src='http://smartjob.vn/wp-content/themes/SMARTJOB/epanel/js/colorpicker.js?ver=4.3.1'></script>
<script type='text/javascript' src='http://smartjob.vn/wp-content/themes/SMARTJOB/epanel/js/eye.js?ver=4.3.1'></script>
<script type='text/javascript' src='http://smartjob.vn/wp-content/themes/SMARTJOB/epanel/js/custom_uploader.js?ver=4.3.1'></script>
<!-- -----------------------------------ket thuc script cua upload anh------------------------------------------ -->
<div id="wpbody" role="main">	
	<div class="wrap">
	    <?php if(isset($_GET['gate'])){?> <h1>Edit Information  Company</h1><?php }else{?><h1>Add New Company</h1><?php }?>
	   
		<h2 style="color:red"><?php if(isset($sucess))echo $sucess;unset($sucess)?></h2>
		<form name="post" action="?page=add-new-company-page<?php if(isset($_GET['gate']))echo '&gate='.$_GET['gate'].'&manager='.$_GET['manager'].'&userid='.$_GET['userid'];?>" method="post" id="post">
			<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="post-body-content">
							<div id="titlediv">
								<div id="titlewrap">									
									<?php if(isset($name_com)){?>
									<label class="screen-reader-text" id="title-prompt-text" style="color:red" for="title"><?php echo $name_com;?></label>
									<?php 
									}elseif(isset($data_edit)) {?>
									<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo $data_edit->display_name; ?></label>
									<?php }else{?>
									<label class="screen-reader-text" id="title-prompt-text" for="title">Enter title here</label>
                                    <?php }?>
									<input type="text" name="post_title_company" size="30" value="<?php if(isset($name_company))echo $name_company;?>" id="title" spellcheck="true" autocomplete="off" />
								</div>
							    <input type="hidden" id="samplepermalinknonce" name="samplepermalinknonce" value="be38683dda" />
							</div><!-- /titlediv -->
							<div id="postdivrich" class="postarea wp-editor-expand">
								<div id="wp-content-wrap" class="wp-core-ui wp-editor-wrap tmce-active has-dfw"><link rel='stylesheet' id='editor-buttons-css'  href='<?php bloginfo('url');?>/wp-includes/css/editor.min.css?ver=4.3.1' type='text/css' media='all' />
									<div id="wp-content-editor-tools" class="wp-editor-tools hide-if-no-js"><div id="wp-content-media-buttons" class="wp-media-buttons"><button type="button" id="insert-media-button" class="button insert-media add_media" data-editor="content"><span class="wp-media-buttons-icon"></span> Add Media</button></div>
										<div class="wp-editor-tabs"><button type="button" id="content-tmce" class="wp-switch-editor switch-tmce" data-wp-editor-id="content">Visual</button>
											<button type="button" id="content-html" class="wp-switch-editor switch-html" data-wp-editor-id="content">Text</button>
										</div>
									</div>
									<div id="wp-content-editor-container" class="wp-editor-container">
										<div id="ed_toolbar" class="quicktags-toolbar"></div>
										<textarea class="wp-editor-area" style="height: 300px" autocomplete="off" cols="40" name="content_compan" id="content"><?php if(isset($content_company))echo $content_company;?></textarea>
									</div>
								</div>
								<table id="post-status-info">
									<tbody>
										<tr>
											<td id="wp-word-count">Word count: <span class="word-count">0</span></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div><!-- /post-body-content -->
						<div id="postbox-container-1" class="postbox-container">
							<div id="side-sortables" class="meta-box-sortables">
								<div id="submitdiv" class="postbox " >
									<div class="handlediv" title="Click to toggle"><br /></div>
									<h3 class='hndle'><span>Publish</span></h3>
									<div class="inside">
										<div class="submitbox" id="submitpost">
											<div id="minor-publishing">
												<div id="minor-publishing-actions">
													<div id="preview-action">
													<a class="preview button" href="<?php bloginfo('url');?>/?post_type=job&#038;p=770&#038;preview=true" target="wp-preview-770" id="post-preview">Preview</a>
													<input type="hidden" name="wp-preview" id="wp-preview" value="" />
													</div>
												</div><!-- #minor-publishing-actions -->
											</div>
											<div id="major-publishing-actions" style="overflow:hidden">
												<div id="publishing-action">											
														<input name="original_publish" type="hidden" id="original_publish" value="Publish" />
														<input type="submit" name="publish_company" id="publish" class="button button-primary button-large" value="Publish"  />
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id="postbox-container-2" class="postbox-container">
							<div id="normal-sortables" class="meta-box-sortables">
								<div id="et_job" class="postbox " >
									<div class="handlediv" title="Click to toggle"><br /></div>
									<h3 class='hndle'><span>Company information</span></h3>
									<div class="inside">
										<style>
											.et-field{
												width: 400px;
											}
										</style>
										<p>
											<label for=""><strong>Company Email</strong></label> <br>
											<input type="text" id="et_location" name="mail_compan" class="et-field" value="<?php if(isset($mail_company))echo $mail_company;?>">
										</p>		
										<p>
											<label for=""><strong>Company Website</strong></label> <br>
											<input type="text" name="url_com" class="et-field" value="<?php if(isset($Website_company))echo $Website_company;?>"> <br>
										</p>										
										<p>
										<img style="width:200px" src="<?php if(isset($logo_company)) echo $logo_company;?>"></br>
											<label for=""><strong>Upload company Logo </strong></label> <br>
											<div class="box-content">
												<input id="sportline_favicon" class="uploadfield" type="text" size="90" name="logo_com" value="<?php if(isset($logo_company)) echo $logo_company;?>" />
												<div class="upload_buttons">
													<span class="upload_image_reset">Reset</span>
													<input class="upload_image_button" type="button" value="Upload Image" />
												</div>
											</div> <!-- end box-content div -->	
										</p>		
									<script type="text/template" id="et_companies">[]	</script>
									</div>
								</div>
							</div>
						</div>
					</div><!-- /post-body -->
			</div><!-- /poststuff -->
		</form>
	</div>
</div><!-- wpbody -->
	<!--[if lte IE 8]>
	<style>
		.attachment:focus {
			outline: #1e8cbe solid;
		}
		.selected.attachment {
			outline: #1e8cbe solid;
		}
	</style>
	<![endif]-->


	<script type="text/html" id="tmpl-media-modal">
		<div class="media-modal wp-core-ui">
			<button type="button" class="button-link media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span></button>
			<div class="media-modal-content"></div>
		</div>
		<div class="media-modal-backdrop"></div>
	</script>

	<script type="text/html" id="tmpl-uploader-window">
		<div class="uploader-window-content">
			<h3>Drop files to upload</h3>
		</div>
	</script>

	<script type="text/html" id="tmpl-uploader-editor">
		<div class="uploader-editor-content">
			<div class="uploader-editor-title">Drop files to upload</div>
		</div>
	</script>

	<script type="text/html" id="tmpl-uploader-inline">
		<# var messageClass = data.message ? 'has-upload-message' : 'no-upload-message'; #>
		<# if ( data.canClose ) { #>
		<button class="close dashicons dashicons-no"><span class="screen-reader-text">Close uploader</span></button>
		<# } #>
		<div class="uploader-inline-content {{ messageClass }}">
		<# if ( data.message ) { #>
			<h3 class="upload-message">{{ data.message }}</h3>
		<# } #>
					<div class="upload-ui">
				<h3 class="upload-instructions drop-instructions">Drop files anywhere to upload</h3>
				<p class="upload-instructions drop-instructions">or</p>
				<button type="button" class="browser button button-hero">Select Files</button>
			</div>

			<div class="upload-inline-status"></div>

			<div class="post-upload-ui">
				
				<p class="max-upload-size">Maximum upload file size: 1 GB.</p>

				<# if ( data.suggestedWidth && data.suggestedHeight ) { #>
					<p class="suggested-dimensions">
						Suggested image dimensions: {{data.suggestedWidth}} &times; {{data.suggestedHeight}}
					</p>
				<# } #>

							</div>
				</div>
	</script>

	<script type="text/html" id="tmpl-media-library-view-switcher">
		<a href="/wp-admin/post-new.php?mode=list" class="view-list">
			<span class="screen-reader-text">List View</span>
		</a>
		<a href="/wp-admin/post-new.php?mode=grid" class="view-grid current">
			<span class="screen-reader-text">Grid View</span>
		</a>
	</script>

	<script type="text/html" id="tmpl-uploader-status">
		<h3>Uploading</h3>
		<button type="button" class="button-link upload-dismiss-errors"><span class="screen-reader-text">Dismiss Errors</span></button>

		<div class="media-progress-bar"><div></div></div>
		<div class="upload-details">
			<span class="upload-count">
				<span class="upload-index"></span> / <span class="upload-total"></span>
			</span>
			<span class="upload-detail-separator">&ndash;</span>
			<span class="upload-filename"></span>
		</div>
		<div class="upload-errors"></div>
	</script>

	<script type="text/html" id="tmpl-uploader-status-error">
		<span class="upload-error-filename">{{{ data.filename }}}</span>
		<span class="upload-error-message">{{ data.message }}</span>
	</script>

	<script type="text/html" id="tmpl-edit-attachment-frame">
		<div class="edit-media-header">
			<button class="left dashicons <# if ( ! data.hasPrevious ) { #> disabled <# } #>"><span class="screen-reader-text">Edit previous media item</span></button>
			<button class="right dashicons <# if ( ! data.hasNext ) { #> disabled <# } #>"><span class="screen-reader-text">Edit next media item</span></button>
		</div>
		<div class="media-frame-title"></div>
		<div class="media-frame-content"></div>
	</script>

	<script type="text/html" id="tmpl-attachment-details-two-column">
		<div class="attachment-media-view {{ data.orientation }}">
			<div class="thumbnail thumbnail-{{ data.type }}">
				<# if ( data.uploading ) { #>
					<div class="media-progress-bar"><div></div></div>
				<# } else if ( 'image' === data.type && data.sizes && data.sizes.large ) { #>
					<img class="details-image" src="{{ data.sizes.large.url }}" draggable="false" />
				<# } else if ( 'image' === data.type && data.sizes && data.sizes.full ) { #>
					<img class="details-image" src="{{ data.sizes.full.url }}" draggable="false" />
				<# } else if ( -1 === jQuery.inArray( data.type, [ 'audio', 'video' ] ) ) { #>
					<img class="details-image icon" src="{{ data.icon }}" draggable="false" />
				<# } #>

				<# if ( 'audio' === data.type ) { #>
				<div class="wp-media-wrapper">
					<audio style="visibility: hidden" controls class="wp-audio-shortcode" width="100%" preload="none">
						<source type="{{ data.mime }}" src="{{ data.url }}"/>
					</audio>
				</div>
				<# } else if ( 'video' === data.type ) {
					var w_rule = h_rule = '';
					if ( data.width ) {
						w_rule = 'width: ' + data.width + 'px;';
					} else if ( wp.media.view.settings.contentWidth ) {
						w_rule = 'width: ' + wp.media.view.settings.contentWidth + 'px;';
					}
					if ( data.height ) {
						h_rule = 'height: ' + data.height + 'px;';
					}
				#>
				<div style="{{ w_rule }}{{ h_rule }}" class="wp-media-wrapper wp-video">
					<video controls="controls" class="wp-video-shortcode" preload="metadata"
						<# if ( data.width ) { #>width="{{ data.width }}"<# } #>
						<# if ( data.height ) { #>height="{{ data.height }}"<# } #>
						<# if ( data.image && data.image.src !== data.icon ) { #>poster="{{ data.image.src }}"<# } #>>
						<source type="{{ data.mime }}" src="{{ data.url }}"/>
					</video>
				</div>
				<# } #>

				<div class="attachment-actions">
					<# if ( 'image' === data.type && ! data.uploading && data.sizes && data.can.save ) { #>
					<button type="button" class="button edit-attachment">Edit Image</button>
					<# } #>
				</div>
			</div>
		</div>
		<div class="attachment-info">
			<span class="settings-save-status">
				<span class="spinner"></span>
				<span class="saved">Saved.</span>
			</span>
			<div class="details">
				<div class="filename"><strong>File name:</strong> {{ data.filename }}</div>
				<div class="filename"><strong>File type:</strong> {{ data.mime }}</div>
				<div class="uploaded"><strong>Uploaded on:</strong> {{ data.dateFormatted }}</div>

				<div class="file-size"><strong>File size:</strong> {{ data.filesizeHumanReadable }}</div>
				<# if ( 'image' === data.type && ! data.uploading ) { #>
					<# if ( data.width && data.height ) { #>
						<div class="dimensions"><strong>Dimensions:</strong> {{ data.width }} &times; {{ data.height }}</div>
					<# } #>
				<# } #>

				<# if ( data.fileLength ) { #>
					<div class="file-length"><strong>Length:</strong> {{ data.fileLength }}</div>
				<# } #>

				<# if ( 'audio' === data.type && data.meta.bitrate ) { #>
					<div class="bitrate">
						<strong>Bitrate:</strong> {{ Math.round( data.meta.bitrate / 1000 ) }}kb/s
						<# if ( data.meta.bitrate_mode ) { #>
						{{ ' ' + data.meta.bitrate_mode.toUpperCase() }}
						<# } #>
					</div>
				<# } #>

				<div class="compat-meta">
					<# if ( data.compat && data.compat.meta ) { #>
						{{{ data.compat.meta }}}
					<# } #>
				</div>
			</div>

			<div class="settings">
				<label class="setting" data-setting="url">
					<span class="name">URL</span>
					<input type="text" value="{{ data.url }}" readonly />
				</label>
				<# var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly'; #>
								<label class="setting" data-setting="title">
					<span class="name">Title</span>
					<input type="text" value="{{ data.title }}" {{ maybeReadOnly }} />
				</label>
								<# if ( 'audio' === data.type ) { #>
								<label class="setting" data-setting="artist">
					<span class="name">Artist</span>
					<input type="text" value="{{ data.artist || data.meta.artist || '' }}" />
				</label>
								<label class="setting" data-setting="album">
					<span class="name">Album</span>
					<input type="text" value="{{ data.album || data.meta.album || '' }}" />
				</label>
								<# } #>
				<label class="setting" data-setting="caption">
					<span class="name">Caption</span>
					<textarea {{ maybeReadOnly }}>{{ data.caption }}</textarea>
				</label>
				<# if ( 'image' === data.type ) { #>
					<label class="setting" data-setting="alt">
						<span class="name">Alt Text</span>
						<input type="text" value="{{ data.alt }}" {{ maybeReadOnly }} />
					</label>
				<# } #>
				<label class="setting" data-setting="description">
					<span class="name">Description</span>
					<textarea {{ maybeReadOnly }}>{{ data.description }}</textarea>
				</label>
				<label class="setting">
					<span class="name">Uploaded By</span>
					<span class="value">{{ data.authorName }}</span>
				</label>
				<# if ( data.uploadedToTitle ) { #>
					<label class="setting">
						<span class="name">Uploaded To</span>
						<# if ( data.uploadedToLink ) { #>
							<span class="value"><a href="{{ data.uploadedToLink }}">{{ data.uploadedToTitle }}</a></span>
						<# } else { #>
							<span class="value">{{ data.uploadedToTitle }}</span>
						<# } #>
					</label>
				<# } #>
				<div class="attachment-compat"></div>
			</div>

			<div class="actions">
				<a class="view-attachment" href="{{ data.link }}">View attachment page</a>
				<# if ( data.can.save ) { #> |
					<a href="post.php?post={{ data.id }}&action=edit">Edit more details</a>
				<# } #>
				<# if ( ! data.uploading && data.can.remove ) { #> |
											<button type="button" class="button-link delete-attachment">Delete Permanently</button>
									<# } #>
			</div>

		</div>
	</script>

	<script type="text/html" id="tmpl-attachment">
		<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">
			<div class="thumbnail">
				<# if ( data.uploading ) { #>
					<div class="media-progress-bar"><div style="width: {{ data.percent }}%"></div></div>
				<# } else if ( 'image' === data.type && data.sizes ) { #>
					<div class="centered">
						<img src="{{ data.size.url }}" draggable="false" alt="" />
					</div>
				<# } else { #>
					<div class="centered">
						<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
							<img src="{{ data.image.src }}" class="thumbnail" draggable="false" />
						<# } else { #>
							<img src="{{ data.icon }}" class="icon" draggable="false" />
						<# } #>
					</div>
					<div class="filename">
						<div>{{ data.filename }}</div>
					</div>
				<# } #>
			</div>
			<# if ( data.buttons.close ) { #>
				<button type="button" class="button-link attachment-close media-modal-icon"><span class="screen-reader-text">Remove</span></button>
			<# } #>
		</div>
		<# if ( data.buttons.check ) { #>
			<button type="button" class="button-link check" tabindex="-1"><span class="media-modal-icon"></span><span class="screen-reader-text">Deselect</span></button>
		<# } #>
		<#
		var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly';
		if ( data.describe ) {
			if ( 'image' === data.type ) { #>
				<input type="text" value="{{ data.caption }}" class="describe" data-setting="caption"
					placeholder="Caption this image&hellip;" {{ maybeReadOnly }} />
			<# } else { #>
				<input type="text" value="{{ data.title }}" class="describe" data-setting="title"
					<# if ( 'video' === data.type ) { #>
						placeholder="Describe this video&hellip;"
					<# } else if ( 'audio' === data.type ) { #>
						placeholder="Describe this audio file&hellip;"
					<# } else { #>
						placeholder="Describe this media file&hellip;"
					<# } #> {{ maybeReadOnly }} />
			<# }
		} #>
	</script>

	<script type="text/html" id="tmpl-attachment-details">
		<h3>
			Attachment Details
			<span class="settings-save-status">
				<span class="spinner"></span>
				<span class="saved">Saved.</span>
			</span>
		</h3>
		<div class="attachment-info">
			<div class="thumbnail thumbnail-{{ data.type }}">
				<# if ( data.uploading ) { #>
					<div class="media-progress-bar"><div></div></div>
				<# } else if ( 'image' === data.type && data.sizes ) { #>
					<img src="{{ data.size.url }}" draggable="false" />
				<# } else { #>
					<img src="{{ data.icon }}" class="icon" draggable="false" />
				<# } #>
			</div>
			<div class="details">
				<div class="filename">{{ data.filename }}</div>
				<div class="uploaded">{{ data.dateFormatted }}</div>

				<div class="file-size">{{ data.filesizeHumanReadable }}</div>
				<# if ( 'image' === data.type && ! data.uploading ) { #>
					<# if ( data.width && data.height ) { #>
						<div class="dimensions">{{ data.width }} &times; {{ data.height }}</div>
					<# } #>

					<# if ( data.can.save && data.sizes ) { #>
						<a class="edit-attachment" href="{{ data.editLink }}&amp;image-editor" target="_blank">Edit Image</a>
					<# } #>
				<# } #>

				<# if ( data.fileLength ) { #>
					<div class="file-length">Length: {{ data.fileLength }}</div>
				<# } #>

				<# if ( ! data.uploading && data.can.remove ) { #>
											<button type="button" class="button-link delete-attachment">Delete Permanently</button>
									<# } #>

				<div class="compat-meta">
					<# if ( data.compat && data.compat.meta ) { #>
						{{{ data.compat.meta }}}
					<# } #>
				</div>
			</div>
		</div>

		<label class="setting" data-setting="url">
			<span class="name">URL</span>
			<input type="text" value="{{ data.url }}" readonly />
		</label>
		<# var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly'; #>
				<label class="setting" data-setting="title">
			<span class="name">Title</span>
			<input type="text" value="{{ data.title }}" {{ maybeReadOnly }} />
		</label>
				<# if ( 'audio' === data.type ) { #>
				<label class="setting" data-setting="artist">
			<span class="name">Artist</span>
			<input type="text" value="{{ data.artist || data.meta.artist || '' }}" />
		</label>
				<label class="setting" data-setting="album">
			<span class="name">Album</span>
			<input type="text" value="{{ data.album || data.meta.album || '' }}" />
		</label>
				<# } #>
		<label class="setting" data-setting="caption">
			<span class="name">Caption</span>
			<textarea {{ maybeReadOnly }}>{{ data.caption }}</textarea>
		</label>
		<# if ( 'image' === data.type ) { #>
			<label class="setting" data-setting="alt">
				<span class="name">Alt Text</span>
				<input type="text" value="{{ data.alt }}" {{ maybeReadOnly }} />
			</label>
		<# } #>
		<label class="setting" data-setting="description">
			<span class="name">Description</span>
			<textarea {{ maybeReadOnly }}>{{ data.description }}</textarea>
		</label>
	</script>

	<script type="text/html" id="tmpl-media-selection">
		<div class="selection-info">
			<span class="count"></span>
			<# if ( data.editable ) { #>
				<button type="button" class="button-link edit-selection">Edit Selection</button>
			<# } #>
			<# if ( data.clearable ) { #>
				<button type="button" class="button-link clear-selection">Clear</button>
			<# } #>
		</div>
		<div class="selection-view"></div>
	</script>

	<script type="text/html" id="tmpl-attachment-display-settings">
		<h3>Attachment Display Settings</h3>

		<# if ( 'image' === data.type ) { #>
			<label class="setting">
				<span>Alignment</span>
				<select class="alignment"
					data-setting="align"
					<# if ( data.userSettings ) { #>
						data-user-setting="align"
					<# } #>>

					<option value="left">
						Left					</option>
					<option value="center">
						Center					</option>
					<option value="right">
						Right					</option>
					<option value="none" selected>
						None					</option>
				</select>
			</label>
		<# } #>

		<div class="setting">
			<label>
				<# if ( data.model.canEmbed ) { #>
					<span>Embed or Link</span>
				<# } else { #>
					<span>Link To</span>
				<# } #>

				<select class="link-to"
					data-setting="link"
					<# if ( data.userSettings && ! data.model.canEmbed ) { #>
						data-user-setting="urlbutton"
					<# } #>>

				<# if ( data.model.canEmbed ) { #>
					<option value="embed" selected>
						Embed Media Player					</option>
					<option value="file">
				<# } else { #>
					<option value="file" selected>
				<# } #>
					<# if ( data.model.canEmbed ) { #>
						Link to Media File					<# } else { #>
						Media File					<# } #>
					</option>
					<option value="post">
					<# if ( data.model.canEmbed ) { #>
						Link to Attachment Page					<# } else { #>
						Attachment Page					<# } #>
					</option>
				<# if ( 'image' === data.type ) { #>
					<option value="custom">
						Custom URL					</option>
					<option value="none">
						None					</option>
				<# } #>
				</select>
			</label>
			<input type="text" class="link-to-custom" data-setting="linkUrl" />
		</div>

		<# if ( 'undefined' !== typeof data.sizes ) { #>
			<label class="setting">
				<span>Size</span>
				<select class="size" name="size"
					data-setting="size"
					<# if ( data.userSettings ) { #>
						data-user-setting="imgsize"
					<# } #>>
											<#
						var size = data.sizes['thumbnail'];
						if ( size ) { #>
							<option value="thumbnail" >
								Thumbnail &ndash; {{ size.width }} &times; {{ size.height }}
							</option>
						<# } #>
											<#
						var size = data.sizes['medium'];
						if ( size ) { #>
							<option value="medium" >
								Medium &ndash; {{ size.width }} &times; {{ size.height }}
							</option>
						<# } #>
											<#
						var size = data.sizes['large'];
						if ( size ) { #>
							<option value="large" >
								Large &ndash; {{ size.width }} &times; {{ size.height }}
							</option>
						<# } #>
											<#
						var size = data.sizes['full'];
						if ( size ) { #>
							<option value="full"  selected='selected'>
								Full Size &ndash; {{ size.width }} &times; {{ size.height }}
							</option>
						<# } #>
											<#
						var size = data.sizes['company-logo'];
						if ( size ) { #>
							<option value="company-logo" >
								Company logo with default size &ndash; {{ size.width }} &times; {{ size.height }}
							</option>
						<# } #>
											<#
						var size = data.sizes['small_thumb'];
						if ( size ) { #>
							<option value="small_thumb" >
								Small thumbnail for job list items &ndash; {{ size.width }} &times; {{ size.height }}
							</option>
						<# } #>
									</select>
			</label>
		<# } #>
	</script>

	<script type="text/html" id="tmpl-gallery-settings">
		<h3>Gallery Settings</h3>

		<label class="setting">
			<span>Link To</span>
			<select class="link-to"
				data-setting="link"
				<# if ( data.userSettings ) { #>
					data-user-setting="urlbutton"
				<# } #>>

				<option value="post" <# if ( ! wp.media.galleryDefaults.link || 'post' == wp.media.galleryDefaults.link ) {
					#>selected="selected"<# }
				#>>
					Attachment Page				</option>
				<option value="file" <# if ( 'file' == wp.media.galleryDefaults.link ) { #>selected="selected"<# } #>>
					Media File				</option>
				<option value="none" <# if ( 'none' == wp.media.galleryDefaults.link ) { #>selected="selected"<# } #>>
					None				</option>
			</select>
		</label>

		<label class="setting">
			<span>Columns</span>
			<select class="columns" name="columns"
				data-setting="columns">
									<option value="1" <#
						if ( 1 == wp.media.galleryDefaults.columns ) { #>selected="selected"<# }
					#>>
						1					</option>
									<option value="2" <#
						if ( 2 == wp.media.galleryDefaults.columns ) { #>selected="selected"<# }
					#>>
						2					</option>
									<option value="3" <#
						if ( 3 == wp.media.galleryDefaults.columns ) { #>selected="selected"<# }
					#>>
						3					</option>
									<option value="4" <#
						if ( 4 == wp.media.galleryDefaults.columns ) { #>selected="selected"<# }
					#>>
						4					</option>
									<option value="5" <#
						if ( 5 == wp.media.galleryDefaults.columns ) { #>selected="selected"<# }
					#>>
						5					</option>
									<option value="6" <#
						if ( 6 == wp.media.galleryDefaults.columns ) { #>selected="selected"<# }
					#>>
						6					</option>
									<option value="7" <#
						if ( 7 == wp.media.galleryDefaults.columns ) { #>selected="selected"<# }
					#>>
						7					</option>
									<option value="8" <#
						if ( 8 == wp.media.galleryDefaults.columns ) { #>selected="selected"<# }
					#>>
						8					</option>
									<option value="9" <#
						if ( 9 == wp.media.galleryDefaults.columns ) { #>selected="selected"<# }
					#>>
						9					</option>
							</select>
		</label>

		<label class="setting">
			<span>Random Order</span>
			<input type="checkbox" data-setting="_orderbyRandom" />
		</label>

		<label class="setting size">
			<span>Size</span>
			<select class="size" name="size"
				data-setting="size"
				<# if ( data.userSettings ) { #>
					data-user-setting="imgsize"
				<# } #>
				>
									<option value="thumbnail">
						Thumbnail					</option>
									<option value="medium">
						Medium					</option>
									<option value="large">
						Large					</option>
									<option value="full">
						Full Size					</option>
									<option value="company-logo">
						Company logo with default size					</option>
									<option value="small_thumb">
						Small thumbnail for job list items					</option>
							</select>
		</label>
	</script>

	<script type="text/html" id="tmpl-playlist-settings">
		<h3>Playlist Settings</h3>

		<# var emptyModel = _.isEmpty( data.model ),
			isVideo = 'video' === data.controller.get('library').props.get('type'); #>

		<label class="setting">
			<input type="checkbox" data-setting="tracklist" <# if ( emptyModel ) { #>
				checked="checked"
			<# } #> />
			<# if ( isVideo ) { #>
			<span>Show Video List</span>
			<# } else { #>
			<span>Show Tracklist</span>
			<# } #>
		</label>

		<# if ( ! isVideo ) { #>
		<label class="setting">
			<input type="checkbox" data-setting="artists" <# if ( emptyModel ) { #>
				checked="checked"
			<# } #> />
			<span>Show Artist Name in Tracklist</span>
		</label>
		<# } #>

		<label class="setting">
			<input type="checkbox" data-setting="images" <# if ( emptyModel ) { #>
				checked="checked"
			<# } #> />
			<span>Show Images</span>
		</label>
	</script>

	<script type="text/html" id="tmpl-embed-link-settings">
		<label class="setting link-text">
			<span>Link Text</span>
			<input type="text" class="alignment" data-setting="linkText" />
		</label>
		<div class="embed-container" style="display: none;">
			<div class="embed-preview"></div>
		</div>
	</script>

	<script type="text/html" id="tmpl-embed-image-settings">
		<div class="thumbnail">
			<img src="{{ data.model.url }}" draggable="false" />
		</div>

					<label class="setting caption">
				<span>Caption</span>
				<textarea data-setting="caption" />
			</label>
		
		<label class="setting alt-text">
			<span>Alt Text</span>
			<input type="text" data-setting="alt" />
		</label>

		<div class="setting align">
			<span>Align</span>
			<div class="button-group button-large" data-setting="align">
				<button class="button" value="left">
					Left				</button>
				<button class="button" value="center">
					Center				</button>
				<button class="button" value="right">
					Right				</button>
				<button class="button active" value="none">
					None				</button>
			</div>
		</div>

		<div class="setting link-to">
			<span>Link To</span>
			<div class="button-group button-large" data-setting="link">
				<button class="button" value="file">
					Image URL				</button>
				<button class="button" value="custom">
					Custom URL				</button>
				<button class="button active" value="none">
					None				</button>
			</div>
			<input type="text" class="link-to-custom" data-setting="linkUrl" />
		</div>
	</script>

	<script type="text/html" id="tmpl-image-details">
		<div class="media-embed">
			<div class="embed-media-settings">
				<div class="column-image">
					<div class="image">
						<img src="{{ data.model.url }}" draggable="false" />

						<# if ( data.attachment && window.imageEdit ) { #>
							<div class="actions">
								<input type="button" class="edit-attachment button" value="Edit Original" />
								<input type="button" class="replace-attachment button" value="Replace" />
							</div>
						<# } #>
					</div>
				</div>
				<div class="column-settings">
											<label class="setting caption">
							<span>Caption</span>
							<textarea data-setting="caption">{{ data.model.caption }}</textarea>
						</label>
					
					<label class="setting alt-text">
						<span>Alternative Text</span>
						<input type="text" data-setting="alt" value="{{ data.model.alt }}" />
					</label>

					<h3>Display Settings</h3>
					<div class="setting align">
						<span>Align</span>
						<div class="button-group button-large" data-setting="align">
							<button class="button" value="left">
								Left							</button>
							<button class="button" value="center">
								Center							</button>
							<button class="button" value="right">
								Right							</button>
							<button class="button active" value="none">
								None							</button>
						</div>
					</div>

					<# if ( data.attachment ) { #>
						<# if ( 'undefined' !== typeof data.attachment.sizes ) { #>
							<label class="setting size">
								<span>Size</span>
								<select class="size" name="size"
									data-setting="size"
									<# if ( data.userSettings ) { #>
										data-user-setting="imgsize"
									<# } #>>
																			<#
										var size = data.sizes['thumbnail'];
										if ( size ) { #>
											<option value="thumbnail">
												Thumbnail &ndash; {{ size.width }} &times; {{ size.height }}
											</option>
										<# } #>
																			<#
										var size = data.sizes['medium'];
										if ( size ) { #>
											<option value="medium">
												Medium &ndash; {{ size.width }} &times; {{ size.height }}
											</option>
										<# } #>
																			<#
										var size = data.sizes['large'];
										if ( size ) { #>
											<option value="large">
												Large &ndash; {{ size.width }} &times; {{ size.height }}
											</option>
										<# } #>
																			<#
										var size = data.sizes['full'];
										if ( size ) { #>
											<option value="full">
												Full Size &ndash; {{ size.width }} &times; {{ size.height }}
											</option>
										<# } #>
																			<#
										var size = data.sizes['company-logo'];
										if ( size ) { #>
											<option value="company-logo">
												Company logo with default size &ndash; {{ size.width }} &times; {{ size.height }}
											</option>
										<# } #>
																			<#
										var size = data.sizes['small_thumb'];
										if ( size ) { #>
											<option value="small_thumb">
												Small thumbnail for job list items &ndash; {{ size.width }} &times; {{ size.height }}
											</option>
										<# } #>
																		<option value="custom">
										Custom Size									</option>
								</select>
							</label>
						<# } #>
							<div class="custom-size<# if ( data.model.size !== 'custom' ) { #> hidden<# } #>">
								<label><span>Width <small>(px)</small></span> <input data-setting="customWidth" type="number" step="1" value="{{ data.model.customWidth }}" /></label><span class="sep">&times;</span><label><span>Height <small>(px)</small></span><input data-setting="customHeight" type="number" step="1" value="{{ data.model.customHeight }}" /></label>
							</div>
					<# } #>

					<div class="setting link-to">
						<span>Link To</span>
						<select data-setting="link">
						<# if ( data.attachment ) { #>
							<option value="file">
								Media File							</option>
							<option value="post">
								Attachment Page							</option>
						<# } else { #>
							<option value="file">
								Image URL							</option>
						<# } #>
							<option value="custom">
								Custom URL							</option>
							<option value="none">
								None							</option>
						</select>
						<input type="text" class="link-to-custom" data-setting="linkUrl" />
					</div>
					<div class="advanced-section">
						<h3><button type="button" class="button-link advanced-toggle">Advanced Options</button></h3>
						<div class="advanced-settings hidden">
							<div class="advanced-image">
								<label class="setting title-text">
									<span>Image Title Attribute</span>
									<input type="text" data-setting="title" value="{{ data.model.title }}" />
								</label>
								<label class="setting extra-classes">
									<span>Image CSS Class</span>
									<input type="text" data-setting="extraClasses" value="{{ data.model.extraClasses }}" />
								</label>
							</div>
							<div class="advanced-link">
								<div class="setting link-target">
									<label><input type="checkbox" data-setting="linkTargetBlank" value="_blank" <# if ( data.model.linkTargetBlank ) { #>checked="checked"<# } #>>Open link in a new window/tab</label>
								</div>
								<label class="setting link-rel">
									<span>Link Rel</span>
									<input type="text" data-setting="linkRel" value="{{ data.model.linkClassName }}" />
								</label>
								<label class="setting link-class-name">
									<span>Link CSS Class</span>
									<input type="text" data-setting="linkClassName" value="{{ data.model.linkClassName }}" />
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</script>

	<script type="text/html" id="tmpl-image-editor">
		<div id="media-head-{{ data.id }}"></div>
		<div id="image-editor-{{ data.id }}"></div>
	</script>

	<script type="text/html" id="tmpl-audio-details">
		<# var ext, html5types = {
			mp3: wp.media.view.settings.embedMimes.mp3,
			ogg: wp.media.view.settings.embedMimes.ogg
		}; #>

				<div class="media-embed media-embed-details">
			<div class="embed-media-settings embed-audio-settings">
				<audio style="visibility: hidden"
	controls
	class="wp-audio-shortcode"
	width="{{ _.isUndefined( data.model.width ) ? 400 : data.model.width }}"
	preload="{{ _.isUndefined( data.model.preload ) ? 'none' : data.model.preload }}"
	<#
	if ( ! _.isUndefined( data.model.autoplay ) && data.model.autoplay ) {
		#> autoplay<#
	}
	if ( ! _.isUndefined( data.model.loop ) && data.model.loop ) {
		#> loop<#
	}
	#>
>
	<# if ( ! _.isEmpty( data.model.src ) ) { #>
	<source src="{{ data.model.src }}" type="{{ wp.media.view.settings.embedMimes[ data.model.src.split('.').pop() ] }}" />
	<# } #>

	<# if ( ! _.isEmpty( data.model.mp3 ) ) { #>
	<source src="{{ data.model.mp3 }}" type="{{ wp.media.view.settings.embedMimes[ 'mp3' ] }}" />
	<# } #>
	<# if ( ! _.isEmpty( data.model.ogg ) ) { #>
	<source src="{{ data.model.ogg }}" type="{{ wp.media.view.settings.embedMimes[ 'ogg' ] }}" />
	<# } #>
	<# if ( ! _.isEmpty( data.model.wma ) ) { #>
	<source src="{{ data.model.wma }}" type="{{ wp.media.view.settings.embedMimes[ 'wma' ] }}" />
	<# } #>
	<# if ( ! _.isEmpty( data.model.m4a ) ) { #>
	<source src="{{ data.model.m4a }}" type="{{ wp.media.view.settings.embedMimes[ 'm4a' ] }}" />
	<# } #>
	<# if ( ! _.isEmpty( data.model.wav ) ) { #>
	<source src="{{ data.model.wav }}" type="{{ wp.media.view.settings.embedMimes[ 'wav' ] }}" />
	<# } #>
	</audio>

				<# if ( ! _.isEmpty( data.model.src ) ) {
					ext = data.model.src.split('.').pop();
					if ( html5types[ ext ] ) {
						delete html5types[ ext ];
					}
				#>
				<label class="setting">
					<span>SRC</span>
					<input type="text" disabled="disabled" data-setting="src" value="{{ data.model.src }}" />
					<button type="button" class="button-link remove-setting">Remove audio source</button>
				</label>
				<# } #>
				<# if ( ! _.isEmpty( data.model.mp3 ) ) {
					if ( ! _.isUndefined( html5types.mp3 ) ) {
						delete html5types.mp3;
					}
				#>
				<label class="setting">
					<span>MP3</span>
					<input type="text" disabled="disabled" data-setting="mp3" value="{{ data.model.mp3 }}" />
					<button type="button" class="button-link remove-setting">Remove audio source</button>
				</label>
				<# } #>
				<# if ( ! _.isEmpty( data.model.ogg ) ) {
					if ( ! _.isUndefined( html5types.ogg ) ) {
						delete html5types.ogg;
					}
				#>
				<label class="setting">
					<span>OGG</span>
					<input type="text" disabled="disabled" data-setting="ogg" value="{{ data.model.ogg }}" />
					<button type="button" class="button-link remove-setting">Remove audio source</button>
				</label>
				<# } #>
				<# if ( ! _.isEmpty( data.model.wma ) ) {
					if ( ! _.isUndefined( html5types.wma ) ) {
						delete html5types.wma;
					}
				#>
				<label class="setting">
					<span>WMA</span>
					<input type="text" disabled="disabled" data-setting="wma" value="{{ data.model.wma }}" />
					<button type="button" class="button-link remove-setting">Remove audio source</button>
				</label>
				<# } #>
				<# if ( ! _.isEmpty( data.model.m4a ) ) {
					if ( ! _.isUndefined( html5types.m4a ) ) {
						delete html5types.m4a;
					}
				#>
				<label class="setting">
					<span>M4A</span>
					<input type="text" disabled="disabled" data-setting="m4a" value="{{ data.model.m4a }}" />
					<button type="button" class="button-link remove-setting">Remove audio source</button>
				</label>
				<# } #>
				<# if ( ! _.isEmpty( data.model.wav ) ) {
					if ( ! _.isUndefined( html5types.wav ) ) {
						delete html5types.wav;
					}
				#>
				<label class="setting">
					<span>WAV</span>
					<input type="text" disabled="disabled" data-setting="wav" value="{{ data.model.wav }}" />
					<button type="button" class="button-link remove-setting">Remove audio source</button>
				</label>
				<# } #>
				
				<# if ( ! _.isEmpty( html5types ) ) { #>
				<div class="setting">
					<span>Add alternate sources for maximum HTML5 playback:</span>
					<div class="button-large">
					<# _.each( html5types, function (mime, type) { #>
					<button class="button add-media-source" data-mime="{{ mime }}">{{ type }}</button>
					<# } ) #>
					</div>
				</div>
				<# } #>

				<div class="setting preload">
					<span>Preload</span>
					<div class="button-group button-large" data-setting="preload">
						<button class="button" value="auto">Auto</button>
						<button class="button" value="metadata">Metadata</button>
						<button class="button active" value="none">None</button>
					</div>
				</div>

				<label class="setting checkbox-setting">
					<input type="checkbox" data-setting="autoplay" />
					<span>Autoplay</span>
				</label>

				<label class="setting checkbox-setting">
					<input type="checkbox" data-setting="loop" />
					<span>Loop</span>
				</label>
			</div>
		</div>
	</script>

	<script type="text/html" id="tmpl-video-details">
		<# var ext, html5types = {
			mp4: wp.media.view.settings.embedMimes.mp4,
			ogv: wp.media.view.settings.embedMimes.ogv,
			webm: wp.media.view.settings.embedMimes.webm
		}; #>

				<div class="media-embed media-embed-details">
			<div class="embed-media-settings embed-video-settings">
				<div class="wp-video-holder">
				<#
				var w = ! data.model.width || data.model.width > 640 ? 640 : data.model.width,
					h = ! data.model.height ? 360 : data.model.height;

				if ( data.model.width && w !== data.model.width ) {
					h = Math.ceil( ( h * w ) / data.model.width );
				}
				#>

				<#  var w_rule = h_rule = '', classes = [],
		w, h, settings = wp.media.view.settings,
		isYouTube = isVimeo = false;

	if ( ! _.isEmpty( data.model.src ) ) {
		isYouTube = data.model.src.match(/youtube|youtu\.be/);
		isVimeo = -1 !== data.model.src.indexOf('vimeo');
	}

	if ( settings.contentWidth && data.model.width >= settings.contentWidth ) {
		w = settings.contentWidth;
	} else {
		w = data.model.width;
	}

	if ( w !== data.model.width ) {
		h = Math.ceil( ( data.model.height * w ) / data.model.width );
	} else {
		h = data.model.height;
	}

	if ( w ) {
		w_rule = 'width: ' + w + 'px; ';
	}
	if ( h ) {
		h_rule = 'height: ' + h + 'px;';
	}

	if ( isYouTube ) {
		classes.push( 'youtube-video' );
	}

	if ( isVimeo ) {
		classes.push( 'vimeo-video' );
	}

#>
<div style="{{ w_rule }}{{ h_rule }}" class="wp-video">
<video controls
	class="wp-video-shortcode {{ classes.join( ' ' ) }}"
	<# if ( w ) { #>width="{{ w }}"<# } #>
	<# if ( h ) { #>height="{{ h }}"<# } #>
	<#
		if ( ! _.isUndefined( data.model.poster ) && data.model.poster ) {
			#> poster="{{ data.model.poster }}"<#
		} #>
		preload="{{ _.isUndefined( data.model.preload ) ? 'metadata' : data.model.preload }}"<#
	 if ( ! _.isUndefined( data.model.autoplay ) && data.model.autoplay ) {
		#> autoplay<#
	}
	 if ( ! _.isUndefined( data.model.loop ) && data.model.loop ) {
		#> loop<#
	}
	#>
>
	<# if ( ! _.isEmpty( data.model.src ) ) {
		if ( isYouTube ) { #>
		<source src="{{ data.model.src }}" type="video/youtube" />
		<# } else if ( isVimeo ) { #>
		<source src="{{ data.model.src }}" type="video/vimeo" />
		<# } else { #>
		<source src="{{ data.model.src }}" type="{{ settings.embedMimes[ data.model.src.split('.').pop() ] }}" />
		<# }
	} #>

	<# if ( data.model.mp4 ) { #>
	<source src="{{ data.model.mp4 }}" type="{{ settings.embedMimes[ 'mp4' ] }}" />
	<# } #>
	<# if ( data.model.m4v ) { #>
	<source src="{{ data.model.m4v }}" type="{{ settings.embedMimes[ 'm4v' ] }}" />
	<# } #>
	<# if ( data.model.webm ) { #>
	<source src="{{ data.model.webm }}" type="{{ settings.embedMimes[ 'webm' ] }}" />
	<# } #>
	<# if ( data.model.ogv ) { #>
	<source src="{{ data.model.ogv }}" type="{{ settings.embedMimes[ 'ogv' ] }}" />
	<# } #>
	<# if ( data.model.wmv ) { #>
	<source src="{{ data.model.wmv }}" type="{{ settings.embedMimes[ 'wmv' ] }}" />
	<# } #>
	<# if ( data.model.flv ) { #>
	<source src="{{ data.model.flv }}" type="{{ settings.embedMimes[ 'flv' ] }}" />
	<# } #>
		{{{ data.model.content }}}
</video>
</div>

				<# if ( ! _.isEmpty( data.model.src ) ) {
					ext = data.model.src.split('.').pop();
					if ( html5types[ ext ] ) {
						delete html5types[ ext ];
					}
				#>
				<label class="setting">
					<span>SRC</span>
					<input type="text" disabled="disabled" data-setting="src" value="{{ data.model.src }}" />
					<button type="button" class="button-link remove-setting">Remove video source</button>
				</label>
				<# } #>
				<# if ( ! _.isEmpty( data.model.mp4 ) ) {
					if ( ! _.isUndefined( html5types.mp4 ) ) {
						delete html5types.mp4;
					}
				#>
				<label class="setting">
					<span>MP4</span>
					<input type="text" disabled="disabled" data-setting="mp4" value="{{ data.model.mp4 }}" />
					<button type="button" class="button-link remove-setting">Remove video source</button>
				</label>
				<# } #>
				<# if ( ! _.isEmpty( data.model.m4v ) ) {
					if ( ! _.isUndefined( html5types.m4v ) ) {
						delete html5types.m4v;
					}
				#>
				<label class="setting">
					<span>M4V</span>
					<input type="text" disabled="disabled" data-setting="m4v" value="{{ data.model.m4v }}" />
					<button type="button" class="button-link remove-setting">Remove video source</button>
				</label>
				<# } #>
				<# if ( ! _.isEmpty( data.model.webm ) ) {
					if ( ! _.isUndefined( html5types.webm ) ) {
						delete html5types.webm;
					}
				#>
				<label class="setting">
					<span>WEBM</span>
					<input type="text" disabled="disabled" data-setting="webm" value="{{ data.model.webm }}" />
					<button type="button" class="button-link remove-setting">Remove video source</button>
				</label>
				<# } #>
				<# if ( ! _.isEmpty( data.model.ogv ) ) {
					if ( ! _.isUndefined( html5types.ogv ) ) {
						delete html5types.ogv;
					}
				#>
				<label class="setting">
					<span>OGV</span>
					<input type="text" disabled="disabled" data-setting="ogv" value="{{ data.model.ogv }}" />
					<button type="button" class="button-link remove-setting">Remove video source</button>
				</label>
				<# } #>
				<# if ( ! _.isEmpty( data.model.wmv ) ) {
					if ( ! _.isUndefined( html5types.wmv ) ) {
						delete html5types.wmv;
					}
				#>
				<label class="setting">
					<span>WMV</span>
					<input type="text" disabled="disabled" data-setting="wmv" value="{{ data.model.wmv }}" />
					<button type="button" class="button-link remove-setting">Remove video source</button>
				</label>
				<# } #>
				<# if ( ! _.isEmpty( data.model.flv ) ) {
					if ( ! _.isUndefined( html5types.flv ) ) {
						delete html5types.flv;
					}
				#>
				<label class="setting">
					<span>FLV</span>
					<input type="text" disabled="disabled" data-setting="flv" value="{{ data.model.flv }}" />
					<button type="button" class="button-link remove-setting">Remove video source</button>
				</label>
				<# } #>
								</div>

				<# if ( ! _.isEmpty( html5types ) ) { #>
				<div class="setting">
					<span>Add alternate sources for maximum HTML5 playback:</span>
					<div class="button-large">
					<# _.each( html5types, function (mime, type) { #>
					<button class="button add-media-source" data-mime="{{ mime }}">{{ type }}</button>
					<# } ) #>
					</div>
				</div>
				<# } #>

				<# if ( ! _.isEmpty( data.model.poster ) ) { #>
				<label class="setting">
					<span>Poster Image</span>
					<input type="text" disabled="disabled" data-setting="poster" value="{{ data.model.poster }}" />
					<button type="button" class="button-link remove-setting">Remove poster image</button>
				</label>
				<# } #>
				<div class="setting preload">
					<span>Preload</span>
					<div class="button-group button-large" data-setting="preload">
						<button class="button" value="auto">Auto</button>
						<button class="button" value="metadata">Metadata</button>
						<button class="button active" value="none">None</button>
					</div>
				</div>

				<label class="setting checkbox-setting">
					<input type="checkbox" data-setting="autoplay" />
					<span>Autoplay</span>
				</label>

				<label class="setting checkbox-setting">
					<input type="checkbox" data-setting="loop" />
					<span>Loop</span>
				</label>

				<label class="setting" data-setting="content">
					<span>Tracks (subtitles, captions, descriptions, chapters, or metadata)</span>
					<#
					var content = '';
					if ( ! _.isEmpty( data.model.content ) ) {
						var tracks = jQuery( data.model.content ).filter( 'track' );
						_.each( tracks.toArray(), function (track) {
							content += track.outerHTML; #>
						<p>
							<input class="content-track" type="text" value="{{ track.outerHTML }}" />
							<button type="button" class="button-link remove-setting remove-track">Remove video track</button>
						</p>
						<# } ); #>
					<# } else { #>
					<em>There are no associated subtitles.</em>
					<# } #>
					<textarea class="hidden content-setting">{{ content }}</textarea>
				</label>
			</div>
		</div>
	</script>

	<script type="text/html" id="tmpl-editor-gallery">
		<# if ( data.attachments.length ) { #>
			<div class="gallery gallery-columns-{{ data.columns }}">
				<# _.each( data.attachments, function( attachment, index ) { #>
					<dl class="gallery-item">
						<dt class="gallery-icon">
							<# if ( attachment.thumbnail ) { #>
								<img src="{{ attachment.thumbnail.url }}" width="{{ attachment.thumbnail.width }}" height="{{ attachment.thumbnail.height }}" />
							<# } else { #>
								<img src="{{ attachment.url }}" />
							<# } #>
						</dt>
						<# if ( attachment.caption ) { #>
							<dd class="wp-caption-text gallery-caption">
								{{ attachment.caption }}
							</dd>
						<# } #>
					</dl>
					<# if ( index % data.columns === data.columns - 1 ) { #>
						<br style="clear: both;">
					<# } #>
				<# } ); #>
			</div>
		<# } else { #>
			<div class="wpview-error">
				<div class="dashicons dashicons-format-gallery"></div><p>No items found.</p>
			</div>
		<# } #>
	</script>

	<script type="text/html" id="tmpl-crop-content">
		<img class="crop-image" src="{{ data.url }}">
		<div class="upload-errors"></div>
	</script>

	<script type="text/html" id="tmpl-site-icon-preview">
		<h2>Preview</h2>
		<strong>As a browser icon</strong>
		<div class="favicon-preview">
			<img src="images/browser.png" class="browser-preview" width="182" height="" alt=""/>

			<div class="favicon">
				<img id="preview-favicon" src="{{ data.url }}" alt="Preview as a browser icon"/>
			</div>
			<span class="browser-title">Smartjob &#8211; Mạng tuyển dụng hàng đầu Việt Nam</span>
		</div>

		<strong>As an app icon</strong>
		<div class="app-icon-preview">
			<img id="preview-app-icon" src="{{ data.url }}" alt="Preview as an app icon"/>
		</div>
	</script>

		<div id="local-storage-notice" class="hidden notice">
	<p class="local-restore">
		The backup of this post in your browser is different from the version below.		<a class="restore-backup" href="#">Restore the backup.</a>
	</p>
	<p class="undo-restore hidden">
		Post restored successfully.		<a class="undo-restore-backup" href="#">Undo.</a>
	</p>
	</div>
	<script type='text/javascript'>list_args = {"class":"WP_Post_Comments_List_Table","screen":{"id":"post","base":"post"}};</script>
	<link rel='stylesheet' href='http://smartjob.vn/wp-admin/load-styles.php?c=1&amp;dir=ltr&amp;load=wp-pointer&amp;ver=4.3.1' type='text/css' media='all' />
<link rel='stylesheet' id='aioseop-module-style-css'  href='http://smartjob.vn/wp-content/plugins/all-in-one-seo-pack/aioseop_module.css?ver=4.3.1' type='text/css' media='all' />
<script type='text/javascript'>
/* <![CDATA[ */
var aiosp_data = [{"plugin_path":{"basename":"all-in-one-seo-pack\/aioseop_class.php","dirname":"all-in-one-seo-pack","url":"http:\/\/smartjob.vn\/wp-content\/plugins\/all-in-one-seo-pack\/","images_url":"http:\/\/smartjob.vn\/wp-content\/plugins\/all-in-one-seo-pack\/images"},"condshow":{"aiosp_home_title":{"aiosp_use_static_home_info":0},"aiosp_home_description":{"aiosp_use_static_home_info":0},"aiosp_home_keywords":{"aiosp_togglekeywords":0,"aiosp_use_static_home_info":0},"aiosp_no_paged_canonical_links":{"aiosp_can":"on"},"aiosp_customize_canonical_links":{"aiosp_can":"on"},"aiosp_can_set_protocol":{"aiosp_can":"on"},"aiosp_home_page_title_format":{"aiosp_rewrite_titles":1},"aiosp_page_title_format":{"aiosp_rewrite_titles":1},"aiosp_post_title_format":{"aiosp_rewrite_titles":1},"aiosp_category_title_format":{"aiosp_rewrite_titles":1},"aiosp_archive_title_format":{"aiosp_rewrite_titles":1},"aiosp_date_title_format":{"aiosp_rewrite_titles":1},"aiosp_author_title_format":{"aiosp_rewrite_titles":1},"aiosp_tag_title_format":{"aiosp_rewrite_titles":1},"aiosp_search_title_format":{"aiosp_rewrite_titles":1},"aiosp_description_format":{"aiosp_rewrite_titles":1},"aiosp_404_title_format":{"aiosp_rewrite_titles":1},"aiosp_paged_format":{"aiosp_rewrite_titles":1},"aiosp_cpostactive":{"aiosp_enablecpost":"on"},"aiosp_cpostadvanced":{"aiosp_enablecpost":"on"},"aiosp_cpostnoindex":{"aiosp_enablecpost":"on","aiosp_cpostadvanced":"on"},"aiosp_cpostnofollow":{"aiosp_enablecpost":"on","aiosp_cpostadvanced":"on"},"aiosp_cpostnoodp":{"aiosp_enablecpost":"on","aiosp_cpostadvanced":"on"},"aiosp_cpostnoydir":{"aiosp_enablecpost":"on","aiosp_cpostadvanced":"on"},"aiosp_cposttitles":{"aiosp_rewrite_titles":1,"aiosp_enablecpost":"on","aiosp_cpostadvanced":"on"},"aiosp_google_specify_site_name":{"aiosp_google_set_site_name":"on"},"aiosp_google_author_location":{"aiosp_google_author_advanced":"on"},"aiosp_google_enable_publisher":{"aiosp_google_author_advanced":"on"},"aiosp_google_specify_publisher":{"aiosp_google_author_advanced":"on","aiosp_google_enable_publisher":"on"},"aiosp_ga_use_universal_analytics":{"aiosp_google_analytics_id":{"lhs":"aiosp_google_analytics_id","op":"!=","rhs":""}},"aiosp_ga_advanced_options":{"aiosp_google_analytics_id":{"lhs":"aiosp_google_analytics_id","op":"!=","rhs":""}},"aiosp_ga_domain":{"aiosp_google_analytics_id":{"lhs":"aiosp_google_analytics_id","op":"!=","rhs":""},"aiosp_ga_advanced_options":"on"},"aiosp_ga_multi_domain":{"aiosp_google_analytics_id":{"lhs":"aiosp_google_analytics_id","op":"!=","rhs":""},"aiosp_ga_advanced_options":"on"},"aiosp_ga_addl_domains":{"aiosp_google_analytics_id":{"lhs":"aiosp_google_analytics_id","op":"!=","rhs":""},"aiosp_ga_advanced_options":"on","aiosp_ga_multi_domain":"on"},"aiosp_ga_anonymize_ip":{"aiosp_google_analytics_id":{"lhs":"aiosp_google_analytics_id","op":"!=","rhs":""},"aiosp_ga_advanced_options":"on"},"aiosp_ga_display_advertising":{"aiosp_google_analytics_id":{"lhs":"aiosp_google_analytics_id","op":"!=","rhs":""},"aiosp_ga_advanced_options":"on"},"aiosp_ga_exclude_users":{"aiosp_google_analytics_id":{"lhs":"aiosp_google_analytics_id","op":"!=","rhs":""},"aiosp_ga_advanced_options":"on"},"aiosp_ga_track_outbound_links":{"aiosp_google_analytics_id":{"lhs":"aiosp_google_analytics_id","op":"!=","rhs":""},"aiosp_ga_advanced_options":"on"},"aiosp_ga_link_attribution":{"aiosp_google_analytics_id":{"lhs":"aiosp_google_analytics_id","op":"!=","rhs":""},"aiosp_ga_advanced_options":"on"},"aiosp_ga_enhanced_ecommerce":{"aiosp_google_analytics_id":{"lhs":"aiosp_google_analytics_id","op":"!=","rhs":""},"aiosp_ga_use_universal_analytics":"on","aiosp_ga_advanced_options":"on"},"aiosp_use_categories":{"aiosp_togglekeywords":0},"aiosp_use_tags_as_keywords":{"aiosp_togglekeywords":0},"aiosp_dynamic_postspage_keywords":{"aiosp_togglekeywords":0},"aiosp_tax_noindex":{"aiosp_enablecpost":"on","aiosp_cpostadvanced":"on"},"aiosp_run_shortcodes":{"aiosp_generate_descriptions":"on"},"aiosp_disable_analytics":{"aiosp_disable":"on"}}}];
/* ]]> */
</script>

<script type='text/javascript'>
/* <![CDATA[ */
var commonL10n = {"warnDelete":"You are about to permanently delete the selected items.\n  'Cancel' to stop, 'OK' to delete.","dismiss":"Dismiss this notice."};var heartbeatSettings = {"nonce":"131d49da36"};var autosaveL10n = {"autosaveInterval":"60","blog_id":"1"};
var autosaveL10n = {"autosaveInterval":"60","blog_id":"1"};var wpAjax = {"noPerm":"You do not have permission to do that.","broken":"An unidentified error has occurred."};var tagsBoxL10n = {"tagDelimiter":","};var wordCountL10n = {"type":"words","shortcodes":["embed","wp_caption","caption","gallery","playlist","audio","video","img","latest_job"]};var postL10n = {"ok":"OK","cancel":"Cancel","publishOn":"Publish on:","publishOnFuture":"Schedule for:","publishOnPast":"Published on:","dateFormat":"%1$s %2$s, %3$s @ %4$s:%5$s","showcomm":"Show more comments","endcomm":"No more comments found.","publish":"Publish","schedule":"Schedule","update":"Update","savePending":"Save as Pending","saveDraft":"Save Draft","private":"Private","public":"Public","publicSticky":"Public, Sticky","password":"Password Protected","privatelyPublished":"Privately Published","published":"Published","saveAlert":"The changes you made will be lost if you navigate away from this page.","savingText":"Saving Draft\u2026"};var thickboxL10n = {"next":"Next >","prev":"< Prev","image":"Image","of":"of","close":"Close","noiframes":"This feature requires inline frames. You have iframes disabled or your browser does not support them.","loadingAnimation":"http:\/\/smartjob.vn\/wp-includes\/js\/thickbox\/loadingAnimation.gif"};var _wpUtilSettings = {"ajax":{"url":"\/wp-admin\/admin-ajax.php"}};var _wpMediaModelsL10n = {"settings":{"ajaxurl":"\/wp-admin\/admin-ajax.php","post":{"id":0}}};var pluploadL10n = {"queue_limit_exceeded":"You have attempted to queue too many files.","file_exceeds_size_limit":"%s exceeds the maximum upload size for this site.","zero_byte_file":"This file is empty. Please try another.","invalid_filetype":"This file type is not allowed. Please try another.","not_an_image":"This file is not an image. Please try another.","image_memory_exceeded":"Memory exceeded. Please try another smaller file.","image_dimensions_exceeded":"This is larger than the maximum size. Please try another.","default_error":"An error occurred in the upload. Please try again later.","missing_upload_url":"There was a configuration error. Please contact the server administrator.","upload_limit_exceeded":"You may only upload 1 file.","http_error":"HTTP error.","upload_failed":"Upload failed.","big_upload_failed":"Please try uploading this file with the %1$sbrowser uploader%2$s.","big_upload_queued":"%s exceeds the maximum upload size for the multi-file uploader when used in your browser.","io_error":"IO error.","security_error":"Security error.","file_cancelled":"File canceled.","upload_stopped":"Upload stopped.","dismiss":"Dismiss","crunching":"Crunching\u2026","deleted":"moved to the trash.","error_uploading":"\u201c%s\u201d has failed to upload."};
var _wpPluploadSettings = {"defaults":{"runtimes":"html5,flash,silverlight,html4","file_data_name":"async-upload","url":"\/wp-admin\/async-upload.php","flash_swf_url":"http:\/\/smartjob.vn\/wp-includes\/js\/plupload\/plupload.flash.swf","silverlight_xap_url":"http:\/\/smartjob.vn\/wp-includes\/js\/plupload\/plupload.silverlight.xap","filters":{"max_file_size":"1342177280b"},"multipart_params":{"action":"upload-attachment","_wpnonce":"672b34b118"}},"browser":{"mobile":false,"supported":true},"limitExceeded":false};var mejsL10n = {"language":"en-US","strings":{"Close":"Close","Fullscreen":"Fullscreen","Download File":"Download File","Download Video":"Download Video","Play\/Pause":"Play\/Pause","Mute Toggle":"Mute Toggle","None":"None","Turn off Fullscreen":"Turn off Fullscreen","Go Fullscreen":"Go Fullscreen","Unmute":"Unmute","Mute":"Mute","Captions\/Subtitles":"Captions\/Subtitles"}};
var _wpmejsSettings = {"pluginPath":"\/wp-includes\/js\/mediaelement\/"};var _wpMediaViewsL10n = {"url":"URL","addMedia":"Add Media","search":"Search","select":"Select","cancel":"Cancel","update":"Update","replace":"Replace","remove":"Remove","back":"Back","selected":"%d selected","dragInfo":"Drag and drop to reorder media files.","uploadFilesTitle":"Upload Files","uploadImagesTitle":"Upload Images","mediaLibraryTitle":"Media Library","insertMediaTitle":"Insert Media","createNewGallery":"Create a new gallery","createNewPlaylist":"Create a new playlist","createNewVideoPlaylist":"Create a new video playlist","returnToLibrary":"\u2190 Return to library","allMediaItems":"All media items","allDates":"All dates","noItemsFound":"No items found.","insertIntoPost":"Insert into post","unattached":"Unattached","trash":"Trash","uploadedToThisPost":"Uploaded to this post","warnDelete":"You are about to permanently delete this item.\n  'Cancel' to stop, 'OK' to delete.","warnBulkDelete":"You are about to permanently delete these items.\n  'Cancel' to stop, 'OK' to delete.","warnBulkTrash":"You are about to trash these items.\n  'Cancel' to stop, 'OK' to delete.","bulkSelect":"Bulk Select","cancelSelection":"Cancel Selection","trashSelected":"Trash Selected","untrashSelected":"Untrash Selected","deleteSelected":"Delete Selected","deletePermanently":"Delete Permanently","apply":"Apply","filterByDate":"Filter by date","filterByType":"Filter by type","searchMediaLabel":"Search Media","noMedia":"No media attachments found.","attachmentDetails":"Attachment Details","insertFromUrlTitle":"Insert from URL","setFeaturedImageTitle":"Featured Image","setFeaturedImage":"Set featured image","createGalleryTitle":"Create Gallery","editGalleryTitle":"Edit Gallery","cancelGalleryTitle":"\u2190 Cancel Gallery","insertGallery":"Insert gallery","updateGallery":"Update gallery","addToGallery":"Add to gallery","addToGalleryTitle":"Add to Gallery","reverseOrder":"Reverse order","imageDetailsTitle":"Image Details","imageReplaceTitle":"Replace Image","imageDetailsCancel":"Cancel Edit","editImage":"Edit Image","chooseImage":"Choose Image","selectAndCrop":"Select and Crop","skipCropping":"Skip Cropping","cropImage":"Crop Image","cropYourImage":"Crop your image","cropping":"Cropping\u2026","suggestedDimensions":"Suggested image dimensions:","cropError":"There has been an error cropping your image.","audioDetailsTitle":"Audio Details","audioReplaceTitle":"Replace Audio","audioAddSourceTitle":"Add Audio Source","audioDetailsCancel":"Cancel Edit","videoDetailsTitle":"Video Details","videoReplaceTitle":"Replace Video","videoAddSourceTitle":"Add Video Source","videoDetailsCancel":"Cancel Edit","videoSelectPosterImageTitle":"Select Poster Image","videoAddTrackTitle":"Add Subtitles","playlistDragInfo":"Drag and drop to reorder tracks.","createPlaylistTitle":"Create Audio Playlist","editPlaylistTitle":"Edit Audio Playlist","cancelPlaylistTitle":"\u2190 Cancel Audio Playlist","insertPlaylist":"Insert audio playlist","updatePlaylist":"Update audio playlist","addToPlaylist":"Add to audio playlist","addToPlaylistTitle":"Add to Audio Playlist","videoPlaylistDragInfo":"Drag and drop to reorder videos.","createVideoPlaylistTitle":"Create Video Playlist","editVideoPlaylistTitle":"Edit Video Playlist","cancelVideoPlaylistTitle":"\u2190 Cancel Video Playlist","insertVideoPlaylist":"Insert video playlist","updateVideoPlaylist":"Update video playlist","addToVideoPlaylist":"Add to video playlist","addToVideoPlaylistTitle":"Add to Video Playlist","settings":{"tabs":[],"tabUrl":"http:\/\/smartjob.vn\/wp-admin\/media-upload.php?chromeless=1","mimeTypes":{"image":"Images","audio":"Audio","video":"Video"},"captions":true,"nonce":{"sendToEditor":"b1586e4872"},"post":{"id":1774,"nonce":"4e0bc247bb","featuredImageId":-1},"defaultProps":{"link":"","align":"","size":""},"attachmentCounts":{"audio":0,"video":0},"embedExts":["mp3","ogg","wma","m4a","wav","mp4","m4v","webm","ogv","wmv","flv"],"embedMimes":{"mp3":"audio\/mpeg","ogg":"audio\/ogg","wma":"audio\/x-ms-wma","m4a":"audio\/mpeg","wav":"audio\/wav","mp4":"video\/mp4","m4v":"video\/mp4","webm":"video\/webm","ogv":"video\/ogg","wmv":"video\/x-ms-wmv","flv":"video\/x-flv"},"contentWidth":null,"months":[{"year":"2016","month":"1","text":"January 2016"},{"year":"2015","month":"12","text":"December 2015"},{"year":"2015","month":"11","text":"November 2015"},{"year":"2015","month":"10","text":"October 2015"}],"mediaTrash":0}};var imageEditL10n = {"error":"Could not load the preview image. Please reload the page and try again."};var authcheckL10n = {"beforeunload":"Your session has expired. You can log in again from this page or go to the login page.","interval":"180"};var wpPointerL10n = {"dismiss":"Dismiss"};var quicktagsL10n = {"closeAllOpenTags":"Close all open tags","closeTags":"close tags","enterURL":"Enter the URL","enterImageURL":"Enter the URL of the image","enterImageDescription":"Enter a description of the image","textdirection":"text direction","toggleTextdirection":"Toggle Editor Text Direction","dfw":"Distraction-free writing mode","strong":"Bold","strongClose":"Close bold tag","em":"Italic","emClose":"Close italic tag","link":"Insert link","blockquote":"Blockquote","blockquoteClose":"Close blockquote tag","del":"Deleted text (strikethrough)","delClose":"Close deleted text tag","ins":"Inserted text","insClose":"Close inserted text tag","image":"Insert image","ul":"Bulleted list","ulClose":"Close bulleted list tag","ol":"Numbered list","olClose":"Close numbered list tag","li":"List item","liClose":"Close list item tag","code":"Code","codeClose":"Close code tag","more":"Insert Read More tag"};var wpLinkL10n = {"title":"Insert\/edit link","update":"Update","save":"Add Link","noTitle":"(no title)","noMatchesFound":"No results found."};/* ]]> */
</script>
<script type='text/javascript' src='http://smartjob.vn/wp-admin/load-scripts.php?c=1&amp;load%5B%5D=hoverIntent,common,admin-bar,heartbeat,autosave,suggest,wp-ajax-response,jquery-color,wp-lists,jquery-ui-core,jquery-ui-widget,j&amp;load%5B%5D=query-ui-mouse,jquery-ui-sortable,postbox,tags-box,underscore,word-count,post,editor-expand,thickbox,shortcode,backbone,wp-util,&amp;load%5B%5D=wp-backbone,media-models,wp-plupload,mediaelement,wp-mediaelement,media-views,media-editor,media-audiovideo,mce-view,imgareasele&amp;load%5B%5D=ct,image-edit,svg-painter,wp-auth-check,media-upload,jquery-ui-position,wp-pointer,editor,quicktags,wplink&amp;ver=4.3.1'></script>
<script type='text/javascript' src='http://smartjob.vn/wp-content/plugins/all-in-one-seo-pack/aioseop_module.js?ver=2.2.7.2'></script>

		<script type="text/javascript">
		tinyMCEPreInit = {
			baseURL: "http://smartjob.vn/wp-includes/js/tinymce",
			suffix: ".min",
			dragDropUpload: true,			mceInit: {'content':{theme:"modern",skin:"lightgray",language:"en",formats:{alignleft: [{selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", styles: {textAlign:"left"}},{selector: "img,table,dl.wp-caption", classes: "alignleft"}],aligncenter: [{selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", styles: {textAlign:"center"}},{selector: "img,table,dl.wp-caption", classes: "aligncenter"}],alignright: [{selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", styles: {textAlign:"right"}},{selector: "img,table,dl.wp-caption", classes: "alignright"}],strikethrough: {inline: "del"}},relative_urls:false,remove_script_host:false,convert_urls:false,browser_spellcheck:true,fix_list_elements:true,entities:"38,amp,60,lt,62,gt",entity_encoding:"raw",keep_styles:false,cache_suffix:"wp-mce-4205-20150910",preview_styles:"font-family font-size font-weight font-style text-decoration text-transform",end_container_on_empty_block:true,wpeditimage_disable_captions:false,wpeditimage_html5_captions:false,plugins:"charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview",external_plugins:{"etHeading":"http:\/\/smartjob.vn\/wp-content\/themes\/SMARTJOB\/js\/lib\/tiny_mce\/plugins\/et_heading\/editor_plugin.js","etLink":"http:\/\/smartjob.vn\/wp-content\/themes\/SMARTJOB\/js\/lib\/tiny_mce\/plugins\/et_link\/editor_plugin.js"},content_css:"http://smartjob.vn/wp-includes/css/dashicons.min.css?ver=4.3.1,http://smartjob.vn/wp-includes/js/tinymce/skins/wordpress/wp-content.css?ver=4.3.1",selector:"#content",resize:false,menubar:false,wpautop:true,indent:false,toolbar1:"bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,dfw,wp_adv",toolbar2:"formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",toolbar3:"",toolbar4:"",tabfocus_elements:"content-html,save-post",body_class:"content post-type-post post-status-auto-draft post-format-standard locale-en-us",wp_autoresize_on:true,add_unload_trigger:false}},
			qtInit: {'content':{id:"content",buttons:"strong,em,link,block,del,ins,img,ul,ol,li,code,more,close,dfw"},'replycontent':{id:"replycontent",buttons:"strong,em,link,block,del,ins,img,ul,ol,li,code,close"}},
			ref: {plugins:"charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview",theme:"modern",language:"en"},
			load_ext: function(url,lang){var sl=tinymce.ScriptLoader;sl.markDone(url+'/langs/'+lang+'.js');sl.markDone(url+'/langs/'+lang+'_dlg.js');}
		};
		</script>
		<script type='text/javascript' src='http://smartjob.vn/wp-includes/js/tinymce/wp-tinymce.php?c=1&amp;ver=4205-20150910'></script>
<script type='text/javascript'>
tinymce.addI18n( 'en', {"Ok":"OK","Bullet list":"Bulleted list","Spellcheck":"Check Spelling","Row properties":"Table row properties","Cell properties":"Table cell properties","Paste row before":"Paste table row before","Paste row after":"Paste table row after","Cut row":"Cut table row","Copy row":"Copy table row","Merge cells":"Merge table cells","Split cell":"Split table cell","Paste is now in plain text mode. Contents will now be pasted as plain text until you toggle this option off.":"Paste is now in plain text mode. Contents will now be pasted as plain text until you toggle this option off.\n\nIf you\u2019re looking to paste rich content from Microsoft Word, try turning this option off. The editor will clean up text pasted from Word automatically.","Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help":"Rich Text Area. Press Alt-Shift-H for help","You have unsaved changes are you sure you want to navigate away?":"The changes you made will be lost if you navigate away from this page.","Your browser doesn't support direct access to the clipboard. Please use the Ctrl+X\/C\/V keyboard shortcuts instead.":"Your browser does not support direct access to the clipboard. Please use keyboard shortcuts or your browser\u2019s edit menu instead.","Edit ":"Edit"});
tinymce.ScriptLoader.markDone( 'http://smartjob.vn/wp-includes/js/tinymce/langs/en.js' );
</script>
<script type='text/javascript' src='http://smartjob.vn/wp-includes/js/tinymce/langs/wp-langs-en.js?ver=4205-20150910'></script>
		<script type="text/javascript">
		tinyMCEPreInit.load_ext("http://smartjob.vn/wp-content/themes/SMARTJOB/js/lib/tiny_mce/plugins/et_heading", "en");
tinymce.PluginManager.load("etHeading", "http://smartjob.vn/wp-content/themes/SMARTJOB/js/lib/tiny_mce/plugins/et_heading/editor_plugin.js");
tinyMCEPreInit.load_ext("http://smartjob.vn/wp-content/themes/SMARTJOB/js/lib/tiny_mce/plugins/et_link", "en");
tinymce.PluginManager.load("etLink", "http://smartjob.vn/wp-content/themes/SMARTJOB/js/lib/tiny_mce/plugins/et_link/editor_plugin.js");


		( function() {
			var init, id, $wrap;

			if ( typeof tinymce !== 'undefined' ) {
				for ( id in tinyMCEPreInit.mceInit ) {
					init = tinyMCEPreInit.mceInit[id];
					$wrap = tinymce.$( '#wp-' + id + '-wrap' );

					if ( ( $wrap.hasClass( 'tmce-active' ) || ! tinyMCEPreInit.qtInit.hasOwnProperty( id ) ) && ! init.wp_skip_init ) {
						tinymce.init( init );

						if ( ! window.wpActiveEditor ) {
							window.wpActiveEditor = id;
						}
					}
				}
			}

			if ( typeof quicktags !== 'undefined' ) {
				for ( id in tinyMCEPreInit.qtInit ) {
					quicktags( tinyMCEPreInit.qtInit[id] );

					if ( ! window.wpActiveEditor ) {
						window.wpActiveEditor = id;
					}
				}
			}
		}());
		</script>		
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>

<?php
}
?>