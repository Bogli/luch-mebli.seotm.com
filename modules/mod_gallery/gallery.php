<?php
/**
* gallery.php
* script for all actions with photogallery
* @package Gallery Package of SEOCMS
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 07.07.2012
* @copyright (c) 2010+ by SEOTM
*/

if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] );
include_once( SITE_PATH.'/include/defines.php' );

$Page = &check_init('PageUser', 'PageUser');
$Page->FrontendPages->lang_id = _LANG_ID;
$Page->FrontendPages->page = PAGE_GALLERY;
$Page->FrontendPages->page_txt = $Page->FrontendPages->GetPageTxt($Page->FrontendPages->page);


$ModulesPlug = new ModulesPlug();
$id_module = $ModulesPlug->GetModuleIdByPath( '/modules/mod_gallery/gallery.backend.php' );

if(!isset ($Page->Gallery))
    $Page->Gallery = &check_init('GalleryLayout', 'GalleryLayout');
$Page->Gallery->module =  $id_module;    // 156
$Page->module=$id_module;

$Gallery = &$Page->Gallery;
$Gallery->FrontendPages = &$Page->FrontendPages;

if( !isset( $_REQUEST['task'] ) ) $Gallery->task = 'showall';
else $Gallery->task = $_REQUEST['task'];

if( !isset( $_REQUEST['start'] ) ) $Gallery->start = 0;
else $Gallery->start = $_REQUEST['start'];

if( !isset( $_REQUEST['sort'] ) ) $Gallery->sort = NULL;
else $Gallery->sort = $_REQUEST['sort'];

if( !isset( $_REQUEST['display'] ) ) $Gallery->display = 18;
else $Gallery->display = $_REQUEST['display'];

if(!isset($_REQUEST['page'])) $Gallery->page=1;
else $Gallery->page=$_REQUEST['page'];
if($Gallery->page>1) $start = ($Gallery->page-1)*$display;
if($Gallery->page=='all') {
   $start = 0;
   $display = 999999;
}

if( !isset( $_REQUEST['cat'] ) ) $Gallery->category = NULL; // category cod
else {
    $Gallery->category = $Gallery->GetCategoryIdByTranslit($_REQUEST['cat']);
    if(!$Gallery->category)
        $Page->Set_404();
    else
        $Gallery->catData = $Gallery->Spr->GetDataByCod(TblModGalleryCat, $Gallery->category, $Gallery->lang_id);
}

if( !isset( $_REQUEST['position'] ) ) $Gallery->id = NULL;
else {
    $Gallery->id = $Gallery->GetPositionIdByTranslit($_REQUEST['position'], $Gallery->category);
    if(!$Gallery->id)
        $Page->Set_404();
    else
        $Gallery->pageData = $Gallery->GetGalleryData($Gallery->id);
}

if( !isset( $_REQUEST['ajax'] ) ) $Gallery->ajax = false;
else $Gallery->ajax = true;

$Gallery->SetMetaData();

if ( empty($Gallery->title) ) $title = $Page->FrontendPages->GetTitle();
else $title = $Gallery->title;
if ( empty($Gallery->description) ) $Description = $Page->FrontendPages->GetDescription();
else $Description = $Gallery->description;
if ( empty($Gallery->keywords) ) $Keywords = $Page->FrontendPages->GetKeywords();
else $Keywords = $Gallery->keywords;

$Page->SetTitle( $title );
$Page->SetDescription( $Description );
$Page->SetKeywords( $Keywords );

if(!$Gallery->ajax)
$Page->WriteHeader();

//echo '<br>$Gallery->task='.$Gallery->task;
switch( $Gallery->task ){
    case 'last':
    case 'showall':
        $Gallery->ShowGallerysByPages();
        break;

    case 'cat':
        if($Gallery->category!=NULL) {
            $Gallery->ShowGallerysByPages();
        }
        else
            $Gallery->ShowGalleryCat();
        break;

    case 'position':
        $Gallery->ShowGalleryFull();
        break;

    case 'add':
        $Gallery->ShowAddForm();
        break;
}
if(!$Gallery->ajax)
    $Page->WriteFooter();
?>