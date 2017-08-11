<?php
$result = $client->call("GetAllPackages",SubscriptionDNA_wrapAsSoap(array($_SERVER['REMOTE_ADDR'])));
$packages = SubscriptionDNA_parseResponse($result);
$login_name = $_SESSION['login_name'];
?>

<div style="color:#990000">
<h4><?php echo($_POST["response"]); ?></h4>
</div>
<?php
if(count($packages)>0)
{
$newPackages=0;
?>

<form name='customSubscribeForm' action='https://<?php echo($GLOBALS['SubscriptionDNA']['Settings']['TLD']) ; ?>.xsubscribe.com/widgetvalidate/remoteSubscribeServiceHandlerP' method='POST'>
    <table id="packagesList" cellpadding="3" width="100%">
        
		<input type='hidden' name='login_name' value='<?= $_SESSION['login_name']?>'>
		<input type='hidden' name='password' value='<?= $_SESSION['password']?>'>
        <tr valign=top>
            <td colspan="3"><b>Subscription Plans:</b></td>
		</tr>
		<tr valign=top>			
            <td colspan="3">
				<div style="border: 1px solid gray; padding: 5px;">
				<!-- height: 150px; overflow: auto; -->
				<?php 
				foreach($packages as $package)
				{
					if(!in_array($package["service_id"],$alreadySigned))
					{
					$newPackages++;
					?>
					<div id="innerDiv">
					<strong><input type="checkbox" name="packages[]" id="packages_<?php echo($package["id"]); ?>"  value="<?php echo($package["service_id"]); ?>;<?php echo($package["billing_routine_id"]); ?>" <?php if($package["defaultval"]=="Yes") echo("checked");  ?>  ><?php echo($package["package_name"]);  ?></strong>
						<div style="margin-left:20px;"><?php echo($package["package_description"]); ?></div><br>
					</div>
					<?php
					} 
				}
				?>
				</div>
				<br>
			</td>
        </tr>
		<?php
		if($ccinfo)
		{
		?>
        <tr valign=top>
            <td colspan="3">
			<input type='checkbox' name='cc_on_file' <?php if($_POST["cc_on_file"]=="1" or $ccinfo) echo("checked"); ?> value='1' onclick="hideShowCCInfo(this.checked);">Use Existing Credit Card<br>
		</td>
		</tr>	
        <tr valign=top id="existingCCInfo">
            <td>
			<b>Payment Method:</b>
			<td>
			<td>
			<select name="ccid" id="ccid" style="width:250px;">
			<?php
			foreach($ccList as $ccdetail)
			{
				?>
				<option value="<?php echo($ccdetail["ccid"]); ?>"><?=$ccdetail["card_number"]?> | <?=$ccdetail["expire_date"]?> | <?=$ccdetail["card_type"]?></option>
				<?php
			}
			?>
			</select>
			</td>
        </tr>
        <?php
		$display="none";
		}
		
		include 'cc_info_fields.php';
		?>
		<tr>
           <td colspan="3"><input type='submit' name='x_submit' value='Submit'>&nbsp;</td>
        </tr>
    </table>
</form>
<script>
<?php 
if($newPackages==0)
{
	echo("document.getElementById('packagesList').style.display='none';");
}
?>
</script>
<?php
}
?>

</div>
