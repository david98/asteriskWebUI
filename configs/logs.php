<?php
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
	if( isset($_GET['download']) && ($_GET['download'] == true) )
	{
		header('Content-type: text/plain');
		header('Content-Disposition: attachment; filename="logs.txt"');
		
		echo shell_exec("cat /var/log/asterisk/messages 2>&1");
		exit();
	}
	else
	{
		echo '<textarea readonly id="log" class="form-control" rows="25">';
			
		echo shell_exec("cat /var/log/asterisk/messages 2>&1");
			
		echo '</textarea><br><br><a href="configs/logs.php?download=true" id="downloadBtn" class="btn btn-primary"><i class="fa fa-download"></i> Download</a>';
	}
?>