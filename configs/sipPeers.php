<?php
    ini_set('display_errors', '1');
    error_reporting(E_ALL);

	$services = shell_exec("sudo /usr/sbin/asterisk -r -x 'sip show peers' 2>&1");
	$process_list = explode("\n", $services);
	
	$statuses = array();
	
	// Controllo nella lista dei processi
	foreach ($process_list as $process) {
		$parts = preg_split('/\s+/', $process);
		$name = explode("/",$parts[0])[0];
		//echo $name . "\n";
		
		$statuses[$name] = false;
		
		foreach( $parts as $peerInfo )
			if($peerInfo == "OK")
				$statuses[$name] = true; 
	}

?>