<?php   global $user_ID;?>

<div id="step-2" class="form-signup" <?php if(!is_singular('resume') ) echo 'style="display:none;"' ; ?> >
    <input type="hidden" class="post_author" value="" />
    <input type="hidden" class="ID" value="" />
	<h1 class="title-resume">
		<?php _e("Create a resume", ET_DOMAIN); ?> <span class="step-number"><?php _e("Step <strong>2</strong> of <strong>2</strong>", ET_DOMAIN); ?></span>
	</h1>
	<div class="content-info content-text content-timeline et_education" data-resume="et_education"  >
		<h1 class=""><?php _e('EDUCATION', ET_DOMAIN) ?></h1>
        <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
        <div class="edu-container">
            <form class="education element"  >
        		<div class="input-text-remind">
                    <input type="text" class="name"  value="" placeholder="<?php _e('School name', ET_DOMAIN); ?>">
                </div>
                <div class="input-text-remind">
                    <input type="text" class="degree"  value="" placeholder="<?php _e('Degree', ET_DOMAIN); ?>">
                </div>
                <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
                <div class="date-select">
                <?php
                    JE_Helper::monthSelectBox('fromMonth' , false, array('class' => 'month fromMonth' ,  ) );
                    JE_Helper::yearSelectBox('fromYear' , false, array('class' => 'year fromYear' ,  ) );
                ?>
                </div>
                <div class="clear" style="clear:both; height:18px; overflow:hidden;"><?php _e("to", ET_DOMAIN); ?></div>
                <div class="date-select">
                <?php
                    JE_Helper::monthSelectBox('toMonth' , false, array('class' => 'month toMonth'  ) );
                    JE_Helper::yearSelectBox('toYear' , false, array('class' => 'year toYear' ,  ) );
                ?>
                </div>
                <div class="ui-checkbox signup">
                    <label for="checkbox-enhanced" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-checkbox-off">
                        <?php _e("I currently study here", ET_DOMAIN); ?>
                    </label>
                    <input type="checkbox" name="checkbox-enhanced" id="checkbox-enhanced" class="curr" data-enhanced="true">
                    <span class="icon icon-track" data-icon="#"></span>
                </div>
            </form>
        </div>
        <div class="input-text-remind btn-signup-add">
            <input type="submit" class="add_more_school" name="" value="<?php _e("Add another school", ET_DOMAIN); ?>"/>
        </div>
    </div>

    <div class="content-info content-text content-timeline et_experience">
		<h1 class=""><?php _e('Work Experience', ET_DOMAIN) ?></h1>
        <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
        <div class="exp-container">
            <form class="experience element">
        		<div class="input-text-remind">
                    <input type="text" required class="name"  value="" placeholder="<?php _e('Company name', ET_DOMAIN); ?>">
                </div>
                <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
                <div class="input-text-remind">
                    <input type="text" required class="position"  value="" placeholder="<?php _e('Position', ET_DOMAIN); ?>">
                </div>
                <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
                <div class="date-select">
                <?php
                    JE_Helper::monthSelectBox('fromMonth' , false, array('class' => 'month fromMonth' ) );
                    JE_Helper::yearSelectBox('fromYear' , false, array('class' => 'year fromYear' ) );
                ?>
                </div>
                <div class="clear" style="clear:both; height:18px; overflow:hidden;"><?php _e("to", ET_DOMAIN); ?></div>
                <div class="date-select">
                <?php
                    JE_Helper::monthSelectBox('toMonth' , false, array('class' => 'month toMonth') );
                    JE_Helper::yearSelectBox('toYear' , false, array('class' => 'year toYear'  ) );
                ?>
                </div>
                <div class="ui-checkbox signup">
                    <label for="checkbox-enhanced-2" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-checkbox-off">
                        <?php _e("I currently work here", ET_DOMAIN); ?>
                    </label>
                    <input type="checkbox" name="checkbox-enhanced-2" id="checkbox-enhanced-2" class="curr" data-enhanced="true">
                    <span class="icon icon-track" data-icon="#"></span>
                </div>
            </form>
        </div>
        <div class="input-text-remind btn-signup-add">
            <input type="submit" class="add_more_exp" value="<?php _e("Add another position", ET_DOMAIN); ?>"/> 
        </div>
    </div>
    <?php
        $position_tax = new JE_Jobseeker_Position ();
        $positions = $position_tax->get_terms_in_order();
        if(!empty($positions)) {
    ?>
	<div class="content-info content-text content-timeline category" data-resume="resume_category">

        <h1 class=""><?php echo $position_tax->get_title (); ?></h1>
        <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
        <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
        <div class="date-select option-signup">
             <?php JE_Helper::jobPositionSelectTemplate('position[]', false, array('class' => 'month' , 'attr' => array('multiple' => 'multiple', 'data-native-menu'=> 0) ) ); ?>
        </div>
	</div>
    <?php  }

        $available_tax  =   JE_TaxFactory::get_instance('available');
        $availables     =   $available_tax->get_terms_in_order(); 
        $colors         =   $available_tax->get_color();
        //$availables = get_terms( 'available' , array('hide_empty' => false) );
        if(!empty($availables)) {

    ?>
    <div class="content-info content-text content-timeline available" data-resume="available">
		<h1 class=""><?php echo  $available_tax->get_title () ?></h1>
		<?php foreach ($availables as $avail) { ?>
        	<div class="ui-checkbox signup">
                <label for="<?php echo $avail->slug; ?>" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-checkbox-off <?php echo isset($colors[$avail->term_id]) ? 'color-' . $colors[$avail->term_id] : '' ?>">
                    <?php echo $avail->name; ?>
                </label>
                <input class="" id="<?php echo $avail->slug; ?>" type="checkbox" name="" value="<?php echo $avail->slug ?>" data-enhanced="true">
            </div>
		<?php } ?>
	</div>
    <?php } ?>

    <div class="content-info content-text content-timeline skill-container" data-resume="skill">
        <h1 class=""><?php _e('Skills', ET_DOMAIN) ?></h1>
        <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>

        <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
        <div class="date-select option-signup">
            <input type="text" name="skill" value="" class="skill" />
        </div>

        <ul class="select-option-signup skill-list">
            <!-- <li><span class="icon icon-track" data-icon="#"></span><span class="text">PHP Developer</span></li>
            <li><span class="icon icon-track" data-icon="#"></span><span class="text">PHP Developer</span></li>
            <li><span class="icon icon-track" data-icon="#"></span><span class="text">PHP Developer</span></li> -->
        </ul>

    </div>
    <?php do_action('je_resume_add_fields'); ?>
	<div class="content-info content-info-last content-text">
        <div class="input-text-remind btn-custom-submit">
            <input type="submit" class="sign_up" value="<?php _e("SIGN UP AS A CREATIVE  ", ET_DOMAIN); ?>"  />
        </div>
    </div>

</div>