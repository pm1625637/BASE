<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>XLDent Dev Login Form</title>
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <style type="text/css">
     body{background-color: #ccc;}
     .bslf{
  	width: 350px;
	margin: 120px auto;
	padding: 25px 20px;
	background: #eee;
	box-shadow: 2px 2px 4px #000;
	border-radius: 5px;
	color: #000;
     }
     .bslf h2{
  	margin-top: 0px;
	margin-bottom: 15px;
	padding-bottom: 5px;
	border-radius: 10px;
	border: 1px solid #25055f;
     }
     .bslf a{color: #783ce2;}
     .bslf a:hover{
  	text-decoration: none;
    	color: #000;
     }
     .bslf .checkbox-inline{padding-top: 7px;}
  </style>
</head>
<body>
  <div class="bslf">
  <!--<h2 class="text-center"><?php echo trim(DEFAULTCONTROLLER,'/'); ?></h2>-->
    <h2 class="text-center"><?php echo 'XLDent Analysis'; ?></h2>
	 <h6 class="text-center"><?php echo (isset($msg))? $msg:'$msg'; ?></h6>
    <form action="<?=$action?>" method="post">     
      <div class="form-group">
        <input type="text" class="form-control" placeholder="Username" id="username" name="username" required="required">
      </div>
      <div class="form-group">
        <input type="password" class="form-control" placeholder="Password"  id="password" name="password" required="required">
      </div>
      <div class="form-group clearfix">
      	<label class="checkbox-inline pull-left"><input type="checkbox"> Remember me</label>
        <button type="submit" class="btn btn-primary pull-right">Log in</button>
      </div>
     <!--<div class="clearfix">
        <a href="#" class="pull-left">Forgot Password?</a>
        <a href="#" class="pull-right">By Pierre Martin</a>
      </div>  -->      
    </form>
  </div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>