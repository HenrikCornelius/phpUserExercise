<?
require_once('clsConfig.php');
//
class dbConn extends clsConfig {
	private static $is_online = false;
	public static $conn;
// 
// Constructor creates connection
//
public function __construct() {
	parent::__construct();

// Create connection
	if (self::$is_online==false) {
		$opt = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_EMULATE_PREPARES   => false,
			PDO::MYSQL_ATTR_LOCAL_INFILE => true
		];
		try {
			self::$conn = new PDO(self::$dsn, self::$dbuser, self::$dbpwd, $opt);
			self::$conn->exec('SET sql_mode=\'ANSI_QUOTES\'');
			self::$is_online=true;
		} catch (PDOException $e) {
			echo "ERROR connecting to repository: " . $e->getMessage() . '<br>';
			echo "Driver: " . self::$dsn . '<br>';
			exit();
		}
	}
} // construct

function exec($pSql) {
		try {
			self::$conn->exec($pSql);
		} catch (PDOException $e) {
			parent::lerror("ERROR connecting to repository: " . $e->getMessage() );
			parent::lerror("SQL: ");
			parent::lerror($pSql );
			exit();
		}
} // exec
// ##############################
function getConn() {
	return self::$conn;
} // getConn

// ##############################
} // dbConn
?>