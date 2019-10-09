<!DOCTYPE html>
<?php
session_start();
//echo($_SESSION["member_id"]);
//TODO - Change login code, hard-coded for prototyping - NdK
$configs = include('config.php');
if(!empty($_POST["login"])) {
	if(!empty($_POST["member_name"]) && strtolower($_POST["member_name"]) == $configs->username && $_POST["member_password"] == $configs->pwd) {
			$_SESSION["member_id"] = $_POST["member_name"];
			
			if(!empty($_POST["remember"])) {
				setcookie ("member_login",$_POST["member_name"],time()+ (10 * 365 * 24 * 60 * 60));
				setcookie ("member_password",$_POST["member_password"],time()+ (10 * 365 * 24 * 60 * 60));
			} else {
				if(isset($_COOKIE["member_login"])) {
					setcookie ("member_login","");
				}
				if(isset($_COOKIE["member_password"])) {
					setcookie ("member_password","");
				}
			}
		header('Location: /neemansec.php');
	} else {
		$message = "Invalid Login";
		$_SESSION["member_id"] = "";
	}
}
?>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <script src="scripts/jquery-3.2.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="styles/app.css">
    <title>NEEMAN LOGIN</title>
</head>
<body>
	<form action="" method="post" id="frmLogin">
		<table id="main">
			<tr>
				<td class="MainHead">
					<span>NEEMAN</span> <sup>&#8486;</sup> SYSTEM

				</td>
			</tr>
			<tr>
				<td class="login">
				<div class="error-message"><?php if(isset($message)) { echo $message; } ?></div>	
					<div class="field-group">
						<div><label for="login">Username</label></div>
						<div><input name="member_name" type="text" value="<?php if(isset($_COOKIE["member_login"])) { echo $_COOKIE["member_login"]; } ?>" class="input-field">
					</div>
					<div class="field-group">
						<div><label for="password">Password</label></div>
						<div><input name="member_password" type="password" value="<?php if(isset($_COOKIE["member_password"])) { echo $_COOKIE["member_password"]; } ?>" class="input-field"> 
					</div>
					<div class="field-group">
						<div><input type="checkbox" name="remember" id="remember" <?php if(isset($_COOKIE["member_login"])) { ?> checked <?php } ?> />
						<label for="remember-me">Remember me</label>
					</div>
					<div class="field-group">
						<div><input type="submit" name="login" value="Login" class="form-submit-button"></span></div>
					</div>       
				</td>
			</tr>
		</table>
	</form>
</body>
</html>