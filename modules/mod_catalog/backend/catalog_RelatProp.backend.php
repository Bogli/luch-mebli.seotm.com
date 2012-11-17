<?php

/**
 * catalogcontent.backend.php
 * script for all actions with Catalog content
 * @package Catalog Package of SEOCMS
 * @author Panarin Sergey  <sp@seotm.com>
 * @version 1.0, 21.01.2012
 * @copyright (c) 2010+ by SEOTM
 *
 */

if (!defined("SITE_PATH"))
    define("SITE_PATH", $_SERVER['DOCUMENT_ROOT']);
include_once(SITE_PATH . '/modules/mod_catalog/catalog.defines.php' );

if (!defined("_LANG_ID")) {
    $pg = &check_init('PageAdmin', 'PageAdmin');
}

if (!isset($_REQUEST['module']))
    $module = NULL;
else
    $module = $_REQUEST['module'];

//Blocking to execute a script from outside (not Admin-part)
if (!$pg->logon->isAccessToScript($module))
    exit;
/**
 * @var $CatalogRelatProp catalog_RelatProp
 */
$CatalogRelatProp = &check_init('catalog_RelatProp', 'catalog_RelatProp', "'" . $pg->logon->user_id . "', '$module'");

if (!isset($_REQUEST['task']) || empty($_REQUEST['task']))
    $task = 'show';
else
    $task = $_REQUEST['task'];

if (!isset($_REQUEST['tbl']) || empty($_REQUEST['tbl']))
    $tbl = NULL;
else
    $tbl = $_REQUEST['tbl'];

if (!isset($_REQUEST['id_prop']) || empty($_REQUEST['id_prop']))
    $id_prop = NULL;
else
    $id_prop = $_REQUEST['id_prop'];

if (!isset($_REQUEST['id_cat']) || empty($_REQUEST['id_cat']))
    $id_cat = 0;
else
    $id_cat = $_REQUEST['id_cat'];

if (!isset($_REQUEST['propStr']) || empty($_REQUEST['propStr']))
    $propStr = '';
else
    $propStr = $_REQUEST['propStr'];

if (!isset($_REQUEST['start']) || empty($_REQUEST['start']))
    $start = 0;
else
    $start = $_REQUEST['start'];

if (!isset($_REQUEST['display']) || empty($_REQUEST['display']))
    $display = 20;
else
    $display = $_REQUEST['display'];

if (!isset($_REQUEST['add']) || empty($_REQUEST['add']))
    $add = NULL;
else
    $add = $_REQUEST['add'];

if (!isset($_REQUEST['del']) || empty($_REQUEST['del']))
    $del = NULL;
else
    $del = $_REQUEST['del'];

$CatalogRelatProp->id_prop=$id_prop;
$CatalogRelatProp->id_cat=$id_cat;
$CatalogRelatProp->tbl=$tbl;
$CatalogRelatProp->add=$add;
$CatalogRelatProp->del=$del;
$CatalogRelatProp->task=$task;
$CatalogRelatProp->propStr=$propStr;
$CatalogRelatProp->start=$start;
$CatalogRelatProp->display=$display;
$CatalogRelatProp->script="/admin/index.php?module=".$module."&id_prop=".$id_prop;
//echo '<br>$CatalogRelatProp->task='.$CatalogRelatProp->task;
switch ($CatalogRelatProp->task) {
    case 'show':
        $CatalogRelatProp->show();
        break;
    case 'CatalogShow':
        $CatalogRelatProp->showCatalogByPages();
        break;
    case 'CatalogInnerShow':
        $CatalogRelatProp->showCatalogPropPart();
        break;
    case 'addProp':
        $CatalogRelatProp->save();
        ?><div><?
        $CatalogRelatProp->show();
//        $CatalogRelatProp->showCatalogPropPart();
        $CatalogRelatProp->showCatalogByPages();
        ?></div><?
        break;
    case 'save':
        $CatalogRelatProp->saveMove();
        $CatalogRelatProp->show();
        break;
    case 'delete':
        $CatalogRelatProp->delete();
        $CatalogRelatProp->show();
        break;
}

?>
