<?php

if(!defined('JE_LANGUAGE_PATH') )
	define('JE_LANGUAGE_PATH', WP_CONTENT_DIR.'/et-content/jobengine/lang/');

if(class_exists("ET_Base")) :
	class ET_Language extends ET_Base {

		protected $_pot_name;
		protected $_pot;

		protected $_mo_name;
		protected $_mo;

		protected $_selected_lang;

		protected $_language_list;

		protected $_wp_include_dir;

		static $option ;


		function __construct ($mo_name) {

			$this->_file_name 	= 'engine';
			$this->_pot			= new POT();

			$this->_mo			= new MO();

			$this->_wp_include_dir 	= preg_replace('/wp-content$/', 'wp-includes', WP_CONTENT_DIR);

			if(!class_exists('PO')) {
				require_once $this->get_includes_dir ().'/pomo/po.php';
			}

			self::$option	=	new ET_GeneralOptions ();
			$this->_selected_lang	=	self::$option->get_language ();

			$this->add_action ('wp_ajax_et-load-translation-form', 'load_translation_form');
			$this->add_action('wp_ajax_et-add-new-lang', 'add_new_lang');
			$this->add_action ('wp_ajax_et-save-language', 'save_language');
			$this->add_action('after_setup_theme', 'load_text_domain');
		}
		/**
		 *	create a directory et-content in wp-content to store engine themes content
		 *	@param : array sub directory path
		 *  @return : void
		*/
		public static function create_content_directory ( $path	=	array ('/lang')) {
			if(!is_dir(WP_CONTENT_DIR.'/et-content')) {
				mkdir(WP_CONTENT_DIR.'/et-content', 0755);
				$fh = fopen(WP_CONTENT_DIR.'/et-content/index.html', 'w');
			}
			foreach ($path as $value) {
				if(!is_dir(WP_CONTENT_DIR.'/et-content'.$value))
				mkdir(WP_CONTENT_DIR.'/et-content'.$value, 0755);
				$fh = fopen(WP_CONTENT_DIR.'/et-content'.$value.'/index.html', 'w');
			}
		}

		/**
		 * get wp_includes dir
		 * return string : wp_includes dir
		 */
		function get_includes_dir () {
		    return $this->_wp_include_dir;
		}
		/**
		 * list all language mo file in theme
		*/
		function get_language_list () {
			/**
			 * use wp function get_available_languages to get language list
			*/
			$custom_langs	=	get_available_languages(JE_LANGUAGE_PATH);
			$default_langs	=	get_available_languages(DEFAULT_LANGUAGE_PATH);
			foreach ($default_langs as $key => $value) {
				if(!in_array($value, $custom_langs))
					$custom_langs[]	=	$value;
			}

			$this->_language_list =  $custom_langs;
			return $this->_language_list;
		}


		/**
		 * generate a po file
		 * @param string $file_name : file name;
		 */
		function generate_pot (  ) {
			// check enginethem.po exist or not
			$b	=	glob(DEFAULT_LANGUAGE_PATH.$this->_pot_name );
			if(!empty($b)) {
				return false;
			}

			$file_name	=	$this->_pot_name;
			$makePOT	=	new MakePOT();
			$makePOT->xgettext('wp-theme', TEMPLATEPATH, DEFAULT_LANGUAGE_PATH.'/'.$file_name);

			$pot		=	$this->_pot ;
			$pot->import_from_file(DEFAULT_LANGUAGE_PATH.$file_name);

			// set file header
			$pot->set_header( 'Project-Id-Version', 'Job Engine v'.ET_VERSION);
			$pot->set_header( 'Report-Msgid-Bugs-To', ET_URL );
			$pot->set_header( 'POT-Creation-Date', gmdate( 'Y-m-d H:i:s+00:00' ) );
			$pot->set_header( 'MIME-Version', '1.0' );
			$pot->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
			$pot->set_header( 'Content-Transfer-Encoding', '8bit' );
			$pot->set_header( 'PO-Revision-Date', '2010-MO-DA HO:MI+ZONE' );
			$pot->set_header( 'Last-Translator', 'Engine Themes <contact@enginethemes.com>' );
			$pot->set_header( 'Language-Team', 'Engine Themes <contact@enginethemes.com>' );
			$pot->set_header('Plural-Forms', 'nplurals=2; plural=n == 1 ? 0 : 1');

			$pot->export_to_file(DEFAULT_LANGUAGE_PATH.$file_name,true );
			return true;

		}
		/**
		 * generate mo file
		 * @param string $file_name
		 */
		function generate_mo ( $file_name ) {
			$this->_mo_name	=	$file_name .'.mo';
			// check file exist or not
			$b	=	glob(DEFAULT_LANGUAGE_PATH.$this->_mo_name);
			if(!empty($b)) {
				return false;
			}

			$this->generate_pot();

			$mo	=	$this->_mo;

			$mo->set_header( 'Project-Id-Version', 'Job Engine v'.ET_VERSION );
			$mo->set_header( 'Report-Msgid-Bugs-To', ET_URL );
			$mo->set_header( 'MO-Creation-Date', gmdate( 'Y-m-d H:i:s+00:00' ) );
			$mo->set_header( 'MIME-Version', '1.0' );
			$mo->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
			$mo->set_header( 'Content-Transfer-Encoding', '8bit' );
			$mo->set_header( 'MO-Revision-Date', '2010-MO-DA HO:MI+ZONE' );
			$mo->set_header( 'Last-Translator', 'Engine Themes <contact@enginethemes.com>' );
			$mo->set_header( 'Language-Team', 'Engine Themes <contact@enginethemes.com>' );

			$mo->export_to_file(JE_LANGUAGE_PATH.'/'.$file_name.'.mo',true );
			return true;

		}

		/**
		 * generate translate string
		*/
		function get_translate_string () {
			$this->_pot		=	new PO();
			$this->generate_pot();
			$this->_pot->import_from_file(DEFAULT_LANGUAGE_PATH.'/engine.po',true );
			return apply_filters( 'et_get_translate_string', $this->_pot);
		}


		/**
		 *	load text domain
		*/
		function load_text_domain () {
			//load mo file and localize
			$selected_lang	=	$this->_selected_lang;
			if( in_array($selected_lang, get_available_languages(JE_LANGUAGE_PATH)) )
				load_textdomain(ET_DOMAIN, JE_LANGUAGE_PATH."/$selected_lang.mo");
			else 
				load_textdomain(ET_DOMAIN, DEFAULT_LANGUAGE_PATH."/$selected_lang.mo");
		}

		function save_language () {
			header( 'HTTP/1.0 200 OK' );
			header( 'Content-type: application/json' );
			$selected_lang	=	$_POST['lang_name'];
			$langArr		=	$this->get_language_list();
			//file name invalid
			if(	$selected_lang == '' 	 || $selected_lang == null 
				||  $selected_lang == 'null' || !in_array($selected_lang, $langArr))  {
				echo json_encode(
					array (
						'success'	=>	false,
						'msg'		=> 	__("Invalid file name!",ET_DOMAIN)
					)
				);
				exit;
			}
			$singular		=	isset($_POST['singular'])  ? $_POST['singular'] : array();
			$translation	=	isset($_POST['translations']) ? $_POST['translations'] : array();
			$context		=	isset($_POST['context']) ? $_POST['context'] : array ();
			if(empty($singular) || empty($translation) || empty($context)) {
				echo json_encode(
				array (
					'success'	=>	true,
					'msg'		=> 	__("There was no changes in your translation.",ET_DOMAIN)
				));
				exit;
			}
			$mo 			=	 new MO ();

			$mo->set_header( 'Project-Id-Version', 'jobengine v'.ET_VERSION );
			$mo->set_header( 'Report-Msgid-Bugs-To', ET_URL );
			$mo->set_header( 'MO-Creation-Date', gmdate( 'Y-m-d H:i:s+00:00' ) );
			$mo->set_header( 'MIME-Version', '1.0' );
			$mo->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
			$mo->set_header( 'Content-Transfer-Encoding', '8bit' );
			$mo->set_header( 'MO-Revision-Date', '2010-MO-DA HO:MI+ZONE' );
			$mo->set_header( 'Last-Translator', 'JOB <EMAIL@ADDRESS>' );
			$mo->set_header( 'Language-Team', 'ENGINETHEMES.COM <enginethemes@enginethemes.com>' );

			// import language file from et_content/lang if exist
			if( in_array( $selected_lang, get_available_languages(JE_LANGUAGE_PATH) ) )
				$mo->import_from_file(JE_LANGUAGE_PATH.'/'.$selected_lang.'.mo');
			else
				$mo->import_from_file(DEFAULT_LANGUAGE_PATH.'/'.$selected_lang.'.mo');
			foreach ($singular as $key => $value) {

				if($translation[$key] == "") {
					if(isset( $mo->entries[$value] ))
						unset($mo->entries[$value]);
					continue;
				}
				if($context[$key] != '' )
					$mo->add_entry(new Translation_Entry(
										array (
											'singular' => trim ( stripcslashes($value),''),
											'context'  => trim (stripcslashes($context[$key]),''),
											'translations' => array('0'=> trim((stripcslashes($translation[$key])), '')
										)
									)
								)
						);
				else
					$mo->add_entry(new Translation_Entry(
										array (
											'singular' => trim ( stripcslashes($value),''),
											'translations' => array('0'=> trim((stripcslashes( $translation[$key]) ), '')
										)
									)
								)
						);
			}

			$mo->export_to_file(JE_LANGUAGE_PATH.'/'.$selected_lang.'.mo');
			echo json_encode(
				array (
					'success'	=>	true,
					'msg'		=> 	__("Translation saved! ",ET_DOMAIN)
			));
			exit;
		}

		function add_new_lang () {
			$lang_name	=	str_replace(' ', '-',$_POST['lang_name']);
			$lang_arr	=	$this->get_language_list();
			if( $lang_name== "" || in_array($lang_name, $lang_arr)) {
				$return =	array (
					'success'	=> false,
					'msg'		=> __("Invalid file name!",ET_DOMAIN)
				);
			} else {
				if($this->generate_mo($lang_name)) {
					// update language options
					$general_opts	=	new ET_GeneralOptions();
					$general_opts->set_language($lang_name);

					$return =	array (
						'success'	=> true,
						'msg'		=> __("Adding new language successfully.",ET_DOMAIN),
						'lang_name'	=> $lang_name
					);
				}
			}
			wp_send_json($return);

		}

		/*
		 * ajax function load translation form
		 */
		function load_translation_form () {
			$lang_name	=	$_POST['lang_name'];
			$pot		=	new PO();
			/*et_generate_pot();
			$pot->import_from_file(DEFAULT_LANGUAGE_PATH.'/engine.po',true );
			*/
			$translated	=	array ();
			$mo			=	new MO();
			$langArr	=	$this->get_language_list();

			if( in_array( $lang_name, $langArr ) ){
				if( in_array( $lang_name, get_available_languages(JE_LANGUAGE_PATH) ) )
					$mo->import_from_file(JE_LANGUAGE_PATH.'/'.$lang_name.'.mo');
				else
					$mo->import_from_file(DEFAULT_LANGUAGE_PATH.'/'.$lang_name.'.mo');

				$translated	=	$mo->entries;
			}

			$trans_arr	=	et_get_translate_string ();

			$data		=	'';
			foreach ($trans_arr as $key =>  $value ) {

				if(isset($translated[$key])) continue;
				if($value->context != '') continue;

				$singular			=	htmlentities(stripcslashes( $value->singular ),ENT_COMPAT, "UTF-8" );
				if( empty($value->translations)) {
					$translate_txt	=	'';//$singular;
				} else {
					$translate_txt	=	htmlentities(stripcslashes( $value->translations[0] ),ENT_COMPAT, "UTF-8" );
				}

			$data	.=
				'<div class="form-item">
					<div class="label">'. $singular. '</div>
					<input type="hidden" value="'.$singular.'" name="singular[]">
					<input type="hidden" value="'.$value->context.'" name="context[]">
					<textarea type="text"  name="translations[]" class="autosize" row="1" style="height: auto;overflow: visible;"
						placeholder="'. __("Type the translation in your language",ET_DOMAIN).'" >'.$translate_txt.'</textarea>
				</div>';
			}
			foreach ($translated as $key =>  $value ) {
				$singular			=	htmlentities(stripcslashes( $value->singular ),ENT_COMPAT, "UTF-8" );
				if( empty($value->translations)) {
					$translate_txt	=	'';//$singular;
				} else {
					$translate_txt	=	htmlentities(stripcslashes( $value->translations[0] ),ENT_COMPAT, "UTF-8" );
				}

				$data	.=	'
					<div class="form-item">
						<div class="label">'. $singular. '</div>
						<input type="hidden" value="'.$singular.'" name="singular[]">
						<input type="hidden" value="'.$value->context.'" name="context[]">
						<textarea  type="text"  name="translations[]" class="autosize" row="1" style="height: auto;overflow: visible;"
						placeholder="'. __("Type the translation in your language",ET_DOMAIN).'" >'.$translate_txt.'</textarea>
					</div>';
			}
			$return 	=	array (
				'success'	=> true,
				'data'		=> $data,
				'msg'		=> __("Loading successfully!",ET_DOMAIN)
			);
			wp_send_json($return);
		}

		/**
		 * change language : if language file not exist return false
		 * if language file not in ET_LANG_DIR copy it from DEFAULT_LANG to ET_LANG_DIR 
		*/
		function change_language ( $lang ) {
			if( !in_array($lang, et_get_language_list() )) {
				return 0;
			}

			if(!in_array( $lang, get_available_languages(JE_LANGUAGE_PATH)) ) {
				$mo 			=	 new MO ();
				$mo->set_header( 'Project-Id-Version', 'jobengine v'.ET_VERSION );
				$mo->set_header( 'Report-Msgid-Bugs-To', ET_URL );
				$mo->set_header( 'MO-Creation-Date', gmdate( 'Y-m-d H:i:s+00:00' ) );
				$mo->set_header( 'MIME-Version', '1.0' );
				$mo->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
				$mo->set_header( 'Content-Transfer-Encoding', '8bit' );
				$mo->set_header( 'MO-Revision-Date', '2010-MO-DA HO:MI+ZONE' );
				$mo->set_header( 'Last-Translator', 'JOB <EMAIL@ADDRESS>' );
				$mo->set_header( 'Language-Team', 'ENGINETHEMES.COM <enginethemes@enginethemes.com>' );

				$mo->import_from_file(DEFAULT_LANGUAGE_PATH.'/'.$lang.'.mo');

				$mo->export_to_file(JE_LANGUAGE_PATH.'/'.$lang.'.mo') ;
			}

			//$general	=	new ET_GeneralOptions();
			$response	=	self::$option->set_language($lang);

			return true;
		}

	}
