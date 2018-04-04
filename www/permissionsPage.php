<?php
require_once('webPage.php');
require_once('clsEditPermission.php');

// ##############################
// This webPage
// ##############################
class myPage extends webPage {

	private $editModal;

public function __construct( ) {
	parent::__construct( array( 'title' => 'Administer permissions', 'requiredPrivilege' => 'User_adm') );
	$this->editModal = new editModal();
}

public function more_header() {
	$versionFix = '?v=' . time();
?>
<script type="text/javascript" src="webModalApp_ES6.js<?php echo $versionFix;?>"></script>
<script type="text/javascript" src="permissionPage.js<?php echo $versionFix;?>"></script>
<?php
} // more_header()

public function local_styles() {
?>
.table > thead > tr {background-color: silver}
#permissionList > thead > tr > th:nth-child(1), #permissionList > tbody > tr > td:nth-child(1) {width: 7ch;}
#permissionList > thead > tr > th:nth-child(2), #permissionList > tbody > tr > td:nth-child(2) {width: 32ch; white-space: nowrap; overflow: hidden;}
#permissionList > thead > tr > th:nth-child(3), #permissionList > tbody > tr > td:nth-child(3) {white-space: nowrap; overflow: hidden;}
#permissionList > thead > tr > th:nth-child(4), #permissionList > tbody > tr > td:nth-child(4) {white-space: nowrap; overflow: hidden;}
#permissionList > thead > tr > th:nth-child(5), #permissionList > tbody > tr > td:nth-child(5) {width: 19ch; white-space: nowrap; overflow: hidden;}
<?php
	$this->editModal->local_styles();
} // local_styles

public function show_contents() {
// assign default filter values
	$arr = array('filter_name', 'filter_description');
	foreach ($arr as &$field) {
		if (!isset($_REQUEST[$field])) {
			$_REQUEST[$field] = '%';
		}
		if ($_REQUEST[$field] == '') {
			$_REQUEST[$field] = '%';
		}
	}
// The filter form
$btnNewData = '';
?>
<div class="flex-item">
<div class="" style="float: left; margin-left: 8px; margin-right: 8px; width: 25ch; height: 100%; overflow-y: auto;">
<h4>Filter</H4>
<form id="filterForm" method="post" autocomplete="off">
	<input id="filter_action" name="action" type="text" class="hidden" readonly size="8" value="list">
	<div class="form-group">
		<label for="filter_name" class="control-label">N<u>a</u>me:</label>
		<input id="filter_name" name="filter_name" type="text" class="form-control" autofocus size="20" accesskey="a" value="<?php echo escape_request('filter_name');?>">
	</div>
	<div class="form-group">
		<label for="filter_description" class="control-label">Description:</label>
		<input id="filter_description" name="filter_description" type="text" class="form-control" size="20" value="<?php echo escape_request('filter_description');?>">
	</div>
</form>
	<button type="submit" form="filterForm" class="btn btn-primary" accesskey="l" value="List"><em>L</em>ist</button>
	<button type="button" id="btnNew" class="btn btn-primary" onclick="thatApp.actionNew();" accesskey="N" value="New"<?php echo ($this->editModal->canUpdate ? '' : ' disabled');?>><em>N</em>ew</button>
</div>
<?php
// Create SQL filter
	$filter = '';
	$filter .= string_filter('filter_name','name');
	$filter .= string_filter('filter_description','description');
// The permissionList
	echo '<div class="fullheight" style="float: left; width: calc(100% - 25ch - 16px); height: 100%; overflow-y: scroll; padding-left: 8px; padding-right: 8px;">' . "\n";
	$sql = 'select id, name, description, notes, created_dtm '
		 . 'from www_permission '
		 . 'where deleted_dtm is null or deleted_dtm > current_timestamp ' . $filter
		 . ' order by 1';
	$sth = self::$conn->prepare( $sql );
	$sth->execute();
	echo '<h4>Permissions</H4>' . "\n";
	echo '<table id="permissionList" class="table table-bordered table-condensed table-striped" style="width: 100%;">' . "\n";
	echo '<thead>' . "\n";
	echo '<tr><th>Id</th><th>Name</th><th>Description</th><th>Notes</th><th>Created time</th></tr>' . "\n";
	echo '</thead>' . "\n";
	echo '<tbody>' . "\n";
	while( $row=$sth->fetch(PDO::FETCH_ASSOC) ) {
		echo '<tr>';
		echo '<td>' . escape($row['id']) . '</td>';
		echo '<td>' . escape($row['name']) . '</td>';
		echo '<td>' . escape($row['description']) . '</td>';
		echo '<td>' . escape($row['notes']) . '</td>';
		echo '<td>' . escape($row['created_dtm']) . '</td>';
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
