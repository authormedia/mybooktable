<?php
//Sample PayPal Button Encryption: Copyright 2006-2010 StellarWebSolutions.com
//Not for resale  - license agreement at
//http://www.stellarwebsolutions.com/en/eula.php
//Updated: 2010 02 01

# private key file to use
$MY_KEY_FILE = "/home/angelah/public_html/paypal/private-key.pem";

# public certificate file to use
$MY_CERT_FILE = "/home/angelah/public_html/paypal/cert.pem";

# Paypal's public certificate
$PAYPAL_CERT_FILE = "/home/angelah/public_html/paypal/cert_key_from_paypal.pem";

# path to the openssl binary
$OPENSSL = "/usr/bin/openssl";


$form = array('cmd' => '_xclick',
        'business' => 'jcamomile@pobox.com',
        'cert_id' => 'PZ7ESMLDAJN2U',
        'lc' => 'US',
        'custom' => 'test',
        'invoice' => '',
        'currency_code' => 'USD',
        'no_shipping' => '1',
        'item_name' => 'Donation',
        'item_number' => '1',
	'amount' => '10'
	);


	$encrypted = paypal_encrypt($form);


function paypal_encrypt($hash)
{
	//Sample PayPal Button Encryption: Copyright 2006-2010 StellarWebSolutions.com
	//Not for resale - license agreement at
	//http://www.stellarwebsolutions.com/en/eula.php
	global $MY_KEY_FILE;
	global $MY_CERT_FILE;
	global $PAYPAL_CERT_FILE;
	global $OPENSSL;


	if (!file_exists($MY_KEY_FILE)) {
		echo "ERROR: MY_KEY_FILE $MY_KEY_FILE not found\n";
	}
	if (!file_exists($MY_CERT_FILE)) {
		echo "ERROR: MY_CERT_FILE $MY_CERT_FILE not found\n";
	}
	if (!file_exists($PAYPAL_CERT_FILE)) {
		echo "ERROR: PAYPAL_CERT_FILE $PAYPAL_CERT_FILE not found\n";
	}


	//Assign Build Notation for PayPal Support
	$hash['bn']= 'StellarWebSolutions.PHP_EWP2';

	$data = "";
	foreach ($hash as $key => $value) {
		if ($value != "") {
			//echo "Adding to blob: $key=$value\n";
			$data .= "$key=$value\n";
		}
	}

	$openssl_cmd = "($OPENSSL smime -sign -signer $MY_CERT_FILE -inkey $MY_KEY_FILE " .
						"-outform der -nodetach -binary <<_EOF_\n$data\n_EOF_\n) | " .
						"$OPENSSL smime -encrypt -des3 -binary -outform pem $PAYPAL_CERT_FILE";

	exec($openssl_cmd, $output, $error);

	if (!$error) {
		return implode("\n",$output);
	} else {
		return "ERROR: encryption failed";
	}
};
?> 
<HEAD>
<LINK REL=stylesheet HREF="/styles/stellar.css" TYPE="text/css">
<TITLE>PHP Sample Donation using PayPal Encrypted Buttons</TITLE>
</HEAD>
<BODY bgcolor=white>
<TABLE border=0>
<TR><TD align=center>
<h1>Sample Donation Page</h1>
<P>This page uses encrypted PayPal buttons for your security.</P>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target=_blank>
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="
<?PHP echo $encrypted; ?>">
<input type="submit" value="Donate $10">
</form>
<P><SMALL>(PayPal will open in a new window for demonstration purposes.)</SMALL></P>
</TD></TR></TABLE>
</BODY>
