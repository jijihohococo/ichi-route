<?php

namespace JiJiHoHoCoCo\IchiRoute\Middleware;

use ReflectionMethod;
abstract class MainMiddleware{
	
	public $next;

	public $parameters = [];
	public $domainParameters = [];

	public function setNext(MainMiddleware $next){
		$this->next = $next;
		return $next;
	}

	public function setParameters(array $parameters){
		$this->parameters = $parameters;
	}

	public function setDomainParameters($domainParameters){
		$this->domainParameters = $domainParameters;
	}

	protected function getDomainParameters(){
		return $this->domainParameters;
	}

	public function getParameters(){
		return $this->parameters;
	}



	

	public function next(){
		if($this->next!==null){
			$nextClass=(string)get_class($this->next);
			$reflectionMethod=new ReflectionMethod($nextClass ,'handle');
			return $reflectionMethod->invokeArgs($this->next,$this->next->getParameters() );
		}
	}
}