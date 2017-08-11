<?php
require_once(dirname(__FILE__).'/lib/nusoap.php');
$wsdl =$GLOBALS['SubscriptionDNA']['WSDL_URL'];
	
$client = new nusoap_client($wsdl,true);

$result = $client->call("GetAllPackages",SubscriptionDNA_wrapAsSoap(array($_SERVER['REMOTE_ADDR'])));
$packages = SubscriptionDNA_parseResponse($result);

$customFields = $client->call("GetCustomFields",SubscriptionDNA_wrapAsSoap(array()));
$customFields = SubscriptionDNA_parseResponse($customFields);


$result=array();
if($_REQUEST['check_available'])
{
		$client = new nusoap_client($wsdl,true);		
		$login_name = $_REQUEST['login_name'];
		$result = $client->call("UsernameAvailability",SubscriptionDNA_wrapAsSoap(array($login_name)));	
	$result = SubscriptionDNA_parseResponse($result);
	if($result['errcode']!=4){
		$msgu='<div class="lblErr">'.$result['errdesc'].'</div>';
	}else{
		$msgu='<div class="success">'.$result['errdesc'].'</div>';
	}
}else if($_REQUEST['check_email_available']){
		$client = new nusoap_client($wsdl,true);		
		$email = $_REQUEST['email'];
		$result = $client->call("EmailAvailability", SubscriptionDNA_wrapAsSoap(array($email)));
	$result = SubscriptionDNA_parseResponse($result);
	if($result['errcode']!=5){
		$msge='<div class="lblErr">'.$result['errdesc'].'</div>';
	}else{
		$msge='<div class="success">'.$result['errdesc'].'</div>';
	}
}?>
 
