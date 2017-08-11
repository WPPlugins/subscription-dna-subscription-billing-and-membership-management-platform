<?php
	if($_REQUEST["innerAction"]=="delete")
	{
		@mysql_query("delete from ".$GLOBALS['SubscriptionDNA']['Variables']['Table']." where ID='".$_REQUEST["package_id"]."'");
	}
	else if($_REQUEST["innerAction"]=="add")
	{
		@mysql_query("insert into ".$GLOBALS['SubscriptionDNA']['Variables']['Table']." set name='".$_REQUEST["name"]."',defaultval='".$_REQUEST["defaultval"]."',service_id='".$_REQUEST["service_id"]."',billing_routine_id='".$_REQUEST["billing_routine_id"]."'");
	}
	else if($_REQUEST["innerAction"]=="update")
	{
		@mysql_query("update ".$GLOBALS['SubscriptionDNA']['Variables']['Table']."  set name='".$_REQUEST["name"]."',defaultval='".$_REQUEST["defaultval"]."',service_id='".$_REQUEST["service_id"]."',billing_routine_id='".$_REQUEST["billing_routine_id"]."' where ID='".$_REQUEST["package_id"]."'");
	}

	$wsdl =$GLOBALS['SubscriptionDNA']['WSDL_URL'];
	$client = new nusoap_client($wsdl,true);
	$serviceArray=array();
	$billingArray=array();
	$serviceArray = $client->call("GetAllServices",SubscriptionDNA_wrapAsSoap(array($_SERVER['REMOTE_ADDR'])));
	$serviceArray = SubscriptionDNA_parseResponse($serviceArray);
	if($serviceArray["errcode"]=="-51")
	{
		echo("Error getting services list: ".$serviceArray["errdesc"]."<br>");	
		$serviceArray=array();
	}
	$billingArray = $client->call("GetAllBillingRoutines",SubscriptionDNA_wrapAsSoap(array( $_SERVER['REMOTE_ADDR'])));
	$billingArray = SubscriptionDNA_parseResponse($billingArray);
	if($billingArray["errcode"]=="-51")
	{
		echo("Error getting billing routines list: ".$billingArray["errdesc"]."<br>");	
		$billingArray=array();
	}

?>
<form  id="SubscriptionDNA_list_form" name="SubscriptionDNA_list_form" method="post" innerAction=""> 
    <?php
    $Terms = @mysql_query("select * from ".$GLOBALS['SubscriptionDNA']['Variables']['Table']." order by `name` ASC") ;
    ?>
    <table class="widefat post fixed" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th class="manage-column column-cb check-column" id="dir" scope="col"><input type="checkbox" /></th>
                <th class="manage-column" id="cat_id" scope="col" width="30">ID</th>
                <th class="manage-column" id="cat_desc" scope="col" >Name</th>
                <th class="manage-column" id="cat_desc" scope="col" >Service</th>
                <th class="manage-column" id="cat_desc" scope="col" >Billing</th>
                <th class="manage-column" id="cat_desc" scope="col" >Default</th>
            </tr>
        </thead>

        <tfoot>

            <tr>
                <th scope="col" id="dir" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
                <th class="manage-column" id="cat_id" scope="col" align="right">ID</th>
                <th class="manage-column" id="cat_desc" scope="col" >Name</th>
                <th class="manage-column" id="cat_desc" scope="col" >Service</th>
                <th class="manage-column" id="cat_desc" scope="col" >Billing</th>
                <th class="manage-column" id="cat_desc" scope="col" >Default</th>
            </tr>

        </tfoot>

        <tbody>

<?php 
if($Terms) {

                while ($Term=@mysql_fetch_array($Terms) ) {

                    $Class = ( 'alternate' == $Class ) ? '' : 'alternate' ;
                    $Edit = '<a href="?package_id=' . $Term['ID'] . '&page=subscription-dna-subscription-billing-and-membership-management-platform/SubscriptionDNA.php&manage_packages=1&innerAction=edit" class="edit" title="Edit"><img src="'.get_option("home").'/wp-content/plugins/SubscriptionDNA/images/edit.gif" /></a>' ;
                    $Delete = '<a onClick="if(confirm(\'Are you sure\')){return true}else{return(false);}" href="?package_id=' . $Term['ID'] . '&page=subscription-dna-subscription-billing-and-membership-management-platform/SubscriptionDNA.php&manage_packages=1&innerAction=delete" class="delete" title="Delete"><img src="'.get_option("home").'/wp-content/plugins/SubscriptionDNA/images/delete.gif" /></a>' ;

					foreach($serviceArray as $v)
					{
						if($Term["service_id"]==$v["sid"])
						{
							$service=$v["service_name"];
							break;
						}
					}
					foreach($billingArray as $v)
					{
						if($Term["billing_routine_id"]==$v["uid"])
						{
							$billing=$v["routinename"];
							break;
						}
					}
                    ?>

            <tr  id="cat-<?php echo $Term['ID'] ; ?>" class="<?php echo $Class ; ?>">
                <th scope="row" class="check-column">
                    <input type="checkbox" name="dir_action[]" id="dir_action_<?php echo $Term['ID'] ; ?>" value="<?php echo $Term['ID'] ; ?>">
                </th>
                <td class="manage-column column-comments num">
        <?php echo $Term['ID'] ; ?>
                </td>
                <td class="manage-column column-title">
                            <?php echo $Term['name'] ; ?>
                    <div class="row-actions">
                        <span class='edit'><?php echo $Edit ; ?> | </span><span class='delete'><?php echo $Delete ; ?></span></div>
                </td>
                <td class="manage-column column-title">
        <?php echo $service ; ?>
                </td>
                <td class="manage-column column-title">
        <?php echo $billing ; ?>
                </td>
                <td class="manage-column column-title">
        <?php echo $Term['defaultval'] ; ?>
                </td>

            </tr>
        <?php
    }
}

            //end loop here
            ?>

        </tbody>
    </table>
