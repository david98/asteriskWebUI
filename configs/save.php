<?php
	ini_set('display_errors', '1');
    error_reporting(E_ALL);
    
    /*
    	BASE_FILE_PATH: qui il programma cerca i file di configurazione di base ("base" + nome del file) da caricare in memoria
		SETTINGS_FILE_PATH: percorso dei file di configurazione di asterisk (es: /etc/asterisk/) 
		N.B.: i due percorsi richiedono lo slash "/" finale
		ACCOUNT_BASE_CONF: stringa di configurazione comune ad ogni account
		EXTENSION_BASE_CONF: stringa di configurazione comune ad ogni estensione
		CURRENT_USER: l'utente che sta eseguendo il processo di Apache (es: www-data)
		N.B: l'utente in questione DEVE avere i permessi di sudo SENZA PASSWORD sui seguenti comandi: cp, mv, chown, service
		REGISTER_STRING_FINDER: stringa provvisoria che il programma andrà poi a sostituire con quelle degli account registrati. Utilizzata come "sentinella" nella ricerca.
	*/
    define("BASE_FILE_PATH", "/etc/asterisk/", true);
    define("SETTINGS_FILE_PATH", "/etc/asterisk/", true);
    define("ACCOUNT_BASE_CONF", "\ncontext=complic\ntype=friend\ncanreinvite=yes\nqualify=yes\nnat=yes\disallow=all\nallow=g729\nallow=ulaw\nallow=alaw\ndtmfmode=rfc2833\ninsecure=port,invite\n", true);
    define("EXTENSION_BASE_CONF", "\ncontext=complic\nhost=dynamic\ntype=friend\ncanreinvite=no\nqualify=yes\nnat=yes\ndtmfmode=rfc2833\ncallgroup=20\npickupgroup=20\ndisallow=all\nallow=g729\nallow=ulaw\nallow=alaw\nrelaxdtmf=yes\ndeny=0.0.0.0/0.0.0.0\n", true);
    define("CURRENT_USER", posix_getpwuid(posix_geteuid())['name'], true);
    define("REGISTER_STRING_FINDER", ";register => 1234:password@mysipprovider.com", true);
	
	//database principale
    $mongodb = new MongoClient();
    $asteriskWebUi = $mongodb->asteriskWebUi;
    $asteriskSettings = $asteriskWebUi->asteriskSettings;
    $extensions = $asteriskWebUi->extensions;
    $sipAccounts = $asteriskWebUi->sipAccounts;
    
    //database che contiene la penultima configurazione salvata
    $backupDb = $mongodb->asteriskWebUiBackup;
    $asteriskSettingsBackup = $backupDb->asteriskSettings;
    $extensionsBackup = $backupDb->extensions;
	$sipAccountsBackup = $backupDb->sipAccounts;
    
    /*
    	La funzione va a cercare il file dal nome contenuto nella stringa $confName nella directory SETTINGS_FILE_PATH e ne crea una copia chiamata $confName.bak,
    	dopodichè va a cercare la corrispondente versione base nella directory BASE_FILE_PATH e ne crea una copia nella cartella in cui risiede il presente script.
    	Viene restituito il contenuto di questo file sottoforma di stringa.
    */
    function openConf($confName)
    {
	    shell_exec("sudo cp " . SETTINGS_FILE_PATH . $confName . " " . SETTINGS_FILE_PATH . $confName . ".bak 2>&1");
	    shell_exec("sudo cp " . BASE_FILE_PATH . "base" . $confName . " " . getcwd() . "/" . $confName . " 2>&1");
	    shell_exec("sudo chown " . CURRENT_USER . " " . $confName . " 2>&1");
	    
	    $confFile = file_get_contents($confName);
	    
	    return $confFile;
    }
    
    /*
	    La funzione riceve un array associativo di parametri (nomeParametro => valore) che vengono scritti nella stringa $confFile e torna $confFile
    */
    function writeParametersToSipConf($parameters, $confFile)
    {
	    foreach($parameters as $name => $value)
	    {
			$confFile = preg_replace("/" . $name . "\s*[=].*/", $name . "=" . $value, $confFile);
	    }
	    
	    return $confFile;
    }
    
    /*
	    La funzione riceve un array di estensioni (a loro volta array associativi) e per ciascuna di esse scrive le corrispondenti righe di configurazione nella stringa $confFile.
	    Torna $confFile.
    */
	function addExtensionsToSipConf($extensions, $confFile)
	{
		foreach( $extensions as $extension )
		{
			$writableExtension = "\n[" . $extension['name'] . "]\nusername=" . $extension['username'] . "\nsecret=" . $extension['password'] . EXTENSION_BASE_CONF . "permit=" . $extension['ip'] . "/255.255.255.255\n";
			$confFile .= $writableExtension;
		}
		
		return $confFile;
	}
	
	/*
		La funzione si comporta esattamente come addExtensionsToSipConf, ma lavora su un array di account.
	*/
	function addAccountsToSipConf($accounts, $confFile)
	{
		$registrationStrings = "\n";
		
		foreach($accounts as $account)
		{
			$writableAccount = "\n[" . $account['phone'] . "]\nusername=" . $account['username'] . "\nsecret=" . $account['password'] . "\nhost=" . $account['domain'] . "\nfromdomain=" . $account['domain']
							. "\nfromuser=" . $account['username'] . ACCOUNT_BASE_CONF;
			$registrationStrings .= "\nregister => " . $account['username'] . ":" . $account['password'] . "@" . $account['domain'] . "/" . $account['phone'];
			
			$confFile .= $writableAccount;
		}
		
		echo $registrationStrings;
		
		$confFile = str_replace(REGISTER_STRING_FINDER, $registrationStrings, $confFile);
		
		return $confFile;
	}
	
	/*
		La funzione si occupa di fixare il file extensions.conf (passato in $confFile sottoforma di stringa) aggiungendo le righe Dial necessarie.
		Torna $confFile.
	*/
	function fixExtensionsConf($accounts, $extensions, $confFile)
	{
		foreach( $accounts as $account )
		{
			if( $account['ringsOn'] !== "non impostato")
			{
				$writableAccount = "\nexten => " . $account['phone'] . ",1,Dial(SIP/" . $account['ringsOn'] . ",30,t);";
				$confFile .= $writableAccount;
			}
		}
		
		$confFile .= "\n";
		
		foreach( $extensions as $extension )
		{
			$writableExtension = "\nexten => _" . $extension['name'] . ",1,Dial(SIP/" . $extension['name'] . ",40,t);";
			$confFile .= $writableExtension;
		}
		
		return $confFile;
	}
	
	/*
		La funzione riceve il nome del file di configurazione e il suo nuovo contenuto, entrambi sottoforma di stringa. Scrive i contenuti nella copia locale del file e poi la sposta nella cartella SETTINGS_FILE_PATH.
		Torna true in caso di riuscita, false in caso di errore.
	*/
	function writeConf($confName, $newFile)
	{
		$result = true;
		$output = null;
		
		if( !file_put_contents($confName, $newFile) )
			$result = false;
		$output = shell_exec("sudo mv " . $confName . " " . SETTINGS_FILE_PATH . $confName . " 2>&1");
		$output .= shell_exec("sudo chown asterisk " . SETTINGS_FILE_PATH . $confName . " 2>&1");
		$output .= shell_exec("sudo service asterisk restart");
		
		if( $output )
			$result = false;
		
		return $result;
	}
	
	$parameters = array();
	
	$allAsteriskSettings = $asteriskSettings->find();
	
	foreach( $allAsteriskSettings as $asteriskSetting )
		$parameters[$asteriskSetting['name']] = $asteriskSetting['value'];
	
	$allAccounts = $sipAccounts->find();

	$allExtensions = $extensions->find();
		
	$sipConf = openConf("sip.conf");
	$extensionsConf = openConf("extensions.conf");
	
	$sipConf = writeParametersToSipConf($parameters, $sipConf);
	$sipConf = addAccountsToSipConf($allAccounts, $sipConf);
	$sipConf = addExtensionsToSipConf($allExtensions, $sipConf);
	$extensionsConf = fixExtensionsConf($allAccounts, $allExtensions, $extensionsConf);
	
	echo $extensionsConf;
	
	writeConf("sip.conf", $sipConf);
	writeConf("extensions.conf", $extensionsConf);
	
	$asteriskSettingsBackup->remove(array());
	$extensionsBackup->remove(array());
	$sipAccountsBackup->remove(array());
	
	foreach( $allAsteriskSettings as $newSetting )
	{
		$asteriskSettingsBackup->insert($newSetting);
	}
	
	foreach( $allAccounts as $newAccount )
	{
		$sipAccountsBackup->insert($newAccount);
	}
	
	foreach( $allExtensions as $newExtension )
	{
		$extensionsBackup->insert($newExtension);
	}
	
	header("Location: /");
?>