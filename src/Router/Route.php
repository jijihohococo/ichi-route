<?php

namespace JiJiHoHoCoCo\IchiRoute\Router;

use JiJiHoHoCoCo\IchiRoute\Container\DependencyInject;
use JiJiHoHoCoCo\IchiRoute\Middleware\RouteMiddleware;
use JiJiHoHoCoCo\IchiRoute\Cache\RouteCache;
use JiJiHoHoCoCo\IchiRoute\Setting\Host;
use ReflectionMethod, PDO, ReflectionFunction, Exception;

class Route
{

	private $groupURL = [];
	private $currentGroup;
	private $numberOfGroups = 0;
	private $baseControllerPath, $baseMiddlewarePath;

	private $dependencyInject, $routeMiddleware, $defaultMiddlewares;

	private $redis, $redisCachedTime, $memcached, $memcachedCachedTime, $pdo, $pdoCachedTime, $cacheMode;

	private $host, $currentDomain, $domains, $parameterDomains;
	private $usedMultipleDomains = FALSE;
	private $keyValues = [];

	private $caller;

	const PAGE_NOT_FOUND = "404 - URL is not found.";


	public function __construct()
	{
		$this->dependencyInject = new DependencyInject;
		$this->routeMiddleware = new RouteMiddleware;
		$this->host = new Host;
	}

	public function setKeyValue(string $key, $values)
	{
		$this->keyValues[$key] = $values;
	}

	public function getKeyValue(string $key)
	{
		if (isset($this->keyValues[$key])) {
			return $this->keyValues[$key];
		}
		return null;
	}

	public function getKeyValues()
	{
		return $this->keyValues;
	}

	public function setDefaultDomain(string $domain)
	{
		$this->host->setDefaultDomain($domain);
	}

	private function getCurrentDomain()
	{
		return $this->currentDomain == NULL ? $this->host->getDefaultDomain() : $this->currentDomain;
	}

	private function getServerHost()
	{
		if ($this->usedMultipleDomains == FALSE) {
			return $this->host->getDefaultDomain();
		} else {
			return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $this->host->getDefaultDomain();
		}
	}

	public function setRedis($redis, int $redisCachedTime = NULL)
	{
		try {
			$this->caller = getCallerInfo();
			if (!is_a($redis, 'Redis')) {
				throw new Exception("You need to use php redis object", 1);
			}
			if ($this->cacheMode !== NULL) {
				throw new Exception("You already set " . $this->cacheMode . " Mode", 1);
			}
			$this->redis = $redis;
			$this->redisCachedTime = $redisCachedTime;
			$this->cacheMode = 'redis';
		} catch (Exception $e) {
			return showErrorPage($e->getMessage() . showCallerInfo($this->caller));
		}
	}

	public function getRedis()
	{
		return $this->redis;
	}

	public function setMemcached($memcached, int $memcachedCachedTime = 0)
	{
		try {
			$this->caller = getCallerInfo();
			if (!is_a($memcached, 'Memcached')) {
				throw new Exception("You need to use php memcached object", 1);
			}
			if ($this->cacheMode !== NULL) {
				throw new Exception("You already set " . $this->cacheMode . " Mode", 1);
			}
			$this->memcached = $memcached;
			$this->memcachedCachedTime = $memcachedCachedTime;
			$this->cacheMode = 'memcached';
		} catch (Exception $e) {
			return showErrorPage($e->getMessage() . showCallerInfo($this->caller));
		}
	}

	public function getMemcached()
	{
		return $this->memcached;
	}

	public function setPDO(PDO $pdo, int $pdoCachedTime = NULL)
	{
		try {
			$this->caller = getCallerInfo();
			if ($this->cacheMode !== NULL) {
				throw new Exception("You already set " . $this->cacheMode . " Mode", 1);
			}
			$this->pdo = new RouteCache($pdo, $pdoCachedTime);
			$this->pdoCachedTime = $pdoCachedTime;
			$this->cacheMode = 'pdo';
		} catch (Exception $e) {
			return showErrorPage($e->getMessage() . showCallerInfo($this->caller));
		}
	}

	public function getPDO()
	{
		return $this->pdo;
	}

