<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

class PatchMethodMiddleware extends MainMiddleware
{

	public function handle()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
			return showErrorPage("404 - Method Not Found", 404);
		}
		return $this->next();
	}


}