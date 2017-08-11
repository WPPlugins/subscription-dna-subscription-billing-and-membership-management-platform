<?php
	require_once(dirname(__FILE__).'/lib/nusoap.php');

	$client = new nusoap_client($GLOBALS['SubscriptionDNA']['WSDL_URL'],true);
	$login_name = $_SESSION['login_name'];
	$alreadySigned=array();
	
	if($_REQUEST['status']){
		
		$result = $client->call("UpdateSubscriptionStatus",SubscriptionDNA_wrapAsSoap(array($login_name, $_REQUEST['subId'], $_REQUEST['status'])));
		$result = SubscriptionDNA_parseResponse($result);

		if($result["errcode"]!=8){
			$msg='<font color="#FF0000">'.$result["errdesc"].'</font>';
		}else{
			$msg='<font color="#009933">'.$result["errdesc"].'</font>';
		}
		
	}
	else if($_REQUEST['renew'])
	{
		if($_REQUEST['confirmation_page'] == 1)
		{
			$result = $client->call("RenewSubscriptionById",SubscriptionDNA_wrapAsSoap(array($_REQUEST['subId'], $_REQUEST['confirmation_page'], $login_name)));
			$result = SubscriptionDNA_parseResponse($result);
			
			?>
			

				<form method="post" action="?&subId=<?=$_REQUEST['subId']?>&renew=renew&confirmation_page=0">
                <h2>Renew Your Expired Subscription</h2>
                
                <p>
					<table align="center">
					<tr>
					<td valign="top" style="padding-right: 15px;">
						<table cellpadding="4">
						<tr><td>
						<b>Name:</b><br>
						<?=$result["first_name"]?> <?=$result["last_name"]?><br>
					
						<br>
						<b>Email:</b><br>
						<?=$result["email"]?><br>
						
						<br>
						<b>Login Name:</b><br>
						<?=$result["login_name"]?><br>
						
						</td></tr>
						</table>
					</td>
					<td valign="top" style="padding-left: 15px;">
						<table cellpadding="4">
						<tr><td valign="top">
                        <b>Subscription:</b><br />
                        	<?=$result["services"]?><br />
							<?=$result["billing_routine"]?>
                        </td>
						</tr>
						</table>
					</td>
					
					<td valign="top" style="padding-left: 15px;">
						<table cellpadding="4">
						<tr><td>
						<b>Payment Method:</b><br>
						<?=$result["card_holder_name"]?><br>
						<?=$result["card_type"]?> <?=$result["card_number"]?> | <?=$result["expmonth"]?>/<?=$result["expyear"]?><br>
						<input type="checkbox" name="paid_by_new_card" value="1" onclick="hideShowCCInfo(!this.checked);"> Use alternate payment method?
						</td></tr>
						
						</table>
			
					</td>
					</tr>						
					<tr><td colspan="4">
						<table width="100%">
						<?php
						$display="none";
						include 'cc_info_fields.php';
						?>
						</table>
					
						</td></tr>
						<tr><td><br>
						<input type="submit" value="Renew Subscription" /></td></tr>
					</table>
				</form>
			<?	
			exit;
		}
		else
		{
			$result = $client->call("RenewSubscriptionById", SubscriptionDNA_wrapAsSoap(array($_REQUEST['subId'], $_REQUEST['confirmation_page'], $login_name)));
			$result = SubscriptionDNA_parseResponse($result);
	
			if($result["errcode"]!=10){
				$msg='<font color="#FF0000">'.$result["errdesc"].', Please try again</font>';
			}else{
				$msg='<font color="#009933">'.$result["errdesc"].'</font>';
			}
		}	
	}
	else if($_REQUEST["update_cc"])
	{
		$result = $client->call("UpdateSubscriptionCardInfo", SubscriptionDNA_wrapAsSoap(array($login_name,$_REQUEST['subId'], $_REQUEST['update_cc'])));
		$result = SubscriptionDNA_parseResponse($result);
		if($result["errcode"]!=7){
			$msg='<font color="#FF0000">'.$result["errdesc"].', Please try again</font>';
		}else{
			$msg='<font color="#009933">'.$result["errdesc"].'</font>';
		}
	}

	SubscriptionDNA_Update_Subscription($client);

	$ccdetail = $client->call("CCInfoData",SubscriptionDNA_wrapAsSoap(array($login_name)));
	$ccList = SubscriptionDNA_parseResponse($ccdetail);
	if(count($ccList)>0)
	{
		$ccinfo=true;
		$ccdetail=$ccList["record0"];
	}
	else
	{
		$ccinfo=false;
	}


	$result = $client->call("SubscriptionInfo", SubscriptionDNA_wrapAsSoap(array($login_name)));
	$result = SubscriptionDNA_parseResponse($result);
