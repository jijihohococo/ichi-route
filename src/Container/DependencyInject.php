<?php

namespace JiJiHoHoCoCo\IchiRoute\Container;

use ReflectionClass, Exception;

class DependencyInject
{

	private $keyValues = [];
	private $createdClass;
	private $functionName;
	private $functionParameters;

	public function setCreatedClass($className)
	{
		$this->createdClass = $this->getObject($className);
	}

	public function getCreatedClass()
	{
		return $this->createdClass;
	}

	public function setFunctionName($functionName)
	{
		$this->functionName = $functionName;
	}

	public function getFunctionName()
	{
		return $this->functionName;
	}

	public function setFunctionParameters($functionParameters)
	{
		$this->functionParameters = $functionParameters;
	}

	public function getFunctionParameters()
	{
		return $this->functionParameters;
	}

	public function setKeyValue(string $key, $value)
	{
		if ($this->createdClass == NULL) {
			throw new Exception("You need to set the created class");
		}
		if (!method_exists($this->createdClass, "setKeyValue")) {
			throw new Exception("'setKeyValue' function is not exist in " . get_class($this->createdClass) . " Class.", 1);
		}
		$this->createdClass->setKeyValue($key, $value);
	}

	public function getKeyValue(string $key)
	{
		if ($this->createdClass == NULL) {
			throw new Exception("You need to set the created class");
		}
		if (!method_exists($this->createdClass, "getKeyValue")) {
			throw new Exception("'getKeyValue' function is not exist in " . get_class($this->createdClass) . " Class.", 1);
		}
		return $this->createdClass->getKeyValue($key);
	}

	public function runClassFunction()
	{
		if ($this->createdClass == NULL) {
			throw new Exception("You need to set the created class");
		}
		if ($this->functionName == NULL) {
			throw new Exception("You need to set the function name");
		}
		if (!method_exists($this->createdClass, $this->functionName)) {
			throw new Exception($this->functionName . " function is not exist in " . get_class($this->createdClass) . " Class.", 1);
		}

		return call_user_func_array([
			$this->createdClass,
			$this->functionName
		], $this->functionParameters);
	}

	public function getObject($className)
	{
		$class = new ReflectionClass($className);
		$constructor = $class->getConstructor();
		if (!is_null($constructor)) {
			return $class->newInstanceArgs($this->createObjects($constructor->getParameters()));
		}
		return new $className;
	}

	public function createObjects($parameters)
	{
		$objects = [];
		$interface = 'Interface';
		foreach ($parameters as $key => $parameter) {
			$type = str_replace($interface, '', (string) $parameter->getType());
			if (!interface_exists($type . $interface)) {
				throw new Exception($parameter . " Interface is not exist.", 1);
			}
			$typeArray = explode('\\', $type);
			if (isset($typeArray[0])) {

				$objects[] = $this->getObject($type);
			}
		}
		return $objects;

	}

	public function getClassFilePath()
	{
        // Get the reflection class for the current class
        $reflection = new ReflectionClass($this);
        
        // Get the filename associated with the class
        $filePath = $reflection->getFileName();
        
        return $filePath;
    }
}