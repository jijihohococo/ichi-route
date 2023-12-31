<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;
use JiJiHoHoCoCo\IchiRoute\UI\NotFound;
class CSRFMiddleware extends MainMiddleware{

	public function handle(){
		if($_SERVER['REQUEST_METHOD'] == 'POST' && ((isset($_SESSION['csrf_token']) && $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']) || 
			(!isset($_SESSION['csrf_token'])))){

			if(isset($_SESSION['csrf_token'])){
				unset($_SESSION['csrf_token']);
			}
			
			echo NotFound::show('401 - CSRF Token Expired',401);
			exit();
		}
		return $this->next();
	}
}