//print_r($result);
	if(count($result)<1)
	{
		echo '&nbsp;&nbsp;<font color="#FF0000">No Subscription Found.</font><br />';
	}
	else
	{
	?>


		
        <table id="dna-subscriptions" width="100%" cellpadding="3" cellspacing="0">
			<tr>
				<td colspan="6"><?php echo($msg); ?></td>
			</tr>
			<tr>	
				<th>Subscription</th>
				<th>Start / Expiry Date</th>				
				<th>Status</th>
				<th>Action</th>
			</tr>	
				
	<?php		

	$resultRows = $result;
	for($i = 0; $i <count($resultRows); $i++)
	{
		$resultRow = $resultRows["record".$i];
		if(!$resultRow)
		break;
		if($resultRow["status"]=='Active')
		$_SESSION['subscription']="1";
		if($resultRow["status"]!="Unsubscribed")
		$alreadySigned[]=$resultRow["service_id"];	
		?>
			
			<tr>
			<td width="200"><b><?php echo $resultRow["service_name"]; ?></b><br>
			<?php if ($resultRow["service_description"]!="") { echo($resultRow["service_description"]); echo "<br>"; } ?>
			<?php if ($resultRow["billing_description"]!="") { echo "<i>"; echo($resultRow["billing_description"]); echo "</i><br />"; } ?>
			</td>
			

			<?php				
			echo "<td valign=top nowrap>" . substr($resultRow["subscription_date"],0,10)." / ".substr($resultRow["expires"],0,10). "<br>"; ?>
			
			<select name="card_id" id="card_id" onchange="if(confirm('Are you sure you want to change selected card for this subscription?')){location.href='?&subId=<?php  echo($resultRow["subid"]); ?>&update_cc='+this.value;}">
			<option value="">No Card Selected</option>
			<?php
			foreach($ccList as $ccdetail) {
			?>
			<option value="<?php echo($ccdetail["ccid"]); ?>" <?php if($ccdetail["ccid"]==$resultRow["ccid"]) echo("selected"); ?>><?=$ccdetail["card_number"]?> | <?=$ccdetail["expire_date"]?> | <?=$ccdetail["card_type"]?></option>
			<?php }	?>
			</select></td>
			<?php
			
			echo"<td valign=top>" . $resultRow["status"] . "</td>";
				
		if(trim($resultRow["status"])=='Active'){
			echo "<td valign=top><a onClick=\"if(!confirm('Are you sure you want to Unsubscribe?')) return(false);\" href='?&subId=" . $resultRow["subid"] . "&status=Unsubscribed'>Unsubscribe</a>&nbsp;| <a onClick=\"if(!confirm('Are you sure you want to Discontinue?')) return(false);\"  href='?&subId=" . $resultRow["subid"] . "&status=Discontinued'>Discontinue</a></td>";
		}else if($resultRow["status"]=='Discontinued'){
			echo "<td valign=top><a onClick=\"if(!confirm('Are you sure you want to Unsubscribe?')) return(false);\"  href='?&subId=" . $resultRow["subid"] . "&status=Unsubscribed'>Unsubscribe</a>&nbsp;| <a onClick=\"if(!confirm('Are you sure you want to Re-activate?')) return(false);\"  href='?&subId=" . $resultRow["subid"] . "&status=Active'>Re-activate</a></td>";
		}else if($resultRow["status"]=='Expired'){
			echo "<td valign=top><a onClick=\"if(!confirm('Are you sure you want to Renew?')) return(false);\" href='?&subId=" . $resultRow["subid"] . "&card_id=" . $resultRow["ccid"] . "&renew=renew&confirmation_page=0'>Renew</a></td>";
		}else{ echo "<td>&nbsp;</td>"; }
		echo "</tr>";
				
	}
	
	echo '</table>';
?>

<p>
<small><b>Please note that UNSUBSCRIBE will immediately terminate your subscription.  Therefore, please only UNSUBSCRIBE if you have decided for sure to permanently cancel the service. If you would like to cancel your subscription at the expiration of the current billing period (allowing you to finish your current subscription term), please click on DISCONTINUE. When you do so, you can change your mind until the expiration date by clicking on REACTIVATE to restore your subscription.</b></small>
</p>
<?php
}
include 'subscribe.php';
?>
