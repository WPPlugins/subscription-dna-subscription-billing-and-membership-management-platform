<? 
	require_once(dirname(__FILE__).'/lib/nusoap.php');
	$wsdl =$GLOBALS['SubscriptionDNA']['WSDL_URL'];
	include_once(dirname(__FILE__).'/session.php');
	$client = new nusoap_client($wsdl,true);
	$session_id = $_SESSION['user_session_id'];
	//print_r($_SESSION);
	$login_name = $_SESSION['login_name'];	
	if($_POST['send']){
			if(empty($_POST['password'])) $_POST['password']=$_POST['oldpassword'];	
			
			/*$passwords = array("old_password"=>$_POST['oldpassword'],
			"new_password"=>$_POST['password']);*/	
										
			$old_password = $_POST['oldpassword'];
			$new_password = $_POST['password'];
			$result = $client->call("ChangePass", SubscriptionDNA_wrapAsSoap(array($login_name, $old_password, $new_password)));
		$result = SubscriptionDNA_parseResponse($result);
//	echo $result;exit;
			if($result["errcode"]!=11){
				$msg='<font color="#FF0000">'.$result["errdesc"].'</font>';
			}else{
				$msg='<font color="#009933">'.$result["errdesc"].'</font>';
			}
	}	
	$result=array();
		$result = $client->call("ProfileData",SubscriptionDNA_wrapAsSoap(array($login_name)));		
		$result = SubscriptionDNA_parseResponse($result);
		
	function dump( $txt ){
		echo "<pre>"; print_r($txt); echo "</pre>";
	}
	
	?>
    
<div id="dna-heading">Change Password</div>

		<form name="login_form" method="post" action="" onsubmit="return verify();">
			<table id="dna-profile" align="center">
				<tr>
					<td colspan="2" id="avail_msg"><?=$msg ?></td>
				</tr>
				<tr>
					<td>Old Password</td>
					<td><input name="oldpassword" id="oldpassword" type="password" />
				</tr>
				
				<tr>
					<td>New Password</td>
					<td><input name="password" id="password" type="password" /> * </td>
				</tr>
				<tr>
					<td>Re-enter Password</td>
					<td><input name="password2" id="password2" type="password" /> * </td>
				</tr>			
				<tr>
					<td></td>
					<td><input name="send" value="Submit" type="submit" /></td>
				</tr>
			</table>
		</form>

        
	<script language="javascript" type="text/javascript">
	
	function SubscriptionDNA_GetElement(id){	
		return document.getElementById(id);
	}
	
	function IsNumeric(strString){
		var strValidChars = "0123456789.-";
		var strChar;
		var blnResult = true;
		if (strString.length == 0) return false;
	
		//  test strString consists of valid characters listed above
		for (i = 0; i < strString.length && blnResult == true; i++){
			strChar = strString.charAt(i);
			if (strValidChars.indexOf(strChar) == -1){
				blnResult = false;
			}
		}
		return blnResult;
	}
	
	
	function check_special_chr(fld){
	
		var iChars = "~!@#$%^&*()+=-[]\\\';,./{}|\":<>?";	
		
		for (var i = 0; i < fld.length; i++) {		
			if (iChars.indexOf(fld.charAt(i)) != -1) {						
				return false;
			}
		}	
		return true;
	}
	
	function verify(){
		if(SubscriptionDNA_GetElement('oldpassword').value!="")
		{
			if(check_special_chr(SubscriptionDNA_GetElement('password').value)==false){	
				alert ("Your Password has special characters. \nThese are not allowed.\n Please remove them and try again.");
				SubscriptionDNA_GetElement('password').focus();
				return false;
			}
			if(SubscriptionDNA_GetElement('password2').value==""){
				alert("Please provide Re-type Passowrd.");
				SubscriptionDNA_GetElement('password2').focus();	
				return false;
			}else if(SubscriptionDNA_GetElement('password').value != SubscriptionDNA_GetElement('password2').value){
				alert("Password and Re Type Passowrd fields do not match");
				SubscriptionDNA_GetElement('password').value='';
				SubscriptionDNA_GetElement('password2').value='';
				SubscriptionDNA_GetElement('password').focus();
				return false;
			}
		}
		else
		{
				alert("Please enter old password");
				SubscriptionDNA_GetElement('oldpassword').focus();
				return false;
		}				
		return true;
	}
	</script>