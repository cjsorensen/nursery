<?php 
require_once ('connection.inc.php');
$conn = dbConnect('read');


$sql = "SELECT * FROM child INNER JOIN checkedIn USING (childID) WHERE checkedIn.checkDateTime > DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY child.lastName ASC";
$result = $conn->query($sql) or die($conn->error);
$numRows = $result->num_rows;

?>
<?php 
?>
	<div id="todayModal" class="reveal-modal">
    <table class="twelve">
  <thead>
  <tr>
    <th>Number</th>
    <th>First Name</th>
    <th>Last Name</th>
    <th>Checked In</th>
    <th>Pager Number</th>
  </tr>
  </thead>
  <?php $i=1; 
  	while ($row = $result->fetch_assoc()){?>
  <tr>
    <td><?php echo $i; $i++?></td>
    <td><?php echo $row['firstName']; ?></td>
    <td><?php echo $row['lastName']; ?></td>
    <td><?php $date = new DateTime($row['checkDateTime']);
		echo $date->format('M d h:i A'); ?></td> 
    <td><?php echo $row['pagerNumber']; ?></td>
    <?php }?>
    
  </tr>
</table>
 <a class="close-reveal-modal">&#215;</a>
		</div>






