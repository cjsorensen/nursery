<?php ob_start();?>
<?php date_default_timezone_set("America/Chicago"); 
$currentDate = date("Y-m-d H:i:s");?>

<?php require_once('Connections/nursery.php'); ?>
<?php
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
  //to fully log out a visitor we need to clear the session varialbles
  $_SESSION['MM_Username'] = NULL;
  $_SESSION['MM_UserGroup'] = NULL;
  $_SESSION['PrevUrl'] = NULL;
  unset($_SESSION['MM_Username']);
  unset($_SESSION['MM_UserGroup']);
  unset($_SESSION['PrevUrl']);
	
  $logoutGoTo = "index.php";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}
?>
<?php
require_once('./includes/restrict.inc.php');
?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

if ((isset($_GET['childID'])) && ($_GET['childID'] != "")) {
  $deleteSQL = sprintf("DELETE FROM child WHERE childID=%s",
                       GetSQLValueString($_GET['childID'], "int"));

  mysql_select_db($database_nursery, $nursery);
  $Result1 = mysql_query($deleteSQL, $nursery) or die(mysql_error());

  $deleteGoTo = "deleted.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $deleteGoTo));
}
?>
<?php
  require_once('./includes/connection.inc.php');
  $conn = dbConnect('read');
  
  $sql = "SELECT * FROM child ORDER BY lastName ASC";
  $result = $conn->query($sql) or die($conn->error);
  $numRows = $result->num_rows;
  
  $sqlInfants ="SELECT * FROM child INNER JOIN (SELECT childID, MAX(checkDateTime) AS 'mostRecentTime' FROM checkedIn GROUP BY childID) subTime  ON subTime.childID=child.childID WHERE subTime.mostRecentTime > DATE_SUB(NOW(), INTERVAL 1 DAY) && child.birthday > DATE_SUB(NOW(), INTERVAL 1 YEAR) && child.isThere=1  ORDER BY child.birthday ASC";
  $resultInfants = $conn->query($sqlInfants) or die($conn->error);
  $numRowsInfants = $resultInfants->num_rows;
  
  $sqlToddler ="SELECT * FROM child INNER JOIN (SELECT childID, MAX(checkDateTime) AS 'mostRecentTime' FROM checkedIn GROUP BY childID) subTime  ON subTime.childID=child.childID WHERE subTime.mostRecentTime > DATE_SUB(NOW(), INTERVAL 1 DAY) && child.birthday < DATE_SUB(NOW(), INTERVAL 1 YEAR) && child.isThere=1  ORDER BY child.birthday ASC";
  $resultToddler = $conn->query($sqlToddler) or die($conn->error);
  $numRowsToddler = $resultToddler->num_rows;
  
  
?>
<?php
if (isset($_POST['details']) && is_numeric($_POST['details'])) {
  require_once('./includes/connection.inc.php');
  $conn = dbConnect('read');
  $detailPost = ($_POST['details']);
  $sql = "SELECT firstName, lastName, DATE_FORMAT(birthday, '%M %D, %Y') AS dateBirthday, notes, pagerNumber FROM child WHERE childID=$detailPost";
  $resultDetails = $conn->query($sql) or die($conn->error);
  
  $sqlParent = "SELECT parentFirstName, parentLastName FROM parent INNER JOIN parentChild USING (parentID) WHERE parentChild.childID=$detailPost";
  $resultParent = $conn->query($sqlParent) or die($conn->error);
  
  $sqlAllergies = "SELECT allergiesName, allergiesDescription FROM allergies INNER JOIN child USING (allergiesName) WHERE child.childID=$detailPost";
  $resultAllergies = $conn->query($sqlAllergies) or die($conn->error);
  
  $sqlTimestamp = "SELECT DATE_FORMAT(checkedIn.checkDateTime, '%b %d at %T') AS dateTimestamp FROM checkedIn WHERE checkedIn.childID=$detailPost && checkedIn.checkDateTime > DATE_SUB(NOW(), INTERVAL 2 MONTH) ORDER BY checkedIn.checkDateTime DESC";
  $resultTimestamp = $conn->query($sqlTimestamp) or die($conn->error);
  
  $sqlCheckedOut = "SELECT DATE_FORMAT(checkedOut.checkDateTime, '%b %d at %T') AS dateCheckedOut FROM checkedOut WHERE checkedOut.childID=$detailPost && checkedOut.checkDateTime > DATE_SUB(NOW(), INTERVAL 2 MONTH) ORDER BY checkedOut.checkDateTime DESC";
  $resultCheckedOut = $conn->query($sqlCheckedOut) or die($conn->error);
  
  
   
  
}
?>
<?php if (isset($_POST['checkInID'])) {
  require_once('./includes/connection.inc.php');
  // initialize flag
  $In = false;
  // create database connection
  $conn = dbConnect('write');
  // initialize prepared statement
  $stmt = $conn->stmt_init();
  // create SQL
  $sql = "INSERT INTO `checkedIn` (`checkDateTime`, `childID`)
		  VALUES(?, ?)";
  if ($stmt->prepare($sql)) {
	// bind parameters and execute statement
	$stmt->bind_param('si' , $currentDate, $_POST['checkInID']);
    // execute and get number of affected rows
	$stmt->execute();
	if ($stmt->affected_rows > 0) {
	  $In = true;
	}
	if ($In){
		$sql = "UPDATE  `child` SET isThere = '1'
				WHERE `childID` = ?";
		$stmt->prepare($sql);
		$stmt->bind_param('i' , $_POST['checkInID']);
		$stmt->execute();
	}
  }
  // redirect if successful or display error
  if ($In) {
	$InMessage = "The Child Has Been Checked In";
	header ('location: http://nursery.cjsorensen.com/checkIn.php');
	
  } else {
	$error = $stmt->error;
  }
} 
?>
<?php if (isset($_POST['checkOutID'])) {
  require_once('./includes/connection.inc.php');
  // initialize flag
  $Out = false;
  // create database connection
  $conn = dbConnect('write');
  // initialize prepared statement
  $stmt = $conn->stmt_init();
  //Set correct timezone
  
  // create SQL
  $sql = "INSERT INTO `checkedOut` (`checkDateTime`, `childID`)
		  VALUES(?, ?)";
  if ($stmt->prepare($sql)) {
	// bind parameters and execute statement
	$stmt->bind_param('si' , $currentDate, $_POST['checkOutID']);
    // execute and get number of affected rows
	$stmt->execute();
	if ($stmt->affected_rows > 0) {
	  $Out = true;
	}
	if ($Out){
		$sql = "UPDATE  `child` SET isThere = NULL
				WHERE `childID` = ?";
		$stmt->prepare($sql);
		$stmt->bind_param('i' , $_POST['checkOutID']);
		$stmt->execute();
	}
  }
  // redirect if successful or display error
  if ($Out) {
	$OutMessage = "The Child Has Been Checked Out";
	header ('location: http://nursery.cjsorensen.com/checkOut.php');
  } else {
	$error = $stmt->error;
  }
}?>


