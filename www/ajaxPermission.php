<?php
require_once('dbConn.php');
require_once('myFunctions.php');
require_once('dbSessionHandler.php');
// #########################
// User
// #########################
class ajaxPermission extends dbConn {
	public $id;
	public $userRow;
	public $result;
	public $who = '1';
	public $now;
	protected $mySession;
//
	private $keys = array("id");
	private $attrib = array("name", "description", "notes");
	private $ins_hist = array("created_by", "created_dtm");
	private $upd_hist = array("updated_by", "updated_dtm");
// ####################################
function do_read( $pId ) {
	try {
		$sql = 'select a.*, crea.username created_name, upda.username updated_name '
			 . 'from www_permission a '
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
		echo json_encode($this->result);
	} catch (Exception $e) {
		$this->error_exit($e, 'Retrieval of the information failed.');
	}
} // read
// ####################################
function validate_form( ) {
	if (strlen($_REQUEST['name']) < 3) {
		$this->result['status'] = 'error';
		$this->result['field'] = 'name';
		$this->result['message'] = '"name" must be at least 3 characters';
		echo json_encode($this->result);
		exit;
	}
// Check for duplicate name
	try {
		$sql = 'select max(id) as id '
			 . 'from www_permission  '
			 . 'where name = :name '
			 . 'and (deleted_dtm is null or deleted_dtm > current_timestamp) ';
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':name',$_REQUEST['name']);
		$sth->execute();
		$foundId = -7;
		while( $row=$sth->fetch(PDO::FETCH_ASSOC) ) {
			$foundId = $row['id'];
		}
		$sth->closeCursor();
		if ($foundId > 0 && $foundId != $this->id) {
			$this->result['status'] = 'error';
			$this->result['field'] = 'name';
			$this->result['message'] = 'Name "' . $_REQUEST['name'] . '" is in use.';
			echo json_encode($this->result);
			exit;
		}
	} catch (Exception $e) {
		$this->error_exit($e, 'Retrieval of the information failed.');
	}
	return true;
} // validate_form
// ####################################
function do_create( ) {
	if ($this->validate_form() == false) {
		return;
	}
	try {
		$sql = 'insert into '
			 . 'www_permission (name, description, notes, created_by, created_dtm) '
			 . 'values (:name, :description, :notes, :created_by, str_to_date(:created_dtm,\'%Y-%m-%d %H:%i:%s\')) ';
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':name',$_REQUEST['name']);
		$sth->bindParam(':description',$_REQUEST['description']);
		$sth->bindParam(':notes',$_REQUEST['notes']);
		$sth->bindParam(':created_by',$this->who);
		$sth->bindParam(':created_dtm',$this->now);
		$sth->execute();
		$this->id = self::$conn->lastInsertId();
		$rowCount = $sth->rowCount();
		$sth->closeCursor();
	} catch (Exception $e) {
		$this->error_exit($e, 'Creation failed.');
	}
	$this->do_read($this->id);
} // do_create()
// ####################################
function do_update( $pId ) {
	if ($this->validate_form() == false) {
		return;
	}
	$cols = array_merge($this->attrib);
	try {
		$sql = 'update www_permission '
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
	} catch (Exception $e) {
		$this->error_exit($e, 'Update failed');
	}
	$this->do_read($pId);
} // update
// ###########################
function do_delete( $pId ) {
// Check for duplicate name
	try {
		$sql = 'select count(1) as nbr '
			 . 'from www_grant  '
			 . 'where fk_permission_id = :id ';
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':id',$this->id);
		$sth->execute();
		$nbr = -7;
		while( $row=$sth->fetch(PDO::FETCH_ASSOC) ) {
			$nbr = $row['nbr'];
		}
		$sth->closeCursor();
		if ($nbr > 0) {
			$this->alert_exit( 'This permission cannot be deleted - it is in use.' );
			exit;
		}
	} catch (Exception $e) {
		$this->error_exit($e, 'Retrieval of the information failed.');
	}
// delete the row
	try {
		$sql = 'delete from www_permission '
			 . 'where id = :id ';
		$sth = self::$conn->prepare( $sql );
		$sth->bindParam(':id',$this->id);
		$sth->execute();
		$rowCount = $sth->rowCount();
		$sth->closeCursor();
//
		echo json_encode($this->result);
		return;
//
	} catch (Exception $e) {
		$this->error_exit($e, 'Delete failed.');
	}
} // do_delete
// ###########################
function alert_exit( $pMsg ) {
	header($_SERVER["SERVER_PROTOCOL"].' 666 ' . $pMsg,true,666);
	exit;
} // alert_exit
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
	$this->result['action'] = $_REQUEST['action'];
	$this->result['status'] = 'ok';
	try {
		$action = $_REQUEST['action'];
		$_REQUEST['id'] = $_REQUEST['id'];
		$this->id = $_REQUEST['id'];
		if ($action == 'read') {
			$this->do_read( $this->id );
		} elseif ($action == 'create') {
			$this->do_create();
		} elseif ($action == 'delete') {
			$this->do_delete( $this->id);
		} elseif ($action == 'update') {
			$this->do_update( $this->id);
		} else {
			throw new Exception('Unknown server action: ' . $_REQUEST['action']);
		}
	} catch (Exception $e) {
		$this->error_exit($e, 'Update failed');
	}
} // process_request

} // ajaxPermission
// #########################
// Main
// #########################

$ajax = new ajaxPermission();
$ajax->process_request();

?>