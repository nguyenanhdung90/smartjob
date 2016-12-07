<?php
/**
 * Template Name: Upgrade Account
 */
$options	=	new JE_Resume_Options ();
if(!$options->get_resume_status() ) wp_redirect( home_url( ) );

global $user_ID , $current_user, $post;
if(isset($_REQUEST['resume_id']) && $_REQUEST['resume_id'] != '') {
	et_write_session ('resume_id' , $_REQUEST['resume_id']);
}
get_header();
$plans	=	et_get_resume_plans ();

$job_opt 		= new ET_JobOptions () ;
$contact_widget	= $job_opt->get_upgrade_account_sidebar ();

$steps = array('1','2','3' );
?>
<div class="wrapper">

	<div class="heading">
		<div class="main-center">
			<h1 class="font-quicksand"><span class="icon" data-icon="c"></span><?php the_title () ?></h1>
		</div>
	</div>

	<div class="main-center margin-top25 clearfix ">
		<?php
			$main_colum	=	'full-column';

			if( !empty($contact_widget) || current_user_can('manage_options') ) {
				$main_colum	=	'main-column' ;
			}
		?>

		<div class="<?php echo $main_colum ?>">
			<div class="post-a-job" id="upgrade_account">
				<div class="step">
					<div class="toggle-title f-left-all">
						<div class="icon-border"><?php echo array_shift($steps) ?></div>
						<span class="icon" data-icon="2"></span>
						<span><?php  _e("Choose the pricing plan that fits your needs", ET_DOMAIN); ?></span>
					</div>
					<div class="toggle-content clearfix">
						<ul>
							<?php foreach ($plans as $key => $value) { ?>
								<li class="clearfix">
									<div class="f-left">
										<div class="title"><?php echo $value['title'] ?> <span><?php echo et_get_price_format($value['price']) ?></div>
										<div class="desc"><?php echo $value['description'] ?></div>
									</div>
									<div class="btn-select f-right">
										<button class="bg-btn-hyperlink border-radius select_plan" data-package="<?php echo $key ?>"  data-price="<?php echo $value['price'] ?>" >Select</button>
									</div>
								</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<!-- step authentication -->
				<?php if(!$user_ID)  { ?>
				<div class="step" id='step_auth'>
					<div class="toggle-title f-left-all">
						<div class="icon-border"><?php echo array_shift($steps) ?></div>
						<span class="icon" data-icon="2"></span>
						<span><?php _e('Login or create an account', ET_DOMAIN );?></span>
					</div>
					<div class="toggle-content login clearfix"  style="display: none">
						<div class="tab-title f-left-all clearfix">
							<div class="bg-tab active"><?php _e('Register', ET_DOMAIN );?></div>
							<div class="bg-tab"><span><?php _e('Already have an account?', ET_DOMAIN );?></span> <?php _e('Login', ET_DOMAIN );?></div>
						</div>
						<div class="tab-content">
							<div class="form current">
								<form id="register" novalidate="novalidate" autocomplete="on">
									<div class="form-item">
										<div class="label">
											<label for="reg_email">
												<h6 class="font-quicksand"><?php _e('USER NAME', ET_DOMAIN );?></h6>
												<?php _e('Please enter your username', ET_DOMAIN );?>
											</label>
										</div>
										<div>
											<input class="bg-default-input is_user_name" tabindex="1" name="reg_user_name" id="reg_user_name" type="text"/>
										</div>
									</div>
									<div class="form-item">
										<div class="label">
											<label for="reg_email">
												<h6 class="font-quicksand"><?php _e('EMAIL ADDRESS', ET_DOMAIN );?></h6>
												<?php _e('Please enter your email address', ET_DOMAIN );?>
											</label>
										</div>
										<div>
											<input class="bg-default-input is_email" tabindex="1" name="reg_email" id="reg_email" type="email"/>
										</div>
									</div>
									<div class="form-item">
										<div class="label">
											<label for="reg_pass">
												<h6 class="font-quicksand"><?php _e('PASSWORD', ET_DOMAIN );?></h6>
												<?php _e('Enter your password', ET_DOMAIN );?>
											</label>
										</div>
										<div>
											<input class="bg-default-input is_pass" tabindex="2" name="reg_pass" id="reg_pass" type="password" />
										</div>
									</div>
									<div class="form-item">
										<div class="label">
											<label for="reg_pass_again">
												<h6 class="font-quicksand repeat_pass "><?php _e('RETYPE YOUR PASSWORD', ET_DOMAIN );?></h6>
												<?php _e('Retype your password', ET_DOMAIN );?>
											</label>
										</div>
										<div>
											<input class="bg-default-input" tabindex="3" name="reg_pass_again" id="reg_pass_again" type="password" />
										</div>
									</div>
									<div class="form-item no-border-bottom clearfix">
										<div class="label">&nbsp;</div>
										<div class="btn-select">
											<button class="bg-btn-action border-radius" tabindex="4" type="submit" id="submit_register"><?php _e('CONTINUE', ET_DOMAIN );?></button>
										</div>
									</div>
								</form>
							</div>
							<div class="form" style="display:none;">
								<form id="login" novalidate="novalidate" autocomplete="on">
									<div class="form-item">
										<div class="label">
											<h6 class="font-quicksand"><?php _e('USERNAME or EMAIL ADDRESS', ET_DOMAIN );?></h6>
											<?php _e('Please enter your username or email', ET_DOMAIN );?>
										</div>
										<div>
											<input class="bg-default-input is_email is_user_name" tabindex="1" name="log_email" id="log_email" type="text" />
										</div>
									</div>
									<div class="form-item">
										<div class="label">
											<h6 class="font-quicksand"><?php _e('PASSWORD', ET_DOMAIN );?></h6>
											<?php _e('Enter your password', ET_DOMAIN );?>
										</div>
										<div>
											<input class="bg-default-input is_pass" tabindex="2" name="log_pass" id="log_pass" type="password" />
										</div>
									</div>
									<div class="form-item no-border-bottom clearfix">
										<div class="label">&nbsp;</div>
										<div class="btn-select">
											<button class="bg-btn-action border-radius" tabindex="3" type="submit" id="submit_login"><?php _e('LOGIN', ET_DOMAIN );?></button>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

				<?php } ?>
				<!-- step payment -->
				<?php
					$payment_gateways	=	et_get_enable_gateways();
					if(!empty($payment_gateways) ) { ?>
						<div class="step" id='step_payment'>
							<div class="toggle-title f-left-all">
								<div class="icon-border"><?php echo array_shift($steps); ?></div>
								<span class="icon" data-icon="2"></span>
								<span><?php _e('Send payment and upgrade your account', ET_DOMAIN );?></span>
							</div>

							<div class="toggle-content payment clearfix" style= "display : none" id="payment_form">
								<form method="post" action="" id="checkout_form">
									<div class="payment_info"> </div>
									<div style="position:absolute; left : -7777px; " >
										<input type="submit" id="payment_submit" />
									</div>
								</form>
								<form method="post" action="https://www.2checkout.com/checkout/purchase" id="2checkout_form">
									<div class="payment_info"> </div>
									<div style="position:absolute; left : -7777px; " >
										<input type="submit" id="2co_submit" class="payment_submit" />
									</div>
								</form>
								<ul>
									<?php
										$je_default_payment	=	array('google_checkout', 'paypal', 'cash', '2checkout')	;

										do_action('before_je_payment_button', $payment_gateways);

										foreach ( $payment_gateways as $key => $payment ) {
											if( !isset($payment['active']) || $payment['active'] == -1 || !in_array($key, $je_default_payment) )
												continue; ?>
											<li class="clearfix">
												<div class="f-left">
													<div class="title"><?php echo $payment['label']?></div>
													<?php if ( isset($payment['description']) ) {?>
														<div class="desc"><?php echo $payment['description'] ?></div>
													<?php }?>
												</div>
												<div class="btn-select f-right">
													<button class="bg-btn-hyperlink border-radius select_payment" data-gateway="<?php echo $key?>" ><?php _e('Select', ET_DOMAIN );?></button>
												</div>

											</li>
									<?php } ?>

									<?php
										do_action('after_je_payment_button', $payment_gateways);
									?>
								</ul>
								<div style="position:absolute; left : -7777px; " >
									<input type="submit" id="payment_submit" />
								</div>
							</div>
						</div>
				<?php }?>

			</div>
		</div>

		<?php if( !empty($contact_widget) || current_user_can('manage_options') ) {	?>
				<div class="second-column widget-area" id="static-text-sidebar">
					<div id="sidebar" class="upgrade-account-sidebar">
						<?php

							je_user_package_data ($user_ID , 'resume');

							foreach ( $contact_widget as $key => $value ) { ?>
								<div class="widget widget-contact bg-grey-widget" id="<?php echo $key ?>">
									<div class="view">
										<?php echo $value ?>
									</div>
									<?php if ( current_user_can('manage_options') ) { ?>
										<div class="btn-widget edit-remove">
											<a href="#" class="bg-btn-action border-radius edit"><span class="icon" data-icon='p'></span></a>
											<a href="#" class="bg-btn-action border-radius remove"><span class="icon" data-icon='#'></span></a>
										</div>
									<?php }	?>
								</div>

						<?php } ?>
					</div>

					<?php if ( current_user_can('manage_options') ) { ?>
							<div class="widget widget-contact bg-grey-widget" id="widget-contact">
								<a href="#" class="add-more"><?php _e('Add a text widget +', ET_DOMAIN) ?> </a>
							</div>
					<?php }	?>

				</div>
		<?php } ?>

	</div>
</div>
<script type="text/data" id="job_data">
	<?php echo json_encode($post) ?>
</script>
<?php get_footer(); ?>