<div class="et-main-main clearfix inner-content" id="setting-payment"  <?php if ($sub_section != 'payment') echo 'style="display:none"' ?> >
<?php
	$currencies			=	et_get_currency_list();
	$selected_currency	=	et_get_default_currency( ARRAY_A );
	$currency			=	isset($selected_currency['code']) ? $selected_currency['code'] : 'USD';
?>
	<div class="title font-quicksand"><?php _e("Currency",ET_DOMAIN);?></div>
	<div class="desc choose-currency">
		<?php _e("Select the currency you will use in your financial transactions.",ET_DOMAIN);?>
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
		<div class="no-padding f-left width100p clearfix">	        				
			<div class="add-new-currency btn-language">
				<button><?php _e('Add a new currency', ET_DOMAIN) ?><span data-icon="+" class="icon"></span></button>
			</div>
			<div class="show-new-currency">
				<form id="engine-currency-form" class="engine-currency-form">
					<div class="form payment-plan">
						<div class="form-item">
							<div class="label"><?php _e("Enter the name of your currency",ET_DOMAIN);?></div>
							<input class="bg-grey-input not-empty" name="currency_name" type="text" id="currency_name"  />
						</div>
						<div class="form-item f-left-all clearfix">
							<div class="width50p">
								<div class="label"><?php _e("Currency Code",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty" name="currency_code" type="text" id="currency_code" /> 
							</div>
							<div class="width50p">
								<div class="label"><?php _e("Currency Sign",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty" type="text" name="currency_icon" id="currency_icon" /> 
							</div>
						</div>
						<div class="form-item clearfix">
							<div class="label"><?php _e("Currency Sign Position",ET_DOMAIN);?></div>
							<div class="button-enable font-quicksand symbol-pos">
								<a href="#" rel="right" title="" class="active">
									<span><?php _e('After', ET_DOMAIN) ?></span>
								</a>
								<a href="#" rel="left" title="" class="active selected">
									<span><?php _e('Before', ET_DOMAIN) ?></span>
								</a>
							</div>
							<span class="currency_text"><sup>$</sup>1,000</span>
						</div>
						<div class="submit">
							<button id="add-new-currency" class="btn-button engine-submit-btn">
								<?php _e("Save Currency",ET_DOMAIN);?><span class="icon" data-icon="+"></span>
							</button>
						</div>
						<div class="plans_help">
							<span>
								<a href="https://www.2checkout.com/blog/getting-started/international-issues/are-foreign-currencies-supported/" target="_blank"><?php _e('2Checkout supported currencies') ?></a>
							</span><br/>
							<span>
								<a href="https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_currency_codes" target="_blank"><?php _e('Paypal supported currencies') ?></a>
							</span>
						</div>
					</div>
				</form>
			</div>
		</div>

	</div>

	<div class="title font-quicksand"><?php _e("Payment Test Mode",ET_DOMAIN);?></div>
	<div class="desc">
	 	<?php _e("Enabling this will allow you to test payment without charging your account.",ET_DOMAIN);?>			
		<!-- <a class="find-out font-quicksand" href="#">
	 		<?php _e("Find out more",ET_DOMAIN);?> <span class="icon" data-icon="i"></span>
	 	</a> -->
		<div class="inner no-border btn-left">
			<div class="payment">
				<div class="button-enable font-quicksand">
					<?php et_display_enable_disable_button('payment_test_mode', __("Payment Test Mode",ET_DOMAIN), 'payment_test_mode'); ?>
				</div>
			</div>
		</div>	        				
	</div>	

	<div class="title font-quicksand"><?php _e("Payment Gateways",ET_DOMAIN);?></div>
	<div class="desc">
		<?php 
			$validator	=	new ET_Validator();
			$payment_gateways	=	et_get_enable_gateways();
			
			$paypal_API			=	ET_Paypal::get_api ();
			$_2CO_API			=	ET_2CO::get_api();
			//$google				=	ET_GoogleCheckout::get_api();
			$cash				= 	ET_Cash::get_message ();
			
		?>
		<?php _e("Payment options your users can choose from when making payments to publish jobs on your website.",ET_DOMAIN);?> 
		<!-- <a class="find-out font-quicksand" href="#">
			Find out more <span class="icon" data-icon="i"></span>
		</a> -->
		<div class="inner">
			<div class="item">
				<div class="payment">
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
				<div class="payment">
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
							<?php _e("Your 2Checkout Secret Word ",ET_DOMAIN);?>
							
						</div>
						<input class="bg-grey-input <?php if($_2CO_API['secret_key'] == '') echo 'color-error' ?>" type="text" name="2checkout-secretkey" value="<?php echo $_2CO_API['secret_key'] ?>" />
						<span class="icon <?php if($_2CO_API['secret_key'] == '') echo 'color-error' ?>" data-icon="<?php  data_icon($_2CO_API['secret_key']) ?>"></span>
					</div>
					
				</div>
			</div>

			<!-- <div class="item">
				<div class="payment">
					<a class="icon" data-icon="y" href="#"></a>
					<div class="button-enable font-quicksand">
						<?php //et_display_enable_disable_button('google_checkout', 'Google Checkout')?>
					</div>
					<span class="message"></span>
					<?php _e("Google Checkout",ET_DOMAIN);?>
				</div>
				<div class="form payment-setting">
					<div class="form-item">
						<div class="label">
							<?php _e("Your Google Checkout Merchant ID ",ET_DOMAIN);?> 
							
						</div>
						<input class="bg-grey-input <?php if($google['merchant_id'] == '') echo 'color-error' ?>" name="google-merchant-id" type="text" value="<?php echo $google['merchant_id'] ?>" />
						<span class="icon <?php if($google['merchant_id'] == '') echo 'color-error' ?>" data-icon="<?php  data_icon($google['merchant_id']) ?>"></span>
					</div>
					<div class="form-item">
						<div class="label">
							<?php _e("Your Google Checkout Merchant Key ",ET_DOMAIN);?>
							
						</div>
						<input class="bg-grey-input <?php if($google['merchant_key'] == '') echo 'color-error' ?>" type="text" name="google-merchant-key" value="<?php echo $google['merchant_key'] ?>" />
						<span class="icon <?php if($google['merchant_key'] == '') echo 'color-error' ?>" data-icon="<?php  data_icon($google['merchant_key']) ?>"></span>
					</div>
				</div>
			</div> -->

			<?php do_action ('je_payment_settings')?>
			<div class="item">
				<div class="payment">
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

	<?php require_once 'setting-payment-plans.php'; ?>
	<div class="title font-quicksand"><?php _e(" Limit Free Plan Use",ET_DOMAIN);?></div>
	<div class="desc">
		<?php _e("Enter the maximum number allowed for employers to use your Free plan",ET_DOMAIN);?>
		<div class="form no-margin no-padding no-background">
			<div class="form-item">
				<input class="bg-grey-input" type="text" value="<?php echo get_theme_mod( 'je_limit_free_plan' , '' ); ?>" id="limit-free-plan" name="limit-free-plan" />
			</div>
		</div>
	</div>
</div>
