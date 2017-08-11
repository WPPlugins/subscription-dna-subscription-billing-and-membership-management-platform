<?php
	$client = new nusoap_client($GLOBALS['SubscriptionDNA']['WSDL_URL'],true);
	$session_id = $_SESSION['user_session_id'];
	$login_name = $_SESSION['login_name'];	
	
	function dump( $txt ){
		echo "<pre>"; print_r($txt); echo "</pre>";
	}
	
	$cid=$_REQUEST['cid'];
//print_r($cid);	
	if(!empty($cid)){
			$result = $client->call("CCInfoDataByCCid", SubscriptionDNA_wrapAsSoap(array($login_name,$cid)));
			$result = SubscriptionDNA_parseResponse($result);
//echo "<pre>";			
//print_r(explode("|",$result));
//print_r($_REQUEST);
			//$result =$result[0];		
	}
	if(!empty($_REQUEST['send'])){

			if(!empty($_REQUEST['cc_id'])){
				if($_REQUEST['isPrimary']!=1) $_REQUEST['isPrimary']=0;
				
				$result = $client->call("EditCCInfo",SubscriptionDNA_wrapAsSoap(array($_REQUEST['cc_id'], $_REQUEST['cc_name'], $_REQUEST['cc_type'], $_REQUEST['cc_number'], 
															      $_REQUEST['cc_exp_month'], $_REQUEST['cc_exp_year'], $_REQUEST['isPrimary'], $login_name)));
				

			$result = SubscriptionDNA_parseResponse($result);
				$msg=$result["errdesc"];			

				?>
				<script>
				location.href='<?php echo(get_permalink($GLOBALS['SubscriptionDNA']['Settings']['CreditCards'])."?&msg=".urlencode($msg)); ?>';
				</script>
				<?php

	exit;
			}else{
								
				$result = $client->call("AddCCInfo", SubscriptionDNA_wrapAsSoap(array($_REQUEST['cc_name'], $_REQUEST['cc_type'], $_REQUEST['cc_number'], $_REQUEST['cc_exp_month'], $_REQUEST['cc_exp_year'], $login_name)));
			$result = SubscriptionDNA_parseResponse($result);
	//	print_r($result);exit;
			}
			if($result["errcode"]==7 || $result["errcode"]==12){
				$msg=$result["errdesc"];			
				?>
				<script>
				location.href='<?php echo(get_permalink($GLOBALS['SubscriptionDNA']['Settings']['CreditCards'])."?&msg=".urlencode($msg)); ?>';
				</script>
				<?php
				die();
			}else{
				$msg='<font color="#FF0000">'.$result["errdesc"].'</font>';			
				if($_REQUEST['isPrimary']==1){
					$status='Primary';
				}			
				$result=array('cc_type'=>$_REQUEST['cc_type'],
					'cc_name'=>$_REQUEST['cc_name'],
					'cc_number'=>$_REQUEST['cc_number'],
					'status'=>$status,
					'expire_date'=>$_REQUEST['cc_exp_month'].'/'.$_REQUEST['cc_exp_year'],
				);
			}		
	}
	?>	
	
	<form name="cc_form" method="post" action="" onsubmit="return frmValidate(this);">	
		<table>
			<tr>
				<td id="avail_msg" colspan="2"><b><?=$msg; ?></b></td>
			</tr>
			<tr>
				<td>Card Type</td>
				<td><select name="cc_type" id="cc_type">
						<option></option>
						<option label="American Express" value="American Express">American Express</option>
						<option label="Discover" value="Discover">Discover</option>
						<option label="MasterCard" value="MasterCard">MasterCard</option>
						<option label="Visa" value="Visa">Visa</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Card Holder Name</td>
				<td><input name="cc_name" id="cc_name" value="<?=$result["card_holder_name"]; ?>" size="35" maxlength="100" type="text"></td>
			</tr>
			<tr>
				<td>Card Number</td>
				<td><input name="cc_number" id="cc_number" value="" size="35" maxlength="16" type="text"> <?=$result["card_number"]; ?></td>
			</tr>
			<tr>
				<td>Card Expiry</td>
				<td><select name="cc_exp_month" id="cc_exp_month">
						<option label="January" value="01">January</option>
						<option label="February" value="02">February</option>
						<option label="March" value="03">March</option>
						<option label="April" value="04">April</option>
						<option label="May" value="05">May</option>
						<option label="June" value="06">June</option>
						<option label="July" value="07">July</option>
						<option label="August" value="08">August</option>					
						<option label="September" value="09">September</option>
						<option label="October" value="10">October</option>
						<option label="November" value="11">November</option>
						<option label="December" value="12">December</option>
					</select>
	
					<select name="cc_exp_year" id="cc_exp_year">
						<option label="2010" value="2010">2010</option>
						<option label="2011" value="2011">2011</option>
						<option label="2012" value="2012">2012</option>
						<option label="2013" value="2013">2013</option>
						<option label="2014" value="2014">2014</option>
						<option label="2015" value="2015">2015</option>
						<option label="2016" value="2016">2016</option>
						<option label="2017" value="2017">2017</option>
						<option label="2018" value="2018">2018</option>
					</select>
				</td>
			</tr>
			<? if(!empty($result["ccid"])){ ?>			
			<tr>
				<td>Is Primary <input type="hidden" id="cc_id" name="cc_id" value="<?=$result["ccid"]; ?>" /></td>
				<td><input name="isPrimary" id="isPrimary" <? if($result["status"]=='Primary'){ echo 'checked="checked"'; } ?>  type="checkbox" value="1"></td>
			</tr>
			<? } ?>	
			<tr>
				<td></td>
					<td><input name="send" value="Save Credit Card" type="submit"/></td>
				</tr>
		</table>
	</form>
	
	<script language="javascript" type="text/javascript">
		function $(id){	
			return document.getElementById(id);
		}
		
		function dropdown_select(did,val){
			var dropdownid=$(did);
			try{
				for (i=dropdownid.options.length - 1; i>=0; i--){
					if(dropdownid.options[i].value==val){
						dropdownid.options[i].selected = true;
					}else{
						dropdownid.options[i].selected = false;
					}			
				}		
			}catch(er){
			}
		}
		dropdown_select('cc_type','<?=$result["card_type"]; ?>');
		var cc_date="<?=$result["expire_date"]; ?>";
		if(cc_date!=""){
			var split_date=cc_date.split('/');
			dropdown_select('cc_exp_month',split_date[0]);
			dropdown_select('cc_exp_year','20'+split_date[1]);	
		}	
		
		function ValidateCC(ccNumb) {  // v2.0
			var valid = "0123456789"  // Valid digits in a credit card number
			var len = ccNumb.length;  // The length of the submitted cc number
			var iCCN = parseInt(ccNumb);  // integer of ccNumb
			var sCCN = ccNumb.toString();  // string of ccNumb
			sCCN = sCCN.replace (/^\s+|\s+$/g,'');  // strip spaces
			var iTotal = 0;  // integer total set at zero
			var bNum = true;  // by default assume it is a number
			var bResult = false;  // by default assume it is NOT a valid cc
			var temp;  // temp variable for parsing string
			var calc;  // used for calculation of each digit
			
			// Determine if the ccNumb is in fact all numbers
			for (var j=0; j<len; j++) {
				temp = "" + sCCN.substring(j, j+1);
				if (valid.indexOf(temp) == "-1"){bNum = false;}
			}
			
			// if it is NOT a number, you can either alert to the fact, or just pass a failure
			if(!bNum){
				/*alert("Not a Number");*/bResult = false;
			}
			
			// Determine if it is the proper length 
			if((len == 0)&&(bResult)){  // nothing, field is blank AND passed above # check
				bResult = false;
			} else{  // ccNumb is a number and the proper length - let's see if it is a valid card number
			if(len >= 15){  // 15 or 16 for Amex or V/MC
				for(var i=len;i>0;i--){  // LOOP throught the digits of the card
						calc = parseInt(iCCN) % 10;  // right most digit
						calc = parseInt(calc);  // assure it is an integer
						iTotal += calc;  // running total of the card number as we loop - Do Nothing to first digit
						i--;  // decrement the count - move to the next digit in the card
						iCCN = iCCN / 10;                               // subtracts right most digit from ccNumb
						calc = parseInt(iCCN) % 10 ;    // NEXT right most digit
						calc = calc *2;                                 // multiply the digit by two
						// Instead of some screwy method of converting 16 to a string and then parsing 1 and 6 and then adding them to make 7,
						// I use a simple switch statement to change the value of calc2 to 7 if 16 is the multiple.
						switch(calc){
							case 10: calc = 1; break;       //5*2=10 & 1+0 = 1
							case 12: calc = 3; break;       //6*2=12 & 1+2 = 3
							case 14: calc = 5; break;       //7*2=14 & 1+4 = 5
							case 16: calc = 7; break;       //8*2=16 & 1+6 = 7
							case 18: calc = 9; break;       //9*2=18 & 1+8 = 9
							default: calc = calc;           //4*2= 8 &   8 = 8  -same for all lower numbers
						}                                               
					iCCN = iCCN / 10;  // subtracts right most digit from ccNum
					iTotal += calc;  // running total of the card number as we loop
				}  // END OF LOOP
				if ((iTotal%10)==0){  // check to see if the sum Mod 10 is zero
					bResult = true;  // This IS (or could be) a valid credit card number.
				} else {
					bResult = false;  // This could NOT be a valid credit card number
					}
				}
			}
			// change alert to on-page display or other indication as needed.
			return bResult; // Return the results
		}
		
		
		var frmValidate = function(frm){	
			if(frm.cc_type.value=="")
			{
				alert('Credit card type can not be left blank.');
				frm.cc_type.focus();
				return false;
			}
			if(frm.cc_name.value=='')
			{
				alert('Card holder name can not be left blank.');
				frm.cc_name.focus();
				return false;
			}
			if(frm.cc_number.value=='')
			{
				alert('Credit card number can not be left blank.');
				frm.cc_number.focus();
				return false;
			}
			if(!ValidateCC( frm.cc_number.value ) ){
				alert("Invalid Credit Card Number");
				frm.cc_number.focus();
				return false;
			}
			return true;
		}				
	</script>