<?php require_once('./includes/connection.inc.php');
// initialize flags
$OK = false;
$done = false;
// create database connection
$conn = dbConnect('write');
// initialize statement
$stmt = $conn->stmt_init();
// get details of selected record
if (isset($_GET['childID']) && !$_POST) {
  // prepare SQL query
  $sql = 'SELECT childID, firstName, lastName, birthday, pagerNumber, notes, allergiesName 
		  FROM child WHERE childID = ?';
  if ($stmt->prepare($sql)) {
	// bind the query parameter
	$stmt->bind_param('i', $_GET['childID']);
	// bind the results to variables
	$stmt->bind_result($childID, $firstName, $lastName, $birthday, $pagerNumber, $notes, $allergiesName);
	// execute the query, and fetch the result
	$OK = $stmt->execute();
	$stmt->fetch();
  }
}
// redirect if $_GET['article_id'] not defined
if (!isset($_GET['childID'])) {
  header('Location: http://nursery.cjsorensen.com');
  exit;
}
// store error message if query fails
if (isset($stmt) && !$OK && !$done) {
  $error = $stmt->error;
}

?>
<?php 
//if form has been submitted, update record
 if (isset($_POST ['update'])) {
  // prepare update query
  $sql = 'UPDATE child SET firstName = ?, lastName = ?, birthday = ?, pagerNumber = ?, notes = ?
		  WHERE childID = ?';
  if ($stmt->prepare($sql)) {
	$stmt->bind_param('sssssi', $_POST['firstName'], $_POST['lastName'], $_POST['birthday'], $_POST['pagerNumber'], $_POST['notes'],$_POST['childID']);
	$done = $stmt->execute();
  }
}
// redirect if done
if ($done) {
  header('Location: http://nursery.cjsorensen.com/registered.php');
  exit;
}
if (!isset($_GET['childID'])) {
  header('Location: http://nursery.cjsorensen.com/app.php');
  exit;
}

// store error message if query fails
if (isset($stmt) && !$OK && !$done) {
  $error = $stmt->error;
}

?>

  

<!DOCTYPE html>


<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]--><head>
  <meta charset="utf-8" />

  <!-- Set the viewport width to device width for mobile -->
  <meta name="viewport" content="width=device-width" />

  <title>Edit a Child</title>
  
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
<?php //$arr = get_defined_vars();?>

<pre> <?php //print_r($arr);?></pre>

<?php echo $row['allergiesName'];?>
<div class="row"><div class= "twelve columns"><h1>Update the Child's Information Below</h1></div></div>
<div class="six columns centered">			
<form name="registerChild" method="post" action="">
<input type="hidden" name="childID" value="<?php echo $childID ?>">
<label>First Name</label>
<input required value ="<?php echo htmlentities($firstName, ENT_COMPAT, 'utf-8');?>" name="firstName" type="text">
<label>Last Name</label>
<input  required value="<?php echo htmlentities($lastName, ENT_COMPAT, 'utf-8');?> " name="lastName" type="text">
<label>Birthday</label>
<input  required value="<?php echo $birthday;?> " name="birthday" type="text">
<label>Pager Number</label>
<input  required value="<?php echo $pagerNumber;?> " name="pagerNumber" type="text">
<label>Notes</label>
<input  required value="<?php echo htmlentities($notes, ENT_COMPAT, 'utf-8');?> " name="notes" type="text">
<label>Allergies</label>
					<select name="allergies">
						<option value="">Select An Allergy</option>
                        <option value="<?php echo $allergiesName; ?>"><?php echo $allergiesName; ?></option>
                        <option>dairy</option>
                        <option>gluten</option>
						
						</select>
<input class="button" name="update" type="submit" value="Update">
<a href="http://nursery.cjsorensen.com/app.php" class=" alert button">Cancel</a></form>



		</div>