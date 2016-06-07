<?php
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
    
	$mongodb = new MongoClient();
	$asteriskWebUi = $mongodb->asteriskWebUi;
	$extensions = $asteriskWebUi->extensions;
	
	if( isset($_POST['name']) && isset($_POST['ip']) && isset($_POST['password']) )
	{
		$updateQuery = array(
			'criteria' => array(
				'name' => $_POST['name'],
			),
			'newData' => array(
				'$set' => array(
					
				),
			),
		);

		if( $_POST['password'] !== "" )
			$updateQuery['newData']['$set']['password'] = $_POST['password'];
		if( $_POST['ip'] !== "" )
			$updateQuery['newData']['$set']['ip'] = $_POST['ip'];
			
		$extensions->update($updateQuery['criteria'], $updateQuery['newData']);
	}
	else if( isset($_POST['removeExtension']) )
	{
		$removeQuery = array(
			'name' => $_POST['removeExtension'],
		);
		$extensions->remove($removeQuery);
		
		echo '<div id="extension-deleted" class="box box-solid box-info"><div class="box-header"><h3 class="box-title">Successo!</h3></div><!-- /.box-header --><div class="box-body">Estensione eliminata con successo.</div><!-- /.box-body --></div>';
	}
	else if( isset($_POST['addExtension']) )
	{
		$newExtension = array(
			'name' => $_POST['addExtension'],
			'username' => $_POST['username'],
			'password' => $_POST['password'],
			'ip' => $_POST['ip'],
		);
		
		try{
			$extensions->insert($newExtension);
			echo '<div class="box box-solid box-success"> <div class="box-header"> <h3 class="box-title">Successo!</h3> </div><div class="box-body"> Estensione aggiunta con successo! </div></div>';
		} catch (MongoDuplicateKeyException $e) {
			echo '<div class="box box-solid box-danger"> <div class="box-header"> <h3 class="box-title">Errore!</h3> </div><div class="box-body"> Nome, username o IP duplicati. </div></div>';
		}
		
	}
	else if( isset($_GET['listExtensions']) )
	{
		$extensionsCursor = $extensions->find();
		
		include 'sipPeers.php';
		
		foreach( $extensionsCursor as $extension)
		{
			if( $statuses[$extension['name']] )
				echo '<option value="' .  $extension["username"] . '">' . $extension["name"] . '</option>';
		}
	}
	else
	{
		$extensionsCursor = $extensions->find();
		
		include 'sipPeers.php';
		
		foreach( $extensionsCursor as $extension)
		{
			echo "<div class='info-box extensionBox'>\n\n<span class='info-box-icon";
			
			if( $statuses[$extension['name']] )
				echo " bg-green";
			else
				echo " bg-red";
			
			echo "'><i class='fa fa-phone'></i></span>\n<div class='info-box-content'><span class='info-box-number'>" . $extension['name'] . "</span>\n<span>Username: " . $extension['username'] . "</span><br />\n<span>Password: " . $extension['password'] . "</span>\n<span class='info-box-text'>IP: " . $extension['ip'] . "</span>\n</div><!-- /.info-box-content -->\n<br />&nbsp;<button type='button' class='btn btn-primary edit-btn' onclick=\"$('#" . $extension['name'] . "').toggleClass('hidden')\">Modifica</button><br /><br />&nbsp;<button type='button' class='btn btn-danger " . $extension['name'] . "'>Rimuovi</button><br /><br /><form id='" . $extension['name'] . "' class='form-horizontal hidden'>\n<div class='form-group'>\n<label for='newPassword" . $extension['name'] . "' class='col-sm-2 control-label'>Password</label>\n<div class='col-sm-10'>\n<input type='password' class='form-control' id='newPassword" . $extension['name'] . "' placeholder='Password' name='password" . $extension['name'] . "'>\n</div>\n</div>\n<div class='form-group'>\n<label for='newIP" . $extension['name'] . "' class='col-sm-2 control-label'>IP</label>\n<div class='col-sm-10'>\n<input type='text' class='form-control' id='newIP" . $extension['name'] . "' placeholder='Indirizzo IP' name='ip" . $extension['name'] . "'>\n<span class='help-block'>Inserire un indirizzo IP nel formato XXX.XXX.XXX.XXX (es: 192.168.1.1)</span></div>\n</div>\n<div class='form-group'>\n<div class='col-sm-offset-2 col-sm-10'>\n<button type='submit' class='btn btn-default'>Conferma</button>\n</div>\n</div>\n</form><br /></div><br /><!-- /.info-box -->";
		}
	}
	
?>
