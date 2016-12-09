<?php
/**
 * Job Engine Resumes API
 *
 */

class JE_Resume extends ET_Base{

    const ACTION_INIT       = 'init';
    const ACTION_META_BOXES   = 'add_meta_boxes';
    const ACTION_SAVE_POST  = 'save_post';

    const META_EDUCATION    = 'et_education';
    const META_EXPERIENCE   = 'et_experience';

    const POST_TYPE_RESUME  = 'resume';
    const TAX_SKILL         = 'skill';
    const TAX_AVAILABLE     = 'available';
    const TAX_RESUME_CATEGORY  = 'resume_category';

    static $fields = array('et_education', 'et_experience', 'et_location', 'et_url', 'et_profession_title' , 'je_include_search', 'et_privacy' , 'et_privacy_post_name', 'et_contact');
    static $taxonomies = array('skill', 'available', 'resume_category');
    static $slug    =    array( 'resume_archive' => 'resumes' ,  'resume' => 'resume' ,'skill' => 'skill' , 'available' => 'available' , 'resume_category' => 'resume-category') ;

    static $privacy = array('public', 'confidential');
    /**
     * register post type, etc...
     */
    public function __construct(){
        $this->add_action(self::ACTION_INIT, 'init');
        self::$slug   =   apply_filters('je_resume_slug' , self::$slug );
    }

    public function init(){
        $this->register_post_type();
        $this->register_tax();

        // add meta box
        $this->add_action(self::ACTION_META_BOXES, 'add_meta_boxes');
        $this->add_action(self::ACTION_SAVE_POST, 'save_meta_data');
    }

    public function add_meta_boxes(){
        add_meta_box(
            'resume_info',
            __('Resume Information', ET_DOMAIN),
            array($this, 'meta_box_information'),
            'resume',
            'normal', 
            'high'
        );
    }

