<?php
/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class JE_Available_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @return void
     **/
    function JE_Available_Widget() {
        $widget_ops = array( 'classname' => 'je-resume-available', 'description' => 'This widget should only be dragged to the resume sidebar to function properly.' );
        $this->WP_Widget( 'je-resume-available', 'JE Available For', $widget_ops );
    }

    /**
     * Outputs the HTML for this widget.
     *
     * @param array  An array of standard parameters for widgets in this theme
     * @param array  An array of settings for this widget instance
     * @return void Echoes it's output
     **/
    function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        $job_vailable = new JE_Jobseeker_Available;
        $title_available  = $job_vailable->get_title(); 

        $before_widget	=	str_replace('widget ', 'widget widget-select widget_archive content-dot ', $before_widget);
        echo $before_widget;
        echo $before_title;
        echo apply_filters( 'widget_title' , $title_available );
        //echo $instance['title'];
        echo $after_title;
        ?>
			<ul class="filter-jobtype filter-joblist resume-filter available-lists" data-tax="available" >
				<?php 
				$available_tax  =   ET_TaxFactory::get_instance('available');
                $availables     =   $available_tax->get_terms_in_order(); 
                $colors         =   $available_tax->get_color ();
               
				foreach ($availables as $item) {
                    $color = 'color-13';
                    if(isset($colors[$item->term_id])) {
                        if($instance['hide_empty'] && $item->count <= 0 ) continue;
                        $color = 'color-'.$colors[$item->term_id];
                    }
					echo "<li><a data='{$item->slug}' href='".$available_tax->get_term_link( $item )."' class='available-input {$color}'>
                            <div class='name'>{$item->name} </div> <span class='icon-label flag'></span></a></li>";
				}
				?>
			</ul>
        <?php 
        echo $after_widget;

    }

    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array  An array of new settings as submitted by the admin
     * @param array  An array of the previous settings
     * @return array The validated and (if necessary) amended settings
     **/
   function update ( $new_instance, $old_instance ) {
		$instance 				= $old_instance;
		$new_instance 			= wp_parse_args( (array) $new_instance, array( 'title' => '', 'hide_empty' => false) );
		
		$instance['title'] 		= ($new_instance['title']);
		$instance['hide_empty'] = ($new_instance['hide_empty']);
		return $instance;
	}

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array  An array of the current settings for this widget
     * @return void Echoes it's output
     **/
    function form( $instance ) {
       	$instance = wp_parse_args( (array) $instance, array( 'title' => __('AVAILABLE', ET_DOMAIN ), 'hide_empty' => false) );
		//$title = $instance['title'];
	?>
		
		<p>
			<input <?php echo  ($instance['hide_empty'] ? 'checked="checked"' : '') ; ?> type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>">
			<label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php _e("Hide empty", ET_DOMAIN); ?></label><br>
		</p>

	<?php
    }
}

/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class JE_JobPosition_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @return void
     **/
    function JE_JobPosition_Widget() {
        $widget_ops = array( 'classname' => 'je_resume_category', 'description' => __('This widget should only be dragged to the resume sidebar to function properly.', ET_DOMAIN) );
        $this->WP_Widget( 'je_resume_category', __('JE Resume Categories', ET_DOMAIN), $widget_ops );
    }

    /**
     * Outputs the HTML for this widget.
     *
     * @param array  An array of standard parameters for widgets in this theme
     * @param array  An array of settings for this widget instance
     * @return void Echoes it's output
     **/
    function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        $job_pos        = new JE_Jobseeker_Position();
        $title_position  = $job_pos->get_title();

        $before_widget	=	str_replace('widget ', 'widget widget-select widget_archive content-dot ', $before_widget);
        echo $before_widget;
        echo $before_title;
        echo apply_filters( 'widget_title' ,$title_position ); 
        //echo $instance['title']; // Can set this with a widget option, or omit altogether
        echo $after_title;
     
				JE_Helper::resume_categories_filter_list(0, false, $instance);
				
   
	    echo $after_widget;
    }

    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array  An array of new settings as submitted by the admin
     * @param array  An array of the previous settings
     * @return array The validated and (if necessary) amended settings
     **/
    function update( $new_instance, $old_instance ) {
    
        $instance 				= $old_instance;
		$new_instance 			= wp_parse_args( (array) $new_instance, array( 'title' => '') );
		
		$instance['title'] 		= ($new_instance['title']);
		$instance['hide_empty'] = ($new_instance['hide_empty']);
        $instance['hide_jobcount'] = ($new_instance['hide_jobcount']);
		return $instance;
    }

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array  An array of the current settings for this widget
     * @return void Echoes it's output
     **/
    function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => __('RESUME CATEGORIES', ET_DOMAIN ), 'hide_empty' => false , 'hide_jobcount' => false) );
		//$title = $instance['title'];
	?>
		
		<p>
			<input <?php echo  ($instance['hide_empty'] ? 'checked="checked"' : '') ; ?> type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>">
			<label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php _e('Hide empty', ET_DOMAIN) ?></label><br>
		</p>
         <p>
            <input <?php echo  ($instance['hide_jobcount'] ? 'checked="checked"' : '') ; ?> type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hide_jobcount'); ?>" name="<?php echo $this->get_field_name('hide_jobcount'); ?>">
            <label for="<?php echo $this->get_field_id('hide_jobcount'); ?>"><?php _e("Hide job count", ET_DOMAIN); ?></label><br>
        </p>
	<?php
    }
}

