//import 'webModalApp_ES6.js';

class usersAppClass extends modalAppClass {

constructor() {
	super({tableId: 'userList', modalTag: 'user', formId: 'userForm'
			, createdMsg: 'User created'
			, updatedMsg: 'User updated'
			, deletedMsg: 'The user has been deleted'
			, entityName: 'user'
			, keyColumns: ['id']
			, tableColumns: ['id', 'account_status', 'username', 'first_name', 'last_name', 'email']
			, ajaxUrl: 'ajaxUser.php'
			});
	
}

data2form(theData) {
	$('#id').val( theData.data.id);
	$('#username').val( theData.data.username);
	$('#password').val( theData.data.password);
	$('#first_name').val( theData.data.first_name);
	$('#last_name').val( theData.data.last_name);
	$('#email').val( theData.data.email);
	if (theData.data.is_enabled == 1) {
		$('#is_enabled1').click();
	} else {
		$('#is_enabled0').click();
	}
	if (theData.data.is_superuser == 1) {
		$('#is_superuser1').click();
	} else {
		$('#is_superuser0').click();
	}
	$('#password_expiry').val( theData.data.password_expiry);
	$('#created_dtm').val( theData.data.created_dtm);
	$('#created_username').val( theData.data.created_username);
	$('#updated_dtm').val( theData.data.updated_dtm);
	$('#updated_username').val( theData.data.updated_username);
/* permissions */
	var fName;
	for (fName in theData.data.permission) {
		$('#' + fName).val( theData.data.permission[fName] );
	}
}

enable_input() {
	super.enable_input();
	if (this.canUpdate) {
		if (!this.gvCurrentRow) {
			$('#username').prop('readonly',false);
			$('#username').focus();
		} else {
			$('#username').prop('readonly',true);
			$('#first_name').focus();
		}
	}
} // enable_input

} // usersAppClass
// #############################################

var thatApp = new usersAppClass();
var x=1;
