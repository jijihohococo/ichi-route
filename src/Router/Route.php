<?php

namespace JiJiHoHoCoCo\IchiRoute\Router;

use JiJiHoHoCoCo\IchiRoute\Container\DependencyInject;
use JiJiHoHoCoCo\IchiRoute\Middleware\RouteMiddleware;
use JiJiHoHoCoCo\IchiRoute\Cache\RouteCache;
use JiJiHoHoCoCo\IchiRoute\UI\NotFound;
use ReflectionMethod,PDO,ReflectionFunction;
class Route{

	private $routes,$groupURL,$parameterRoutes=[];
	private $currentGroup,$urlParameters;
	private $numberOfGroups=0;
	private $baseControllerPath,$baseMiddlewarePath;

	private $dependencyInject,$routeMiddleware,$defaultMiddlewares;

	private $redis , $redisCachedTime , $memcached , $memcachedCachedTime , $pdo , $pdoCachedTime , $cacheMode ;

	public function __construct(){
		$this->dependencyInject=new DependencyInject;
		$this->routeMiddleware=new RouteMiddleware;
	}

	public function setRedis($redis,int $redisCachedTime=NULL){
		if(!is_a($redis,'Redis')){
			throw new \Exception("You need to use php redis object", 1);
		}
		if($this->cacheMode!==NULL){
			throw new \Exception("You already set ".$this->cacheMode." Mode", 1);
		}
		$this->redis=$redis;
		$this->redisCachedTime=$redisCachedTime;
		$this->cacheMode='redis';
	}

	public function getRedis(){
		return $this->redis;
	}

	public function setMemcached($memcached,int $memcachedCachedTime=0){
		if(!is_a($redis,'Memcached')){
			throw new \Exception("You need to use php memcached object", 1);
		}
		if($this->cacheMode!==NULL){
			throw new \Exception("You already set ".$this->cacheMode." Mode", 1);
		}
		$this->memcached=$memcached;
		$this->memcachedCachedTime=$memcachedCachedTime;
		$this->cacheMode='memcached';
	}

	public function getMemcached(){
		return $this->memcached;
	}

	public function setPDO(PDO $pdo,int $pdoCachedTime=NULL){
		if($this->cacheMode!==NULL){
			throw new \Exception("You already set ".$this->cacheMode." Mode", 1);
		}
		$this->pdo=new RouteCache($pdo,$pdoCachedTime);
		$this->pdoCachedTime=$pdoCachedTime;
		$this->cacheMode='pdo';
	}

	public function getPDO(){
		return $this->pdo;
	}

	private function callingRequest(){
		if(func_num_args()>=1){
			$parameters=func_get_args();
			if(is_callable($parameters[0])){
				$function=$parameters[0];
				unset($parameters[0]);
				$reflectionFunction=new ReflectionFunction($function);
				return $reflectionFunction->invokeArgs($parameters);
			}else{
				$calledFunction=explode('@',  $parameters[0] );
				if(isset($calledFunction[0]) && isset($calledFunction[1])){
					$className=$this->getBaseControllerPath().$calledFunction[0];
					if(!class_exists($className)){
						throw new \Exception($class . " Class is not exist", 1);
					}
					$functionName=$calledFunction[1];
					unset($parameters[0]);
					return $this->dependencyInject->getConstructor($className,$functionName,$parameters);
				}else{
					throw new \Exception("You don't pass controller and function correctly", 1);
				}
			}
		}
	}

	public function setBaseControllerPath(string $baseControllerPath){
		$this->baseControllerPath=addFolderSlash($baseControllerPath);
	}

	public function setDefaultMiddlewares(array $middlewares){
		$this->defaultMiddlewares=$middlewares;
	}

	public function showErrorPage(){
		http_response_code(404);
		echo NotFound::show();
	}

	public function getBaseControllerPath(){
		return $this->baseControllerPath;
	}

	public function setBaseMiddlewarePath(string $baseMiddlewarePath){
		$this->baseMiddlewarePath=addFolderSlash($baseMiddlewarePath);
	}