    public function meta_box_information($post){
        $resume = self::convert_from_post($post);
        ?>
        <style>
        .timeline-date{
            width: 140px;
            display: inline-block;
            margin-right: 20px;
        }
        .timeline-date input[type=text]{
            width: 70px;
        }
        .resume-info{
            width: 290px;
        }
        .timeline-item{
            margin-bottom: 20px;
            padding: 10px;
            background: #f0f0f0;
            width: 330px;
        }
        </style>
        <?php wp_enqueue_script( 'jquery-ui-autocomplete' ) ?>
        <script>
        (function($){
            $(document).ready(function(){
                $('.add-timeline').click(function(event){
                    event.preventDefault();
                    var target      = $(this).attr('href');
                    var template    = '';
                    if ($(this).hasClass('add-edu')){
                        var index = $('.edu-item').length;
                        template = $('#edu_template').html();
                        template = template.replace('{index}', index);
                    }else if ( $(this).hasClass('add-exp') ){
                        var index = $('.exp-item').length;
                        template = $('#exp_template').html();
                        template = template.replace('{index}', index);
                    }
                    $(target).append(template);
                });

                // auto complete
                $('#user_input').autocomplete({
                    source: JSON.parse($('#users_data').html()),
                    select: function(event, ui){
                        $('#user_input').val(ui.item.value);
                        $('#user_id_input').val(ui.item.id);
                        return false;
                    }
                })
            });
        })(jQuery);
        </script>
        <input type="hidden" name="_resume_nonce" value="<?php echo wp_create_nonce( 'resume_meta_box' ) ?>">
        <h4><?php _e('Education background') ?></h4>
        <div id="edu_info">
            <?php $index = 0; ?>
            <?php foreach ((array)$resume->et_education as $key => $edu) { 
                if (empty($edu)) continue;

                $is_current = $edu['current'] ? 'checked="checked"' : '';
                ?>
                <div class="timeline-item edu-item">
                    <div>
                        <label for="education"><?php _e('School', ET_DOMAIN) ?></label><br />
                        <input class="resume-info" type="text" name="education[name][]" value="<?php echo $edu['name'] ?>">
                    </div>
                    <div>
                        <label for="education"><?php _e('Degree', ET_DOMAIN) ?></label><br />
                        <input class="resume-info" type="text" name="education[degree][]" value="<?php echo $edu['degree'] ?>">
                    </div>
                    <div class="timeline-date">
                        <label for="from"><?php _e('From', ET_DOMAIN) ?></label> <br>
                        <select name="education[from][month][]" id="">
                            <option value=""><?php _e('Month', ET_DOMAIN) ?></option>
                            <?php for($i = 1; $i <= 12; $i++ ) {
                                $selected = $edu['from']['month'] == $i ? 'selected="selected"' : '';
                                echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                            }?>
                        </select>
                        <select name="education[from][year][]" id="">
                            <option value=""><?php _e('Year', ET_DOMAIN) ?></option>
                            <?php for($i = (int)date('Y'); $i >= 1950 ; $i-- ) {
                                $selected = $edu['from']['year'] == $i ? 'selected="selected"' : '';
                                echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                            }?>
                        </select>
                    </div>
                    <div class="timeline-date">
                        <label for="from"><?php _e('To', ET_DOMAIN) ?></label> <br>
                        <select name="education[to][month][]" id="">
                            <option value=""><?php _e('Month', ET_DOMAIN) ?></option>
                            <?php for($i = 1; $i <= 12; $i++ ) {
                                $selected = $edu['to']['month'] == $i ? 'selected="selected"' : '';
                                echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                            }?>
                        </select>
                        <select name="education[to][year][]" id="">
                            <option value=""><?php _e('Year', ET_DOMAIN) ?></option>
                            <?php for($i = (int)date('Y'); $i >= 1950 ; $i-- ) {
                                $selected = $edu['to']['year'] == $i ? 'selected="selected"' : '';
                                echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                            }?>
                        </select>
                    </div>
                    <div class="curr">
                        <input type="hidden" name="education[current][<?php echo $index ?>]" value="0">
                        <input type="checkbox" name="education[current][<?php echo $index ?>]" <?php echo $is_current ?> value="1"> 
                        <label for=""><?php _e('Currently study here', ET_DOMAIN) ?></label>
                    </div>
                </div>    
            <?php $index++; } ?>
            <div class="timeline-item edu-item">
                <div>
                    <label for="education"><?php _e('School', ET_DOMAIN) ?></label><br />
                    <input class="resume-info" type="text" name="education[name][]">
                </div>
                <div>
                    <label for="education"><?php _e('Degree', ET_DOMAIN) ?></label><br />
                    <input class="resume-info" type="text" name="education[degree][]">
                </div>
                <div class="timeline-date">
                    <label for="from"><?php _e('From', ET_DOMAIN) ?></label> <br>
                    <select name="education[from][month][]" id="">
                        <option value=""><?php _e('Month', ET_DOMAIN) ?></option>
                        <?php for($i = 1; $i <= 12; $i++ ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                    <select name="education[from][year][]" id="">
                        <option value=""><?php _e('Year', ET_DOMAIN) ?></option>
                        <?php for($i = (int)date('Y'); $i >= 1950 ; $i-- ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                </div>
                <div class="timeline-date">
                    <label for="from"><?php _e('To', ET_DOMAIN) ?></label> <br>
                    <select name="education[to][month][]" id="">
                        <option value=""><?php _e('Month', ET_DOMAIN) ?></option>
                        <?php for($i = 1; $i <= 12; $i++ ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                    <select name="education[to][year][]" id="">
                        <option value=""><?php _e('Year', ET_DOMAIN) ?></option>
                        <?php for($i = (int)date('Y'); $i >= 1950 ; $i-- ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                </div>
                <div class="curr">
                    <input type="hidden" name="education[current][<?php echo $index ?>]" value="0">
                    <input type="checkbox" name="education[current][<?php echo $index ?>]" value="1"> 
                    <label for=""><?php _e('Currently study here', ET_DOMAIN) ?></label>
                </div>
            </div>
        </div>
        <a href="#edu_info" rel="" class="button add-edu add-timeline"><?php _e('Add more school', ET_DOMAIN) ?></a>

        <h4><?php _e('Work Experience') ?></h4>
        <div id="exp_info">
            <?php 
            if (is_array($resume->et_experience)){
                $index = 0;
                foreach ((array)$resume->et_experience as $key => $exp) {
                    if (empty($exp)) continue;
                    $is_current = $exp['current'] ? 'checked="checked"' : '';
                ?>
                    <div class="timeline-item exp-item">
                        <div>
                            <label for="experience"><?php _e('Company', ET_DOMAIN) ?></label><br />
                            <input class="resume-info" type="text" name="experience[name][]" value="<?php echo $exp['name'] ?>">
                        </div>
                        <div>
                            <label for="experience"><?php _e('Category', ET_DOMAIN) ?></label><br />
                            <input class="resume-info" type="text" name="experience[position][]" value="<?php echo !empty($exp['position']) ? $exp['position'] : '' ?>">
                        </div>
                        <div class="timeline-date">
                            <label for="from"><?php _e('From', ET_DOMAIN) ?></label> <br>
                            <select name="experience[from][month][]" id="">
                                <option value=""><?php _e('Month', ET_DOMAIN) ?></option>
                                <?php for($i = 1; $i <= 12; $i++ ) {
                                    $selected = $exp['from']['month'] == $i ? 'selected="selected"' : '';
                                    echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                                }?>
                            </select>
                            <select name="experience[from][year][]" id="">
                                <option value=""><?php _e('Year', ET_DOMAIN) ?></option>
                                <?php for($i = (int)date('Y'); $i >= 1950 ; $i-- ) {
                                    $selected = $exp['from']['year'] == $i ? 'selected="selected"' : '';
                                    echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                                }?>
                            </select>
                        </div>
                        <div class="timeline-date">
                            <label for="from"><?php _e('To', ET_DOMAIN) ?></label> <br>
                            <select name="experience[to][month][]" id="">
                                <option value=""><?php _e('Month', ET_DOMAIN) ?></option>
                                <?php for($i = 1; $i <= 12; $i++ ) {
                                    $selected = $exp['to']['month'] == $i ? 'selected="selected"' : '';
                                    echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                                }?>
                            </select>
                            <select name="experience[to][year][]" id="">
                                <option value=""><?php _e('Year', ET_DOMAIN) ?></option>
                                <?php for($i = (int)date('Y'); $i >= 1950 ; $i-- ) {
                                    $selected = $exp['to']['year'] == $i ? 'selected="selected"' : '';
                                    echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                                }?>
                            </select>
                        </div>
                        <div class="curr">
                            <input type="hidden" name="experience[current][<?php echo $index ?>]" value="0">
                            <input type="checkbox" name="experience[current][<?php echo $index ?>]" <?php echo $is_current ?> value="1">
                            <label for=""><?php _e('Currently work here', ET_DOMAIN) ?></label>
                        </div>
                    </div>    
            <?php $index++; }  // end foreach
             } // end if ?>
            <div class="timeline-item exp-item">
                <div>
                    <label for="experience"><?php _e('Company', ET_DOMAIN) ?></label><br />
                    <input class="resume-info" type="text" name="experience[name][]">
                </div>
                <div>
                    <label for="experience"><?php _e('Category', ET_DOMAIN) ?></label><br />
                    <input class="resume-info" type="text" name="experience[position][]">
                </div>
                <div class="timeline-date">
                    <label for="from"><?php _e('From', ET_DOMAIN) ?></label> <br>
                    <select name="experience[from][month][]" id="">
                        <option value=""><?php _e('Month', ET_DOMAIN) ?></option>
                        <?php for($i = 1; $i <= 12; $i++ ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                    <select name="experience[from][year][]" id="">
                        <option value=""><?php _e('Year', ET_DOMAIN) ?></option>
                        <?php for($i = (int)date('Y'); $i >= 1950 ; $i-- ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                </div>
                <div class="timeline-date">
                    <label for="from"><?php _e('To', ET_DOMAIN) ?></label> <br>
                    <select name="experience[to][month][]" id="">
                        <option value=""><?php _e('Month', ET_DOMAIN) ?></option>
                        <?php for($i = 1; $i <= 12; $i++ ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                    <select name="experience[to][year][]" id="">
                        <option value=""><?php _e('Year', ET_DOMAIN) ?></option>
                        <?php for($i = (int)date('Y'); $i >= 1950 ; $i-- ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                </div>
                <div class="curr">
                    <input type="hidden" name="experience[current][<?php echo $index ?>]" value="0">
                    <input type="checkbox" name="experience[current][<?php echo $index ?>]" value="1"> 
                    <label for=""><?php _e('Currently work here', ET_DOMAIN) ?></label>
                </div>
            </div>
        </div>
        <a href="#exp_info" rel="" class="button add-exp add-timeline"><?php _e('Add more company', ET_DOMAIN) ?></a>

        <div id="form_user">
            <h4><?php _e('Job seeker', ET_DOMAIN) ?></h4>
            <?php $author =  get_userdata($post->post_author); ?>
            <input type="text" id="user_input" name="jobseeker_name" value="<?php echo $author->display_name ?>">
            <input type="hidden" id="user_id_input" name="jobseeker_id" value="<?php echo $post->post_author; ?>">
        </div>
        <?php  do_action('et_resume_meta_box',$resume); ?>
        <?php 
        $users = get_users(array(
            'role'      => 'jobseeker'
        ));
        global $wpdb;
        $json = array();
        foreach ($users as $user) {
            $json[] = array('value' => $user->display_name, 'id' => $user->ID, 'label' => $user->display_name);
        }
        ?>
        <script type="text/data" id="users_data">
            <?php echo json_encode($json); ?>
        </script>
        <script type="text/template" id="edu_template">
            <div class="timeline-item edu-item">
                <div>
                    <label for="education"><?php _e('School', ET_DOMAIN) ?></label><br />
                    <input class="resume-info" type="text" name="education[name][]">
                </div>
                <div class="timeline-date">
                    <label for="from"><?php _e('From', ET_DOMAIN) ?></label> <br>
                    <select name="education[from][month][]" id="">
                        <option value=""><?php _e('Month', ET_DOMAIN) ?></option>
                        <?php for($i = 1; $i <= 12; $i++ ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                    <select name="education[from][year][]" id="">
                        <option value=""><?php _e('Year', ET_DOMAIN) ?></option>
                        <?php for($i = (int)date('Y'); $i >= 1950 ; $i-- ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                </div>
                <div class="timeline-date">
                    <label for="from"><?php _e('To', ET_DOMAIN) ?></label> <br>
                    <select name="education[to][month][]" id="">
                        <option value=""><?php _e('Month', ET_DOMAIN) ?></option>
                        <?php for($i = 1; $i <= 12; $i++ ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                    <select name="education[to][year][]" id="">
                        <option value=""><?php _e('Year', ET_DOMAIN) ?></option>
                        <?php for($i = (int)date('Y'); $i >= 1950 ; $i-- ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                </div>
                <div class="curr">
                    <input type="hidden" name="education[current][{index}]" value="0"> 
                    <input type="checkbox" name="education[current][{index}]" value="1"> 
                    <label for=""><?php _e('I currently work here', ET_DOMAIN) ?></label>
                </div>
            </div>
        </script>
        <script type="text/template" id="exp_template">
            <div class="timeline-item exp-item">
                <div>
                    <label for="education"><?php _e('Company', ET_DOMAIN) ?></label><br />
                    <input class="resume-info" type="text" name="experience[name][]">
                </div>
                <div class="timeline-date">
                    <label for="from"><?php _e('Category', ET_DOMAIN) ?></label> <br>
                    <input type="text" name="experience[position][]" id="">
                </div>
                <div class="timeline-date">
                    <label for="from"><?php _e('From', ET_DOMAIN) ?></label> <br>
                    <select name="experience[from][month][]" id="">
                        <option value=""><?php _e('Month', ET_DOMAIN) ?></option>
                        <?php for($i = 1; $i <= 12; $i++ ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                    <select name="experience[from][year][]" id="">
                        <option value=""><?php _e('Year', ET_DOMAIN) ?></option>
                        <?php for($i = (int)date('Y'); $i >= 1950 ; $i-- ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                </div>
                <div class="timeline-date">
                    <label for="from"><?php _e('To', ET_DOMAIN) ?></label> <br>
                    <select name="experience[to][month][]" id="">
                        <option value=""><?php _e('Month', ET_DOMAIN) ?></option>
                        <?php for($i = 1; $i <= 12; $i++ ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                    <select name="experience[to][year][]" id="">
                        <option value=""><?php _e('Year', ET_DOMAIN) ?></option>
                        <?php for($i = (int)date('Y'); $i >= 1950 ; $i-- ) {
                            echo '<option value="'.$i.'">'.$i.'</option>';
                        }?>
                    </select>
                </div>
                <div class="curr">
                    <input type="hidden" name="experience[current][{index}]" value="0"> 
                    <input type="checkbox" name="experience[current][{index}]" value="1"> 
                    <label for=""><?php _e('I currently work here', ET_DOMAIN) ?></label>
                </div>
            </div>
        </script>
        <?php
    }

    /**
     * Perform saving resume data
     */
    public function save_meta_data($post_id){
        if ( !isset( $_POST['post_type'] ) || $_POST['post_type'] != 'resume')
            return ;

        if ( !isset( $_POST['_resume_nonce'] ) || !wp_verify_nonce( $_POST['_resume_nonce'], 'resume_meta_box' ) )
            return;

        // clear nonce
        //$_POST['_resume_nonce'] = '';

        // setup education fields 
        $args   = array();
        $edu    = array();
        foreach ($_POST['education']['name'] as $index => $value) {
            if (!empty($value) && (!empty($_POST['education']['from']))) {
                $edu[] = array(
                    'name'  => $value, 
                    'from'  => array( 
                        'month' => $_POST['education']['from']['month'][$index], 
                        'year' => $_POST['education']['from']['year'][$index]
                        ) ,
                    'to'    => array( 
                        'month' => $_POST['education']['to']['month'][$index], 
                        'year' => $_POST['education']['to']['year'][$index]
                        ) ,
                    'degree' => $_POST['education']['degree'][$index],
                    'current' => empty($_POST['education']['current'][$index]) ? 0 : $_POST['education']['current'][$index]
                );
            }
        }
        usort( $edu, array('JE_Resume', 'sortTimeline') );
        update_post_meta( $post_id, 'et_education', $edu );

        // setup exp fields
        $exp    = array();
        foreach ($_POST['experience']['name'] as $index => $value) {
            if (!empty($value) && (!empty($_POST['experience']['from']))) {
                $exp[] = array(
                    'name'  => $value, 
                    'position'  => $_POST['experience']['position'][$index], 
                    'from'  => array( 
                        'month' => $_POST['experience']['from']['month'][$index], 
                        'year' => $_POST['experience']['from']['year'][$index]
                        ) ,
                    'to'    => array( 
                        'month' => $_POST['experience']['to']['month'][$index], 
                        'year' => $_POST['experience']['to']['year'][$index]
                        ) ,
                    'current' => empty($_POST['experience']['current'][$index]) ? 0 : $_POST['experience']['current'][$index]
                );
            }
        }
        usort( $exp, array('JE_Resume', 'sortTimeline') );
        update_post_meta( $post_id, 'et_experience', $exp );

        // update post author
        global $wpdb;
        $wpdb->query("UPDATE {$wpdb->posts} SET post_author = '{$_POST['jobseeker_id']}' WHERE ID = {$post_id} ");
        
        //update_post_meta( $post_id, 'et_education', $meta_value, $prev_value = '' )
    }

    private function register_tax(){
        // register skill taxonomy 
        register_taxonomy( 'skill', 'resume', array(
            'hierarchical'            => false,
            'labels'                  => array(
                'name'                         => _x( 'Skills', 'taxonomy general name' ),
                'singular_name'                => _x( 'Skill', 'taxonomy singular name' ),
                'search_items'                 => __( 'Search Skills' ),
                'popular_items'                => __( 'Popular Skills' ),
                'all_items'                    => __( 'All Skills' ),
                'parent_item'                  => null,
                'parent_item_colon'            => null,
                'edit_item'                    => __( 'Edit Skill' ), 
                'update_item'                  => __( 'Update Skill' ),
                'add_new_item'                 => __( 'Add New Skill' ),
                'new_item_name'                => __( 'New Skill Name' ),
                'separate_items_with_commas'   => __( 'Separate skills with commas' ),
                'add_or_remove_items'          => __( 'Add or remove skills' ),
                'choose_from_most_used'        => __( 'Choose from the most used skills' ),
                'not_found'                    => __( 'No skills found.' ),
                'menu_name'                    => __( 'Skills' )
            ),
            'show_ui'                 => true,
            'show_admin_column'       => true,
            'update_count_callback'   => '_update_post_term_count',
            'query_var'               => true,
            'rewrite'                 => array( 'slug' => self::$slug['skill'] )
        ) );
        
        // register available for
        register_taxonomy( 'available', 'resume', array(
            'hierarchical'            => false,
            'labels'                  => array(
                'name'                         => _x( 'Available', 'taxonomy general name' ),
                'singular_name'                => _x( 'Available', 'taxonomy singular name' ),
                'search_items'                 => __( 'Search Available for' ),
                'popular_items'                => __( 'Popular Available for' ),
                'all_items'                    => __( 'All Available for' ),
                'parent_item'                  => null,
                'parent_item_colon'            => null,
                'edit_item'                    => __( 'Edit Available' ), 
                'update_item'                  => __( 'Update Available' ),
                'add_new_item'                 => __( 'Add New Available' ),
                'new_item_name'                => __( 'New Available Name' ),
                'separate_items_with_commas'   => __( 'Separate "available for" with commas' ),
                'add_or_remove_items'          => __( 'Add or remove available' ),
                'choose_from_most_used'        => __( 'Choose from the most used available' ),
                'not_found'                    => __( 'No available found.' ),
                'menu_name'                    => __( 'Available For' )
            ),
            'show_ui'                 => true,
            'show_admin_column'       => true,

            'query_var'               => true,
            'rewrite'                 => array( 'slug' => self::$slug['available'] )
        ) );

        // register resume category
        register_taxonomy( 'resume_category', 'resume', array(
            'hierarchical'        => true,
            'labels'              =>  array(
                'name'                => _x( 'Categories', 'taxonomy general name' ),
                'singular_name'       => _x( 'Category', 'taxonomy singular name' ),
                'search_items'        => __( 'Search Categories' ),
                'all_items'           => __( 'All Categories' ),
                'parent_item'         => __( 'Parent Category' ),
                'parent_item_colon'   => __( 'Parent Category:' ),
                'edit_item'           => __( 'Edit Category' ), 
                'update_item'         => __( 'Update Category' ),
                'add_new_item'        => __( 'Add New Category' ),
                'new_item_name'       => __( 'New Category Name' ),
                'menu_name'           => __( 'Category' )
            ),
            'show_ui'             => true,
            'show_admin_column'   => true,
            'query_var'           => true,
            'rewrite'             => array( 'slug' => self::$slug['resume_category'] )
        ) );
    }

    /**
     * register post type for resume 
     */
    private function register_post_type(){
        register_post_type( 'resume', array(
            'labels' => array(
                'name' => __('Resumes',ET_DOMAIN),
                'singular_name' => __('Resume',ET_DOMAIN),
                'add_new' => __('Add New',ET_DOMAIN),
                'add_new_item' => __('Add New Resume',ET_DOMAIN),
                'edit_item' => __('Edit Resume',ET_DOMAIN),
                'new_item' => __('New Resume',ET_DOMAIN),
                'all_items' => __('All Resumes',ET_DOMAIN),
                'view_item' => __('View Resume',ET_DOMAIN),
                'search_items' => __('Search Resumes',ET_DOMAIN),
                'not_found' =>  __('No resumes found',ET_DOMAIN),
                'not_found_in_trash' => __('No resumes found in Trash',ET_DOMAIN), 
                'parent_item_colon' => '',
                'menu_name' => __('Resumes',ET_DOMAIN)
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true, 
            'show_in_menu' => true, 
            'query_var' => true,
            'rewrite' => array( 'slug' => self::$slug['resume'] ),
            'capability_type' => 'post',
            'has_archive' => self::$slug['resume_archive'], 
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
        ));

    }

    private static function sortTimeline($a, $b){
        $a = wp_parse_args( $a, array(
            'from' => array('month' => '', 'year' => ''), 
            'to' => array('month' => '', 'year' => ''), 
            'current' => '0')
        );
        $b = wp_parse_args( $b, array(
            'from' => array('month' => '', 'year' => ''), 
            'to' => array('month' => '', 'year' => ''), 
            'current' => '0')
        );
        
        // if a is "currently working/study" and b is not
        if ( $a['current'] && !$b['current']){
            return -1;
        } 
        // if b is "currently working/study" and a is not
        else if ( !$a['current'] && $b['current'] ){
            return 1;
        } 
        else {
            $a1 = array(
                'from'  => empty($a['from']['month']) ? $a['from']['year'] . '-1' : $a['from']['year'] . '-' . $a['from']['month'],
                'to'    => empty($a['to']['month']) ? $a['to']['year'] . '-1' : $a['to']['year'] . '-' . $a['to']['month'],
            );
            $b1 = array(
                'from'  => empty($b['from']['month']) ? $b['from']['year'] . '-1' : $b['from']['year'] . '-' . $b['from']['month'],
                'to'    => empty($b['to']['month']) ? $b['to']['year'] . '-1' : $b['to']['year'] . '-' . $b['to']['month'],
            );
            if ( $a1['to'] > $b1['to'] ){
                return -1;
            }
            else if ($a1['to'] < $b1['to']) {
                return 1;
            } 
            else if ( $a1['from'] > $b1['from']){
                return -1;
            } 
            else
                return 1;
        }
    }

    /**
     * Insert new resume
     * @param $args array contain resume information
     * @return bool
     */
    static public function insert($args){
        // if args contain id, return update resume
        // $args['post_title']     = $args['title'];
        //$args['post_content ']  = $args['title'];

        // check permission
        // if (current_user_can( 'manage_options' )) return false;
        global $current_user , $user_ID;
        if (empty($current_user->ID)) return false;
        if($args['post_author'] != $current_user->ID && !current_user_can('manage_options'))
            return false;
        /**
         * if user already has a resume, change method to update
         */
        $resume =   self::get_resumes(array('author' => $args['post_author'],'post_status' => array('pending', 'publish', 'reject')));
        if(isset($resume[0])) {
            $args['ID'] =   $resume[0]->ID;
        }
        
        if (isset($args['ID'])){
            return self::update($args);
        } 

        $options        =   JE_Resume_Options::get_instance();
        $pending_resume =   $options->et_pending_resume;
        // 
        $args = wp_parse_args( $args, array(
            'post_type'     => self::POST_TYPE_RESUME, 
            'post_status'   => ($pending_resume) ? 'publish' : 'publish',
            'post_title'    => $current_user->display_name,
        ) );

        $args['je_include_search']          =   'yes';
        $args['et_location']                =   get_user_meta( $user_ID, 'et_location' , true );
        $args['et_profession_title']        =   get_user_meta( $user_ID, 'et_profession_title' , true );
        // setup field
        $fields = array();
        
        $meta_fields      = apply_filters( 'je_filter_resume_fields' , self::$fields  ) ;
        foreach ($meta_fields as $field) {
            if (isset($args[$field])){
                $fields[$field] = $args[$field];
                unset($args[$field]);
            }
        }

        // education
        if (isset($fields['et_education'])){
            foreach ((array)$fields['et_education'] as $key => $row) {
                $fields['et_education'][$key] = JE_Resume_Education::verify_fields($fields['et_education'][$key]);
                // $fields['et_education'][$key] = wp_parse_args( $fields['et_education'][$key], array(
                //     'name'  => '', 
                //     'from'  => array('month' => '', 'year' => ''), 
                //     'to'    => array('month' => '', 'year' => ''), 
                //     'current' => 0) );
            }
            usort($fields['et_education'], array('JE_Resume', 'sortTimeline') );
        }

        // work experience
        if (isset($fields['et_experience'])){
            foreach ((array)$fields['et_experience'] as $key => $row) {
                $fields['et_education'][$key] = JE_Resume_Experience::verify_fields($fields['et_experience'][$key]);
                // $fields['et_experience'][$key] = wp_parse_args( $fields['et_experience'][$key], array(
                //     'name' => '',
                //     'position' => '', 
                //     'from'  => array('month' => '', 'year' => ''), 
                //     'to'    => array('month' => '', 'year' => ''), 
                //     'current' => 0) );
            }
            usort($fields['et_experience'], array('JE_Resume', 'sortTimeline') );
        }

        // taxonomies
        $taxs      =  apply_filters( 'je_filter_resume_taxs', self::$taxonomies  ) ;
        $tax_input = array();
        foreach ($taxs as $tax) {
            if (isset($args[$tax]) && is_array($args[$tax])){
                $tax_input[$tax] = $args[$tax];
                unset($args[$tax]);
            }
        }
        if (!empty($tax_input)){
            $args['tax_input'] = $tax_input;
        }

        $args['post_type']  = self::POST_TYPE_RESUME;
        $args['post_title'] = $current_user->display_name;

        $id = wp_insert_post( $args );
        /**
         * update resume tax
        */
        foreach ($tax_input as $key => $value) {
            wp_set_post_terms( $id, $value, $key );
        }
        // continuely update information
        if ($id){
            // update fields
            foreach ($fields as $key => $field) {
                update_post_meta( $id, $key, $field );
            }
        }

        do_action('je_insert_resume', $id);

        return $id;
    }

    /**
     * Update resume
     * @param $args array contain resume information
     * @return bool
     */
    static public function update($args){
        if (empty($args['ID'])) return false;

        //return self::insert($args);

        // setup field
        $fields = array();

        $meta_fields      = apply_filters( 'je_filter_resume_fields' , self::$fields  ) ;
        foreach ($meta_fields as $field) {
            if (isset($args[$field])){
                $fields[$field] = $args[$field];
                unset($args[$field]);
            }
        }

        // education
        if (isset($fields['et_education'])){
            foreach ((array)$fields['et_education'] as $key => $row) {
                if ( empty($row['name']) ) {
                    unset($fields['et_education'][$key]);
                    continue;
                }
                $fields['et_education'][$key] = JE_Resume_Education::verify_fields($fields['et_education'][$key]);

            }
            usort($fields['et_education'], array('JE_Resume', 'sortTimeline') );
        }

        // work experience
        if (isset($fields['et_experience'])){
            foreach ((array)$fields['et_experience'] as $key => $row) {
                if ( empty($row['name']) ) {
                    unset($fields['et_experience'][$key]);
                    continue;
                }

                $fields['et_experience'][$key] = JE_Resume_Experience::verify_fields($fields['et_experience'][$key]);
                //$fields['et_experience'][$key] = wp_parse_args( $fields['et_experience'][$key], array('name' => '', 'position' => '', 'from' => '', 'to' => '', 'current' => 0) );
            }
            usort($fields['et_experience'], array('JE_Resume', 'sortTimeline') );
        }

        // taxonomies
        $tax_input = array();
        $taxs      =  apply_filters( 'je_filter_resume_taxs', self::$taxonomies  ) ;

        foreach ($taxs as $tax) {
            if (isset($args[$tax]) && is_array($args[$tax])){
                $tax_input[$tax] = $args[$tax];
                unset($args[$tax]);
            }
        }
        if (!empty($tax_input)){
            $args['tax_input'] = $tax_input;
        }

        if(isset($args['post_title']) && get_post_meta( $args['ID'], 'et_privacy' , true) == 'public' )
            $args['post_name'] =   $args['post_title'];

        if(isset($fields['et_privacy'])) {
            if($fields['et_privacy'] == 'confidential') {
                $args['post_name']  =   sprintf(__("Anonymous %s", ET_DOMAIN), md5($args['ID']) ); 
                $args['post_title'] =   __("Anonymous", ET_DOMAIN) ;
            }
            else {
                $args['post_name'] =   $args['post_title'];
            }
        }

        $id =   wp_update_post($args);

        if($id) {
            // update fields
            foreach ($fields as $key => $field) {
                update_post_meta( $args['ID'], $key, $field );
            }

            $resume =   self::convert_from_post(get_post($id));

            /**
             * update privacy for user
            */
 
            do_action ('je_update_resume', $id );
            return $resume;
        }

        do_action ('je_update_resume', $id );
        return $id;
    }

    /**
     * Delete resume by id
     * @param $id
     * @param $force_delete bool force delete resume from database
     * @return bool
     */
    static public function delete_resume($id, $force_delete = false){
        return wp_delete_post( $id,  $force_delete );
    }

    static public function convert_from_post($post){
        $arr = (array)$post;

        $arr['post_content_filtered'] = apply_filters( 'the_content', $arr['post_content'] );

        // permalink
        $arr['permalink'] = get_permalink( $post->ID );

        // custom fields
        $meta_fields      = apply_filters( 'je_filter_resume_fields' , self::$fields  ) ;
        foreach ($meta_fields as $field) {
            $arr[$field] = get_post_meta( $arr['ID'], $field, true );
        }

        // education
        // work experience
        foreach (array('et_education', 'et_experience') as $meta) {
            if ( !empty( $arr[$meta] ) ){
                foreach ((array)$arr[$meta] as $index => $edu) {
                    if ($meta == 'et_education' && is_array($arr[$meta][$index]))
                        $arr[$meta][$index] = JE_Resume_Education::output($arr[$meta][$index]);
                    else if ($meta == 'et_experience' && is_array($arr[$meta][$index])){
                        $arr[$meta][$index] = JE_Resume_Experience::output($arr[$meta][$index]);
                    }
                }
            }
        }

        // taxonomy
        $taxs      =  apply_filters( 'je_filter_resume_taxs', self::$taxonomies  ) ;
        foreach ($taxs as $taxonomy) {
            $arr[$taxonomy] = wp_get_object_terms( $arr['ID'], $taxonomy );
        }
        switch ($arr['et_privacy']) {
            case 'confidential':
                $arr = self::confidential_resume ($arr);
                break;
            default:
                $arr = self::public_resume ($arr);
                break;
        }
        $arr = apply_filters( 'convert_resume_data', (object)$arr );
        return (object)$arr;
    }

    static function confidential_resume ($arr) {
        global $user_ID;
        /**
         * shouldn't set confidential for owner
        */
        if( $user_ID == $arr['post_author'] || current_user_can( 'manage_options' ) ) return $arr;

        $accessible_list    =   JE_Job_Seeker::get_accessible_list($arr['post_author']);
        if( in_array( $user_ID, $accessible_list)) return $arr;

        $arr['et_experience']   =   array ();
        $arr['post_title']      =   __("Anonymous", ET_DOMAIN);
        $arr['et_url']          =   '';
        $arr['author']          =   __("Anonymous", ET_DOMAIN);
        $arr['title']           =   $arr['author'];
        $arr    =   apply_filters( 'convert_confidential_user', $arr );

        return $arr;

    }

    static function public_resume ($arr) {
        $arr['title']       = $arr['post_title'];
        $arr['author']      = get_user_meta( $arr['post_author'] , 'display_name', true);
        $arr    =   apply_filters( 'convert_public_user', $arr );
        return $arr;
    }

    static public function get($ID){
        $post = get_post($ID);
        return self::convert_from_post($post);
    }

    static public function get_resumes($args){
        $args   = wp_parse_args( $args, array( 'post_type' => 'resume' ) );
        $posts  = get_posts($args);
        $result = array();
        foreach ($posts as $post) {
            $result[] = self::convert_from_post($post);
        }
        return $result;
    }

    static public function query_resumes($params){
        $params   = wp_parse_args( $params, array( 'post_type' => 'resume' ) );

        global $wp_query, $post;
        //
        $fields = array('relation' => 'AND');
        foreach (self::$fields as $field) {
            if ( !empty($params[$field]) ){
                $fields[] = array(
                    'key'   => $field,
                    'value' => $params[$field],
                    'compare' => 'LIKE'
                );
            }
        }
        if ( !empty($params['meta_query']) )
            $params['meta_query'] = wp_parse_args( $params['meta_query'], $fields );
        else
            $params['meta_query'] = $fields;
        return query_posts($params);
    }

    static public function get_meta($post_id, $key){
        return get_post_meta( $post_id, $key, true );
    }

    static public function get_skills($post_id, $args = array()){
        return wp_get_object_terms( $post_id, 'skill', $args );
    }

    static public function get_availables($post_id, $args = array()){
        return wp_get_object_terms( $post_id, 'available', $args );
    }

    static public function get_resume_categories($post_id, $args = array()){
        return wp_get_object_terms( $post_id, 'resume_category', $args );
    }
}

/**
 *
 */
class JE_Resume_Timeline{

    public function __construct($name, array $fields){
        $this->name     = $name;
        $this->fields   = $fields;// apply_filters( 'et_education_fields', array('name', 'from', 'to', 'current') );
    }

    protected function _verify_fields($fields){
        $return = wp_parse_args( $fields, array(
            'name'  => '' ,
            'from'  => array('month' => '', 'year' => ''),
            'to'    => array('month' => '', 'year' => ''),
            'current' => 0) );

        foreach ((array)$this->fields as $value) {
            if (!isset($return[$value]))
                $return[$value] = apply_filters( 'et_default_' . $this->name . '_field_' . $value, '' );
        }
        return $return;
    }

    protected function _input($fields){
        foreach ((array)$fields as $key => $value){
            if ( !in_array($key, $this->fields) )
                unset($fields[$key]);
        }

        return $this->_verify_fields($fields);
    }

    protected function _output($fields){
        $return = $this->_verify_fields($fields);

        if ( $return['from']['month'] == '' )
            $return['from']['display'] = $return['from']['year'];
        else
            $return['from']['display'] = $return['from']['month'] . '-' . $return['from']['year'];

        if ( $return['to']['month'] == '' )
            $return['to']['display'] = $return['to']['year'];
        else
            $return['to']['display'] = $return['to']['month'] . '-' . $return['to']['year'];

        if ( $return['current'] == 1)
            $return['to']['display'] = __('Now', ET_DOMAIN);

        return $return;
    }

}

/**
 * This class handle education information
 * in every resume profile
 * Developer also can add more fields to display
 * in jobseeker profile
 */
class JE_Resume_Education extends JE_Resume_Timeline{

    static $instance = null;

    public function __construct(){
        parent::__construct('education', apply_filters( 'et_education_fields', array('name', 'from', 'to', 'current', 'degree') ));
    }

    static public function get_instance(){
        if (self::$instance == null)
            self::$instance = new JE_Resume_Education();
        return self::$instance;
    }

    /**
     * Verify fields for input or output
     */
    static public function verify_fields($fields){
       $instance = self::get_instance();
       return $instance->_verify_fields($fields);
    }

    static public function input($fields){
        $instance = self::get_instance();
        return $instance->_input($fields);
    }

    protected function _output($fields){
        $output = parent::_output($fields);
        if($output['degree'] != '')
            $output['highlight']    = sprintf( __('%s <span> at </span> %s', ET_DOMAIN), $output['degree'], $output['name']);
        else 
            $output['highlight']    =  $output['name'] ;
        return $output;
    }

    static public function output($fields){
        $instance = self::get_instance();
        return $instance->_output($fields);
    }
}

/**
 * This class handle experience information 
 * in every resume profile
 * Developer also can add more fields to display 
 * in jobseeker profile
 */
class JE_Resume_Experience extends JE_Resume_Timeline{

    static $instance = null;

    public function __construct(){
        parent::__construct('experience', apply_filters( 'et_experience_fields', array('name', 'from', 'to', 'current', 'position') ));
    }

    static public function get_instance(){
        if (self::$instance == null)
            self::$instance = new JE_Resume_Experience();
        return self::$instance;
    }

    /**
     * Verify fields for input or output
     */
    static public function verify_fields($fields){
       $instance = self::get_instance();
       return $instance->_verify_fields($fields);
    }

    static public function input($fields){
        $instance = self::get_instance();
        return $instance->_input($fields);
    }

    protected function _output($fields){
        $output = parent::_output($fields);
        $output['highlight'] = sprintf( __('%s <span> at </span> %s', ET_DOMAIN), $output['position'], $output['name']);
        return $output;
    }

    static public function output($fields){
        $instance = self::get_instance();
        return $instance->_output($fields);
    }
}


/**
 * Resumes ajax requests
 */
class JE_Resume_Ajax extends ET_Base{
    const AJAX_CREATE   = 'resume_create';
    const AJAX_SYNC     = 'et_resume_sync';
    const AJAX_FETCH    = 'et_fetch_resumes';
    const AJAX_APPLY    = 'et_jobseeker_apply_job';

    function __construct(){
        $this->add_ajax(self::AJAX_CREATE, 'create_resume', true, false);
        $this->add_ajax(self::AJAX_SYNC, 'resume_sync');
        $this->add_ajax(self::AJAX_FETCH, 'resume_fetch');
        $this->add_ajax(self::AJAX_APPLY, 'resume_apply');
        /*
        * Upload cv file.
        */
        $this->add_ajax('et_jobseeker_upload_resume', 'et_jobseeker_upload_resume');
    }

    /**
     * AJAX: create resume
     */
    public function create_resume(){

    }

    /**
     * AJAX method: fetch resume
     */
    public function resume_fetch(){
        //
        $request = $_REQUEST['content'];

        if( isset($_COOKIE['rand_sort_resume']) ) {
            $rand   =   $_COOKIE['rand_sort_resume'];
        } else {
            $order_by   =   array ('date', 'author', 'title', 'name', 'ID');
            $rand   =   $order_by[array_rand($order_by)];
        }

        // build params
        $params = array(
            'post_type'     => 'resume',
            'post_status'   => array('publish'),
            'orderby'       => $rand,
            'ignore_sticky_posts' => 1,
        );

        $params = wp_parse_args( $request, $params );

        foreach ($params as $key => $param) {
            if ($param === '') unset($params[$key]);
        }
        // query resume
        global $post, $wp_query;
        JE_Resume::query_resumes($params);

        // retrieve results
        $results    = array();
        $users      = array();
        while (have_posts()){
            the_post();

            $user_added = false;
            foreach ($users as $index => $user) {
                if (isset($user->ID) && $post->post_author == $user->ID){
                    $user_added = true;
                }
            }
            if (!$user_added){
                $user       = get_userdata( $post->post_author );

                $jobseeker  = JE_Job_Seeker::convert_from_user($user);

                if(!$jobseeker) continue;

                $users[]    = $jobseeker;

                $results[]  = JE_Resume::convert_from_post($post);
            }

        }

        $this->ajax_header();

        $resp = array(
            'success'   => true,
            'msg'       => __('Fetch success'),
            'data'      => array(
                'resumes'       => $results,
                'jobseekers'     => $users,
                'total_pages'   => $wp_query->max_num_pages,
                'paged'         => isset($wp_query->query_vars['paged'] ) ? $wp_query->query_vars['paged'] : 1
                )
            );

        echo json_encode($resp);
        exit;
    }

    /**
     * sync resume
    */
    public function resume_sync () {
        $resume    =   $_REQUEST['content'];
        switch ($_REQUEST['method']) {
            case 'update':
                $response    =   $this->update_resume ($resume);
                break;
            case 'create':
                $response    =   $this->insert_resume ($resume);
                break;
            case 'read':
                $response     =   $this->get_resume ($resume);
                break;
            case 'delete' :
                $response     =   $this->delete_resume ($resume);
                break;
            case 'reviewResume':
                $response      =   $this->review_resume($resume);
                break; 
            default:
                $response      =   array('success' => false , 'msg' => __("Invalid method", ET_DOMAIN));

        }

        wp_send_json( $response );
    }

    /**
     * attempt a function to update/create resume
    */
    private function insert_resume ($resume) {
        
        $resume =    JE_Resume::insert($resume);
        $post   =    get_post($resume);
       
        if($resume) {
            $response   =   array(
                'success'       => true, 
                'msg'           => __("Hooray! Your resume has been created.", ET_DOMAIN),
                'method'        => 'create',
                'data'          => array('resume' => JE_Resume::convert_from_post($post) ),
                'redirect_post' => urlencode(get_permalink($resume))
            );
        } else {
            $response   =   array(
                'success'   => false, 
                'msg'       => __("Unfortunately! Create Resume Fail :'(!", ET_DOMAIN)
            );
        }
        return $response;
    }

    /**
     * attempt a function to update/create resume
    */
    private function update_resume ( $resume ) {
        // refine data
        // refine education
        foreach (array('et_education', 'et_experience') as $type) {
            if (isset($resume[$type])){
                if(is_array($resume[$type]))
                    $data = $resume[$type];
                else 
                    $data  =   array();
                foreach ($data as $key => $value) {
                    if (empty($value['name'])) continue;

                    if ( $type == 'et_education' )
                        $data[$key] = JE_Resume_Education::input($value);
                    else 
                        $data[$key] = JE_Resume_Experience::input($value);

                    // if (isset($value['position']))
                    //     $data[$key]['position'] = $value['position'];
                }
                $resume[$type] = $data;
            }
        }

        // reset taxonomies when input nothing
        foreach (array('resume_category', 'skill', 'available') as $tax) {
            if (isset($resume[$tax]) && $resume[$tax] == '')
               $resume[$tax] = array();
        }

        if( isset($resume['et_privacy']) ) {
            if(!in_array($resume['et_privacy'], self::$privacy ) ) {
                $resume['et_privacy']    =   'public';
            }
        }

        return $this->update_resume_data ($resume);
    }

    /**
     * attempt a function to retrieve resume data
    */
    private function get_resume ($resume) {
        $resume = JE_Resume::get($resume['id']);
        if($resume) {
            $response   =   array(
                'success'   => true, 
                'msg'       => __("Hooray! Get Resume Data Successful! :)", ET_DOMAIN),
                'data'      => array('resume' => $resume ,  'jobseeker' =>  JE_Job_Seeker::convert_from_user(get_userdata($resume->post_author)))
            );
        } else {
            $response   =   array(
                'success'   => false,
                'msg'       => __("Unfortunately! You can not get your Resume details.", ET_DOMAIN)
            );
        }
        return $response;

    }

    private function delete_resume ($resume) {
        $return =   JE_Resume::delete_resume($resume['id'], true );
        if($return) {
            $response   =   array(
                'success'   => true,
                'msg'       => __("Delete resume successfull!", ET_DOMAIN)
            );
        } else {
            $response   =   array(
                'success'   => false,
                'msg'       => __("Error! Uncaughtable error when delete resume!", ET_DOMAIN)
            );
        }
        return $response;
    }

    private function review_resume ($resume) {
        if(!current_user_can('manage_options')) {
            return array(
                'success'   => false,
                'msg'       => __("Permission Denied!", ET_DOMAIN)
            );
        }

        return $this->change_resume_status ($resume['id'], $resume['status'] , $resume);

    }

    private function change_resume_status ( $resume_id, $new_status , $args ) {
        $response    =   $this->update_resume_data (array('ID' => $resume_id, 'post_status' => $new_status ));

        if($response['success']) {
            $resume     =   $response['data']['resume'];
            $jobseeker  =   $response['data']['jobseeker'];
            switch ($new_status) {
                case 'publish':
                    /**
                     * message when publish resume
                    */
                    $response['msg']    =   __("The resume is now published active.", ET_DOMAIN) ;
                    do_action ('et_approve_resume',$resume, $jobseeker );
                    break;
                case 'reject':
                    /**
                     * message when reject resume
                    */
                    $response['msg']    =   __("The resume is now rejected.", ET_DOMAIN);
                    do_action('et_reject_resume', $resume, $jobseeker ,$args );
                    break;
                default: 
                    do_action( 'et_change_resume_status', $resume, $jobseeker );
            }
        }
        return $response;
    }

     private function update_resume_data ($resume) {
         // update
        $resume = JE_Resume::update( $resume );
        if($resume) {
            $response   =   array(
                'success'   => true,
                'msg'       => __("Your resume has been updated.", ET_DOMAIN),
                'data'      => array('resume' => $resume ,  'jobseeker' => JE_Job_Seeker::convert_from_user(get_userdata($resume->post_author)) )
            );
        } else {
            $response   =   array(
                'success'   => false,
                'msg'       => __("Unfortunately, resume update failed.", ET_DOMAIN)
            );
        }
        return $response;
    }
     /**
     * apply for job by resume
     */
    public function resume_apply() {
        global $user_ID;
        $this->ajax_header();
        $attach_id = isset($_POST['attach_id']) ? $_POST['attach_id'] : false;
        try {
            parse_str( $_POST['content'], $args );

            if ( empty($args['_ajax_nonce']) && wp_verify_nonce( $args['_ajax_nonce'], 'apply_job' ));

            // if job seeker id doesn't exist, cancel
            if ( empty($args['jobseeker_id']) ) throw new Exception(__("Can't find jobseeker", ET_DOMAIN));

            // if job seeker id doesn't exist, cancel
            if ( empty($args['job_id']) ) throw new Exception(__("There is error occurred!", ET_DOMAIN));

            $job_option =   ET_JobOptions::get_instance();
            $useCaptcha =   $job_option->use_captcha () ;

            if($useCaptcha) {
                $captcha    =   ET_GoogleCaptcha::getInstance();
                if( !$captcha->checkCaptcha( $args['recaptcha_challenge_field'] , $args['recaptcha_response_field']  ) ) {
                    throw new Exception(__("You enter an invalid captcha!", ET_DOMAIN), 400);
                }
            }

            $job = get_post( $args['job_id'] );

            // find resume via job seeker id
            $resumes =  get_posts(array(
                'post_type'     => 'resume',
                'author'        => $args['jobseeker_id'],
                'numberposts'   => 1
            ));

            // if no resume found, cancel
            if ( empty($resumes) ) throw new Exception(__("It seems you don't have any resume", ET_DOMAIN));

            $resume             = $resumes[0];
            $args['resume_id']  = $resume->ID;

            // unset unwanted variable
            unset($args['_ajax_nonce']);

            $args['post_author']   =    $user_ID;
            $args['company_id']    =    $job->post_author;

            JE_Job_Seeker::set_accessible_list ($user_ID, $job->post_author);

            $this->add_action('et_insert_application', 'action_insert_application', 10, 2);
            $app = et_insert_application($args);
            $this->add_action('et_insert_application', 'action_insert_application', 10, 2);

            if (!is_wp_error( $app ) && is_numeric($app)){
                $this->send_application_mail($args, $template= array(), $attach_id);

                // update post parent for attachement
                if($attach_id)
                    wp_update_post(array('ID'=>$attach_id, 'post_parent' => $app));

                $resp = array(
                    'success'   => true,
                    'data'      => array(
                        'application_id' => $app
                    ),
                    'msg'   => __("Congratulations! Your application has been sent. Good luck!", ET_DOMAIN)
                );
            } else if ( is_wp_error( $app )) {
                throw new Exception( $app->get_error_message() );
            } else {
                throw new Exception(__("Cannot create application", ET_DOMAIN));
            } 
        } catch (Exception $e) {
            $resp = array(
                'success'   => false,
                'msg'       => $e->getMessage()
            );
        }
        echo json_encode($resp);
        exit;
    }

    /**
    *action et_jobseeker_upload_resume
    * For a jobseeker upload a resume when submit apply job form.
    *
    * @since 2.9.2
    *
    **/

    function et_jobseeker_upload_resume(){
        global $user_ID, $wpdb;
        $result = array('success' => false, 'msg' => __('You don\'t have permission to access!',ET_DOMAIN ));
        $job_id = isset($_REQUEST['job_id']) ? $_REQUEST['job_id'] : 0;

        /*
        * check permision
        */
        $applied =  $wpdb->get_row("SELECT * FROM $wpdb->posts where post_type = 'application' AND  post_parent = $job_id AND post_author = ".$user_ID);
        if($applied != null){
            $result['msg'] = __('You have applied on this job.',ET_DOMAIN );
            wp_send_json($result);
        }

        if( !is_user_logged_in() ){
            $result['msg'] = __('You must log in to update resume.',ET_DOMAIN );
            wp_send_json($result);
        }

        // action upload cv here

        if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
            try {

                $attach_id = et_process_file_upload($_FILES['resume_upload'],$user_ID, 0 , array(
                        'pdf'       => 'application/pdf',
                        'rtf'       => 'application/rtf',
                        'txt'       => 'text/plain',
                        'doc|docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

                    ) );
                if( !is_wp_error($attach_id) ){
                }
                $result = array(
                    'success'   => true,
                    'msg'       => __('You have uploaded cv successfully', ET_DOMAIN),
                    'data'      => array_merge((array)get_post($attach_id) , array('post_title'=>$_FILES['resume_upload']['name']) )
                    );
            } catch (Exception $e) {
                $result = array(
                    'success'   => false,
                    'msg'       => __('Upload Failed! Maybe your image size is too large!',ET_DOMAIN)
                    );
            }

        //end upload file



        wp_send_json($result);
    }

    public function action_insert_application ( $application_id, $args ){
        if ( isset($args['resume_id']) )
            update_post_meta( $application_id, 'et_resume_id', $args['resume_id'] );
        if ( isset($args['jobseeker_id']) )
            update_post_meta( $application_id, 'et_jobseeker_id', $args['jobseeker_id'] );
    }

    private function send_application_mail($args, $template_args = array(), $attach_id = false){
        $job            = get_post($args['job_id']);
        $company        = get_userdata( $job->post_author );
        $jobseeker      = get_userdata( $args['jobseeker_id'] );
        $resume         = get_post( $args['resume_id'] );
        $mail_template  = JE_Resumes_Mailing::get_instance();
        $company_vari   = array(
            'display_name'  => $company->display_name,
            'job_title'     => $job->post_title,
            'seeker_name'   => $args['emp_name'],
            'seeker_mail'   => $args['emp_email'],
            'seeker_note'   => $args['apply_note'],
            'resume_link'   => get_permalink( $resume ),
            'job_link'      => get_permalink( $args['job_id'] ),
            'seeker_note'   => isset($args['apply_note']) ? $args['apply_note'] :'',
            'profile_link'  => get_permalink( $resume )
            );

        $jobseeker_vari   = array(
            'display_name'  => $jobseeker->display_name,
            'job_title'     => $job->post_title,
            'seeker_name'   => $args['emp_name'],
            'seeker_mail'   => $args['emp_email'],
            'seeker_note'   => $args['apply_note'],
            'resume_link'   => get_permalink( $resume ),
            'job_link'      => get_permalink( $args['job_id'] ),
            'profile_link'  => get_permalink( $resume )
        );

        $mail_template = JE_Resumes_Mailing::get_instance();
        $template   = $mail_template->get_template('apply');
        $message    = $template;
        foreach ($company_vari as $key => $arg) {
                $message = str_replace("[$key]", $arg, $message);
        }
        $message = stripslashes(html_entity_decode($message));

        // $message_seeker    = $template;
        // foreach ($jobseeker_vari as $key => $arg) {
                $message_seeker = $message;
        // }
        /**
         * add an announce text
        */
        $head           =   sprintf(__("<p>Dear %s,</p> <p>You have sent your application successfully for this job: %s. Here is the email which was sent to the employer.</p>",ET_DOMAIN),ucfirst($jobseeker_vari['display_name']),$job->post_title); 
        $message_seeker = stripslashes(html_entity_decode($message_seeker));
        $message_seeker =   $head . $message_seeker ;

        $company_email  =   et_get_post_field( $args['job_id'], 'apply_email');
        $company_email  =   ($company_email != '')? $company_email : $company->user_email;

        $blogname       =   get_option('blogname');
        $mail_to_company = array(
            'to'        => $company_email,
            'subject'   => sprintf(__("Application for %s you posted on %s",ET_DOMAIN), $job->post_title,  $blogname ),
            'message'   => et_get_mail_header() . $message. et_get_mail_footer(),
            'headers'   => 'MIME-Version: 1.0' . "\r\n" .
                        'Content-type: text/html; charset=utf-8' . "\r\n" .
                        "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n"
            );
        $mail_to_employee = array(
            'to'        => $args['emp_email'],
            'subject'   => sprintf(__("Application for %s you sent through %s",ET_DOMAIN),$job->post_title,$blogname) ,
            'message'   => et_get_mail_header() . $message_seeker . et_get_mail_footer(),
            'headers'   => 'MIME-Version: 1.0' . "\r\n" .
                        'Content-type: text/html; charset=utf-8' . "\r\n" .
                        "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n"
            );
        $attachment = '';
        if($attach_id)
            $attachment = array(get_attached_file($attach_id));
        wp_mail( $mail_to_company['to'], $mail_to_company['subject'], $mail_to_company['message'], $header ='', $attachment ); 
        wp_mail( $mail_to_employee['to'], $mail_to_employee['subject'], $mail_to_employee['message'], $mail_to_employee['headers'] );
    }

}

class ET_TaxFactory {
     /**
    * prevent this class being instantiated
    */
    private function __construct() {}
    /**
    * return the correct instance of the tax to be handled
    * @param string $type passing the type like this no longer ties it to use the $_GET global
    * @return ET_Tax_Base|null we do not care what object is to be returned as long as it implements the ET_Tax_Base interface
    */
    public static function get_instance($type) {
        switch ($type) {
            case 'available': return new JE_Jobseeker_Available();
            case 'resume_category': return new JE_Jobseeker_Position();
            default: return null;
        }
    }
}

class JE_TaxFactory extends ET_TaxFactory {
    /**
    * prevent this class being instantiated
    */
    private function __construct() {}
    /**
    * return the correct instance of the tax to be handled
    * @param string $type passing the type like this no longer ties it to use the $_GET global
    * @return ET_Tax_Base|null we do not care what object is to be returned as long as it implements the ET_Tax_Base interface
    */
    public static function get_instance($type) {
        switch ($type) {
            case 'available':
                return new JE_Jobseeker_Available();
            case 'resume_category':
                return new JE_Jobseeker_Position();
            default: return null;
        }
    }
}


class JE_Tax_Base_Action extends ET_Tax_Base {
    function __construct () {
        add_action ('wp_insert_term',  array($this, 'refesh_terms') );
        add_action ('wp_ajax_je_change_resume_tax_title', array(&$this, 'update_resume_tax_title'));//danng
    }

    function update_resume_tax_title () {
        header( 'HTTP/1.0 200 OK' );
        header( 'Content-type: application/json' );
        $tax    =   JE_TaxFactory::get_instance ($_REQUEST['content']['name']);
        $tax->set_title ($_REQUEST['content']['value']);
        echo json_encode (array('success' => true ) );
        exit;
    }
}


class JE_Jobseeker_Available extends ET_TaxType {

    protected $_tax_name    = 'available';
    protected $_order       = 'et_available_order';
    protected $_transient   = 'job_availables';
    protected $_tax_label   =  'Job Available';
    protected $_color       =  'job_available_colors';

    //function __construct () { }

    // function get_term_link ($term) {
    //     return get_term_link( $term,$this->_tax_name );
    // }
    public static function get_title(){
        return get_option('je_resume_available_title',__(' AVAILABLE FOR',ET_DOMAIN));
    }
    public static function set_title($value){
        update_option( 'je_resume_available_title', $value );
    }

}

class JE_Jobseeker_Available_Ajax extends JE_Jobseeker_Available {
    function __construct () {
        add_action ('wp_ajax_et_sort_available', array(&$this, 'sort_terms'));
        add_action ('wp_ajax_et_sync_availale', array(&$this, 'sync_term'));
        add_action ('wp_ajax_et_update_available_color', array(&$this, 'update_tax_color'));

    }
}


class JE_Jobseeker_Position extends ET_TaxCategory {
    protected $_tax_name    = 'resume_category';
    protected $_order       = 'et_resume_category_order';
    protected $_transient   = 'resume_category';
    protected $_tax_label   =  'Resume Category';

    function __construct () {
    }

    function mobile_filter_list($parent = 0, $positions = false){
        if ( !$positions )
            $positions = $this->get_terms_in_order();

        foreach ($positions as $pos) {
            if ( $pos->parent != $parent ) continue;
            ?>
            <li>
                <a data="<?php echo $pos->slug ?>" data-name="resume_category" class="pick-param ui-list ui-link"><?php echo $pos->name ?></a>
            </li>
            <?php
            $has_children = false;
            foreach ($positions as $child) {
                if ( $child->parent == $pos->term_id ){
                    $has_children = true; break;
                }
            }
            if ($has_children){
                echo '<li><ul>';
                $this->mobile_filter_list($pos->term_id, $positions);
                echo '</ul></li>';
            }
        }
    }

    public static function get_title(){
        return get_option('je_resume_category_title',__('RESUME CATEGORIES',ET_DOMAIN)) ;
    }

    public static function set_title($value){
        update_option( 'je_resume_category_title', $value );
    }

}

class JE_Jobseeker_Position_Ajax extends JE_Jobseeker_Position {
    function __construct () {
        add_action ('wp_ajax_et_sort_resume_category', array(&$this, 'sort_terms'));
        add_action ('wp_ajax_et_sync_resume_category', array(&$this, 'sync_term'));
    }
}