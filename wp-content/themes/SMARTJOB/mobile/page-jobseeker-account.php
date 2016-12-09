<?php et_get_mobile_header('mobile'); ?>
<div data-role="content" class="post-content">
	<h1 class="post-title job-title">
		<?php _e("Log Out", ET_DOMAIN); ?>
<!-- 		<span class="post-title-right"><a href="#">Logout</a></span> -->
	</h1>
	<form action="" method="post">
		<div class="content-field f-padding">
			<div class="input-button">
				<input type="button" class="et_logout" value="<?php _e('Logout', ET_DOMAIN) ?>">
			</div>
		</div>
	</form>
</div><!-- /content -->

<?php et_get_mobile_footer('mobile'); ?>