/**
 * new WordPress Widget format
 * Wordpress 2.8 and above
 * @see http://codex.wordpress.org/Widgets_API#Developing_Widgets
 */
class JE_Resume_Count_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @return void
     **/
    function JE_Resume_Count_Widget() {
        $widget_ops = array( 'classname' => 'widget je-resume-count bg-grey-widget companies-statis', 'description' => __('Drag this widget to any sidebar to display the total count of active resumes.',ET_DOMAIN) );
        $this->WP_Widget( 'je-resume-count', ' JE Resume Count ', $widget_ops );
    }

    /**
     * Outputs the HTML for this widget.
     *
     * @param array  An array of standard parameters for widgets in this theme
     * @param array  An array of settings for this widget instance
     * @return void Echoes it's output
     **/
    function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        echo $before_widget;
        // if(!empty($instance['title'])) {
        //     echo $before_title;
        //     echo $instance['title'];
        //     echo $after_title;
        // }
        ?>
        <div class="first">
            <?php _e('Resumes', ET_DOMAIN); ?>
            <?php 
                $count_posts = wp_count_posts('resume');
                $published  =   $count_posts->publish;
                $rejected   =   $count_posts->reject;
                $pending    =   $count_posts->pending;
                $draft      =   $count_posts->draft;
                $total      =   ($published + $rejected + $pending + $draft);
            ?>
            <span class="impress"><?php echo $total ?></span>
        </div>
        <div>
            <?php _e('Available resumes', ET_DOMAIN); ?>
            <span class="impress"><?php echo $published ?></span>
        </div>  
        <?php
    //
    // Widget display logic goes here
    //

    echo $after_widget;
    }

    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array  An array of new settings as submitted by the admin
     * @param array  An array of the previous settings
     * @return array The validated and (if necessary) amended settings
     **/
    function update( $new_instance, $old_instance ) {    
        // update logic goes here        
        $instance              = $old_instance;
        $new_instance           = wp_parse_args( (array) $new_instance, array( 'title' => '') );
        
        $instance['title']      = ($new_instance['title']);
        $instance['hide_empty'] = ($new_instance['hide_empty']);
        return $instance;
        
    }

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array  An array of the current settings for this widget
     * @return void Echoes it's output
     **/
    function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '') );
       // $title = $instance['title'];
    
    
    }
}

class JE_Resume_Search_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @return void
     **/
    function JE_Resume_Search_Widget() {
        $widget_ops = array( 'classname' => 'widget content-dot widget_je_alert', 'description' => __('This widget should only be dragged to the resume sidebar to function properly.',ET_DOMAIN) );
        $this->WP_Widget( 'je-resume-search', ' JE Resume Search ', $widget_ops );
    }

    function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        echo $before_widget;
        // if(!empty($instance['title'])){
        //     echo $before_title;
        //     echo $instance['title']; // Can set this with a widget option, or omit altogether
        //     echo $after_title;
        // }
        ?>
        <div class="widget-jse-search bg-grey-widget">
            <div class="jse-input">
                <label><?php _e('What', ET_DOMAIN); ?></label>
                <div class="input-area">
                    <input type="text" data-filter="rq" class="bg-default-input resume-input-query filter-input" placeholder='<?php _e("Job title or Skills", ET_DOMAIN); ?>' />
                    <span class="icon" data-icon="s"></span>
                </div>
            </div>
            <div class="jse-input">
                <label><?php  _e("Where", ET_DOMAIN); ?></label>
                <div class="input-area">
                    <input type="text" data-filter="et_location" class="bg-default-input resume-input-location filter-input" placeholder='<?php _e("Location", ET_DOMAIN); ?>' />
                    <span class="icon" data-icon="@"></span>
                </div>
            </div>
        </div>
        <?php
    //
    // Widget display logic goes here
    //
    echo $after_widget;
    }
    /**
     * Deals with the settings when they are saved by the admin. Here is
     * where any validation should be dealt with.
     *
     * @param array  An array of new settings as submitted by the admin
     * @param array  An array of the previous settings
     * @return array The validated and (if necessary) amended settings
     **/
    function update( $new_instance, $old_instance ) {
    
        // update logic goes here
        $updated_instance = $new_instance;
        return $updated_instance;
    }

    /**
     * Displays the form for this widget on the Widgets page of the WP Admin area.
     *
     * @param array  An array of the current settings for this widget
     * @return void Echoes it's output
     **/
    function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
        
    
    
    }
}

add_action( 'widgets_init', 'je_register_resume_widgets');
function je_register_resume_widgets () {

	if(current_user_can('manage_options')) {
		$before_widget	=	'<aside id="%1$s" class="widget %2$s"><div class="sort-handle"></div>';
	} else {
		$before_widget	=	'<aside id="%1$s" class="widget %2$s">';
	}

	register_sidebar ( array(
			'name' => __( 'Resume Sidebar', ET_DOMAIN ),
			'id' => 'sidebar-resume',
			'description' => __( 'Drop widgets here to position them in the Resume list page', ET_DOMAIN ),
			'before_widget' => $before_widget,
			'after_widget' => "</aside>",
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		) 
	);

	register_widget( 'JE_Available_Widget' );
	register_widget( 'JE_JobPosition_Widget' );
    register_widget( 'JE_Resume_Count_Widget' );
    register_widget( 'JE_Resume_Search_Widget' );
}