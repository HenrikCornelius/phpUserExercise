<?php
function escape($string) {
	return htmlentities($string, ENT_QUOTES, 'ISO-8859-1');
}
function escape_request($string) {
	return htmlentities($_REQUEST[$string], ENT_QUOTES, 'ISO-8859-1');
}
function string_filter($pField,$pColumn) {
	if ($_REQUEST[$pField] == '' || $_REQUEST[$pField] == '%') {
		return '';
	} elseif($_REQUEST[$pField] == ' ') {
		return 'and coalesce(' . $pColumn . ',\'\') = \'\' ';
	} else {
		return 'and ' . $pColumn . ' like \'' . $_REQUEST[$pField] . '\' ';
	}
}
function is_selected($pField, $pOption) {
	if ($_REQUEST[$pField] == $pOption) {
		return ' selected ';
	} else {
		return '';
	}
}
function has_privilege( $privName, $privMode = 'read' ) {
	if (isset($_SESSION['userid']) == false || empty($_SESSION['userid']) == true) return false;
	if (isset($_SESSION['is_superuser']) && $_SESSION['is_superuser'] == true) return true;
	if ($privName == 'public') return true;
	if ( ! isset($_SESSION['privileges'][$privName]) ) return false;
	if ( $_SESSION['privileges'][$privName] == $privMode || $_SESSION['privileges'][$privName] == 'write' ) return true;
	return false;
}
?>