endif;


add_action('after_setup_theme', 'et_load_text_domain');
/**
 *	load text domain
*/
function et_load_text_domain () {
	//load mo file and localize
	$general_opts	=	new ET_GeneralOptions ();
	$selected_lang	=	$general_opts->get_language ();
	et_create_content_directory_jobengine ();

	if( in_array($selected_lang, get_available_languages(JE_LANGUAGE_PATH)) )
		load_textdomain(ET_DOMAIN,JE_LANGUAGE_PATH."/$selected_lang.mo");
	else
		load_textdomain(ET_DOMAIN, DEFAULT_LANGUAGE_PATH."/$selected_lang.mo");
}

/*
 * ajax sync save language translations
 */
add_action ('wp_ajax_et-save-language', 'et_save_language');
function et_save_language () {
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	$selected_lang	=	$_POST['lang_name'];
	$langArr		=	et_get_language_list(JE_LANGUAGE_PATH);
	//file name invalid
	if(	$selected_lang == '' 	 || $selected_lang == null
		||  $selected_lang == 'null' || !in_array($selected_lang, $langArr))  {
		echo json_encode(
			array (
				'success'	=>	false,
				'msg'		=> 	__("Invalid file name!",ET_DOMAIN)
			)
		);
		exit;
	}

	$singular		=	isset($_POST['singular'])  ? $_POST['singular'] : array();
	$translation	=	isset($_POST['translations']) ? $_POST['translations'] : array();
	$context		=	isset($_POST['context']) ? $_POST['context'] : array ();

	if(empty($singular) || empty($translation) || empty($context)) {
		echo json_encode(
		array (
			'success'	=>	true,
			'msg'		=> 	__("There was no changes in your translation.",ET_DOMAIN)
		));
		exit;
	}

	$mo 			=	 new MO ();

	$mo->set_header( 'Project-Id-Version', 'jobengine v'.ET_VERSION );
	$mo->set_header( 'Report-Msgid-Bugs-To', ET_URL );
	$mo->set_header( 'MO-Creation-Date', gmdate( 'Y-m-d H:i:s+00:00' ) );
	$mo->set_header( 'MIME-Version', '1.0' );
	$mo->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
	$mo->set_header( 'Content-Transfer-Encoding', '8bit' );
	$mo->set_header( 'MO-Revision-Date', '2010-MO-DA HO:MI+ZONE' );
	$mo->set_header( 'Last-Translator', 'JOB <EMAIL@ADDRESS>' );
	$mo->set_header( 'Language-Team', 'ENGINETHEMES.COM <enginethemes@enginethemes.com>' );

	// import language file from et_content/lang if exist
	if( in_array( $selected_lang, get_available_languages(JE_LANGUAGE_PATH) ) )
		$mo->import_from_file(JE_LANGUAGE_PATH.'/'.$selected_lang.'.mo');
	else
		$mo->import_from_file(DEFAULT_LANGUAGE_PATH.'/'.$selected_lang.'.mo');

	foreach ($singular as $key => $value) {

		if($context[$key] != '' )
			$mo->add_entry(new Translation_Entry(
								array (
									'singular' => trim ( stripcslashes($value),''),
									'context'  => trim (stripcslashes($context[$key]),''),
									'translations' => array('0'=> trim((stripcslashes($translation[$key])), '')
								)
							)
						)
				);
		else
			$mo->add_entry(new Translation_Entry(
								array (
									'singular' => trim ( stripcslashes($value),''),
									'translations' => array('0'=> trim((stripcslashes( $translation[$key]) ), '') 
								)
							)
						)
				);
	}

	$mo->export_to_file(JE_LANGUAGE_PATH.'/'.$selected_lang.'.mo');

	echo json_encode(
		array (
			'success'	=>	true,
			'msg'		=> 	__("Translation saved! ",ET_DOMAIN)
	));
	exit;
}
/*
 * ajax sync create new language
 */
