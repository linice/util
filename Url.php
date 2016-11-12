<?php
namespace Linice\Util;

/**
 * Url
 * @author los
 */
class Url {
	/**
	 * 获取当前请求的完整URL
	 * @return string
	 */
	static public function currPageUrl() {
		$pageURL = 'http';

		if (isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS'])
		{
			$pageURL .= 's';
		}
		$pageURL .= '://';

		if ($_SERVER['SERVER_PORT'] != '80')
		{
			$pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
		}
		else
		{
			$pageURL .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		}
		return $pageURL;
	}

}