	private function callingRequest()
	{
		if (func_num_args() >= 1) {
			$parameters = func_get_args();
			if (is_callable($parameters[0])) {
				$function = $parameters[0];
				unset($parameters[0]);
				$reflectionFunction = new ReflectionFunction($function);
				return $reflectionFunction->invokeArgs($parameters);
			} else {
				$calledFunction = explode('@', $parameters[0]);
				if (isset($calledFunction[0]) && isset($calledFunction[1])) {
					$className = $this->getBaseControllerPath() . $calledFunction[0];
					if (!class_exists($className)) {
						throw new Exception($className . " Class is not exist", 1);
					}
					$functionName = $calledFunction[1];
					unset($parameters[0]);
					$routeKeyalues = $this->getKeyValues();
					$this->dependencyInject->setCreatedClass($className);
					$this->dependencyInject->setFunctionName($functionName);
					$this->dependencyInject->setFunctionParameters($parameters);
					if (!empty($routeKeyalues)) {
						foreach ($routeKeyalues as $key => $value) {
							$this->dependencyInject->setKeyValue($key, $value);
						}
					}
					return $this->dependencyInject->runClassFunction();
				} else {
					throw new Exception("You don't pass controller and function correctly", 1);
				}
			}
		}
	}

	public function setBaseControllerPath(string $baseControllerPath)
	{
		$this->caller = getCallerInfo();
		$this->baseControllerPath = addFolderSlash($baseControllerPath);
	}

	public function setDefaultMiddlewares(array $middlewares)
	{
		$this->caller = getCallerInfo();
		$this->defaultMiddlewares = $middlewares;
	}

	public function getBaseControllerPath()
	{
		return $this->baseControllerPath;
	}

	public function setBaseMiddlewarePath(string $baseMiddlewarePath)
	{
		$this->caller = getCallerInfo();
		$this->baseMiddlewarePath = addFolderSlash($baseMiddlewarePath);
	}

	public function getBaseMiddlewarePath()
	{
		return $this->baseMiddlewarePath;
	}

	private function addRoute($parameters, $method)
	{
		$url = getRoute($parameters[0]);
		if (strpos($url, '{') !== FALSE && strpos($url, '}') !== FALSE) {
			$this->addParameterRoutes($url, $parameters, $method);
		} else {
			$currentDomain = $this->getCurrentDomain();
			if (
				isset($this->domains[$currentDomain]['routes']) &&
				isset($this->domains[$currentDomain]['routes'][$url . '{' . $method . '}'])
			) {
				throw new Exception($url . " is duplicated", 1);
			}
			$this->domains[$currentDomain]['routes'][$url . '{' . $method . '}'] = $this->getRouteData($parameters, $method);
		}
	}

	private function addParameterRoutes($url, $parameters, $method)
	{
		$i = 0;
		$currentDomain = $this->getCurrentDomain();
		foreach (explode('/', $url) as $key => $urlData) {
			if (substr($urlData, 0, 1) == '{' && substr($urlData, -1) == '}') {
				if (
					isset($this->domains[$currentDomain]['parameterRoutes']) &&
					isset($this->domains[$currentDomain]['parameterRoutes'][$url . '{' . $method . '}'])
				) {
					if ($i == 0) {
						throw new Exception($url . " is duplicated", 1);
					}
					$this->domains[$currentDomain]['parameterRoutes'][$url . '{' . $method . '}']['parameters'][$key] = $urlData;
				} else {


					$this->domains[$currentDomain]['parameterRoutes'][$url . '{' . $method . '}'] = $this->getRouteData($parameters, $method);
					$this->domains[$currentDomain]['parameterRoutes'][$url . '{' . $method . '}']['parameters'] = [$key => $urlData];
				}
				$i++;
			}
		}
	}

	private function getRouteData($parameters, $method)
	{
		return [
			'function' => $parameters[1],
			'method' => $method,
			'middleware' => isset($parameters[2]) && is_array($parameters[2]) ? $parameters[2] : null
		];
	}

	private function groupRoutes($arguments, $method)
	{
		$parameters = $arguments;
		$dataGroup = $this->groupURL[$this->currentGroup];
		$parameters[0] = $dataGroup['url_group'] !== NULL ? '/' . $dataGroup['url_group'] . getRoute($parameters[0]) : getRoute($parameters[0]);
		if (
			isset($dataGroup['middleware']) &&
			is_array($middlewares = $dataGroup['middleware'])
		) {
			$parameters[2] = isset($parameters[2]) && is_array($parameters[2]) && !empty($parameters[2]) ? array_merge($middlewares, $parameters[2]) : $middlewares;
		}
		return $this->addRoute($parameters, $method);
	}
	private function checkGroup()
	{
		return $this->currentGroup !== NULL && isset($this->groupURL[$this->currentGroup]);
	}

