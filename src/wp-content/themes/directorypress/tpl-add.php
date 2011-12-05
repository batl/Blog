<?php
/*
Template Name: [Add/Submit Template]
*/

/***************** DO NOT EDIT THIS FILE *************************
******************************************************************

INFORMATION:
------------

This is a core theme file, you should not need to edit 
this file directly. Code changes maybe lost during updates.

LAST UPDATED: June 26th 2011
EDITED BY: MARK FAIL
------------------------------------------------------------------

******************************************************************/

define("PPT-PAGE","add");
$GLOBALS['tpl-add'] = 1;

global $PPT, $PPTFunction, $PPTDesign, $userdata; get_currentuserinfo(); // grabs the user info and puts into vars

$wpdb->hide_errors(); nocache_headers();



/* ================================= PAYMENT OPTIONS =============================== */

if(isset($_POST['customgateway'])){
	include(TEMPLATEPATH ."/PPT/class/class_payment.php");
	$PPTPayment 		= new PremiumPressTheme_Payment;
	$PPTPayment->CustomGateway($_POST['customgateway']);
}

/* ============================= PREMIUM PRESS FOR SUBMISSION ========================================= */

if(!isset($_POST['packageID']) && !isset($_GET['eid']) && get_option('pak_enabled') ==1){ $GLOBALS['nosidebar'] =1; }

if(isset($_POST['form'])){ $_POST['form'] = PPTOUTPUT($_POST['form']);  }

if(get_option("tpl_add_mustlogin") =="yes" && ( isset($_POST['packageID']) || get_option('pak_enabled') != 1) ){ $PPT->auth_redirect_login(); }

$PACKAGE_OPTIONS = get_option("packages");


/* ============================= PREMIUM PRESS FOR SUBMISSION ========================================= */

