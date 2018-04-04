<?php
$myPage = pathinfo(basename($_SERVER['SCRIPT_NAME']), PATHINFO_FILENAME);
?>

<nav class="navbar navbar-inverse" style="margin: 0px;">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#">UserMenu</a>
    </div>
    <ul class="nav navbar-nav">
<?php if ( has_privilege('User_adm') ) { ?>
      <li><a href="usersPage.php">Users</a></li>
      <li><a href="permissionsPage.php">Permissions</a></li>
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Administration
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="usersPage.php">Users</a></li>
          <li><a href="permissionsPage.php">Permissions</a></li>
        </ul>
      </li>
<?php } ?>
<?php if ( has_privilege('public') ) { ?>
      <li><a href="phpInfoFrame.php">phpInfo</a></li>
<?php } ?>
    </ul>
<?php
	if ( isset($_SESSION['loggedin']) && empty($_SESSION['loggedin']) == false && $_SESSION['loggedin'] == true) {
		echo '<ul class="nav navbar-nav navbar-right">';
		echo '<li><a href="#"><span class="glyphicon glyphicon-user"></span> ' . $_SESSION['userCaption'] . '</a></li>';
		echo '<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>';
		echo '</ul>';
	} else {
		echo '<ul class="nav navbar-nav navbar-right">';
		echo '<li><a href="signup.php"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>';
		echo '<li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>';
		echo '<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>';
		echo '</ul>';
	}
?>
  </div>
</nav>
