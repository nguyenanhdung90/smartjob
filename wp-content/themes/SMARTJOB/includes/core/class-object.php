<?php

/**
 * Declare post field information
 *
 * @since 1.0
 */
class ET_PostField{
    var $name           = '';
    var $title          = '';
    var $description    = '';
    var $type           = '';
    var $post_type      = '';
    var $prefix         = 'et_';
    var $metakey        = '';

    private $fields = array('name', 'title','description','type', 'post_type' );

    function __construct($name, $args = array()){
        $args = wp_parse_args($args, array(
            'title'         => '',
            'description'   => '',
            'type'          => 'string',
            'post_type'     => array()
        ));

        $this->name         = $name;
        $this->title        = $args['title'];
        $this->description  = $args['description'];
        $this->type         = $args['type'];
        $this->post_type    = $args['post_type'];
        $this->metakey      = $this->prefix . $this->name;
    }

    public function __get($name){
        if ( in_array($name, $this->fields) ){
            return $this->$name;
        }
        return false;
    }
}

/**
 * Post field's factory
 * 
 * @since 1.0
 */
class ET_PostFields_Factory{
    var $fields = array();

    public function __construct(){

    }

    public function register($name, $args = array()){
        $args = wp_parse_args($args, array(
            'title'         => '',
            'description'   => '',
            'type'          => 'string',
            'post_type'     => array()
        ));

        $this->fields[$name] = new ET_PostField($name, $args);
    }

    public function get_fields_by_post_type($post_type){
        $return_fields = array();
        foreach ($this->fields as $name => $field) {
            if ( (!is_array($field->post_type) && $field->post_type == 'all') ||
                is_array($field->post_type) && in_array($post_type, $field->post_type) ){
                $return_fields[$name] = $field;
            }
        }
        return $return_fields;
    }

    public function save_fields($post_id, $save_fields){
        foreach ($save_fields as $key => $value) {
            $this->save_field($post_id, $key, $value);
        }
    }

    public function save_field($post_id, $name, $value){
        global $et_global;
        //if( isset( $this->fields[$name]->metakey ) ){
        update_post_meta($post_id, $et_global['db_prefix'] . $name, $value);
        //}
    }

    public function get_field($post_id, $name){
        global $et_global;
        return get_post_meta($post_id, $et_global['db_prefix'] . $name, true);
    }

    public function has_field($post_type, $name){
        if ( isset($this->fields[$name]) && in_array($post_type, (array)$this->fields[$name]->post_type ) ){
            return true;
        }
        else 
            return false;
    }
}

global $et_global;
$et_global['post_fields'] = new ET_PostFields_Factory();

/**
 * Reigster field for post type
 * @param $name field name, alphabel & number only
 * @param $args argument need for field:
 *  - title 
 *  - description
 *  - type: data type of field (int, string, decimal)
 *  - post_type: array of post type that field is belonged to
 * @since 1.0
 */
function et_register_post_field($name, $args = array()){
    global $et_global;
    $factory = $et_global['post_fields'];

    $args = wp_parse_args($args, array(
        'title'         => '',
        'description'   => '',
        'type'          => 'string',
        'post_type'     => array()
    ));

    $factory->register($name, $args);
}

/**
 * Update field for post
 * @param $fields saving fields
 * @since 1.0
 */
function et_insert_post($fields = array()){
    global $et_global;
    $factory = $et_global['post_fields'];
    $extra_fields = array();

    // get post type
    if ( isset($fields['ID']) && $post = get_post($fields['ID']) ){ 
        $post_type = $post->post_type;
    } 
    else {
        $post_type = $fields['post_type'];
    }

    foreach ($fields as $key => $value) {
        if ( $factory->has_field($post_type, $key) ){
            $extra_fields[$key] = $value;
            unset($fields[$key]);
        }
    }

    // update fields
    if ( empty($fields->ID) )
        $post_id = wp_insert_post($fields, true);
    else 
        $post_id = wp_update_post($fields, true);
    if ( !($post_id instanceof WP_Error) ){
        // update extra fields
        $factory->save_fields($post_id, $extra_fields);
    }
    return $post_id;
}

