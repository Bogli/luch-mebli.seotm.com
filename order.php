<?
  if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] ); 
  include_once( SITE_PATH.'/include/defines.php' );
  //include_once( SITE_PATH.'/modules/mod_user/user.defines.php' );
  //phpinfo();
  
  $Page = new PageUser();             

  //$logon = new  UserAuthorize();

  $title = 'Пользовательская корзина | '.META_TITLE;
  $Description = 'Пользовательская корзина. '.META_DESCRIPTION;
  $Keywords = 'Пользовательская корзина, '.META_KEYWORDS; 


/*  $Page->SetTitle( $title );
  $Page->SetDescription( $Description );
  $Page->SetKeywords( $Keywords );    
     */
  //$Page->WriteHeader();

  $scriptact='/modules/mod_order/order.frontend.php';
  include_once(SITE_PATH.$scriptact);

 // $Page->WriteFooter();
?>