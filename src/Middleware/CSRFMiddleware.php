<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;
use JiJiHoHoCoCo\IchiRoute\UI\NotFound;
class CSRFMiddleware{

	public function handle(){
		if($_SERVER['REQUEST_METHOD']=='POST' && $_REQUEST['csrf_token']!==$_SESSION['csrf_token']){
			$notFound=new NotFound;
			echo $notFound->show('401 - CSRF Token Expired',401);
			exit();
		}
		return $this->next();
	}
}