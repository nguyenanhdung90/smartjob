<?php 
/**
 * EngineThemes custom fields library 
 *
 */
class ETFields{
	const POST_TYPE = 'et_field';

	const FIELD_NAME 		= 'et_name';
	const FIELD_TYPE 		= 'et_type';
	const FIELD_POST_TYPE	= 'et_post_type';
	const FIELD_INPUT_TYPE 	= 'et_input_type';
	const FIELD_SEARCHABLE 	= 'et_searchable';
	const FIELD_FILTERABLE 	= 'et_filterable';
	const FIELD_QUERY_TYPE 	= 'et_query_type';
	const FIELD_MANDATORY 	= 'et_mandatory';
	const FIELD_OPTIONS		= 'et_options';

	const INPUT_TEXT 		= 'text';
	const INPUT_OPTIONS 	= 'option';
	const INPUT_RADIO 		= 'radio';
	const INPUT_CHECKBOX	= 'checkbox';
	const INPUT_TEXTAREA	= 'textarea';
	const INPUT_EDITOR 		= 'editor';

	const TYPE_TEXT 		= 'text';
	const TYPE_INT 			= 'number';
	const TYPE_DECIMAL 		= 'decimal';
	const TYPE_CURRENCY 	= 'currency';

	protected $instance;
	protected $fields;

	public $id;
	public $name;
	public $desc;
	public $title;
	public $type;
	public $post_type;
	public $searchable;
	public $filterable;
	public $query_type;
	public $mandatory;
	public $options;

	public function __construct($id = 0){
		// if ($id != 0){
		// 	$this = self::get_field_by_id($id)
		// }
	}

	/**
	 * get a field by its id
	 */
	public static function get_field_by_id($id){
		$row = get_post($id);
		return self::build_fields_by_post($row);
	}

	/**
	 * get a field by its name
	 */
	public static function get_field_by_name($name){
		$rows = get_posts(array(
			'numberposts' 	=> 1,
			'meta_key' 		=> self::FIELD_NAME,
			'meta_value' 	=> $name,
		));

		if (empty($rows)){
			return false;
		}else {			
			$post = false;
			foreach ($rows as $value) {
				$post = $value;
				break;
			}
			return self::build_fields_by_post($post);
		}
	}

	/**
	 * Build a field object by a post object
	 * @param object $post to build
	 * @return $ETFields object
	 */
	public static function build_fields_by_post($post){
		$field = new ETFields();

		// assign attribute
		$field->id 			= $post->ID;
		$field->title 		= $post->post_title;
		$field->desc 		= $post->post_content;
		$field->name 		= get_post_meta( $field->id, self::FIELD_NAME ,true);
		$field->type 		= get_post_meta( $field->id, self::FIELD_TYPE ,true);
		$field->post_type 	= get_post_meta( $field->id, self::FIELD_POST_TYPE ,true);
		$field->query_type 	= get_post_meta( $field->id, self::FIELD_QUERY_TYPE ,true);
		$field->searchable 	= get_post_meta( $field->id, self::FIELD_SEARCHABLE ,true) == '1' ? true : false;
		$field->filterable 	= get_post_meta( $field->id, self::FIELD_FILTERABLE ,true) == '1' ? true : false;
		$field->mandatory 	= get_post_meta( $field->id, self::FIELD_MANDATORY ,true) == '1' ? true : false;
		$field->options 	= unserialize(get_post_meta( $field->id, self::FIELD_OPTIONS ,true));
		return $field;
	}

	/**
	 * Query custom fields by post type
	 */
	public static function get_fields_by_post_type($post_type){
		$fields = get_posts(array(
			'numberposts' 	=> -1,
			'post_type' 	=> self::POST_TYPE,
			'meta_value' 	=> self::FIELD_POST_TYPE,
			'meta_value' 	=> $post_type
		));
		// if no field founds, return empty array
		if (empty($fields)) return array();

		$return = array();
		foreach ($fields as $field) {
			$return[] = self::build_fields_by_post($field);
		}
		return $return;
	}

	/**
	 * Register field
	 * @param $id
	 * @param $title
	 * @param $type
	 * @param $post_type to apply
	 * @param array $args other parameters
	 */
	public static function register_field($name, $title, $post_type, $args = array()){
		// default args
		$args = wp_parse_args( $args, array(
			'desc' 			=> '',
			'searchable' 	=> false,
			'filterable' 	=> false,
			'query_type' 	=> '=', // available values : =, <=, >=, between, like, in, not in
			'mandatory' 	=> false,
			'options' 		=> array(),
			'type' 			=> self::TYPE_TEXT,
			'input_type' 	=> self::INPUT_TEXT
			) );
		// insert new field
		$id = wp_insert_post(array(
			'post_title' 	=> $title,
			'post_content' 	=> $args['desc'],
			'post_type' 	=> self::POST_TYPE,
			'post_status' 	=> 'publish'
		));
		// if field successully added, update more information
		if (!empty($id)){
			update_post_meta( $id, self::FIELD_NAME, $name);
			update_post_meta( $id, self::FIELD_POST_TYPE, $post_type);
			update_post_meta( $id, self::FIELD_TYPE, $args['type']);
			update_post_meta( $id, self::FIELD_INPUT_TYPE, $args['input_type']);
			update_post_meta( $id, self::FIELD_FILTERABLE, $args['filterable'] ? '1' : '0');
			update_post_meta( $id, self::FIELD_SEARCHABLE, $args['searchable'] ? '1' : '0');
			update_post_meta( $id, self::FIELD_QUERY_TYPE, $args['query_type']);
			update_post_meta( $id, self::FIELD_MANDATORY, $args['mandatory'] ? '1' : '0');
			update_post_meta( $id, self::FIELD_OPTIONS, serialize($args['options']));
		}
		return $id;
	}
}

