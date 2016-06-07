<?php
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
	define("CALL_FILE", "call.call");
	if( isset($_POST['telNum']) && isset($_POST['extension']) )
	{
		if( strlen($_POST['telNum']) < 4 )
			$content = "Channel: SIP/";
		else
			$content = "Channel: SIP/03411918025/";
		
		$content .= $_POST['telNum'] ."\nMaxRetries: 2\nRetryTime: 60\nWaitTime: 30\nContext: complic\nExtension: " . $_POST['extension'] ."\nPriority: 1";	
		file_put_contents(CALL_FILE, $content);
		echo shell_exec("sudo cp " . CALL_FILE . " /var/spool/asterisk/outgoing/ 2>&1");
		echo shell_exec("sudo rm -R /var/spool/asterisk/outgoing/" . CALL_FILE . " 2>&1 <<<EOFcpvoipEOF");
	}
	else
	{
		echo "nopeee";
	}
	header("Location: /index.php");
?>