	private function makeRouteAction(string $route, $return, array $middlewares = [], $method)
	{
		if (!is_string($return) && !is_callable($return)) {
			throw new Exception("You can use controller class with function name or closure function ", 1);
		}
		if ($this->checkGroup()) {
			return $this->groupRoutes([
				$route,
				$return,
				$middlewares
			], $method);
		}
		return $this->addRoute([
			$route,
			$return,
			$middlewares
		], $method);
	}
	public function get(string $route, $return, array $middlewares = [])
	{
		$this->caller = getCallerInfo();
		return $this->makeRouteAction($route, $return, $middlewares, 'GET');
	}
	public function post(string $route, $return, array $middlewares = [])
	{
		$this->caller = getCallerInfo();
		return $this->makeRouteAction($route, $return, $middlewares, 'POST');
	}

	public function put(string $route, $return, array $middlewares = [])
	{
		$this->caller = getCallerInfo();
		$newMiddlewares = $middlewares;
		$newMiddlewares[] = 'JiJiHoHoCoCo\IchiRoute\Middleware\PutMethodMiddleware';
		return $this->makeRouteAction($route, $return, $newMiddlewares, 'POST');
	}

	public function delete(string $route, $return, array $middlewares = [])
	{
		$this->caller = getCallerInfo();
		$newMiddlewares = $middlewares;
		$newMiddlewares[] = 'JiJiHoHoCoCo\IchiRoute\Middleware\DeleteMethodMiddleware';
		return $this->makeRouteAction($route, $return, $newMiddlewares, 'POST');
	}

	public function head(string $route, $return, array $middlewares = [])
	{
		$this->caller = getCallerInfo();
		return $this->makeRouteAction($route, $return, $middlewares, 'HEAD');
	}

	public function patch(string $route, $return, array $middlewares = [])
	{
		$this->caller = getCallerInfo();
		$newMiddlewares = $middlewares;
		$newMiddlewares[] = 'JiJiHoHoCoCo\IchiRoute\Middleware\PatchMethodMiddleware';
		return $this->makeRouteAction($route, $return, $newMiddlewares, 'POST');
	}

	public function apiResource(string $route, $return, array $middlewares = [])
	{
		try {
			$this->caller = getCallerInfo();
			if (!is_string($return) && !is_callable($return)) {
				throw new Exception("You can pass controller class with method or closure function", 1);
			}
			$route = getRoute($route);

			$this->get($route, $return . '@index', $middlewares);
			$this->post($route . '/create', $return . '@save', $middlewares);
			$this->get($route . '/{id}/edit', $return . '@edit', $middlewares);
			$this->patch($route . '/{id}/edit', $return . '@update', $middlewares);
			$this->delete($route . '/{id}/delete', $return . '@destroy', $middlewares);
		} catch (Exception $e) {
			return showErrorPage($e->getMessage() . showCallerInfo($this->caller));
		}
	}

	public function resource(string $route, $return, array $middlewares = [])
	{
		try {
			$this->caller = getCallerInfo();
			if (!is_string($return) && !is_callable($return)) {
				throw new Exception("You can pass controller class with method or closure function", 1);
			}
			$route = getRoute($route);

			$this->get($route, $return . '@index', $middlewares);
			$this->get($route . '/create', $return . '@create', $middlewares);
			$this->post($route . '/create', $return . '@save', $middlewares);
			$this->get($route . '/{id}/edit', $return . '@edit', $middlewares);
			$this->patch($route . '/{id}/edit', $return . '@update', $middlewares);
			$this->delete($route . '/{id}/delete', $return . '@destroy', $middlewares);
		} catch (Exception $e) {
			return showErrorPage($e->getMessage() . showCallerInfo($this->caller));
		}
	}

