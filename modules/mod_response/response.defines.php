<?php
// ================================================================================================
// System : SEOCMS
// Module : response.defines.php
// Date : 05.12.2006
// Purpose : All Definitions for module of response
// ================================================================================================
include_once( SITE_PATH.'/include/defines.php' );
include_once( SITE_PATH.'/modules/mod_response/response.class.php' );
include_once( SITE_PATH.'/modules/mod_response/responseLayout.class.php' );

define("MOD_response", true);

define("TblModresponse","mod_response");
define("TblModresponseTxt","mod_response_txt");
define("TblModresponseSprGroup","mod_response_spr_group");
//define("TblModresponseSprTxt","mod_response_spr_txt");
define("TblModresponseLinks","mod_response_links");

define("response_Path", "/modules/mod_response/");
define("response_Full_Path", SITE_PATH."/modules/mod_response/");
define("response_Img_Path", "/images/mod_response/");
define("response_Img_Full_Path", SITE_PATH."/images/mod_response/");
?>