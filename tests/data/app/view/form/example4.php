<!DOCTYPE html>
<html lang="pl">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>site</title>

    <link href="http://127.0.0.1:8100/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://127.0.0.1:8100/css/default.css" rel="stylesheet">

    <script src="http://127.0.0.1:8100/js/jquery.min.js"></script>
    <script src="http://127.0.0.1:8100/js/bootstrap.min.js"></script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <link rel="shortcut icon" href="http://127.0.0.1:8100/favicon.ico" />
  </head>
  <body>

	<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="container">
		    <div class="navbar-header">
		      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-menu">
		        <span class="sr-only">Toggle navigation</span>
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
		        <span class="icon-bar"></span>
		      </button>
		      <a class="navbar-brand" title="site" href="http://127.0.0.1:8100"><img src="http://127.0.0.1:8100/img/logo.png"></a>
		    </div>

			<div class="collapse navbar-collapse" id="navbar-collapse-menu">

					<div class="nav navbar-nav navbar-right">
		<a href="http://127.0.0.1:8100/auth/login"><button type="button" class="btn btn-default navbar-btn pull-right"><span class="glyphicon glyphicon-log-in"></span> Zaloguj się</button></a>
	</div>

							</div>

		</div>
	</nav>

	<div class="container">


<div class="row">

<form method="POST" action="/form/complex" accept-charset="UTF-8" id="register" class="form-width-narrow well" role="form"><input name="_token" type="hidden" value="VsSbDK5vI0LE5rVULAX8Xd3AJ3iOPfKAksmLNxpW"><fieldset>

    <legend>Rejestracja</legend>


    <div class="form-group">
    	<label for="email" class="control-label">E-Mail</label>        <input class="form-control" required="1" name="email" type="email" id="email">    </div>

    <div class="form-group">
      <label for="password" class="control-label">Hasło</label>        <input class="form-control" required="1" name="password" type="password" value="" id="password">    </div>

    <div class="form-group">
    	<label for="password_confirmation" class="control-label">Powt&oacute;rz hasło</label>        <input class="form-control" required="1" name="password_confirmation" type="password" value="" id="password_confirmation">    </div>

	  <div class="checkbox">
	    <label>
        <input required="1" name="terms" type="checkbox" value="1"> Akceptuje warunki <a href="http://127.0.0.1:8100/terms">regulaminu</a>.
	    </label>
	  </div>

    <div class="form-group">
      <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span> Utwórz konto</button>
    </div>
  </fieldset>

</form>
</div>



	</div>

  </body>
</html>
 