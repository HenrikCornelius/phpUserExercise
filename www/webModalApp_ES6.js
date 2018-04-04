// 
// webModalApp_ES6: Uses "class" statement, which is new in JavaScript (EcmaScript6 - 2015).
// https://www.sitepoint.com/the-es6-conundrum/
// This does not work in Internet Explorer.
//
class modalAppClass {
//
constructor( pOptns = {} ) {
	var options = {tableId: '', modalTag: '', formId: ''
			, createdMsg: 'Row created'
			, updatedMsg: 'Row updated'
			, deletedMsg: 'Row deleted'
			, entityName: 'row'
			, keyColumns: []
			, tableColumns: []
			, ajaxUrl: ''
			, canUpdate: true
			};
	Object.assign(options, pOptns);
	this.options = options;
	this.tableSelector = '#' + options.tableId;
	this.tableBodySelector = this.tableSelector + ' tbody';
	this.modalSelector = '#' + options.modalTag + 'Modal';
	this.formSelector = '#' + options.formId;
	this.formAlert = options.modalTag + 'FormAlert';
	this.btnSave = options.modalTag + 'Save';
	this.btnDelete = options.modalTag + 'Delete';
	this.btnPrev = options.modalTag + 'Prev';
	this.btnNext = options.modalTag + 'Next';
	this.canUpdate = true;
	this.gvCurrentRow = null;
	
	document.addEventListener("DOMContentLoaded", this.setEvents.bind(this) );
}

setEvents() {
	var Apl = this;
	Apl.canUpdate = $(Apl.modalSelector).data('canupdate');
//
// Note: "btnNew" event is set in html with "onclick=thatApp.ActionNew();"
//	document.getElementById('btnNew').addEventListener('click', Apl.actionNew.bind(Apl));
	document.getElementById(Apl.options.formId).addEventListener('submit', Apl.actionSave.bind(Apl));
	document.getElementById(Apl.btnDelete).addEventListener('click', Apl.actionDelete.bind(Apl));
	document.getElementById(Apl.btnNext).addEventListener('click', Apl.moveNext.bind(Apl));
	document.getElementById(Apl.btnPrev).addEventListener('click', Apl.movePrev.bind(Apl));
	$(Apl.tableBodySelector).on('click', 'tr', function(pEvent) {
		document.getElementById(Apl.options.formId).reset();
		Apl.actionRead( this );
	});
	$(Apl.modalSelector).on('hidden.bs.modal', function () {
		Apl.cancelEdit();
		$('#' + this.btnSave).prop('disabled',true);
		$('#' + this.btnDelete).prop('disabled',true);
		$('#' + this.btnPrev).prop('disabled',true);
		$('#' + this.btnNext).prop('disabled',true);
	});
	$(Apl.modalSelector).on('shown.bs.modal', Apl.enable_input.bind(Apl));
}

moveNext() {
	if (this.gvCurrentRow) {
		var theTable = $(this.tableBodySelector).get(0);
		var rowIndex = this.gvCurrentRow.rowIndex;
		if (rowIndex < theTable.rows.length) {
			this.actionRead( theTable.rows[rowIndex] );
		} else {
			this.setFormMessage('warning','This is the last row in the table.');
		}
	}
} // moveNext

movePrev() {
	if (this.gvCurrentRow) {
		var theTable = $(this.tableBodySelector).get(0);
		var rowIndex = this.gvCurrentRow.rowIndex - 1;
		if (rowIndex > 0) {
			this.actionRead( theTable.rows[rowIndex - 1] );
		} else {
			this.setFormMessage('warning','This is the first row in the table.');
		}
	}
}

cancelEdit() {
	if (this.gvCurrentRow) {
		this.gvCurrentRow.style.backgroundColor = "";
	}
	this.gvCurrentRow = null;
}

show_error( theData ) {
	if (typeof theData.message !== 'string' || theData.message.length < 1) return;
	var msgField = '';
	if (typeof theData.field === 'string' && theData.field.length > 0) {
		$('#' + theData.field).focus();
		$('#' + theData.field).parent().addClass('has-error'); /* BS3 */
		$('#' + theData.field).addClass('is-invalid'); /* BS4 */
		msgField = $('#' + theData.field).data('errormsg') || '';
	}
	if (msgField) {
		$('#' + msgField).text(theData.message);
		$('#' + msgField).removeClass('hidden');
	} else {
		this.setFormMessage('danger', theData.message);
	}
}

clear_errors() {
	this.clearFormMessage();
	$('.is-invalid,.has-error').removeClass('is-invalid has-error');
	$('.error-message').each(function(idx){
		$(this).addClass('hidden');
	});
}

setFormMessage( pSeverity, pMessage) {
	var Apl = this;
	$('#' + Apl.formAlert).removeClass('alert-success alert-info alert-warning alert-danger');
	$('#' + Apl.formAlert).text( pMessage );
	switch (pSeverity) {
		case 'success': $('#' + Apl.formAlert).addClass('alert-success');
			break;
		case 'info': $('#' + Apl.formAlert).addClass('alert-info');
			break;
		case 'warning': $('#' + Apl.formAlert).addClass('alert-warning');
			break;
		case 'danger': $('#' + Apl.formAlert).addClass('alert-danger');
			break;
		default: $('#' + Apl.formAlert).addClass('alert-info');
			break;
	}
	var msgno = $('#' + Apl.formAlert).data('msgno') || 0;
	msgno++;
	$('#' + Apl.formAlert).data('msgno', msgno);
	window.setTimeout( Apl.clearFormMessage.bind(Apl,msgno), 10000);
}

clearFormMessage( pNbr ) {
	var vNbr = pNbr || $('#' + this.formAlert).data('msgno') || 0;
	if ($('#' + this.formAlert).data('msgno') === vNbr) {
		$('#' + this.formAlert).removeClass('alert-success alert-info alert-warning alert-danger');
		$('#' + this.formAlert).text('');
	}
}

process_result( pResult ) {
	this.clear_errors();
	var theData = {};
	try {
		theData = JSON.parse(pResult);
	} catch(err) {
		alert("Error in server response: \n" + err + "\n" + pResult);
		return;
	}
	if (theData.status == 'error') {
		this.show_error(theData);
		return;
	}
// Copy the data to the form.
	this.data2form(theData);
// Copy the data to the table.
	this.data2table(theData);
	$(this.modalSelector + ' .modal-title').text('Edit ' + this.options.entityName);
	$('#action').val('update');
	
	this.enable_input();
	$(this.modalSelector).modal('show');
	this.gvCurrentRow.style.backgroundColor = "#FFE4C4";
}

data2form(theData) {
//	var form = document.getElementById(this.options.formId);
//	for (var I = 0; I < form.elements.length; I++) {
//		alert( 'Control: ' + form.elements[I].name);
//	}
}

data2table(theData) {
	var Apl = this;
	if ($('#action').val() == 'create') {
		var trString = '';
		this.options.tableColumns.forEach( function(tabCol, tabPos) {
			trString += '<td>' + theData.data[tabCol] + '</td>';
		});
		var newRow = $('<tr>' + trString + '</tr>');
		$(this.tableBodySelector).append(newRow);
		this.gvCurrentRow = $(this.tableBodySelector + ' tr:last').get(0);
		this.setFormMessage('success',this.options.createdMsg);
	} else {
		this.options.tableColumns.forEach( function(tabCol, tabPos) {
			Apl.gvCurrentRow.cells[tabPos].innerText = theData.data[tabCol];
		});
		if ($('#action').val() == 'update') this.setFormMessage('success',this.options.updatedMsg);
	}
}

enable_input() {
	if (this.canUpdate) {
		$('#' + this.btnSave).prop('disabled',false);
		if (!this.gvCurrentRow) {
			$('#' + this.btnDelete).prop('disabled',true);
		} else {
			$('#' + this.btnDelete).prop('disabled',false);
		}
	} else {
		$(this.formSelector + ' :input').each( function(elem) {
			//$(this).prop('readonly', true); // "radio" cannot be readonly.
			$(this).attr('disabled', true);
		});
		$('#' + this.btnSave).prop('disabled',true);
		$('#' + this.btnDelete).prop('disabled',true);
	}
	if (!this.gvCurrentRow) {
		$('#' + this.btnPrev).prop('disabled',true);
		$('#' + this.btnNext).prop('disabled',true);
	} else {
		$('#' + this.btnPrev).prop('disabled',false);
		$('#' + this.btnNext).prop('disabled',false);
	}
}

actionNew() {
	this.clear_errors();
	this.cancelEdit();
	this.gvCurrentRow = null;
	document.getElementById(this.options.formId).reset();
	$('#action').val('create');
	$(this.modalSelector + ' .modal-title').text('New ' + this.options.entityName);
	$(this.modalSelector).modal('show');
	this.enable_input();
}

actionRead( pRow ) {
	var Apl = this;
	this.cancelEdit();
	this.gvCurrentRow = pRow;

// Get primary key columns from table.
	var idString = {'action': 'read'};
	Apl.options.keyColumns.forEach( function(keyCol) {
		Apl.options.tableColumns.forEach( function(tabCol, tabPos) {
			if (keyCol == tabCol) {
				idString[keyCol] = Apl.gvCurrentRow.cells[tabPos].innerText;
			}
		});
	});

	$('#action').val('read');
	$.ajax(
		{ url: Apl.options.ajaxUrl
		, async: false
		, cache: false
		, method: 'POST'
		, data: idString
		, dataType: 'text'
		, success: function( data, pStatus, jqXHR ) {
			Apl.process_result( data );
			}
		, error: function(jqXHR, pStatus, pError) {
			Apl.setFormMessage('danger',pError);
			alert(pError);}
		}
	);
};

actionSave(event) {
	var Apl = this;
	event.preventDefault();
	this.clear_errors();
	if (event.target.checkValidity() != true) return;
	
	$.ajax(
		{ url: Apl.options.ajaxUrl
		, async: false
		, cache: false
		, method: 'POST'
		, data: $(this.formSelector).serialize()
		, dataType: 'text'
		, success: function( data, pStatus, jqXHR ) {
			Apl.process_result( data );
			}
		, error: function(jqXHR, pStatus, pError) {
			Apl.setFormMessage('danger',pError);
			alert( pError)}
		}
	);
}

actionDelete() {
	var Apl = this;
	
// Get primary key columns from the FORM.
	var idString = {'action': 'delete'};
	Apl.options.keyColumns.forEach( function(keyCol) {
		idString[keyCol] = $('#' + keyCol).val();
		if ($('#' + keyCol).val() == '') return;
	});
	
	
	$.ajax(
		{ url: Apl.options.ajaxUrl
		, async: false
		, cache: false
		, method: 'POST'
		, data: idString
		, dataType: 'text'
		, success: function( data, pStatus, jqXHR ) {
			var theData = JSON.parse(data);
			if (theData.status == 'error') {
				Apl.show_error(theData);
				return;
			}
			if (theData.status == 'ok') {
				Apl.setFormMessage('success',Apl.options.deletedMsg);
				alert(Apl.options.deletedMsg);
			}
			var theTable = $(Apl.tableBodySelector).get(0);
			var rowIndex = Apl.gvCurrentRow.rowIndex;
			$(Apl.gvCurrentRow).remove();
			if (rowIndex < theTable.rows.length) {
				Apl.actionRead( theTable.rows[rowIndex - 1] );
			} else if (theTable.rows.length > 0) {
				Apl.actionRead( theTable.rows[theTable.rows.length - 1] );
			} else {
				$(Apl.modalSelector).hide();
			}
		}
		, error: function(jqXHR, pStatus, pError) {
			Apl.setFormMessage('danger',pError);
			alert(pError);
		}
	});
}

} // modalAppClass
