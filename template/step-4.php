<?php
	global $steps;
	$payment_gateways	=	et_get_enable_gateways();

	if ( !empty($payment_gateways) ) {
?>

		<div class="step" id='step_payment'>
			<div class="toggle-title f-left-all">
				<div class="icon-border"><?php echo array_shift($steps) ?></div>
				<span class="icon" data-icon="2"></span>
				<span><?php _e('Send payment and submit your job', ET_DOMAIN );?></span>
			</div>

			<div class="toggle-content payment clearfix" style="display: none" id="payment_form">
				<form method="post" action="" id="checkout_form">
					<div class="payment_info"> </div>
					<div style="position:absolute; left : -7777px; " >
						<input type="submit" id="payment_submit" />
					</div>
				</form>
				<ul>
					<?php
						$je_default_payment	=	array( 'paypal', 'cash', '2checkout' )	;
						do_action ('before_je_payment_button', $payment_gateways);
						foreach ($payment_gateways as $key => $payment) {
							if( !isset($payment['active']) || $payment['active'] == -1 || !in_array($key, $je_default_payment))
								continue; ?>

							<li class="clearfix">

								<div class="f-left">
									<div class="title"><?php echo $payment['label']?></div>

									<?php if(isset($payment['description'])) {?>
										<div class="desc"><?php echo $payment['description'] ?></div>
									<?php }?>

								</div>

								<div class="btn-select f-right">
									<button class="bg-btn-hyperlink border-radius select_payment" data-gateway="<?php echo $key?>" ><?php _e('Select', ET_DOMAIN );?></button>
								</div>

							</li>
					<?php } ?>

					<?php do_action ('after_je_payment_button', $payment_gateways); ?>

				</ul>
			</div>

		</div>
	<?php } ?>