<?php
	//ini_set('display_errors', '1');
    //error_reporting(E_ALL);
    
	$mongodb = new MongoClient();
	$asteriskWebUi = $mongodb->asteriskWebUi;
	$asteriskSettings = $asteriskWebUi->asteriskSettings;
	
	if( isset($_POST['newIP']) )
	{
		$updateQuery = array(
			'criteria' => array(
				'name' => "externip",
			),
			'update' => array(
				'$set' => array(
					'value' => $_POST['newIP'],
				),
			),
			'options' => array(
				'upsert' => true,
			),
		);
		
		$asteriskSettings->update($updateQuery['criteria'], $updateQuery['update'], $updateQuery['options']);
	}

	$ip = file_get_contents('https://api.ipify.org');
    echo '<div class="box box-default"> <div class="box-header with-border"> <h3 class="box-title">IP esterno del server Asterisk</h3> <div class="box-tools pull-right"> <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button> </div></div><div class="box-body"> Potresti volere utilizzare questo IP come IP esterno: ' . $ip . '</div></div>';
    
    $currentIp = $asteriskSettings->find(
    	array(
    		'name' => "externip",
    	)
    );
    
    $currentIp = $currentIp->getNext()['value'];
    
    if( !isset($currentIp) )
    	$currentIp = "Non Impostato.";
    
    echo '<div class="callout callout-info lead"> <h4>Impostazioni correnti</h4> <p> L\'IP attualmente impostato è: ' . $currentIp . '</p></div>';
    
?>