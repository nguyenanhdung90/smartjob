<?php
/**
 * Helper API for Resumes
 */
class JE_Helper extends ET_Base{
	function __construct(){ }

	static public function yearSelectBox( $name, $selected = false, $args = array()){
		$args 	= wp_parse_args( $args, array('class' => '', 'id' => '') );
		$from 	= 1950;
		$to 	= date('Y');
		?>
		<select name="<?php echo $name ?>" class="<?php echo $args['class']?>" <?php if (!empty($args['id'])) echo 'id="' . $args['id'] . '"' ?> >
			<option value=""><?php _e('Year', ET_DOMAIN) ?></option>
			<?php for($i = $to; $i > $from ; $i--){ ?>
				<option value="<?php echo $i ?>" <?php if($selected == $i) echo 'selected="selected"' ?>><?php echo $i ?></option>
			<?php } ?>
		</select>
		<?php
	}

	static public function monthSelectBox( $name, $selected = false, $args = array() ){
		$args 	= wp_parse_args( $args, array('class' => '', 'id' => '') );
		$from 	= 1;
		$to 	= 12;
		?>
		<select name="<?php echo $name ?>" class="<?php echo $args['class']?>" <?php if (!empty($args['id'])) echo 'id="' . $args['id'] . '"' ?> >
			<option value=""><?php _e('Month', ET_DOMAIN) ?></option>
			<?php for($i = $from; $i <= $to ; $i++){ ?>
				<option value="<?php echo $i ?>" <?php if($selected == $i) echo 'selected="selected"' ?>><?php echo $i ?></option>
			<?php } ?>
		</select>
		<?php
	}

	/**
	 * Generate javascript template: year's select box 
	 * @param $name the select name
	 * @param $selected fromYear or toYear
	 * @param $args attributes
	 */
	static public function yearSelectBoxTemplate( $name, $selected = false, $args = array()){
		$args 	= wp_parse_args( $args, array('class' => '', 'id' => '') );
		$from 	= 1950;
		$to 	= date('Y');
		?>
		<select name="<?php echo $name ?>" class="<?php echo $args['class']?>" <?php if (!empty($args['id'])) echo 'id="' . $args . '"' ?> >
			<option value=""><?php _e('Year', ET_DOMAIN ) ?></option>
			<?php for($i = $to; $i > $from ; $i--){ 
				if ( $selected == 'from.year' || $selected == 'to.year' ) {
					echo "<option value='$i' <# if ( $selected == $i ) { #> selected='selected' <# } #> >$i</option>";				
				} else {
					echo "<option value='$i'>$i</option>";
				} ?>
			<?php } ?>
		</select>
		<?php
	}

	/**
	 * print out resume category template
	 */
	static public function jobPositionSelectTemplate($name, $selected = false, $args = array(), $field = 'term_id'){
		$args 	= wp_parse_args( $args, array('class' => '', 'id' => '' , 'attr' => array()) );
		$position_tax	=	JE_TaxFactory::get_instance('resume_category');
		$data	=	$position_tax->get_terms_in_order();

		$attr	=	'';
		if(!empty($args['attr'])) {
			foreach ($args['attr'] as $key => $value) {
				$attr	.= $key .'="'.$value . '"';
			}
		}
		
		?>
		<select <?php echo $attr; ?> name="<?php echo $name ?>" class="<?php echo $args['class']?>" <?php if (!empty($args['id'])) echo 'id="' . $args['id'] . '"' ?> >
			<option value=""><?php _e('Select your matches', ET_DOMAIN) ?></option>
			<?php //foreach ($data as $value) { ?>
				<?php self::job_cat_children_options($field, 'resume_category', $data , false, 0, $selected ); ?>
				
			<?php //} ?>
		</select>
		<?php
	}

