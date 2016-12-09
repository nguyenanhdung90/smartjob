	<div class="title font-quicksand"><?php _e("Payment Plans",ET_DOMAIN);?></div>
	<div class="desc">
		<?php _e("Set payment plans your employers can choose from when posting new jobs.",ET_DOMAIN);?> 
		<!-- <a class="find-out font-quicksand" href="#">
			<?php _e("Find out more",ET_DOMAIN);?><span class="icon" data-icon="i"></span>
		</a> -->
		<div class="inner">
			<div id="payment_lists">
			<?php 
				$plans = et_get_payment_plans();
				$json_plans = array();
				if ( is_array($plans) ){
					echo '<ul class="pay-plans-list sortable">';
					foreach ((array)$plans as $plan) {
						$tooltip = array(
							'delete' => __('Delete', ET_DOMAIN),
							'edit' => __('Edit', ET_DOMAIN),
							);
						$feature = $plan['featured'] == 1 ? '<em class="icon-text">^</em>' : '';
						?>
						<li class="item" id="payment_<?php echo $plan['ID'] ?>" data="<?php echo $plan['ID'] ?>">
							<div class="sort-handle"></div>
							<span><?php echo $plan['title'] . ' ' . $feature?></span>  
							<?php printf( __('%s for %s days', ET_DOMAIN), et_get_price_format($plan['price']), $plan['duration'] ) ?>
							<div class="actions">
								<a href="#" title="<?php _e('Edit', ET_DOMAIN) ?>" class="icon act-edit" rel="<?php echo $plan['ID'] ?>" data-icon="p"></a>
								<a href="#" title="<?php _e('Delete', ET_DOMAIN) ?>" class="icon act-del" rel="<?php echo $plan['ID'] ?>" data-icon="D"></a>
							</div>
						</li>
						<?php
					}
					echo '</ul>';
				}
				else {
					echo '<p>' . __('There is no added plan yet' ,ET_DOMAIN) . '</p>';
				}
				?>
			</div>
			<script type="application/json" id="payment_plans_data">
				<?php echo json_encode( array_map('et_create_payment_plan_response', array_values($plans)) ) ?>
			</script>
		
			<div class="item">
				<form id="payment_plans_form" action="" class="engine-payment-form">
					<input type="hidden" name="action" value="et-save-payment">
					<input type="hidden" name="et_action" value="et-save-payment">
					<div class="form payment-plan">
						<div class="form-item">
							<div class="label"><?php _e("Enter a name for your plan",ET_DOMAIN);?></div>
							<input class="bg-grey-input not-empty" name="payment_name" type="text" />
						</div>
						<div class="form-item f-left-all clearfix">
							<div class="width33p">
								<div class="label"><?php _e("Price",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty is-number" name="payment_price" type="text" /> 
								<?php echo $selected_currency['label']; ?>
							</div>
							<div class="width33p">
								<div class="label"><?php _e("Availability",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty is-number" type="text" name="payment_duration" /> 
								<?php _e("days",ET_DOMAIN);?>
							</div>
							<div class="width33p">
								<div class="label"><?php _e("Number of jobs",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty is-number" type="text" name="payment_quantity" /> 
								<?php _e("posts",ET_DOMAIN);?>
							</div>
						</div>
						<div class="form-item">
							<div class="label"><?php _e("Featured Job",ET_DOMAIN);?></div>
							<input type="hidden" name="payment_featured" value="0">
							<input type="checkbox" name="payment_featured" value="1"/> <?php _e("Jobs posted under this plan will be featured.",ET_DOMAIN);?>
						</div>
						<div class="submit">
							<button id="add_playment_plan" class="btn-button engine-submit-btn">
								<span><?php _e("Save Plan",ET_DOMAIN);?></span><span class="icon" data-icon="+"></span>
							</button>
						</div>
					</div>
				</form>
			</div>

			<script type="text/template" id="template_edit_form">
				<form action="" class="edit-plan engine-payment-form">
					<input type="hidden" name="action" value="et-save-payment">
					<input type="hidden" name="et_action" value="et-save-payment">
					<input type="hidden" name="id" value="{{id }}">
					<div class="form payment-plan">
						<div class="form-item">
							<div class="label"><?php _e("Enter a name for your plan",ET_DOMAIN);?></div>
							<input class="bg-grey-input not-empty" name="title" type="text" value="{{title }}" />
						</div>
						<div class="form-item f-left-all clearfix">
							<div class="width33p">
								<div class="label"><?php _e("Price",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty is-number" name="price" type="text" value="{{ price }}"/> 
								<?php et_display_currency($selected_currency['label'], $selected_currency,' ','')?>
							</div>
							<div class="width33p">
								<div class="label"><?php _e("Availability",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty is-number" type="text" name="duration" value="{{ duration }}" /> 
								days
							</div>
							<div class="width33p">
								<div class="label"><?php _e("Jobs",ET_DOMAIN);?></div>
								<input class="bg-grey-input width50p not-empty is-number" type="text" name="quantity" value="{{ quantity }}" /> 
								<?php _e("jobs",ET_DOMAIN);?>
							</div>
						</div>
						<div class="form-item">
							<div class="label"><?php _e("Featured Job",ET_DOMAIN);?></div>
							<input type="checkbox" name="featured" value="1" <# if ( featured == 1 ) { #> checked="checked" <# } #>/> <?php _e("Jobs posted under this plan will be featured.",ET_DOMAIN);?>
						</div>
						<div class="submit">
							<button id="save_playment_plan" class="btn-button engine-submit-btn">
								<span><?php _e("Save Plan",ET_DOMAIN);?></span><span class="icon" data-icon="+"></span>
							</button>
							or <a href="#" class="cancel-edit">Cancel</a>
						</div>
					</div>
				</form>
			</script>
		</div>
	</div>