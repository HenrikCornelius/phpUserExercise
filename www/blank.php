<?php
require_once('webPage.php');
// ##############################
// userMenu
// ##############################
class myPage extends webPage {
public function __construct( ) {
	parent::__construct( array( 'title' => 'Administer users') );
}
} // myPage

$myPage = new myPage();
$myPage->show_menu_page();
?>
