<?php require_once('Connections/nursery.php'); ?>
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
<?php
$conn = dbConnect('write'); 
$sql = "SELECT * FROM child INNER JOIN (SELECT childID, MAX(checkDateTime) AS 'mostRecentTime' FROM checkedIn GROUP BY childID) subTime  ON subTime.childID=child.childID WHERE subTime.mostRecentTime > DATE_SUB(NOW(), INTERVAL 1 DAY) && child.isThere=1  ORDER BY child.birthday ASC";
$result = $conn->query($sql) or die($conn->error);
$numRows = $result->num_rows;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>
</head>

<body>


	<!--<div id="isThereModal" class="reveal-modal">-->
    <table>
  <thead>
  <tr>
    <th>Number</th>
    <th>First Name</th>
    <th>Last Name</th>
    <th>Checked In</th>
    <th>Pager Number</th>
    <th>Check Out</th>
  </tr>
  </thead>
  <?php $i=1; 
  	while ($row = $result->fetch_assoc()){?>
  <tr>
    <td><?php echo $i; $i++?></td>
    <td><?php echo $row['firstName']; ?></td>
    <td><?php echo $row['lastName']; ?></td>
    <td><?php $date = new DateTime($row['mostRecentTime']);
		echo $date->format('M d h:i A'); ?></td> 
    <td><?php echo $row['pagerNumber']; ?></td>
    <td><form name="checkOutButton" method="post" action="">
    <input type="hidden" name="checkOutButton" value="<?php echo $row['childID'];?>"</input>
    <input type="button" class="small button" name="checkOutSubmit" value="Check Out"</input></form></td>
    <?php }?>
  </tr>
</table>
 <!--<a class="close-reveal-modal">&#215;</a>
		</div>-->

</body>
</html>