</form>
<?php
if (@mysql_num_rows ( $Terms )<1 ) 
{
	echo '<p>There are no Packages in the database.  Add one Below!</p>' ;
}
?> 


<div id="icon-edit" class="icon32"><br /></div>
<?php
if($_REQUEST["innerAction"]=="edit")
{
	$package=@mysql_fetch_array(@mysql_query("select * from ".$GLOBALS['SubscriptionDNA']['Variables']['Table']." where ID='".$_REQUEST["package_id"]."'"));
?>
<h3>Update Package</h3>
<form method="post" name="customSubscribeForm" innerAction="?page=subscription-dna-subscription-billing-and-membership-management-platform/SubscriptionDNA.php&manage_packages=1" > 
<input type="hidden" name="innerAction" value="update" />
<input type="hidden" name="package_id" value="<?php echo($_REQUEST["package_id"]); ?>" />
<?php
}
else
{
?>
<h3>Create New Package</h3>
<form method="post" name="customSubscribeForm" innerAction="" onSubmit=""> 
<input type="hidden" name="innerAction" value="add" />
<?php
}
?>
<table border="0" width="100%">
<tr>
	<td width="14%" nowrap="nowrap">Package Name:</td>
	<td width="38%"><input name="name" id="name" value="<?php echo($package["name"]); ?>" style="width:250px;">
</td>
</tr>
<tr>
	<td width="14%" nowrap="nowrap">Default Package:</td>
	<td width="38%"><input type="radio" name="defaultval" value="Yes" <?php if($package["defaultval"]=="Yes")echo("checked"); ?>> Yes <input type="radio" name="defaultval" value="No"  <?php if($package["defaultval"]=="No")echo("checked"); ?>> No
</td>
</tr>
<tr>
<td>Services:</td>
<td>
<select name="service_id" id="service_id" style="width:250px;">
<? foreach($serviceArray as $v){
if($v){
?>
<option label="<?=$v["service_name"]; ?>" value="<?=$v["sid"] ?>"  <?php if($package["service_id"]==$v["sid"])echo("selected"); ?>><?=$v["service_name"]; ?></option>
<? 
}

}
?>
</select>
</td>
<td><span id="service_id_lbl_error" class="lblErr"></span></td> 
</tr>

<tr>                    
<td width="14%" nowrap="nowrap">Billing Routine:</td>
<td width="38%"><select name="billing_routine_id" id="billing_routine_id" style="width:250px;">
<? foreach($billingArray as $v){ 
if($v){
?>
<option label="<?=$v["routinename"]; ?>" value="<?=$v["uid"] ?>"   <?php if($package["billing_routine_id"]==$v["uid"])echo("selected"); ?>><?=$v["routinename"]; ?></option>
<? 
}
} ?>
</select>
</td>
<td><span id="billing_routine_id_lbl_error" class="lblErr"></span></td> 
</tr>
<tr>
<td></td><td>
<?php
if($_REQUEST["innerAction"]=="edit")
{
?>
<input type="submit" value="Update" name="cmdSave" id="cmdSave" style="width:100px;" class="button-secondary innerAction" />
<input type="button" value="Cancel" name="cmdSave" id="cmdSave" style="width:100px;" class="button-secondary innerAction" onclick="location.href='?page=subscription-dna-subscription-billing-and-membership-management-platform/SubscriptionDNA.php&manage_packages=1';" />
<?php
}
else
{
?>
<input type="submit" value="Save" name="cmdSave" id="cmdSave" style="width:100px;" class="button-secondary innerAction" />
<?php
}
?>

</td>
</tr>
</table>
</form>
 