if(isset($_POST['action']) && !empty($_POST['action'])){

	$GLOBALS['premiumpress']['language'] = get_option("language");
	$PPT->Language();

	// CHECK FOR ADDITONAL CATEGORY PRICES
	$GLOBALS['ExtraPrice'] = 0;
	$catArray = array($_POST['CatSel'][1],$_POST['CatSel'][2],$_POST['CatSel'][3]);
	$runningCount = 0;
	foreach($catArray as $CATID){
		$price  = get_option('CatPrice_'.$CATID);
		if($price == ""){ $GLOBALS['ExtraPrice'] += 0; }else{ $GLOBALS['ExtraPrice'] += $price; }	
	}
	
	
	/* ============================= ONLY IF RECEP LINK ENABLED ========================================= */

	if(strtolower(PREMIUMPRESS_SYSTEM) == "directorypress" && get_option('display_rlink') == "yes" && !isset($_POST['norec']) && !isset($_GET['eid']) && !isset($_POST['step3'])  ){		
		
		$tcss = explode(" ",stripslashes(strip_tags(get_option("display_rlink_text"))));
			 
			if(strlen($_POST['r_link']) < 5){
			
			$GLOBALS['error'] 		= 1;
			$GLOBALS['error_type'] 	= "error"; //ok,warn,error,info
			$GLOBALS['error_msg'] 	= SPEC($GLOBALS['_LANG']['_tpl_add_error1']);
			
			$canContinue = false;
			
			}else{
			
				$website_data = fetch_URL($_POST['r_link']);
	 
				if(strlen($website_data) > 5){
				
					if(strlen($tcss[0]) == 0){
					
						$checkThis = stripslashes(get_option("display_rlink_text"));
					
					}else{
					
						$checkThis = $tcss[0];
					
					}
				
					$pos = strpos($website_data, $checkThis); 
					if ($pos === false) {

						$GLOBALS['error'] 		= 1;
						$GLOBALS['error_type'] 	= "error"; //ok,warn,error,info
						$GLOBALS['error_msg'] 	= SPEC($GLOBALS['_LANG']['_tpl_add_error1']);

					}else{
					
						$GLOBALS['recpFound'] =1;
					
					}
					
					
				}else{
				
					$GLOBALS['error'] 		= 1;
					$GLOBALS['error_type'] 	= "error"; //ok,warn,error,info
					$GLOBALS['error_msg'] 	= SPEC($GLOBALS['_LANG']['_tpl_add_error1']);
				
				}
			}
		}			
	
	/* ============================= ONLY IF RECEP LINK ENABLED ========================================= */
	
	
	
	
	

	$canContinue = $PPT->Action_Validate();

	if($canContinue){
 
		switch($_POST['action']){
		
			case "add": { 			
			  
				$NEW_POST_ID = $PPT->Action_Add();  
			
				// SEND EMAIL
				$emailID = get_option("email_admin_newlisting");					 
				if(is_numeric($emailID) && $emailID != 0){
					SendMemberEmail("admin", $emailID);
				}
				
				$_POST['eid'] = $NEW_POST_ID;
				if(is_numeric($_POST['eid'])){ }else{ $_POST['eid'] =0; }	
				
				// EXTRA OPTIONS WE MIGHT WANT TO USE LATER TO PLACE HERE JUST INCASE :)
				if(strtolower(constant('PREMIUMPRESS_SYSTEM')) == "auctionpress"){
				
					if(isset($_POST['TotalCost']) && is_numeric($_POST['TotalCost']) && $_POST['TotalCost'] > 0){
					 
					mysql_query("UPDATE $wpdb->usermeta SET meta_value=meta_value-".PPTCLEAN($_POST['TotalCost'])." WHERE meta_key='aim' AND user_id='".$userdata->ID."' LIMIT 1"); 				
					
					}
					
					// SEND EMAIL
					$emailID = get_option("email_auction_new");					 
					if(is_numeric($emailID) && $emailID != 0){
						SendMemberEmail($userdata->ID, $emailID);
					}
				}			
 	
						
			} break;
	
			case "edit": { 
			
				//if($PACKAGE_OPTIONS[$_POST['packageID']]['a1'] != 1){ $_POST['form']['short'] = strip_tags(strip_tags($_POST['form']['short']));  $_POST['form']['description'] = strip_tags(strip_tags($_POST['form']['description']));  }
			
				$canContinue = $PPT->Action_Edit();  
			
				//  SEND EMAIL
				$emailID = get_option("email_admin_listingedit");					 
				if(is_numeric($emailID) && $emailID != 0){
					SendMemberEmail("admin", $emailID);
				}	
					
				$emailID = get_option("email_user_listingedit");					 
				if(is_numeric($emailID) && $emailID != 0){
					SendMemberEmail($userdata->ID, $emailID);
				}
				
				$NEW_POST_ID = $_POST['eid'];
				
				//header("location:".get_option("manage_url")."?c=1");
				//exit();
				
 
			} break;
		}
		 
		if(($_POST['action'] == "add" || $_POST['action'] == "edit" ) && get_option('pak_enabled') ==1  ){
	 
		
			// WORK OUT ANY PACKAGE PRICES
			$GLOBALS['TotalPriceDue'] 	= $PACKAGE_OPTIONS[$_POST['packageID']]['price'];
			$_POST['rec'] 				= $PACKAGE_OPTIONS[$_POST['packageID']]['rec'];
			$_POST['rec_days'] 			= $PACKAGE_OPTIONS[$_POST['packageID']]['expire'];				
			$GLOBALS['TotalPriceDue'] 	+=$GLOBALS['ExtraPrice'];
			
			if(isset($_POST['NEWpackageID']) && $_POST['NEWpackageID'] > 0){
						$newPrice = $PACKAGE_OPTIONS[$_POST['NEWpackageID']]['price'] - $PACKAGE_OPTIONS[$_POST['packageID']]['price'] + $GLOBALS['ExtraPrice'];
						$ORDERTYPE = "UPGRADE";
						}else{
						$newPrice = $GLOBALS['TotalPriceDue'];
						$ORDERTYPE = "NEW";
			}	
			if($_POST['NEWpackageID'] == ""){ $_POST['NEWpackageID'] = $_POST['packageID']; }		
			$_POST['price']['total'] 	= $newPrice;
			$_POST['orderid'] 			= $NEW_POST_ID."-".$userdata->ID."-".$ORDERTYPE."-".$_POST['NEWpackageID'];
			$_POST['description'] 		= "".$_POST['orderid']; //Cart Order ID:
					
					
					
			if($newPrice > 0){
					 
					// UPDATE PRICE
					if(strtolower(constant('PREMIUMPRESS_SYSTEM')) != "realtorpress" && strtolower(constant('PREMIUMPRESS_SYSTEM')) != "classifiedstheme" ){ update_post_meta($NEW_POST_ID, "price", $newPrice); }
					
					// SAVE THE ORDER INTO THE DATABASE
					include(str_replace("functions/","",THEME_PATH)."/PPT/func/func_paymentgateways.php");
					include(TEMPLATEPATH ."/PPT/class/class_payment.php");	
					$PPTPayment	= new PremiumPressTheme_Payment;
					
					// BUILD ORDER DESCRIPTION
					$OrderData = "\r\n --------- POST ID: ". $NEW_POST_ID. " ------------- \r\n";
					$OrderData .= "\r\n Package: ".$PACKAGE_OPTIONS[$_POST['NEWpackageID']]['name']. "\r\n";
					$OrderData .= "\r\n Category: ".$PPT->CategoryFromID($_POST['CatSel']). "\r\n";
					$OrderData .= "\r\n Name: ".PPTOUTPUT($_POST['form']['title']). "\r\n";
					$OrderData .= "\r\n Short Description: ".PPTOUTPUT($_POST['form']['short']). "\r\n";
					$OrderData .= "\r\n Order ID: ".$_POST['orderid']. "\r\n";
					
					$GLOBALS['orderData'] = strip_tags($OrderData);			 
					$GLOBALS['orderItems'] = $NEW_POST_ID."x1";
					// DATA TO ADD TO THE PAYMENT CALL
					$GLOBALS['total'] 		= $newPrice;
					$GLOBALS['subtotal'] 	= $newPrice;
					$GLOBALS['shipping'] 	= 0;  
					$GLOBALS['tax'] 		= 0; 
						
					$PPTPayment->InsertOrder("",$_POST['orderid'],0);
					
			}
		
		}  

	}

}

