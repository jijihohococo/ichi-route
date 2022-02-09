<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

class DeleteMethodMiddleware extends MethodMiddleware{
	
	use MethodMiddlewareTrait;

	public function handle(){
		return $this->check('DELETE');
	}


}