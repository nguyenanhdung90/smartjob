<?php 
global $user_ID,$current_user ;
$currency		=	ET_Payment::get_currency();

$apply_method		=	trim(et_get_user_field ($user_ID,'apply_method'));
$apply_email		=	trim(et_get_user_field ($user_ID,'apply_email'));
$applicant_detail	=	trim(et_get_user_field ($user_ID,'applicant_detail'));

$recent_location	=	et_get_user_field ($current_user->ID,'recent_job_location');

$request		=	wp_parse_args( $_REQUEST,
									array(
										'post_title' 			=> '' ,
										'post_content'			=> '',
										'apply_method'			=> ($apply_method != '') ? $apply_method : 'isapplywithprofile' ,
										'apply_email'			=> ($apply_email != '') ? $apply_email : $current_user->user_email ,
										'applicant_detail'		=> ($applicant_detail != '') ? $applicant_detail : '',
										'full_location' 		=>	isset($recent_location['full_location']) ? $recent_location['full_location'] : '' ,
										'location' 				=>	isset($recent_location['location']) ? $recent_location['location'] : '',
										'location_lat'			=>	isset($recent_location['location_lat']) ? $recent_location['location_lat'] : '',
										'location_lng' 			=>	isset($recent_location['location_lng']) ? $recent_location['location_lng'] : '',
										'job_type'				=>  array(''),
										'category'				=>  array(''),
										'job_package'			=> '',
										'display_name'			=> $current_user->display_name,
										//'display_name'			=> $current_user->display_name,
										'user_url' 				=> $current_user->user_url,
									)

								);
extract($request, EXTR_OVERWRITE );
?>


