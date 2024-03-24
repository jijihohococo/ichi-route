<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

class PutMethodMiddleware extends MainMiddleware
{

	use MethodMiddlewareTrait;

	public function handle()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
			return showErrorPage("404 - Method Not Found", 404);
		}
		return $this->next();
	}


}