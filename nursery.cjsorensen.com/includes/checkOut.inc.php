<?php if (isset($_POST['checkOutButton'])) {
  require_once('./includes/connection.inc.php');
  // initialize flag
  $Out = false;
  // create database connection
  $conn = dbConnect('write');
  // initialize prepared statement
  $stmt = $conn->stmt_init();
  // create SQL
  $sql = "INSERT INTO `checkedOut` (`checkDateTime`, `childID`)
		  VALUES(?, ?)";
  if ($stmt->prepare($sql)) {
	// bind parameters and execute statement
	$stmt->bind_param('si' , $currentDate, $_POST['checkOutButton']);
    // execute and get number of affected rows
	$stmt->execute();
	if ($stmt->affected_rows > 0) {
	  $Out = true;
	}
	if ($Out){
		$sql = "UPDATE  `child` SET isThere = NULL
				WHERE `childID` = ?";
		$stmt->prepare($sql);
		$stmt->bind_param('i' , $_POST['checkOutButton']);
		$stmt->execute();
	}
  }
  // redirect if successful or display error
  if ($Out) {
	header ('location: http://nursery.cjsorensen.com/checkOut.php');
  } else {
	$error = $stmt->error;
  }
}?>