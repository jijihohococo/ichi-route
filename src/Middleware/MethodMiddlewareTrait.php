<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

use JiJiHoHoCoCo\IchiRoute\UI\ErrorPage;

trait MethodMiddlewareTrait
{

	public function check(string $key)
	{
		$methodNotFound = "404 - Method Not Found";
		if (!isset($_REQUEST['__method']) || (isset($_REQUEST['__method']) && $_REQUEST['__method'] !== $key)) {
			return showErrorPage("404 - Method Not Found", 404);
		}
		return $this->next();
	}
}