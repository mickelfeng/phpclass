/**
* session信息存储到数据库的类
* 表结构：
* CREATE TABLE IF NOT EXISTS `sessioninfo` (
*  `sid` varchar(255) NOT NULL,
*  `value` text NOT NULL,
*  `expiration` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
*  PRIMARY KEY (`sid`)
* ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/
class MySessionHandler implements SessionHandlerInterface {

	/**
	* @access private
	* @var object 数据库连接
	*/
	private $_dbLink;
	/**
	* @access private
	* @var string 保存session的表名
	*/
	Private $_sessionTable;
	/**
	* @access private
	* @var string session名
	*/
	private $_sessionName;
	/**
	* @const 过期时间
	*/
	const SESSION_EXPIRE = 10;

	public function __construct($dbLink, $sessionTable) {
		if(!is_object($dbLink)) {
			return false;
		}
		$this->_dbLink = $dbLink;
		$this->_sessionTable = $sessionTable;
	}

	/**
	* 打开
	* @access public
	* @param string $session_save_path 保存session的路径
	* @param string $session_name session名
	* @return integer
	*/
	public function open($session_save_path, $session_name) {
		$this->_sessionName = $session_name;
		return 0;
	}

	/**
	* 关闭
	* @access public
	* @return integer
	*/
	public function close() {
		return 0;
	}

	/**
	* 关闭session
	* @access public
	* @param string $session_id session ID
	* @return string
	*/
	public function read($session_id) {
		$query = "SELECT value FROM {$this->_sessionTable} WHERE sid = {$session_id} AND UNIX_TIMESTAMP(expiration) + " . self::SESSION_EXPIRE . " > UNIX_TIMESTAMP(NOW())";
		$result = $this->_dbLink->query($query);
		if(!isset($value) || empty($value)) {
			$value = "";
			return $value;
		}
		$this->_dbLink->query("UPDATE {$this->_sessionTable} SET expiration = CURRENT_TIMESTAMP() WHERE sid = {$session_id}");
		$value = $result->fetch_array();
		$result->free();
		return $value['value'];
	}

	/**
	* 写入session
	* @access public
	* @param string $session_id session ID
	* @param string $session_data session data
	* @return integer
	*/
	public function write($session_id, $session_data) {
		$query = "SELECT value FROM {$this->_sessionTable} WHERE sid = '{$session_id}' AND UNIX_TIMESTAMP(expiration) + " . self::SESSION_EXPIRE . " > UNIX_TIMESTAMP(NOW())";
		$result = $this->_dbLink->query($query);
		$result = $result->fetch_array();
		if(!empty($result)) {
			$result = $this->_dbLink->query("UPDATE {$this->_sessionTable} SET value = {$session_data} WHERE sid = {$session_id}");
		}
		else{
			$result = $this->_dbLink->query("INSERT INTO {$this->_sessionTable} (sid, value) VALUES ('{$session_id}', '{$session_data}')");
		}
		if($result){
			return 0;
		}
		else{
			return 1;
		}		
	}

	/**
	* 销魂session
	* @access public
	* @param string $session_id session ID
	* @return integer
	*/
	public function destroy($session_id) {
		$result = $this->_dbLink->query("DELETE FROM {$this->_sessionTable} WHERE sid = '{$session_id}'");
		if($result){
			return 0;
		}
		else{
			return 1;
		}
	}

	/**
	* 垃圾回收
	* @access public
	* @param string $maxlifetime session 最长生存时间
	* @return integer
	*/
	public function gc($maxlifetime) {
		$result = $this->_dbLink->query("DELETE FROM {$this->_sessionTable} WHERE UNIX_TIMESTAMP(expiration) < UNIX_TIMESTAMP(NOW()) - " . self::SESSION_EXPIRE);
		if($result){
			return 0;
		}
		else{
			return 1;
		}
	}

}



/**********************************************************************************************************************************/

$dbLink = new mysqli("localhost", "root", "root", "test");
$sessionTable = "sessioninfo";

$handler = new MySessionHandler($dbLink, $sessionTable);
session_set_save_handler($handler);
session_start();
$_SESSION['name'] = "test";
echo $_SESSION["name"];
//session_destroy();
