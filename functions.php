<?php
function SubscriptionDNA_Initialize ( )
{
	$GLOBALS['SubscriptionDNA']['DefaultPages']=array("MainMenu"=>"Member Home","Register"=>"Register","Login"=>"Login","ForgotPassword"=>"Forgot Password","EditProfile"=>"Profile","ChangePassword"=>"Change Password","ViewTransactions"=>"View Transactions","CreditCards"=>"Payment Methods","Subscriptions"=>"Subscriptions");	
	$GLOBALS['SubscriptionDNA']['Settings'] = SubscriptionDNA_Get_Settings (1) ;
	$GLOBALS['SubscriptionDNA']['WSDL_URL']="http://".$GLOBALS['SubscriptionDNA']['Settings']["TLD"].".xsubscribe.com/soap/soapbridge/wsdl";
	if (function_exists('register_sidebar_widget') && function_exists('register_widget_control') ) 
	{
		register_sidebar_widget("SubscriptionDNA Login", 'SubscriptionDNA_widget_login');
		register_widget_control("SubscriptionDNA Login", 'SubscriptionDNA_widget_login_control');
	}
	return TRUE ;
}
function SubscriptionDNA_widget_login($args)
{
	extract($args);
	if($_SESSION["user_session_id"]!="")
	return(true);
	echo $before_widget;
	include 'login.php';
	echo $after_widget;	
}
function SubscriptionDNA_widget_login_control()
{
	//echo("OK");
}
function SubscriptionDNA_Admin_Initialize() {
	global $wp_version;
	add_meta_box('SubscriptionDNA_admin_meta_box','SubscriptionDNA Options', 'SubscriptionDNA_admin_sidebar', 'page', 'side', 'high');
	add_action('save_post', 'SubscriptionDNA_admin_sidebar_save');
	add_action ('edit_category_form', 'SubscriptionDNA_admin_category_add');
	
		
	if($_REQUEST["post_id"]!="")
	{
		//die($_REQUEST["status"]);
		update_post_meta($_REQUEST["post_id"], "_SubscriptionDNA_yes", $_REQUEST["status"]);
	}
	else if($_REQUEST["cat_id"]!="")
	{
		$cats=get_option("SubscriptionDNA_cats");
		if(is_array($cats))
		{
			if(!in_array($_REQUEST["cat_id"],$cats))
				$cats[]=$_REQUEST["cat_id"];
			else
			{
				foreach($cats as $key=>$val)
				{
					if($val==$_REQUEST["cat_id"])
					unset($cats[$key]);
				}	
			}	
			
		}
		else
		{
			$cats=array();
			$cats[]=$_REQUEST["cat_id"];
		}
		update_option("SubscriptionDNA_cats",$cats);
		
		
		//wp_redirect("categories.php");
	}
}
function SubscriptionDNA_admin_category_add()
{
	?>
	<div id="memberOnly"><strong>After adding a new category, you can modify the access level using <a href="options-general.php?page=subscription-dna-subscription-billing-and-membership-management-platform/SubscriptionDNA.php&manage_cats=1">SubscriptionDNA</a></strong></div>
	<?php
}
function SubscriptionDNA_admin_sidebar($post)
{
	$memberpage=get_post_meta($post->ID, "_SubscriptionDNA_yes", true);
	
	$menu=get_post_meta($post->ID, "_SubscriptionDNA_menu",true);
	?>
	<div class="wrap" class="new-admin-wp25">
	<table width="100%" class="form-table1" cellpadding="0" cellspacing="5">
	<?php
	if(function_exists("SubscriptionDNA_admin_sidebar_wp25"))
	SubscriptionDNA_admin_sidebar_wp25();
	?>	
	<tr>
	<td><input type="checkbox" name="_SubscriptionDNA_yes" value="1" <?php if($memberpage) echo("checked"); ?> /></td>
	<td align="left" style="font-size: 11px;">Member Only Page?</td>
	</tr>
	
	<tr>
	<td><input type="checkbox" name="_SubscriptionDNA_menu" value="1" <?php if($menu) echo("checked"); ?> /></td>
	<td align="left" style="font-size: 11px;">Display SubscriptionDNA Member Menu?</td>
	</tr>
	</table>
	</div>
	<script>
	window.onload=test;
	function test()
	{
		try{
			document.getElementById('category-ajax-response').innerHTML='<div id="memberOnly"><strong>After adding category you can make is "Member Only" from category listing page</strong></div>';
		}catch(ERROR){}
	}
	</script>
	<?php	
}
function SubscriptionDNA_admin_sidebar_save($post_id)
{
	update_post_meta($post_id, "_SubscriptionDNA_menu", $_REQUEST["_SubscriptionDNA_menu"]);
	update_post_meta($post_id, "_SubscriptionDNA_yes", $_REQUEST["_SubscriptionDNA_yes"]);
}

