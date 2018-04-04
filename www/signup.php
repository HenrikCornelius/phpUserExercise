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
	private $first_name = '';
	private $first_name_err = '';
	private $last_name = '';
	private $last_name_err = '';
	private $email = '';
	private $email_err = '';

public function __construct( ) {
	parent::__construct( array( 'title' => 'Users - signup', 'startSession' => false) );
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
	$this->first_name = trim($_POST['first_name']);
	$this->last_name = trim($_POST['last_name']);
	$this->email = trim($_POST['email']);
	
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
	if(empty($this->first_name)){
		$this->focus = 'first_name';
		$this->password_err = 'Please enter your first name';
		return;
	}
	if(empty($this->last_name)){
		$this->focus = 'last_name';
		$this->password_err = 'Please enter your last name.';
 		return;
	}
	 if(empty($this->email)){
		$this->focus = 'email';
		  $this->password_err = 'Please enter your e-mail address.';
 		return;
	}

	$sql = 'SELECT count(1) as nbr FROM www_user '
		 . 'WHERE username = :username '
		 . 'and (deleted_dtm is null or deleted_dtm > current_timestamp) ';
	try {
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':username', $this->username, PDO::PARAM_STR);
		$sth->execute();
		while( $row=$sth->fetch(PDO::FETCH_ASSOC) ) {
			$userData = $row;
		}
		$sth->closeCursor();
	} catch (Exception $e) {
		return;
	}
	if ($userData['nbr'] > 0) {
		$this->focus = 'username';
		$this->username_err = 'This user name has already been used.';
		return;
	}
/*
** Create the account (Q&D: Using userId=1 for "created_by")
*/
	try {
		self::$conn->beginTransaction();
		$now = date("Y-m-d H:i:s");
		$sql = 'insert into www_user (username, password, first_name, last_name, email, created_by, created_dtm) '
			 . 'values(:username, :password, :first_name, :last_name, :email, 1, str_to_date(:created_dtm,\'%Y-%m-%d %H:%i:%s\') )';
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':username', $this->username, PDO::PARAM_STR);
		$sth->bindParam(':password', $this->password, PDO::PARAM_STR);
		$sth->bindParam(':first_name', $this->first_name, PDO::PARAM_STR);
		$sth->bindParam(':last_name', $this->last_name, PDO::PARAM_STR);
		$sth->bindParam(':email', $this->email, PDO::PARAM_STR);
/*		$sth->bindParam(':created_by',$this->who); */
		$sth->bindParam(':created_dtm',$now);
		$sth->execute();
		$userId = self::$conn->lastInsertId();
		$sth->closeCursor();
/* Reduce confusion by auto-granting "User_adm" permission. */
		$sql = 'insert into www_grant (fk_user_id, fk_permission_id, created_by, created_dtm) '
			 . 'select :userid, id, 1, str_to_date(:created_dtm,\'%Y-%m-%d %H:%i:%s\') from www_permission where name = \'User_adm\' ';
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':userid',$userId);
		$sth->bindParam(':created_dtm',$now);
		$sth->execute();
		$sth->closeCursor();
		self::$conn->commit();
	} catch (Exception $e) {
		return;
	}
	
	header("location: login.php");
	exit;
} // process_form()

public function local_styles() {
?>
.wrapper { width: 50ch; margin-top: 20px;}
<?php
} // local_styles()

private function setFocus( $element ) {
	if ($this->focus == $element) echo 'autofocus';
}
public function show_contents() {
?>
    <div class="container wrapper">
        <h2>Signup</h2>
        <p>Please fill in your information to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($this->username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" size="48" required value="<?php echo escape($this->username); ?>" <?php $this->setFocus('username'); ?>>
                <span class="help-block"><?php echo escape($this->username_err); ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($this->password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control" size="48" required value="<?php echo escape($this->password); ?>"  <?php $this->setFocus('password'); ?> >
                <span class="help-block"><?php echo escape($this->password_err); ?></span>
            </div>
            <div class="form-group <?php echo (!empty($this->first_name_err)) ? 'has-error' : ''; ?>">
                <label>First name:</label>
                <input type="first_name" name="first_name" class="form-control" size="48" required value="<?php echo escape($this->first_name); ?>"  <?php $this->setFocus('first_name'); ?> >
                <span class="help-block"><?php echo escape($this->first_name_err); ?></span>
            </div>
            <div class="form-group <?php echo (!empty($this->last_name_err)) ? 'has-error' : ''; ?>">
                <label>Last_name</label>
                <input type="last_name" name="last_name" class="form-control" size="48" required value="<?php echo escape($this->last_name); ?>"  <?php $this->setFocus('last_name'); ?> >
                <span class="help-block"><?php echo escape($this->last_name_err); ?></span>
            </div>
            <div class="form-group <?php echo (!empty($this->email_err)) ? 'has-error' : ''; ?>">
                <label>E-mail</label>
                <input type="email" name="email" class="form-control" size="48" required value="<?php echo escape($this->email); ?>"  <?php $this->setFocus('email'); ?> >
                <span class="help-block"><?php echo escape($this->email_err); ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Create account">
            </div>
        </form>
    </div>
<?php
} // show_contents

} // myPage

$myPage = new myPage();
$myPage->run();
?>
