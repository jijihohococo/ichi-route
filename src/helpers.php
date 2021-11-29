<?php

if(!function_exists('getDirectURL')){
	function getDirectURL($path=null){
		$url=parse_url($path==null ? $_SERVER["REQUEST_URI"] : $path, PHP_URL_PATH);
		$urlArray=explode('/', $url);
		if(end($urlArray)!==''){
			array_push($urlArray,'');
		}
		return $urlArray;
	}
}

if(!function_exists('getRoute')){
	function getRoute($route){
		return $route[0]!=='/'?'/'.$route:$route;
	}
}

if(!function_exists('getRouteParameter')){
	function getRouteParameter($parameter){
		return str_replace('}', '', str_replace('{', '', $parameter) );
	}
}

if(!function_exists('getCachedRoute')){
	function getCachedRoute($cachingObject,$serverURL,$requestMethod){
		$newServerURL=$cachingObject->get($serverURL.$requestMethod);
		return $newServerURL==NULL && substr($serverURL, -1)=='/' ? $cachingObject->get(substr($serverURL,0,-1).$requestMethod) : $newServerURL;
	}
}

if(!function_exists('addExpiredDateTime')){
	function addExpiredDateTime($expiredTime){
		$date = new DateTime();
		$date->add(new DateInterval('PT'.$expiredTime.'S'));
		$expiredTime=$date->format('Y-m-d H:i:s');
		return $expiredTime;
	}
}

if(!function_exists('route')){
	function route($url){
		return '/' . $url;
	}
}