<?php

namespace JiJiHoHoCoCo\IchiRoute\Setting;

class Host{

	private $defaultDomain='localhost';

	public function getDefaultDomain(){
		return $this->defaultDomain;
	}

	public function setDefaultDomain(string $domain){
		$this->defaultDomain = $domain;
	}

}