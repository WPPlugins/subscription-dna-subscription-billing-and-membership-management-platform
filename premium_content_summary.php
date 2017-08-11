<?php
	require_once(dirname(__FILE__).'/lib/nusoap.php');
	$client = new nusoap_client($GLOBALS['SubscriptionDNA']['WSDL_URL'],true);
	$serviceId = "8f5d7240-ce42-102c-980c-001372fb8066";

	$result = $client->call("GetServiceSummary", array($serviceId));	

//echo "<pre>"; print_r($result);echo "</pre>";exit;
	
	if($result==""){
		echo '<font color="#FF0000">&nbsp;No Services Found.</font><br />';
	}

	$resultRows = explode("\n",$result);
	$count = 1;
//echo "<pre>"; print_r($resultRows);echo "</pre>";exit;	
	echo '<table border=0 cellpadding="5">
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
		$resultRow = explode("|",$resultRows[$j]);
//print_r($resultRow);		
		for($i = 0; $i < count($resultRow); $i++)
		{
			$content_detail = explode("~", $resultRow[$i]);
			if($content_detail[0] != "")
			{
//				echo '<tr><td><a class="hyper" href="' . $content_detail[0] . '" onClick="return openwindow(this.href);">' . $content_detail[1] . '</a></td><td>'.date('Y-m-d H:i:s', $content_detail[2]).'</td><td>'.$content_detail[3].'</td><td width="500px">'.$content_detail[5].'</td></tr>';

				echo '<tr><td><a class="hyper" href="' . $content_detail[0] . '" onClick="return openwindow(this.href);" style="font-size:13pt;">' . $content_detail[1] . '</a><br>
				'.date('m-d-Y', strtotime($content_detail[2])).' | '.$content_detail[3].'<br><br>
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
		var testwindow = window.open (url);//,"","scrollbars=1,resize=1,status=0,toolbar=0,menubar=0,width=750,height=550,location=0");
		return false;
	}
</script>
