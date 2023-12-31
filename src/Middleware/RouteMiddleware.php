<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;
use ReflectionMethod;
class RouteMiddleware{

	private $domainParameters;

	public function setDomainParameters($domainParameters){
		$this->domainParameters = $domainParameters;
	}

	private function getDomainParameters(){
		return $this->domainParameters;
	}
	
	public function check($middlewares = [], $route, $parameters = []){
		$middlewares = array_unique($middlewares);
		$middlewareObjects = [];
		foreach($middlewares as $key => $middleware){
			$middlewareData = explode(':', $middleware);
			if(isset($middlewareData[0])){
				$class = $middlewareData[0];
				$middlewareClassString = strpos($class, 'JiJiHoHoCoCo\IchiRoute\Middleware')!==FALSE ? $class : $route->getBaseMiddlewarePath().$class;
				if(!class_exists($middlewareClassString)){
					throw new \Exception($middlewareClassString . " Middleware Class is not exist", 1);
				}
				$middlewareClass = new $middlewareClassString;
				if(!$middlewareClass instanceof MainMiddleware){
					throw new \Exception("Your ".$middlewareClassString." need to extend JiJiHoHoCoCo\IchiRoute\Middleware\MainMiddleware", 1);
				}
				if(!method_exists($middlewareClass, 'handle')){
					throw new \Exception("You need to have 'handle' function in {$middlewareClassString}", 1);
				}
				if(isset($middlewareData[1])){
					$middlewareParameters = [];
					foreach(explode(',', $middlewareData[1]) as $middlewareParameter ){
						if(isset($parameters[$middlewareParameter])){
							$middlewareParameters[] = $parameters[$middlewareParameter];
						}
					}
					$middlewareClass->setParameters($middlewareParameters);
					$domainParameters = $this->getDomainParameters();
					if($domainParameters !== NULL){
						$middlewareClass->setDomainParameters($domainParameters);
					}
				}
				$middlewareObjects[] = $middlewareClass;
				if($key>0){
					$oldMiddlewareObject = $middlewareObjects[$key-1];
					$oldMiddlewareObject->setNext($middlewareClass);
				}
			}else{
				throw new \Exception("You don't pass the middleware class", 1);
			}
		}
		$firstMiddlewareObject = $middlewareObjects[0];
		$reflectionMethod = new ReflectionMethod(get_class($firstMiddlewareObject) , 'handle' );
		return $reflectionMethod->invokeArgs($firstMiddlewareObject ,$firstMiddlewareObject->getParameters());
	}
}