<?php
    //ini_set('display_errors', '1');
    //error_reporting(E_ALL);
    
	$mongodb = new MongoClient();
	$asteriskWebUi = $mongodb->asteriskWebUi;
	$sipAccounts = $asteriskWebUi->sipAccounts;
	
	if( isset($_POST['password']) && isset($_POST['domain']) )//condizione per modifica account
	{
		if( isset($_POST['addAccount']) && isset($_POST['username']) )
		{
			$newAccount = array(
				'phone' => $_POST['addAccount'],
				'username' => $_POST['username'],
				'password' => $_POST['password'],
				'domain' => $_POST['domain'],
				'ringsOn' => "non impostato",
			);
			
			try{
				$sipAccounts->insert($newAccount);
				echo '<div class="box box-solid box-success"> <div class="box-header"> <h3 class="box-title">Successo!</h3> </div><div class="box-body"> Account aggiunto con successo! </div></div>';
			} catch( MongoDuplicateKeyException $e ){
				echo '<div class="box box-solid box-danger"> <div class="box-header"> <h3 class="box-title">Errore!</h3> </div><div class="box-body"> Numero di telefono già esistente. </div></div>';
			}
			
			
		}
		else if( isset($_POST['phone']) && isset($_POST['ringsOn']))
		{
			$updateQuery = array(
				'criteria' => array(
					'phone' => $_POST['phone'],
				),
				'newData' => array(
					'$set' => array(
						
					),
				),
			);
	
			if( $_POST['password'] !== "" )
				$updateQuery['newData']['$set']['password'] = $_POST['password'];
			if( $_POST['domain'] !== "" )
				$updateQuery['newData']['$set']['domain'] = $_POST['domain'];
			if( $_POST['ringsOn'] !== "" )
				$updateQuery['newData']['$set']['ringsOn'] = $_POST['ringsOn'];
				
			$sipAccounts->update($updateQuery['criteria'], $updateQuery['newData']);
			
			echo '<div class="box box-solid box-success"> <div class="box-header"> <h3 class="box-title">Successo!</h3> </div><div class="box-body"> Account modificato con successo! </div></div>';

		}
	}
	else if( isset($_POST['removeAccount']) )
	{
		$removeQuery = array(
			'phone' => $_POST['removeAccount'],
		);
		
		$sipAccounts->remove($removeQuery);
		
		echo '<div id="extension-deleted" class="box box-solid box-info"><div class="box-header"><h3 class="box-title">Successo!</h3></div><!-- /.box-header --><div class="box-body">Account eliminato con successo.</div><!-- /.box-body --></div>';
	}
	else
	{
		$accounts = $sipAccounts->find();
		$extensions = $asteriskWebUi->extensions;
		$extensionsCursor = $extensions->find();
		
		foreach( $accounts as $account )
		{
			echo "<div class='info-box'><span class='info-box-icon";
			
			include 'sipPeers.php';
			
			if( $statuses[$account['phone']] )
				echo " bg-green";
			else
				echo " bg-red";


			
			echo "'><i class='fa fa-user'></i></span> <div class='info-box-content'><span class='info-box-number'>" . $account['phone'] . "</span><span>Username: " . $account['username'] . "</span><br/><span>Password: " . $account['password'] . "</span><br/><span>Dominio: " . $account['domain'] . "</span><br/><span>Squilla su: " . $account['ringsOn'] . "</span></div><br/>&nbsp; <button type='button' class='btn btn-primary edit-btn' onclick=\"$('#" . $account['phone'] . "').toggleClass('hidden')\">Modifica</button> <br/> <br/>&nbsp; <button type='button' class='btn btn-danger' onclick=\"removeAccount('" . $account['phone'] . "')\">Rimuovi</button> <br/> <br/> <form id='" . $account['phone'] . "' class='form-horizontal hidden' onsubmit='submitAccountForm(this.id)'> <div class='form-group'> <label for='newPassword" . $account['phone'] . "' class='col-sm-2 control-label'>Password</label> <div class='col-sm-10'> <input type='password' class='form-control' id='newPassword" . $account['phone'] . "' placeholder='Password' ></div></div><div class='form-group'> <label for='newDomain" . $account['phone'] . "' class='col-sm-2 control-label'>Dominio</label> <div class='col-sm-10'> <input type='text' class='form-control' id='newDomain" . $account['phone'] . "' placeholder='Dominio o indirizzo IP'><span class='help-block'>Inserire un indirizzo IP nel formato XXX.XXX.XXX.XXX (es: 192.168.1.1) o un nome di dominio</span></div></div><div class='form-group'> <label for='newRingsOn" . $account['phone'] . "' class='col-sm-2 control-label'>Squilla su</label> <div class='col-sm-10'> <select id='newRingsOn" . $account['phone'] . "' class='form-control'>";
			
			foreach( $extensionsCursor as $extension )
			{
				echo "<option value='" . $extension['name'] . "'> " . $extension['name'] . "</option>";
			}
			
			echo "</select></div></div><div class='form-group'> <div class='col-sm-offset-2 col-sm-10'> <button type='submit' class='btn btn-default'>Conferma</button></div></div></form> <br/></div><br/>";
		}
		
	}
	
?>