/**
 * Update field for post
 * @param $fields saving fields
 * @since 1.0
 */
function et_update_post($fields = array()){
    global $et_global;
    $factory = $et_global['post_fields'];
    $extra_fields = array();

    // get post type
    if ( isset($fields['ID']) && $post = get_post($fields['ID']) ){ 
        $post_type = $post->post_type;
    } 
    else {
        $post_type = $fields['post_type'];
    }

    foreach ($fields as $key => $value) {
        if ( $factory->has_field($post_type, $key) ){
            $extra_fields[$key] = $value;
            unset($fields[$key]);
        }
    }

    // update fields
    $post_id = wp_update_post($fields, true);
    if ( !($post_id instanceof WP_Error) ){
        // update extra fields
        $factory->save_fields($post_id, $extra_fields);
    }
    return $post_id;
}

/**
 * get field value of a post
 * @param $post_id 
 * @param $name field's name
 * 
 * @since 1.0
 *
 */
function et_update_post_field($post_id, $name, $value){
    global $et_global;
    $factory = $et_global['post_fields'];

    return $factory->save_field($post_id, $name, $value);
}

/**
 * get field value of a post
 * @param $post_id 
 * @param $name field's name
 * 
 * @since 1.0
 *
 */
function et_get_post_field($post_id, $name){
    global $et_global;
    $factory = $et_global['post_fields'];

    return $factory->get_field($post_id, $name);
}

/**
 * get fields available in post type
 * @param $post_type : string post type 
 */
function et_get_post_type_fields($post_type){
    global $et_global;
    $factory = $et_global['post_fields'];
    return $factory->get_fields_by_post_type($post_type);
}

/**
 * register post type will have been count views
 * @param $post_type : string post type 
 * @param $role : user role : when user has $role visit post,post views will be increse 1
 *                  anonym for unregister user
 */
function et_register_post_type_count_views ( $post_type , $role = array() ) {
    if($post_type == ''  ) return ;
 
    if(!is_array($post_type) )  $post_type  =   (array)$post_type;
    if(!is_array($role) )   $role   =   (array)$role;

    et_register_post_field('post_views', array(
        'title' => 'Post view',
        'description' => 'Store post views',
        'type' => 'int',
        'post_type' => $post_type
    ));

    global $et_global;

    $et_global['post_type_views']   = array('post_type' =>  $post_type, 'role' => $role ) ;

}
/**
 * count post views and store
*/
function et_count_post_views () {
    if( is_single() ) {
        global $post, $et_global, $user_ID;
        $post_type_views    =   empty($et_global['post_type_views']) ? array() : $et_global['post_type_views'];

        if($post->post_status == 'publish' && isset($post_type_views['post_type']) && in_array( $post->post_type ,$post_type_views['post_type'] )) {

            if(!$user_ID)  $role    =   'anonym';
            else {
                $user   =   new WP_User($user_ID);
                $roles  =   $user->roles; 
                $role   =   array_pop($roles);
            }

            if(!empty($post_type_views['role']) && !in_array($role, $post_type_views['role']) ) return ;

            $views  =   et_get_post_field ($post->ID, 'post_views');
            $key    =   "et_post_".$post->ID."_viewed";

            if(!isset($_COOKIE[$key]) ||  $_COOKIE[$key] != 'on') {
                et_update_post_field ($post->ID, 'post_views', $views + 1 ) ;
                setcookie($key, 'on', time()+3600, "/");
            }
        }
    }
}
add_action('template_redirect', 'et_count_post_views' );

/**
 *  get post vỉews
 *  @param $post_id : int
 * @return int post views
 */
function et_get_post_views ($post_id) {
    $view = et_get_post_field ($post_id, 'post_views' );
    return (int)$view;
}
/**
 * Engine Theme taxonomy base class
*/
class ET_Tax_Base {
    protected $prefix   =   'et_';

    protected $_tax_name;
    protected $_tax_label;
    protected $_order;
    protected $_transient;