add_action('wp_ajax_et-add-new-lang', 'et_add_new_lang');
function et_add_new_lang () {

	$lang_name	=	str_replace(' ', '-',$_POST['lang_name']);
	$lang_arr	=	et_get_language_list();
	if( $lang_name== "" || in_array($lang_name, $lang_arr)) {
		$return =	array (
			'success'	=> false,
			'msg'		=> __("Invalid file name!",ET_DOMAIN)
		);
	} else {
		if(je_generate_mo($lang_name)) {
			// update language options
			$general_opts	=	new ET_GeneralOptions();
			$general_opts->set_language($lang_name);

			$return =	array (
				'success'	=> true,
				'msg'		=> __("Adding new language successfully.",ET_DOMAIN),
				'lang_name'	=> $lang_name
			);
		}
	}
	wp_send_json($return);

}

/*
 * ajax function load translation form
 */
function et_load_translation_form () {
	$lang_name	=	$_POST['lang_name'];
	$pot		=	new PO();
	/*et_generate_pot();
	$pot->import_from_file(DEFAULT_LANGUAGE_PATH.'/engine.po',true );
	*/
	$translated	=	array ();
	$mo			=	new MO();
	if( in_array( $lang_name, get_available_languages(JE_LANGUAGE_PATH) ) )
		$mo->import_from_file(JE_LANGUAGE_PATH.'/'.$lang_name.'.mo');
	else
		$mo->import_from_file(DEFAULT_LANGUAGE_PATH.'/'.$lang_name.'.mo');

	$translated	=	$mo->entries;

	$trans_arr	=	et_get_translate_string ();

	$data		=	'';

	foreach ($trans_arr as $key =>  $value ) {
		if(isset($translated[$key])) continue;
		if($value->context != '') continue;

		$singular			=	htmlentities(stripcslashes( $value->singular ),ENT_COMPAT, "UTF-8" );
		if( empty($value->translations)) {
			$translate_txt	=	'';//$singular;
		} else {
			$translate_txt	=	htmlentities(stripcslashes( $value->translations[0] ),ENT_COMPAT, "UTF-8" );
		}

	$data	.=
		'<div class="form-item">
			<div class="label">'. $singular. '</div>
			<input type="hidden" value="'.$singular.'" name="singular[]">
			<input type="hidden" value="'.$value->context.'" name="context[]">
			<textarea type="text"  name="translations[]" class="autosize" row="1" style="height: auto;overflow: visible;"
				placeholder="'. __("Type the translation in your language",ET_DOMAIN).'" >'.$translate_txt.'</textarea>
		</div>';
	}
	foreach ($translated as $key =>  $value ) {
		$singular			=	htmlentities(stripcslashes( $value->singular ),ENT_COMPAT, "UTF-8" );
		if( empty($value->translations)) {
			$translate_txt	=	'';//$singular;
		} else {
			$translate_txt	=	htmlentities(stripcslashes( $value->translations[0] ),ENT_COMPAT, "UTF-8" );
		}
		$data	.=	'
			<div class="form-item">
				<div class="label">'. $singular. '</div>
				<input type="hidden" value="'.$singular.'" name="singular[]">
				<input type="hidden" value="'.$value->context.'" name="context[]">
				<textarea  type="text"  name="translations[]" class="autosize" row="1" style="height: auto;overflow: visible;"
				placeholder="'. __("Type the translation in your language",ET_DOMAIN).'" >'.$translate_txt.'</textarea>
			</div>';
	}
	$return 	=	array (
		'success'	=> true,
		'data'		=> $data,
		'msg'		=> __("Loading successfully!",ET_DOMAIN)
	);
	header( 'HTTP/1.0 200 OK' );
	header( 'Content-type: application/json' );
	echo json_encode($return);
	exit;
}
add_action ('wp_ajax_et-load-translation-form', 'et_load_translation_form');

