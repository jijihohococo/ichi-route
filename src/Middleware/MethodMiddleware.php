<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;
use JiJiHoHoCoCo\IchiRoute\UI\NotFound;
class MethodMiddleware extends MainMiddleware{

	public function handle(string $key){
		if(!isset($_REQUEST['__method']) || (isset($_REQUEST['__method']) && $_REQUEST['__method']!==$key ) ){
			echo NotFound::show('404 - Not Found');
			exit();
		}
		return $this->next();
	}
}