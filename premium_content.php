<?php
	require_once(dirname(__FILE__).'/lib/nusoap.php');
	$client = new nusoap_client($GLOBALS['SubscriptionDNA']['WSDL_URL'],true);
	$session_id = $_SESSION['user_session_id'];
	$login_name = $_SESSION['login_name'];

	$result = $client->call("GetAllSubscribedServicesLink", SubscriptionDNA_wrapAsSoap(array($session_id, $login_name)));	
	$result = SubscriptionDNA_parseResponse($result);
//echo "<pre>"; print_r($result);echo "</pre>";exit;
	
	if(count($result)<1){
		echo '<div id="dna-login"><div id="failure">Sorry, no service content is available.</div></div>';
	}

	$resultRows = $result;
	$count = 1;
//echo "<pre>"; print_r($resultRows);echo "</pre>";exit;	
	echo '<table id="dna-content">
	<!--
		  <tr align="center">
			<td><b>Title</b></td>
			<td><b>Article Date</b></td>
			<td><b>Author</b></td>
			<td><b>Summary</b></td>
		  </tr>
		  -->
		 ';
	

	for($j = 0; $j <count($resultRows); $j++)
	{	
		$resultRow = $resultRows["record".$j];
		for($i = 0; $i < count($resultRow); $i++)
		{
			$content_detail = explode("~", $resultRow["link".$i]);
			if($content_detail[0] != "")
			{
				
				list($d,$t)=split(" ",$content_detail[2]);
				list($y,$m,$d)=split("-",$d);
//				echo '<tr><td><a class="hyper" href="' . $content_detail[0] . '" target="new" onClick="return openwindow(this.href);">' . $content_detail[1] . '</a></td><td>'.date('Y-m-d H:i:s', $content_detail[2]).'</td><td>'.$content_detail[3].'</td><td width="500px">'.$content_detail[5].'</td></tr>';

				echo '<tr><td><a class="hyper" href="' . $content_detail[0] . '" target="new" onClick="return openwindow(this.href);" style="font-size:13pt;">' . $content_detail[1] . '</a><br>
				<b>'.$content_detail[4].'</b> | '."$m/$d/$y".' | '.$content_detail[3].'<br>
				'.$content_detail[5].'<br><br></td></tr>';

				$count++;
			}	
		}	
	}	

	echo '</table>';
?>

<script language="javascript">
	function openwindow(url)
	{
		var testwindow = window.open (url,"","scrollbars=1,resize=1,status=0,toolbar=0,menubar=0,width=750,height=550,location=0");
		return false;
	}
</script>
