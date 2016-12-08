<?php 
    global $user_ID , $resume, $jobseeker;
?>

<div class="form-signup" >
    <input type="hidden" class="post_author" value="<?php echo $jobseeker->ID; ?>" />
    <input type="hidden" class="ID" value="<?php echo $resume->ID; ?>" />
	<h1 class="title-resume">
		<?php _e("Edit your resume", ET_DOMAIN); ?> 
        <span class="step-number"><a href="<?php the_permalink(); ?>" ><?php _e("Done", ET_DOMAIN); ?> </a></span>
	</h1>

    <div data-role="collapsible" data-collapsed="false" class="edit-form-resume" >
        <h6><?php _e("Your Profile", ET_DOMAIN); ?></h6>

        <form id="jobseeker_signup" data-ajax="false" class="form-signup">

            <div class="content-info content-text ">
                <label><?php _e("Your Full Name", ET_DOMAIN); ?></label>
                <div class="input-text-remind">
                    <input type="text" required id="display_name" name="display_name" value="<?php echo $jobseeker->display_name; ?>" >
                </div>

            </div>

            <div class="content-info content-text">
                <label><?php _e("Professional Title", ET_DOMAIN); ?></label>
                <div class="input-text-remind">
                    <input autocomplete="true" type="text" required id="et_profession_title" name="et_profession_title" value="<?php echo $jobseeker->et_profession_title ?>" placeholder="<?php _e('e.g. User Interface Design', ET_DOMAIN); ?>" >
                </div>

            </div>

            <div class="content-info  content-text">
                <label><?php _e("Location", ET_DOMAIN); ?></label>
                <div class="input-text-remind">
                    <span class="icon icon-location" data-icon="@"></span>
                    <input type="text" value="<?php echo $jobseeker->et_location; ?>" name="et_location" id="et_location" title="Enter the location..." placeholder="Enter the location..." class="ui-input-text ui-body-c">
                </div>
            </div>

            <div class="content-info content-info-last content-text btn-custom-submit">

                <div class="input-text-remind">
                    <input type="submit" required id="emp_name" name="emp_name" value="<?php _e("Save", ET_DOMAIN); ?>"  />
                </div>

            </div>
        </form>
    </div>
    <div data-role="collapsible" class="edit-form-resume" >
        <h6><?php _e("Your resume", ET_DOMAIN); ?></h6>
        <div class="content-info content-text textarea">
            <label><?php _e("About You", ET_DOMAIN); ?></label>
            <div class="input-text-remind">
                <textarea type="text" required id="post_content" name="post_content" data-resume="post_content" ><?php echo $resume->post_content; ?></textarea>
            </div>

        </div>
    	<div class="content-info content-text content-timeline et_education" data-resume="et_education"  >
    		<h1 class=""><?php _e('EDUCATION', ET_DOMAIN) ?></h1>
            <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
            <div class="edu-container">
                <form class="education element"  >
                <?php
                if (!empty($resume->et_education)) {
                    foreach ($resume->et_education as $key => $item) {
                ?>
                		<div class="input-text-remind">
                            <input type="text" class="name"  value="<?php echo $item['name'] ?>" placeholder="<?php _e('School name', ET_DOMAIN); ?>">
                        </div>
                        <div class="input-text-remind">
                            <input type="text" class="degree"  value="<?php echo $item['degree'] ?>" placeholder="<?php _e('Degree', ET_DOMAIN); ?>">
                        </div>
                        <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
                        <div class="date-select">
                        <?php
                            JE_Helper::monthSelectBox('fromMonth' , $item['from']['month'], array('class' => 'month fromMonth' ,  ) );
                            JE_Helper::yearSelectBox('fromYear' , $item['from']['year'], array('class' => 'year fromYear' ,  ) );
                        ?>
                        </div>
                        <div class="clear" style="clear:both; height:18px; overflow:hidden;"><?php _e("to", ET_DOMAIN); ?></div>
                        <div class="date-select">
                        <?php
                            JE_Helper::monthSelectBox('toMonth' ,$item['to']['month'], array('class' => 'month toMonth'  ) );
                            JE_Helper::yearSelectBox('toYear' , $item['to']['year'], array('class' => 'year toYear' ,  ) );
                        ?>
                        </div>
                        <div class="ui-checkbox signup">
                            <label for="checkbox-enhanced-<?php echo $key ?>" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-checkbox-off">
                                <?php _e("I currently study here", ET_DOMAIN); ?>
                            </label>
                            <input <?php checked( 1, $item['current'] ); ?> type="checkbox" name="checkbox-enhanced-<?php echo $key ?>" id="checkbox-enhanced-<?php echo $key ?>" class="curr" data-enhanced="true">
                            <span class="icon icon-track" data-icon="#"></span>
                        </div>
                <?php
                    }
                }
                ?>
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
                <?php 
                if (!empty($resume->et_experience)) { 
                    foreach ($resume->et_experience as $key => $item) {
                ?>
            		<div class="input-text-remind">
                        <input type="text" required class="name"  value="<?php echo $item['name'] ?>" placeholder="<?php _e('Company name', ET_DOMAIN); ?>">
                    </div>
                    <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
                    <div class="input-text-remind">
                        <input type="text" required class="position"  value="<?php echo $item['position'] ?>" placeholder="<?php _e('Position', ET_DOMAIN); ?>">
                    </div>
                    <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
                    <div class="date-select">
                    <div class="date-select">
                    <?php 
                        JE_Helper::monthSelectBox('fromMonth' , $item['from']['month'], array('class' => 'month fromMonth' ,  ) );
                        JE_Helper::yearSelectBox('fromYear' , $item['from']['year'], array('class' => 'year fromYear' ,  ) );
                    ?>
                    </div>
                    <div class="clear" style="clear:both; height:18px; overflow:hidden;"><?php _e("to", ET_DOMAIN); ?></div>
                    <div class="date-select">
                    <?php 
                        JE_Helper::monthSelectBox('toMonth' ,$item['to']['month'], array('class' => 'month toMonth'  ) );
                        JE_Helper::yearSelectBox('toYear' , $item['to']['year'], array('class' => 'year toYear' ,  ) );
                    ?>
                    </div>
                    </div>
                    <div class="ui-checkbox signup">
                        <label for="checkbox-enhanced-<?php echo $key ?>" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-checkbox-off">
                            <?php _e("I currently work here", ET_DOMAIN); ?>
                        </label>
                        <input <?php checked( 1, $item['current'] ); ?> type="checkbox" name="checkbox-enhanced-<?php echo $key ?>" id="checkbox-enhanced-<?php echo $key ?>" class="curr" data-enhanced="true">
                        <span class="icon icon-track" data-icon="#"></span>
                    </div>
                <?php
                    }
                }
                ?>
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
                $selected_cat   =   array ();
                foreach ($resume->resume_category as $key => $value) {
                    array_push( $selected_cat, $value->term_id );
                }
        ?>
    	<div class="content-info content-text content-timeline category" data-resume="resume_category">

            <h1 class=""><?php echo $position_tax->get_title (); ?></h1>
            <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
    		<!-- <ul class="select-option-signup">
            	<li><span class="icon icon-track" data-icon="#"></span><span class="text">PHP Developer</span></li>
                <li><span class="icon icon-track" data-icon="#"></span><span class="text">PHP Developer</span></li>
                <li><span class="icon icon-track" data-icon="#"></span><span class="text">PHP Developer</span></li>
            </ul> -->
            <div class="clear" style="clear:both; height:5px; overflow:hidden;"></div>
            <div class="date-select option-signup">
                <!-- <select name="select-native-1" id="select-native-1" class="month">
                    <option value="1">Select Jobs</option>
                    <option value="2">The 2nd Option</option>
                    <option value="3">The 3rd Option</option>
                    <option value="4">The 4th Option</option>
                </select> -->
                <?php JE_Helper::jobPositionSelectTemplate('position[]', $selected_cat, array('class' => 'month' , 'attr' => array('multiple' => 'multiple', 'data-native-menu'=> 0 , 'data-defaults' => "true") ) ); ?>
            </div>
    	</div>
        <?php  }

            $available_tax  =   JE_TaxFactory::get_instance('available');
            $availables     =   $available_tax->get_terms_in_order();
            $colors         =   $available_tax->get_color();
            //$availables = get_terms( 'available' , array('hide_empty' => false) );
            if(!empty($availables)) {
                $checked_available  =   array();
        ?>
        <div class="content-info content-text content-timeline available" data-resume="available">
    		<h1 class=""><?php echo  $available_tax->get_title () ?></h1>

            <?php foreach ($resume->available as $r_avail) {
                array_push($checked_available, $r_avail->term_id );
                ?>
                <div class="ui-checkbox signup">
                    <label for="<?php echo $r_avail->slug; ?>" class="ui-btn ui-corner-all ui-btn-inherit ui-btn-icon-left ui-checkbox-off <?php echo isset($colors[$r_avail->term_id]) ? 'color-' . $colors[$r_avail->term_id] : '' ?>">
                        <?php echo $r_avail->name; ?>
                    </label>
                    <input checked="true" class="" id="<?php echo $r_avail->slug; ?>" type="checkbox" name="" value="<?php echo $r_avail->slug ?>" data-enhanced="true">
                </div>
            <?php } ?>

    		<?php foreach ($availables as $avail) {
                if(in_array($avail->term_id , $checked_available )) continue;
             ?>
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
                <?php foreach ($resume->skill as $key => $value) { ?>
                    <li class="element" ><span class="icon icon-track" data-icon="#"></span><span class="text"><?php echo $value->name ?></span><input class="skill" type="hidden" value="<?php echo $value->name ?>" ></li>
                <?php } ?>
            </ul>

    	</div>
        <?php do_action('je_resume_edit_form',$resume); ?>
    	<div class="content-info content-info-last content-text btn-custom-submit">
            <div class="input-text-remind">
                <input type="submit" class="sign_up" value="<?php _e("Update and Finish", ET_DOMAIN); ?>"  />
            </div>

        </div>
    </div>

</div>