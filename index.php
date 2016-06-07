<?php
    //ini_set('display_errors', '1');
    //error_reporting(E_ALL);
    
    session_start();
    
    if( !isset($_SESSION['user']) )
    {
        header("Location: login.php");
    }
    else
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
    <title>Asterisk WebUI | Stato</title>
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
    <!-- jQuery 2.1.4 -->
    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
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
  <body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

      <!-- Main Header -->
      <header class="main-header">

        <!-- Logo -->
        <a href="/" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b>A</b>W</span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b>Asterisk</b>WebUI</span>
        </a>

        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
        </nav>
      </header>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">

        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">

          <!-- Sidebar user panel (optional) -->
          <div class="user-panel">
            <div class="pull-left image">
              <img src="dist/img/admin-512.png" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
              <p id="username"><?php echo $_SESSION['user']['username']?></p>
              <!-- Status -->
            </div>
          </div>

          <!-- Sidebar Menu -->
          <ul class="sidebar-menu">
            <li class="header">Impostazioni</li>
            <!-- Optionally, you can add icons to the links -->
            <li id="loadStatus"><a href="/"><i class="fa fa-info"></i> <span>Stato</span></a></li>
            <li id="loadContacts"><a href="#"><i class="fa fa-book"></i> <span>Rubrica</span></a></li>
            <li id="loadSipAccountsSettings"><a href="#"><i class="fa fa-users"></i> <span>Account SIP</span></a></li>
            <li id="loadAsteriskSettings"><a href="#"><i class="fa fa-asterisk"></i> <span>Asterisk</span></a></li>
            <li id="loadExtensionsSettings"><a href="#"><i class="fa fa-phone"></i> <span>Estensioni</span></a></li>
            <li class="treeview">
              <a href="#"><i class="fa fa-cog"></i> <span>Salvataggio</span> <i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <li id="saveSettings"><a href="configs/save.php">Salva</a></li>
                <li id="undoSettings"><a href="configs/loadConfig.php">Annulla Modifiche</a></li>
              </ul>
            </li>
            <li id="loadLogs"><a href="#"><i class="fa fa-file"></i> <span>File di Log</span></a></li>
            <li><a href="logout.php"><i class="fa fa-sign-out"></i> <span>Esci</span></a></li>
          </ul><!-- /.sidebar-menu -->
        </section>
        <!-- /.sidebar -->
      </aside>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1 id="page-title">
            Benvenuto!
            <small>Qui sono elencate le impostazioni del server.</small>
          </h1>
        </section>

        <!-- Main content -->
        <section class="content">
          <div id="current-page">
          <!-- Your Page Content Here -->
          
          <div class="box box-solid box-primary">
	        <div class="box-header">
	          <h3 class="box-title">Impostazioni del server</h3>
	        </div><!-- /.box-header -->
	        <div class="box-body">
	          <p><b>IP</b>: <?php echo $_SERVER['SERVER_ADDR'] ?></p>
	          <p><b>Porta</b>: 5060</p>
	          <p><b>NAT</b>: sì</p>
		    </div><!-- /.box-body -->
	      </div>
          
          <div id="dialer" class="box">
          		<div class="box-header with-border">
			  		<h3 class="box-title">Dialer</h3>
          		</div>
          		<form action="configs/call.php" method="post" class="form-inline">
	          		<div class="form-group">
			  			<input type="tel" class="form-control" id="telNum" name="telNum" placeholder="0123456789" maxlength="10" size="20" autofocus>
					</div>
					<select name="extension" class="form-control">
						<?php
							echo file_get_contents("http://localhost/configs/extensions.php?listExtensions=true");
						?>
  					</select>
					<button type="submit" class="btn btn-default bg-green" id="callSubmitBtn"><i class="fa fa-phone"></i></button>
				</form>
				<div class="dialer_btn_group">
				  	<span class="dialer_btn">1</i></span>
				  	<span class="dialer_btn">2</i></span>
				  	<span class="dialer_btn">3</i></span>
			  	</div>
			  	<br />
			  	<span class="dialer_btn">4</i></span>
			  	<span class="dialer_btn">5</i></span>
			  	<span class="dialer_btn">6</i></span>
			  	<br />
			  	<span class="dialer_btn">7</i></span>
			  	<span class="dialer_btn">8</i></span>
			  	<span class="dialer_btn">9</i></span>	
			  	<br />
			  	<span class="dialer_btn">*</i></span>
			  	<span class="dialer_btn">0</i></span>
			  	<span class="dialer_btn">#</i></span>
          </div>
          
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
?>