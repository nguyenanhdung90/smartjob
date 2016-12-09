<?php
class ET_Widget_Contract_Type extends WP_Widget
{
	function __construct() {
		$widget_ops = array('classname' => 'widget_job_contract', 'description' => __( 'This widget should only be dragged to the main sidebar to function properly.', ET_DOMAIN) );
		parent::__construct('job_contract', __( 'CONTRACT TYPE', ET_DOMAIN) , $widget_ops);
	}

	function widget( $args, $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' , 'hide_empty' => false , 'hide_jobcount' => false) );
		ET_Widget_Contract_Type ($args , $instance);
	}

	function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
		$instance['title'] 		= strip_tags($new_instance['title']);
		$instance['hide_empty'] = strip_tags($new_instance['hide_empty']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('CONTRACT TYPE', ET_DOMAIN ), 'hide_empty' => false , 'hide_jobcount' => false) );
		$title = $instance['title'];
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo 'Title:' ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" 
				type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<input type="checkbox" <?php echo  ($instance['hide_empty'] ? 'checked="checked"' : '') ; ?> class="checkbox" id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>">
			<label for="<?php echo $this->get_field_id('hide_empty'); ?>">Hide empty</label><br>
		</p>

	<?php
	}
}

class ET_Widget_Job_Categories extends WP_Widget
{
	function __construct() {
		$widget_ops = array('classname' => 'widget_job_category', 'description' => __( 'This widget should only be dragged to the main sidebar to function properly.', ET_DOMAIN) );
		parent::__construct('job_category', __( 'JOB CATEGORIES', ET_DOMAIN) , $widget_ops);
	}

	function widget( $args, $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' , 'hide_empty' => false , 'hide_jobcount' => false ) );
		ET_Widget_Job_Categories ($args , $instance );
	}

	function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => __('JOB CATEGORIES', ET_DOMAIN ),'hide_empty'=>0,'hide_jobcount'=>1, ) );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['hide_empty'] = strip_tags($new_instance['hide_empty']);
		$instance['hide_jobcount'] = strip_tags($new_instance['hide_jobcount']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' , 'hide_empty' => false , 'hide_jobcount' => false) );
		$title = $instance['title'];
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo 'Title:' ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" 
					type="text" value="<?php echo esc_attr($instance['title']); ?>" />
		</p>
		<p>
			<input type="checkbox" <?php echo  ($instance['hide_empty'] ? 'checked="checked"' : '') ; ?>  class="checkbox" id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>">
			<label for="<?php echo $this->get_field_id('hide_empty'); ?>">Hide empty</label><br>
		</p>
		<p>
			<input type="checkbox" <?php echo  ($instance['hide_jobcount'] ? 'checked="checked"' : '') ; ?> class="checkbox" id="<?php echo $this->get_field_id('hide_jobcount'); ?>" name="<?php echo $this->get_field_name('hide_jobcount'); ?>">
			<label for="<?php echo $this->get_field_id('hide_jobcount'); ?>">Hide job count</label><br>
		</p>
	<?php
	}
}

/**
 *
 */
class JE_Location_Filter extends WP_Widget{
	function __construct(){
		$widget_ops = array('classname' => 'widget_job_locations', 'description' => __( 'This widget should only be dragged to the main sidebar to function properly.', ET_DOMAIN) );
		parent::__construct('job_locations', __( 'JE Locations Filter', ET_DOMAIN) , $widget_ops);
	}

