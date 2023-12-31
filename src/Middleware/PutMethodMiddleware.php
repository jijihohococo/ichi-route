<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

class PutMethodMiddleware extends MainMiddleware{
	
	use MethodMiddlewareTrait;

	public function handle(){
		return $this->check('PUT');
	}


}