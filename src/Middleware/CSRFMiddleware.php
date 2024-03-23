<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

use JiJiHoHoCoCo\IchiRoute\UI\ErrorPage;

class CSRFMiddleware extends MainMiddleware
{

	public function handle()
	{
		$headers = getallheaders();
		if (
			isset ($headers['Content-Type']) && $headers['Content-Type'] !== 'application/json' &&
			$_SERVER['REQUEST_METHOD'] == 'POST' &&
			((isset ($_SESSION['csrf_token']) && $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']) ||
				(!isset ($_SESSION['csrf_token'])))
		) {
			if (isset ($_SESSION['csrf_token'])) {
				unset($_SESSION['csrf_token']);
			}

			echo ErrorPage::show('401 - CSRF Token Expired', 401);
			exit();
		}
		return $this->next();
	}
}