<?php

class ET_NoPermissionException extends Exception{
	public function __construct(){
		$this->code = 403;
		$this->message = __('Permission Denied!', ET_DOMAIN);
	}
}

class ET_GeneralException extends Exception{
	public function __construct(){
		$this->code = 400;
		$this->message = __('There is an error occurred', ET_DOMAIN);
	}
}

?>