function SubscriptionDNA_admin_menu ( )
{

	$Aarzi = add_options_page ( 'SubscriptionDNA' , 'SubscriptionDNA' , 10 , 'subscription-dna-subscription-billing-and-membership-management-platform/SubscriptionDNA.php' , 'SubscriptionDNA_Options_Edit' ) ;
	return TRUE ;

}
function SubscriptionDNA_parseResponse($xml)
{
	$xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xml);
	require_once dirname(__FILE__).'/parser_php4.php';
	$parser = new XMLParser($xml);

	//Work the magic...
	$parser->Parse();

	$arr=get_object_vars($parser->document->tagChildren[1]->tagChildren[0]);
	$arr=$arr["tagChildren"];
	$responseArray=array();
	foreach($arr as $key=>$val)
	{
		$param=get_object_vars($val);
		$valTest=$param["tagChildren"];
		if(count($valTest)>0)
		{
			foreach($valTest as $key2=>$val2)
			{
				$param2=get_object_vars($val2);
				$responseArray[$param["tagName"]][$param2["tagName"]]=urldecode($param2["tagData"]);
			}
		}
		else
		{
			$responseArray[$param["tagName"]]=urldecode($param["tagData"]);
		}
	}
	return($responseArray);
}
function SubscriptionDNA_wrapAsSoap($param)
{
	$xml='<?xml version="1.0" encoding="UTF-8"?>
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"	xmlns:wsa="http://schemas.xmlsoap.org/ws/2004/08/addressing" 	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  xsi:schemaLocation="http://schemas.xmlsoap.org/soap/envelope/">
			<soapenv:Header>
			<apikey>'.$GLOBALS['SubscriptionDNA']['Settings']['API_KEY'].'</apikey>
			<userip>'.$_SERVER['REMOTE_ADDR'].'</userip>
			</soapenv:Header>
			<soapenv:Body>
				<req:echo>';
	$count=1;
	foreach($param as $key=>$value)
	{
		$xml.='
					<req:key'.$key.'>'.urlencode($value).'</req:key'.$key.'>	';
	}
	$xml.='
				</req:echo>
			</soapenv:Body>
		</soapenv:Envelope>
		';

	return(array($xml));
}
function SubscriptionDNA_Update_Subscription($client)
{
	$categories = get_categories("hide_empty=0");
	$services=array();
	$servicesToCategories=array();
	foreach($categories as $key=>$Term) 
	{
		$service_ids=get_option('SubscriptionDNA_-_Settings_-_Cat'.$Term->cat_ID);
		if(is_array($service_ids))
		{
			foreach($service_ids as $service_id)
			{
				if(!in_array($service_id,$services))
				$services[]=$service_id;
				if(!is_array($servicesToCategories[$service_id]))
				$servicesToCategories[$service_id]=array();
				if(!in_array($Term->cat_ID,$servicesToCategories[$service_id]))
				$servicesToCategories[$service_id][]=$Term->cat_ID;
			}
		}	
	}
	$serviceArray=array();
	$serviceArray = $client->call("SubscriptionCheck",SubscriptionDNA_wrapAsSoap(array($_SESSION['login_name'],"")));
	$serviceArray = SubscriptionDNA_parseResponse($serviceArray);
	$activeCategories=array();
	$activeServices=array();
	foreach($serviceArray as $service)
	{
		if($service["status"]=="Active")
		{
			if(is_array($servicesToCategories[$service["service_id"]]))
			foreach($servicesToCategories[$service["service_id"]] as $key=>$val)
			$activeCategories[]=$val;
			
			$activeServices[]=$service["service_id"];
			$_SESSION['subscription']="1";
		}
	}
	$_SESSION['subscribed_categories']=$activeCategories;
	$_SESSION['subscribed_services']=$activeServices;
}
function SubscriptionDNA_wp_head ( )
{
/*	$restrictedCats=get_option("SubscriptionDNA_cats");
	if((is_single() && in_category($restrictedCats)) && $_SESSION['login_name']==""  && $GLOBALS['post']->ID!=$GLOBALS['SubscriptionDNA']['Settings']['Login'] && !is_home())
	{
		?>
		<style>
		body{display:none;}
		</style>
		<script>
		location.href='<?php echo(get_permalink($GLOBALS['SubscriptionDNA']['Settings']['Login'])); ?>';
		</script>
		<?php
		exit;
	}
*/
?>
	<link rel='stylesheet' href='<?php echo(WP_PLUGIN_URL); ?>/subscription-dna-subscription-billing-and-membership-management-platform/styles.css' type='text/css'/>
<?php
	return TRUE ;

}
function SubscriptionDNA_wp_footer ( )
{}