	function update( $new_instance, $old_instance){
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array)$new_instance, array(
			'title' 		=> __('JOB LOCATIONS', ET_DOMAIN),
			'locations' 	=> '',
			));
		$instance['title'] = strip_tags(trim($new_instance['title']));
		$instance['locations'] = trim($new_instance['locations']);
		return $instance;
	}

	function form($instance){
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'locations' => '') );
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo 'Title:' ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" 
					type="text" value="<?php echo esc_attr($instance['title']); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('locations') ?>"><?php _e('Locations (separate location by semi-colon):') ?></label>
			<textarea class="widefat" id="<?php echo $this->get_field_id('locations') ?>" name="<?php echo $this->get_field_name('locations') ?>"><?php echo esc_attr($instance['locations']) ?></textarea>
		</p>
	<?php
	}

	function widget($args, $instance){
		extract($args);
		$before_widget	= str_replace('widget ', ' widget widget-select widget_archive ', $before_widget);

		echo $before_widget;
		if ($instance['title'] != '') echo $before_title . $instance['title'] . $after_title;

		$locations = explode(';', $instance['locations']);
		$query_loc = get_query_var( 'location' );
		if (!empty($locations)){
			?>
			<ul class="filter-location filter-joblist" id="location_filter">
				<?php foreach ($locations as $loc) {
					$activated = strtolower(trim($loc)) == strtolower($query_loc); ?>
					<li><a href="<?php echo home_url('?location='.trim($loc)) ?>" rel="location" data-slug="<?php echo trim($loc) ?>" class="<?php echo $activated ? 'active' : '' ?>" ><?php echo trim($loc) ?></a></li>
				<?php } ?>
			</ul>
			<?php
		}
		echo $after_widget;
	}
}

class JE_Company_Count extends WP_Widget
{
	function __construct() {
		$widget_ops = array(
			'classname' => 'widget_je_company_count',
			'description' => __( 'Drag this widget to any sidebar to display the total count of active companies and jobs.', ET_DOMAIN) 
		);
		parent::__construct('je_company_count', __( 'JE Companies &amp; Jobs Count', ET_DOMAIN) , $widget_ops);
	}

	function widget( $args, $instance ) {
		JE_Company_Count ($args);
	}

	function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' =>  __('COMPANIES & JOBS', ET_DOMAIN )) );
		$title = $instance['title'];
	?>
		<!-- <p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo 'Title:' ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" 
					type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p> -->
	<?php
	}

}

class JE_Company_Profile extends WP_Widget {
	function __construct() {
		$widget_ops = array(
			'classname' => 'widget_je_company_profile',
			'description' => __( 'This widget will display the company information and should only be dragged to the company sidebar or job detail sidebar to function properly.', ET_DOMAIN) 
		);
		parent::__construct('je_company_profile', __( 'JE Company Profile', ET_DOMAIN) , $widget_ops);
	}

	function widget( $args, $instance ) {
		JE_Company_Profile ($args, $instance );
	}

	function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('COMPANY PROFILE', ET_DOMAIN )) );
		$title = $instance['title'];
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo 'Title:' ?> </label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" 
					type="text" value="<?php echo esc_attr($instance['title'] ); ?>" />
		</p>
	<?php
	}
}


function et_widgets_init () {

	register_widget('ET_Widget_Contract_Type');
	register_widget('ET_Widget_Job_Categories');
	register_widget('JE_Company_Count');
	register_widget('JE_Company_Profile');
	register_widget('JE_Location_Filter');

	$check		=	get_option('et_first_install_widget', 0 );

	global $sidebars_widgets;

	if( $check < 1 ) {

		$contract_type		=	get_option('widget_job_contract');
		$categories			=	get_option('widget_job_category');


		$next_cat				=	et_next_widget_number('job_category') ;
		$next_contract			=	et_next_widget_number('job_contract') ;


		$categories[$next_cat]	=	array (
	        'title' => __('JOB CATEGORIES', ET_DOMAIN )
	    );

	    $contract_type[$next_contract]		=	 array (
	        'title' => __( 'CONTRACT TYPE', ET_DOMAIN)
	    );

		update_option('widget_job_category', $categories);
		update_option('widget_job_contract', $contract_type );

		if(isset($sidebars_widgets['sidebar-main']) && is_array($sidebars_widgets['sidebar-main']) ) {
			$sidebars_widgets['sidebar-main']	=	array();
			array_unshift ($sidebars_widgets['sidebar-main'] , 'job_contract-'.$next_contract , 'job_category-'.$next_cat);
			wp_set_sidebars_widgets($sidebars_widgets);
			update_option('et_first_install_widget', 1);
		}

	}

}

