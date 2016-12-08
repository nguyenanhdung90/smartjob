<div class="et-main-main clearfix inner-content" id="wizard-payment"  <?php if ($section != 'payment') echo 'style="display:none"' ?> >
<?php
	$currencies			=	et_get_currency_list();
	$selected_currency	=	et_get_default_currency( ARRAY_A );
	$currency			=	isset($selected_currency['code']) ? $selected_currency['code'] : 'USD';
?>
	<div class="title font-quicksand"><?php _e("Currency",ET_DOMAIN);?></div>
	<div class="desc">
		<?php _e("Select the currency you want to use in your job board",ET_DOMAIN);?> 
		<!-- <a class="find-out font-quicksand" href="#">
			<?php _e("Find out more",ET_DOMAIN);?><span class="icon" data-icon="i"></span>
		</a> -->
		<ul class="menu-currency">
		<?php 
		foreach ($currencies as $key => $cur) {
			$active	=	'';
			if( $key == $currency )  $active	=	'active';
		?>	
			<li><a 	href="#et-change-currency" class="select-currency <?php echo $active ?>" title="<?php echo $cur['alt']?>" 
					rel="<?php echo $cur['code']?>">
					<?php et_display_currency( $cur['label'], $cur,' ', ' ')?> 
				</a>
			</li>
		<?php 
		} 
		?>
		</ul>
	</div>

	<div class="title font-quicksand"><?php _e("Payment Gateways",ET_DOMAIN);?></div>
	<div class="desc">
	<?php 
		$validator	=	new ET_Validator();
		$payment_gateways	=	et_get_enable_gateways();
		
		$paypal_API			=	ET_Paypal::get_api ();
		$_2CO_API			=	ET_2CO::get_api();
		$cash				= 	ET_Cash::get_message ();
		
	?>
		<?php _e("How your users pay you for publishing jobs on your website",ET_DOMAIN);?> 
		<!-- <a class="find-out font-quicksand" href="#">
			Find out more <span class="icon" data-icon="i"></span>
		</a> -->
		<div class="inner">
			<div class="item">
				<div class="payment options">
					<a class="icon" data-icon="y" href="#"></a>
					<div class="button-enable font-quicksand">
						<?php et_display_enable_disable_button('paypal', 'Paypal')?>
					</div>
					<span class="message"></span>
					<?php _e("Paypal",ET_DOMAIN);?>
				</div>
				<div class="form payment-setting">
					<div class="form-item">
						<div class="label">
							<?php _e("Enter your PayPal email address ",ET_DOMAIN);?>
							
						</div>
						<input class="bg-grey-input <?php if($paypal_API['api_username'] == '') echo 'color-error' ?>" type="text" name="paypal-APIusername" value="<?php echo $paypal_API['api_username']?>"/>
						<span class="icon <?php if (!$validator->validate('email', $paypal_API['api_username'])) echo 'color-error' ?>" data-icon="<?php  data_icon($paypal_API['api_username'],'email') ?>"></span>
					</div>
				</div>
			</div>

			<div class="item">
				<div class="payment options">
					<a class="icon" data-icon="y" href="#"></a>
					<div class="button-enable font-quicksand">
						<?php et_display_enable_disable_button('2checkout', '2CheckOut')?>
					</div>
					<span class="message"></span>
					<?php _e("2CheckOut",ET_DOMAIN);?>
				</div>
				<div class="form payment-setting">
					<div class="form-item">
						<div class="label">
							<?php _e("Your 2Checkout Seller ID ",ET_DOMAIN);?> 
							
						</div>
						<input class="bg-grey-input <?php if($_2CO_API['sid'] == '') echo 'color-error' ?>" name="2checkout-sid" type="text" value="<?php echo $_2CO_API['sid'] ?>" />
						<span class="icon <?php if($_2CO_API['sid'] == '') echo 'color-error' ?>" data-icon="<?php  data_icon($_2CO_API['sid']) ?>"></span>
					</div>
					<div class="form-item">
						<div class="label">
							<?php _e("Your 2Checkout Secret Key ",ET_DOMAIN);?>
							
						</div>
						<input class="bg-grey-input <?php if($_2CO_API['secret_key'] == '') echo 'color-error' ?>" type="text" name="2checkout-secretkey" value="<?php echo $_2CO_API['secret_key'] ?>" />
						<span class="icon <?php if($_2CO_API['secret_key'] == '') echo 'color-error' ?>" data-icon="<?php  data_icon($_2CO_API['secret_key']) ?>"></span>
					</div>
				</div>
			</div>
			<?php do_action ('et_payment_setup')?>
			<div class="item">
				<div class="payment options">
					<a class="icon" data-icon="y" href="#"></a>
					<div class="button-enable font-quicksand">
						<?php et_display_enable_disable_button('cash', 'Cash')?>
					</div>
					<span class="message"></span>
					<?php _e("Cash",ET_DOMAIN);?>
				</div>	    
				<div class="form payment-setting">
					<div class="form-item">
						<div class="label">
							<?php _e("Cash Message",ET_DOMAIN);?>
							
						</div>
						<div class="cash-message">
							<?php wp_editor( $cash ,'cash-message' , je_editor_settings () ); ?>
							<!-- <textarea name="cash-message" id="cash-message" style="width:100%;"><?php echo $cash ?></textarea> -->
						</div>
						<span class="icon <?php if($cash == '') echo 'color-error' ?>" data-icon="<?php  data_icon($cash) ?>"></span>
					</div>
				</div>    						
			</div>

		</div>
	</div>
	<?php require_once 'setting-payment-plans.php'; 
	et_wizard_nexstep_button (3);
	?>
</div>