<div class="job-form" <?php if( !is_user_logged_in() ) echo "style='display: none'"; ?> >
	<form class="post-classifed-form" data-ajax="false" method="post" enctype="multipart/form-data" >
		<input type="hidden" value="<?php echo $user_ID; ?>" class="post_author" />

		<!-- tittle -->
		<div data-role="fieldcontain" class="post-new-job">
			<label for="post_title"><?php _e("JOB TITLE", ET_DOMAIN); ?>
				<span class="subtitle"><?php _e("Enter a short title for your job", ET_DOMAIN); ?></span>
			</label>
			<input type="text" class="job_field" name="post_title" id="post_title" value="<?php echo $post_title;  ?>" placeholder="<?php _e("Title", ET_DOMAIN); ?>" required />
		</div>

		<div data-role="fieldcontain" class="post-new-job">
			<label for="info">
				<?php _e("JOB DESCRIPTION", ET_DOMAIN); ?>
				<span class="subtitle"><?php _e("Describe your job in a few paragraphs", ET_DOMAIN); ?></span>
			</label>
	        <textarea name="post_content" class="job_field" id="post_content"><?php echo $post_content;  ?></textarea>
		</div>

		<div data-role="fieldcontain" class="post-new-job">
			<label for="full_location">
				<?php _e("LOCATION", ET_DOMAIN); ?>
				<span class="subtitle"><?php _e("Enter a city and country or leave it blank", ET_DOMAIN); ?></span>
			</label>
			<input type="text" class="job_field" name="full_location" id="full_location" value="<?php echo $full_location;  ?>" placeholder="<?php _e("e.g. Melbourne VIC", ET_DOMAIN); ?>"  />
			<input type="hidden" class="job_field" name="location_lat" id="location_lat" value="<?php echo $location_lat;  ?>" placeholder="<?php _e("e.g. Melbourne VIC", ET_DOMAIN); ?>"  />
			<input type="hidden" class="job_field" name="location_lng" id="location_lng" value="<?php echo $location_lng;  ?>" placeholder="<?php _e("e.g. Melbourne VIC", ET_DOMAIN); ?>"  />
		</div>

		<div data-role="fieldcontain" class="post-new-job job_type" data-ad="job_type">
	        <label for="day">
	        	<?php _e("CONTRACT TYPE", ET_DOMAIN); ?>
	        	<span class="subtitle"><?php _e("Select the correct type for your job", ET_DOMAIN); ?></span>
	        </label>
	        <?php
	        	je_dropdown_tax (	'job_type' ,
        							array( 'show_option_all' => __("Select your", ET_DOMAIN),
		        							'name' 		=> 'job_type[]',
		        							'id' 		=> 'job_type',
		        							'taxonomy' 	=> 'job_type' ,
		        							'hierarchical' => false,
		        							'selected'	=> $job_type[0],
		        							'hide_empty' => false,
		        							'attr' => array( 'data-native-menu' => true, 'required' => 'required' )
		        						)
        							);
			        		?>
	    </div>

	    <div data-role="fieldcontain" class="post-new-job job_category" data-ad="job_category">
	      	<label for="day"><?php _e("JOB CATEGORY", ET_DOMAIN); ?><span class="subtitle"><?php _e("Select a category for your job", ET_DOMAIN); ?></span></label>
	      	<?php 
	      		je_dropdown_tax (	'job_category' ,
	      							array( 	'show_option_all' => __("Select your", ET_DOMAIN),
			      							'attr' => array( 'data-native-menu' => true ,  'required' => 'required'
			      						) ,
			      							'name' => 'category[]',
			      							'id' => 'job_category',
			      							'selected'	=> $category[0],
			      							'taxonomy' => 'job_category' ,
			      							'hide_empty' => false,
			      							'hierarchical' => true
			      						)
			      					);
			    ?>
	    </div>

	    <div data-role="fieldcontain" class="post-new-job">
			<label for="dddđ">
				<?php _e("HOW TO APPLY", ET_DOMAIN); ?>
				<span class="subtitle"><?php _e("Select how you would want jobseekers to submit their applications", ET_DOMAIN); ?></span>
			</label>

			<input <?php checked('isapplywithprofile' , $apply_method); ?> type="radio" data-role="none" name="apply_method" id="email_apply" value="isapplywithprofile"  />
			<label style="display:inline-block;" for="email_apply"><?php _e("Send applications to this email address", ET_DOMAIN); ?></label>
	        <input placholder="<?php _e("Apply email", ET_DOMAIN); ?>" type="email" class="job_field" name="apply_email" id="apply_email" value="<?php echo $apply_email;  ?>"  required />

 			<br/>
			<input <?php checked('howtoapply' , $apply_method); ?> type="radio" data-role="none" name="apply_method" id="howtoapply" value="ishowtoapply" >
	        <label style="display:inline-block;" for="howtoapply"><?php _e("Must follow the application steps", ET_DOMAIN); ?></label>

	        <textarea placholder="<?php _e("Enter applicant details here", ET_DOMAIN); ?>" name="applicant_detail" class="job_field" id="applicant_detail"><?php echo $applicant_detail;  ?></textarea>	

		</div>

		<div data-role="fieldcontain" class="post-new-job">
			<label for="username"><?php _e("COMPANY NAME", ET_DOMAIN); ?><span class="subtitle"><?php _e("Enter your company name", ET_DOMAIN); ?></span></label>
			<input type="text" name="display_name"  value="<?php echo $display_name; ?>" placeholder="<?php _e("Company name", ET_DOMAIN); ?>" required />
		</div>

		<div data-role="fieldcontain" class="post-new-job">
			<label for="user_url"><?php _e("COMPANY WEBSITE", ET_DOMAIN); ?><span class="subtitle"><?php _e("Your company site address", ET_DOMAIN); ?></span></label>
			<input type="url" name="user_url" value="<?php echo $user_url; ?>" placeholder="<?php _e("Website url", ET_DOMAIN); ?>" required />
		</div>
		<?php
			do_action( 'je_mobile_job_post_form_fields' );
		?>

		<div class="ui-content plan-job">
		<?php

		$plans			=	et_get_payment_plans ();
		$package_data	=	!empty($user_ID ) ? et_get_purchased_quantity($user_ID) : array();

		$one_plan		=	0;
		if(count($plans) == 1 ) {
			$one_plan = 1;
		}

		if(!empty($plans)) { ?>
		  	<div data-role="fieldcontain" class="post-new-job">
		  		<label class="ui-input-text">
		  			<?php _e("Select a pricing plan", ET_DOMAIN); ?>
		  			<span class="subtitle"><?php _e("We offer different types of pricing plan.", ET_DOMAIN); ?></span>
		  		</label>
			    <?php foreach ($plans as $key => $plan) { 	?>
				    <label for="package-<?php echo $plan['ID']; ?>">
				    	<?php echo $plan['title']; ?> - <?php echo et_get_price_format ($plan['price'] , 'sup'); ?>
					    <span class="subtitle">
							<?php
								$number_of_post	=	$plan['quantity'];
								$mark			=	0;
								/**
								 * default mark if only have one plan
								*/
								if($one_plan) $mark	=	1;

								if($plan['quantity'] > 1 ) {
									if( isset($package_data[$key] ) && $package_data[$key] > 0 ) {
		              					/**
		              					 * print text when company has job left in package
		              					*/
		              					// $k  	= 1;
		              					$mark 	=  1;
		              					$number_of_post	=	$package_data[$key];
		              					echo '<span style="color:#0F5ED5">';
		              					if($number_of_post > 1 ) {

		              						printf(__("You purchased and have %d jobs left in this plan.", ET_DOMAIN) , $number_of_post);
		              					}
		              					else  {
		              						printf(__("You purchased and have %d job left in this plan.", ET_DOMAIN) , $number_of_post);
		              					}
		              					echo '</span>';

		              				}else {
		              					/**
		              					 * print normal text if company dont have job left in this package
		              					*/
		              					printf(__("This plan includes %d jobs.", ET_DOMAIN) , $number_of_post);
		              				}
									echo '<br/>';
								} 

								echo $plan['description'];
							?>
	                  	</span>
	                </label>

				    <input type="radio" <?php checked( 1, $mark ); ?> name="job_package" id="package-<?php echo $plan['ID']; ?>" value="<?php echo $plan['ID']; ?>">
			   	<?php } ?>
		  	</div>
		<?php } ?>
		<?php do_action( 'je_mobile_captcha' ); ?>
		<div data-role="fieldcontain" class="post-new-job">
			<input type="submit" value="<?php _e('Submit',ET_DOMAIN);?>" data-icon="check" data-iconpos="right" data-inline="true">
		</div>

		</div>
	</form>
</div>