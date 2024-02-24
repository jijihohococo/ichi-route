<?php

use JiJiHoHoCoCo\IchiRoute\UI\ErrorPage;

if (!function_exists('method')) {
	function method(string $key)
	{
		echo '<input type="hidden" name="__method" value="' . $key . '">';
	}
}

if (!function_exists('generateCSRFToken')) {
	function generateCSRFToken()
	{
		if (empty($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token'])) {
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		}
	}
}

if (!function_exists('csrfToken')) {
	function csrfToken()
	{
		echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
	}
}

if (!function_exists('getDirectURL')) {
	function getDirectURL($path = null)
	{
		$url = parse_url($path == null ? $_SERVER["REQUEST_URI"] : $path, PHP_URL_PATH);
		$urlArray = explode('/', $url);
		if (end($urlArray) !== '') {
			array_push($urlArray, '');
		}
		return $urlArray;
	}
}

if (!function_exists('getRoute')) {
	function getRoute($route)
	{
		return $route[0] !== '/' ? '/' . $route : $route;
	}
}

if (!function_exists('getRouteParameter')) {
	function getRouteParameter($parameter)
	{
		return str_replace('}', '', str_replace('{', '', $parameter));
	}
}

if (!function_exists('getCachedRoute')) {
	function getCachedRoute($cachingObject, $serverURL, $requestMethod)
	{
		$newServerURL = $cachingObject->get($serverURL . $requestMethod);
		return $newServerURL == NULL && substr($serverURL, -1) == '/' ? $cachingObject->get(substr($serverURL, 0, -1) . $requestMethod) : $newServerURL;
	}
}

if (!function_exists('addExpiredDateTime')) {
	function addExpiredDateTime($expiredTime)
	{
		$date = new DateTime();
		$date->add(new DateInterval('PT' . $expiredTime . 'S'));
		$expiredTime = $date->format('Y-m-d H:i:s');
		return $expiredTime;
	}
}

if (!function_exists('route')) {
	function route($url)
	{
		return '/' . $url;
	}
}

if (!function_exists('getRequestDomain')) {
	function getRequestDomain()
	{
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			return $_SERVER['HTTP_ORIGIN'];
		} elseif (isset($_SERVER['HTTP_REFERER'])) {
			return substr($_SERVER['HTTP_REFERER'], 0, -1);
		}
	}
}

if (!function_exists('getAccessData')) {
	function getAccessData($data)
	{
		return is_array($data) ? $data : '*';
	}
}

if (!function_exists('addFolderSlash')) {
	function addFolderSlash($path)
	{
		return substr($path, 0, -1) !== '\\' ? $path . '\\' : $path;
	}
}

if (!function_exists('getSubdomainRoute')) {
	function getSubdomainRoute(string $domain, string $link)
	{
		$http = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
		return $http . $domain . '/' . $link;
	}
}

if (!function_exists('showErrorPage')) {
	function showErrorPage(string $message, int $code = 500)
	{
		$headers = getallheaders();
		if (isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json') {
			return jsonResponse([
				'status' => $code,
				'message' => $message
			], $code);
		} else {
			http_response_code($code);
			echo ErrorPage::show($message, $code);
			exit();
		}
	}
}

if (!function_exists('getCallerInfo')) {
	function getCallerInfo()
	{
		// Get the call stack
		$backtrace = debug_backtrace();
		// Skip the first element (current function)
		array_shift($backtrace);
		// Extract information about the caller
		$caller = $backtrace[0]; // Index 0 is the immediate caller
		return $caller;
	}
}

if (!function_exists('showCallerInfo')) {
	function showCallerInfo(array $callerInfo)
	{
		if (isset($callerInfo['file']) && isset($callerInfo['line'])) {
			$callerFile = $callerInfo['file'];
			$callerLine = $callerInfo['line'];
			return "\nError in file '$callerFile' at line $callerLine";
		}
		return null;
	}
}

if (!function_exists('jsonResponse')) {
	function jsonResponse(array $data, int $code = 200)
	{
		header('Content-type:application/json');
		http_response_code($code);
		echo json_encode($data);
		return TRUE;
	}
}