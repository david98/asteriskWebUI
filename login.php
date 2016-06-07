<?php
	ini_set('display_errors', '1');
    error_reporting(E_ALL);
    session_start();
    
    if( isset($_SESSION['user']) )
    	header("Location: configs/loadConfig.php");
	else if( !isset($_POST['username']) && !isset($_POST['password']) )
	{
?>
<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Asterisk WebUI</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
          page. However, you can choose any other skin. Make sure you
          apply the skin class to the body tag so the changes take effect.
    -->
    <link rel="stylesheet" href="dist/css/skins/skin-blue.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <script src="js/custom.js"></script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <!--
  BODY TAG OPTIONS:
  =================
  Apply one or more of the following classes to get the
  desired effect
  |---------------------------------------------------------|
  | SKINS         | skin-blue                               |
  |               | skin-black                              |
  |               | skin-purple                             |
  |               | skin-yellow                             |
  |               | skin-red                                |
  |               | skin-green                              |
  |---------------------------------------------------------|
  |LAYOUT OPTIONS | fixed                                   |
  |               | layout-boxed                            |
  |               | layout-top-nav                          |
  |               | sidebar-collapse                        |
  |               | sidebar-mini                            |
  |---------------------------------------------------------|
  -->
  <body class="hold-transition skin-blue layout-top-nav">
    <div class="wrapper">

      <!-- Main Header -->
      <header class="main-header">

        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Logo -->
		  <a href="/" class="logo">
        	<!-- mini logo for sidebar mini 50x50 pixels -->
			<span class="logo-mini"><b>A</b>W</span>
          	<!-- logo for regular state and mobile devices -->
          	<span class="logo-lg"><b>Asterisk</b>WebUI</span>
		  </a>

        </nav>
      </header>
      
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper"id="login-page-content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Login
          </h1>
        </section>

        <!-- Main content -->
        <section class="content center-box" id="login-page-content">

          <!-- Your Page Content Here -->
          
          <div class="box box-solid box-primary" id="login-box">
	        <div class="box-header">
	        </div><!-- /.box-header -->
	        <div class="box-body">
	          <form action="login.php" method="post" onsubmit="return checkForm()">
				  <div class="form-group">
				    <label for="username-field">Username</label>
				    <input type="text" class="form-control" id="username-field" placeholder="Username" name="username">
				  </div>
				  <div class="form-group">
				    <label for="password-field">Password</label>
				    <input type="password" class="form-control" id="password-field" placeholder="Password" name="password">
				  </div>
				  <?php
				  	if( isset($_GET['error']) )
				  		echo "\n<div class='callout callout-danger lead'>
    <h4>Errore!</h4>
    <p>
      Username o password errati.
    </p>
  </div>\n";
				  ?>
				  <button type="submit" class="btn btn-default">Login<div></div></button>
				</form>
	        </div><!-- /.box-body -->
	      </div>

        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->

      <!-- Main Footer -->
      <footer class="main-footer">
        <!-- To the right -->
        <div class="pull-right hidden-xs">
          Anything you want
        </div>
        <!-- Default to the left -->
        <strong>Copyright &copy; 2015 <a href="#">Company</a>.</strong> All rights reserved.
      </footer>

     
    <!-- REQUIRED JS SCRIPTS -->

    <!-- jQuery 2.1.4 -->
    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/app.min.js"></script>

    <!-- Optionally, you can add Slimscroll and FastClick plugins.
         Both of these plugins are recommended to enhance the
         user experience. Slimscroll is required when using the
         fixed layout. -->
  </body>
</html>
<?php
	}
	else
	{
		$mongodb = new MongoClient();
		$asteriskWebUi = $mongodb->asteriskWebUi;
		$users = $asteriskWebUi->users;
		
		$_POST = array_map('trim', $_POST);
		
		$user = $users->find(
			array(
				'username' => $_POST['username'],
			)
		);
		
		$user = $user->getNext();
		
		if( $user )
		{
			if( password_verify($_POST['password'], $user['password']) )
			{
				$_SESSION['user'] = $user;
				header("Location: configs/loadConfig.php");
			}
			else
				header("Location: login.php?error");
		}
		else
		{
			header("Location: login.php?error");
		}
	}
?>