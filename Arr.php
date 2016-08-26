<?php
namespace Linice\Util;

/**
 * 数组类
 * @author los_gsy
 */
class Arr {
	/**
	 * 获取数组第一个不为空的元素
	 * @param array $arr
	 * @return mixed	如未找到返回null
	 */
	static public function firstNotEmpty(array $arr) {
		foreach ($arr as $v) {
			if (!empty($v)) {
				return $v;
			}
		}
		return null;
	}


	/**
	 * 按键名排序数组
	 * @param mixed<array|object> $arr
	 * @return void
	 */
	static public function sortByKey(&$arr) {
		if (is_object($arr)) {
			$arr = (array)$arr;
		}
		foreach ($arr as &$v) {
			if (is_array($v) || is_object($v)) {
				self::sortByKey($v);
			}
		}
		ksort($arr);
	}

}