	public function any(string $route, $return, array $middlewares = [])
	{
		try {
			$this->caller = getCallerInfo();
			if (!is_string($return) && !is_callable($return)) {
				throw new Exception("You can pass controller class with method or closure function", 1);
			}
			$route = getRoute($route);
			$this->get($route, $return, $middlewares);
			$this->post($route, $return, $middlewares);
			$this->put($route, $return, $middlewares);
			$this->delete($route, $return, $middlewares);
			$this->head($route, $return, $middlewares);
		} catch (Exception $e) {
			return showErrorPage($e->getMessage() . showCallerInfo($this->caller));
		}
	}

	private function checkMiddleware($routes, $serverURL, $parameters = [])
	{

		if ($routes[$serverURL]['middleware'] !== NULL && !empty($routes[$serverURL]['middleware'])) {
			return $this->routeMiddleware->check($routes[$serverURL]['middleware'], $this, $parameters);
		}
	}

	public function domain(string $domain, callable $function)
	{
		try {
			$this->caller = getCallerInfo();
			if ($this->currentGroup !== NULL) {
				throw new Exception("Don't use domain function within group function", 1);
			}
			if ($this->host->getDefaultDomain() == 'localhost') {
				throw new Exception('You need to set your default domain name', 1);
			}
			$this->usedMultipleDomains = TRUE;
			if (strpos($domain, '{') !== FALSE && strpos($domain, '}') !== FALSE) {
				$i = 0;
				foreach (explode('.', $domain) as $key => $domainData) {
					if (substr($domainData, 0, 1) == '{' && substr($domainData, -1) == '}') {
						if (
							isset($this->parameterDomains[$domain]) &&
							isset($this->parameterDomains[$domain]['parameters'])
						) {
							if ($i == 0) {
								throw new Exception($domain . " is duplicated", 1);
							}
							$this->parameterDomains[$domain]['parameters'][$key] = $domainData;
						} else {
							$this->parameterDomains[$domain]['parameters'] = [$key => $domainData];
						}
						$i++;
					}
				}
			}
			$this->currentDomain = $domain;
			$function->call($this);
			$this->currentDomain = NULL;
		} catch (Exception $e) {
			return showErrorPage($e->getMessage() . showCallerInfo($this->caller));
		}
	}

	public function group(array $data, callable $function)
	{
		$this->caller = getCallerInfo();
		$this->numberOfGroups++;
		if ($this->currentGroup == null) {
			$this->groupURL[$this->numberOfGroups] = [
				'middleware' => isset($data['middleware']) && is_array($data['middleware']) ? $data['middleware'] : NULL,
				'url_group' => isset($data['url_group']) ? $data['url_group'] : NULL
			];
		} else {
			return $this->childCall($data, $function);
		}
		$this->currentGroup = $this->numberOfGroups;
		$function->call($this);
		$this->currentGroup = null;
	}

	private function childCall($data, $function)
	{
		$previousGroupNumber = $this->currentGroup;
		$previousGroup = $this->groupURL[$previousGroupNumber];
		$usedMiddleware = $this->getMiddleware($data, $previousGroup['middleware']);
		$usedURLGroup = $this->getURLGroup($data, $previousGroup['url_group']);
		$this->groupURL[$this->numberOfGroups] = [
			'middleware' => $usedMiddleware,
			'url_group' => $usedURLGroup
		];
		$this->currentGroup = $this->numberOfGroups;
		$function->call($this);
		$this->currentGroup = $previousGroupNumber;
	}

	private function getMiddleware($data, $oldMiddleware)
	{
		if (isset($data['middleware']) && is_array($data['middleware']) && is_array($oldMiddleware)) {
			return array_merge($oldMiddleware, $data['middleware']);
		} elseif (!isset($data['middleware']) && is_array($oldMiddleware)) {
			return $oldMiddleware;
		} elseif (isset($data['middleware']) && !is_array($oldMiddleware)) {
			return $data['middleware'];
		} elseif (!isset($data['middleware']) && !is_array($oldMiddleware)) {
			return NULL;
		}
	}

	private function getURLGroup($data, $oldURLGroup)
	{
		if (isset($data['url_group']) && isset($oldURLGroup)) {
			return $oldURLGroup . '/' . $data['url_group'];
		} elseif (!isset($data['url_group']) && isset($oldURLGroup)) {
			return $oldURLGroup;
		} elseif (isset($data['url_group']) && !isset($oldURLGroup)) {
			return $data['url_group'];
		} elseif (!isset($data['url_group']) && !isset($oldURLGroup)) {
			return NULL;
		}
	}