/*

Settings

*/
function SubscriptionDNA_Get_Settings($createPages=0)
{

	$Settings = Array ( ) ;


	$Settings['TLD'] = get_option ( 'SubscriptionDNA_-_Settings_-_TLD' ) ;
	if ( empty ( $Settings['TLD'] ))
	{
		$Aarzi = add_option ( 'SubscriptionDNA_-_Settings_-_TLD' , $Settings['TLD']  , '' , 'no' ) ;
	}
	else
	{
		$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_TLD' , $Settings['TLD'] ) ;
	}

	foreach ($GLOBALS['SubscriptionDNA']['DefaultPages'] as $page=>$title)
	{
		$Settings[$page] = get_option ( 'SubscriptionDNA_-_Settings_-_'.$page ) ;
		if ( empty ($Settings[$page] ))
		{
			if($createPages==1)
			{
				$Settings[$page]=SubscriptionDNA_CreatePage($title,$Settings["MainMenu"]);
				$_POST['SubscriptionDNA_this_page_included']=false;
				$_POST['SubscriptionDNA_ctrl_present']=1;
				SubscriptionDNA_update_exclusions($Settings[$page]);
				update_post_meta($Settings[$page], "_SubscriptionDNA_menu", 1);
				update_post_meta($Settings[$page], "_SubscriptionDNA_yes", 1);
			}
			$Aarzi = add_option ( 'SubscriptionDNA_-_Settings_-_'.$page , $Settings[$page]  , '' , 'no' ) ;
		}
		else
		{
			$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_'.$page , $Settings[$page] ) ;
		}
		$Settings[$page.'_HTTPS'] = get_option ( 'SubscriptionDNA_-_Settings_-_'.$page.'_HTTPS' ) ;
		$Settings[$page.'_Home'] = get_option ( 'SubscriptionDNA_-_Settings_-_'.$page.'_Home' ) ;
		if($createPages==1 && $Settings[$page.'_Home']=="")
		$Settings[$page.'_Home']=1;

		$Settings[$page.'_Order'] = get_option ( 'SubscriptionDNA_-_Settings_-_'.$page.'_Order' ) ;
		if($Settings[$page.'_Order']=="")
		$Settings[$page.'_Order']=0;

	}	
	update_post_meta($Settings["Login"], "_SubscriptionDNA_yes", 0);
	update_post_meta($Settings["Register"], "_SubscriptionDNA_yes", 0);
	update_post_meta($Settings["ForgotPassword"], "_SubscriptionDNA_yes", 0);

	$Settings['Extra'] = get_option ( 'SubscriptionDNA_-_Settings_-_Extra' ) ;
	if ( empty ( $Settings['Extra'] ))
	{
		$Aarzi = add_option ( 'SubscriptionDNA_-_Settings_-_Extra' , $Settings['Extra']  , '' , 'no' ) ;
	}
	else
	{
		$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_Extra' , $Settings['Extra'] ) ;
	}
	$Settings['SSL'] = get_option ( 'SubscriptionDNA_-_Settings_-_SSL' ) ;
	if ( empty ( $Settings['SSL'] ))
	{
		$url=get_option("home");
		$url=str_replace("http://","https://",$url);
		$Settings['SSL']=$url;
		$Aarzi = add_option ( 'SubscriptionDNA_-_Settings_-_SSL' , $Settings['SSL']  , '' , 'no' ) ;
	}
	else
	{
		$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_SSL' , $Settings['SSL'] ) ;
	}

	$Settings['API_KEY'] = get_option ( 'SubscriptionDNA_-_Settings_-_API_KEY' ) ;
	if ( empty ( $Settings['API_KEY'] ))
	{
		$Aarzi = add_option ( 'SubscriptionDNA_-_Settings_-_API_KEY' , $Settings['API_KEY']  , '' , 'no' ) ;
	}
	else
	{
		$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_API_KEY' , $Settings['API_KEY'] ) ;
	}

	$Settings['Limit'] = get_option ( 'SubscriptionDNA_-_Settings_-_Limit' ) ;
	if ( empty ( $Settings['Limit'] ))
	{	
		$Settings['Limit']=25;
		$Aarzi = add_option ( 'SubscriptionDNA_-_Settings_-_Limit' , $Settings['Limit']  , '' , 'no' ) ;
	}
	else
	{
		$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_Limit' , $Settings['Limit'] ) ;
	}
	$Settings['LimitOnly'] = get_option ( 'SubscriptionDNA_-_Settings_-_LimitOnly' ) ;
	if ( empty ( $Settings['LimitOnly'] ))
	{	
		$Settings['LimitOnly']=1;
		$Aarzi = add_option ( 'SubscriptionDNA_-_Settings_-_LimitOnly' , $Settings['LimitOnly']  , '' , 'no' ) ;
	}
	else
	{
		$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_LimitOnly' , $Settings['LimitOnly'] ) ;
	}
	$Settings['MemOnly'] = get_option ( 'SubscriptionDNA_-_Settings_-_MemOnly' ) ;
	if ( empty ( $Settings['MemOnly'] ))
	{	
		$Settings['MemOnly']=1;
		$Aarzi = add_option ( 'SubscriptionDNA_-_Settings_-_MemOnly' , $Settings['MemOnly']  , '' , 'no' ) ;
	}
	else
	{
		$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_MemOnly' , $Settings['MemOnly'] ) ;
	}
	return $Settings ;

}
function SubscriptionDNA_CreatePage($title,$parent)
{
        $page=@mysql_query("select ID,post_status from ".$GLOBALS['table_prefix'] . 'posts'." where "."`post_title`='$title'");
        $ID = @mysql_result($page,0,0) ;
		$status=@mysql_result($page,0,1) ;
        $ID = intval ( $ID ) ;
        if($ID>0)
        {
			if ( 'publish' != $status )
			{
	            $Array = Array
	            (
	                'post_status' => 'publish' ,
	            ) ;
	            @mysql_query("update ".$GLOBALS['table_prefix']."posts set post_status='publish' where `ID` = '$ID'") ;
				
			}
        	return $ID ;
        }
        else
        {
            $Array = Array
            (
                'post_author'           => "1"                  ,
                'post_date'             => date   ( 'Y-m-d H:i:s' ) ,
                'post_date_gmt'         => gmdate ( 'Y-m-d H:i:s' ) ,
                'post_content'          => ''     ,
                'post_title'            => $title           ,
                'post_excerpt'          => ''                       ,
                'post_status'           => 'publish'                ,
                'comment_status'        => 'closed'                 ,
                'ping_status'           => 'closed'                 ,
                'post_password'         => ''                       ,
                'post_name'             => sanitize_title($title)   ,
                'to_ping'               => ''                       ,
                'pinged'                => ''                       ,
                'post_modified'         => date   ( 'Y-m-d H:i:s' ) ,
                'post_modified_gmt'     => gmdate ( 'Y-m-d H:i:s' ) ,
                'post_content_filtered' => ''                       ,
                'post_parent'           => (int)$parent                        ,
                'guid'                  => ''                       ,
                'menu_order'            => 0                        ,
                'post_type'             => 'page'                   ,
                'post_mime_type'        => ''                       ,
                'comment_count'         => 0                        ,
            ) ;
            $ID = SubscriptionDNA_Database_Save ( $GLOBALS['table_prefix'] . 'posts' , $Array ) ;
            return($ID);
        }
}
function SubscriptionDNA_Options_Edit ( )
{

	if ( !empty ( $_POST['action'] ) AND 'update' == $_POST['action'] )
	{
		$Aarzi = SubscriptionDNA_Options_Save ( ) ;
	}

?>
<div class="wrap">
	<div id="icon-edit" class="icon32"><br /></div>
	<h2><?php echo __ ('SubscriptionDNA' ) ; ?></h2>
	
	<br>
	<div style="font-size:12px" align="center"> 
		<a href="http://www.SubscriptionDNA.com" target="_blank"><img src="http://www.subscriptiondna.com/wordpress/logo.png" border="0"></a><br>
		<br>
		<a href="options-general.php?page=subscription-dna-subscription-billing-and-membership-management-platform/SubscriptionDNA.php">View/Edit Settings</a>&nbsp;|   
		<a href="options-general.php?page=subscription-dna-subscription-billing-and-membership-management-platform/SubscriptionDNA.php&manage_cats=1">Category/Post Access Settings</a>&nbsp;|   
		<a href="http://www.subscriptiondna.com/wordpress/" target="_blank">Plugin Information</a>&nbsp;| 
		<a href="http://www.subscriptiondna.com/contact/" target="_blank">Contact Us</a>
	</div>
	<br>
	<br>
	<?php
	if($_REQUEST["manage_cats"]=="1")
	{
		include dirname(__FILE__).'/categories.php';
	}
	else if($_REQUEST["manage_posts"]=="1")
	{
		include dirname(__FILE__).'/posts.php';
	}
	else
	{
	?>	   
		<form action="options-general.php?page=<?php echo $_GET['page'] ; ?>" method="post">
		<fieldset class="options">
		<legend></legend>
		<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="tagsdiv-post_tag" class="postbox">
		<h3 class='hndle'><span>SubscriptionDNA Basic Plugin Settings</span></h3>
<link rel='stylesheet' href='styles.css' type='text/css' media='all' />
		<div class="inside">
		<table class="editform optiontable">
		<tr>
		<th scope="row"><div align="right">TLD:</div></th>
		<td class="dna-small"><input type="text" name="SubscriptionDNA_-_Settings_-_TLD" value="<?php echo($GLOBALS['SubscriptionDNA']['Settings']['TLD']) ; ?>" style="width:200px;" />&nbsp;(ex: client1.xsubscribe.com - client1 is your tld)
		</td>
		</tr>
		
		<tr>
		<th scope="row"><div align="right">API KEY:</div></th>
		<td class="dna-small"><input type="text" name="SubscriptionDNA_-_Settings_-_API_KEY" value="<?php echo($GLOBALS['SubscriptionDNA']['Settings']['API_KEY']) ; ?>" style="width:200px;" />&nbsp;(ex: API KEY is found on Configurations page in DNA portal )
		</td>
		</tr>
		<?php
		foreach ($GLOBALS['SubscriptionDNA']['DefaultPages'] as $page=>$title)
		{
		?>                
		<tr>
		<th scope="row"><div align="right"><?php echo($title);?> Page:</div></th>
		<td class="dna-small"><label  style="display:none"><?php SubscriptionDNA_dropdown_pages(array('depth' => 100, 'child_of' => 0,'selected' =>$GLOBALS['SubscriptionDNA']['Settings'][$page] , 'echo' => 1,'name' => 'SubscriptionDNA_-_Settings_-_'.$page,'show_option_no_change' => 'All', 'show_option_none' => 'None', 'option_none_value' => "0"));?></label>
		&nbsp; &nbsp; &nbsp; Display Order:<input type="text" name="<?php echo('SubscriptionDNA_-_Settings_-_'.$page."_Order");?>" value="<?php echo($GLOBALS['SubscriptionDNA']['Settings'][$page.'_Order']); ?>" style="width:30px;"> 
		&nbsp; &nbsp; &nbsp; <input type="checkbox" name="<?php echo('SubscriptionDNA_-_Settings_-_'.$page."_HTTPS");?>" value="1" <?php if($GLOBALS['SubscriptionDNA']['Settings'][$page.'_HTTPS']=="1")echo("checked"); ?>> Use SSL? 
		&nbsp; &nbsp; &nbsp; <input type="checkbox" name="<?php echo('SubscriptionDNA_-_Settings_-_'.$page."_Home");?>" value="1" <?php if($GLOBALS['SubscriptionDNA']['Settings'][$page.'_Home']=="1")echo("checked"); ?>> Include in Member Navigation? 
		</td>
		</tr>
		<?php
		}
		?>
		<tr>
		<th scope="row"><div align="right">Show custom fields:</div></th>
		<td class="dna-small"><input type="checkbox" name="SubscriptionDNA_-_Settings_-_Extra" value="1" <?php if($GLOBALS['SubscriptionDNA']['Settings']['Extra']=="1")echo("checked") ; ?> /> (registration and profile update)</td>
		</tr>
		
		<tr>
		<th scope="row"><div align="right">SSL URL:</div></th>
		<td class="dna-small"><input type="text" name="SubscriptionDNA_-_Settings_-_SSL" value="<?php echo($GLOBALS['SubscriptionDNA']['Settings']['SSL']) ; ?>" style="width:320px;" />
		</tr>
		
		<tr>
		<th scope="row"><div align="right">Limit # of words on listing page:</div></th>
		<td class="dna-small"><input type="text" name="SubscriptionDNA_-_Settings_-_Limit" value="<?php echo($GLOBALS['SubscriptionDNA']['Settings']['Limit']) ; ?>" style="width:50px;" /> (0=Unlimited)
		</tr>
		
		<tr>
		<th scope="row"><div align="right">Apply limit to "Member Only" Posts:</div></th>
		<td class="dna-small"><input type="checkbox" name="SubscriptionDNA_-_Settings_-_LimitOnly" value="1" <?php if($GLOBALS['SubscriptionDNA']['Settings']['LimitOnly']=="1")echo("checked") ; ?> /> (applies limit to secure posts only)
		</tr>
		
		<tr>
		<th scope="row"><div align="right">Show "Member Only" Label:</div></th>
		<td class="dna-small"><input type="checkbox" name="SubscriptionDNA_-_Settings_-_MemOnly" value="1" <?php if($GLOBALS['SubscriptionDNA']['Settings']['MemOnly']=="1")echo("checked") ; ?> />
		</tr>
		</table>
		</div>
		</div>
		</div>                
		</fieldset>
		<center> <p class="submit">
		<input type="hidden" name="butSwitchValue" id="butSwitchValue" value="text"            />
		<input type="hidden" name="action" value="update"                                 />
		<input type="submit" name="submit" value="<?php echo __ ( 'Update &raquo;' ) ; ?>"/>
		
		  
		</p></center>
		</form>
	<?php
	}
	?>	
</div>
<?php

return TRUE ;

}

