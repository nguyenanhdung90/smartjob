<?php et_get_mobile_header('mobile'); ?>

<div data-role="content" class="post-content">
	<h1 class="post-title job-title">
		<?php _e('Page Not found :(',ET_DOMAIN); ?>
	</h1>
	<div class="content-info content-padding">
		<p>
		<?php  
			_e('Sorry, but the page you were trying to view does not exist.</p><p>It looks like this was the result of either:</p><p>- a mistyped address<br />	- an out-of-date link', ET_DOMAIN);
		?>
		</p>
		<p><?php _e('Other things to try:', ET_DOMAIN ) ?> </p>
		<p><?php _e('Search', ET_DOMAIN ) ?> <strong id="google-url"><?php echo home_url(); ?></strong>:</p>
		<div class="input-text">
			<form action="http://google.com/search" method="get" id="google-search">
			</form>		
			<span class="icon" data-icon="s"></span>
		</div>
		<script type="text/javascript" src="<?php bloginfo('template_url')?>/mobile/js/404.js"></script>
	</div>
	<div class="content-padding">
		<a rel="external" data-ajax="false" href="<?php echo home_url(); ?>" class="btn-grey btn-wide btn-load-more ui-corner-all">
		<?php _e('Return to Home',ET_DOMAIN); ?>
		</a>
	</div>
</div><!-- /content -->

<?php et_get_mobile_footer('mobile'); ?>