	public function getBaseMiddlewarePath(){
		return $this->baseMiddlewarePath;
	}
	
	private function addRoute($parameters,$method){
		$url=getRoute($parameters[0]);
		if(strpos($url,'{')==TRUE && strpos($url,'}')==TRUE ){
			$this->addParameterRoutes($url,$parameters,$method);
		}else{
			$this->routes[$url.'{'.$method.'}']=$this->getRouteData($parameters,$method);
		}
	}

	private function addParameterRoutes($url,$parameters,$method){
		foreach(explode('/',$url) as $key => $urlData ){
			if(substr($urlData,0,1)=='{' && substr($urlData,-1)=='}'){
				if(isset($this->parameterRoutes[$url.'{'.$method.'}'])){
					$this->parameterRoutes[$url.'{'.$method.'}']['parameters'][$key]=$urlData;
				}else{
					$this->parameterRoutes[$url.'{'.$method.'}']=$this->getRouteData($parameters,$method);
					$this->parameterRoutes[$url.'{'.$method.'}']['parameters']=[$key=>$urlData];
				}
			}
		}
	}

	private function getRouteData($parameters,$method){
		return [
			'function' => $parameters[1],
			'method' => $method,
			'middleware' => isset($parameters[2]) && is_array($parameters[2]) ? $parameters[2] : null
		];
	}

