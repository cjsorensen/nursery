<?php 
require_once('includes/connection.inc.php');
  $conn = dbConnect('read');

$sqlAllergies = "SELECT * FROM allergies ORDER by allergiesName ASC";
  $resultAllergies = $conn->query($sqlAllergies) or die($conn->error);?>
<div id="childModal" class="reveal-modal">
			
<form name="registerChild" method="post" action="">
<label>First Name</label>
<input required name="firstName" type="text" placeholder="First Name">
<label>Last Name</label>
<input  required name="lastName" type="text" placeholder="Last Name">
<label>Birthday</label>
<input required name="birthday" type="date" placeholder="mm/dd/yyyy">
<label>Pager Number</label>
<input required name="pager" type="number" placeholder="100">
<label>Notes</label>
<input name="notes" type="text" placeholder="Notes">
<label>Allergies</label>
					<select name="allergies">
						<option value="">Select An Allergy</option>
                        <option><?php if (isset($resultAllergies)){
							while ($row = $resultAllergies->fetch_assoc()){?>
								<option value="<?php echo $row['allergiesName'];?>"><?php echo $row['allergiesName'];?></option>
						<?php }}?>
						
						</select>
<input class="button" name="register" type="submit" value="Register"></form>

 <a class="close-reveal-modal">&#215;</a>
		</div>
        