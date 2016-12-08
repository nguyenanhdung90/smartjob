<?php
global $steps,$job;
?>

<div class="step current" id="step_package">
	<div class="toggle-title f-left-all  <?php if(!!$job) echo 'toggle-complete';?>">
		<div class="icon-border"><?php echo array_shift($steps) ?></div>
		<span class="icon" data-icon="2"></span>
		<span class="step-plan-label" data-label="<?php _e('Choose the pricing plan that fits your needs', ET_DOMAIN);?>" >
			<?php _e('Choose the pricing plan that fits your needs', ET_DOMAIN);?>
		</span>
	</div>
	<div class="toggle-content clearfix">
	<?php
		global $current_user;
		$plans = et_get_payment_plans();
		do_action ('je_before_job_package_list');

		if ( !empty($plans) ){	?>
			<ul>
				<?php
				$only_free = false;
				/**
				 * check if only one package force user select it
				*/
				if(count($plans) == 1 ) {
					$temp	=	$plans;
					$p		=	array_pop($temp);
					if( $p['price'] == 0 )  $only_free = true;
				}

				foreach ( $plans as $plan ) :

					$sel = ( isset($job['job_package']) && $job['job_package'] == $plan['ID']) ? 'selected' : '';

					$featured_text = $plan['featured'] ? __('featured', ET_DOMAIN) : __('normal', ET_DOMAIN);

					$plan['quantity'] = isset($plan['quantity']) ? $plan['quantity'] : 1;
					if ($plan['quantity'] > 1){
						$content_plural = sprintf( __('Each job will be displayed as %s for %d days.', ET_DOMAIN), $featured_text, $plan['duration'] );
						$content_single = sprintf( __('Each job will be displayed as %s for %d day.', ET_DOMAIN), $featured_text, $plan['duration'] );
					}else {
						$content_plural = sprintf( __('Your job will be displayed as %s for %d days.', ET_DOMAIN), $featured_text, $plan['duration'] );
						$content_single = sprintf( __('Your job will be displayed as %s for %d day.', ET_DOMAIN), $featured_text, $plan['duration'] );
					}
					$desc = $plan['duration'] == 1 ? $content_single : $content_plural;
					$purchase_plans = !empty($current_user->ID) ? et_get_purchased_quantity($current_user->ID) : array();
					$a = 0; $j =  0; ?>

					<li class="clearfix <?php // echo $sel;?>">

						<div class="label f-left">
							<div class="title">
								<?php echo $plan['title'] ?>
								<?php if($plan['price'] > 0) {?>
									<span> <?php echo et_get_price_format( $plan['price'], 'sup' ) ?> </span>
								<?php } ?>
								<?php
									// if current user have purchased plans, show they
									if (!empty($purchase_plans[$plan['ID']]) && $purchase_plans[$plan['ID']] > 0) {
										echo '<span class="quan"> - ';
										echo $purchase_plans[$plan['ID']] > 1 ?
											sprintf( __('You have %d jobs in this plan', ET_DOMAIN), $purchase_plans[$plan['ID']]) :
											sprintf( __('You have %d job in this plan', ET_DOMAIN), $purchase_plans[$plan['ID']]);
											$a	=	1;
										echo '</span>';
									} else if($plan['price'] > 0) {
										echo '<span class="quan"> - ';
											echo $plan['quantity'] > 1 ?
												sprintf( __('This plan includes %s jobs', ET_DOMAIN), $plan['quantity']) :
												sprintf( __('This plan includes %s job', ET_DOMAIN), $plan['quantity']);
										echo '</span>';
									}
								?>

							</div>
							<div class="desc"><?php echo $desc ?></div>
						</div>
						<div class="btn-select f-right">
							<!-- /*add class mark-step will be auto select*/ -->
							<button class="bg-btn-hyperlink border-radius select_plan <?php if( ($a == 1 && $j == 0) || $only_free ) { echo 'mark-step' ; $j = 1;} ?>" 
								data-package="<?php echo $plan['ID'];?>"
								data-price="<?php echo $plan['price'];?>"
								<?php if( $plan['price'] > 0 ) { ?>
									data-label="<?php printf(__("You have selected: '%s'", ET_DOMAIN) , $plan['title'] ); ?>"
								<?php } else { ?>
									data-label="<?php _e("You are currently using the 'Free' plan", ET_DOMAIN); ?>"
								<?php } ?>
								>
								<?php _e('Select', ET_DOMAIN );?>
							</button>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

		<?php } ?>

		<?php do_action ('je_after_job_package_list');	 ?>

		<script id="package_plans" type="text/data">
			<?php echo json_encode($plans); ?>
		</script>

	</div> <!-- end toggle content !-->

</div> <!-- end step_package !-->