	private function groupRoutes($arguments,$method){
		$parameters=$arguments;
		$dataGroup=$this->groupURL[$this->currentGroup];
		$parameters[0]=$dataGroup['url_group']!==NULL ? '/'.$dataGroup['url_group'].getRoute($parameters[0]) : getRoute($parameters[0]);
		if(isset($dataGroup['middleware']) &&
			is_array($middlewares=$dataGroup['middleware']) ){
			$parameters[2]=isset($parameters[2]) && is_array($parameters[2]) && !empty($parameters[2]) ? array_merge($middlewares,$parameters[2])  : $middlewares;
	}
	return $this->addRoute($parameters,$method);
}
private function checkGroup(){
	return $this->currentGroup!==NULL && isset($this->groupURL[$this->currentGroup]);
}

private function makeRouteAction(string $route,$return,array $middlewares=[],$method){
	if(!is_string($return) && !is_callable($return) ){
		throw new \Exception("You can use controller class with function name or closure function ", 1);
	}
	if($this->checkGroup() ){
		return $this->groupRoutes([
			$route,
			$return,
			$middlewares
		],$method);
	}
	return $this->addRoute([
		$route,
		$return,
		$middlewares
	],$method);
}
public function get(string $route,$return,array $middlewares=[]){
	return $this->makeRouteAction($route,$return,$middlewares,'GET');
}
public function post(string $route,$return,array $middlewares=[]){
	return $this->makeRouteAction($route,$return,$middlewares,'POST');
}

public function put(string $route,$return,array $middlewares=[]){
	return $this->makeRouteAction($route,$return,$middlewares,'PUT');
}

public function delete(string $route,$return,array $middlewares=[]){
	return $this->makeRouteAction($route,$return,$middlewares,'DELETE');
}

public function head(string $route,$return,array $middlewares=[]){
	return $this->makeRouteAction($route,$return,$middlewares,'HEAD');
}

public function resource(string $route,$return,array $middlewares=[]){
	if(!is_string($return) && !is_callable($return) ){
		throw new \Exception("You can pass controller class with method or closure function", 1);
	}
	$route=getRoute($route);
	$this->get($route,$return.'@index',$middlewares);
	$this->get($route.'/create',$return.'@create',$middlewares);
	$this->post($route.'/create',$return.'@save',$middlewares);
	$this->get($route.'/{id}/edit',$return.'@edit',$middlewares);
	$this->put($route.'/{id}/edit',$return.'@update',$middlewares);
	$this->delete($route.'/{id}/delete',$return.'@destroy',$middlewares);
}

public function any(string $route,$return,array $middlewares=[]){
	if(!is_string($return) && !is_callable($return) ){
		throw new \Exception("You can pass controller class with method or closure function", 1);
	}
	$route=getRoute($route);
	$this->get($route,$return,$middlewares);
	$this->post($route,$return,$middlewares);
	$this->put($route,$return,$middlewares);
	$this->delete($route,$return,$middlewares);
	$this->head($route,$return,$middlewares);
}

private function checkMiddleware($routes,$serverURL,$parameters=[]){

	if($this->{$routes}[$serverURL]['middleware']!==NULL && !empty($this->{$routes}[$serverURL]['middleware']) ){
		return $this->routeMiddleware->check($this->{$routes}[$serverURL]['middleware'],$this,$parameters);
	}
}

public function group(array $data,callable $function){
	$this->numberOfGroups++;
	if($this->currentGroup==null){
		$this->groupURL[$this->numberOfGroups]=[
			'middleware' => isset($data['middleware']) && is_array($data['middleware']) ? $data['middleware'] : NULL,
			'url_group' => isset($data['url_group']) ? $data['url_group'] : NULL
		];
	}else{
		return $this->childCall($data,$function);
	}
	$this->currentGroup=$this->numberOfGroups;
	$function->call($this);
	$this->currentGroup=null;
}

private function childCall($data,$function){
	$previousGroupNumber=$this->currentGroup;
	$previousGroup=$this->groupURL[$previousGroupNumber];
	$usedMiddleware=$this->getMiddleware($data,$previousGroup['middleware']);
	$usedURLGroup=$this->getURLGroup($data,$previousGroup['url_group']);
	$this->groupURL[$this->numberOfGroups]=[
		'middleware' => $usedMiddleware,
		'url_group' => $usedURLGroup
	];
	$this->currentGroup=$this->numberOfGroups;
	$function->call($this);
	$this->currentGroup=$previousGroupNumber;
}

private function getMiddleware($data,$oldMiddleware){
	if(isset($data['middleware']) && is_array($data['middleware']) && is_array($oldMiddleware) ){
		return array_merge($oldMiddleware,$data['middleware']);
	}elseif(!isset($data['middleware']) && is_array($oldMiddleware)){
		return $oldMiddleware;
	}elseif(isset($data['middleware']) && !is_array($oldMiddleware) ){
		return $data['middleware'];
	}elseif(!isset($data['middleware']) && !is_array($oldMiddleware) ){
		return NULL;
	}
}

private function getURLGroup($data,$oldURLGroup){
	if(isset($data['url_group']) && isset($oldURLGroup) ){
		return $oldURLGroup .'/'. $data['url_group'];
	}elseif(!isset($data['url_group']) && isset($oldURLGroup) ){
		return $oldURLGroup;
	}elseif(isset($data['url_group']) && !isset($oldURLGroup) ){
		return $data['url_group'];
	}elseif(!isset($data['url_group']) && !isset($oldURLGroup) ){
		return NULL;
	}
}


private function serverURLCheckOne($serverURL,$requestMethod,$routes,$method){
	return array_key_exists($serverURL.$requestMethod,$routes) && $routes[$serverURL.$requestMethod]['method']==$method;
}

private function serverURLCheckTwo($serverURL,$requestMethod,$routes,$method){
	return array_key_exists($serverURL.'/'.$requestMethod,$routes) && $routes[$serverURL.'/'.$requestMethod]['method']==$method;
}

private function serverURLCheckThree($serverURL,$requestMethod,$routes,$method){
	return substr($serverURL, -1) == '/' && array_key_exists(substr($serverURL,0,-1).$requestMethod,$routes) && $routes[substr($serverURL, 0,-1).$requestMethod]['method']==$method;
}

private function getRouteFromCache($newServerURL){
	$availableParameterRoute=$this->parameterRoutes[$newServerURL];
	$parameterRoute=$availableParameterRoute['parameters'];
	$middlewareParameters=$parameters=[];
	$url=getDirectURL();
	foreach ($parameterRoute as $key => $parameter) {
		$parameters[$key]=$url[$key];
		$newValue=getRouteParameter($parameter);
		$middlewareParameters[$newValue]=$url[$key];
	}
	$this->checkMiddleware('parameterRoutes',$newServerURL,$middlewareParameters);
	$parameters[0]=$availableParameterRoute['function'];
	ksort($parameters);


	$reflectionMethod=new ReflectionMethod((string)get_class($this),'callingRequest');
	$reflectionMethod->setAccessible(true);
	return $reflectionMethod->invokeArgs($this,$parameters);
}

public function run(){
	if($this->defaultMiddlewares!==NULL){
		$this->routeMiddleware->check($this->defaultMiddlewares,$this);
	}

	$serverURL=parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
	$method=$_SERVER['REQUEST_METHOD'];
	$requestMethod='{'.$method.'}';


	$newServerURL=NULL;

	$redis=$this->getRedis();
	$memcached=$this->getMemcached();
	$pdo=$this->getPDO();
	if($this->cacheMode!==NULL){

		$cacheObject=${$this->cacheMode};
		$newServerURL=getCachedRoute($cacheObject,$serverURL,$requestMethod);

		if($newServerURL!==NULL){
			if(isset($this->parameterRoutes[$newServerURL])){
				return $this->getRouteFromCache($newServerURL);
			}else{
				$cacheObject->delete($serverURL.$requestMethod);
			}
		}
	}


	if($this->routes!==NULL){
		if($this->serverURLCheckOne($serverURL,$requestMethod,$this->routes,$method)){
			$this->checkMiddleware('routes',$serverURL.$requestMethod);
			return $this->callingRequest($this->routes[$serverURL.$requestMethod]['function']);

		}
		if($this->serverURLCheckTwo($serverURL,$requestMethod,$this->routes,$method)){
			$this->checkMiddleware('routes',$serverURL.'/'.$requestMethod);
			return $this->callingRequest($this->routes[$serverURL.'/'.$requestMethod]['function']);
		}
		if($this->serverURLCheckThree($serverURL,$requestMethod,$this->routes,$method)){
			$this->checkMiddleware('routes',substr($serverURL,0,-1).$requestMethod);
			return $this->callingRequest($this->routes[substr($serverURL,0,-1).$requestMethod]['function']);
		}
	}
	if($this->parameterRoutes!==NULL){
		$url=$urlData=getDirectURL();
		$availableParameterRoute=null;
		foreach($this->parameterRoutes as $urlKey=> $route){
			$parameters=[];
			$requestURL=getDirectURL(str_replace($requestMethod, '', $urlKey));
			if(count($requestURL)==count($urlData) && $method==$route['method']){
				$middlewareParameters=[];
				$parameterRoute=$route['parameters']; 
				foreach($parameterRoute as $parameterKey => $parameter){
					$parameters[$parameterKey]=$url[$parameterKey];
					$url[$parameterKey]=$parameter;
					$newValue=getRouteParameter($parameter);
					$middlewareParameters[$newValue]=$parameters[$parameterKey];
				}
				$newServerURL=substr(implode('/',$url),0,-1).$requestMethod;
				if(isset($this->parameterRoutes[$newServerURL])){
					
					if($this->cacheMode!==NULL){
						$saveRoute=substr(implode('/',$urlData),0,-1).$requestMethod;
						${$this->cacheMode}->set($saveRoute,$newServerURL,$this->{$this->cacheMode.'CachedTime'} );
					}


					$availableParameterRoute=$this->parameterRoutes[$newServerURL];

					$this->checkMiddleware('parameterRoutes',$newServerURL,$middlewareParameters);
					$parameters[0]=$availableParameterRoute['function'];
					ksort($parameters);

					$reflectionMethod=new ReflectionMethod((string)get_class($this),'callingRequest');
					$reflectionMethod->setAccessible(true);
					return $reflectionMethod->invokeArgs($this,$parameters);

				}else{
					$url=$urlData;
				}

			}
		}
	}
	return $this->showErrorPage();
}
}