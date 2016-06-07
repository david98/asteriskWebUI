<?php
	$users = (new MongoClient())->asteriskWebUi->users;
	
	$users->insert(
		array(
			'username' => $_GET['username'],
			'password' => password_hash($_GET['password'],PASSWORD_DEFAULT),
		)
	);
?>