function SubscriptionDNA_Options_Save ( )
{

	if($_REQUEST["butSwitchValue"]=="text")
	$_POST['content']=$_POST['content2'];

	$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_TLD', $_POST['SubscriptionDNA_-_Settings_-_TLD']) ;
	foreach ($GLOBALS['SubscriptionDNA']['DefaultPages'] as $page=>$title)
	{
		$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_'.$page, $_POST['SubscriptionDNA_-_Settings_-_'.$page]) ;
		$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_'.$page.'_HTTPS', $_POST['SubscriptionDNA_-_Settings_-_'.$page.'_HTTPS']) ;
		$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_'.$page.'_Home', $_POST['SubscriptionDNA_-_Settings_-_'.$page.'_Home']) ;
		$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_'.$page.'_Order', $_POST['SubscriptionDNA_-_Settings_-_'.$page.'_Order']) ;
	}
	$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_Extra', $_POST['SubscriptionDNA_-_Settings_-_Extra']) ;
	$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_SSL', $_POST['SubscriptionDNA_-_Settings_-_SSL']) ;
	$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_API_KEY', $_POST['SubscriptionDNA_-_Settings_-_API_KEY']) ;
	
	$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_Limit', $_POST['SubscriptionDNA_-_Settings_-_Limit']) ;
	$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_LimitOnly', $_POST['SubscriptionDNA_-_Settings_-_LimitOnly']) ;

	$Aarzi = update_option ( 'SubscriptionDNA_-_Settings_-_MemOnly', $_POST['SubscriptionDNA_-_Settings_-_MemOnly']) ;

	$GLOBALS['SubscriptionDNA']['Settings'] = SubscriptionDNA_Get_Settings (1) ;

?>

    <div id="message" class="updated fade">
        <p><strong><?php echo __ ( 'The options were saved successfully.' ) ; ?></strong></p>
    </div>

<?php

return TRUE ;

}

