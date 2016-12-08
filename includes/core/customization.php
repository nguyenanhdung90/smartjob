<?php 

/**
 * Convert a RGB color to HSV color
 * @since 1.0
 * @author Toan
 */
function et_rgb_to_hsl($color){	
	// validate if input value is a proper color
	if ( !preg_match('/^#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/', $color, $parts) )
		return false;

	$r = hexdec($parts[1]) / 255;
	$g = hexdec($parts[2]) / 255;
	$b = hexdec($parts[3]) / 255;

	$maxi = max($r, $g, $b);
	$mini = min($r, $g, $b);
	$c = $maxi - $mini;

	if ( $c === 0){
		$hcomma = 0;
	}else {
		switch( $maxi ){
			case $r:
				$hcomma = ( ( $g - $b ) / $c ) % 6;
				break;
			case $g:
				$hcomma = ( ( $b - $r ) / $c ) + 2;
				break;
			case $b:
				$hcomma = ( ( $r - $g ) / $c ) + 4;
				break;
		}
	}
	$hue = 60 * $hcomma;

	$lightness = 0.5*($maxi+$mini);

	$saturation = $c == 0 ? 0 : ($c / 1 - abs(2 * $lightness - 1) );
	return array(
		'hue' 			=> $hue, 
		'saturation' 	=> $saturation, 
		'lightness' 	=> $lightness
		);
}

/**
 * Convert a HSV color to RGB color
 * @since 1.0
 * @author Toan
 */
function et_hsl_to_rgb($hue, $saturation, $lightness){
	//$c = $lightness * $saturation;
	$c = (1 - abs((2 * $lightness) - 1)) * $saturation;

	$hcomma = $hue / 60; 

	$mod = $hcomma - ( floor( floor($hcomma) / 2 ) * 2 );

	$x = $c * ( 1 - abs( $mod - 1  ) );

	$return = array();
	$t = floor($hcomma);
	switch ($t) {
		default:
		case 0:
			$return = array( 'red' => $c, 'green' => $x, 'blue' => 0 );
			break;
		case 1:
			$return = array( 'red' => $x, 'green' => $c, 'blue' => 0 );
			break;
		case 2:
			$return = array( 'red' => 0, 'green' => $c, 'blue' => $x );
			break;
		case 3:
			$return = array( 'red' => 0, 'green' => $x, 'blue' => $c );
			break;
		case 4:
			$return = array( 'red' => $x, 'green' => 0, 'blue' => $c );
			break;
		case 5:
			$return = array( 'red' => $c, 'green' => 0, 'blue' => $x );
			break;
	}
	$m = $lightness - ($c * 0.5);

	$return['red'] = round( ($return['red'] + $m )* 255);
	$return['green'] = round( ($return['green'] + $m )* 255);
	$return['blue'] = round( ($return['blue'] + $m )* 255);

	return '#' . str_pad(dechex($return['red']), 2, 0, STR_PAD_LEFT) . 
		str_pad(dechex($return['green']), 2, 0, STR_PAD_LEFT) . 
		str_pad(dechex($return['blue']), 2, 0, STR_PAD_LEFT);
}

/**
 * Generate simmiliar colors from a origin color. 
 * Include 10 lighter version & 10 darker version 
 * Return array consist of original color, lighter versions, darker version
 * @author Toan
 * @since 1.0
 */
function et_generate_colors( $color ){

	// validate if input value is a proper color
	if ( !preg_match('/^#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/', $color, $parts) )
		return false;

	// declare a resule array
	$result = array(
		'org' => $color,
		'lighter' => array(),
		'darker' => array()
		);

	// convert to hsv
	$hsvcolor = et_rgb_to_hsl($color);

	for( $i = 0; $i < 10; $i++ ){
		$lighter 				= $hsvcolor;
		$lighter['lightness']   += 0.04*$i;
		if ( $lighter['lightness'] > 1 ) $lighter['lightness'] = 1;
		$darker 				= $hsvcolor;
		$darker['lightness'] 	-= 0.04*$i;
		if ( $darker['lightness'] < 0 ) $darker['lightness'] = 0;
		$result['lighter'][] = et_hsl_to_rgb($lighter['hue'], $lighter['saturation'], $lighter['lightness']);
		$result['darker'][] = et_hsl_to_rgb($darker['hue'], $darker['saturation'], $darker['lightness']);
	}
	return $result;
}

/**
 * Convert less file to css file. Variables are allowed
 * @since 1.0
 * @param $less path to less file
 * @param @css path to css file
 * @param @variables array of variables
 */
function et_less2css($less, $css, $variables = array()){

	if(!class_exists('lessc')) {
		require FRAMEWORK_PATH . "/lib/lessc.inc.php";	
		if ( !file_exists($less) ) return new WP_Error("file_not_exist", __('File is not exist', ET_DOMAIN));
	}	

	$lesslib = new lessc();
	$lesslib->setVariables($variables);
	$lesslib->compileFile($less, $css);

	return true;
}