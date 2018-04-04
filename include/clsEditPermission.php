<?php
require_once('webPage.php');
require_once('webModal.php');

// ##############################
// editModal
// ##############################
class editModal extends webModal {

public function __construct( ) {
	parent::__construct( array('tag' => 'permission', 'header' => 'Edit permission', 'requiredPrivilege' => 'User_adm') );
} // construct()

public function dialog_contents() {
?>
<form id="permissionForm" class="form-inline" autocomplete="off">
	<div id="outer" style="width: 100%; height: 100%; overflow-y: auto;">
	<input id="action" name="action" type="text" class="hidden" readonly size="8" value="">
	<table id="permissionGrid" class="horizontal-grid"><tbody>
	<tr>
		<td><label for="id" class="control-label">Id:</label></td>
		<td><input id="id" name="id" type="number" class="form-control" readonly size="8" value=""></td>
	</tr>
	<tr>
		<td><label for="name" class="control-label">Name:</label></td>
		<td><input id="name" name="name" type="text" class="form-control" required size="48" value="" data-errormsg="nameError"><br>
		<span id="nameError" class="help-block hidden error-message"></span></td>
	</tr>
	<tr>
		<td><label for="description" class="control-label">Description:</label></td>
		<td><input id="description" name="description" type="text" class="form-control" required size="80" value=""></td>
	</tr>
	<tr>
		<td><label for="notes" class="control-label">Notes:</label></td>
		<td><textarea id="notes" class="form-control" name="notes" rows="4" style="width: 100%; white-space: pre-wrap;"></textarea></td>
	</tr>
	<tr>
		<td><label for="created_dtm" class="control-label">Created at:</label></td>
		<td>
		<input id="created_dtm" name="created_dtm" type="text" class="form-control" style="width:auto;" disabled size="16" value="">
		<label for="created_name" class="control-label" style="width:auto;">by:</label>
		<input id="created_name" name="created_name" type="text" class="form-control" style="width:auto;" disabled size="20" value="">
		</td>
	</tr>
	<tr>
		<td><label for="updated_dtm" class="control-label">Updated at:</label></td>
		<td>
		<input id="updated_dtm" name="updated_dtm" type="text" class="form-control" style="width:auto;" disabled size="16" value="">
		<label for="updated_name" class="control-label" style="width:auto;">by:</label>
		<input id="updated_name" name="updated_name" type="text" class="form-control" style="width:auto;" disabled size="20" value="">
		</td>
	</tr>
	</tbody></table>
	</div> <!-- outer -->
</form>
<?php
} // dialog_contents

public function local_styles() {
?>
.modal-dialog {min-width: 500px; }

.horizontal-grid > tbody > tr > td {padding-top: 3px; padding-bottom: 3px; padding-left: 3px; padding-right: 8px; white-space: nowrap !important;}
.horizontal-grid > tbody > tr > td:nth-child(1) {text-align: right; vertical-align: top; }
.horizontal-grid > tbody > tr > td:nth-child(1) > label { width: 100%; text-align: right; }

div.row {padding-top: 3px; padding-bottom: 3px;}
#permissionForm label.control-label {padding-top: 6px; vertical-align: baseline;}
#permissionForm .inline-label {padding-left: 25px; padding-right: 10px;}

<?php
} // local_styles


} // editModal
?>
