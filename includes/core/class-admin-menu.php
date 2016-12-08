<?php
/**
 * 
 */
if ( !class_exists('ET_Base') ){
class ET_Base{

	// protected $filter_scripts = 'et_admin_enqueue_script';
	// protected $filter_style = 'et_admin_enqueue_style';

	const AJAX_PREFIX = 'wp_ajax_';
	const AJAX_NOPRIV_PREFIX = 'wp_ajax_nopriv_';

	const FILTER_SCRIPT = 'et_enqueue_script';
	const FILTER_STYLE = 'et_enqueue_style';

	public function __construct() {}

	/**
	 * Add an action hook
	 */
	protected function add_action($hook, $callback, $priority = 10, $accepted_args = 1){
		add_action($hook, array($this, $callback), $priority, $accepted_args);
	}

	protected function remove_action($hook, $callback){
		remove_action($hook, array($this, $callback));
	}

	/**
	 * Add a filter hook
	 */
	protected function add_filter($hook, $callback, $priority = 10, $accepted_args = 1){
		add_filter($hook, array($this, $callback), $priority, $accepted_args);
	}

	protected function remove_filter($hook, $callback){
		remove_filter($hook, array($this, $callback));
	}

	/**
	 * Add ajax action for short
	 */
	protected function add_ajax($hook, $callback, $priv = true, $no_priv = true, $priority = 10, $accepted_args = 1){
		if ( $priv )
			$this->add_action( self::AJAX_PREFIX . $hook, $callback, $priority, $accepted_args );
		if ( $no_priv )
			$this->add_action( self::AJAX_NOPRIV_PREFIX . $hook, $callback, $priority, $accepted_args );
	}

	protected function ajax_header(){
		header( 'HTTP/1.0 200 OK' );
		header( 'Content-type: application/json' );
	}

	/**
	 * Register script and add it into queue
	 */
	protected function add_script($handle, $src, $deps = array(), $ver = false, $in_footer = true){
		$scripts = apply_filters( self::FILTER_SCRIPT, array(
			'handle' 	=> $handle,
			'src' 		=> $src,
			'deps' 		=> $deps,
			'ver' 		=> $ver,
			'in_footer' 	=> $in_footer
		));
		wp_register_script( $scripts['handle'], $scripts['src'], $scripts['deps'], $scripts['ver'], $scripts['in_footer']);
		wp_enqueue_script( $scripts['handle'] );
	}

	protected function register_script($handle, $src, $deps = array(), $ver = false, $in_footer = true){
		$scripts = apply_filters( self::FILTER_SCRIPT, array(
			'handle' 	=> $handle,
			'src' 		=> $src,
			'deps' 		=> $deps,
			'ver' 		=> $ver,
			'in_footer' 	=> $in_footer
		));
		wp_register_script( $scripts['handle'], $scripts['src'], $scripts['deps'], $scripts['ver'], $scripts['in_footer']);
	}

	protected function add_existed_script($handle , $src = '', $deps = array(), $ver=false, $in_footer = true ){
		wp_enqueue_script( $handle,  $src, $deps, $ver, $in_footer );
	}

	protected function add_style($handle, $src = false, $deps = array(), $ver = false, $media = 'all'){
		$style = apply_filters( self::FILTER_STYLE, array(
			'handle' 	=> $handle,
			'src' 		=> $src,
			'deps' 		=> $deps,
			'ver' 		=> $ver,
			'media' 	=> $media
		));
		wp_register_style( $style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media'] );
		wp_enqueue_style( $style['handle'] );
	}


	protected function register_style($handle, $src = false, $deps = array(), $ver = false, $media = 'all'){
		$style = apply_filters( self::FILTER_STYLE, array(
			'handle' 	=> $handle,
			'src' 		=> $src,
			'deps' 		=> $deps,
			'ver' 		=> $ver,
			'media' 	=> $media
		));
		wp_register_style( $style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media'] );
		wp_enqueue_style( $style['handle'] );
	}

	protected function add_existed_style($handle){
		wp_enqueue_style( $handle );
	}
}
}

/**
 * Class: Engine Theme Admin Menu
 * Main menu
 */
class ET_EngineAdminMenu extends ET_Base{

	var $args;
	var $hookname;
	var $sections = array();

	public function __construct(){
		$this->args = array(
			'page_title' 	=> __('Engine Settings', ET_DOMAIN),
			'menu_title' 	=> __('Engine Settings', ET_DOMAIN),
			'cap' 			=> 'administrator',
			'slug' 			=> 'engine-settings',
			'icon_url' 		=> '',
			'pos' 			=> 3
		);

		$this->args = apply_filters('engine_menu_args', $this->args);
		$this->add_action('admin_menu', 'add_menu_page');

		// register some lib
		add_action('admin_init', array($this,'admin_init'));

		// scripts and styles
		$this->add_action('admin_enqueue_scripts', 'on_add_scripts');
		$this->add_action('admin_print_styles', 'on_add_styles');

		// parent
		parent::__construct();
	}

	protected function get_page_link($page_slug){
		return add_query_arg('page', $page_slug, admin_url('admin.php'));
	}

	/**
	 * 
	 */
	public function get_menu_items(){
		return $this->sections;
	}

	public function admin_init(){
		// sort admin section
		//uasort($this->sections, array($this, 'sortcallback'));
	}

	public function sortcallback($a, $b){
		if (empty($a->pos) || empty($b->pos) || $a->pos == $b->pos)
			return 0;

		return $a->pos < $b->pos ? -1 : 1;
	}

	public function add_menu_page(){
		$args 			= $this->args;

		$first 			= true;
		$parent_slug 	= $this->args['slug'];
		// sort first
		uasort($this->sections, array($this, 'sortcallback'));

		foreach ($this->sections as $name => $section) {
			if ( $first ){
				$parent_slug = $section->slug;
				add_menu_page(
					$this->args['page_title'] ,
					$this->args['menu_title'] ,
					$this->args['cap'] ,
					$parent_slug ,
					array( $this , 'render_frame' ),
					$this->args['icon_url'],
					$this->args['pos'] );
				$first = false;
			}

			add_submenu_page( $parent_slug, $section->page_title, $section->menu_title, 'manage_options', $section->slug, array($this, 'render_frame') );
		}
	}

	/**
	 * Create default scripts
	 */
	public function register_default_scripts(){
		$this->register_script('et_backbone', FRAMEWORK_URL . '/js/lib/backbone-min.js' );
		$this->register_script('et_underscore', FRAMEWORK_URL . '/js/lib/underscore-min.js' );
		$this->register_script('jquery-textarea-autosize', FRAMEWORK_URL . '/js/lib/jquery.autosize.js', array('jquery'));
	}

	/**
	 * Adding Styles
	 */
	public function on_add_styles(){
		// add some default styles
		$this->add_style( 'engine_styles', TEMPLATEURL . '/includes/core/css/engine.css', array(), false, 'all' ); 
		$this->add_style( 'et_colorpicker', FRAMEWORK_URL . '/js/lib/css/colorpicker.css', array() );

		// add other styles
		$slug = isset($_GET['page']) ? $_GET['page'] : '';
		do_action('et_admin_enqueue_styles', $slug);
		do_action('et_admin_enqueue_styles-' . $slug);
	}

	/**
	 * Adding scripts
	 */
	public function on_add_scripts(){
		// add some default script
		$this->register_default_scripts();

		// add other scripts
		$slug = isset($_GET['page']) ? $_GET['page'] : '';
		do_action('et_admin_enqueue_scripts', $slug); 
		do_action('et_admin_enqueue_scripts-' . $slug);
		do_action('et_admin_localize_scripts', $slug);// for localize script
	}

	public function render_frame(){
		global $et_global;
		$minipages = $et_global['engine_admin_pages'];
		?>
		<!-- ================================ -->
		<!-- Admin Frame                      -->
		<!-- ================================ -->
		<div class="wrap">
			<div class="et-body">
				<div class="et-header">
					<div class="logo">
						<a href="<?php bloginfo('url');?>"> Powered by <img src="<?php echo FRAMEWORK_URL ?>/img/engine-logo.png" /> </a>
					</div>
					<div class="slogan"><span><?php _e('Administration',ET_DOMAIN) ?></span>. <?php _e('You are an admin. Here you administrate.',ET_DOMAIN) ?></div>	    	
				</div>
				<div class="et-wrapper clearfix">
					<div class="et-left-column">
						<ul class="et-menu-items font-quicksand">
							<?php 
							// detect subpage
							$keys = array_values($this->sections);
							$subpage = $keys[0];
							foreach ($this->sections as $section) {
								if ( $section->slug == $_GET['page'] ){
									$subpage = $section;
									break;
								}
							}

							$i = 0;
							foreach ($this->sections as $classname => $section) {
								$i++;
								$classes = $subpage->slug == $section->slug ? 'active' : '';
								echo '<li>';
								echo '<a class="engine-menu '. $classes .'" href="' . $this->get_page_link($section->slug) . '">';
								echo '<div class="engine-menu-icon ' . $section->icon_class . '"></div>';
								echo $section->page_title;
								echo '</a>';
								echo '</li>';
							}
							?>
						</ul>
					</div>
					<div id="engine_setting_content" class="et-main-column clearfix">
						<?php 
						echo $this->render_section($subpage);
						?>
					</div>
				</div>
				<div class="et-footer"></div>
				<!--
				<div class="et-footer">
					If you have any troubles you can <a  href="#">watch a video about this page <span class="icon" data-icon="V"></span></a>or  <a href="#">send us a message <span class="icon" data-icon="M"></span></a>.
				</div>
				-->
			</div>
		</div><!-- wrap -->
		
		<?php 
	}

	/**
	 * 
	 */
	public function render_section($target){
		if ( isset($target->callback) ){
			call_user_func($target->callback, $target);
		}else if ( !empty($target->class) ){
			$target->class->view();
		}
	}

	/**
	 * Use for registering new sections
	 * @since 1.0
	 */
	public function register_sections($classname, $args = array()){
		//$this->sections[$classname] = & new $classname();
		if ( class_exists($classname) ){
			$class = new $classname();
			$instance =(object)array(
				'icon_class' 	=> $class->icon_class,
				'menu_title' 	=> $class->menu_title,
				'page_title' 	=> $class->page_title,
				'slug' 			=> $class->slug,
				'pos' 			=> $class->pos,
				'class' 		=> $class
				);
		} else {
			$instance =(object)wp_parse_args($args ,array(
				'icon_class' 	=> 'icon-gear',
				'menu_title' 	=> __('Menu', ET_DOMAIN),
				'callback' 		=> false,
				'page_title' 	=> __('Menu', ET_DOMAIN),
				'slug' 			=> '',
				'pos' 			=> 40
				));
		}

		$this->sections[$classname] = $instance; // $instance;
	}

	/**
	 * Use for unregistering exist sections
	 * @since 1.0
	 */
	public function unregister_sections($classname){
		unset($this->sections[$classname]);
	}

	public function add_library($type = 'css', $handle = '', $args = array() ){
		// check if input is valid
		if ( in_array($type, array('css', 'js')) && !empty($handle) ){
			$this->libraries[$type][$handle] = $args;
		}
	}

	public function delete_library($type = 'css', $handle = ''){
		// check if library is exist
		if ( isset($this->libraries[$type][$handle]) ){
			unset( $this->libraries[$type][$handle] );
		}
	}
}

/**
 * Register new section in engine menu
 * @since 1.0
 */
function et_register_menu_section($classname, $args = array()){
	global $et_admin_page;
	$et_admin_page->register_sections($classname, $args);
}
/**
 * Delete a register section in engine menu
 * @since 1.0
 */
function et_unregister_menu_section($classname){
	global $et_admin_page;
	$et_admin_page->unregister_sections($classname);
}

/**
 * Return all menu items with url
 */
function et_get_menu_items(){
	global $et_admin_page;
	$items 		= $et_admin_page->get_menu_items();
	$return 	= array();
	foreach ($items as $item) {
		$return[] = array('slug' => $item->slug, 'link' => add_query_arg( 'page', $item->slug, admin_url( 'admin.php' ) ) );
	}
	return $return;
}

/**
 * Admin menu sections
 * @since 1.0
 */
abstract class ET_EngineAdminSection extends ET_Base{

	const FILTER_SCRIPT = 'et_enqueue_script';
	const FILTER_STYLE = 'et_enqueue_style';

	var $icon_class;
	var $menu_title;
	var $page_title;
	var $page_subtitle;
	var $slug;

	// declare view for 
	abstract function view();

	function __construct($menu_title, $page_title, $page_subtitle, $slug, $icon_class = 'icon-gear', $pos = 5){
		$this->icon_class 		= $icon_class;
		$this->menu_title 		= $menu_title;
		$this->page_title 		= $page_title;
		$this->page_subtitle 	= $page_subtitle;
		$this->pos 				= $pos;
		$this->slug 			= $slug;

		// scripts and styles
		$this->add_action("et_admin_enqueue_scripts-{$this->slug}", 'on_add_scripts');
		$this->add_action("et_admin_enqueue_styles-{$this->slug}", 'on_add_styles');
	}

	public function get_menu_item(){
		$icondiv = '<div class="img-icon" >';
		return $icondiv . $menu_title;
	}

	/**
	 * Header view
	 * @since 1.0
	 */
	public function get_header(){
		?>
		<div class="et-main-header">
			<div class="title font-quicksand"><?php echo $this->page_title ?></div>
			<div class="desc"><?php echo $this->page_subtitle ?></div>
		</div>
		<?php
		//return $div;
	}

	/**
	 * Footer view
	 * @since 1.0
	 */
	public function get_footer(){
		echo '';
	}

	/**
	 * Auto display content view
	 * @since 1.0
	 */
	public function render_content(){
		$this->get_header();
		$this->view();
		$this->get_footer();
	}
}
