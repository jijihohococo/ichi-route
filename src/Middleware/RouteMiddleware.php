<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;
class RouteMiddleware{

	
	public function check($middlewares=[],$route,$parameters=[]){
		$middlewares=array_unique($middlewares);
		$middlewareObjects=[];
		foreach($middlewares as $key => $middleware){
			$middlewareData=explode(':', $middleware);
			if(isset($middlewareData[0])){
				$class=$middlewareData[0];
				$middlewareClassString=strpos($class, 'JiJiHoHoCoCo\IchiRoute\Middleware')==TRUE ? $class : $route->getBaseMiddlewarePath().$class;
				$middlewareClass=new $middlewareClassString;
				if(!$middlewareClass instanceof MainMiddleware){
					throw new \Exception("You need to extend JiJiHoHoCoCo\IchiRoute\Middleware\MainMiddleware", 1);
				}
				if(!method_exists($middlewareClass, 'handle')){
					throw new \Exception("You need to have 'handle' function in {$middlewareClassString}", 1);
				}
				if(isset($middlewareData[1])){
					$middlewareParameters=[];
					foreach(explode(',', $middlewareData[1]) as $middlewareParameter ){
						if(isset($parameters[$middlewareParameter])){
							$middlewareParameters[]=$parameters[$middlewareParameter];
						}
					}
					$middlewareClass->setParameters($middlewareParameters);
				}
				$middlewareObjects[]=$middlewareClass;
				if($key>0){
					$oldMiddlewareObject=$middlewareObjects[$key-1];
					$oldMiddlewareObject->setNext($middlewareClass);
				}
			}else{
				throw new \Exception("You don't pass the middleware class", 1);
			}
		}
		return $middlewareObjects[0]->handle();
	}
}