<?php
require_once(dirname(__FILE__).'/lib/nusoap.php');
if($_REQUEST["save_cc_info"])
{
	include 'save_cc_info.php';
}
else
{
	$wsdl =$GLOBALS['SubscriptionDNA']['WSDL_URL'];
	$client = new nusoap_client($wsdl,true);
	$session_id = $_SESSION['user_session_id'];
	$login_name = $_SESSION['login_name'];
	if($_REQUEST["del_id"])
	{
	$result = $client->call("DeleteCCInfo",SubscriptionDNA_wrapAsSoap(array($_REQUEST["del_id"],$login_name)));
	}
	
	$result = $client->call("CCInfoData",SubscriptionDNA_wrapAsSoap(array($login_name)));
	$result = SubscriptionDNA_parseResponse($result);
?>
	<br><a href='?&save_cc_info=1'>Add New Credit Card</a><br><br>
<?php	

	if(count($result)<1){
	echo '&nbsp;&nbsp;<font color="#FF0000">No credit card found.</font><br />';
	}
	else
	{
		?>

		
<table id="dna-subscriptions" width="100%" cellpadding="3" cellspacing="0">
		<tr>
		<td colspan="6"><?=$_REQUEST['msg']; ?></td>
		</tr>
		<tr>	
		<!--				<th>CC ID</th>-->
		<th>Card Type</th>				
		<th>Name on Card</th>
		<th>Card Number</th>
		<th>Expiration Date</th>
		<th>Subscriptions</th>
		<th>Action</th>
		</tr>	
		
		<?php		
		
		$resultRows = $result;
		
		for($i = 0; $i <count($resultRows); $i++)
		{
		$resultRow = $resultRows["record".$i];

		?>
		<tr onmouseover="this.style.backgroundColor='#ebebeb'" onmouseout="this.style.backgroundColor=''">
		
		<?php echo "
		<!--<td>" . $resultRow["ccid"] . "</td>-->
			<td>" . $resultRow["card_type"] . "</td>
			<td>" . $resultRow["card_holder_name"] . "</td>
			<td>" . $resultRow["card_number"] . "</td>					
			<td>" . $resultRow["expire_date"] . "</td>
			<td>(<a href='/members/subscriptions.php'>" . $resultRow["no_of_subscriptions"] . "</a>)</td>
			<td><a href='?&save_cc_info=1&cid=" . $resultRow["ccid"] . "'>Edit</a> | <a onClick=\"if(!confirm('Are you sure you want to delete?')) return(false);\"  href='?&del_id=" . $resultRow["ccid"] . "'>Delete</a></td>
		</tr>";
		
		}
		
		echo '</table>';
	}
}

?>