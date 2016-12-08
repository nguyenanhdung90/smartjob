<?php

/**
 * Mobile Detect
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class ET_MobileDetect
{

	protected $accept;
	protected $userAgent;
	protected $isMobile = false;
	protected $isTablet	= false;
	protected $isAndroid = null;
	protected $isAndroidtablet = null;
	protected $isIphone = null;
	protected $isIpad = null;
	protected $isBlackberry = null;
	protected $isBlackberrytablet = null;
	protected $isOpera = null;
	protected $isPalm = null;
	protected $isWindows = null;
	protected $isWindowsphone = null;
	protected $isGeneric = null;
	protected $devices = array(
		"android" => "android.*mobile",
		"androidtablet" => "android(?!.*mobile)",
		"blackberry" => "(blackberry|bb10)",
		"blackberrytablet" => "rim tablet os",
		"iphone" => "(iphone|ipod)",
		"ipad" => "(ipad)",
		"palm" => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)",
		"windows" => "windows ce; (iemobile|ppc|smartphone)",
		"windowsphone" => "Windows Phone 8.0|Windows Phone OS|XBLWP7|ZuneWP7",
		"generic" => "(kindle|mobile|mmp|midp|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap|opera mini)"
	);

	protected $tabletdevices = array(
		"androidtablet" => "android(?!.*mobile)",
		"blackberrytablet" => "rim tablet os",
		"ipad" => "(ipad)"
	);

	public function __construct()
	{
		$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
		$this->accept = isset($_SERVER['HTTP_ACCEPT'])?$_SERVER['HTTP_ACCEPT']:"";
		if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
			$this->isMobile = true;
		} elseif (strpos($this->accept, 'text/vnd.wap.wml') > 0 || strpos($this->accept, 'application/vnd.wap.xhtml+xml') > 0) {
			$this->isMobile = true;
		} else {
			foreach ($this->devices as $device => $regexp) {
				if ($this->isDevice($device)) {
					$this->isMobile = true;
					break;
				}
			}
		}
	}

	/**
	 * Overloads isAndroid() | isAndroidtablet() | isIphone() | isIpad() | isBlackberry() | isBlackberrytablet() | isPalm() | isWindowsphone() | isWindows() | isGeneric() through isDevice()
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return bool
	 */
	public function __call($name, $arguments)
	{
		$device = substr($name, 2);
		if ($name == "is" . ucfirst($device) && array_key_exists(strtolower($device), $this->devices)) {
			return $this->isDevice($device);
		} else {
			trigger_error("Method $name not defined", E_USER_WARNING);
		}
	}

	/**
	 * Returns true if any type of mobile device detected, including special ones
	 * @return bool
	 */
	public function isMobile()
	{
		return $this->isMobile;
	}

	/**
	 * Returns true if any type of mobile device detected, including special ones
	 * @return bool
	 */
	public function isTablet()
	{
		foreach ($this->tabletdevices as $device => $regexp) {
			if ($this->isDevice($device)) {
				$this->isTablet = true;
				break;
			}
		}
		return $this->isTablet;
	}

	protected function isDevice($device)
	{

		$var = "is" . ucfirst($device);
		$return = $this->$var === null ? (bool) preg_match("/" . $this->devices[strtolower($device)] . "/i", $this->userAgent) : $this->$var;
		if ($device != 'generic' && $return == true) {
			$this->isGeneric = false;
		}

		return $return;
	}

}

/**
 * global variable mobile detector
 */
global $et_mobile_detector;
$et_mobile_detector = new ET_MobileDetect();

/**
 * Verify if current browser is mobile
 * @since 1.0
 */
function et_is_mobile($name = ''){
	global $et_mobile_detector;
	return $et_mobile_detector->isMobile();
}
if(!function_exists('et_is_table')) :
	function et_is_table(){
		global $et_mobile_detector;
		return $et_mobile_detector->isTablet();
	}
endif;
