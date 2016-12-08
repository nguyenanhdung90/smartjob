<div id="step-1">
	<h1 class="title-resume">
		<?php _e("Create a resume", ET_DOMAIN); ?> <span class="step-number"><?php _e("Step <strong>1</strong> of <strong>2</strong>", ET_DOMAIN); ?></span>
	</h1>
	<form id="jobseeker_signup" data-ajax="false" class="form-signup">
		<div class="content-info  content-text">
			<label><?php _e("Your Username", ET_DOMAIN); ?></label>
			<div class="input-text-remind">
				<input type="text" required id="user_name" name="user_name" value=""  >
			</div>

		</div>

		<div class="content-info content-text">
			<label><?php _e("Your Full Name", ET_DOMAIN); ?></label>
			<div class="input-text-remind">
				<input type="text" required id="display_name" name="display_name" value="" >
			</div>

		</div>

		<div class="content-info content-text">
			<label><?php _e("Professional Title", ET_DOMAIN); ?></label>
            <span class="sub-content-text"><?php _e('e.g. "User Interface Designer"', ET_DOMAIN); ?></span>
			<div class="input-text-remind">
				<input autocomplete="true" type="text" required id="et_profession_title" name="et_profession_title" value="" placeholder="<?php _e('e.g. User Interface Design', ET_DOMAIN); ?>" >
			</div>

		</div>

        <div class="content-info  content-text">
			<label><?php _e("Location", ET_DOMAIN); ?></label>
			<div class="input-text-remind">
            	<span class="icon icon-location" data-icon="@"></span>
				<input type="text" name="et_location" id="et_location" title="Enter the location..." placeholder="Enter the location..." class="ui-input-text ui-body-c">
			</div>
		</div>

		<div class="content-info content-text">
			<label><?php _e("About You", ET_DOMAIN); ?></label>
			<div class="input-text-remind">
				<textarea type="text" required id="description" name="description"  ></textarea>
			</div>

		</div>

		<div class="content-info content-text">
			<label><?php _e("Email Address", ET_DOMAIN); ?></label>
			<div class="input-text-remind">
				<input type="email" required id="user_email" name="user_email" value="" placeholder="<?php _e("Email", ET_DOMAIN); ?>" >
			</div>

		</div>

		<div class="content-info content-text">
			<label><?php _e("Password", ET_DOMAIN); ?></label>
			<div class="input-text-remind">
				<input type="password" required id="user_pass" name="user_pass" value="" placeholder="<?php _e("Password", ET_DOMAIN); ?>" >
			</div>

		</div>

		<div class="content-info content-text">
			<label><?php _e("Retype Your Password", ET_DOMAIN); ?></label>
			<div class="input-text-remind">
				<input type="password" required id="password_again" name="password_again" value="" placeholder="<?php _e("Repeat password", ET_DOMAIN); ?>" >
			</div>

		</div>

		<?php do_action( 'je_mobile_captcha' ); ?>

		<div class="content-info content-info-last content-text">

			<div class="input-text-remind btn-custom-submit">
				<input type="submit" required id="emp_name" name="emp_name" value="<?php _e("Continue", ET_DOMAIN); ?>"  />
			</div>

		</div>
	</form>

</div>