	static private function job_cat_children_options($field, $tax, $cats = array(), $parent = false, $level = 0 , $select = ''){
		// re get categories if it empty
		if (empty($cats))
			$cats = array();

		// echo 
		foreach ($cats as $cat) {
			if ( ($parent == false && !$cat->parent) || $parent == $cat->parent ){
				// seting spacing
				$space = '';
				for ($i = 0; $i < $level; $i++ )
					$space .= '&nbsp;&nbsp;';

				$current 	= get_query_var( $tax );
				$selected 	= $current == $cat->slug ? 'selected="selected"' : '';

				if( $select ) {	
					if(!is_array( $select )) {
						$selected 	= $select == $cat->slug ? 'selected="selected"' : '';
					} else {
						
						$selected 	= in_array( $cat->$field , $select ) ? 'selected="selected"' : '';

					}					
				}

				global $current_filter;
				if (empty($current_filter)) $current_filter = array();
				if ( $current == $cat->slug )
					$current_filter[$tax] = $cat->name;

				// display option tag
				echo '<option value="' . $cat->$field . '" '. $selected .' rel="' . $cat->name . '">' . $space . $cat->name . '</option>';
				self::job_cat_children_options($field, $tax, $cats, $cat->term_id, $level + 1, $select );
			}
		} 
	}
	
	static public function extractDate($input = ''){
		if (empty($input)) 
			return array('month' => '', 'year' => '');
		else 
			return array(
				'month' => date('m', strtotime($input)),
				'year' 	=> date('Y', strtotime($input)) );
	}

	static public function get_page_template_link($page){
		$pages = get_posts( array('post_type' => 'page' , 'meta_key' => '_wp_page_template' ,'meta_value' => $page, 'numberposts' => 1 ) );

		if (!empty($pages)){
			foreach ($pages as $page) {
				return get_page_link( $page->ID );
			}
		}
		else 
			return false;
	}

	static public function get_jobseeker_profile_link($param){
		if (is_numeric($param))
			$object = get_userdata( $param );
		else if ( is_object($param) && !empty($param->user_login) )
			$object = $param;
		else return false;

		global $wp_rewrite;
		if ( $wp_rewrite->using_permalinks() )
			$result = self::get_page_template_link('page-jobseeker-profile.php') . $object->user_login;
		else 
			$result = add_query_arg( 'jobseeker_name', $object->user_login, self::get_page_template_link('page-jobseeker-profile.php'));

		return $result;
	}

	static public function resume_categories_filter_list($parent = 0, $list = false , $args){
		$resume_cat  	=   ET_TaxFactory::get_instance('resume_category');
		if ( !$list ) {
            $list     		=   $resume_cat->get_terms_in_order();
        }
        $args	=	wp_parse_args( $args, array('hide_empty' => false, 'hide_jobcount' => false) );
        extract($args);
        $expand	=	apply_filters( 'je_is_expand_parent_categories_list', 1 );
        ?>
        <ul class="resume-filter resume_category_list category-lists filter-jobcat filter-joblist" data-tax="resume_category" style="<?php echo ($parent != 0 && !$expand) ? 'display: none' :''   ?>">
        <?php 
			foreach ($list as $item) { 
				if( !$hide_empty || $item->count > 0 )
				if ( $item->parent == $parent ){
				?>
				<li class="<?php echo 'position-' . $item->term_id ?>">
					<a data="<?php echo $item->slug ?>" class="position-input" href="<?php echo $resume_cat->get_term_link( $item ) ?>">
						<div class="name"><?php echo $item->name ?> </div>
						<?php if(!$hide_jobcount) { ?>
						<span class="count"><?php echo $item->count ?></span>
						<?php } ?>
					</a>
					<?php 
					$has_child = false;
					foreach ($list as $sub_item) {
						if ($sub_item->parent == $item->term_id){
							$has_child = true;
							break;
						}
					}
					if ( $has_child ){ 
					?> 
					<div class="<?php if($expand) echo 'sym-multi arrow sym-multi-expand'; else echo 'sym-multi arrow'; ?>" ></div>
					<?php 
						self::resume_categories_filter_list($item->term_id, $list, $args);
					}
					?>
				</li>
			<?php 
				} // end if
			} // end foreach
		?>
		</ul>
		<?php 
	}
}

function je_get_resume_status () {
	return get_option('et_resumes_status', false);
}

?>