<?php
	ini_set('display_errors', '1');
    error_reporting(E_ALL);

    $mongodb = new MongoClient();
    $asteriskWebUi = $mongodb->asteriskWebUi;
    $asteriskSettings = $asteriskWebUi->asteriskSettings;
    $extensions = $asteriskWebUi->extensions;
    $sipAccounts = $asteriskWebUi->sipAccounts;
    
    $backupDb = $mongodb->asteriskWebUiBackup;
    $asteriskSettingsBackup = $backupDb->asteriskSettings;
    $extensionsBackup = $backupDb->extensions;
	$sipAccountsBackup = $backupDb->sipAccounts;
    
    $oldAsteriskSettings = $asteriskSettingsBackup->find();
    $oldExtensions = $extensionsBackup->find();
    $oldSipAccounts = $sipAccountsBackup->find();
    
    $asteriskSettings->remove(array());
    
    foreach( $oldAsteriskSettings as $oldSetting )
    {
    	$asteriskSettings->insert($oldSetting);
	}
	
	$extensions->remove(array());
	
	foreach( $oldExtensions as $oldExtension )
	{
		$extensions->insert($oldExtension);
	}
	
	$sipAccounts->remove(array());
	
	foreach( $oldSipAccounts as $oldSipAccount )
	{
		$sipAccounts->insert($oldSipAccount);
	}
        
    header("Location: /");
    
?>