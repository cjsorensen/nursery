
<?php 
$sql = "SELECT * FROM child INNER JOIN (SELECT childID, MAX(checkDateTime) AS 'mostRecentTime' FROM checkedIn GROUP BY childID) subTime  ON subTime.childID=child.childID WHERE subTime.mostRecentTime > DATE_SUB(NOW(), INTERVAL 1 DAY) && child.isThere=1  ORDER BY child.birthday ASC";
$result = $conn->query($sql) or die($conn->error);
$numRows = $result->num_rows;

?>
<?php 
?>
	<div id="isThereModal" class="reveal-modal large">
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
    <input type="hidden" name="checkOutID" value="<?php echo $row['childID'];?>">
    <input type="submit" class="small button" name="checkOutSubmit" value="Check Out"></form></td>
    <?php }?>
  </tr>
</table>
 <a class="close-reveal-modal">&#215;</a>
		</div>