<? //Register Child and add to database
require_once ('./includes/registerChild.inc.php'); ?>


<!DOCTYPE html>


<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]--><head>
  <meta charset="utf-8" />

  <!-- Set the viewport width to device width for mobile -->
  <meta name="viewport" content="width=device-width" />

  <title>Welcome to Nursery App</title>
  
  <!-- Included CSS Files (Uncompressed) -->
  <!--
  <link rel="stylesheet" href="stylesheets/foundation.css">
  -->
  
  <!-- Included CSS Files (Compressed) -->
  <link rel="stylesheet" href="stylesheets/foundation.min.css">
  <link rel="stylesheet" href="stylesheets/app.css">


  <script src="javascripts/modernizr.foundation.js"></script>

  <!-- Attach necessary scripts -->
		

  <!-- IE Fix for HTML5 Tags -->
  <!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
  
 
<style>
.contrast {color: #2ba6cb !important;
}
</style>



</head>

<body>


<?php if (isset ($error)){
	echo "<p> Error: $error</p>";
}?>

<div class="row">
    <div class="ten columns">
    <h1>Nursery Check In</h1>
    </div>
    <div class="two columns">
    <h1><a class="button" href="<?php echo $logoutAction ?>">Sign Out</a></h1>
    </div> 
    
    <div class="twelve columns">
      <ul class="nav-bar">
      	<li><a href="">Edit list</a></li>
        <li><a href="">Open list</a></li>
        <li><a href="">New list</a></li>
      </ul>  
      
    
    </div>
  </div>
