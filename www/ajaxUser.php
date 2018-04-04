<?php
require_once('dbConn.php');
require_once('myFunctions.php');
require_once('dbSessionHandler.php');
// #########################
// User
// #########################
class ajaxUser extends dbConn {
	public $userId;
	public $result;
	public $who = '1';
	public $now;
	protected $mySession;
//
	private $keys = array("id");
	private $attrib = array("username", "first_name", "last_name", "email", "is_enabled", "is_superuser", "password", "password_expiry");
	private $ins_hist = array("created_by", "created_dtm");
	private $upd_hist = array("updated_by", "updated_dtm");
// ####################################
function read_user( $pId ) {
	try {
		$sql = 'select a.*, crea.username created_username, upda.username updated_username '
			 . ', case '
			 . 'when a.is_enabled = false then \'Locked\' '
			 . 'when coalesce(a.password_expiry,current_timestamp) < current_timestamp then \'Expired\' '
			 . 'else \'Open\' '
			 . 'end as account_status '
			 . 'from www_user a '
			 . 'left outer join www_user crea on a.created_by = crea.id '
			 . 'left outer join www_user upda on a.updated_by = upda.id '
			 . 'where a.id = :id';
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(":id",$pId);
		$sth->execute();
		while( $row=$sth->fetch(PDO::FETCH_ASSOC) ) {
			$this->result['data'] = $row;
		}
		$sth->closeCursor();
/*
** get permissions
*/
		$sql = 'SELECT a.id, a.name, a.description, b.can_update, b.fk_user_id '
			. 'FROM `www_permission` a '
			. 'left outer join www_grant b ON a.id = b.fk_permission_id '
			. 'and b.fk_user_id = :userid '
			. 'order by 1 ';
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(":userid",$pId);
		$sth->execute();
		$permission = [];
		while( $row=$sth->fetch(PDO::FETCH_ASSOC) ) {
			if ( $row['can_update'] == '1') {
				$permission[ 'permission' . $row['id'] ] = 'write';
			} elseif ( $row['can_update'] == '0') {
				$permission[ 'permission' . $row['id'] ] = 'read';
			} else {
				$permission[ 'permission' . $row['id'] ] = 'none';
			}
		}
		$this->result['data']['permission'] = $permission;
		echo json_encode($this->result);
	} catch (Exception $e) {
		$this->error_exit($e, 'Retrieval of the information failed.');
	}
} // read
// ####################################
function validate_form( ) {
	if (strlen($_REQUEST['username']) < 3) {
		$this->result['status'] = 'error';
		$this->result['field'] = 'username';
		$this->result['message'] = '"username" must be at least 3 characters';
		echo json_encode($this->result);
		exit;
	}
// Check for duplicate username
	try {
		$sql = 'select max(id) as userid '
			 . 'from www_user  '
			 . 'where username = :username '
			 . 'and (deleted_dtm is null or deleted_dtm > current_timestamp) ';
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':username',$_REQUEST['username']);
		$sth->execute();
		$foundId = -7;
		while( $row=$sth->fetch(PDO::FETCH_ASSOC) ) {
			$foundId = $row['userid'];
		}
		$sth->closeCursor();
		if ($foundId > 0 && $foundId != $this->userId) {
			$this->result['status'] = 'error';
			$this->result['field'] = 'username';
			$this->result['message'] = 'Username "' . $_REQUEST['username'] . '" is in use.';
			echo json_encode($this->result);
			exit;
		}
	} catch (Exception $e) {
		$this->error_exit($e, 'Retrieval of the information failed.');
	}
	return true;
} // validate_form
// ####################################
function create_user( ) {
	if ($this->validate_form() == false) {
		return;
	}
	try {
		$sql = 'insert into '
			 . 'www_user (username, first_name, last_name, email, is_enabled, is_superuser, password, password_expiry, created_by, created_dtm) '
			 . 'values (:username, :first_name, :last_name, :email, :is_enabled, :is_superuser, :password, :password_expiry, :created_by, str_to_date(:created_dtm,\'%Y-%m-%d %H:%i:%s\')) ';
		self::$conn->beginTransaction();
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':username',$_REQUEST['username']);
		$sth->bindParam(':first_name',$_REQUEST['first_name']);
		$sth->bindParam(':last_name',$_REQUEST['last_name']);
		$sth->bindParam(':email',$_REQUEST['email']);
		$sth->bindParam(':is_enabled',$_REQUEST['is_enabled']);
		$sth->bindParam(':is_superuser',$_REQUEST['is_superuser']);
		$sth->bindParam(':password',$_REQUEST['password']);
		$sth->bindParam(':password_expiry',$_REQUEST['password_expiry']);
		$sth->bindParam(':created_by',$this->who);
		$sth->bindParam(':created_dtm',$this->now);
		$sth->execute();
		$this->userId = self::$conn->lastInsertId();
		$rowCount = $sth->rowCount();
		$sth->closeCursor();
//
		$this->update_permissions( $this->userId );
		self::$conn->commit();
//
	} catch (Exception $e) {
		$this->error_exit($e, 'User creation failed.');
	}
	$this->read_user($this->userId);
} // create_user()
// ####################################
function update_user( $pId ) {
	if ($this->validate_form() == false) {
		return;
	}
	$cols = array_merge($this->attrib);
	try {
		$sql = 'update www_user '
			 . 'set updated_by = :updated_by'
			 . ', updated_dtm = str_to_date(:updated_dtm, \'%Y-%m-%d %H:%i:%s\')';
		$more = ', ';
		foreach ($cols as $coname) {
			$sql .= $more . $coname . '= :' . $coname;
			$more = ', ';
		}
		$more = ' where ';
		foreach ($this->keys as $coname) {
			$sql .= $more . $coname . '= :' . $coname;
			$more = ', ';
		}
		self::$conn->beginTransaction();
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':updated_by',$this->who);
		$sth->bindParam(':updated_dtm',$this->now);
		foreach ($cols as $coname) {
			$sth->bindParam(':' . $coname,$_REQUEST[$coname]);
		}
		foreach ($this->keys as $coname) {
			$sth->bindParam(':' . $coname,$_REQUEST[$coname]);
		}
		$sth->execute();
		$rowCount = $sth->rowCount();
		$sth->closeCursor();
//
		$this->update_permissions( $this->userId );
		self::$conn->commit();
//
	} catch (Exception $e) {
		$this->error_exit($e, 'Update failed');
	}
	$this->read_user($pId);
} // update
// ###########################
function update_permissions( $pId ) {
	try {
		if (array_key_exists("permission",$_REQUEST) == false) {
			throw new Exception('ajaxUser::update_permissions: $_REQUEST[\'permission\'] does not exist !');
		}
		if (is_array($_REQUEST['permission']) == false) {
			throw new Exception('ajaxUser::update_permissions: $_REQUEST[\'permission\'] is not an array !');
		}
		foreach  ( array_keys($_REQUEST['permission']) as $item ) {
			$right = $_REQUEST['permission'][$item];
			if ($right == 'none') {
				$this->revoke_right($item);
			} elseif ($right == 'read') {
				$this->grant_right( $item, false);
			} elseif ($right == 'write') {
				$this->grant_right( $item, true);
			} else {
				throw new Exception('ajaxUser::update_permissions - invalid permission-value: ' . $item . ' = ' . $right );
			}
		}
	} catch (Exception $e) {
		$this->error_exit($e, 'Update failed.');
	}
} // update_permissions
// ###########################
function revoke_right( $pPermissionId ) {
	try {
		$sql = 'delete from www_grant where fk_user_id = :userId and fk_permission_id = :permissionId';
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':userId',$this->userId);
		$sth->bindParam(':permissionId',$pPermissionId);
		$sth->execute();
		$sth->closeCursor();
	} catch (Exception $e) {
		$this->error_exit($e, 'Update failed.');
	}
} // revoke_right
// ###########################
function grant_right( $pPermissionId, $pCanWrite ) {
	try {
// try update
		$sql = 'update www_grant '
			 . ' set can_update = :can_update, updated_by = :updated_by, updated_dtm = str_to_date(:updated_dtm, \'%Y-%m-%d %H:%i:%s\') '
			 . ' where fk_user_id = :fk_user_id and fk_permission_id = :fk_permission_id ';
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':can_update',$pCanWrite);
		$sth->bindParam(':updated_by',$this->who);
		$sth->bindParam(':updated_dtm',$this->now);
		$sth->bindParam(':fk_user_id',$this->userId);
		$sth->bindParam(':fk_permission_id',$pPermissionId);
		$sth->execute();
		$rowCount = $sth->rowCount();
		$sth->closeCursor();
		if ($rowCount > 0) {
			return;
		}
// No rows updated - try insert
		$sql = 'insert into www_grant (fk_user_id, fk_permission_id, can_update, created_by, created_dtm) '
			 . 'values(:fk_user_id, :fk_permission_id, :can_update, :created_by, :created_dtm) ';
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':fk_user_id',$this->userId);
		$sth->bindParam(':fk_permission_id',$pPermissionId);
		$sth->bindParam(':can_update',$pCanWrite);
		$sth->bindParam(':created_by',$this->who);
		$sth->bindParam(':created_dtm',$this->now);
		$sth->execute();
		$sth->closeCursor();
		return;
	} catch (Exception $e) {
		$unique_violation = '23505';
		$this->error_exit($e, 'Update failed.');
	}
} // grant_right
// ###########################
function delete_user( $pId ) {
	try {
		$sql = 'delete from www_grant '
			 . ' where fk_user_id = :fk_user_id';
		self::$conn->beginTransaction();
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':fk_user_id',$this->userId);
		$sth->execute();
		$sth->closeCursor();
//
		$sql = 'update www_user '
			 . 'set is_enabled = false'
			 . ', deleted_by = :deleted_by'
			 . ', deleted_dtm = str_to_date(:deleted_dtm, \'%Y-%m-%d %H:%i:%s\') '
			 . 'where id = :userid ';
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':deleted_by',$this->who);
		$sth->bindParam(':deleted_dtm',$this->now);
		$sth->bindParam(':userid',$this->userId);
		$sth->execute();
		$rowCount = $sth->rowCount();
		$sth->closeCursor();
