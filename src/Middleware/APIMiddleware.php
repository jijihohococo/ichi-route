<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

use JiJiHoHoCoCo\IchiRoute\UI\ErrorPage;

class APIMiddleware extends MainMiddleware
{

	public function handle()
	{
		$headers = getallheaders();

		if (!isset($headers['Content-Type']) || (isset($headers['Content-Type']) && $headers['Content-Type'] !== 'application/json')) {
			echo ErrorPage::show('405 - Only API Request is allowed', 405);
			exit();
		}
		return $this->next();
	}
}