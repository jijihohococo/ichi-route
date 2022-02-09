<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

class PatchMethodMiddleware extends MethodMiddleware{
	
	use MethodMiddlewareTrait;

	public function handle(){
		return $this->check('PATCH');
	}


}