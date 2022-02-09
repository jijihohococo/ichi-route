<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

class PutMethodMiddleware extends MethodMiddleware{
	
	use MethodMiddlewareTrait;

	public function handle(){
		return $this->check('PUT');
	}


}