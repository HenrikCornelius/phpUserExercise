<?
class clsConfig {
	private static $conf_loaded=false;
	public static $dbdriver="";
	public static $dbhost="";
	public static $dbport="";
	public static $dbname="";
	public static $charset="";
	public static $dbuser="";
	public static $dbpwd="";
	public static $dsn="";
//
	public static $NL="<br>\n";
	public static $errorlog="error_log.txt";
// 
// Constructor creates connection
//
public function __construct() {

// Load configuration as an array.
	if (! self::$conf_loaded ) {
		$config = parse_ini_file('../cfg/config.ini');
		self::$dbdriver = $config['dbdriver'];
		self::$dbhost = $config['dbhost'];
		self::$dbport = $config['dbport'];
		self::$dbname = $config['dbname'];
		self::$charset = $config['charset'];
		self::$dbuser = $config['dbuser'];
		self::$dbpwd  = $config['dbpwd'];
		self::$errorlog  = $config['errorlog'];
		//self::$dsn = self::$dbdriver . ':host=' . self::$dbhost . ';port=' . self::$dbport . ';dbname=' . self::$dbname;
		self::$dsn = self::$dbdriver . ':host=' . self::$dbhost . ';port=' . self::$dbport . ';dbname=' . self::$dbname . ';charset=' . self::$charset;
		self::$conf_loaded = true;
	}
} // construct

function setNewlineUnix() {
	self::$NL = "\n";
}

function setNewlineHtml() {
	self::$NL = "<br>\n";
}

function lerror($pMsg) {
	file_put_contents(self::$errorlog, $pMsg . "\n", FILE_APPEND | LOCK_EX);
}

// ##############################
} // clsConfig
?>