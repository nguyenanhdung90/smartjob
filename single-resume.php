<?php
global $current_user, $wp_query, $user_ID;

get_header();

if (have_posts()) {
	the_post();
	$jobseeker 			= get_userdata($post->post_author);
	$jobseeker 			= JE_Job_Seeker::convert_from_user($jobseeker);
	$resume 			= null;
	$authorise 			= (current_user_can( 'manage_options' ) || $current_user->ID == $jobseeker->ID) ? true : false;

	$post_status		=	$post->post_status;

	$resume_options	=	JE_Resume_Options::get_instance();
?>


<div id="profile">

<div class="heading-message message">

	<div class="main-center">
	<?php if ( $post_status != 'publish') {
		if($post_status == 'reject') {
			$msg	=	__('THIS RESUME IS REJECTED.', ET_DOMAIN);
			if($current_user->ID == $jobseeker->ID){ 
				$msg .= ' <span data-icon="1" class="icon"></span> <span  id="republish" class="btn-republish">';
				$msg .= __('PUBLISH AGAIN!',ET_DOMAIN);
				$msg .='</span>';
			}
		}

		else
			$msg	=	__('THIS RESUME IS WAITING FOR APPROVAL TO PUBLISH.', ET_DOMAIN);
	?>
		<div class="text">
			<?php echo $msg ?>
		</div>
	<?php } ?>
	</div>

</div>
<div class="heading">
	<div class="main-center main-single-reume">
		<div class="technical font-quicksand f-right job-controls">
			<?php if(current_user_can('manage_options' )) { ?>
			<div class="f-right" id="adminAction">
				<a href="#" class="color-active" id="approveResume" <?php if ($post_status == 'publish') echo 'style="display : none"'; ?>>
					<span data-icon="3" class="icon"></span>
					<?php _e( 'APPROVE', ET_DOMAIN ) ?>
				</a>

				<a rel="modal-box" id="rejectResume" href="#modal_reject_resume" class="color-pending" <?php if ($post_status == 'reject')  echo 'style="display : none"'; ?> >
					<span data-icon="*" class="icon"></span><?php _e( 'REJECT', ET_DOMAIN ) ?>
				</a>

			</div>
			<?php } ?>
		</div>
		<h1 class="title"><?php printf( __("Profile of %s",ET_DOMAIN), $jobseeker->display_name); ?></h1>
	</div>
</div>

<div class="wrapper jobseeker">
	<?php if( $user_ID || !$resume_options->et_resumes_priavcy ) { //check privacy hide resume detail  ?>
	<div  class="main-center clearfix padding-top30 jse-profile <?php if ($authorise) echo 'authorized' ?>">
		<?php
		/**
		 * Job Seeker Information
		 */
		?>


		<?php
		// get the resume
		global $post;
		$resume = JE_Resume::convert_from_post($post);
		$resume->author = $jobseeker->user_login;

		if( $jobseeker->et_contact ) {
			if(isset($_GET['action'])) unset($_GET['action']);
		}

		if ( !isset($_GET['action']) || $_GET['action'] != 'send_message' ){
		/**
		 * Resume Information
		 */
		$content	=	 $post->post_content;
		?>
		<div id="resume_profile" class="jse-account main-column jse-profile-main jse-signup-profile">

			<?php if($content != '' || $authorise ) { ?>
			<div class="module module-edit education info-resume">
				<div class="title">
					<?php _e('About me', ET_DOMAIN) ?>
					<div class="btn-edit"><a href="#" class="icon" data-icon="p">&nbsp;&nbsp;&nbsp;</a></div>
				</div>
				<div class="edu-content cnt" id="edu-content">
					<?php
						if( $content != '' || $current_user->ID != $jobseeker->ID ) the_content();
						else {
							echo "<div class='edu-module' >";
							_e("Oops! You're missing something here...", ET_DOMAIN); 
							echo '</div>';
						}
					?>
				</div>
				<?php if ($authorise) {  ?>
				<div class="inline-edit">
					<form action="" id="form_about">
						<div class="jse-input">
							<textarea class="bg-default-input content-about "><?php echo $post->post_content ?></textarea>
						</div>
						<div class="btn-save">
							<input class="save-edit button" type="submit" value="<?php _e('SAVE', ET_DOMAIN) ?>"> <a class="cancel-edit close" href="#"><?php _e('Cancel', ET_DOMAIN) ?><span data-icon="D" class="icon"></span><span class="line-bottom"></span></a>
						</div>
					</form>
				</div>
				<?php } // end if authorise?>
			</div>
			<?php
			} // end about me

			/**
			 *  Education
			 */
			if(!empty($resume->et_education) || $authorise ) {
			?>
			<div class="module module-edit education">
				<div class="title">
					<?php _e('Education', ET_DOMAIN); ?>
					<div class="btn-edit"><a href="#" class="icon" data-icon="p"  data-module="school">&nbsp;&nbsp;&nbsp;</a></div>
				</div>
				<div class="edu-module cnt">
					<?php
					if(!empty( $resume->et_education ) || $current_user->ID != $jobseeker->ID )
					foreach ((array)$resume->et_education as $edu) {
						if(!is_array($edu)) continue;
						if ( empty($edu['from']) && empty($edu['name']) ) continue;

					?>
						<div class="item">
							<div class="line"></div>
							<div class="dot"></div>
							<div class="content">
								<div class="year">
									<?php
									echo $edu['from']['display'] . ' - ' . $edu['to']['display'];
									// echo $edu['fromText'] . ' - ';
									// echo $edu['current'] ? "Now" : $edu['to']['display'];
									?></div>
								<div class="school"><?php echo $edu['highlight'] ?></div>
							</div>
						</div>
					<?php }
					else _e("Oops! You're missing something here...", ET_DOMAIN);
					//if(empty($resume->et_education)) _e('You have not add your eductioncation <a class=""', ET_DOMAIN);
					?>

				</div>
				<?php if ($authorise) { ?>
				<div id="edit_education" class="inline-edit">
					<form id="form_education" action="">
						<div id="inline_edu" class="edu-inline-wrap">

						</div>
						<div id="save_edu" class="edu-form btn-save">
							<input rel="#education_template" id="add_more_school" class="btn-add-another" type="button" value="<?php _e('Add another school', ET_DOMAIN); ?>">
							<input  rel="#edit_education" class="save-edit button" type="submit" value="<?php _e('SAVE' , ET_DOMAIN) ?>"> <a class="cancel-edit close" href="#"><?php _e('Cancel') ?><span data-icon="D" class="icon"></span><span class="line-bottom"></span></a>
						</div>
					</form>
				</div>
				<?php } // end if authorise ?>
			</div>
			<?php } // end education

			/**
			 * Experience
			*/
			if(!empty($resume->et_experience) || $authorise ) {
			?>

			<div class="module module-edit education">
				<div class="title">
					<?php _e('Work Experience', ET_DOMAIN); ?>
					<div class="btn-edit"><a href="#" class="icon" data-icon="p" data-module="work-exp">&nbsp;&nbsp;&nbsp;</a></div>
				</div>
				<div class="edu-module cnt">
					<?php
					if(!empty($resume->et_experience) || $current_user->ID != $jobseeker->ID)
					foreach ((array)$resume->et_experience as $exp) {
						if ( empty($exp['from']) && empty($exp['name']) ) continue;
					?>
						<div class="item">
							<div class="line"></div>
							<div class="dot"></div>
							<div class="content">
								<div class="year">
									<?php
									echo $exp['from']['display'] . ' - ' . $exp['to']['display'];
									?>
								</div>
								<div class="school"><?php echo $exp['highlight'] ?></div>
							</div>
						</div>
					<?php } else
					_e("Oops! You're missing something here...", ET_DOMAIN);
					?>
				</div>
				<?php if ($authorise) { ?>
				<div id="edit_experience" class="inline-edit">
					<form action="" id="form_experience">
						<div id="inline_exp"></div>
						<div class="edu-form btn-save">
							<input id="add_more_company" rel="#exp_template" class="btn-add-another" type="button" value="<?php _e('Add another company', ET_DOMAIN); ?>">
							<input rel="#edit_experience" class="save-edit button" type="submit" value="<?php _e('SAVE' , ET_DOMAIN) ?>"> <a class="cancel-edit close" href="#"><?php _e('Cancel', ET_DOMAIN) ?><span data-icon="D" class="icon"></span><span class="line-bottom"></span></a>
						</div>
					</form>
				</div>
				<?php } // end if authorise ?>
			</div>
			<?php } // end experience

			/**
			 * Resume category
			*/
			$terms = JE_Resume::get_resume_categories($post->ID);
			$job_pos = new JE_Jobseeker_Position;
			$positions = $job_pos->get_terms_in_order();
			if( (!empty($terms) || $authorise) && !empty($positions)  ) {
			?>
			<div class="module module-edit education">
				<div class="title">
				<?php

					$title_position  = $job_pos->get_title();
					echo $title_position;
				?>
					<?php //_e('Categories', ET_DOMAIN) ?>
					<div class="btn-edit"><a href="#" class="icon" data-icon="p">&nbsp;&nbsp;&nbsp;</a></div>
				</div>
				<div class="edu-skill cnt">

					<?php
					if(!empty($terms) || $current_user->ID != $jobseeker->ID )
					foreach ($terms as $term) { ?>
						<div class="item">
							<div class="content">
								<?php echo $term->name ?>
							</div>
						</div>
					<?php }
					 else _e("Oops! You're missing something here...", ET_DOMAIN);
					 ?>
				</div>
				<?php if ( $authorise  ) { ?>
				<div class="inline-edit jobposition">
					<form action="" id="form_resume_categories">
						<script type="text/data" id="data_resume_positions">
							<?php echo json_encode($terms); ?>
						</script>
						<div class="edu-form first">
							<div class="jse-multi-select jobpos_select">
								<div class="select-style job-pos-sel btn-background border-radius">
									<?php JE_Helper::jobPositionSelectTemplate('position[]', false, array('job-position')); ?>
								</div>
							</div>
						</div>
						<ul class="skill-list clearfix">
						</ul>

						<div class="edu-form btn-save">
							<input rel="#edit_education" class="save-edit button" type="submit" value="<?php _e('SAVE' , ET_DOMAIN) ?>"> <a class="cancel-edit close" href="#"><?php _e('Cancel' , ET_DOMAIN) ?><span data-icon="D" class="icon"></span><span class="line-bottom"></span></a>
						</div>
					</form>
				</div>
				<?php } // end if authorise Resume category ?>
			</div>
			<?php } // end resume category


			$available_tax  =   ET_TaxFactory::get_instance('available');
            $colors         =   $available_tax->get_color ();

            $availables = $available_tax->get_terms_in_order();

			$terms 		= JE_Resume::get_availables($post->ID);
			if( (!empty($terms) || $authorise ) && !empty($availables) ) {
			?>

			<div class="module module-edit education jobtype">
				<div class="title">
					<?php 
					//$job_vailable = new JE_Jobseeker_Available;
					$title_available  = $available_tax->get_title();
					echo $title_available;
					 ?>
					<?php //_e('Available for', ET_DOMAIN) ?>
					<div class="btn-edit"><a href="#" class="icon" data-icon="p">&nbsp;&nbsp;&nbsp;</a></div>
				</div>
				<div class="edu-skill job-type cnt">
					<?php

					$term_ids 	= array();
					if(!empty($terms) || $current_user->ID != $jobseeker->ID )
					foreach ($terms as $term) { $term_ids[] = $term->term_id; ?>
						<div class="item">
							<div class="job-type color-<?php echo $colors[$term->term_id] ?>" >
								<span class="flag"></span>
								<?php echo $term->name ?>
							</div>
						</div>
					<?php } else
						_e("Oops! You're missing something here...", ET_DOMAIN);
					?>
					<!-- <div class="item">
						<div class="job-type color-1">
							<span class="flag"></span>
							<a href="#">Fulltime</a>
						</div>
					</div> -->
				</div>
				<?php if ($authorise) {	?>
				<div class="inline-edit edu-form job-type">
					<form action="" id="form_available">
						<?php
						// show the all the available
						foreach ($availables as $avail) { $checked = in_array($avail->term_id, $term_ids) ? 'checked="checked"' : ''; ?>
							<div class="jse-input">
								<div class="jse-checkbox">
									<input id="available-<?php echo $avail->term_id; ?>" data-color="<?php echo $colors[$avail->term_id] ?>"  type="checkbox" name="" data-name="<?php echo $avail->name ?>" value="<?php echo $avail->slug ?>" <?php echo $checked ?>>
								</div>
								<div class="job-type color-<?php echo $colors[$avail->term_id] ?>">
									<span class="flag"></span>
									<label for="available-<?php echo $avail->term_id; ?>" ><?php echo $avail->name ?></label>
								</div>
							</div>
						<?php } ?>
						<div class="edu-form btn-save">
							<input class="save-edit button" type="submit" value="<?php _e('SAVE', ET_DOMAIN) ?>"> <a class="cancel-edit close" href="#"><?php _e('Cancel', ET_DOMAIN) ?><span data-icon="D" class="icon"></span><span class="line-bottom"></span></a>
						</div>
					</form>
				</div>
				<?php } // end if authorise available ?>
			</div>
			<?php } // end available

			$terms = JE_Resume::get_skills($post->ID);
			if(!empty($terms) || $authorise ) {

			?>
			<div class="module module-edit education">
				<div class="title">
					<?php _e('Skills', ET_DOMAIN) ?>
					<div class="btn-edit"><a href="#" class="icon" data-icon="p">&nbsp;&nbsp;&nbsp;</a></div>
				</div>

				<div class="edu-skill cnt">
					<?php
					if(!empty($terms) || $current_user->ID != $jobseeker->ID )
					foreach ($terms as $term) { ?>
						<div class="item">
							<div class="content">
								<?php echo $term->name ?>
							</div>
						</div>
					<?php } else {
						_e("Oops! You're missing something here...", ET_DOMAIN);
					} ?>
				</div>
				<?php if ( $authorise ) { ?>
				<div class="inline-edit skill">
					<form action="" id="form_skills">
						<script type="text/data" id="data_resume_skills">
							<?php echo json_encode($terms); ?>
						</script>
						<div id="inline_skills" class="auto-add">
							<div class="jse-input">
								<span>
									<input id="skill_input" type="text" class="bg-default-input skill-input" value="" placeholder="<?php _e("Type your skills", ET_DOMAIN); ?>" />	
								</span>
								<?php _e('Press Enter to keep adding skills', ET_DOMAIN) ?>
							</div>
							<!-- <button class="save-edit button" rel="" id="add_skill"><?php _e("Add +", ET_DOMAIN); ?></button> -->
						</div>
						<ul class="skill-list clearfix">
						</ul>

						<div class="edu-form btn-save">
							<input rel="#edit_education" class="save-edit button" type="submit" value="<?php _e('SAVE', ET_DOMAIN) ?>"> <a class="cancel-edit close" href="#"><?php _e('Cancel', ET_DOMAIN) ?><span data-icon="D" class="icon"></span><span class="line-bottom"></span></a>
						</div>
					</form>
				</div>
				<?php } // end if authorise skills?>
			</div>
			<?php } // end skill ?>
			<?php do_action( 'je_jobseeker_edit_resume_form', $post, $jobseeker , $authorise  ); ?>
		</div>
		<div class="widget-area second-column">
			<div class="widget sortable ui-sortable">
				<aside class="widget bg-grey-widget jobseeker-profile-widget">
					<?php if ( $user_ID == $post->post_author){ ?>
					<a title="<?php _e("Go to profile settings", ET_DOMAIN); ?>" href="<?php echo et_get_page_link('jobseeker-account') ?>" class="setting-profile">
						<?php _e("Profile settings", ET_DOMAIN); ?>  <span class="icon" data-icon="y"></span>
					</a>
					<?php } ?>
					<?php
					$avatar = et_get_resume_avatar($jobseeker->ID, 171);
					?>
					<div id="avatar_container" class="avatar-container">
						<input type="hidden" name="author" value="<?php echo $jobseeker->ID ?>">
						<span id="avatar_thumbnail"><?php echo $avatar ?></span>
						<?php if ($authorise) { ?>
							<button id="avatar_browse_button" class="btn-background thumb border-radius">
								<?php _e("Change Avatar", ET_DOMAIN); ?>
								<span class="icon" data-icon="p"></span>
							</button>
						<?php } // end if authorise ?>
					</div>
					<div class="information">
						<div class="info">
							<div class="display">
								<div class="name display">
									<span id="info_display_name" class="cnt">
										<?php echo $jobseeker->display_name ?>
									</span>
								</div>
								<div class="position display" id="info_title" rel="#title_edit">
									<span id="info_et_profession_title" class="cnt"><?php echo $jobseeker->et_profession_title ? $jobseeker->et_profession_title: __("No title", ET_DOMAIN)  ?></span>
								</div>
								<div class="location display">
									<span id="info_et_location" class="cnt"><?php echo $jobseeker->et_location ? $jobseeker->et_location: __("No Location", ET_DOMAIN);  ?></span>
								</div>

								<?php do_action( 'je_jobseeker_info_field' , $jobseeker ); ?>

								<?php if ($authorise) { ?>
								<a href="" class="toggle-edit">
									<span class="icon" data-icon="p"></span>
									<?php _e('Edit', ET_DOMAIN) ?>
								</a>
								<?php } ?>
							</div>
							<?php if ($authorise) { ?>
							<div class="inline-edit">
								<div class="item edit">
									<label for=""><?php _e('Full name', ET_DOMAIN) ?></label>
									<div id="display_name_edit" class="name-edit">
										<input type="text" class="bg-default-input" name="display_name" value="<?php echo $jobseeker->display_name ?>">
									</div>
								</div>
								<div class="item edit">
									<label for=""><?php _e('Profession title', ET_DOMAIN) ?></label>
									<div id="title_edit" class="title-edit">
										<input type="text" class="bg-default-input" name="et_profession_title" value="<?php echo $jobseeker->et_profession_title ?>">
									</div>
								</div>
								<div class="item edit">
									<label for=""><?php _e('Location', ET_DOMAIN) ?></label>
									<div id="title_edit" class="title-edit">
										<input type="text" class="bg-default-input" name="et_location" value="<?php echo $jobseeker->et_location ?>">
									</div>
								</div>


								<?php do_action( 'je_jobseeker_edit_info_field' , $jobseeker ); ?>

								<div class="item edit">
									<div class="btn-save">
										<input type="submit" class="save" value="<?php _e("SAVE", ET_DOMAIN); ?>" />
										<a href="#" class="toggle-edit"><?php _e('Cancel', ET_DOMAIN) ?><span data-icon="D" class="icon"></span><span class="line-bottom"></span></a>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<div class="social">
						<div class="info">
							<div class="display">
								<div id="profile_urls">
									<?php
									$urls = array(
										'linkedin' 	=> array(
											'class' => 'linkedin',
										 	'label' => 'Linkedin'
										 	),
										'twitter' 	=> array(
											'class' => 'twitter',
										 	'label' => 'Twitter'
										 	),
										'facebook' 	=> array(
											'class' => 'facebook',
										 	'label' => 'Facebook'
										 	),
										'gplus' 	=> array(
											'class' => 'google',
										 	'label' => __('Google Plus',ET_DOMAIN)
										 	),
										'user_url' 	=> array(
											'class' => 'personal',
										 	'label' => __('Personal Website',ET_DOMAIN)
										 	),
									);
									?>
									<?php
									foreach ($urls as $key => $value) {	?>
										<div id="url_<?php echo $key ?>" class="item view <?php if (empty($jobseeker->$key)) echo 'hide'  ?>">
											<a target="_blank" href="<?php echo $jobseeker->$key ?>" rel="nofollow">
												<span class="sicon <?php echo $value['class'] ?>"></span>
												<span class="name"><?php echo $value['label'] ?></span>
											</a>
										</div>
										<?php
									}
									?>
								</div>
								<?php if ($authorise){ ?>
									<div class="btn-save">
										<a href="#" class="toggle-edit"><?php _e("Edit URLs", ET_DOMAIN); ?></a>
										<!-- <input type="submit" value="" />  -->
									</div>
								<?php } // end if authorise ?>
							</div>
							<?php if ($authorise){ ?>
							<div class="inline-edit">
								<form id="urls_edit" action="">
									<?php
										foreach ($urls as $key => $value) {	?>
											<div class="item edit">
												<div class="jse-profile-input">
													<input type="text" name="<?php echo $key ?>" class="bg-default-input" placeholder="<?php echo $value['label'] ?>" value="<?php echo $jobseeker->$key ?>"/>
													<span class="sicon <?php echo $value['class'] ?>"></span>
												</div>
											</div>
											<?php
										} // end foreach
										?>
									<div class="btn-save">
										<input type="button" class="save" value="<?php _e("SAVE", ET_DOMAIN); ?>" />
										<a href="#" class="toggle-edit"><?php _e('Cancel', ET_DOMAIN) ?><span data-icon="D" class="icon"></span><span class="line-bottom"></span></a>
									</div>
								</form>
							</div>
							<?php } // end if authorise ?>
						</div>
					</div>
					<?php
					if ( $current_user->ID != $jobseeker->ID  &&  !$jobseeker->et_contact ){ ?>
					<div class="btn-jse-contact">
						<a href="<?php echo add_query_arg('action', 'send_message') ?>" class="bg-btn-action border-radius btn">
							<?php  _e("CONTACT", ET_DOMAIN) ?>
							<span class="icon" data-icon="M"></span>
						</a>
					</div>
					<?php } ?>
				</aside>
			</div>
		</div>

		<?php
		}  // end if isset $_GET actions
		else {
			/**
			 * Display a form for sending message to job seeker
			 */
		?>
			<div id="private_message" class="jse-account main-column jse-profile-main jse-signup-profile">
			<div class="module send-message">
				<div class="title"><?php printf(__("Message %s",ET_DOMAIN), $jobseeker->display_name); ?></div>
				<?php
				$sender = array(
					'name' => empty($current_user->display_name) ? "" : $current_user->display_name,
					'email' => empty($current_user->user_email) ? "" : $current_user->user_email
					);
				?>
				<form action="" id="jobseeker_send_message" method="post">
					<div class="jse-message">
						<input type="hidden" name="receive" value="<?php echo $resume->post_author ?>">
						<div class="jse-input">
							<label><?php _e('Your name', ET_DOMAIN) ?></label>
							<div class="inner input-area">
								<input type="text" class="bg-default-input" name="sender_name" value="<?php echo $sender['name'] ?>" required />
							</div>
						</div>
						<div class="jse-input">
							<label><?php _e('Email address', ET_DOMAIN) ?></label>
							<div class="inner input-area">
								<input type="text" class="bg-default-input email" required="email" name="sender_email" value="<?php echo $sender['email'] ?>"/>
							</div>
						</div>
						<div class="jse-input jse-textarea">
							<label><?php _e('Message', ET_DOMAIN) ?></label>
							<div class="inner input-area">
								<textarea class="bg-default-input" name="message"></textarea>
							</div>
						</div>
						<?php do_action ('je_contact_jobseeker_form' , $jobseeker); ?>
						<div class="jse-submit">
							<input class="bg-btn-action border-radius" type="submit" value="<?php _e('Send Message', ET_DOMAIN) ?>"/>&nbsp;&nbsp;

							<a id="cancel_form" class="button-cancel" href="<?php echo remove_query_arg( 'action' ) ?>"><?php _e('Cancel', ET_DOMAIN) ?> <span data-icon="D" class="icon"></span><span class="line-bottom"></span></a>
						</div>
						<!-- <div class="alert alert-warning">
							Your message has been sent successfully!
						</div>
						<div class="alert alert-error">
							Your message has been sent successfully!
						</div> -->
					</div>
				</form>
			</div>
		</div>
		<?php } ?>
	</div>
	<?php } // end privacy ?>
</div>
</div>
<?php } // end if have posts?>

<script type="text/data" id="data_resume">
<?php echo json_encode($resume) ?>
</script>
<script type="text/data" id="data_jobseeker">
<?php echo json_encode($jobseeker)?>
</script>
<script type="text/template" id="template_edu_item">
	<div class="item">
		<div class="line"></div>
		<div class="dot"></div>
		<div class="content">
			<div class="year">
				{{ from.display }} - {{ to.display }}
			</div>
			<div class="school">{{ highlight }}</div>
		</div>
	</div>
</script>
<script type="text/template" id="template_exp_item">
	<div class="item">
		<div class="line"></div>
		<div class="dot"></div>
		<div class="content">
			<div class="year">
				{{ from.display }} - {{ to.display }}
			</div>
			<div class="school">{{ highlight }}</div>
		</div>
	</div>
</script>
<?php get_footer(); ?>