    protected $_term_in_order;
    /**
     * get term in ordered
    */
    function get_terms_in_order ($args = array()) {
        $this->refesh_terms($args);
        if (get_transient($this->_transient) == false)
           $this->refesh_terms();
        $this->_term_in_order    =   get_transient($this->_transient);
        return $this->_term_in_order;
    }
    /**
     * refesh transient
    */
    function refesh_terms ($args = array()) {

        $ordered    = array();
        $args       = wp_parse_args( $args,  array('orderby' => 'id', 'order' => 'ASC','hide_empty' => false ,'pad_counts' => true));
        $categories = get_terms($this->_tax_name, $args);

        $order      = (array)get_option($this->_order);

        if ($order) {
            foreach ($order as $pos) {
                foreach ($categories as $key => $cat) {
                    if ( isset($pos['item_id']) && $cat->term_id == $pos['item_id']){
                        $ordered[] = $cat;
                        unset($categories[$key]);
                    }
                }
            }
            if (count($categories) > 0)
                foreach ($categories as $cat) {
                    $ordered[] = $cat;
                }
            set_transient($this->_transient, $ordered);
        }else {
            set_transient($this->_transient, $categories);
        }
    }

    function sort_terms () {
        $resp = array();
        $content    =   $_REQUEST['content'];
        if (!$content['order']) 
            return false;

        // update parent
        if (isset($content['id']) && isset($content['parent'])){
            $parent = $content['parent'] ? $content['parent'] : 0;
            wp_update_term($content['id'], $this->_tax_name, array('parent' => $parent));
        }
        if(isset($content['json']) && $content['json'] ) {
            $order  =   str_replace("\\", "",$content['order']);
            $order  =   json_decode( $order, true );
        }else {
            $order = $content['order'];
        }
        
        // update order
        // 
        update_option($this->_order, $order); 

        $this->refesh_terms();

        $resp = array(
            'success'   => true,
            'msg'       => ''
        );
        header( 'HTTP/1.0 200 OK' );
        header( 'Content-type: application/json' );
        echo json_encode($resp) ;
        exit;
    }

    function delete_term ($data) {
        // delete term with wp_delete_term
        if ($data['default_cat']){
            wp_delete_term( $data['id'], $this->_tax_name, array( 'default' => $data['default_cat'] )); // get_option('default_job_category')));

            // raise an action after delete job category
            do_action('et_delete_'.$this->_tax_name, $data['id']);

            $resp = $this->build_success_ajax_response(array(), sprintf(__('%s has been deleted!', ET_DOMAIN), $this->_tax_label ) );
        } else {
            $resp = build_error_ajax_response(null, '');
        }
        return $resp;
    }

    function create_term ($data) {
         $args = array();
        if ( !empty($data['parent']) )
            $args['parent'] = $data['parent'];

        // insert new term
        if(!empty($args))
            $result = wp_insert_term( $data['name'] , $this->_tax_name , $args);
        else 
            $result = wp_insert_term( $data['name'] , $this->_tax_name );

        // if term can't be inserted throw an error
        if ( is_wp_error($result) )  
            throw new Exception(sprintf(__('Error when trying to add new %s!', ET_DOMAIN), strtolower($this->_tax_label) ), 400);
        /**
         * update term color if have request
        */
        if ( !empty($result['term_id']) && (isset($data['color']) && $data['color'] ) )
            $this->update_term_color( $result['term_id'], $data['color'] );
        else $data['color'] = '';
        // build response for client
        $resp = $this->build_success_ajax_response( array( 
                'id' => $result['term_id'],
                'name' => $data['name'],
                'color' => !empty($data['color']) ? $data['color'] : 0
            ), sprintf(__('%s has been inserted', ET_DOMAIN), $this->_tax_label ) );

        return $resp;
    }

    function update_term ($data) {
        $args = $data;
        unset($args['id']);

        $result = wp_update_term( $data['id'], $this->_tax_name, $args );

        // build response for client
        $resp = $this->build_success_ajax_response( array( 
            'id'        =>  $result['term_id'],
            'name'      =>  $data['name']
        ), sprintf(__('%s has been updated', ET_DOMAIN), $this->_tax_label ) );

        return $resp;

    }

