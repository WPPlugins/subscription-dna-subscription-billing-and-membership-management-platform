<?php

$minimumshow=1;
$display=20;
$upto=20;
if(isset($_REQUEST["dispalypages"]))
{
	$display=$_REQUEST["dispalypages"];
	$upto=$_REQUEST["dispalypages"];
	$link="&dispalypages=".$_REQUEST["dispalypages"];
}
$limitFrom=0;
$curentPage=1;
if($_REQUEST["pageing"])
{
	$curentPage=$_REQUEST["pageing"];
	$limitFrom=($curentPage-1)*$display;
	$upto=$limitFrom+$display;
}

//$strQuery = "Select * from cas_users order by sort_by";

//$rseventT=@mysql_query($strQuery);
$total=@count($resultRows);

?>