/*
Misc. Functions
*/

function SubscriptionDNA_SELECT_Options ( $Values , $Indexed = 'Yes' )
{

	$Options = Array ( ) ;

	if ( empty ( $Values['Default'] ) )
	{
		$Values['Default'] = NULL ;
	}
	else
	{
	}

	foreach ( $Values['All'] As $Key => $Value )
	{

		switch ( $Indexed )
		{
			case 'Yes' :
				break ;
			case 'No' :
				$Key = $Value ;
				break ;
			default :
		}

		if ( $Key == $Values['Default'] )
		{
			$Selected = ' selected="selected"' ;
		}
		else
		{
			$Selected = '' ;
		}

		$Options[] = '<option value="' . $Key . '"' . $Selected . '>' . $Value . '</option>' ;

	}

	$Options = implode ( '' , $Options ) ;

	return $Options ;

}
function SubscriptionDNA_Database_Save ( $Table , $Record )
{

	$Query = Array ( ) ;

	$Keys = Array ( ) ;

	$Values = Array ( ) ;

	$Query[] = 'INSERT INTO `' . $Table . '`' ;

	$Query[] = '(' ;

	if ( !empty ( $Record ) )
	{
		foreach ( $Record As $Key => $Value )
		{
			$Keys[]   = '`'  .  $Key  . '`'  ;
			$Values[] = '\'' . $Value  . '\'' ;
		}
	}
	else
	{
	}

	$Query[] = implode ( ' , ' , $Keys   ) ;
	$Query[] = ')' ;
	$Query[] = 'VALUES' ;
	$Query[] = '(' ;
	$Query[] = implode ( ' , ' , $Values ) ;
	$Query[] = ')' ;                         ;

	$Query = implode ( ' ' , $Query ) ;

	@mysql_query($Query) ;
	$ID=@mysql_insert_id();
	return($ID) ;

}
$GLOBALS['SubscriptionDNA'] = Array ( ) ;

