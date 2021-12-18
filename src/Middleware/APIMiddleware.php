<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;
use JiJiHoHoCoCo\IchiRoute\UI\NotFound;
class APIMiddleware extends MainMiddleware{

	public function handle(){
		header('Accept: application/json');
		return $this->next();
	}
}