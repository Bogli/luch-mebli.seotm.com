<?php
if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] ); 
include_once( SITE_PATH.'/include/defines.php' );

 
$Page = new PageUser();
if(!isset ($Page->response))
    $Page->response = &check_init('responseLayout', 'responseLayout');
$response = &$Page->response;

$ModulesPlug = new ModulesPlug();
$id_module = $ModulesPlug->GetModuleIdByPath( 'mod_response/response.backend.php' );
$Page->module = $id_module;
  
if(empty($Page->FrontendPages)) 
    $Page->FrontendPages = &check_init('FrontendPages', 'FrontendPages');
 
if( !isset ( $_REQUEST['task'] ) ) $response->task = 'show';
else $response->task = addslashes(strip_tags(trim($_REQUEST['task'])));

if( !isset ( $_REQUEST['group'] ) ) $response->group_id = 1;
else $response->group_id = $response->GetIdGroupByTranslit( addslashes(strip_tags(trim($_REQUEST['group']))) );

if( !isset ( $_REQUEST['item'] ) ) $item = NULL;
else $response->item = $response->GetIdCommentByTranslit( addslashes(strip_tags(trim($_REQUEST['item']))) );

if( ( isset($_REQUEST['group']) AND empty($response->group_id) ) OR ( isset($_REQUEST['item']) AND empty($response->item) ) )
    $Page->Set_404();

if( !isset( $_REQUEST['start'] ) ) $start = 0;
else $start = $_REQUEST['start'];

if( !isset( $_REQUEST['sort'] ) ) $sort = NULL;
else $sort = $_REQUEST['sort'];

if( !isset( $_REQUEST['display'] ) ) $display = 10;
else $display = $_REQUEST['display'];

if(!isset($_REQUEST['page'])) $page=1;
else $page=$_REQUEST['page'];
if($page>1) $start = ($page-1)*$display;
if($page=='all') {
   $start = 0;
   $display = 999999;
}
$response->display = $display;
$response->page = $page;
$response->start = $start;
$response->sort = $sort;

$Page->FrontendPages->page = PAGE_COMMENT;

$response->SetMetaData();

//$title = $response->GetNameOfComment($response->item).' | Отзывы и комментарии | '.META_TITLE;
//$Description = $response->GetNameOfComment($response->item).'. Отзывы и комментарии. '.META_DESCRIPTION;
//$Keywords = $response->GetNameOfComment($response->item).', Отзывы и комментарии, '.META_KEYWORDS;
//$Keywords = $response->GetNameOfComment($response->item).', Отзывы и комментарии, '.META_KEYWORDS;

$title_content = $response->multi['TXT_FRONT_USERS_RESPONSES'];
$Page->SetTitle( $response->title );
$Page->SetDescription( $response->description );
$Page->SetKeywords( $response->keywords );
  
$Page->WriteHeader();

$Page->Form->WriteContentHeader($title_content,false,false);

switch($response->task){
    /*case 'short':
        $response->ShowresponseShort();
        break;*/
    case 'show':
        $response->ShowresponseByPages();
        break;
    case 'position':
        $response->ShowresponseDetails();
        break;
    default: 
        $response->ShowresponseAll();
}
$Page->Form->WriteContentFooter();
$Page->WriteFooter();
?>