    function sync_term () {
        header( 'HTTP/1.0 200 OK' );
        header( 'Content-type: application/json' );
        // ajax class should overide this function
        try {
            // return false if request method is empty
            if ( empty($_REQUEST['method']) ) throw new Exception(__('There is an error occurred', ET_DOMAIN), 400);

            $method = empty( $_REQUEST['method'] ) ? '' : $_REQUEST['method'] ;
            $data   = $_REQUEST['content'];

            switch ($method) {
                case 'delete':
                    $resp   =   $this->delete_term ($data);
                    break;

                case 'create' :
                    $resp = $this->create_term ($data);
                    break;

                case 'update' :
                    $resp   =   $this->update_term($data);
                    break;
                default:
                    throw new Exception(__('There is an error occurred', ET_DOMAIN), 400);
                    break;
            }   
            // refresh sorted job categories
            $this->refesh_terms();
        } catch (Exception $e) {
            $resp = $this->build_error_ajax_response(array(), $e->getMessage() );
        }
        echo json_encode( $resp );
        exit;
    }

    function get_terms ($args = array()) {
        $args   =   wp_parse_args($args, array('hide_empty' => false));
        return get_terms($this->_tax_name, $args);
    }    

    function get_term_link ($term) {
        return get_term_link( $term,$this->_tax_name );
    }

    function print_confirm_list () {
        if(!is_array($this->_term_in_order) ) $this->get_terms_in_order ();
    ?>
        <script type="text/template" id="temp_<?php echo $this->_tax_name ?>_delete_confirm">
            <div class="moved-tax">
                <span><?php _e('Move posts to', ET_DOMAIN) ?></span>
                <div class="select-style et-button-select">
                    <select name="move_<?php echo $this->_tax_name ?>" id="move_<?php  echo $this->_tax_name ?>">
                    
                    <?php foreach ($this->_term_in_order as $term ) {  ?>
                            <option value="<?php echo $term->term_id ?>"><?php echo $term->name ?></option>
                    <?php } ?>
                    
                    </select>
                </div>
                <button class="backend-button accept-btn"><?php _e("Accept", ET_DOMAIN); ?></button>
                <a class="icon cancel-del" data-icon="*"></a>
            </div>
        </script>
    <?php 
    }

    /**
     * Build general success response for ajax request
     * @param $data returned data 
     * @param $msg returned message
     * @param $code returned code
     * @since 1.0
     */
    function build_success_ajax_response($data, $msg = '', $code = 200){
        return array(
            'success'   => true,
            'code'      => $code,
            'msg'       => $msg,
            'data'      => $data
            );
    }

    function build_error_ajax_response($data, $msg = '',$code = 400){
        return array(
            'success'   => false,
            'code'      => $code,
            'msg'       => $msg,
            'data'      => $data
            );
    }
}

/**
 * tax category 
*/
class ET_TaxCategory extends ET_Tax_Base {
     /**
     * print backend term list, can override if change template
    */
    function print_backend_terms () {
        ?>
        <ul class="list-job-input list-tax category list-job-categories cat-sortable tax-sortable" data-tax="<?php echo $this->_tax_name ?>">
        <?php 
            $this->print_backend_terms_li () ;
        ?>
        </ul>
        <ul class="list-job-input category add-category ">
            <li class="tax-item">
                <form class="new_tax" action="" data-tax='<?php echo $this->_tax_name ?>'>
                    <div class="controls controls-2">
                        <div class="button">
                            <span class="icon" data-icon="+"></span>
                        </div>
                    </div>
                    <div class="input-form input-form-2 color-default">
                        <input class="bg-grey-input" placeholder="<?php _e('Add a category', ET_DOMAIN) ?>" type="text" />
                    </div>
                </form>
            </li>
        </ul>
        <?php 
    } 
    /**
     * print backend term list, can override if change template
    */
    function print_backend_terms_li($parent = 0, $positions = false) {
        if ( !$positions )
            $positions = $this->get_terms_in_order();
        foreach ($positions as $job_pos) {
            if ( $job_pos->parent == $parent ){
            ?>
            <li class="tax-item" id="tax_<?php echo $job_pos->term_id ?>">
                <div class="container">
                    <div class="sort-handle"></div>
                    <div class="controls controls-2">
                        <a class="button act-open-form" rel="<?php echo $job_pos->term_id ?>"  title="<?php _e('Add sub tax for this tax', ET_DOMAIN) ?>">
                            <span class="icon" data-icon="+"></span>
                        </a>
                        <a class="button act-del" rel="<?php echo $job_pos->term_id ?>">
                            <span class="icon" data-icon="*"></span>
                        </a>
                    </div>
                    <div class="input-form input-form-2">
                        <input class="bg-grey-input tax-name" rel="<?php echo $job_pos->term_id ?>" type="text" value="<?php echo $job_pos->name ?>" />
                    </div>
                </div>
                <ul>
                    <?php $this->print_backend_terms_li($job_pos->term_id, $positions); ?>
                </ul>
            </li>
            <?php
            } // end if
        } // end foreach
    }

}
/**
 * tax have color
*/
class ET_TaxType extends ET_Tax_Base {
    protected $_color = 'type-color';