if ( function_exists ( 'add_action' ) )
{
	$Aarzi = add_action ( 'init' , 'SubscriptionDNA_Initialize' ) ;
	add_action('admin_init', 'SubscriptionDNA_Admin_Initialize');
}

function SubscriptionDNA_Get_Page_Content($Content)
{
	global $wpdb;
	ob_start();
	$memberpage=get_post_meta($GLOBALS['post']->ID, "_SubscriptionDNA_yes", true);
	$menu=get_post_meta($GLOBALS['post']->ID, "_SubscriptionDNA_menu", true);

	$restrictedCats=get_option("SubscriptionDNA_cats");
	if(($memberpage or in_category($restrictedCats)) && (is_single() or $GLOBALS['post']->post_type!="post") && $_SESSION['login_name']=="" && $GLOBALS['post']->ID!=$GLOBALS['SubscriptionDNA']['Settings']['Login'] && !is_home())
	{
		?>
		<style>
		body{display:none;}
		</style>
		<script>
		location.href='<?php echo(get_permalink($GLOBALS['SubscriptionDNA']['Settings']['Login'])); ?>?&redirect_to=<?php echo($GLOBALS['post']->ID);?>';
		</script>
		<?php
		exit;
	}
	if(is_single() && $GLOBALS['post']->post_type=="post" && $_SESSION['login_name']!="" and ($memberpage or in_category($restrictedCats)))
	{
		$allowed=true;
		if($memberpage)
		{
			$services=get_option('SubscriptionDNA_-_Settings_-_Post'.$GLOBALS['post']->ID);
			if(is_array($services))
			{
				$allowed=false;
				foreach($services as $val)
				{
					if(in_array($val,$_SESSION['subscribed_services']))
						$allowed=true;
				}
			}
		}
		else
		{
			if(!in_category($_SESSION['subscribed_categories']))
			$allowed=false;
		}
		if(!$allowed)
		{
			?>
			<style>
			body{display:none;}
			</style>
			<script>
			location.href='<?php echo(get_permalink($GLOBALS['SubscriptionDNA']['Settings']['Subscriptions'])); ?>';
			</script>
			<?php
			exit;
		}
	}
	if($menu) 
	{
		include "main_menu.php";
		$Content=ob_get_contents().$Content;
		ob_end_clean();
		ob_start();
	}
	if($GLOBALS['post']->ID==$GLOBALS['SubscriptionDNA']['Settings']['Register'])
	include_once(dirname(__FILE__).'/register.php');
	else if($GLOBALS['post']->ID==$GLOBALS['SubscriptionDNA']['Settings']['Login'])
	include_once(dirname(__FILE__).'/login.php');
	else if($GLOBALS['post']->ID==$GLOBALS['SubscriptionDNA']['Settings']['ForgotPassword'])
	include_once(dirname(__FILE__).'/forgot_password.php');
	else if($GLOBALS['post']->ID==$GLOBALS['SubscriptionDNA']['Settings']['EditProfile'])
	include_once(dirname(__FILE__).'/change_profile.php');
	else if($GLOBALS['post']->ID==$GLOBALS['SubscriptionDNA']['Settings']['ChangePassword'])
	include_once(dirname(__FILE__).'/change_password.php');
	else if($GLOBALS['post']->ID==$GLOBALS['SubscriptionDNA']['Settings']['ViewTransactions'])
	include_once(dirname(__FILE__).'/transactions.php');
	else if($GLOBALS['post']->ID==$GLOBALS['SubscriptionDNA']['Settings']['CreditCards'])
	include_once(dirname(__FILE__).'/creditcards.php');
	else if($GLOBALS['post']->ID==$GLOBALS['SubscriptionDNA']['Settings']['PremiumContentSummary'])
	include_once(dirname(__FILE__).'/premium_content_summary.php');
	else if($GLOBALS['post']->ID==$GLOBALS['SubscriptionDNA']['Settings']['PremiumContent'])
	include_once(dirname(__FILE__).'/premium_content.php');
	else if($GLOBALS['post']->ID==$GLOBALS['SubscriptionDNA']['Settings']['Subscriptions'])
	include_once(dirname(__FILE__).'/subscriptions.php');
	if(!is_single() and $GLOBALS['post']->post_type=="post")
	{
		if($GLOBALS['SubscriptionDNA']['Settings']['Limit']>0)
		{
			if($GLOBALS['SubscriptionDNA']['Settings']['LimitOnly']=="1")
			{
				$allowed=true;
				if($memberpage)
				{
					$services=get_option('SubscriptionDNA_-_Settings_-_Post'.$GLOBALS['post']->ID);
					if(is_array($services))
					{
						$allowed=false;
						foreach($services as $val)
						{
							if(in_array($val,$_SESSION['subscribed_services']))
								$allowed=true;
						}
					}
				}
				else
				{
					if(in_category($restrictedCats))
					$allowed=false;
				}
				if(!$allowed)
				$Content=SubscriptionDNA_limit_text($Content,$GLOBALS['SubscriptionDNA']['Settings']['Limit']);	
			}
			else
			{
				$Content=SubscriptionDNA_limit_text($Content,$GLOBALS['SubscriptionDNA']['Settings']['Limit']);	
			}
		}
	}
	$Content.=ob_get_contents();
	ob_end_clean();
	return($Content);
}
function SubscriptionDNA_Get_Time ( $Content )
{
	if($GLOBALS['SubscriptionDNA']['Settings']['MemOnly']=="1")
	{
		$memberpage=get_post_meta($GLOBALS['post']->ID, "_SubscriptionDNA_yes", true);
		$restrictedCats=get_option("SubscriptionDNA_cats");
		if($memberpage or in_category($restrictedCats))
		return($Content." | Member Only");
	}
	return($Content);	
}

