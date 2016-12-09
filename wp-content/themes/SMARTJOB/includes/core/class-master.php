<?php 
/**
 * Initialization class which will be extend in every theme to initialize 
 * @since 1.0
 * @author toannm
 */
abstract class ET_Engine{
	protected $styles;
	protected $scripts;
	protected $admin_scripts;
	protected $post_types;
	protected $taxonomies;
	protected $libraries;
	protected $registered_libraries;
	protected $wizard_status;

	// declare some must-have function in the child classes
	abstract protected function init();
	abstract protected function pre_get_posts($query);

	// constructor
	function __construct(){
		//
		// Add hook init
		add_action('init', array($this, 'master_init') );
		add_action('pre_get_posts', array($this, 'pre_get_posts'));

		add_action('wp_print_styles', array($this, 'master_print_styles') );
		add_action('wp_print_scripts', array($this, 'master_print_scripts'));
		add_action('admin_enqueue_scripts', array($this, 'master_admin_enqueue_scripts'));
		add_action('after_setup_theme', array($this,'after_setup_theme'));
		//add_action('wp_print_scripts', array($this, 'print_scripts') );
		$this->wizard_status = get_option('et_wizard_status', 0);

		$this->registered_libraries = array(
			'js' => array(),
			'style' => array()
			);
	}

	public function __set($name, $value){
		switch ($name) {
			case 'wizard_status':
				$this->wizard_status = $value;
				update_option('et_wizard_status', $value ? 1 : 0);
				break;
			default:
				$this->$name = $value;
				break;
		}
	}

	/**
	 * Catch a hook init in master class
	 * In child class, you must declare this method
	 *
	 * @since 1.0
	 * @author toannm
	 */
	public function master_init(){

		$http		=	et_get_http();

		// register post types used in theme
		foreach ((array)$this->post_types as $posttype => $args) {
			if ( is_array($args) )
				register_post_type( $posttype, $args);
		}

		// register taxonomies used in theme
		foreach ((array)$this->taxonomies as $taxo => $info) {
			if ( is_array($info['args']) && is_array($info['object_type']) )
				register_taxonomy($taxo, $info['object_type'], $info['args']);
		}

		// register base scripts
		$base_scripts = array(
			'modernizr'	=> array(
				'src'		=> "$http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js",
				'fallback'	=> FRAMEWORK_URL . '/js/lib/modernizr.min.js'
				),
			'et_underscore'	=> array(
				'src'		=> "$http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.3.3/underscore-min.js",
				'fallback'	=> FRAMEWORK_URL . '/js/lib/underscore-min.js'
				),
			'et_backbone'	=> array(
				'src'		=> "$http://cdnjs.cloudflare.com/ajax/libs/backbone.js/0.9.2/backbone-min.js",
				'fallback'	=> FRAMEWORK_URL . '/js/lib/backbone-min.js'
				),
			);

		// auto enqueue javascript libraries, use CDN; fallback to local file when needed
		if( !empty($base_scripts) ){
			foreach ( $base_scripts as $handle => $script ){
				//$test_url = @fopen( $script['src'], 'r' ); // test parameters
				if (!wp_script_is( $handle, 'queue')){
					wp_register_script( $handle, $script['src'] , array(), false, true);
					//wp_register_script( $handle, ($test_url !== false) ? $script['src'] : $script['fallback'] , array(), false, true); // register the proper file
				}
			}
		}

		// normal scripts
		$this->scripts = wp_parse_args( array(
			'et_colorpicker' => array(
				'src' 		=> FRAMEWORK_URL . '/js/lib/colorpicker.js',
				'deps' 		=> array('jquery'),
				'ver' 		=> false,
				'in_footer' => true,
			),
			'et_autosize' => array(
				'src' 		=> FRAMEWORK_URL . '/js/lib/jquery.autosize.js',
				'deps' 		=> array('jquery'),
				'ver' 		=> false,
				'in_footer' => true,
			),
			'et_json' => array(
				'src' 		=> FRAMEWORK_URL . '/js/lib/json2.js',
				'deps' 		=> array(),
				'ver' 		=> false,
				'in_footer' => true,
			),
		), $this->scripts);

		$this->scripts = apply_filters('et_registered_scripts', $this->scripts);

		foreach ((array)$this->scripts as $handle => $script) {
			$script = wp_parse_args($script, array(
				'src' => false,
				'deps' => array(),
				'ver' => false,
				'in_footer' => true
				)
			);
			wp_register_script($handle, $script['src'], $script['deps'], $script['ver'], $script['in_footer']);
		}
		do_action('et_register_scripts');

		/**
		 * Register style
		 */
		// Stylesheet library for EngineTheme
		$styles = array(
			'et_colorpicker' => array(
				'src' 	=> FRAMEWORK_URL . '/js/lib/css/colorpicker.css',
				'deps' 	=> array(),
				'ver'	=> false,
				'media' => 'all'
			)
		);
		foreach ($styles as $handle => $styles) {
			wp_register_style($handle, $styles['src'], $styles['deps'], $styles['ver'], $styles['media']);
		}

		$this->init();
		do_action('engine_init');
	}