<style TYPE="text/css"> 
.lbl {color: #000000 }
.lblErr {color: maroon }
.urgent {color:maroon}
.required {color:maroon; padding:0 0 0 2px }
.errMsg { white-space:nowrap; color: maroon; font-size: 10pt; font-family: arial; font-weight:regular; display:none }
.how_referred_member { color: #666666; }
.how_referred_other { color: #666666; }

#DNAFormFields form { margin: 0px; }
#DNAFormFields td { font-family: arial; font-size: 12px; text-align: left; }
#DNAFormFields input, select { font-size: 11px; width:175px; padding-left: 4px; }
#DNAFormFields select { width:175px; padding-left: 4px; }
#DNAFormFields textarea { font-family: verdana; font-size: 11px; }
#DNAFormFields input.noErr    {}
#DNAFormFields input.err,select.err { border: 1px #7f9db9 solid; }
#DNAFormFields h3 { font-size: 13pt; margin: 0px; }
</style> 
 
<script LANGUAGE="JavaScript"> 
focused=0;
function countryChanged(country) {
    if(country=="223") {
        document.getElementById('stateList').style.display='block';
        document.getElementById('state').style.display='none';
    } else {
        document.getElementById('stateList').style.display='none';
        document.getElementById('state').style.display='block';
        document.getElementById('state').value="";
    }
}
function stateChanged(state) {
    document.getElementById('state').value=state;
}
 
/* support routines */
function xGetElementById(e) {
    if(typeof(e)!="string") return e;
    if(document.getElementById) e=document.getElementById(e);
    else if(document.all) e=document.all[e];
    else if(document.layers) e=xLayer(e);
    else e=null;
    return e;
}
function xCollapse(e) {
    if(!(e=xGetElementById(e))) return;
    e.style.display = "none";
}
function xExpand(e) {
    if(!(e=xGetElementById(e))) return;
    e.style.display = "block";
}
// set focusObj or lblObj to ZERO (0) to suppress
 
// at this point, the 2 pw's are NOT empty
function validatePasswords(f) {

    var p1 = f.password.value,
        p2 = f.password2.value;
 
    if ( p1 != p2 )
        ValidateField(false,"password","Passwords do not match.");
    else
        ValidateField(true,"password","Passwords do not match.");
}
 
 
// ensure the number doesn't have invalid chars
function validateOnePhoneNumber(num)
{
    var i = 0, ct = num.length, c;
    for ( ; i < ct; ++i)
    {
        c = num.charAt(i);
        if (!( c == '(' || c == ')' || c == ' ' || c == '-' || c == '+' || (c >= '0' && c <= '9')))
            return false;
    }
    return true;
}
 
function validatePhones(f)
{
    var ph = f.phone.value;
    if (ph.length<10)
        ValidateField(false,"phone","Phone number is too short.");
    else if ( !validateOnePhoneNumber(ph))
        ValidateField(false,"phone","Phone number has invalid characters.");
    else
        ValidateField(true,"phone","Phone number has invalid characters.");
}
 
// see [http://www.breakingpar.com/] in the tips/regExp section
function isEmailValid(emailAddress)
{
    var re=/^([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+/;
 
    //var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    return re.test(emailAddress);
    //return(true);
}
 
function validateEmails(f)
{
    var e1 = f.email.value,
        e2 = f.email2.value;
    if ( !isEmailValid(e1)) {
        ValidateField(false,"email","Please enter a valid email.");
    } else if ( e1 != e2) {
        ValidateField(false,"email","Email fields do not match.");
    } else {
        ValidateField(true,"email","Enter the same email in both fields.");
    }
}
 
function checkLicenseAgreement(f) {
    if ( !f.agree.checked) {
        ValidateField(false,"agree","Please see terms & conditions");
    } else {
        ValidateField(true,"agree","Please see terms & conditions");
    }
}
 
function checkMembership(f) {
    if (validateSubscription()) {
        ValidateField(true,"package","");
    } else {
        ValidateField(false,"package","Please select at least one subscription plan.");
    }
}
 
// see [http://www.breakingpar.com/] in the tips/regExp section
function isCCvalid(cc_type, cc_number) {
    var re, checksum = 0, i;
    if (cc_type == "Visa")
        re = /^4\d{3}-?\d{4}-?\d{4}-?\d{4}$/;        // Visa: length 16, prefix 4, dashes optional.
    else if (cc_type == "MasterCard")
        re = /^5[1-5]\d{2}-?\d{4}-?\d{4}-?\d{4}$/;    // MC: length 16, prefix 51-55, dashes optional.
    else if (cc_type == "Discover")
        re = /^6011-?\d{4}-?\d{4}-?\d{4}$/;            // Disc: length 16, prefix 6011, dashes optional.
    else if (cc_type == "American Express")
        re = /^3[4,7]\d{13}$/;                        // Amex: length 15, prefix 34 or 37.
    else if (cc_type == "diners")
        re = /^3[0,6,8]\d{12}$/;                    // Diners: length 14, prefix 30, 36, or 38.
    else
        return false;
    if (!re.test(cc_number)) return false;
    // Checksum ("Mod 10")
    // Add even digits in even length strings or odd digits in odd length strings.
    for (i=(2-(cc_number.length % 2)); i<=cc_number.length; i+=2) {
        checksum += parseInt(cc_number.charAt(i-1));
    }
    // Analyze odd digits in even length strings or even digits in odd length strings.
    for (i=(cc_number.length % 2) + 1; i<cc_number.length; i+=2) {
        var digit = parseInt(cc_number.charAt(i-1)) * 2;
        if (digit < 10) { checksum += digit; } else { checksum += (digit-9); }
    }
    return ((checksum % 10) == 0);
}
 
function checkCreditCard(f) {
   if ( isCCvalid(f.cc_type.value,f.cc_number.value))
        ValidateField(true,"cc_type","");
   else {
        ValidateField(false,"cc_number","Invalid credit card number.");
   }
}

function checkCreditCardExpiry(f) {
    var dtt = new Date();
    m1=dtt.getMonth();
    m2=f.cc_exp_month.value;
    y1=dtt.getFullYear()-2000;
    y2=f.cc_exp_year.value;
 
   if ((m2>=m1 && y2>=y1) || y2>y1) {
        ValidateField(true,"cc_exp_month","");
   } else {
        ValidateField(false,"cc_exp_month","Invalid credit card expiration date.");
   }
}
 
function checkEmpty(fid,message) {
    var obj = xGetElementById(fid);
    if(obj.value=="")
        return(ValidateField(false,fid,message));
    else
        return(ValidateField(true,fid,message));
}
mainValidated=true;
 
function ValidateField(validated,fid,message) {
    if(!validated && mainValidated)
        mainValidated=false;
 
    var obj = xGetElementById(fid);
    var lbl_error = xGetElementById(fid+"_lbl_error");
    if(validated) {
        lbl_error.innerHTML = "";
        obj.className = 'noErr';
    } else {
        lbl_error.innerHTML = message;
    // hilite the error field
        if(focused==0) {
            try    {
                obj.focus();
            }
            catch(errr){}
            focused=1;
        }
        obj.className = 'err';
    }
    return(validated);
}
 
function checkForm(f) {
    checkMembership(f);
    checkEmpty("first_name","Please enter First name.");
    checkEmpty("last_name","Please enter Last name.");
    checkEmpty("login_name","Please enter Login name.");

    //alert(checkEmpty("email","Please enter Email."));
    if(checkEmpty("email","Please enter Email."))
        validateEmails(f);
    checkEmpty("email2","Please re-enter Email.");
 
    if(checkEmpty("password","Please enter Password."))
        validatePasswords(f);
    checkEmpty("password2","Please re-enter Password.");
     
    checkEmpty("cc_name","Please enter Name on Card.");
    checkEmpty("cc_type","Please select Card Type.");
    if(checkEmpty("cc_number","Please enter Card Number."))
    checkCreditCard(f);
    checkEmpty("cc_exp_month","Expiry Month");
    if(checkEmpty("cc_exp_year","Expiry Year"))
    checkCreditCardExpiry(f);
     
	checkEmpty("address1","Please enter Address.");
	checkEmpty("city","Please enter City.");
    checkEmpty("state","Please select State.");
    checkEmpty("zipcode","Please enter Zip.");
    
    if(checkEmpty("phone","Please enter Phone."))
    validatePhones(f);
        checkLicenseAgreement(f);
 
    focused=0;

    if(!mainValidated) {
        mainValidated=true;
        return(false);
    }
    else
    return true;
}
</script> 
 


<div align="center" id="DNAFormFields"> 
<div style="color:#990000;">
<?php if($_POST['response_type'])
	 echo($_POST['response_type'].":".$_POST["response"]);
?>
</div>

<form method="post" name="customSubscribeForm" action="https://<?php echo($GLOBALS['SubscriptionDNA']['Settings']['TLD']) ; ?>.xsubscribe.com/widgetvalidate/remoteSubscriptionHandlerP" > 
            
    <input type='hidden' name='x_confirmurl' value='<?php echo(get_permalink($GLOBALS['SubscriptionDNA']['Settings']['Login'])); ?>'>
    <input type='hidden' name='subscribe_to_service' value='1'>
    <input type='hidden' name='cust_fields' value='1'>
    <input type='hidden' name='service_id' value=''>
    <input type='hidden' name='billing_routine_id' value='0'>
    <input type='hidden' name='paid_by_credit_card' value='1'>
    <input type='hidden' name='add_fields_req' value='1'>
    <input type='hidden' name='group_owner_id' value=''>
 
<span id="x_sid_01_lbl_error" class="lblErr"></span><br> 
 
<p> 
<table border="0" width="100%"> 
<tr valign=top>
    <td colspan="3"><h3>Subscription Plans:</h3></td>
<tr valign=top>            
    <td colspan="3">
        <div style="border: solid 1px black; padding: 4px; background-color: #ffffff; ">
				<?php 
				$count=0;
				foreach($packages as $package)
				{
					if(in_array($package["service_id"].";".$package["billing_routine_id"],$_POST["packages"]))
					$package["defaultval"]="Yes";
					?>
					<div id="innerDiv">
					<strong><input style="width: 15px;" type="checkbox" name="packages[]" id="packages_<?php echo($count); ?>"  value="<?php echo($package["service_id"]); ?>;<?php echo($package["billing_routine_id"]); ?>" <?php if($package["defaultval"]=="Yes") echo("checked");  ?>  ><?php echo($package["package_name"]);  ?></strong>
						<div style="margin-left:20px;"><?php echo($package["package_description"]); ?></div><br>
					</div>
					<?php 
					$count++;
				}
				?>
        		
		<span id="package_lbl_error" class="lblErr"></span>
		</div>
		<br>
		<input type="hidden" name="package" value="" id="package" />
    </td>
</tr>
<script>
function validateSubscription()
{
	for(i=0;i<<?php echo($count); ?>;i++)
	{
		if(document.getElementById('packages_'+i).checked)
		return(true);
	}
	return(false);
}
</script>

<tr><td colspan="3"><h3>Member Info</h3></td></tr> 
 
<tr> 
<td align="left"><span id="first_name_lbl" class="lbl">First Name</span></td> 
<td><input TYPE="TEXT" NAME="first_name" value="<?php echo($_REQUEST["first_name"]); ?>" id="first_name" size="30" class="noErr" MAXLENGTH="50" onfocus="assignAdvisor();"></td> 
<td width="180"><span id="first_name_lbl_error" class="lblErr"></span></td> 
</tr> 
 
<tr> 
<td align="left"><span id="last_name_lbl" class="lbl">Last Name</span></td> 
<td><input TYPE="TEXT" NAME="last_name" value="<?php echo($_REQUEST["last_name"]); ?>"  id="last_name" size="30" class="noErr" MAXLENGTH="50"></td> 
<td><span id="last_name_lbl_error" class="lblErr"></span></td> 
</tr> 

<tr><td colspan="3"><br></td></tr>

<tr> 
<td valign="top" align="left"><span id="login_name_lbl" class="lbl">Login Username</span></td> 
<td><input TYPE="TEXT" NAME="login_name" value="<?php echo($_REQUEST["login_name"]); ?>"  id="login_name" size="30" class="noErr" MAXLENGTH="100" >
<input type="submit" name="check_available"  id="check_available" value="Check Availability" onclick="this.form.action='';" />
</td> 
<td valign="top"><span id="login_name_lbl_error" class="lblErr"><?php echo($msgu); ?></span></td> 
</tr> 
 
<tr> 
<td align="left"><span id="password_lbl" class="lbl">Login Password</span></td> 
<td><input NAME="password" value="<?php echo($_REQUEST["password"]); ?>"  id="password" class="noErr" TYPE="PASSWORD" SIZE="30" MAXLENGTH="20"></td> 
<td><span id="password_lbl_error" class="lblErr"></span></td> 
</tr> 
 
<tr> 
<td align="left" nowrap><span id="password2_lbl" class="lbl">(Re-enter Password)</span></td> 
<td><input NAME="password2" value="<?php echo($_REQUEST["password2"]); ?>" id="password2" class="noErr" TYPE="PASSWORD" SIZE="30" MAXLENGTH="20"></td> 
<td><span id="password2_lbl_error" class="lblErr"></span></td> 
</tr> 
 

<tr> 
<td valign="top" align="left"><span id="email_lbl" class="lbl">Account Email</span></td> 
<td><input TYPE="TEXT" NAME="email" value="<?php echo($_REQUEST["email"]); ?>" id="email" size="30" class="noErr" MAXLENGTH="100">
<input type="submit"  name="check_email_available"  id="check_email_available" value="Check Availability" onclick="this.form.action='';" />
</td> 
<td valign="top"><span id="email_lbl_error" class="lblErr"><?php echo($msge); ?></span></td> 
</tr> 
 
<tr> 
<td align="left"><nobr><span id="email2_lbl" class="lbl">(Re-enter Email)</i></span></nobr></td> 
<td><input TYPE="TEXT" NAME="email2" value="<?php echo($_REQUEST["email2"]); ?>" id="email2" size="30" class="noErr" MAXLENGTH="100"></td> 
<td><span id="email2_lbl_error" class="lblErr"></span></td> 
</tr> 

 
<tr><td colspan="3"><br>
<h3>Payment Info</h3></td></tr> 
 
<tr> 
<td align="left"><span id="cc_name_lbl" class="lbl">Cardholder Name</span></td> 
<td><input TYPE="TEXT" NAME="cc_name" value="<?php echo($_REQUEST["cc_name"]); ?>" id="cc_name" size="30" maxlength="100" class="noErr"></td> 
<td><span id="cc_name_lbl_error" class="lblErr"></span></td> 
</tr> 
 
<tr> 
<td align="left"><span id="cc_type_lbl" class="lbl">Credit Card Type</span></td> 
<td><select class="noErr" name="cc_type" id="cc_type"> 
<option></option> 
<option value='MasterCard' >MasterCard</option>
<option value='Visa' >Visa</option>
<option value='Discover' >Discover</option>
<option value='American Express' >American Express</option>
</select></td> 
<td><span id="cc_type_lbl_error" class="lblErr"></span></td> 
</tr> 
 
<tr> 
<td align="left"><span id="cc_number_lbl" class="lbl">Credit Card Number</span></td> 
<td><input TYPE="TEXT" NAME="cc_number" value="<?php echo($_REQUEST["cc_number"]); ?>" id="cc_number" size="30" maxlength="16" class="noErr"></td> 
<td> <span id="cc_number_lbl_error" class="lblErr"></span></td> 
</tr> 
 
<tr> 
<td align="left" nowrap><span id="cc_exp_month_lbl" class="lbl">Card Expiration Month</span></td> 
<td> 
 
<table cellpadding="0" cellspacing="0" width="175"><tr> 
<td> 
<select NAME="cc_exp_month" id="cc_exp_month"  class="noErr" style="width: 80px;"> 
<option></option> 
<option VALUE="01">January</option> 
<option VALUE="02">February</option> 
<option VALUE="03">March</option> 
<option VALUE="04">April</option> 
<option VALUE="05">May</option> 
<option VALUE="06">June</option> 
<option VALUE="07">July</option> 
<option VALUE="08">August</option> 
<option VALUE="09">September</option> 
<option VALUE="10">October</option> 
<option VALUE="11">November</option> 
<option VALUE="12">December</option> 
</select> 
</td> 
<td align="right"> 
<span id="cc_exp_year_lbl" class="lbl">Year</span>&nbsp
<select NAME="cc_exp_year" id="cc_exp_year"  class="noErr" style="width: 50px;"> 
<option></option> 
<option VALUE="10">2010</option> 
<option VALUE="11">2011</option> 
<option VALUE="12">2012</option> 
<option VALUE="13">2013</option> 
<option VALUE="14">2014</option> 
<option VALUE="15">2015</option> 
<option VALUE="16">2016</option> 
</select></td> 
</tr> 
</table> 
 
</td> 
<td><span id="cc_exp_month_lbl_error" class="lblErr"></span> &nbsp; <span id="cc_exp_year_lbl_error" class="lblErr"></span></td> 
</tr> 
 
<tr> 
<td align="left" class="lbl">CVC Code:</td> 
<td><input type="text" name="cc_cvv" value="<?php echo($_REQUEST["cc_cvv"]); ?>" id="cc_cvv" size="5" maxlength="4" style="width: 50px;" value="" ></td> 
<td></td> 
</tr> 

 
 
<tr><td colspan="3"><br> 
<h3>Billing Address</h3></td></tr> 
 
 
<tr><td align="left"><span id="country_lbl" class="lbl">Cardholder Country</span></td> 
<td>
<select NAME="country" class="noErr" size="1" onchange="countryChanged(this.value);"> 
<option label="&lt;--Please Select--&gt;" value="">&lt;--Please Select--&gt;</option><option value='223'>United States</option><option value='1'>Afghanistan</option><option value='2'>Albania</option><option value='3'>Algeria</option><option value='4'>American Samoa</option><option value='5'>Andorra</option><option value='6'>Angola</option><option value='7'>Anguilla</option><option value='8'>Antarctica</option><option value='9'>Antigua and Barbuda</option><option value='10'>Argentina</option><option value='11'>Armenia</option><option value='12'>Aruba</option><option value='13'>Australia</option><option value='14'>Austria</option><option value='15'>Azerbaijan</option><option value='16'>Bahamas</option><option value='17'>Bahrain</option><option value='18'>Bangladesh</option><option value='19'>Barbados</option><option value='20'>Belarus</option><option value='21'>Belgium</option><option value='22'>Belize</option><option value='23'>Benin</option><option value='24'>Bermuda</option><option value='25'>Bhutan</option><option value='26'>Bolivia</option><option value='27'>Bosnia and Herzegowina</option><option value='28'>Botswana</option><option value='29'>Bouvet Island</option><option value='30'>Brazil</option><option value='31'>British Indian Ocean Territory</option><option value='32'>Brunei Darussalam</option><option value='33'>Bulgaria</option><option value='34'>Burkina Faso</option><option value='35'>Burundi</option><option value='36'>Cambodia</option><option value='37'>Cameroon</option><option value='38'>Canada</option><option value='39'>Cape Verde</option><option value='40'>Cayman Islands</option><option value='41'>Central African Republic</option><option value='42'>Chad</option><option value='43'>Chile</option><option value='44'>China</option><option value='45'>Christmas Island</option><option value='46'>Cocos (Keeling) Islands</option><option value='47'>Colombia</option><option value='48'>Comoros</option><option value='49'>Congo</option><option value='50'>Cook Islands</option><option value='51'>Costa Rica</option><option value='52'>Cote D'Ivoire</option><option value='53'>Croatia</option><option value='54'>Cuba</option><option value='55'>Cyprus</option><option value='56'>Czech Republic</option><option value='57'>Denmark</option><option value='58'>Djibouti</option><option value='59'>Dominica</option><option value='60'>Dominican Republic</option><option value='61'>East Timor</option><option value='62'>Ecuador</option><option value='63'>Egypt</option><option value='64'>El Salvador</option><option value='65'>Equatorial Guinea</option><option value='66'>Eritrea</option><option value='67'>Estonia</option><option value='68'>Ethiopia</option><option value='69'>Falkland Islands (Malvinas)</option><option value='70'>Faroe Islands</option><option value='71'>Fiji</option><option value='72'>Finland</option><option value='73'>France</option><option value='74'>France, Metropolitan</option><option value='75'>French Guiana</option><option value='76'>French Polynesia</option><option value='77'>French Southern Territories</option><option value='78'>Gabon</option><option value='79'>Gambia</option><option value='80'>Georgia</option><option value='81'>Germany</option><option value='82'>Ghana</option><option value='83'>Gibraltar</option><option value='84'>Greece</option><option value='85'>Greenland</option><option value='86'>Grenada</option><option value='87'>Guadeloupe</option><option value='88'>Guam</option><option value='89'>Guatemala</option><option value='90'>Guinea</option><option value='91'>Guinea-bissau</option><option value='92'>Guyana</option><option value='93'>Haiti</option><option value='94'>Heard and Mc Donald Islands</option><option value='95'>Honduras</option><option value='96'>Hong Kong</option><option value='97'>Hungary</option><option value='98'>Iceland</option><option value='99'>India</option><option value='100'>Indonesia</option><option value='101'>Iran (Islamic Republic of)</option><option value='102'>Iraq</option><option value='103'>Ireland</option><option value='104'>Israel</option><option value='105'>Italy</option><option value='106'>Jamaica</option><option value='107'>Japan</option><option value='108'>Jordan</option><option value='109'>Kazakhstan</option><option value='110'>Kenya</option><option value='111'>Kiribati</option><option value='112'>Korea, Democratic People's Republic of</option><option value='113'>Korea, Republic of</option><option value='114'>Kuwait</option><option value='115'>Kyrgyzstan</option><option value='116'>Lao People's Democratic Republic</option><option value='117'>Latvia</option><option value='118'>Lebanon</option><option value='119'>Lesotho</option><option value='120'>Liberia</option><option value='121'>Libyan Arab Jamahiriya</option><option value='122'>Liechtenstein</option><option value='123'>Lithuania</option><option value='124'>Luxembourg</option><option value='125'>Macau</option><option value='126'>Macedonia, The Former Yugoslav Republic of</option><option value='127'>Madagascar</option><option value='128'>Malawi</option><option value='129'>Malaysia</option><option value='130'>Maldives</option><option value='131'>Mali</option><option value='132'>Malta</option><option value='133'>Marshall Islands</option><option value='134'>Martinique</option><option value='135'>Mauritania</option><option value='136'>Mauritius</option><option value='137'>Mayotte</option><option value='138'>Mexico</option><option value='139'>Micronesia, Federated States of</option><option value='140'>Moldova, Republic of</option><option value='141'>Monaco</option><option value='142'>Mongolia</option><option value='143'>Montserrat</option><option value='144'>Morocco</option><option value='145'>Mozambique</option><option value='146'>Myanmar</option><option value='147'>Namibia</option><option value='148'>Nauru</option><option value='149'>Nepal</option><option value='150'>Netherlands</option><option value='151'>Netherlands Antilles</option><option value='152'>New Caledonia</option><option value='153'>New Zealand</option><option value='154'>Nicaragua</option><option value='155'>Niger</option><option value='156'>Nigeria</option><option value='157'>Niue</option><option value='158'>Norfolk Island</option><option value='159'>Northern Mariana Islands</option><option value='160'>Norway</option><option value='161'>Oman</option><option value='162'>Pakistan</option><option value='163'>Palau</option><option value='164'>Panama</option><option value='165'>Papua New Guinea</option><option value='166'>Paraguay</option><option value='167'>Peru</option><option value='168'>Philippines</option><option value='169'>Pitcairn</option><option value='170'>Poland</option><option value='171'>Portugal</option><option value='172'>Puerto Rico</option><option value='173'>Qatar</option><option value='174'>Reunion</option><option value='175'>Romania</option><option value='176'>Russian Federation</option><option value='177'>Rwanda</option><option value='178'>Saint Kitts and Nevis</option><option value='179'>Saint Lucia</option><option value='180'>Saint Vincent and the Grenadines</option><option value='181'>Samoa</option><option value='182'>San Marino</option><option value='183'>Sao Tome and Principe</option><option value='184'>Saudi Arabia</option><option value='185'>Senegal</option><option value='186'>Seychelles</option><option value='187'>Sierra Leone</option><option value='188'>Singapore</option><option value='189'>Slovakia (Slovak Republic)</option><option value='190'>Slovenia</option><option value='191'>Solomon Islands</option><option value='192'>Somalia</option><option value='193'>South Africa</option><option value='194'>South Georgia and the South Sandwich Islands</option><option value='195'>Spain</option><option value='196'>Sri Lanka</option><option value='197'>St. Helena</option><option value='198'>St. Pierre and Miquelon</option><option value='199'>Sudan</option><option value='200'>Suriname</option><option value='201'>Svalbard and Jan Mayen Islands</option><option value='202'>Swaziland</option><option value='203'>Sweden</option><option value='204'>Switzerland</option><option value='205'>Syrian Arab Republic</option><option value='206'>Taiwan</option><option value='207'>Tajikistan</option><option value='208'>Tanzania, United Republic of</option><option value='209'>Thailand</option><option value='210'>Togo</option><option value='211'>Tokelau</option><option value='212'>Tonga</option><option value='213'>Trinidad and Tobago</option><option value='214'>Tunisia</option><option value='215'>Turkey</option><option value='216'>Turkmenistan</option><option value='217'>Turks and Caicos Islands</option><option value='218'>Tuvalu</option><option value='219'>Uganda</option><option value='220'>Ukraine</option><option value='221'>United Arab Emirates</option><option value='222'>United Kingdom</option><option value='223'>United States</option><option value='224'>United States Minor Outlying Islands</option><option value='225'>Uruguay</option><option value='226'>Uzbekistan</option><option value='227'>Vanuatu</option><option value='228'>Vatican City State (Holy See)</option><option value='229'>Venezuela</option><option value='230'>Viet Nam</option><option value='231'>Virgin Islands (British)</option><option value='232'>Virgin Islands (U.S.)</option><option value='233'>Wallis and Futuna Islands</option><option value='234'>Western Sahara</option><option value='235'>Yemen</option><option value='236'>Yugoslavia</option><option value='237'>Zaire</option><option value='238'>Zambia</option><option value='239'>Zimbabwe</option><option value='240'>Aaland Islands</option></select>  
</td> 
<td></td> 
</tr> 
 
<tr><td align="left"><span id="address1_lbl" class="lbl">Address</span><br></td> 
<td VALIGN="TOP"><input TYPE="TEXT" NAME="address1" value="<?php echo($_REQUEST["address1"]); ?>" id="address1" size="30" class="noErr" MAXLENGTH="50"></td> 
<td> <span id="address1_lbl_error" class="lblErr"></span></td> 
</tr> 
 
<tr><td align="left"><span id="city_lbl" class="lbl">City</span><br></td> 
<td VALIGN="TOP"><input TYPE="TEXT" NAME="city" value="<?php echo($_REQUEST["city"]); ?>" id="city" size="30" class="noErr" MAXLENGTH="20"></td> 
<td> <span id="city_lbl_error" class="lblErr"></span></td> 
</tr> 
 
<tr><td align="left"><span id="state_lbl" class="lbl">State or Province</span><br></td> 
<td VALIGN="TOP">
<select name="stateList" class="noErr" id="stateList"  onchange="stateChanged(this.value);"> 
<option></option> 
<option  value="XX">Other...</option> 
<option  value="AK">Alaska</option> 
<option  value="AL">Alabama</option> 
<option  value="AR">Arkansas</option> 
<option  value="AZ">Arizona</option> 
<option  value="CA">California</option> 
<option  value="CO">Colorado</option> 
<option  value="CT">Connecticut</option> 
<option  value="DE">Delaware</option> 
<option  value="FL">Florida</option> 
<option  value="GA">Georgia</option> 
<option  value="GU">Guam</option> 
<option  value="HI">Hawaii</option> 
<option  value="IA">Iowa</option> 
<option  value="ID">Idaho</option> 
<option  value="IL">Illinois</option> 
<option  value="IN">Indiana</option> 
<option  value="KS">Kansas</option> 
<option  value="KY">Kentucky</option> 
<option  value="LA">Louisiana</option> 
<option  value="MA">Massachusetts</option> 
<option  value="MD">Maryland</option> 
<option  value="ME">Maine</option> 
<option  value="MI">Michigan</option> 
<option  value="MN">Minnesota</option> 
<option  value="MO">Missouri</option> 
<option  value="MS">Mississippi</option> 
<option  value="MT">Montana</option> 
<option  value="NC">North Carolina</option> 
<option  value="ND">North Dakota</option> 
<option  value="NE">Nebraska</option> 
<option  value="NH">New Hampshire</option> 
<option  value="NJ">New Jersey</option> 
<option  value="NM">New Mexico</option> 
<option  value="NV">Nevada</option> 
<option  value="NY">New York</option> 
<option  value="OH">Ohio</option> 
<option  value="OK">Oklahoma</option> 
<option  value="OR">Oregon</option> 
<option  value="PA">Pennsylvania</option> 
<option  value="PR">Puerto Rico</option> 
<option  value="RI">Rhode Island</option> 
<option  value="SC">South Carolina</option> 
<option  value="SD">South Dakota</option> 
<option  value="TN">Tennessee</option> 
<option  value="TX">Texas</option> 
<option  value="UT">Utah</option> 
<option  value="VI">Virgin Islands</option> 
<option  value="VT">Vermont</option> 
<option  value="VA">Virginia</option> 
<option  value="WA">Washington</option> 
<option  value="DC">Washington D.C.</option> 
<option  value="WI">Wisconsin</option> 
<option  value="WV">West Virginia</option> 
<option  value="WY">Wyoming</option> 
<option  value="XX">Other</option> 
</select>  <input name="state" value="<?php echo($_REQUEST["state"]); ?>" style="display:none" size="30" type="text" id="state" />

</td> 
<td> <span id="state_lbl_error" class="lblErr"></span></td> 
</tr> 
 
<tr><td align="left"><span id="zipcode_lbl" class="lbl">Zip/Postal Code</span><br></td> 
<td VALIGN="TOP"><input TYPE="TEXT" NAME="zipcode" value="<?php echo($_REQUEST["zipcode"]); ?>" id="zipcode" size="30" class="noErr" MAXLENGTH="10"></td> 
<td> <span id="zipcode_lbl_error" class="lblErr"></span></td> 
</tr> 
 
<tr> 
<td align="left"><span id="phone_lbl" class="lbl">Phone</span><br></td> 
<td VALIGN="TOP"><input TYPE="TEXT" NAME="phone" value="<?php echo($_REQUEST["phone"]); ?>" id="phone" size="30" class="noErr" MAXLENGTH="25"></td> 
<td> <span id="phone_lbl_error" class="lblErr"></span></td> 
</tr> 
 

<?php
if($GLOBALS['SubscriptionDNA']['Settings']['Extra']=="1")
{
?>

<tr><td colspan="3"><br> 
<h3>Additional Information</h3></td></tr> 
 
<tr><td colspan="3">

<table border="0">
				<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	Adding custom fields.
////////////////////////////////////////////////////////////////////////////////////////////////////////////
					
					$str =$customFields["custom_fields"];
					$str1 = explode("~", $str);
					$cf = array();
					for($i= 0; $i < count($str1); $i++)
					{
						$str2 = explode("&", $str1[$i]);

						$caption = explode("=", $str2[0]);
						$type = explode("=", $str2[1]);
						$name = explode("=", $str2[2]);
						$default_value = explode("=", $str2[3]);
						$value[1] = $_POST[$name[1]];
						if($name[1])
						{
							echo "<tr>
								<td><input type='hidden' name='hd_custom_fields[]' value='".substr($name[1],3)."'/>
								".$caption[1].":</td>";
								if($type[1] == 'text')
								{
									$text_val = (empty($value[1])) ? $default_value[1] : $value[1];
									echo '<td><input type="text" name="'. $name[1] .'" id="'. $name[1] .'" value="'. htmlentities( $text_val ) .'"  size="30"></td>';
									//echo $out;
								}
								
								if($type[1] == 'checkbox')
								{									
									if($default_value[1])
									{
										$checkbox_list = explode(",", $default_value[1]);
										$selected_value_list =$value[1];
										echo("<td>");
										
										for($j = 0; $j <count($checkbox_list); $j++)
										{											
											$selected_val = '';
											for($k = 0; $k <count($selected_value_list); $k++)
											{
												if($checkbox_list[$j]==$selected_value_list[$k])
												{
													$selected_val = "checked";
													break;
												}	
											}	
												
											echo "<input style='width: 15px' name='".$name[1]."[]"."' type='".$type[1]."' id='".$name[1]."' ".$selected_val." type='".$type[1]."' value='".$checkbox_list[$j]."' /> ".$checkbox_list[$j]." ";	
										}
										echo("</td>");
									}
									else
									{
										echo "<td><input style='width: 15px' name='".$name[1]."[]"."' id='".$name[1]."' type='".$type[1]."' value='".$value[1]."' /></td>";	
									}
									
								}
								
								if($type[1] == 'radio')
								{
									if($default_value[1])
									{
										$radio_list = explode(",", $default_value[1]);
										echo("<td>");
										for($j = 0; $j <count($radio_list); $j++)
										{											
											if($value[1] == $radio_list[$j]) 
												$sel = "checked";
											else
												$sel = '';	
												
											echo "<input style='width: 15px' name='".$name[1]."' type='".$type[1]."' id='".$name[1]."' ".$sel." type='".$type[1]."' value='".$radio_list[$j]."' /> ".$radio_list[$j]."  ";	
										}
										echo("</td>");
									}
									else
									{
										echo "<td><input style='width: 15px' name='".$name[1]."' id='".$name[1]."' type='".$type[1]."' value='".$value[1]."' /></td>";	
									}
									
								}
								
								if($type[1] == 'textarea')
								{
									echo '<td><textarea name="'.$name[1].'" id="'.$name[1].'" >'.htmlentities($value[1]).'</textarea></td>';
								}
								
								if($type[1] == 'select')
								{											
									if($default_value[1])
									{
										$value_list = explode(",", $default_value[1]);
										
										echo "<td><select name='".$name[1]."' id='".$name[1]."'>";										
										for($j = 0; $j <count($value_list); $j++)
										{											
											if($value_list[$j]==$value[1])
												echo "<option selected value='".$value_list[$j]."'>".$value_list[$j]."</option>";
											else	
												echo "<option value='".$value_list[$j]."'>".$value_list[$j]."</option>";
										}
										echo "</select></td>";
									}									
								}

								if($type[1] == 'multi_select')
								{		
									if($default_value[1])
									{
										$multiselect_list = explode(",", $default_value[1]);
										$selected_value_list = explode(",", $value[1]);

										echo "<td><select name='".$name[1]."[]' multiple id='".$name[1]."'>";
										
										for($j = 0; $j <count($multiselect_list); $j++)
										{
											$selected_val = '';
											for($k = 0; $k <count($selected_value_list); $k++)
											{
												if($multiselect_list[$j]==$selected_value_list[$k])
												{
													$selected_val = "selected";
													break;	
												}	
											}

											//echo 'option '.$selected_val.' value="'.$multiselect_list[$j].'">'.$multiselect_list[$j].'option' . "<br>";
											echo '<option '.$selected_val.' value="'.$multiselect_list[$j].'">'.$multiselect_list[$j].'</option>';
										}
										
										echo "</select></td>";
									}									
								}
									
							echo "</tr>\n";								
						}		
					}

////////////////////////////////////////////////////////////////////////////////////////////////////////////
				
				?>
</table>

</td></tr>

<?php
}
?>



<tr><td colspan="3"><br>
<h3>Referred By</h3></td></tr> 
 
<tr> 
<td align="left"><span id="how_referred_lbl" class="lbl"><span id="how_referred_member">Current member</span></span>;</td> 
<td><input TYPE="TEXT" NAME="how_referred" onkeydown="if(this.value.length>1){document.getElementById('how_referred_list').value='';document.getElementById('how_referred_list').disabled=true;document.getElementById('how_referred_other').className='how_referred_other';}else{document.getElementById('how_referred_list').disabled=false;document.getElementById('how_referred_other').className='';}" onchange="if(this.value==''){document.getElementById('how_referred_list').disabled=false;document.getElementById('how_referred_other').className='';}else{document.getElementById('how_referred_list').value='';document.getElementById('how_referred_list').disabled=true;document.getElementById('how_referred_other').className='how_referred_other';}" size="30" class="noErr" MAXLENGTH="22"></td> 
</tr> 
 
<tr> 
<td align="left"><span id="how_referred_lbl" class="lbl"><span id="how_referred_other">Or select from dropdown</span></span></td> 
<td><select NAME="how_referred" id="how_referred_list" class="noErr" size="1"  onchange="if(this.value==''){document.getElementById('how_referred_member').className='';}else{document.getElementById('how_referred_member').className='how_referred_other';}" > 
<option></option> 
<option value="Magazine">Magazine</option> 
<option value="TV">Television</option> 
<option value="Google">Google search</option> 
<option value="Yahoo">Yahoo search</option> 
<option value="Youtube">YouTube</option> 
</select></td> 
</tr> 

<tr><td colspan="3"><br></td></tr>

<tr>
<td colspan="2">
<input style="width: 15px;" type="checkbox" class="noErr" name="agree" id="agree" value="0" <?php if($_REQUEST["agree"]=="1") echo("checked"); ?>><span id="agree_lbl" class="lbl"> I have read and agree to all the Terms and Conditions</span></td>
<td><span id="agree_lbl_error" class="lblErr"></span></td>
</tr>

<tr>
<td></td>
<td colspan="2"><br><br> 
<input TYPE="submit" name="x_submit" VALUE="Click here to submit form" onclick="return checkForm(this.form);" class="noErr" style="font-size: 13pt; width: 200px;"></td>
</tr>
</table>
</form> 
<?php
if($_POST)
{
	?>
	<script>
		document.getElementById("how_referred_list").value="<?php echo($_REQUEST["how_referred_list"]); ?>";
		document.getElementById("cc_type").value="<?php echo($_REQUEST["cc_type"]); ?>";
		document.getElementById("cc_exp_month").value="<?php echo($_REQUEST["cc_exp_month"]); ?>";
		document.getElementById("cc_exp_year").value="<?php echo($_REQUEST["cc_exp_year"]); ?>";
		document.getElementById("country").value="<?php echo($_REQUEST["country"]); ?>";
		document.getElementById("stateList").value="<?php echo($_REQUEST["stateList"]); ?>";
	</script>
	<?php
}
?> 
 
<br><br> 
<i>Processing may take a few seconds, afterwards you will be able to login instantly and a paid receipt will be sent to you by e-mail.</i> 

</div> 
