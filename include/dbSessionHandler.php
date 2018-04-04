<?php
require_once('dbConn.php');
class dbSessionHandler extends dbConn implements SessionHandlerInterface {
	private static $_instance;
	protected $exists;
	protected $session_path;
	protected $session_name;

	public function __construct() {
		parent::__construct();
		session_set_save_handler($this);
//		ini_set('session.gc_divisor',50); // Test garbage collection at each login.
		ini_set('session.gc_maxlifetime',86400); // session idle time - 1 day.
	}

	public static function getInstance() {
		if ( !(self::$_instance instanceof self) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function open($save_path, $name) {
		$this->session_path = $save_path;
		$this->session_name = $name;
		return true;
	}

	public function close() {
		return true;
	}

	public function destroy($session_id) {
		$sth = self::$conn->prepare('DELETE FROM www_sessions WHERE session_id = ?');
		$sth->execute(array($session_id));
		return true;
	}

	public function gc($maxlifetime) {
		$sth = self::$conn->prepare('DELETE FROM www_sessions WHERE updated_dtm < from_unixtime(?)');
		$sth->execute(array(time() - $maxlifetime));
		return true;
	} // gc

	public function read($session_id) {
		$sth = self::$conn->prepare('SELECT session_data FROM www_sessions WHERE session_id = ?');
		$sth->execute(array($session_id));
		$rows = $sth->fetchALL(PDO::FETCH_NUM);
		if (count($rows) == 0) {
			$this->exists = 'n';
			return '';
		}
		else {
			$this->exists = 'y';
			return $rows[0][0];
		}   
	} // read

	public function write($session_id, $session_data) {
		if ($this->exists == 'y') {
			$sth = self::$conn->prepare('UPDATE www_sessions SET session_data = ?, updated_dtm = NOW() WHERE session_id = ?');
			$sth->execute(array($session_data, $session_id));
		}
		if ($this->exists == 'n') {
			$sql = 'INSERT INTO www_sessions (session_id, session_data, session_path, session_name, updated_dtm) '
				 . 'VALUES (?, ?, ?, ?, NOW())';
			$sth = self::$conn->prepare($sql);
			$sth->execute(array($session_id, $session_data, $this->session_path, $this->session_name));
			$this->exists = 'y';
		}
		return true;
	} // write
} // dbSessionHandler
?>