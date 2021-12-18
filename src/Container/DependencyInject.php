<?php

namespace JiJiHoHoCoCo\IchiRoute\Container;
use ReflectionClass;
class DependencyInject{

	public function getConstructor($className,$functionName,$functionParameters){
		$createdClass=$this->getObject($className);
		if(!method_exists($createdClass, $functionName)){
			throw new \Exception($functionName. " function is not exist in ".$className . " Class", 1);
		}
		return call_user_func_array([$createdClass,$functionName], $functionParameters);
	}

	public function getObject($className){
		$class=new  ReflectionClass($className);
		$constructor=$class->getConstructor();
		if(!is_null($constructor) ){
			return $class->newInstanceArgs($this->createObjects($constructor->getParameters()));
		}
		return new $className; 
	}

	public function createObjects($parameters){
		$objects=[];
		foreach($parameters as $key => $parameter ){
			if(!interface_exists($parameter)){
				throw new \Exception($parameter . " Interface is not exist", 1);
			}
			$type= str_replace('Interface', '', (string)$parameter->getType());
			$typeArray=explode('\\' , $type );
			if(isset($typeArray[0])){
				
				$objects[]=$this->getObject($type);
			}
		}
		return $objects;

	}
}