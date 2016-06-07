<?php

$services = shell_exec("sudo /usr/sbin/asterisk -r -x 'sip show peers' 2>&1");
$process_list = explode("\n", $services);
// Controllo nella lista dei processi
foreach ($process_list as $process) {
	$parts = preg_split('/\s+/', $process);
	print_r($parts);
}

?>