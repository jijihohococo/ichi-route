<?php

namespace JiJiHoHoCoCo\IchiRoute\Setting;

class CORS
{

	private static $availableSites, $availableMethods, $availableHeaders, $availableSitesRegex;
	private static $maxAge = 0;
	private static $allowedCredential = FALSE;

	public static function setAvailableSites(array $sites)
	{
		self::$availableSites = $sites;
	}

	public static function getAvailableSites()
	{
		return getAccessData(self::$availableSites);
	}

	public static function setAvailableMethods(array $sites)
	{
		self::$availableMethods = $sites;
	}

	public static function getAvailableMethods()
	{
		return getAccessData(self::$availableMethods);
	}

	public static function setAvailableHeaders(array $headers)
	{
		self::$availableHeaders = $headers;
	}

	public static function getAvailableHeaders()
	{
		return getAccessData(self::$availableHeaders);
	}

	public static function setToAllowCredential()
	{
		self::$allowedCredential = TRUE;
	}

	public static function getAllowedCredential()
	{
		return self::$allowedCredential == TRUE ? 'true' : 'false';
	}

	public static function setMaxAge(int $age)
	{
		self::$maxAge = $age;
	}

	public static function getMaxAge()
	{
		return self::$maxAge;
	}

	public static function setAvailableSitesRegex(string $regex)
	{
		self::$availableSitesRegex = $regex;
	}

	public static function getAvailableSitesRegex()
	{
		return self::$availableSitesRegex;
	}
}