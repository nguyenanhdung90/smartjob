<?php et_get_mobile_header('mobile'); ?>

<div data-role="content" class="post-content">
	<h1 class="post-title job-title">
		<?php the_title() ?>
	</h1>
	<div class="content-info content-padding">
		<p>
		<?php
			_e('Sorry, this feature is not supported in mobile version yet.', ET_DOMAIN);
		?>
		</p>
	</div>
	<div class="content-padding">
		<a rel="external" data-ajax="false" href="<?php echo home_url(); ?>" class="btn-grey btn-wide btn-load-more ui-corner-all">
		<?php _e('Return to Home',ET_DOMAIN); ?>
		</a>
	</div>
</div><!-- /content -->

<?php et_get_mobile_footer('mobile'); ?>