/* ============================= PREMIUM PRESS EDIT DATA ========================================= */

if(isset($_GET['eid']) && is_numeric($_GET['eid']) ){

	$data = $PPT->Getlistingdata($user_ID, $_GET['eid'],$user_ID);

	$_POST['packageID'] = $data['packageID'][0];
 	if($_POST['packageID'] == ""){ $_POST['packageID'] = 1; }

	$tags=""; 
	$posttags = get_the_tags($_GET['eid']);
		if ($posttags) {
			foreach($posttags as $tag) {
			$tags  .= $tag->name . ','; 
		}
	}
 
	$DefaultCat 	= $data['cats'][0]->cat_ID;
	$DefaultCat1 	= $data['cats'][1]->cat_ID;

	$DefaultCat2 	= $data['cats'][2]->cat_ID;

}
	if(isset($data)){
	$DefaultCat = $data['cats'][0]->cat_ID;
	$DefaultCat1 = $data['cats'][1]->cat_ID;
	$DefaultCat2 = $data['cats'][2]->cat_ID;
	}elseif(isset($_POST['CatSel'][1])){
	$DefaultCat = $_POST['CatSel'][1];
	$DefaultCat1 = $_POST['CatSel'][2];
	$DefaultCat2 = $_POST['CatSel'][3];
	}else{
	$DefaultCat = 0;
	$DefaultCat1 = 0;
	$DefaultCat2 = 0;
	}
/* ============================= PREMIUM PRESS DELETE PHOTO ========================================= */

if(isset($_GET['eid']) && isset($_GET['pid']) && is_numeric($_GET['eid']) ){

	$GLOBALS['premiumpress']['language'] = get_option("language");
	$PPT->Language();

	if(isset($_GET['display'])){
	$PPT->SetDisplayPhoto($_GET['eid'],$_GET['pid'],$user_ID);
	
	$GLOBALS['error'] 		= 1;
	$GLOBALS['error_type'] 	= "tip"; //ok,warn,error,info
	$GLOBALS['error_msg'] 	= SPEC($GLOBALS['_LANG']['_tpl_add_error2']);
		
	}else{
	$canContinue = $PPT->deletephoto($_GET['eid'],$_GET['pid'],$user_ID);
	} 
}

/* ============================= PREMIUM PRESS PAGE ATTRIBUTES ========================================= */

if(isset($_POST['action']) && $_POST['action'] == "step1"){
		// CHECK FOR ADDITONAL CATEGORY PRICES
		$catArray = array($_POST['CatSel'][1],$_POST['CatSel'][2],$_POST['CatSel'][3]);
		$TotalCost = get_option("auction_price_submit");
		foreach($catArray as $CATID){
			$price  = get_option('CatPrice_'.$CATID);
			if($price == ""){ $TotalCost += 0; }else{ $TotalCost += $price; }	
		}
 
}

// quick fix for clicking the back button when editing a listing to show the new content
if(isset($_GET['eid']) && isset($_POST['action']) and $_POST['action'] == "step1"){
unset($data);
}

 
/* ================ LOAD TEMPLATE FILE =========================== */	
 
if(file_exists(str_replace("functions/","",THEME_PATH)."themes/".get_option('theme')."/_tpl_add.php")){
		
		include(str_replace("functions/","",THEME_PATH)."themes/".get_option('theme').'/_tpl_add.php');
		
}else{ 
	
		include("template_".strtolower(PREMIUMPRESS_SYSTEM)."/_tpl_add.php");
	
}
	
?>