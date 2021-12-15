<?php

namespace JiJiHoHoCoCo\IchiRoute\Setting;

class CORS{

	private static $availableSites , $availableMethods , $availableHeaders ;
	private static $allowedCredential = 'false';

	public static function setAvailableSites(array $sites){
		self::$availableSites=$sites;
	}

	public static function getAvailableSites(){
		return getAccessData(self::$availableSites);
	}

	public static function setAvailableMethods(array $sites){
		self::$availableMethods=$sites;
	}

	public static function getAvailableMethods(){
		return getAccessData(self::$availableMethods);
	}

	public static function setAvailableHeaders(array $headers){
		self::$availableHeaders=$headers;
	}

	public static function getAvailableHeaders(){
		return getAccessData(self::$availableHeaders);
	}

	public static function setToAllowCredential(){
		self::$allowedCredential='true';
	}

	public static function getAllowedCredential(){
		return self::$allowedCredential;
	}
}