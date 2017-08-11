<?php
	require_once(dirname(__FILE__).'/lib/nusoap.php');
	$categories = get_categories("hide_empty=0");
	if($_POST["cmdServices"])
	{
		foreach($categories as $key=>$Term) 
		{
			update_option('SubscriptionDNA_-_Settings_-_Cat'.$Term->cat_ID, $_POST["service_id_".$Term->cat_ID]) ;
		}
	}
	$client = new nusoap_client($GLOBALS['SubscriptionDNA']['WSDL_URL'],true);
	$serviceArray=array();
	$serviceArray = $client->call("GetAllServices",SubscriptionDNA_wrapAsSoap(array($_SERVER['REMOTE_ADDR'])));
	$serviceArray = SubscriptionDNA_parseResponse($serviceArray);
	if($serviceArray["errcode"]=="-51")
	{
		echo("Error getting services list: ".$serviceArray["errdesc"]."<br>");	
		$serviceArray=array();
	}
	$defaults = array(
		'numberposts' => 1115, 'offset' => 0,
		'category' => $_REQUEST["post_cat_id"], 'orderby' => 'post_date',
		'order' => 'DESC', 'include' => '',
		'exclude' => '', 'meta_key' => '',
		'meta_value' =>'', 'post_type' => 'post',
		'suppress_filters' => true
	);
?>
<div id="icon-edit" class="icon32"><br /></div>
<h3>Manage categories access level</h3>
<form  id="SubscriptionDNA_list_form" name="SubscriptionDNA_list_form" method="post" innerAction=""> 
<div align="right">
<input type="submit" value="Save Services" name="cmdServices" id="cmdServices" class="button-secondary action" />
</div>
    <table class="widefat post" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th class="manage-column" id="cat_desc" scope="col" >Name</th>
                <th class="manage-column" id="cat_desc" scope="col" >Description</th>
                <th class="manage-column" id="cat_desc" scope="col" >Slug</th>
                <th class="manage-column" id="cat_desc" scope="col" >Category Access</th>
                <th class="manage-column" id="cat_desc" scope="col" >Individual Posts</th>
                <th class="manage-column" id="cat_desc"  scope="col" >Services</th>
            </tr>
        </thead>

        <tfoot>

            <tr>
                <th class="manage-column" id="cat_desc" scope="col" >Name</th>
                <th class="manage-column" id="cat_desc" scope="col" >Description</th>
                <th class="manage-column" id="cat_desc" scope="col" >Slug</th>
                <th class="manage-column" id="cat_desc" scope="col" >Category Access</th>
                <th class="manage-column" id="cat_desc" scope="col" >Posts Access</th>
                <th class="manage-column" id="cat_desc"  scope="col" >Services</th>
            </tr>

        </tfoot>

        <tbody>

<?php 

if($categories) {

                foreach($categories as $key=>$Term) {
					$cats=get_option("SubscriptionDNA_cats");
					$checked="Public";
					$posts='<a href="options-general.php?page=subscription-dna-subscription-billing-and-membership-management-platform/SubscriptionDNA.php&manage_posts=1&post_cat_id='.$Term->cat_ID.'">Posts Access</a>';
					if(is_array($cats))
					{
						if(in_array($Term->cat_ID,$cats))
						{
							$checked="Member Only";
							$posts="Member Only";
						}	
					}
					$cat='<a href="options-general.php?page=subscription-dna-subscription-billing-and-membership-management-platform/SubscriptionDNA.php&manage_cats=1&cat_id='.$Term->cat_ID.'">'.$checked.'</a>';

                    $Class = ( 'alternate' == $Class ) ? '' : 'alternate' ;
					
					$services=get_option('SubscriptionDNA_-_Settings_-_Cat'.$Term->cat_ID);
					if(!is_array($services))
						$services=array();
					?>

            <tr  id="cat-<?php echo $Term->cat_ID ; ?>" class="<?php echo $Class ; ?>">
                <td>
        <?php echo $Term->name ; ?>
                </td>
                <td class="manage-column">
				<?php echo $Term->category_description ; ?>                             
                </td>
                <td class="manage-column">
	         <?php echo $Term->slug ; ?>
                </td>
                <td class="manage-column">
		        <?php echo $cat ; ?>
                </td>
                <td class="manage-column">
		        <?php echo $posts ; ?>
				<?php
				if($checked=="Public")
				{
					$cc=0;
					$defaults["category"]=$Term->cat_ID;
					$c_posts = get_posts($defaults);
					foreach($c_posts as $c_post)
					{
						$memberpage=get_post_meta($c_post->ID, "_SubscriptionDNA_yes", true);
						if($memberpage)
						$cc++;
					}
					echo("($cc currently set)");
				}	
				?>
                </td>
                <td>
				<?php
				if($checked=="Member Only")
				{
				?>
					<select name="service_id_<?php echo $Term->cat_ID ; ?>[]" id="service_id_<?php echo $Term->cat_ID ; ?>" multiple="multiple" size="4" style="height:80px;">
					<option value=""></option>
					<? 
					foreach($serviceArray as $v)
					{
						?>
						<option label="<?=$v["service_name"]; ?>" value="<?=$v["sid"] ?>"  <?php if(in_array($v["sid"],$services))echo("selected"); ?>><?=$v["service_name"]; ?></option>
						<? 
					}
					?>
					</select>
				<?php
				}
				else
				echo("None");
				?>	
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
if (count( $categories )<1 ) 
{
	echo '<p>There are no Categories in the database.</p>' ;
}
?> 