	private function serverURLCheckOne($serverURL, $requestMethod, $routes, $method)
	{
		return array_key_exists($serverURL . $requestMethod, $routes) && $routes[$serverURL . $requestMethod]['method'] == $method;
	}

	private function serverURLCheckTwo($serverURL, $requestMethod, $routes, $method)
	{
		return array_key_exists($serverURL . '/' . $requestMethod, $routes) && $routes[$serverURL . '/' . $requestMethod]['method'] == $method;
	}

	private function serverURLCheckThree($serverURL, $requestMethod, $routes, $method)
	{
		return substr($serverURL, -1) == '/' && array_key_exists(substr($serverURL, 0, -1) . $requestMethod, $routes) && $routes[substr($serverURL, 0, -1) . $requestMethod]['method'] == $method;
	}

	private function getRouteFromCache($host, $newServerURL, $domainParameters = [])
	{
		$availableParameterRoute = $host[$newServerURL];
		$parameterRoute = $availableParameterRoute['parameters'];
		$middlewareParameters = $parameters = [];
		$url = getDirectURL();
		foreach ($parameterRoute as $key => $parameter) {
			$parameters[$key] = $url[$key];
			$newValue = getRouteParameter($parameter);
			$middlewareParameters[$newValue] = $url[$key];
		}
		$check = $this->checkMiddleware($host, $newServerURL, $middlewareParameters);

		if ($check !== NULL) {
			return $check;
		}

		return $this->domainParameterRouteRun($domainParameters, $parameters, $availableParameterRoute['function']);
	}

	private function mainRun($parameters)
	{

		$reflectionMethod = new ReflectionMethod((string) get_class($this), 'callingRequest');
		$reflectionMethod->setAccessible(true);
		return $reflectionMethod->invokeArgs($this, $parameters);
	}

	private function domainParameterRouteRun($domainParameters, $parameters, $function)
	{
		if (empty($domainParameters)) {
			$parameters[0] = $function;
			ksort($parameters);
			return $this->mainRun($parameters);
		} else {
			$mergeParameters = array_merge($domainParameters, $parameters);
			$newParameters[0] = $function;
			$usedParameters = array_merge($newParameters, $mergeParameters);
			return $this->mainRun($usedParameters);
		}
	}

