<?php
namespace Linice\Util;

/**
 * 加/解密
 * @author los
 */
class Encrypt {
	/**
	 * 加密密码。不可逆，所以用于加密密码。
	 * @param string $password	明文密码
	 */
	static public function encryptPassword($password) {
		$salt_cnt = 8; /* Number of bytes with salt. */
		$salt = '';
		for ($i = 0; $i < $salt_cnt; $i++) {
			$salt .= chr(mt_rand(0, 255));
		}
		$digest = sha1($password . $salt, TRUE);
		return base64_encode($digest . $salt);
	}


	/**
	 * 检测encryptPassword加密的密文与明文是否一致
	 * A helper function for validating a password hash.
	 * In this example we check a SSHA-password, where the database
	 * contains a base64 encoded byte string, where the first 20 bytes
	 * from the byte string is the SHA1 sum, and the remaining bytes is
	 * the salt.
	 * @param string $cipher	密文密码
	 * @param string $password	明文密码
	 */
	static public function checkPassword($cipher, $password) {
		$cipher = base64_decode($cipher);
		$digest = substr($cipher, 0, 20);
		$salt = substr($cipher, 20);

		$check_digest = sha1($password . $salt, TRUE);
		return $digest === $check_digest;
	}


	/**
	 * authcode可逆加/解密
	 * 明文加密后得到密文是不确定的。
	 * @param string $string	明文 或 密文
	 * @param string $operation	操作，ENCODE：加密；DECODE：解密；
	 * @param string $key	密匙
	 * @param number $expiry	密文有效期（秒），如3600表示3600s内有效
	 * @return string
	 */
	static public function authcode($string, $operation = 'ENCODE', $key = '9yUpbcx4TcBJ9Tg9', $expiry = 0) {
		// 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
		$ckey_length = 4;
		// 密匙
		$key = md5($key);
		// 密匙a会参与加解密
		$keya = md5(substr($key, 0, 16));
		// 密匙b会用来做数据完整性验证
		$keyb = md5(substr($key, 16, 16));
		// 密匙c用于变化生成的密文
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
		//PHP加密解密函数authcode参与运算的密匙
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);

		// 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
		// 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
		$result = '';
		$box = range(0, 255);
		$rndkey = array();
		//PHP加密解密函数authcode产生密匙簿
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
		// 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		//PHP加密解密函数authcode核心加解密部分
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			// PHP加密解密函数authcode从密匙簿得出密匙进行异或，再转成字符
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		if($operation == 'DECODE') {
			// substr($result, 0, 10) == 0 验证数据有效性
			// substr($result, 0, 10) - time() > 0 验证数据有效性
			// substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
			// 验证数据有效性，请看未加密明文的格式
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			//PHP加密解密函数authcode把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
			// 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}

}
