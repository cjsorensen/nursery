<?php include ('connection.inc.php');
$conn = dbConnect('read');
$sql = "SELECT * FROM allergies ORDER BY allergiesName ASC";
$result = $conn->query($sql) or die($conn->error);
while ($row = $result->fetch_assoc()){?>
<option value="<?php echo $row['allergiesName'];?>"> <?php echo $row['allergiesName'];?></option>

<?php }?>

