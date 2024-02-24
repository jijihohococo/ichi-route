<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

class PatchMethodMiddleware extends MainMiddleware
{

	use MethodMiddlewareTrait;

	public function handle()
	{
		return $this->check('PATCH');
	}


}