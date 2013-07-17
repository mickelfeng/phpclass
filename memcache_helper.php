<?php if (!defined('BASEPATH'))
  exit('No direct script access allowed');
/**
 * 这个助手类主要特点，Memcache连接复用，一个http请求只使用一个Memcache连接
 * 使用Key前缀，使多个项目共享Memcache
 * User: 线筝 http://h5b.net/
 */
class Mcache
{
	static $conn = null;

	static function connect()
	{
		//如果已经有连接，则不在创建新的连接
		if (is_object(self::$conn)) {
			return self::$conn;
		}

		$server = array(
			array(
				'host' => '127.0.0.1',
				'port' => '11211'
			)
		);

		self::$conn = new Memcache;
		for ($i = 0; $i < count($server); $i++) {
			self::$conn->addServer($server[$i]['host'], $server[$i]['port'], false);
		}

		return self::$conn;
	}

	/**
	 * 当要使用助手类没有封装的Memcache方法时，用这个方法获取key
	 * @param $key
	 * @return string
	 */
	static function key($key)
	{
		return md5('h5b.net_' . $key);
	}

	static function read($key)
	{
		$key = md5('h5b.net_' . $key);

		$ret = null;
		if ($conn = self::connect($key)) {
			$ret = $conn->get($key);
		}
		return $ret;
	}

	static function write($key, $val, $expire = 0, $flag = 0)
	{
		$key = md5('h5b.net_' . $key);

		$ret = null;
		if ($conn = self::connect($key)) {
			$ret = $conn->set($key, $val, $flag, $expire);
		}
		return $ret;
	}

	static function delete($key, $expire = 0)
	{
		$key = md5('h5b.net_' . $key);

		$ret = null;
		if ($conn = self::connect($key)) {
			$ret = $conn->delete($key, $expire);
		}
		return $ret;
	}
}