//
		self::$conn->commit();
		echo json_encode($this->result);
		return;
//
	} catch (Exception $e) {
		$this->error_exit($e, 'Delete failed.');
	}
} // delete_user
// ###########################
function error_exit( $e, $pMsg ) {
	parent::lerror( 'Request failed - query string: ' . $_SERVER['REQUEST_URI'] );
	parent::lerror( 'ErrorCode: ' . $e->getCode() . ' Msg: ' . $e->getMessage() . "\n");
	parent::lerror( $e->getTraceAsString() . "\n");
	header($_SERVER["SERVER_PROTOCOL"].' 666 ' . $pMsg . ' See the application error log.',true,666);
	try {
		if (self::$conn->inTransaction()) {
			self::$conn->rollback();
			parent::lerror( 'Rollback performed.' );
		}
	} catch (Exception $e) {
		parent::lerror( 'Rollback failed.' );
	}
	exit;
} // error_exit
// ###########################
function process_request( ) {
	$this->now = date("Y-m-d H:i:s");
	$this->mySession = dbSessionHandler::getInstance();
	session_start();
	$this->who = $_SESSION['userid'];
	session_write_close();
	if ($_REQUEST['action'] == 'read' && has_privilege('User_adm', 'read') == false) throw new Exception('Operation not allowed: ' . $_REQUEST['action']);
	if ($_REQUEST['action'] != 'read' && has_privilege('User_adm', 'write') == false) $this->alert_exit('Updating is not allowed.');
	
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	$this->result['status'] = 'ok';
	try {
		$action = $_REQUEST['action'];
		$this->userId = $_REQUEST['id'];
		if ($action == 'read') {
			$this->read_user( $this->userId );
		} elseif ($action == 'create') {
			$this->create_user();
		} elseif ($action == 'delete') {
			$this->delete_user( $this->userId);
		} elseif ($action == 'update') {
			$this->update_user( $this->userId);
		} else {
			throw new Exception('Unknown server action: ' . $_REQUEST['action']);
		}
	} catch (Exception $e) {
		$this->error_exit($e, 'Update failed');
	}
} // process_request

} // ajaxUser
// #########################
// Main
// #########################

$ajax = new ajaxUser();
$ajax->process_request();

?>