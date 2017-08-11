<? 
require_once(dirname(__FILE__).'/lib/nusoap.php');
$wsdl =$GLOBALS['SubscriptionDNA']['WSDL_URL'];
include_once(dirname(__FILE__).'/session.php');
	$client = new nusoap_client($wsdl,true);
	$session_id = $_SESSION['user_session_id'];
	$login_name = $_SESSION['login_name'];
		
	$result = $client->call("ViewTrans", SubscriptionDNA_wrapAsSoap(array($login_name)));
	$result = SubscriptionDNA_parseResponse($result);
	if(count($result)<1){
		echo '<div id="dna-login"><div id="failure">No Transaction Exists.</div></div>';
	}
		
	?>
	
<table id="dna-subscriptions" width="100%" cellpadding="3" cellspacing="0">
<tr><td colspan="5"></td></tr>
			<tr>
				<th>Invoice</th>
				<th>Payment Method</th>
				<th>Amount</th>
				<th>Txn Date</th>
				<th>Status</th>
			</tr>	
				
	<?php		
		$resultRows = $result;
		$page_file="?";
		include 'pageheader.php';
		for($i = $limitFrom; $i <$upto; $i++)
		{
			$resultRow = $resultRows["record".$i];
			if($resultRow)
			{
				//print_r($resultRows["record".$i]);
				if($resultRow["response_code"] == '')	
				{
						$resultRow["response_code"] = "&nbsp;";
				}
				?>
					<tr onmouseover="this.style.backgroundColor='#ebebeb'" onmouseout="this.style.backgroundColor='';">
						<td><?php echo(str_pad($resultRow["invoice_id"],5,"0",STR_PAD_LEFT)); ?></td>
						<td>
							<?php if ($resultRow["ischeckmo"]=="1") { ?>
							<tr><td>Paid by Cash/Check/MO</td></tr>
							<? } else { ?>
							<?php echo($resultRow["card_number"]); ?> | <?php echo($resultRow["expire_date"]); ?> | <?php echo($resultRow["card_type"]); ?>
							<?php
							}
							?>
						</td>
						
						<td>$<?php echo($resultRow["amount"]); ?></td>
						<td><?php echo($resultRow["invoice_date"]); ?></td>
					<td><?php echo($resultRow["payment_status"]); ?> <?php if ($resultRow["response_code"] != "1") { ?>(<?php echo("<a href='#a' title='". $resultRow["response_message"] . "' >" . $resultRow["response_code"] ); ?></a>) <? } ?></td>
					</tr>	
				<?php
			}
		}
	
		echo '</table>';		
	?>
	<?php include "pagefooter.php";?>
	
