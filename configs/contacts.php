<?php
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
	$mongodb = new MongoClient();
	$asteriskWebUi = $mongodb->asteriskWebUi;
	$contacts = $asteriskWebUi->contacts;
	
	if( isset($_POST['_id']) )
	{
		if( isset($_POST['phone']) || isset($_POST['name']) || isset($_POST['surname']) )
		{
			$updateQuery = array(
				'criteria' => array(
					'_id' => new MongoId($_POST['_id'])
				),
				'update' => array(
					'$set' => array()
				)
			);
			
			if( isset($_POST['name']) )
				$updateQuery['update']['$set']['name'] = $_POST['name'];
			if( isset($_POST['surname']) )
				$updateQuery['update']['$set']['surname'] = $_POST['surname'];
			if( isset($_POST['phone']) )
				$updateQuery['update']['$set']['phone'] = $_POST['phone'];
			
			$contacts->update($updateQuery['criteria'], $updateQuery['update']);
		}
		else if( isset($_POST['delete']) )
		{
			$removeQuery = array(
				'_id' => new MongoId($_POST['_id'])
			);
			
			$contacts->remove($removeQuery);
		}
	}
	else if( isset($_POST['name']) && isset($_POST['surname']) && isset($_POST['phone']) )
	{
		$contact = array(
			'name' => $_POST['name'],
			'surname' => $_POST['surname'],
			'phone' => $_POST['phone']
		);

		try{
			$contacts->insert($contact);
		} catch ( MongoDuplicateKeyException $e ){
			echo '';
		}
	}
	else
	{
		$contactsCursor = $contacts->find();
		$sortCriteria = array(
			'name' => 1,
			'surname' => 1
		);
		$contactsCursor->sort($sortCriteria);
		
		foreach( $contactsCursor as $contact)
		{
			echo '<a href="#" class="list-group-item"><h4 class="list-group-item-heading">' . $contact['name'] . ' ' . $contact['surname'] . '</h4><p class="list-group-item-text">' . $contact['phone'] . '</p><div id="' . $contact['_id'] .  '" class="btn-group" role="group" aria-label="...">
  <button type="button" class="btn btn-danger">Elimina</button>
  <button type="button" class="btn btn-primary">Modifica</button>
  <button type="button" class="btn btn-success">Chiama</button>
</div></a>';
		}
	}
	
?>