function SubscriptionDNA_closetags ( $html )
{
    #put all opened tags into an array
    preg_match_all ( "#<([a-z]+)( .*)?(?!/)>#iU", $html, $result );
    $openedtags = $result[1];

    #put all closed tags into an array
    preg_match_all ( "#</([a-z]+)>#iU", $html, $result );
    $closedtags = $result[1];
    $len_opened = count ( $openedtags );
    # all tags are closed
    if( count ( $closedtags ) == $len_opened )
    {
        return $html;
    }
    $openedtags = array_reverse ( $openedtags );
    # close tags
    for( $i = 0; $i < $len_opened; $i++ )
    {
        if ( !in_array ( $openedtags[$i], $closedtags ) )
        {
            $html .= "</" . $openedtags[$i] . ">";
        }
        else
        {
            unset ( $closedtags[array_search ( $openedtags[$i], $closedtags)] );
        }
    }
    return $html;
}
function SubscriptionDNA_limit_text($text, $limit) {
	$text_in = strip_tags($text);
	$words = str_word_count($text_in, 2);
	$pos = array_keys($words);
	if(count($words) > $limit) 
	{
			$one=$words[$pos[$limit-3]];
			$two=$words[$pos[$limit-2]];
			$three=$words[$pos[$limit-1]];
			$p=strpos($text,$one,$pos[$limit-3]);
			$p=strpos($text,$two,$p);
			$p=strpos($text,$three,$p)+strlen($three);
			
		if($_SESSION['login_name']!="")
		{
			if(!in_category($_SESSION['subscribed_categories']))
			{
				$text = SubscriptionDNA_closetags(substr($text, 0, $p)."..."). ' <a href="'.get_permalink($GLOBALS['SubscriptionDNA']['Settings']['Subscriptions']).'?&redirect_to='.$GLOBALS['post']->ID.'">Subscribe to Read More...</a><br><br>';
			}	
		}
		else
		{
			$text = SubscriptionDNA_closetags(substr($text, 0, $p)."...") . ' <a href="'.get_permalink($GLOBALS['SubscriptionDNA']['Settings']['Login']).'?&redirect_to='.$GLOBALS['post']->ID.'">Login to Read More...</a><br><br>';
		}
	}
	else
	{
		if($_SESSION['login_name']!="")
		{
			if(!in_category($_SESSION['subscribed_categories']))
			{
				$text = $text."...".' <a href="'.get_permalink($GLOBALS['SubscriptionDNA']['Settings']['Subscriptions']).'?&redirect_to='.$GLOBALS['post']->ID.'">Subscribe to Read More...</a><br><br>';
			}	
		}
		else
		{
			$text = $text."..." . ' <a href="'.get_permalink($GLOBALS['SubscriptionDNA']['Settings']['Login']).'?&redirect_to='.$GLOBALS['post']->ID.'">Login to Read More...</a><br><br>';
		}
	}
	return $text;
}
		
