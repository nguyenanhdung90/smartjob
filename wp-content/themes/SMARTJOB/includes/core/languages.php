<?php
define('DEFAULT_LANGUAGE_PATH', get_template_directory().'/lang');

/**
 *	create a directory et-content in wp-content to store engine themes content
 *	@param : array sub directory path
 *  @return : void
*/
function et_create_content_directory ( $path	=	array ('/lang')) {
	if(!is_dir(WP_CONTENT_DIR.'/et-content')) {
		mkdir(WP_CONTENT_DIR.'/et-content', 0755);
		$fh = fopen(WP_CONTENT_DIR.'/et-content/index.html', 'w');
	}	
	foreach ($path as $value) {
		if(!is_dir(WP_CONTENT_DIR.'/et-content'.$value))
		mkdir(WP_CONTENT_DIR.'/et-content'.$value, 0755);
		//$fh = fopen(WP_CONTENT_DIR.'/et-content'.$value.'/index.html', 'w');
	}
}

/**
 * get wp_includes dir
 * return string : wp_includes dir 
 */
function et_get_includes_dir () {
	$wp_include_dir = preg_replace('/wp-content$/', 'wp-includes', WP_CONTENT_DIR);
    return $wp_include_dir;
}

if(!class_exists('PO')) {
	require_once et_get_includes_dir ().'/pomo/po.php';
}
/**
 * list all language mo file in theme
*/
function et_get_language_list ($path = '') {
	if($path == '') $path	=	ET_LANGUAGE_PATH;
		$custom_langs	=	get_available_languages($path);
		$default_langs	=	get_available_languages(DEFAULT_LANGUAGE_PATH);
		foreach ($default_langs as $key => $value) {
			if(!in_array($value, $custom_langs))
				$custom_langs[]	=	$value;
		}
		return $custom_langs;
}


/**
 * generate a po file
 * @param string $file_name : file name;
 */
function et_generate_pot ( $theme_name = "JobEngine" ) {
	// check enginethem.po exist or not
	$b	=	glob(DEFAULT_LANGUAGE_PATH.'/engine.po');

	if(!empty($b)) {
		return false;
	}

	$file_name	=	'engine';
	$makePOT	=	new MakePOT();
	$makePOT->xgettext('wp-theme', get_template_directory(), DEFAULT_LANGUAGE_PATH.'/'.$file_name.'.po');


	$pot		=	new PO();
	$pot->import_from_file(DEFAULT_LANGUAGE_PATH.'/'.$file_name.'.po');

	// set file header
	$pot->set_header( 'Project-Id-Version', $theme_name.' v'.ET_VERSION);
	$pot->set_header( 'Report-Msgid-Bugs-To', ET_URL );
	$pot->set_header( 'POT-Creation-Date', gmdate( 'Y-m-d H:i:s+00:00' ) );
	$pot->set_header( 'MIME-Version', '1.0' );
	$pot->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
	$pot->set_header( 'Content-Transfer-Encoding', '8bit' );
	$pot->set_header( 'PO-Revision-Date', '2010-MO-DA HO:MI+ZONE' );
	$pot->set_header( 'Last-Translator', 'Engine Themes <contact@enginethemes.com>' );
	$pot->set_header( 'Language-Team', 'Engine Themes <contact@enginethemes.com>' );
	$pot->set_header('Plural-Forms', 'nplurals=2; plural=n == 1 ? 0 : 1');

	$pot->export_to_file(DEFAULT_LANGUAGE_PATH.'/'.$file_name.'.po',true );
	return true;	
}
/**
 * generate mo file 
 * @param string $file_name
 */
function et_generate_mo ( $file_name , $theme_name = "JobEngine" ) {
	// check file exist or not
	$b	=	glob(DEFAULT_LANGUAGE_PATH.'/'.$file_name.'.mo');

	if(!empty($b)) {
		return false;
	}

	et_generate_pot($theme_name);

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

	$mo->export_to_file(ET_LANGUAGE_PATH.'/'.$file_name.'.mo',true );
	return true;

}

/**
 * generate translate string
*/
function et_get_translate_string ($filename = 'engine.po') {
	$pot		=	new PO();
	et_generate_pot();
	$pot->import_from_file(DEFAULT_LANGUAGE_PATH . '/' . $filename, true );
	return apply_filters( 'et_get_translate_string', $pot->entries);
}