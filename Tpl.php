<?php
namespace Linice\Util;


/**
 * 模板类
 * @author los_gsy
 */
class Tpl {
	/**
	 * 替换模板字符串中的变量
	 * @param string $content
	 * @param array $replace
	 * @return string
	 */
	static public function replace($content, $replace = []) {
    	foreach ($replace as $k => $v) {
    		$content = str_replace("{{{$k}}}", $v, $content);
    	}
    	return $content;
	}

}
