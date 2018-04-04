<?php
require_once('webPage.php');
require_once('clsEditUser.php');

// ##############################
// This webPage
// ##############################
class myPage extends webPage {

	private $editModal;

public function __construct( ) {
	parent::__construct( array( 'title' => 'Administer users', 'requiredPrivilege' => 'User_adm') );
	$this->editModal = new editModal();
}

public function more_header() {
	$versionFix = '?v=' . time();
?>
<script type="text/javascript" src="webModalApp_ES6.js<?php echo $versionFix;?>"></script>
<script type="text/javascript" src="usersPage.js<?php echo $versionFix;?>"></script>
<?php
// <script type="text/javascript" src="permissionPage.js<?php echo $versionFix;?.>"></script>
} // more_header()

public function local_script() {
?>
$(document).ready(function(){
	$('#password_expiry').datepicker({dateFormat: 'yy-mm-dd', defaultDate: +90});
});
<?php
} // local_script()

public function local_styles() {
?>
.table > thead > tr {background-color: silver}
<?php
} // local_styles

public function show_contents() {
// assign default filter values
	$arr = array('filter_username', 'filter_firstname', 'filter_lastname', 'filter_email', 'filter_status');
	foreach ($arr as &$field) {
		if (!isset($_REQUEST[$field])) {
			$_REQUEST[$field] = '%';
		}
		if ($_REQUEST[$field] == '') {
			$_REQUEST[$field] = '%';
		}
	}
// The filter form
?>
<div class="flex-item">
<div class="" style="float: left; margin-left: 8px; margin-right: 8px; width: 25ch; height: 100%; overflow-y: auto;">
<h4>Filter</H4>
<form id="filterForm" method="post" autocomplete="off">
	<input id="filter_action" name="action" type="text" class="hidden" readonly size="8" value="list">
	<div class="form-group">
		<label for="filter_username" class="control-label"><u>U</u>ser name:</label>
		<input id="filter_username" name="filter_username" type="text" class="form-control" autofocus size="20" accesskey="u" value="<?php echo escape_request('filter_username');?>">
	</div>
	<div class="form-group">
		<label for="filter_firstname" class="control-label">First name:</label>
		<input id="filter_firstname" name="filter_firstname" type="text" class="form-control" size="20" value="<?php echo escape_request('filter_firstname');?>">
	</div>
	<div class="form-group">
		<label for="filter_lastname" class="control-label">Last name:</label>
		<input id="filter_lastname" name="filter_lastname" type="text" class="form-control" size="20" value="<?php echo escape_request('filter_lastname');?>">
	</div>
	<div class="form-group">
		<label for="filter_email" class="control-label">E-mail:</label>
		<input id="filter_email" name="filter_email" type="text" class="form-control" size="20" value="<?php echo escape_request('filter_email');?>">
	</div>
	<div class="form-group">
		<label for="filter_status" class="control-label">Account status:</label>
		<select id="filter_status" name="filter_status" class="form-control">
			<option value="%"<?php echo is_selected('filter_status','%');?>>Any</option>
			<option value="open"<?php echo is_selected('filter_status','open');?>>Open</option>
			<option value="locked"<?php echo is_selected('filter_status','locked');?>>Locked</option>
			<option value="expired"<?php echo is_selected('filter_status','expired');?>>Expired</option>' . "\n";
		</select>
	</div>
	<button type="submit" class="btn btn-primary" accesskey="l" value="List"><em>L</em>ist</button>
	<button type="button" id="btnNew" class="btn btn-primary" onclick="thatApp.actionNew();" accesskey="N" value="New"><em>N</em>ew</button>
</form>
</div>
<?php
// Create SQL filter
	$filter = '';
	$filter .= string_filter('filter_username','username');
	$filter .= string_filter('filter_firstname','first_name');
	$filter .= string_filter('filter_lastname','last_name');
	$filter .= string_filter('filter_email','email');
	if ($_REQUEST['filter_status'] == 'open') {
		$filter .= 'and is_enabled = true and coalesce(password_expiry,current_timestamp) > current_timestamp ';
	} elseif ($_REQUEST['filter_status'] == 'locked') {
		$filter .= 'and is_enabled = false ';
	} elseif ($_REQUEST['filter_status'] == 'expired') {
		$filter .= 'and coalesce(password_expiry,current_timestamp) < current_timestamp ';
	}
// The userList
	echo '<div class="fullheight" style="float: left; width: calc(100% - 25ch - 16px); height: 100%; overflow-y: scroll; padding-left: 8px; padding-right: 8px;">' . "\n";
	$sql = 'select id, username, first_name, last_name, email '
		 . ', case '
		 . 'when is_enabled = false then \'Locked\' '
		 . 'when coalesce(password_expiry,current_timestamp) < current_timestamp then \'Expired\' '
		 . 'else \'Open\' '
		 . 'end as account_status '
		 . 'from www_user '
		 . 'where deleted_dtm is null or deleted_dtm > current_timestamp ' . $filter
		 . ' order by 1';
	$sth = self::$conn->prepare( $sql );
	$sth->execute();
	echo '<h4>Users</H4>' . "\n";
	echo '<table id="userList" class="table table-bordered table-condensed table-striped" style="width: 100%;">' . "\n";
	echo '<thead>' . "\n";
	echo '<tr><th>Id</th><th>Status</th><th>Username</th><th>First name</th><th>Last name</th><th>Email</th></tr>' . "\n";
	echo '</thead>' . "\n";
	echo '<tbody>' . "\n";
	while( $row=$sth->fetch(PDO::FETCH_ASSOC) ) {
		echo '<tr>';
		echo '<td>' . escape($row['id']) . '</td>';
		echo '<td>' . escape($row['account_status']) . '</td>';
		echo '<td>' . escape($row['username']) . '</td>';
		echo '<td>' . escape($row['first_name']) . '</td>';
		echo '<td>' . escape($row['last_name']) . '</td>';
		echo '<td>' . escape($row['email']) . '</td>';
		echo '</tr>' . "\n";
	}
	$sth->closeCursor();
	echo '</tbody>' . "\n";
	echo '</table>' . "\n";
	echo '</div>' . "\n";
	echo '</div>' . "\n";
} // show_contents

public function show_modal_contents() {
	$this->editModal->show();
} // show_modal_contents

} // myPage

$myPage = new myPage();
$myPage->show_menu_page();
?>
