<?php

/**
 * Description of HttpHelper
 *
 * @author arek
 */
class HttpHelper implements iHttpHelper
{

	/**
	 * HTTP method GET
	 * @param string $url
	 * @return string
	 */
	public function httpGet($url)
	{
		$str = file_get_contents($url);
		return $str;
	}
}
