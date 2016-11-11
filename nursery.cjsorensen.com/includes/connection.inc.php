<?php
	function dbConnect($usertype, $connectionType = 'mysqli') {
		$host = '';
		$db = '';
		if ($usertype == 'read') {
			$user = '';
			$pwd = '';
		} elseif ($usertype == 'write'){
			$user = '';
			$pwd = '';
		} else {
			exit ('Unrecognized connection type');
		}
		//connection code goes here
		
		 if ($connectionType == 'mysqli') {
			$conn = new mysqli($host, $user, $pwd, $db) or die("Cannot open database");
			return $conn;
		}
				
	}
	
?>