	private function runDomain($domain, array $domainParameters = [])
	{

		$serverURL = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
		$method = $_SERVER['REQUEST_METHOD'];
		$requestMethod = '{' . $method . '}';


		$newServerURL = NULL;

		$redis = $this->getRedis();
		$memcached = $this->getMemcached();
		$pdo = $this->getPDO();

		if ($this->defaultMiddlewares !== NULL) {
			$check = $this->routeMiddleware->check($this->defaultMiddlewares, $this);
			if ($check !== NULL) {
				return $check;
			}
		}
		// FOR CACHED ROUTES //
		if ($this->cacheMode !== NULL && isset($domain['parameterRoutes'])) {

			$cacheObject = ${$this->cacheMode};
			$newServerURL = getCachedRoute($cacheObject, $serverURL, $requestMethod);

			if ($newServerURL !== NULL && isset($domain['parameterRoutes'][$newServerURL])) {
				return $this->getRouteFromCache($domain['parameterRoutes'], $newServerURL, $domainParameters);
			}
		}
		// FOR CACHED ROUTES //

		// FOR SPECFIC ROUTES //
		if (isset($domain['routes'])) {
			$routes = $domain['routes'];
			if ($this->serverURLCheckOne($serverURL, $requestMethod, $routes, $method)) {
				$check = $this->checkMiddleware($routes, $serverURL . $requestMethod);
				if ($check !== NULL) {
					return $check;
				}
				$parameters[0] = $routes[$serverURL . $requestMethod]['function'];
				$newParameters = array_merge($parameters, $domainParameters);
				return $this->mainRun($newParameters);
			}

			if ($this->serverURLCheckTwo($serverURL, $requestMethod, $routes, $method)) {
				$check = $this->checkMiddleware($routes, $serverURL . '/' . $requestMethod);
				if ($check !== NULL) {
					return $check;
				}
				$parameters[0] = $routes[$serverURL . '/' . $requestMethod]['function'];
				$newParameters = array_merge($parameters, $domainParameters);
				return $this->mainRun($newParameters);
			}

			if ($this->serverURLCheckThree($serverURL, $requestMethod, $routes, $method)) {
				$check = $this->checkMiddleware($routes, substr($serverURL, 0, -1) . $requestMethod);
				if ($check !== NULL) {
					return $check;
				}
				$parameters[0] = $routes[substr($serverURL, 0, -1) . $requestMethod]['function'];
				$newParameters = array_merge($parameters, $domainParameters);
				return $this->mainRun($newParameters);
			}
		}
		// FOR SPECFIC ROUTES //

		// FOR PARAMETER ROUTES //
		if (isset($domain['parameterRoutes'])) {
			$url = $urlData = getDirectURL();
			$availableParameterRoute = null;
			foreach ($domain['parameterRoutes'] as $urlKey => $route) {
				$parameters = [];
				$requestURL = getDirectURL(str_replace($requestMethod, '', $urlKey));
				if (count($requestURL) == count($urlData) && $method == $route['method']) {
					$middlewareParameters = [];
					$parameterRoute = $route['parameters'];
					foreach ($parameterRoute as $parameterKey => $parameter) {
						$parameters[$parameterKey] = $url[$parameterKey];
						$url[$parameterKey] = $parameter;
						$newValue = getRouteParameter($parameter);
						$middlewareParameters[$newValue] = $parameters[$parameterKey];
					}
					$newServerURL = substr(implode('/', $url), 0, -1) . $requestMethod;
					if (isset($domain['parameterRoutes'][$newServerURL])) {

						if ($this->cacheMode !== NULL) {
							$saveRoute = substr(implode('/', $urlData), 0, -1) . $requestMethod;
							${$this->cacheMode}->set($saveRoute, $newServerURL, $this->{$this->cacheMode . 'CachedTime'});
						}


						$availableParameterRoute = $domain['parameterRoutes'][$newServerURL];

						$check = $this->checkMiddleware($domain['parameterRoutes'], $newServerURL, $middlewareParameters);

						if ($check !== NULL) {
							return $check;
						}
						return $this->domainParameterRouteRun($domainParameters, $parameters, $availableParameterRoute['function']);

					} else {
						$url = $urlData;
					}

				}
			}
		}
		return showErrorPage(self::PAGE_NOT_FOUND . showCallerInfo($this->caller), 404);
		// FOR PARAMETER ROUTES //
	}

	public function run()
	{
		$this->caller = getCallerInfo();

		try {

			header("X-XSS-Protection: 1; mode=block");
			header('X-Content-Type-Options: nosniff');

			$serverHost = $this->getServerHost();

			// INCLUDING DOMAIN CHECKING //
			if (is_array($this->domains)) {
				// FOR SPECFIC DOMAIN //
				if (isset($this->domains[$serverHost])) {
					return $this->runDomain($this->domains[$serverHost]);
				}
				// FOR SPECFIC DOMAIN //

				// FOR PARAMETER DOMAIN //
				if (is_array($this->parameterDomains)) {
					foreach ($this->parameterDomains as $domainKey => $domain) {
						$parameters = [];
						$domainArray = explode('.', $domainKey);
						$serverHostArray = $hostArray = explode('.', $serverHost);
						if (count($domainArray) == count($hostArray)) {
							foreach ($domain['parameters'] as $parameterKey => $parameter) {
								$parameters[$parameterKey] = $serverHostArray[$parameterKey];
								$serverHostArray[$parameterKey] = $parameter;
							}
							$newDomain = implode('.', $serverHostArray);
							if (isset($this->domains[$newDomain])) {
								$this->routeMiddleware->setDomainParameters($parameters);
								return $this->runDomain($this->domains[$newDomain], $parameters);
							} else {
								$serverHostArray = $hostArray;
							}
						}
					}
					return showErrorPage(self::PAGE_NOT_FOUND . showCallerInfo($this->caller), 404);

				}
				// FOR PARAMETER DOMAIN //
			}
			// INCLUDING DOMAIN CHECKING //
			return showErrorPage(self::PAGE_NOT_FOUND . showCallerInfo($this->caller), 404);
		} catch (Exception $e) {
			return showErrorPage($e->getMessage() . showCallerInfo($this->caller));
		}
	}
}