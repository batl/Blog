<?php

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

	$te = explode("themes",$_SERVER['SCRIPT_FILENAME']);
	$tf = explode("PPT",trim($te[1])); 
	$themeName = str_replace("\\","",str_replace("\\\\","",str_replace("/","",str_replace("////","",$tf[0]))));	
	$path=dirname(realpath($_SERVER['SCRIPT_FILENAME']));
	$path_parts = pathinfo($path);
	$p = str_replace("wp-content","",$path_parts['dirname']);	
	$p = str_replace("themes","",$p);
	$p = str_replace("PPT","",$p);
	$p = str_replace($themeName,"",$p);
	$p = str_replace("template_","",$p);
	$p = str_replace("\\\\","",$p);
	$p = str_replace("////","",$p);			
	require( $p.'/wp-config.php' );
	
	global $PPT;

	if(is_numeric($_GET['link'])){
	
	// POST ID
	$THISID = strip_tags($_GET['link']);
	
	
	if(strtolower(constant('PREMIUMPRESS_SYSTEM')) == "shopperpress" || strtolower(constant('PREMIUMPRESS_SYSTEM')) == "classifiedstheme"){ 
	

		
		$URL  	= get_post_meta($THISID, "amazon_link", true);
		if($URL == ""){
			$URL  	= get_post_meta($THISID, "buy_link", true);
		}
		if($URL == ""){
			$URL  	= get_post_meta($THISID, "buy_link1", true);
		}
		if($URL == ""){
			$URL  	= get_post_meta($THISID, "buy_link2", true);
		}
		if($URL == ""){
			$URL  	= get_post_meta($THISID, "buy_link3", true);
		}
		if($URL == ""){
			$URL  	= get_post_meta($THISID, "buy_link4", true);
		}
		if($URL == ""){
			$URL  	= get_post_meta($THISID, "buy_link5", true);
		}
		
		$URL = $PPT->AffiliateLink($URL,"",1);
										
	}else{
	
	
		$URL  	= get_post_meta($THISID, "link", true);
		if($URL == ""){
			$URL  	= get_post_meta($THISID, "url", true);
		}	
	
	}
	
	
	if($URL == ""){
	
	die("<h1>Link Missing</h1>");
	}
	

	
	/* ============== UPDATE HIT COUNTER ====================== */
	
	$PPT->UpdateHits($THISID,get_post_meta($THISID, "hits", true));
 
	$pos = strpos($URL , 'http://'); $pos1 = strpos($URL , 'https');		
	if ($pos === false && $pos1 === false && $URL  != "") {		$URL = "http://".$URL ;		} 
?>

<html>
<META NAME="ROBOTS" CONTENT="NOINDEX, FOLLOW">
<META NAME="ROBOTS" CONTENT="INDEX, NOFOLLOW">
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
	<head>
		<title></title>
		<meta name="robots" content="noindex,nofollow" />
		<?php if(!empty($URL)) echo '<meta http-equiv="refresh" content="0; url='.$URL.'" />'; ?>
	</head>
	<body style="margin:0 auto;">
	 
	</body>
</html>

<?php } ?>