	/**
	 * Enqueue default styles in engine theme.
	 * @since 1.0
	 * @author toannm
	 */
	public function master_print_styles(){
		if ( is_admin() ) {
			// enqueue all default admin styles
			$this->enqueue_styles((array)$this->admin_styles);

			// enqueue additional styles
			if (method_exists($this, 'admin_print_styles') ){
				$this->admin_print_styles();
			}
		}
		else{
			$this->styles = apply_filters('et_registered_styles', $this->styles);
			// enqueue all default script
			$this->enqueue_styles((array)$this->styles);

			// enqueue additional styles
			if ( method_exists($this, 'print_styles')  ){
				$this->print_styles();
			}
		}
	}

	/**
	 * Enqueue default styles in engine theme.
	 * @since 1.0
	 * @author toannm
	 */
	public function master_print_scripts(){
		if ( is_admin() ) return;
		// enqueue base scripts
		wp_enqueue_script('modernizr');
		wp_enqueue_script('et_underscore');
		wp_enqueue_script('et_backbone');

		// enqueue additional scripts
		if (method_exists($this, 'print_scripts')){
			$this->print_scripts();
		}
		do_action('et_enqueue_scripts');

		// localize scripts
		$this->localize_scripts( apply_filters('et_localize_scripts', array()) );
	}

	/**
	 *
	 */
	public function master_admin_enqueue_scripts(){
		//$this->enqueue_script_library();

		// enqueue all default admin styles
		if( isset($this->admin_scripts) && !empty($this->admin_scripts) ){
			$this->enqueue_scripts((array)$this->admin_scripts);
		}

		// enqueue additional scripts
		if (method_exists($this, 'admin_print_scripts')){
			$this->admin_print_scripts();
		}
	}

	/**
	 * Run theme initialization right after setup
	 * @since 1.0
	 * @author toannm
	 */
	public function after_setup_theme(){
		// check to see if all default setting have been applied
		$setup_status = get_option('et_jobengine_setup_status');

		// if default setting has not been applied yet, run default setting
		if ( !$setup_status ){
			// setup default setting
			// add default template pages first
			et_generate_templates(); // call the function that generate template pages

			if ( method_exists($this, 'setup_theme') ){
				$this->setup_theme();
			}

			// update setup status to finish, theme won't re-setup next run
			update_option('et_jobengine_setup_status', 1);
		}
	}

	/**
	 * Enqueue styles which were declared in the constructor
	 * @since 1.0
	 * @author toannm
	 */
	protected function enqueue_styles(array $styles){
		foreach ($styles as $handle => $style) {
			$enqueue = wp_parse_args($style, array( 'src' => false,
													'deps' => array(),
													'ver' => false,
													'media' => 'all' ));
			wp_enqueue_style($handle, $enqueue['src'], $enqueue['deps'], $enqueue['ver'], $enqueue['media']);
		}
	}

	/**
	 * Enqueue scripts which were declared in the constructor
	 * @since 1.0
	 * @author toannm
	 */
	protected function enqueue_scripts(array $scripts){
		foreach ($scripts as $handle => $script) {
			wp_enqueue_script($handle);
		}
	}

	protected function localize_scripts(array $scripts){
		foreach ($scripts as $handle => $data) {
			// localize the script here
			// check if the script in $handle has been enqueued or not and check if having l10n array
			if ( wp_script_is( $handle, 'queue') && !empty( $data ) ){
				$enqueue = wp_parse_args( $data, array( 'object_name' => false, 'data' => array() ) );
				wp_localize_script( $handle, $enqueue['object_name'], $enqueue['data'] );
			}
		}
	}

}

// deregister jquery and enqueue jquery library
add_action('wp_enqueue_scripts', 'et_deregister_jquery');
//add_action('admin_enqueue_scripts', 'et_deregister_jquery');
function et_deregister_jquery(){
	if (!is_admin() || (!empty($_GET['page']) && $_GET['page'] == 'engine-settings')){

	    global $concatenate_scripts;

	    $concatenate_scripts = false;
	    //wp_deregister_script( 'jquery' );
		wp_deregister_script( 'jquery' ); // deregisters the default WordPress jQuery & enqueue the new one later
		wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
		wp_enqueue_script('jquery');
	}
}

?>