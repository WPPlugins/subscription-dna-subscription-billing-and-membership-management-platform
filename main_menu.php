       
            <table border="0" width="100%" cellpadding="0" cellspacing="0"><tr>
				<td style="padding-left:5px;" height="30" class="BigText"><?= $page_title?></td>
				<td align='right' class="BigTextUnBold" style="padding-right:5px;" nowrap>
					<?php if(isset($_SESSION["user_session_id"])){ ?>
						<b>You are logged in as: <?= $_SESSION['login_name']?></b>					
					<?php } ?>
				</td>
			</tr></table>



<div align="center">
<div id="dna-nav">
					<?php
						$arranged=array();
						foreach ($GLOBALS['SubscriptionDNA']['DefaultPages'] as $page=>$title)
						{
							$arranged[$page]=$GLOBALS['SubscriptionDNA']['Settings'][$page."_Order"];
						}	
						//print_r($arranged);
						asort($arranged);
						//print_r($arranged);
							
						$count=0;
						foreach ($arranged as $page=>$order)
						{
							if(((get_post_meta($GLOBALS['SubscriptionDNA']['Settings'][$page], "_SubscriptionDNA_yes", true) and $_SESSION["user_session_id"]!="") or(!get_post_meta($GLOBALS['SubscriptionDNA']['Settings'][$page], "_SubscriptionDNA_yes", true) and $_SESSION["user_session_id"]=="")) && $GLOBALS['SubscriptionDNA']['Settings'][$page."_Home"]=="1")
							{
								$home=get_option("home");
								$url=get_permalink($GLOBALS['SubscriptionDNA']['Settings'][$page]);
								if($GLOBALS['SubscriptionDNA']['Settings'][$page."_HTTPS"])
								$url=str_replace($home,$GLOBALS['SubscriptionDNA']['Settings']['SSL'],$url);
							?>                
							<a class="hyper" href="<?php echo($url); ?>"><?php echo get_the_title($GLOBALS['SubscriptionDNA']['Settings'][$page]); ?></a>
							<?php
							if($count==3)
								echo("<br>");
							else if($_SESSION["user_session_id"]=="" and $count==2)
							echo("");
							else 
								echo(" | ");
							$count++;
							}
						}
						if($_SESSION["user_session_id"]!=""){
						?>
						<a class="hyper" href="<?php echo(get_permalink($GLOBALS['SubscriptionDNA']['Settings']['Login'])); ?>?&action=logout">Logout</a>  
						<?php
						}
						?>
				</td>
				</tr>
			</table>
</div>
</div>
<?php
if($GLOBALS["post"]->ID==$GLOBALS['SubscriptionDNA']['Settings']['MainMenu'])
{
require_once(dirname(__FILE__).'/lib/nusoap.php');
$login_name = $_SESSION['login_name'];
$client = new nusoap_client($GLOBALS['SubscriptionDNA']['WSDL_URL'],true);
$result = $client->call("SubscriptionInfo", SubscriptionDNA_wrapAsSoap(array($login_name)));
$result = SubscriptionDNA_parseResponse($result);
//print_r($result);
if(count($result)<1){
echo '&nbsp;&nbsp;<font color="#FF0000">No Subscriptions Found.</font><br />';
}
else
{
?>
<div id="dna-heading-sub"><br>You are subscribed to the following services:</div>

	<div style="border: solid 1px gray; padding: 4px; background-color: #ffffff;">
	<?php
	$resultRows = $result;
	for($i = 0; $i <count($resultRows); $i++)
	{
		$resultRow = $resultRows["record".$i];
		if(!$resultRow)
		break;
		if(trim($resultRow["status"])=='Active' or true)
		{
			?>
			<!--
			Use This ID to map services with contents
			<?php echo($resultRow["subid"]); ?>
			-->
			<strong><a href="<?php echo(get_permalink($GLOBALS['SubscriptionDNA']['Settings']['Subscriptions'])); ?>"><?php echo($resultRow["service_name"]); ?></a></strong><br />
			<?php if ($resultRow["service_description"]!="") { echo($resultRow["service_description"]); echo "<br>"; } ?>
			<?php if ($resultRow["billing_description"]!="") { echo "<i>"; echo($resultRow["billing_description"]); echo "</i><br />"; } ?>

<!--			<?php echo($resultRow["package_description"]); ?><br />-->
			
			<br>
			<?php
		}
	}
	?>
	</div>
	<?php
}
}
?>
