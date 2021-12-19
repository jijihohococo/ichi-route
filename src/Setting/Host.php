<?php

namespace JiJiHoHoCoCo\IchiRoute\Setting;

class Host{

	private $defaultDomain;

	public function getDefaultDomain(){
		if($this->defaultDomain==NULL){
			return $_SERVER['HTTP_HOST'];
		}
		return $this->defaultDomain;
	}

	public function setDefaultDomain(string $domain){
		$this->defaultDomain=$domain;
	}

}