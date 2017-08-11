<div align="center">
<?php
require_once(dirname(__FILE__).'/lib/nusoap.php');
$wsdl =$GLOBALS['SubscriptionDNA']['WSDL_URL'];
if($_POST["cmdLogin"])
{

		if(!isset($_SESSION['user_session_id']))
		{
			$client = new nusoap_client($wsdl,true);
				
			$ipAddress = $_SERVER['REMOTE_ADDR'];
			$result = $client->call("RemoteLogin", SubscriptionDNA_wrapAsSoap(array($_POST['login_name'], $_POST['password'], $ipAddress, 0)));
			$result = SubscriptionDNA_parseResponse($result);
			if($result["errcode"] == 1)
			{
				$_SESSION['user_session_id'] = $result["user_session_id"];
				$_SESSION['login_name'] = $_POST['login_name'];
				$_SESSION['password'] = $_POST['password'];

				SubscriptionDNA_Update_Subscription($client);
				
				if($_REQUEST["redirect_to"]!="")
					$url=get_permalink($_REQUEST["redirect_to"]);
				else
					$url=get_permalink($GLOBALS['SubscriptionDNA']['Settings']['MainMenu']);
								
				?>
                <script>
				location.href='<?php echo($url); ?>';					
				</script>
                <?php
			}	
			else
			{	
				print_r("<div id='dna-login'><div id='failure'>" . $result["errdesc"] . " Please try again.</div></div>"); 
			}	
		}
		else
		{
			?>
			<script>
			location.href='<?php echo(get_permalink($GLOBALS['SubscriptionDNA']['Settings']['MainMenu'])); ?>';					
			</script>
			<?php
			die();
		}	


}
else if($_POST["status"])
{
?>
	<div align="center">
	<h1>Access Confirmation</h1>
	<b>You've successfully registered.  A confirmation email has been sent for your records.</b>
	
	<p>
	
	</div>
	Please Login here!<br>

<?
}
else if($_POST)
{
}
if($_REQUEST["action"]=="logout")
{
		$client = new nusoap_client($wsdl,true);
		
		$session_id = $_SESSION['user_session_id'];

		$result = $client->call("RemoteLogout", SubscriptionDNA_wrapAsSoap(array($session_id)));
		
		//Destory Session
		$_SESSION['user_session_id'] = "";
		$_SESSION["login_name"] = "";
		$_SESSION['password'] = "";
		$_SESSION['subscribed_categories']="";
		$_SESSION['subscribed_services']="";
		unset($_SESSION['user_session_id']);
		unset($_SESSION["login_name"]);
		unset($_SESSION["password"]);
		unset($_SESSION['subscribed_categories']);
		unset($_SESSION['subscribed_services']);
		?>
		<script>
		location.href='<?php echo(get_permalink($GLOBALS['SubscriptionDNA']['Settings']['MainMenu'])); ?>';					
		</script>
		<?php

}
?>

<form name='login' action='' method='POST'>
<input type="hidden" value="<?php echo($_REQUEST["redirect_to"]); ?>" name="redirect_to" />
<table id='dna-login'>
<tr><td class="dna-heading">Username:</td><td><input type='text' name='login_name' value='' size='10' maxlength='50'></td></tr>
<tr><td class="dna-heading">Password:</td><td><input type='password' name='password' size='10' maxlength='8'></td></tr>
<tr><td></td><td><input type='submit' value='Login' name="cmdLogin"></td></tr>
</table>
</form>
</div>