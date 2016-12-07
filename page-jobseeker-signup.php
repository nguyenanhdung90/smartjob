<?php
/**
 * Template Name: Jobseeker Sign up
 */
$options	=	new JE_Resume_Options ();
if(!$options->get_resume_status() ) wp_redirect( home_url( ) );

global $user_ID, $current_user, $wp_query;

get_header();
$require_field	=	apply_filters( 'je_jobseeker_signup_require_field', array(
		'display_name'	=> 'required',
		'et_profession_title' => 'required',
		'user_email'	=>	'required',
		'et_location'	=> 'required',
		'description'	=> '',
	) );

?>

<div class="wrapper" id="page-signup" >
	<div class="main-center" >
		<div class="jse-signup title">
			<h2 class="font-quicksand"><?php _e("SIGN UP AS A PROFESSIONAL", ET_DOMAIN); ?></h2>
			<span><?php _e("Get noticed. Get hired.", ET_DOMAIN); ?></span>
		</div>
		<div class="jse-content">
			<div class="jse-center jse-signup-javascript">
				<span class="arrow-up"><span class="arrow-up-white"></span></span>
				<div class="jse-title bg-toggle-active clearfix">
					<div class="step-1 active">
						<div class="number"><span class="icon" data-icon="2"></span><span class="text">1</span></div>
						<div class="">
							<div><?php _e("Step One", ET_DOMAIN); ?></div>
							<div class="name"><?php _e("Create An Account", ET_DOMAIN); ?></div>
						</div>
					</div>
					<div class="step-1 complete hidden">
						<div class="number"><span class="icon" data-icon="2"></span></div>
						<div class="">
							<div><?php _e("Step One", ET_DOMAIN); ?></div>
							<div class="name"><?php _e("Create An Account", ET_DOMAIN); ?></div>
						</div>
					</div>
					<div class="step-2">
						<div class="number">2</div>
						<div class="">
							<div><?php _e("Step Two", ET_DOMAIN); ?></div>
							<div class="name"><?php _e("Create Your Resume", ET_DOMAIN); ?></div>
						</div>
					</div>
				</div>
				<div class="jse-signup-form signup-step1">
					<form id="signup">
						<?php
						$use_linkle		=	apply_filters( 'je_is_use_linkleIn_signup', true );
						$options		=	JE_Resume_Options::get_instance();
						$linkedin_api 	=	$options->get_linked_api();
						if($use_linkle && $linkedin_api != '') {
						 ?>
						<div class="jse-input">
							<label><?php _e("Do you want to import data from your LinkedIn account?", ET_DOMAIN); ?></label>
							<div class="area-linkedin">
								<button class="btn-linkedin" id="linkleIn">
									<span class="name"><?php _e("Load them up", ET_DOMAIN); ?></span>
									<span class="icon" data-icon="c"></span>
								</button>
							</div>
						</div>
					  	<?php } ?>
						<div class="jse-input">
							<label><?php _e("Your Username", ET_DOMAIN); ?></label>
							<div class="input-area">
								<input type="text" class="bg-default-input user_name required" id="user_name" name="user_name" value=""/>
							</div>
						</div>
						<?php do_action ('je_jobseeker_before_signup_form'); ?>
						<div class="jse-input">
							<label><?php _e("Your Full Name", ET_DOMAIN); ?></label>
							<div class="input-area">
								<input type="text" class="bg-default-input <?php echo $require_field['display_name'] ?>" id="display_name" name="display_name" value=""/>
							</div>
						</div>
						<div class="jse-input professional-title">
							<label><?php _e("Professional Title", ET_DOMAIN); ?></label>
							<div class="input-area">
								<input type="text" class="bg-default-input <?php echo $require_field['et_profession_title'] ?> " placeholder='<?php _e('e.g. "User Interface Designer"', ET_DOMAIN); ?>' id="et_profession_title" value="" name="et_profession_title" />
								<p>150</p>
							</div>
						</div>
						<div class="jse-input location-icon">
							<label><?php _e("Location", ET_DOMAIN); ?></label>
							<div class="input-area">
								<input type="text" class="bg-default-input <?php echo $require_field['et_location'] ?>" placeholder='<?php _e("Location", ET_DOMAIN); ?>' id="et_location" name="et_location" value="" />
								<span class="icon" data-icon="@"></span>
							</div>
						</div>
						<div class="jse-input">
							<label><?php _e("About You", ET_DOMAIN); ?></label>
							<textarea class="bg-default-input <?php echo $require_field['description'] ?>" id="description" name="description" ></textarea>
						</div>

						<?php do_action ('je_jobseeker_after_signup_form'); ?>

						<div class="jse-input">
							<label><?php _e("Email Address", ET_DOMAIN); ?></label>
							<div class="input-area">
								<input type="text" class="bg-default-input required email" name="user_email" id="user_email" value="" />
							</div>
						</div>
						<div class="jse-input">
							<label><?php _e("Password", ET_DOMAIN); ?></label>
							<div class="input-area">
								<input type="password" class="bg-default-input" id="user_pass" name="user_pass" />
							</div>
						</div>
						<div class="jse-input">
							<label><?php _e("Retype Your Password", ET_DOMAIN); ?></label>
							<div class="input-area">
								<input type="password" class="bg-default-input" id="password_again" name="password_again" />
							</div>
						</div>
						<?php do_action ('je_jobseeker_signup_form'); ?>
					</form>
				</div>
				<div class="jse-signup-profile signup-step2 hidden  ">
					<div class="module education" >
						<div class="title">
							<?php _e("Education", ET_DOMAIN); ?>
						</div>
						<div id="school_list" >
							<!-- list all user schools -->
						</div>
						<form id="form_education">
							<div class="" id="inline_edu">
								<!-- inline edit education will be render when document ready -->
							</div>
						</form>
						<div class="add-form-another">
							<button class="btn-add-another" rel="#edit_education" id="add_more_school"><?php _e("Add another school", ET_DOMAIN); ?></button>
						</div>
					</div>
					<div class="module experience">
						<div class="title">
							<?php _e("Work Experience", ET_DOMAIN); ?>
						</div>
						<div id="exp_list" class="">
							<!-- list exp -->
						</div>
						<form id="form_experience" >
							<div class="" id="inline_exper">
								<!-- inline edit exper -->
							</div>
						</form>
						<div class="add-form-another">
							<button id="add_more_experience" class="btn-add-another"><?php _e("Add another experience", ET_DOMAIN); ?></button>
						</div>
					</div>
					<?php
						$position_tax = new JE_Jobseeker_Position ();
						$positions = $position_tax->get_terms_in_order();
						if(!empty($positions)) {
					?>
					<div class="module position" data-resume="resume_category">
						<div class="title">
							<?php echo $position_tax->get_title (); ?>
							<?php //_e("Resume Categories", ET_DOMAIN); ?>
						</div>
						<form action="" id="form_resume_categories">
						<div class="inline-edit jobposition" style="display:block;">

								<div class="edu-form">

									<div class="jse-multi-select jobpos_select">
										<div class="select-style job-pos-sel btn-background border-radius">
											<?php JE_Helper::jobPositionSelectTemplate('position[]', false, array('job-position')); ?>
										</div>
									</div>
								</div>

						</div>
						<ul class="skill-list clearfix">
						</ul>
						</form>

					</div>
					<?php }
						$available_tax	=	JE_TaxFactory::get_instance('available');
						$availables		=	$available_tax->get_terms_in_order();
						$colors 		= 	$available_tax->get_color();
						//$availables = get_terms( 'available' , array('hide_empty' => false) );
						if(!empty($availables)) {
					?>
					<div class="module jobtype available" data-resume="available" >
						<div class="title">
							<?php echo  $available_tax->get_title () ?>
							<?php //_e("Available for", ET_DOMAIN); ?>
						</div>

						<div class="edu-form job-type">
							<?php
							// show the all the available
							foreach ($availables as $avail) { ?>
								<div class="jse-input">
									<div class="jse-checkbox">
										<input type="hidden" name="" value="0">
										<input id="<?php echo $avail->name ?>" type="checkbox" name="" value="<?php echo $avail->slug ?>" />
									</div>
									<div class="job-type <?php echo isset($colors[$avail->term_id]) ? 'color-' . $colors[$avail->term_id] : '' ?>">
										<span class="flag"></span>
										<label for="<?php echo $avail->name ?>" href="#"><?php echo $avail->name ?></label>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
					<?php } ?>

					<div class="module skill" data-resume="skill">
						<div class="title">
							<?php _e("Skills", ET_DOMAIN); ?>
						</div>
						<form action="" id="form_skills">
							<div id="inline_skills" class="edu-form skill auto-add">

								<?php /* foreach ($terms as $term) { ?>
									<div class="jse-input">
										<span><input type="text" class="bg-default-input auto-add-field skill-input" value="<?php echo $term->name ?>" /></span>
									</div>
								<?php } */ ?>
								<div class="jse-input">
									<span><input id="skill_input" type="text" class="bg-default-input skill-input" value="" placeholder="<?php _e("Type your skills", ET_DOMAIN); ?>" /></span>
									<?php _e('Press Enter to keep adding skills', ET_DOMAIN) ?>
								</div>

								<!-- <button class="btn-add-another" rel="" id="add_skill"><?php _e("Add +", ET_DOMAIN); ?></button> -->

							</div>
							<ul class="skill-list clearfix">

							</ul>
						</form>


					</div>

					<?php do_action( 'je_jobseeker_post_resume_form' ); ?>

				</div>
				<div class="jse-footer step-button signup-step1">
					<button class="btn-signup bg-btn-action signup"><?php _e("Continue <span>&raquo;</span>", ET_DOMAIN); ?></button>
				</div>
				<div class="jse-footer step-waiting hidden">
					<span class="name"><?php _e("WE ARE GOING TO THE NEXT STEP ...", ET_DOMAIN); ?></span>
				</div>
				<div class="jse-footer step-button signup-step2 hidden">
					<button class="btn-signup bg-btn-action"><?php _e("SIGN UP AS A PROFESSIONAL", ET_DOMAIN); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/template" id="resume_category_template">
	<div class="select-style job-pos-sel btn-background border-radius">
		<?php JE_Helper::jobPositionSelectTemplate('position[]', false, array('job-position')); ?>
	</div>
</script>
<?php get_footer(); ?>
