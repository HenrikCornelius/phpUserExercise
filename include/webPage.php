<?php
require_once('dbConn.php');
require_once('myFunctions.php');
require_once('dbSessionHandler.php');

class webPage extends dbConn {

	public $myScript;
	public $title;
	public $startSession = true;
	public $requiredPrivilege = 'public';
	public $loggedIn = false;
	public $userid;
	public $userCaption;
	public $showMenu = true;
	protected $mySession;

public function __construct( $options = array() ) {
	foreach ($options as $key => $value) {
		if ($key == 'title') {
			$this->title = $value;
		} elseif ($key == 'startSession') {
			$this->startSession = $value;
		} elseif ($key == 'requiredPrivilege') {
			$this->requiredPrivilege = $value;
		}
	}
	parent::__construct();
	$this->myScript = pathinfo(basename($_SERVER['SCRIPT_NAME']), PATHINFO_FILENAME);
//
	$this->mySession = dbSessionHandler::getInstance();
	if ($this->startSession == true) $this->init_session();
}

protected function show_header() {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <META HTTP-EQUIV="Expires" CONTENT="-1">

<script type="text/javascript" src="http://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="http://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
<link href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css">

    <!-- Bootstrap -->
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" >
<?php
    echo '<title>' . $this->title . '</title>' . "\n";
	$this->more_header();
	echo '<style>' . "\n";
?>
.flex-container {
  display: flex;
  flex-flow: column;
  height: 100vh;
  width: 100vw;
}
.flex-item {
  flex: 1;
  overflow: hidden;
}
<?php
	$this->local_styles();
	echo '</style>' . "\n";
	echo '<script>' . "\n";
?>
$(document).ready(function(){
	var isInIFrame = (window.location != window.parent.location);
	$('.navbar-inverse').each( function(index,elem) {
		if(isInIFrame == true) {
			this.style.display = 'none';
		} else {
			this.style.display = '';
		};
	});
});
<?php
	$this->local_script();
	echo '</script>' . "\n";
	echo '</head>' . "\n";
} // show_header

public function more_header() {
} // more_header

public function local_styles() {
} // local_styles

public function local_script() {
} // local_script

public function show_contents() {
} // show_contents

public function show_modal_contents() {
} // show_modal_contents

public function show_page() {

// Autorization check
	if ($this->startSession == true) {
		if (has_privilege($this->requiredPrivilege) == false) {
			header("location: login.php");
			exit;
		}
	}

	$this->show_header();
	echo '<body>' . "\n";
	echo '<div id="contents" class=flex-container>' . "\n";
	$this->show_contents();
	echo '</div>' . "\n";
	$this->show_modal_contents();
	echo '</body>' . "\n";
	echo '</html>' . "\n";
} // show_page

public function show_menu_page() {

// Autorization check
	if ($this->startSession == true) {
		if (has_privilege($this->requiredPrivilege) == false) {
			header("location: login.php");
			exit;
		}
	}

// Print the page
	$this->show_header();
	echo '<body style="overflow-x: hidden; overflow-y: hidden;">' . "\n";
	echo '<div id="contents" class=flex-container>' . "\n";
	if ($this->showMenu == true) $this->show_menu();
	$this->show_contents();
	echo '</div>' . "\n";
	$this->show_modal_contents();
	echo '</body>' . "\n";
	echo '</html>' . "\n";
} // show_menu_page

private function show_menu() {
	require_once('menu.php');
} // show_menu

private function init_session() {
	session_start();
	if ( isset($_SESSION['loggedin']) && empty($_SESSION['loggedin']) == false &&
		isset($_SESSION['userid']) && empty($_SESSION['userid']) == false &&
		isset($_SESSION['userCaption']) && empty($_SESSION['userCaption']) == false) {
		$this->loggedIn = $_SESSION['loggedin'];
		$this->userid = $_SESSION['userid'];
		$this->userCaption = $_SESSION['userCaption'];
		session_write_close();
	} else {
		$this->loggedIn = false;
		session_destroy();
	}
} // init_session

} // webPage

//	file_put_contents('../trash/debug.txt', print_r($_COOKIE, true), FILE_APPEND );

?>
