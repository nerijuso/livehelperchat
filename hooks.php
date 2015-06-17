<?php

/*
 * *********************************************
 * ** LiveHelperChat Addon Module ***

  If you don't have one, please register here
  https://livehelperchat.com/order/now

  More About livehelperchat
  https://livehelperchat.com/
  
  More About livehelperchat addon for WHMCS
  http://nerijuso.lt/livehelperchat-com-addon-for-whmcs/
  
  License
  Licensed under the Apache License, Version 2.0. More about this license you can read here http://www.apache.org/licenses/LICENSE-2.0

 * *********************************************
 */

function LiveHelperChatJS($vars) {
	
	
	$q = @mysql_query("SELECT * FROM tbladdonmodules WHERE module = 'livehelperchat'");
	
	while ($arr = mysql_fetch_array($q)) {
		$settings[$arr['setting']] = html_entity_decode($arr['value']);
	}
	
	
	
	$url = '';
	if($settings['widget_click']){
		$url .= '(click)/internal/';
	} 
	
	if($settings['hide_offline']) {
		$url .= "(hide_offline)/true/";
	}
	
	if($settings['widget_click']) {
		$url .= "(check_operator_messages)/true/";
	}
	
	if($settings['leaveamessage']) {
		$url .= "(leaveamessage)/true/";
	}

	if($settings['disable_pro_active']) {
		$url .= "(disable_pro_active)/true/";
	}

	if($settings['noresponse']) {
		$url .= "(noresponse)/true/";
	}
	
	if($settings['position'] == 'Native placement - it will be shown where the html is embedded') {
		$url .= '(position)/original/';
	}elseif($settings['position'] == 'Bottom left corner of the screen') {
		$url .= '(position)/bottom_left/';
	}elseif($settings['position'] == 'Bottom right corner of the screen') {
		$url .= '(position)/bottom_right/';
	}elseif($settings['position'] == 'Middle right side of the screen') {
		$url .= '(position)/middle_right/';
	}elseif($settings['position'] == 'Middle left side of the screen') {
		$url .= '(position)/middle_left/';
	}
	
	
	if($settings['enabled']) {
		$script = "<script type=\"text/javascript\">
		var LHCChatOptions = {};</script>";
		
		if ($_SESSION['uid']) {
			$userid = $_SESSION['uid'];
			
			$command = "getclientsdomains";
			$adminuser = "admin";
			$values["clientid"] = $userid;
			
			$results = localAPI($command,$values,$adminuser);
			
			$command = "getclientsproducts";
			$products_results = localAPI($command,$values,$adminuser);
			
			$command = "getinvoices";
			$values_unpaid["userid"] = $userid;
			$values_unpaid["status"] = "Unpaid";
			$unpaidinvoices = localAPI($command,$values_unpaid,$adminuser);
			
			
		    $firstname = $vars['clientsdetails']['firstname'];
		    $lastname = $vars['clientsdetails']['lastname'];
		    $email = $vars['clientsdetails']["email"];
		    $companyname = $vars['clientsdetails']['companyname'];
		    $credit = $vars['clientsdetails']['credit'];
		   
		    $script .= "<script type=\"text/javascript\">
		    	LHCChatOptions.attr = new Array();
				LHCChatOptions.attr.push({'name':'First name','value':'$firstname','type':'hidden','size':6,'req':false});
				LHCChatOptions.attr.push({'name':'Last name','value':'$lastname','type':'hidden','size':6,'req':false});
				LHCChatOptions.attr.push({'name':'Email','value':'$email','type':'hidden','size':6,'req':false});
				LHCChatOptions.attr.push({'name':'Company name','value':'$companyname','type':'hidden','size':6,'req':false});
				LHCChatOptions.attr.push({'name':'Credit','value':'$credit','type':'hidden','size':6,'req':false});";
		    	if(count($results['domains']['domain'])) {
					foreach($results['domains']['domain'] as $domain) {
						$domainname = $domain['domainname'];
						$script .="LHCChatOptions.attr.push({'name':'Domain name','value':'$domainname','type':'hidden','size':6,'req':false});";
						$script .="LHCChatOptions.attr.push({'name':'{$domain['domainname']} expiry date','value':'{$domain['expirydate']}','type':'hidden','size':6,'req':false});";
						$script .="LHCChatOptions.attr.push({'name':'{$domain['domainname']} registrar','value':'{$domain['registrar']}','type':'hidden','size':6,'req':false});";
					}
					
		    	}
		    	
		    	if(count($unpaidinvoices['invoices']['invoice'])) {
					foreach($unpaidinvoices['invoices']['invoice'] as $invoice) {
						$invoicenumber = $invoice['invoicenum'];
						$invoiceid = $invoice['id'];
						$inv = $invoicenumber.'(ID:'.$invoiceid.')';
						$script .="LHCChatOptions.attr.push({'name':'Unpaid invoice','value':'$inv','type':'hidden','size':6,'req':false});";
						$script .="LHCChatOptions.attr.push({'name':'{$inv} duedate','value':'{$invoice['duedate']}','type':'hidden','size':6,'req':false});";
						$script .="LHCChatOptions.attr.push({'name':'{$inv} subtotal','value':'{$invoice['subtotal']}','type':'hidden','size':6,'req':false});";
					}
					
		    	}
		    	
   	    	    if(count($products_results['products']['product'])) {
					foreach($results['products']['product'] as $products) {
						$script .="LHCChatOptions.attr.push({'name':'Service name','value':'{$products['name']}','type':'hidden','size':6,'req':false});";
						$script .="LHCChatOptions.attr.push({'name':'{$products['name']} server ip','value':'{$products['serverip']}','type':'hidden','size':6,'req':false});";
						$script .="LHCChatOptions.attr.push({'name':'{$products['name']} next due date','value':'{$products['nextduedate']}','type':'hidden','size':6,'req':false});";
					}
		    	}  
		    	$script .= "</script>";
		}
		
		$script .= "<script type=\"text/javascript\">\nLHCChatOptions.opt = {widget_height:".$settings['widget_height'].",widget_width:".$settings['widget_height'].",popup_height:".$settings['popup_height'].",popup_width:".$settings['popup_width']."};
		(function() {
		var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
		var refferer = (document.referrer) ? encodeURIComponent(document.referrer.substr(document.referrer.indexOf('://')+1)) : '';
		var location  = (document.location) ? encodeURIComponent(window.location.href.substring(window.location.protocol.length)) : '';
		po.src = '{$settings['widget_domain']}/chat/getstatus/(top)/{$settings['pos_top']}/(units)/{$settings['unit']}/$url?r='+refferer+'&l='+location;
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
		})();
		</script>";
		return $script;
	}
}

add_hook('ClientAreaFooterOutput', 1, 'LiveHelperChatJS');
?>