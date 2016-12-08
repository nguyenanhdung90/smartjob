<?php
/**
 * add default menu item
 */
/* function adding_resume_menu_items($items){
	$items[] = array(
		'id' 				=> 'resume-menu',
		'href' 				=> get_post_type_archive_link( 'resume' ),
		'checking_callback'	=> 'et_is_resume_menu',
		'label' 			=> __('RESUMES', ET_DOMAIN),
		'link_attr' 		=> array('title' => __('Resumes', ET_DOMAIN))
	);
	return $items;
}
add_filter('default_menu_items', 'adding_resume_menu_items'); */

// return if current page relates to resume
function et_is_resume_menu(){
	return is_singular( 'resume' ) || is_post_type_archive( 'resume' ) || is_page_template('page-jobseeker-signup.php') || is_page_template('page-upgrade-account.php');
}

/**
 * returning avatar of jobseeker
 */
function et_get_resume_avatar($id_or_email = false, $size = 150){
	global $current_user;

	// setup size
	if ( !is_array($size) )
		$size_arr = array((int)$size, (int)$size);
	else if (count($size) >= 2) {
		$values = array_values($size);
		$size_arr = array($values[0], $values[1]);
	} else {
		$size_arr = array(150, 150);
	}

	$avatar = '';

	if ($id_or_email || $current_user->ID){
		if (is_email( $id_or_email )){
			$user = get_user_by( 'email', $id_or_email );
			$id_or_email = $user->user_email;
		}

		$id = $id_or_email ? $id_or_email : $current_user->ID;
		$accessible_list	=	JE_Job_Seeker::get_accessible_list($id);
		if(!current_user_can( 'manage_options' ) &&  get_user_meta( $id, 'et_privacy', true ) == 'confidential' && $current_user->ID != $id && !in_array($current_user->ID, $accessible_list)) {
			$avatar = '<img src="http://0.gravatar.com/avatar/e1bd0cb546e6bddfa006a222af53d483?s=171&d=http%3A%2F%2F0.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D171&r=G" width="' . $size_arr[0] . '" height="' . $size_arr[1] . '">';
		} else {
			$avatar_meta = JE_Job_Seeker::get_meta($id, 'et_avatar');
			if ( empty($avatar_meta['thumbnail']) || empty($avatar_meta['thumbnail'][0]) )
				$avatar = get_avatar( $id_or_email, $size );
			else {
				$avatar = '<img src="' . str_ireplace(array('http:', 'https:'), '', $avatar_meta['thumbnail'][0])  . '" width="' . $size_arr[0] . '" height="' . $size_arr[1] . '">';
			}
		}

		return $avatar;
	} else {
		return false;
	}
}

/**
 * Print the reject job modal template
 * @since 1.0
 */
function et_template_modal_reject_resume(){
	?>
	<div class="modal-job" id="modal-reject-job">
		<div class="edit-job-inner">
			<div class="title-white">
				<h5 id="job_title"></h5>
				<span id="company_name"></span>
			</div>
			<form class="modal-form">
				<div class="content">
					<div class="toggle-content login clearfix">
						<div class="form">
							<div class="form-item no-padding">
								<div class="label">
									<div class="f-right"><strong><?php _e('Send a message to this jobseeker', ET_DOMAIN);?></strong></div>
									<h6><?php _e('Why do you reject this resume?', ET_DOMAIN);?></h6>
								</div>
								<div class="">
									<textarea name="reason" class="bg-default-input reject-reason mini"></textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="footer font-quicksand">
					<div class="f-right cancel"><a class="cancel-modal" href="#"><?php _e('Cancel', ET_DOMAIN) ?> <span class="icon" data-icon="D"></span></a></div>
					<div class="modal-btn-reject">
						<input type="button" id="btn-reject" class="bg-btn-action border-radius" value="<?php _e('Reject', ET_DOMAIN);?>" name="reject" />
					</div>					
				</div>
			</form>
		</div>
		<div class="modal-close"></div>
	</div>
	<?php
}