add_action ('widgets_init', 'et_widgets_init');

/**
 * function gernerate widget next number to unique widget
 * @param string $id_base : widget id base
 * @return int : next widget number
 * @author mr Dakachi
 */
function  et_next_widget_number ( $id_base) {
	global $wp_registered_widgets;

	$number	=	1;


	foreach ( $wp_registered_widgets as $widget_id => $widget ) {
	if ( preg_match( '/' . $id_base . '-([0-9]+)$/', $widget_id, $matches ) )
		$number = max($number, $matches[1]);
	}
	$number++;
	return $number;
}

/**
 * ajax function : update sort sidebar widget
*/
function et_sort_sidebar_widget () {

	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	if(!current_user_can('manage_options')) {
		echo json_encode(array('success' => false, 'msg' => __("You have no permission to perform this action", ET_DOMAIN ) )) ;
		exit;
	}

	$sidebar	=	isset($_POST['sidebar'] ) ? $_POST['sidebar'] : '';
	$widget		=	isset($_POST['widget']) ? $_POST['widget'] : '' ;

	global $sidebars_widgets ;
	if( $sidebar == '' || $widget == '' || !isset($sidebars_widgets[$sidebar]) ) {
		echo json_encode(array('success' => false) );
		exit;
	}

	$sidebars_widgets[$sidebar]	=	$widget;
	wp_set_sidebars_widgets($sidebars_widgets);

	echo json_encode(array('success' => true ) );

	exit;

}
add_action ('wp_ajax_et-sort-sidebar-widget', 'et_sort_sidebar_widget');

/**
 * initialize sidebar
 */
add_action ('widgets_init', 'et_register_sidebar');
function et_register_sidebar () {
	if(current_user_can('manage_options')) {
		$before_widget	=	'<aside id="%1$s" class="widget %2$s"><div class="sort-handle"></div>';
	} else {
		$before_widget	=	'<aside id="%1$s" class="widget %2$s">';
	}

	register_sidebar (
		array(
			'name' => __( 'Homepage Main Sidebar', ET_DOMAIN ),
			'id' => 'sidebar-main',
			'description' => __( 'Drop widgets here to position them at the left/right area of your Homepage', ET_DOMAIN ),
			'before_widget' => $before_widget,
			'after_widget' => "</aside>",
			'before_title' => '<div class="widget-title">',
			'after_title' => '</div>',
		)
	) ;

	register_sidebar ( array(
			'name' => __( 'Homepage Top Sidebar', ET_DOMAIN ),
			'id' => 'sidebar-home-top',
			'description' => __( 'Drop widgets here to position them at the top of your Homepage, below the demonstration text', ET_DOMAIN ),
			'before_widget' => $before_widget,
			'after_widget' => "</aside>",
			'before_title' => '<div class="widget-title">',
			'after_title' => '</div>',
		)
	);

	register_sidebar ( array(
			'name' => __( 'Homepage Bottom Sidebar', ET_DOMAIN ),
			'id' => 'sidebar-home-bottom',
			'description' => __( 'Drop widgets here to position them at the bottom of your Homepage, below the load more button', ET_DOMAIN ),
			'before_widget' => $before_widget,
			'after_widget' => "</aside>",
			'before_title' => '<div class="widget-title">',
			'after_title' => '</div>',
		)
	);

	register_sidebar ( array(
			'name' => __( 'Blog Sidebar', ET_DOMAIN ),
			'id' => 'sidebar-blog',
			'description' => __( 'Drop widgets here to position them in the Blog Page', ET_DOMAIN ),
			'before_widget' => $before_widget,
			'after_widget' => "</aside>",
			'before_title' => '<div class="widget-title">',
			'after_title' => '</div>',
		)
	);	
	register_sidebar ( array(
			'name' => __( 'Slide  bar allcate', ET_DOMAIN ),
			'id' => 'sidebar-all-cate',
			'description' => __( 'Drop widgets here to position them in the Blog Page', ET_DOMAIN ),
			'before_widget' => $before_widget,
			'after_widget' => "</aside>",
			'before_title' => '<div class="widget-title">',
			'after_title' => '</div>',
		)
	);

	register_sidebar ( array(
			'name' => __( 'Page Sidebar', ET_DOMAIN ),
			'id' => 'sidebar-page',
			'description' => __( 'Drop widgets here to position them in the Static Page', ET_DOMAIN ),
			'before_widget' => $before_widget,
			'after_widget' => "</aside>",
			'before_title' => '<div class="widget-title">',
			'after_title' => '</div>',
		)
	);

	register_sidebar ( array(
			'name' => __( 'Companies Sidebar', ET_DOMAIN ),
			'id' => 'sidebar-companies',
			'description' => __( 'Drop widgets here to position them in Companies page', ET_DOMAIN ),
			'before_widget' => $before_widget,
			'after_widget' => "</aside>",
			'before_title' => '<div class="widget-title">',
			'after_title' => '</div>',
		)
	);

	register_sidebar ( array(
			'name' => __( 'Company Sidebar', ET_DOMAIN ),
			'id' => 'sidebar-company',
			'description' => __( 'Drop widgets here to position them in the Company Profile page', ET_DOMAIN ),
			'before_widget' => $before_widget,
			'after_widget' => "</aside>",
			'before_title' => '<div class="widget-title">',
			'after_title' => '</div>',
		) 
	);

	register_sidebar ( array(
			'name' => __( ' Job Detail Sidebar', ET_DOMAIN ),
			'id' => 'sidebar-job-detail',
			'description' => __( 'Drop widgets here to position them in the Job Post page', ET_DOMAIN ),
			'before_widget' => $before_widget,
			'after_widget' => "</aside>",
			'before_title' => '<div class="widget-title">',
			'after_title' => '</div>',
		)
	);

}