    function set_color ($colors) {
        //this function should be override if tax have color
        update_option($this->prefix . $this->_color, $colors);

    }
    function get_color () {
        // this function should be override if tax have color
        return (array) get_option($this->prefix .  $this->_color , array());
    }

    function update_tax_color () {

        $resp = array();
        if ( !empty($_REQUEST['content']['term_id']) && !empty($_REQUEST['content']['color']) ){
            $this->update_term_color($_REQUEST['content']['term_id'], $_REQUEST['content']['color']);
            $resp = array(
                'success'   => true,
                'msg'       => sprintf(__('%s color has been updated', ET_DOMAIN), $this->_tax_label )
                );
        }
        else {
            $resp = array(
                    'success'   => false,
                    'msg'       => __("An error has occurred!", ET_DOMAIN)
                    );
        }

        header( 'HTTP/1.0 200 OK' );
        header( 'Content-type: application/json' );
        echo json_encode( $resp );
        exit;
    }

    function update_term_color ($term_id, $color ) {
        $colors = $this->get_color();

        $colors[$term_id] = $color;
        $this->set_color($colors);
    }

     /**
     * print backend term list, can override if change template
    */
    public function print_backend_terms ($parent = 0, $positions = false) {
        $availables =   $this->get_terms_in_order();
        $colors = $this->get_color();
        ?>
        <ul class="list-job-input list-tax jobtype-sortable tax-sortable" data-tax="<?php echo $this->_tax_name ?>">
            <?php
            foreach ($availables as $available ) { ?>
                <li data-tax="<?php echo $this->_tax_name ?>" id="<?php echo $this->_tax_name ?>_<?php echo $available->term_id?>" class="tax-item <?php echo $this->_tax_name ?>-<?php echo $available->term_id ?>  ui-no-nesting" data="<?php echo $available->term_id ?>">
                    <div class="container">
                        <div class="sort-handle"></div>
                        <div class="controls controls-1">
                            <a class="button act-del" rel="<?php echo $available->term_id ?>">
                                <span class="icon" data-icon="*"></span>
                            </a>
                        </div>
                        <div class="input-form input-form-1" data-action="et_update_<?php echo $this->_tax_name ?>_color">
                            <div class="cursor <?php echo isset($colors[$available->term_id]) ? 'color-' . $colors[$available->term_id] : '' ?>"><span class="flag"></span></div>
                            <input class="bg-grey-input tax-name <?php echo isset($colors[$available->term_id]) ? 'color-' . $colors[$available->term_id] : '' ?>" rel="<?php echo $available->term_id ?>" type="text" value="<?php echo $available->name ?>" />
                        </div>
                    </div>
                </li>
            <?php }
            ?>
            </ul>
            <ul class="list-job-input add-category">
                <li class="job-type new-job-type">
                    <form class="new_tax" action="" data-tax='<?php echo $this->_tax_name ?>'>
                        <div class="controls controls-1">
                            <a class="button">
                                <span class="icon" data-icon="+"></span>
                            </a>
                        </div>
                        <div class="input-form input-form-1">
                            <div class="cursor color-26" data="26"><span class="flag"></span></div>
                            <input class="bg-grey-input" placeholder="<?php  _e('Add a job type', ET_DOMAIN); ?>" type="text" />
                        </div>
                    </form>
                </li>
            </ul>
        <?php 
    }

}