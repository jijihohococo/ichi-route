<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

class DeleteMethodMiddleware extends MainMiddleware
{

	public function handle()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
			return showErrorPage("404 - Method Not Found", 404);
		}
		return $this->next();
	}


}