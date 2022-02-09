<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

class DeleteMethodMiddleware extends MainMiddleware{
	
	use MethodMiddlewareTrait;

	public function handle(){
		return $this->check('DELETE');
	}


}