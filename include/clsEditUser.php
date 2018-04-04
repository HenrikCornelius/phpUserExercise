<?php
require_once('webPage.php');
require_once('webModal.php');

// ##############################
// editModal
// ##############################
// ##############################
// editModal
// ##############################
class editModal extends webModal {

public function __construct( ) {
	parent::__construct( array('tag' => 'user', 'header' => 'Edit user', 'requiredPrivilege' => 'User_adm') );
} // construct()

public function dialog_contents() {
?>
<form id="userForm" class="form-inline" autocomplete="off">
	<div id="outer" style="width: 100%; height: 100%; overflow-y: auto;">
	<div class="col-sm-8" style="float: left;">
	<input id="action" name="action" type="text" class="hidden" readonly size="8" value="">
	<table id="userPart1" class="horizontal-grid"><tbody>
	<tr>
		<td><label for="id" class="control-label">User id:</label></td>
		<td><input id="id" name="id" type="number" class="form-control" readonly size="8" value=""></td>
	</tr>
	<tr>
		<td><label for="username" class="control-label">User name:</label></td>
		<td><input id="username" name="username" type="text" class="form-control" required size="48" value=""></td>
	</tr>
	<tr>
		<td><label for="password" class="control-label">Password:</label></td>
		<td><input id="password" name="password" type=password class="form-control" size="48" value=""></td>
	</tr>
	<tr>
		<td><label for="first_name" class="control-label">First name:</label></td>
		<td><input id="first_name" name="first_name" type="text" class="form-control" size="48" value=""></td>
	</tr>
	<tr>
		<td><label for="last_name" class="control-label">Last name:</label></td>
		<td><input id="last_name" name="last_name" type="text" class="form-control" size="48" value=""></td>
	</tr>
	<tr>
		<td><label for="email" class="control-label">E-mail:</label></td>
		<td><input id="email" name="email" type="text" class="form-control" size="48" autocomplete="off" value=""></td>
	</tr>
	<tr>
		<td><label for="is_enabled" class="control-label">Can login:</label></td>
		<td>
		<div class="radio-container">
		<input id="is_enabled1" name="is_enabled" type="radio" class="form-check-input" value="1" checked="true"> Yes
		<input id="is_enabled0" name="is_enabled" type="radio" class="form-check-input" value="0"> No
		</div>
		<label for="is_superuser" class="control-label inline-label">Superuser?:</label>
		<div class="radio-container">
		<input id="is_superuser1" name="is_superuser" type="radio" class="form-check-input" value="1"> Yes
		<input id="is_superuser0" name="is_superuser" type="radio" class="form-check-input" value="0" checked="true"> No
		</div>
		</td>
	</tr>
	<tr>
		<td><label for="password_expiry" class="control-label">Expires at:</label></td>
		<td><input id="password_expiry" name="password_expiry" type="text" class="form-control" size="10" value="<?php echo date('Y-m-d', time()+86400*90);?>"></td>
	</tr>
	<tr>
		<td><label for="created_dtm" class="control-label">Created at:</label></td>
		<td>
		<input id="created_dtm" name="created_dtm" type="text" class="form-control" style="width:auto;" disabled size="16" value="">
		<label for="created_username" class="control-label" style="width:auto;">by:</label>
		<input id="created_username" name="created_username" type="text" class="form-control" style="width:auto;" disabled size="20" value="">
		</td>
	</tr>
	<tr>
		<td><label for="updated_dtm" class="control-label">Updated at:</label></td>
		<td>
		<input id="updated_dtm" name="updated_dtm" type="text" class="form-control" style="width:auto;" disabled size="16" value="">
		<label for="updated_username" class="control-label" style="width:auto;">by:</label>
		<input id="updated_username" name="updated_username" type="text" class="form-control" style="width:auto;" disabled size="20" value="">
		</td>
	</tr>
	</tbody></table>
	</div>
	<div class="col-sm-4" style="float: left;">
<!--
** Permissions
-->
		<table id="permissionTable">
		<thead>
		<tr><th>Rights</th><th>Permission</th></tr>
		</thead>
		<tbody>
<?php
		$sql = 'SELECT a.id, a.name, a.description '
			. 'FROM `www_permission` a '
			. 'order by 1 ';
		$sth = self::$conn->prepare( $sql );
		$sth->execute();
		while( $row=$sth->fetch(PDO::FETCH_ASSOC) ) {
			echo '<tr>' . "\n";
			echo '<td><select id="permission' . $row['id'] . '" name="permission[' . $row['id'] . ']" class="form-control permission_control">' . "\n";
			echo '<option value="none" selected>none</option>' . "\n";
			echo '<option value="read">read</option>' . "\n";
			echo '<option value="write">write</option>' . "\n";
			echo '</select></td>' . "\n";
			echo '<td>' . escape($row['description']) . '</td>' . "\n";
			echo '</tr>' . "\n";
		}
		$sth->closeCursor();
		echo '</tbody>' . "\n";
		echo '</table>' . "\n";
?>
	</div>
	<div class="clearfix" style="clear:both"></div>
	</div> <!-- outer -->
</form>
<?php
} // dialog_contents

public function local_styles() {
?>
.modal-dialog {min-width: 710px; }

.horizontal-grid > tbody > tr > td {padding-top: 3px; padding-bottom: 3px; padding-left: 3px; padding-right: 8px; white-space: nowrap !important;}
.horizontal-grid > tbody>  tr > td:nth-child(1) {text-align: right;}
.horizontal-grid > tbody > tr > td:nth-child(1) > label { width: 100%; text-align: right; }

div.row {padding-top: 3px; padding-bottom: 3px;}
#userForm label.control-label {padding-top: 6px; vertical-align: baseline;}
#userForm .inline-label {padding-left: 25px; padding-right: 10px;}
div.radio-container {display: inline; vertical-align: baseline;}
#userForm .radio-container > input[type=radio] {margin-top: 7px; padding-top: 7px;}

#userList > thead > tr > th:nth-child(1), #userList > tbody > tr > td:nth-child(1) {width: 7ch;}
#userList > thead > tr > th:nth-child(2), #userList > tbody > tr > td:nth-child(2) {width: 8ch;}
#userList > thead > tr > th:nth-child(3), #userList > tbody > tr > td:nth-child(3) {width: 20ch;}
.permission_control { padding: 1px 2px;}
#permissionTable {border-spacing: 2px; width: 100%;}
#permissionTable > thead > tr > th, #permissionTable > tbody > tr > td { padding: 2px}
#permissionTable > thead > tr > th:nth-child(1), #permissionTable > tbody > tr > td:nth-child(1) {text-align: center; width: 8ch;}
#permissionTable > thead > tr > th:nth-child(2), #permissionTable > tbody > tr > td:nth-child(2) {text-align: left;}

<?php
} // local_styles


} // editModal
?>