function JE_Company_Profile ($args = '' , $instance = array('title' => '') ) {
	if($args == '') {
		$args	=	array(
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => "</aside>",
			'before_title' => '<div class="widget-title">',
			'after_title' => '</div>',
		);
	}

	$author_id	=	'';
	if( is_single() ) {
		global $post;
		$author_id	=	$post->post_author;
	}

	if(get_query_var('author')) {
		$author_id	=	get_query_var('author');
	}

	extract($args);
	if($author_id) {
		$company		= et_create_companies_response( $author_id );
		$company_logo	= $company['user_logo'];

		$before_widget	=	str_replace('widget ', 'widget company-profile bg-grey-widget margin-top15 ', $before_widget);
		echo $before_widget;

		if( $instance['title'] ) echo $before_title.$instance['title'].$after_title ?>
			<div class="thumbs"><?php
				if (!empty($company_logo)){
					if(isset($company_logo['company-logo'][0]))
						$com_logo_url  = $company_logo['company-logo'][0];
					else
						$com_logo_url  = $company_logo['medium'][0];
					?>
					<a id="job_author_thumb" href="<?php echo $company['post_url']?>" 
						title="<?php printf(__('View posted jobs by %s', ET_DOMAIN), $company['display_name']) ?>" class="thumb">
						<img src="<?php echo $com_logo_url; ?>" id="company_logo_thumb" data="<?php echo $company_logo['attach_id'];?>" />
					</a>
					<?php
				} ?>
			</div>
			<?php if ( !empty($company['display_name']) ) : ?>
				<div class="title company_name">
					<a  id="job_author_name" class="name job_author_link" href="<?php echo get_author_posts_url($company['ID'])?>" 
					title="<?php printf(__('View jobs posted by %s', ET_DOMAIN), $company['display_name']) ?>">
					  <?php echo $company['display_name']?>
					</a>
				</div>
			<?php endif; ?>
			<?php if ( !empty($company['user_url']) ) : ?>
				<div class="info icon-default">
					<input type="hidden" name="companyid" value="<?php echo $company['ID'] ?>">

						<?php
						if ( preg_match("/^(http:\/\/)/", $company['user_url']) || preg_match("/^(https:\/\/)/", $company['user_url']) )
							//echo '<a id="job_author_url" target="_blank" rel="nofollow" href="'.$company['user_url'].'">'.$company['user_url'].'</a>';
							echo '<a id="job_author_url" target="_blank" rel="nofollow" href="'.$company['user_url'].'">'.$company['user_url'].'</a>';
						else
							echo $company['user_url'];
						?>
						<span class="icon" data-icon="A"></span>

				</div>
			<?php endif; ?>

			<?php if ( !empty($company['description']) ) : ?>
				<div class="info company-description">
					<div class="content">
						<?php echo $company['description']; ?>
					</div>
				</div>
			<?php endif;?>
			<div class="info icon-default">
			    <?php /*
				global $wpdb;
				$rows=$wpdb->get_results( "SELECT decription FROM wp_post_company where users_id ='".$author_id."' " );
				echo $rows[0]->decription;*/
				?>
			</div>
			<?php			
			/**
			* @since 2.9.8
			*/
			do_action('je_company_profile_addition', $author_id);
		echo $after_widget;
	} else {
		if(current_user_can('manage_options')) {
			echo $before_widget;
			_e("<strong>Admin notice:</strong> JE Company Profile widget should only be dragged to the company sidebar or job detail sidebar to function properly.", ET_DOMAIN);
			echo $after_widget;
		}
	}
}

