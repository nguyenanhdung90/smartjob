<?php
	$job_id				=	$_REQUEST['post_id'];
	$post				=	get_post($job_id);
	$packageID			=	get_post_meta( $job_id, 'et_job_package' , true );
	$payment_gateways	=	et_get_enable_gateways();
?>

<h1 class="page-title" >
	<?php _e("Post an Ad", ET_DOMAIN); ?>
    <span class="step-number"><?php _e("Step payment", ET_DOMAIN); ?></span>
</h1>
<div class="payment-form register-form" >

	<input type="hidden" value="<?php echo $post->post_author; ?>" name="post_author" class="post_author" />
	<input type="hidden" value="<?php echo $packageID ?>" name="et_payment_package" class="et_payment_package" />
	<input type="hidden" value="<?php echo $job_id ?>" name="ad_id" class="ad_id" />

   	<?php
   	$ce_default_payment	=	array( 'cash', '2checkout')	;

   	do_action('before_je_mobile_payment_button', $payment_gateways );

	foreach ($payment_gateways as $key => $payment) {
		if( !isset($payment['active']) || $payment['active'] == -1 || !in_array($key, $ce_default_payment)) 
			continue;

	?>
	<div data-role="fieldcontain" class="post-new-job" >
		<?php
			echo $payment['description'];
		?>
		<input type="button" value="<?php echo $payment['label']; ?>" data-payment='<?php echo $key ?>' class="main-payment ui-btn">
	</div>
	<?php }
		do_action('after_je_mobile_payment_button', $payment_gateways );
	?>

</div>

<form method="post" action="" id="checkout_form">
	<div class="payment_info"> </div>
	<div style="position:absolute; left : -7777px; " >
		<input type="submit" id="payment_submit" />
		<input type="hidden" name="no_note" value="1">
	</div>
</form>