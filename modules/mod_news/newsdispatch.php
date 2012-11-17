<?
if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] ); 
include_once( SITE_PATH.'/modules/mod_news/news.defines.php' );

$Page = new PageAdmin();
$News = new NewsCtrl();

if( !isset( $_REQUEST['task'] ) ) $News->task = NULL;
else $News->task = $News->Form->GetRequestTxtData($_REQUEST['task'], 1);

if( !isset( $_REQUEST['subscr_start'] ) ) $News->subscr_start = 0;
else $News->subscr_start = $News->Form->GetRequestTxtData($_REQUEST['subscr_start'], 1);

if( !isset( $_REQUEST['subscr_cnt'] ) ) $News->subscr_cnt = 0;
else $News->subscr_cnt = $News->Form->GetRequestTxtData($_REQUEST['subscr_cnt'], 1);

switch( $News->task ) {
    case 'send':
        $News->MakeDispatch();
        break;
    default:
        break;
} 
 ?> 