function JE_Company_Count ($args = '') {
	if($args == '') {
		$args	=	array(
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => "</aside>",
			'before_title' => '<div class="widget-title">',
			'after_title' => '</div>',
		);
	}

	extract($args);
	$before_widget	=	str_replace('widget ', 'widget bg-grey-widget companies-statis ', $before_widget);
	echo $before_widget;

	global $wpdb;
	$count 				= et_get_job_count();
	//$companies 			= et_get_companies();
	//$companies_count 	= count($companies);
    $wpdb->get_results( "SELECT ID FROM wp_post_company" );
	$companies_count=$wpdb->num_rows;
	?>
		<div class="first">
			<?php printf(__('Companies <span class="impress">%s</span>', ET_DOMAIN), $companies_count); ?>
		</div>
		<div>
			<?php printf(__('Available jobs <span class="impress">%s</span>', ET_DOMAIN), $count['publish']); ?>

		</div>

	<?php
	echo $after_widget;
}

function ET_Widget_Contract_Type ($args = '' , $instance = array('title' => '')) {
	if($args == '') {
		$args	=	array(
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => "</aside>",
			'before_title' => '<div class="widget-title">',
			'after_title' => '</div>',
		);
	}
	global $wp_query;
	$query_obj	= ($wp_query->is_tax) ? $wp_query->queried_object : false;
	extract($args);
	$before_widget	=	str_replace('widget ', 'widget widget-select widget_archive content-dot ', $before_widget);
	echo $before_widget;

	if($instance['title']) echo $before_title.apply_filters( 'widget_title', $instance['title'] ).$after_title ;

	$job_type	=	new JE_JobType();
	$job_type->print_filter_list ($query_obj , $instance);

	echo $after_widget;
}

function ET_Widget_Job_Categories ($args = '' , $instance = array('title' => '')) {

	if($args == '') {
		$args	=	array(
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => "</aside>",
			'before_title' => '<div class="widget-title">',
			'after_title' => '</div>',
		);
	}

	global $wp_query;
		$query_obj	= ($wp_query->is_tax) ? $wp_query->queried_object : false;
		extract($args);
		$before_widget	=str_replace('widget ', 'widget widget-select widget_archive content-dot ', $before_widget);
		echo $before_widget;
	?>
		<?php if($instance['title']) echo $before_title. apply_filters( 'widget_title', $instance['title'] ).$after_title ?>
		<?php et_template_front_category(0, 0, $instance ); ?>

	<?php
		echo $after_widget;
}