<div class="row">
	
    <!--<div class="two columns">
    	<img src="http://placehold.it/200x250"/></div>-->
        <div class="seven columns">
       
        <?php if (isset($resultDetails)){
		($details = $resultDetails->fetch_assoc());
        ?>
        <h1>
		<?php echo $details['firstName'];?> <?php echo $details['lastName']; ?></h1> 
    		<?php if(isset($resultParent)){
				while($detailsParent = $resultParent->fetch_assoc()){ ?>
				
				
				
				<h2><span class="contrast"><?php echo $detailsParent['parentFirstName']?> <?php echo $detailsParent['parentLastName']?></span></h2>
            <?php }} ?>
    		<h3><span class="contrast">Age:</span> 
            <?php $bday = new DateTime($details['dateBirthday']);
			//set the current date
			$date = date('Y-m-d H:i:s');
			//create DateTime object
			$today = new DateTime($date); 
			//Check for the difference
			$diff = $today->diff($bday);
			//display the result	
			printf('%d years, %d months, %d days', $diff->y, $diff->m, $diff->d);?></h3>
           
    		<h3><span class="contrast">Birthday:</span> <?php echo $details['dateBirthday']; ?></h3>
    		<h3><span class="contrast">Pager:</span> <?php echo $details['pagerNumber']; ?></h3>
            <h3><span class="contrast">Allergies:</span></h3>
       	 	<?php if (isset($resultAllergies)){
			while($detailsAllergies = $resultAllergies->fetch_assoc()){?>
			<h4><span class="contrast"><?php echo $detailsAllergies['allergiesName'];?></span></h4>
			<p><?php echo $detailsAllergies['allergiesDescription'];?></p>
			<?php }
			
			}?>
            
            
            
          
              
    </div>
    <!--This is the Attendance section which includes a button for checking kids in and out and a list of recent attendance dates-->
    
    <div class="five columns">
    	
        <h3>Attendance </h3> 
    <!--check in buttons-->
    <form class="six columns" name="attendance" method="post" action="">
    <input type="hidden" name="checkInID" value="<?php echo $detailPost?>"> 
    <input name="checkIn" type="submit" class="medium button" value="Check In"></form>
    <form class="six columns" name="attendance2" method="post" action="">
    <input type="hidden" name="checkOutID" value="<?php echo $detailPost?>"> 
    <input name="checkOut" type="submit" class="medium button" value="Check Out"></form>
   
    <table class="six columns">
    <thead>
    <th>In</th>
    </thead>
  <?php if (isset($resultTimestamp)){
 		while($tableTimestamp = $resultTimestamp->fetch_assoc()){?>
  <tr>
    <td> <?php echo $tableTimestamp['dateTimestamp'];?></td>
  </tr>
		 <?php }}?>
</table>
<table class="six columns">
    <thead>
    <th>Out</th>
    </thead>
  <?php if (isset($resultCheckedOut)){
 		while($tableCheckedOut = $resultCheckedOut->fetch_assoc()){?>
  <tr>
    <td> <?php echo $tableCheckedOut['dateCheckedOut'];?></td>
  </tr>
		 <?php }}?>
</table>

   		</div>
    

</div>

<div class="row">
	<div class="eight columns">
    	<div class="panel">
     	<h2>Notes</h2>
    	<p><?php echo $details['notes']; ?></p><?php } ?> 
        
    	</div>
    	</div>
    <div class="four columns">
    
    </div>
</div>

<div class="row">
    <div class="twelve columns">
    <h3>Registered Children</h3>
    </div>
    <div class="twelve columns">Number of Infants <span class="contrast"><?php echo $numRowsInfants; ?></span> and Number of Toddlers <span class="contrast"><?php echo $numRowsToddler; ?></span></div> 
    
    <div class="twelve columns">
      <ul class="nav-bar">
      	<li><a href="#" data-reveal-id="childModal">Add Child</a></li>
        <li><a href="#" data-reveal-id="todayModal">Who Checked In Today?</a></li>
        <li><a href="#" data-reveal-id="isThereModal">Who Is Checked In Now?</a></li>
        

      </ul>  

</div>



<div class="row">
	<div class="twelve columns">
    	
        <div class="twelve columns">
<?php if (isset($numRows)) { ?>
<?php if ($numRows) { ?>
<table class="twelve">
  <thead>
  <tr>
    <th>Delete</th>
    <th>Edit</th>
    <th>Number</th>
    <th>First Name</th>
    <th>Last Name</th>
    <th>Pager Number</th>
    <th>Details</th>
  </tr>
  </thead>
  <?php $i = 1;
  	while ($row = $result->fetch_assoc()) { ?>
  <tr>
    <td><form name="delete" method="get" action="" onsubmit="return confirm('Are you sure you want to delete this child?')">
    <input type="hidden" name="childID" value="<?php echo $row['childID']?>"> 
    <input name="delete" type="submit" class="small button" value="Delete"></form></td>
    <td><form name="edit" method="get" action="update.php" onsubmit="return confirm('Are you sure you want to update this child?')">
    <input type="hidden" name="childID" value="<?php echo $row['childID']?>"> 
    <input name="delete" type="submit" class="small button" value="Edit"></form></td>
    <td><?php echo $i; $i++;?></td>
    <td><?php echo $row['firstName']; ?></td>
    <td><?php echo $row['lastName']; ?></td>
    <td><?php echo $row['pagerNumber']; ?></td>
    
    <td><form name="details" method="post" action="">
    <input type="hidden" name="details" value="<?php echo $row['childID']?>"> 
    <input name="detail" type="submit" class="small button" value="Detail"></form>
    
    </td>
    
  </tr>
  <?php } ?>
</table>

  <?php }
} ?>
	</div>
    
    </div>


</div>


<?php require_once ('./includes/registrationForm.inc.php');?>
<?php require_once ('./includes/todaysList.inc.php');?>
<?php require_once ('./includes/isThere.inc.php');?>


  <script src="javascripts/app.js"></script>
  <script src="javascripts/jquery-1.4.4.min.js"></script>
  <script src="javascripts/foundation.min.js"></script>

</body>
</html>