<?php

namespace JiJiHoHoCoCo\IchiRoute\Controller;

abstract class BaseController{

	protected $keyValues = [];

	public function setKeyValue(string $key, $value) {
		$this->keyValues[$key] = $value;
	}

	public function getKeyValue(string $key) {
		if (isset($this->keyValues[$key])) {
			return $this->keyValues[$key];
		}
		return null;
	}

	public function getKeyValues() {
		return $this->keyValues;
	}

}