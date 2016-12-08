
<div class="sharing">
	<?php
	$api = get_option('et_addthis_api', '');
	//$api	=	'ra-525f557a07fee94d';
	if ($api)
		$api = '#pubid=' . $api;
	?>
	<span class="h"><?php _e('Share', ET_DOMAIN) ?>:</span>
	<!-- AddThis Button BEGIN -->
	<div class="addthis_toolbox addthis_default_style ">
		<ul>
			<li><a id="addthis_button_facebook  sharing-btn" class="addthis_button_facebook at300b sharing-btn"><img src="<?php bloginfo( 'template_directory' ) ?>/img/share-fb.png" width="40" height="40" border="0" alt="Share to Facebook" /></a></li>
			<li><a id="addthis_button_twitter  sharing-btn" class="addthis_button_twitter at300b sharing-btn"><img src="<?php bloginfo( 'template_directory' ) ?>/img/share-twitter.png" width="40" height="40" border="0" alt="Share to Twiiter" /></a></li>
			<li><a id="addthis_button_google_plusone_share  sharing-btn" class="addthis_button_google_plusone_share at300b sharing-btn"><img src="<?php bloginfo( 'template_directory' ) ?>/img/share-gplus.png" width="40" height="40" border="0" alt="Share to Google plus" /></a></li>
			<li><a id="addthis_button_linkedin  sharing-btn"  class="addthis_button_linkedin at300b sharing-btn"><img src="<?php bloginfo( 'template_directory' ) ?>/img/share-in.png" width="40" height="40" border="0" alt="Share to LinkedIn" /></a></li>
		</ul>
	</div>
		<script type="text/javascript">var addthis_config = {
			"data_track_addressbar":false,
		};
		// addthis.layers({
		//     'theme' : 'transparent',
		//     'share' : {
		//         'position' : 'left',
		//         'services' : 'facebook,twitter,google_plusone_share,pinterest_share,print,more'
		//     },
		//     'desktop' : false,
		//     'mobile' : false,

		//     'whatsnext' : {},
		//     'recommended' : {}
		// });
	</script>
	<!--<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js<?php echo $api; ?>"></script>
	<!-- AddThis Button END -->
</div>