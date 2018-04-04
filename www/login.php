<?php
require_once('webPage.php');
// ##############################
// userMenu
// ##############################
class myPage extends webPage {
	private $focus = 'username';
	private $username = '';
	private $username_err = '';
	private $password = '';
	private $password_err = '';

public function __construct( ) {
	parent::__construct( array( 'title' => 'Users - Login', 'startSession' => false) );
} // __construct

public function run() {
	if ( isset($_COOKIE['PHPSESSID']) ) $this->do_logout();

	if($_SERVER["REQUEST_METHOD"] == "POST"){
		$this->process_form();
	}
	$this->show_menu_page();
} // run()

public function do_logout() {
	session_start();
	$_SESSION = array();
	session_destroy();
} // do_logout()

public function process_form() {
	$this->username = trim($_POST['username']);
	$this->password = trim($_POST['password']);
	
	if(empty($this->username)){
		$this->focus = 'username';
		$this->username_err = 'Please enter username.';
		return;
	}
	if(empty($this->password)){
		$this->focus = 'password';
		$this->password_err = 'Please enter your password.';
		return;
	}

	$sql = 'SELECT * FROM www_user '
		 . 'WHERE username = :username and password = :password '
		 . 'and (deleted_dtm is null or deleted_dtm > current_timestamp) ';
	try {
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':username', $this->username, PDO::PARAM_STR);
		$sth->bindParam(':password', $this->password, PDO::PARAM_STR);
		$sth->execute();
		while( $row=$sth->fetch(PDO::FETCH_ASSOC) ) {
			$userData = $row;
		}
		$sth->closeCursor();
	} catch (Exception $e) {
		return;
	}
	if (isset($userData['id']) == false) {
		$this->focus = 'username';
		$this->username_err = 'Login failed !';
		return;
	}
	
	if ($userData['is_enabled'] == false) {
		$this->focus = 'username';
		$this->username_err = 'This account is disabled !';
		return;
	}
	session_start();
	$_SESSION['loggedin'] = true;
	$_SESSION['userid'] = $userData['id'];
	$_SESSION['is_superuser'] = $userData['is_superuser'];
	if ( empty($userData['first_name']) == false || empty($userData['last_name']) == false) {
		$_SESSION['userCaption'] = trim( $userData['first_name'] . ' ' . $userData['last_name'] );
	} else {
		$_SESSION['userCaption'] = $userData['username'];
	}
// get privileges
	$_SESSION['privileges'] = array();
	$sql = 'SELECT b.name as permission_name'
		 . ', case when a.can_update = true then \'write\' else \'read\' end as permission_mode '
		 . 'FROM www_grant a '
		 . 'inner join www_permission b on a.fk_permission_id = b.id '
		 . 'WHERE a.fk_user_id = :userid ';
	try {
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':userid', $_SESSION['userid'], PDO::PARAM_STR);
		$sth->execute();
		while( $row=$sth->fetch(PDO::FETCH_ASSOC) ) {
			$privName = $row['permission_name'];
			$privMode = $row['permission_mode'];
			$_SESSION['privileges'][$privName] = $privMode;
		}
		$sth->closeCursor();
	} catch (Exception $e) {
		return;
	}
	session_write_close();
	header("location: usersPage.php");
	exit;
} // process_form()

public function local_styles() {
?>
.wrapper { width: 50ch; margin-top: 60px;}
}
<?php
} // local_styles()

private function setFocus( $element ) {
	if ($this->focus == $element) echo 'autofocus';
}
public function show_contents() {
?>
    <div class="container wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($this->username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" size="48" required value="<?php echo escape($this->username); ?>" <?php $this->setFocus('username'); ?>>
                <span class="help-block"><?php echo escape($this->username_err); ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($this->password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control" size="48" required <?php $this->setFocus('password'); ?> >
                <span class="help-block"><?php echo escape($this->password_err); ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="signup.php">Sign up now</a>.</p>
        </form>
    </div>
<?php
} // show_contents

} // myPage

$myPage = new myPage();
$myPage->run();
?>
