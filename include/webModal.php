<?php
require_once('dbConn.php');
require_once('myFunctions.php');
class webModal extends dbConn {

	public $modalTag = 'any';
	public $modalId;
	public $formId = '';
	public $header = 'Modal header';
	public $bodyClass = 'modal-body';
	public $closeAction;
	public $default_footer = true;
	public $requiredPrivilege = 'public';
	public $canUpdate = true;

public function __construct( $options = array() ) {
	parent::__construct();
	foreach ($options as $key => $value) {
		if ($key == 'tag') {
			$this->modalTag = $value;
		} elseif ($key == 'header') {
			$this->header = $value;
		} elseif ($key == 'formId') {
			$this->formId = $value;
		} elseif ($key == 'requiredPrivilege') {
			$this->requiredPrivilege = $value;
		}
	}
	$this->modalId = $this->modalTag . 'Modal';
	if ($this->formId == '') $this->formId = $this->modalTag . 'Form';
	$this->closeAction = 'hide_modal(\'' . $this->modalTag . '\');';
	$this->canUpdate = has_privilege($this->requiredPrivilege, 'write');
}

public function script() {
}

public function dialog_contents() {
}

public function dialog_footer() {
?>
<div style="display:flex; " >
<div>
	<button type="submit" id="<?php echo $this->modalTag;?>Save" class="btn btn-primary" accesskey="s" value="Save" disabled form="<?php echo $this->formId;?>"><em>S</em>ave</button>
	<button type="button" id="<?php echo $this->modalTag;?>Delete" class="btn btn-primary" accesskey="D" value="Delete" disabled><em>D</em>elete</button>
</div>
	<div id="<?php echo $this->modalTag;?>FormAlert" class="footerAlert alert-success"></div>
<div>
	<button type="button" id="<?php echo $this->modalTag;?>Prev" class="btn btn-default" disabled>&blacktriangleleft;</button>&Tab;
	<button type="button" id="<?php echo $this->modalTag;?>Next" class="btn btn-default" disabled>&blacktriangleright;</button>
</div>
</div>
<?php
} // dialog_footer

public function local_styles() {
} // local_styles

public function show() {
?>
<style>
/*
** Wide modal for the edit form.
*/
@media screen and (min-width: 768px) {
	.modal-dialog {
		width: 70%; /* either % (e.g. 60%) or px (400px) */
	}
}
/*
** Used for the edit form:
*/
button em {
	text-decoration:underline;
	font-style: normal;
}
.footerAlert {
	margin-left: 20px; 
	margin-right: 20px; 
	padding-top: 7px; 
	padding-left: 3px; 
	flex: 100; 
	text-align: left; 
	height: auto; 
	border: 1px solid transparent;
	overflow: hidden;
}
<?php
	$this->local_styles();
?>
</style>
<?php
//
	echo '<!-- The ' . $this->modalId . ' -->' . "\n";
	echo '<div id="' . $this->modalId . '" class="modal fade" tabindex="-1" role="dialog" data-canupdate="' . ($this->canUpdate ? 'true' : 'false') . '">' . "\n";
	echo '<!-- Modal content -->' . "\n";
	echo '<div class="modal-dialog" role="document">' . "\n";
	echo '<div class="modal-content">' . "\n";
	echo '<div class="modal-header">' . "\n";
	echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' . "\n";
	echo '<h4 id="' . $this->modalTag . 'ModalTitle" class="modal-title">' . $this->header . '</h4>' . "\n";
	echo '</div> <!-- modal-header -->' . "\n";
	echo '<div class="' . $this->bodyClass . '">' . "\n";
	$this->dialog_contents();
	echo '</div> <!-- Body -->' . "\n";
	echo '<div class="modal-footer">' . "\n";
	$this->dialog_footer();
	echo '</div> <!-- modal-footer -->' . "\n";
	echo '</div> <!-- modal-content -->' . "\n";
	echo '</div> <!-- modal-dialog -->' . "\n";
	echo '</div> <!-- ' . $this->modalId . ' -->' . "\n";
	echo '';
}

}
?>