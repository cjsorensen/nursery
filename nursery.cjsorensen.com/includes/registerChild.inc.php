<?php if (isset ($_POST['register'])){
	require_once('connection.inc.php');
  //get unix timestamp and convert to MySQL date
  $date = date("Y-m-d", strtotime($_POST['birthday']));
  // initialize flag
  $OK = false;
  // create database connection
  $conn = dbConnect('write');
  // initialize prepared statement
  $stmt = $conn->stmt_init();
  // create SQL
  $sql = "INSERT INTO `child` (`firstName`, `lastName`, birthday, pagerNumber, allergiesName, notes)
		  VALUES(?, ?, ?, ?, ?, ?)";
  if ($stmt->prepare($sql)) {
	// bind parameters and execute statement
	$stmt->bind_param('sssiss' , $_POST['firstName'], $_POST['lastName'], $date, $_POST['pager'], $_POST['allergies'], $_POST['notes']);
    // execute and get number of affected rows
	$stmt->execute();
	if ($stmt->affected_rows > 0) {
	  $OK = true;
	}
  }
  // redirect if successful or display error
  if ($OK) {
	  header('Location: http://nursery.cjsorensen.com/registered.php');
  exit;

	
  } else {
	$error = $stmt->error;
  }
}?>