/**
 * change language : if language file not exist return false
 * if language file not in ET_LANG_DIR copy it from DEFAULT_LANG to ET_LANG_DIR
*/
function et_change_language ( $lang ) {
	if(!in_array( $lang, get_available_languages(JE_LANGUAGE_PATH)) ) {
		$mo 			=	 new MO ();
		$mo->set_header( 'Project-Id-Version', 'jobengine v'.ET_VERSION );
		$mo->set_header( 'Report-Msgid-Bugs-To', ET_URL );
		$mo->set_header( 'MO-Creation-Date', gmdate( 'Y-m-d H:i:s+00:00' ) );
		$mo->set_header( 'MIME-Version', '1.0' );
		$mo->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
		$mo->set_header( 'Content-Transfer-Encoding', '8bit' );
		$mo->set_header( 'MO-Revision-Date', '2010-MO-DA HO:MI+ZONE' );
		$mo->set_header( 'Last-Translator', 'JOB <EMAIL@ADDRESS>' );
		$mo->set_header( 'Language-Team', 'ENGINETHEMES.COM <enginethemes@enginethemes.com>' );

		$mo->import_from_file(DEFAULT_LANGUAGE_PATH.'/'.$lang.'.mo');

		$mo->export_to_file(JE_LANGUAGE_PATH.'/'.$lang.'.mo') ;
	}

	$general	=	new ET_GeneralOptions();
	$response	=	$general->set_language($lang);

	return true;
}
/*	copy list .mo file to et-content/jobengine.

*/

