//import modalAppClass from './webModalApp_ES6.js';

class permissionAppClass extends modalAppClass {

constructor() {
	super({tableId: 'permissionList', modalTag: 'permission', formId: 'permissionForm'
			, createdMsg: 'Permission created'
			, updatedMsg: 'Permission updated'
			, deletedMsg: 'The permission has been deleted'
			, entityName: 'permission'
			, keyColumns: ['id']
			, tableColumns: ['id', 'name', 'description', 'notes', 'created_dtm']
			, ajaxUrl: 'ajaxPermission.php'
			});
	
}

data2form(theData) {
	$('#id').val( theData.data.id);
	$('#name').val( theData.data.name);
	$('#description').val( theData.data.description);
	$('#notes').val( theData.data.notes);
	$('#created_dtm').val( theData.data.created_dtm);
	$('#created_name').val( theData.data.created_name);
	$('#updated_dtm').val( theData.data.updated_dtm);
	$('#updated_name').val( theData.data.updated_name);
	$('#description').focus();
	$('#name').prop('readonly',true);
}

enable_input() {
	super.enable_input();
	if (this.canUpdate) {
		if (!this.gvCurrentRow) {
			$('#name').prop('readonly',false);
			$('#name').focus();
		} else {
			$('#name').prop('readonly',true);
			$('#description').focus();
		}
	}
} // enable_input

} // permissionAppClass
// #############################################

var thatApp = new permissionAppClass();
var x=1;
