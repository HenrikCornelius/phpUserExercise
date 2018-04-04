<?php
require_once('webPage.php');
// ##############################
// userMenu
// ##############################
class myPage extends webPage {
public function __construct( ) {
	parent::__construct( array( 'title' => 'Administer users', 'requiredPrivilege' => 'public') );
}

public function show_contents() {
?>
<iframe name="myFrame" src="phpInfo.php" class="fullsize" style="width:100vw; height: calc(100vh - 52px); border: none;"></iframe> 
<?php
} // show_contents
} // myPage

$myPage = new myPage();
$myPage->show_menu_page();
?>