/**
 * Javascript templates are use for backbone views
 */
function je_eduction_template() {
	$output	=	apply_filters( 'je_eduction_template', '' );
	if($output != '') {
		echo $output;
		return;
	}
	if(current_user_can('manage_options')) {
		echo et_template_modal_reject_resume();
	}
?>
	<script type="text/template" id="edit_skill_item">
		<a href="#" class="delete">
			<span data-icon="#" class="icon"></span>
		</a>
		<input type="hidden" name="value[]" value="{{ name }}">
		{{ name }}
	</script>
	<script type="text/template" id="edit_position_item">
		<a href="#" class="delete">
			<span data-icon="#" class="icon"></span>
		</a>
		<input type="hidden" name="value[]" value="{{ term_id }}">
		{{name}}
	</script>
	<script type="text/template" id="resume_list_item">

		<div class="thumb">
			<a href="{{permalink}}" class="resume-title">
				<?php echo '<img src="{{ jobseeker_data.et_avatar.thumbnail[0] }}"> ' ; ?>
			</a>
		</div>
		<div class="content">
			<h6 class="title">
				<a href="{{ permalink }}" class="title resume-title" title="{{ post_title }} ">{{ jobseeker_data.display_name }}</a>
			 	<a href="#" class="professtional">{{ et_profession_title }}</a>
			</h6>
			<div class="desc f-left-all">
				<div>
					<span class="icon" data-icon="@"></span>
					<# if (et_location) { #> {{ et_location }} <# } else { #>  <?php _e("No location", ET_DOMAIN); ?> <# } #>
				</div>
				<div class="link-website">
					<span class="icon" data-icon="G"></span>
					<# if (et_url) { #>
					<a rel="nofollow" href="{{ et_url }}" target="_blank" rel="nofollow" class="website"> {{ et_url }}  </a>
					<# } else { #> <span> <?php _e("No link specified", ET_DOMAIN); ?> </span> <# } #>
				</div>
			</div>
		</div>
	</script>
	<script type="text/template" id="position_select_template">
		<div class="select-style job-pos-sel btn-background border-radius">
			<?php 
				JE_Helper::jobPositionSelectTemplate('position[]', false, array('job-position'));
			?>
		</div>
	</script>
	<script type="text/template" id="education_template">
		<div class="edu-form">
			<div class="jse-input">
				<input type="text" class="bg-default-input name" placeholder="<?php _e("School name", ET_DOMAIN); ?>" value=""/> 
			</div>
			<div class="jse-input">
				<input type="text" class="bg-default-input degree" placeholder="<?php _e("Degree", ET_DOMAIN); ?>" value=""/> 
			</div>
			<div class="jse-multi-select">
				<div class="jse-select-form">
					<div class="select-style select-from-month btn-background border-radius">
						<?php 
						JE_Helper::monthSelectBox('fromMonth');
						?>
					</div>
					<div class="select-style select-from-year btn-background border-radius">
						<?php 
						JE_Helper::yearSelectBox('fromYear');
						?>
					</div>
					<div class="to"><div class="text-to">to</div></div>
					<div class="select-style select-to-month btn-background border-radius">
						<?php 
						JE_Helper::monthSelectBox('toMonth');
						?>
					</div>
					<div class="select-style select-to-year btn-background border-radius">
						<?php 
						JE_Helper::yearSelectBox('toYear');
						?>
					</div>
				</div>
				<div class="sub-row">
					<div class="msg-container">
						<span  class="icon error" data-icon="!"></span>
					</div>
					<div class="current-check">
						<input type="checkbox" class="curr" name="current" value=""/>
						<span class="check-current" ><?php _e('I currently study here', ET_DOMAIN) ?></span>
					</div>
				</div>
				<div class="sub-row">
					<a class="delete-item" href="#"><?php _e("Remove this school", ET_DOMAIN); ?> <span class="icon" data-icon="#"></span></a>
				</div>
			</div>
		</div>
	</script>
	<script type="text/template" id="exp_template">
		<div class="edu-form">
			<div class="jse-input">
				<input type="text" class="bg-default-input name" placeholder="<?php _e("Company Name", ET_DOMAIN); ?>" value=""/> 
			</div>
			<div class="jse-input">
				<input type="text" class="bg-default-input position" placeholder="<?php _e("Position", ET_DOMAIN); ?>" value=""/> 
			</div>
			<div class="jse-multi-select">
				<div class="jse-select-form">
					<div class="select-style select-from-month btn-background border-radius">
						<?php 
						JE_Helper::monthSelectBox('fromMonth');
						?>
					</div>
					<div class="select-style select-from-year btn-background border-radius">
						<?php 
						JE_Helper::yearSelectBox('fromYear');
						?>
					</div>
					<div class="to"><div class="text-to">to</div></div>
					<div class="select-style select-to-month btn-background border-radius">
						<?php 
						JE_Helper::monthSelectBox('toMonth');
						?>
					</div>
					<div class="select-style select-to-year btn-background border-radius">
						<?php 
						JE_Helper::yearSelectBox('toYear');
						?>
					</div>
				</div>
				<div class="sub-row">
					<div class="msg-container">
						<span  class="icon error" data-icon="!"></span>
					</div>
					<div class="current-check">
						<input type="checkbox" class="curr" name="current" value=""/>
						<span class="check-current" ><?php _e('I currently work here', ET_DOMAIN) ?></span>
					</div>
				</div>
				<div class="sub-row">
					<a class="delete-item" href="#"><?php _e("Remove this position", ET_DOMAIN); ?> <span class="icon" data-icon="#"></span></a>
				</div>
			</div>
		</div>
	</script>
	<script type="text/data" id="education_view">
		<div class="jse-input">
			<input type="text" class="bg-default-input name" placeholder="<?php _e("School name", ET_DOMAIN); ?>" value="{{ name }}" /> 
		</div>
		<div class="jse-input">
			<input type="text" class="bg-default-input degree" placeholder="<?php _e("Degree", ET_DOMAIN); ?>" value="{{degree }}" /> 
		</div>
		<div class="jse-multi-select">
			<div class="jse-select-form">
				<div class="select-style select-from-month btn-background border-radius">
					<select class="select-drop fromMonth" name="fromMonth">
						<option value=""><?php _e("Month", ET_DOMAIN); ?></option>
						<option value="1" <# if ( from.month == 1 ) { #> selected="selected" <# } #>>1</option>
						<option value="2" <# if ( from.month == 2 ) { #> selected="selected" <# } #>>2</option>
						<option value="3" <# if ( from.month == 3 ) { #> selected="selected" <# } #>>3</option>
						<option value="4" <# if ( from.month == 4 ) { #> selected="selected" <# } #>>4</option>
						<option value="5" <# if ( from.month == 5 ) { #> selected="selected" <# } #>>5</option>
						<option value="6" <# if ( from.month == 6 ) { #> selected="selected" <# } #>>6</option>
						<option value="7" <# if ( from.month == 7 ) { #> selected="selected" <# } #>>7</option>
						<option value="8" <# if ( from.month == 8 ) { #> selected="selected" <# } #>>8</option>
						<option value="9" <# if ( from.month == 9 ) { #> selected="selected" <# } #>>9</option>
						<option value="10" <# if ( from.month == 10 ) { #> selected="selected" <# } #>>10</option>
						<option value="11" <# if ( from.month == 11 ) { #> selected="selected" <# } #>>11</option>
						<option value="12" <# if ( from.month == 12 ) { #> selected="selected" <# } #>>12</option>
					</select>
				</div>
				<div class="select-style select-from-year btn-background border-radius">
					<?php JE_Helper::yearSelectBoxTemplate('fromYear', 'from.year', array('class' => 'fromYear')); ?>
				</div>
				<div class="to"><div class="text-to"><?php _e("to", ET_DOMAIN); ?></div></div>
				<div class="select-style select-to-month btn-background border-radius">
					<select name="toMonth" class="toMonth">
						<option value=""><?php _e("Month", ET_DOMAIN); ?></option>
						<option value="1" <# if ( to.month == 1 ) { #> selected="selected" <# } #>>1</option>
						<option value="2" <# if ( to.month == 2 ) { #> selected="selected" <# } #>>2</option>
						<option value="3" <# if ( to.month == 3 ) { #> selected="selected" <# } #>>3</option>
						<option value="4" <# if ( to.month == 4 ) { #> selected="selected" <# } #>>4</option>
						<option value="5" <# if ( to.month == 5 ) { #> selected="selected" <#} #>>5</option>
						<option value="6" <# if ( to.month == 6 ) { #> selected="selected" <# } #>>6</option>
						<option value="7" <# if ( to.month == 7 ) { #> selected="selected" <# } #>>7</option>
						<option value="8" <# if ( to.month == 8 ) { #> selected="selected" <# } #>>8</option>
						<option value="9" <# if ( to.month == 9 ) { #> selected="selected" <# } #>>9</option>
						<option value="10" <# if ( to.month == 10 ) { #> selected="selected" <# } #>>10</option>
						<option value="11" <# if ( to.month == 11 ) { #> selected="selected" <# } #>>11</option>
						<option value="12" <# if ( to.month == 12 ) { #> selected="selected" <# } #>>12</option>
					</select>
				</div>
				<div class="select-style select-to-year btn-background border-radius">
					<?php JE_Helper::yearSelectBoxTemplate('toYear', 'to.year', array('class' => 'toYear')); ?>
				</div>
			</div>
			<div class="sub-row">
				<div class="msg-container">
					<span class="icon error" data-icon="!"></span>
				</div>
				<div class="current-check">
					<input type="checkbox" class="curr" name="current" <# if (current == 1) { #> checked="checked" <#}#> value=""/>
					<span class="check-current" ><?php _e('I currently study here', ET_DOMAIN) ?> </span>
				</div>
			</div>
			<div class="sub-row">
				<a class="delete-item" href="#"><?php _e("Remove this school", ET_DOMAIN); ?> <span class="icon" data-icon="#"></span></a>
			</div>
		</div>
	</script>

	<script type="text/data" id="experience_view">
		<div class="jse-input">
			<input type="text" class="bg-default-input name" placeholder="<?php _e("Company name", ET_DOMAIN); ?>" value="{{ name }}" /> 
		</div>
		<div class="jse-input">
			<input type="text" class="bg-default-input position" placeholder="<?php _e("Position", ET_DOMAIN); ?>" value="{{ position }}" /> 
		</div>
		<div class="jse-multi-select">
			<div class="jse-select-form">
				<div class="select-style select-from-month btn-background border-radius">
					<select class="select-drop fromMonth" name="fromMonth">
						<option value=""><?php _e("Month", ET_DOMAIN); ?></option>
						<option value="1" <# if ( from.month == 1 ) { #> selected="selected" <# } #>>1</option>
						<option value="2" <# if ( from.month == 2 ) { #> selected="selected" <# } #>>2</option>
						<option value="3" <# if ( from.month == 3 ) { #> selected="selected" <# } #>>3</option>
						<option value="4" <# if ( from.month == 4 ) { #> selected="selected" <# } #>>4</option>
						<option value="5" <# if ( from.month == 5 ) { #> selected="selected" <# } #>>5</option>
						<option value="6" <# if ( from.month == 6 ) { #> selected="selected" <# } #>>6</option>
						<option value="7" <# if ( from.month == 7 ) { #> selected="selected" <# } #>>7</option>
						<option value="8" <# if ( from.month == 8 ) { #> selected="selected" <# } #>>8</option>
						<option value="9" <# if ( from.month == 9 ) { #> selected="selected" <# } #>>9</option>
						<option value="10" <# if ( from.month == 10 ) { #> selected="selected" <# } #>>10</option>
						<option value="11" <# if ( from.month == 11 ) { #> selected="selected" <# } #>>11</option>
						<option value="12" <# if ( from.month == 12 ) { #> selected="selected" <# } #>>12</option>
					</select>
				</div>
				<div class="select-style select-from-year btn-background border-radius">
					<?php JE_Helper::yearSelectBoxTemplate('fromYear', 'from.year', array('class' => 'fromYear')); ?>
				</div>
				<div class="to"><div class="text-to"><?php _e("to", ET_DOMAIN); ?></div></div>
				<div class="select-style select-to-month btn-background border-radius">
					<select name="toMonth" class="toMonth">
						<option value=""><?php _e("Month", ET_DOMAIN); ?></option>
						<option value="1" <# if ( to.month == 1 ) { #> selected="selected" <# } #>>1</option>
						<option value="2" <# if ( to.month == 2 ) { #> selected="selected" <# } #>>2</option>
						<option value="3" <# if ( to.month == 3 ) { #> selected="selected" <# } #>>3</option>
						<option value="4" <# if ( to.month == 4 ) { #> selected="selected" <# } #>>4</option>
						<option value="5" <# if ( to.month == 5 ) { #> selected="selected" <# } #>>5</option>
						<option value="6" <# if ( to.month == 6 ) { #> selected="selected" <# } #>>6</option>
						<option value="7" <# if ( to.month == 7 ) { #> selected="selected" <# } #>>7</option>
						<option value="8" <# if ( to.month == 8 ) { #> selected="selected" <# } #>>8</option>
						<option value="9" <# if ( to.month == 9 ) { #> selected="selected" <# } #>>9</option>
						<option value="10" <# if ( to.month == 10 ) { #> selected="selected" <# } #>>10</option>
						<option value="11" <# if ( to.month == 11 ) { #> selected="selected" <# } #>>11</option>
						<option value="12" <# if ( to.month == 12 ) { #> selected="selected" <# } #>>12</option>
					</select>
				</div>
				<div class="select-style select-to-year btn-background border-radius">
					<?php JE_Helper::yearSelectBoxTemplate('toYear', 'to.year', array('class' => 'toYear')); ?>
				</div>
			</div>
			<div class="sub-row">
				<div class="msg-container">
					<span class="icon error" data-icon="!"></span>
				</div>
				<div class="current-check">
					<input type="checkbox" class="curr" name="current" <# if (current == 1) { #> checked="checked" <# } #> value=""/>
					<span class="check-current" ><?php _e('I currently work here', ET_DOMAIN) ?> </span>
				</div>
			</div>
			<div class="sub-row">
				<a class="delete-item" href="#"><?php _e("Remove this position", ET_DOMAIN); ?> <span class="icon" data-icon="#"></span></a>
			</div>
		</div>
	</script>
<?php
}

if(!function_exists('je_block_resume_message')) {
	function je_block_resume_message () {
		$redirect_link	=	 et_get_page_link (array(
											'page_type' 	=> 'upgrade-account' , 
											'post_title' 	=> __("Upgrade account", ET_DOMAIN),
											'post_content'	=> __("Jobseeker profile is not free to view, you should upgrade your account to access resume profile.", ET_DOMAIN)
										) ) ;
	?>
		<div class="modal-job modal-login" id="modal_popup">
			<div class="edit-job-inner">
			<form class="modal-form" >
				<div class="content">
					 
					<div class="form-item">
						<div class="label">
							<h6><?php _e('You need to have a premium account to view this resume.', ET_DOMAIN);?></h6>
						</div>	
						<ul class="pop">
							<li><?php _e("Already have one?", ET_DOMAIN); ?> <a href="#" class="open-login"><?php _e("Login here!", ET_DOMAIN); ?></a></li>
							<li><?php _e("or", ET_DOMAIN); ?> <a href="<?php echo $redirect_link ?>" > <?php _e("Create an account now.", ET_DOMAIN); ?> </a> </li>
						</ul>
					</div>			
				</div>
				
			</form>
			</div>
			<div class="modal-close"></div>
		</div>
	<?php 
	}
}