function et_create_content_directory_jobengine ( $path	=	array ('/lang')) {
	et_create_content_directory (array('/jobengine', '/jobengine/lang'));
	// copy list file .mo to jobengine/lang
	$custom_langs	=	get_available_languages(ET_LANGUAGE_PATH);
	$je_lang_dir = WP_CONTENT_DIR . '/et-content/jobengine/lang/';

	foreach ($custom_langs as $lang){
		if(is_file( ET_LANGUAGE_PATH . $lang . '.mo') && !is_file($je_lang_dir . $lang . '.mo')){
			copy( ET_LANGUAGE_PATH . $lang .'.mo', $je_lang_dir . $lang . '.mo' );
		}
	}

}


/**
 * generate mo file
 * @param string $file_name
 */
function je_generate_mo ( $file_name ) {
	// check file exist or not
	$b	=	glob(DEFAULT_LANGUAGE_PATH.'/'.$file_name.'.mo');
	if(!empty($b)) {
		return false;
	}
	et_generate_pot();
	$mo	=	new MO ();

	$mo->set_header( 'Project-Id-Version', 'Job Engine v'.ET_VERSION );
	$mo->set_header( 'Report-Msgid-Bugs-To', ET_URL );
	$mo->set_header( 'MO-Creation-Date', gmdate( 'Y-m-d H:i:s+00:00' ) );
	$mo->set_header( 'MIME-Version', '1.0' );
	$mo->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
	$mo->set_header( 'Content-Transfer-Encoding', '8bit' );
	$mo->set_header( 'MO-Revision-Date', '2010-MO-DA HO:MI+ZONE' );
	$mo->set_header( 'Last-Translator', 'Engine Themes <contact@enginethemes.com>' );
	$mo->set_header( 'Language-Team', 'Engine Themes <contact@enginethemes.com>' );
	$mo->export_to_file(JE_LANGUAGE_PATH.'/'.$file_name.'.mo',true );
	return true;
}