if ( function_exists ( 'add_action' ) )
{

	$Aarzi = add_action ( 'admin_menu' , 'SubscriptionDNA_admin_menu' ) ;
	$Aarzi = add_action ( 'wp_head' , 'SubscriptionDNA_wp_head' ) ;
	$Aarzi = add_action(  'wp_footer', 'SubscriptionDNA_wp_footer');
	add_action ( 'the_excerpt' , 'SubscriptionDNA_Get_Page_Content' , 10 ) ;
	add_action ( 'the_content' , 'SubscriptionDNA_Get_Page_Content' , 10 ) ;

	add_action ( 'the_time' , 'SubscriptionDNA_Get_Time' , 10 ) ;
}
function SubscriptionDNA_dropdown_pages($args = '') {
	$defaults = array(
	'depth' => 0, 'child_of' => 0,
	'selected' => 0, 'echo' => 1,
	'name' => 'page_id', 'show_option_none' => '', 'show_option_no_change' => '',
	'option_none_value' => '0'
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$pages = get_pages($r);
	$output = '';

	if ( ! empty($pages) ) {
		$output = "<select name=\"$name\" id=\"$name\">\n";
		if ( $show_option_none )
		{
			$output .= "\t<option value=\"$option_none_value\">None</option>\n";
		}
		if($selected=="H")
		$output .= "\t<option value=\"H\" selected>Home Page</option>";
		else
		$output .= "\t<option value=\"H\">Homepage</option>";


		$output .= walk_page_dropdown_tree($pages, $depth, $r);
		$output .= "</select>\n";
	}

	$output = apply_filters('wp_dropdown_pages', $output);

	if ( $echo )
	echo $output;

	return $output;
}


?>