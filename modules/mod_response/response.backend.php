<?php
/**
* response.backend.php
* script for all actions with response
* @package response Package of SEOCMS
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 24.02.2011
* @copyright (c) 2010+ by SEOTM
*/
if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] );
include_once( SITE_PATH.'/modules/mod_response/response.defines.php' );


if(!defined("_LANG_ID")) {$pg = new PageAdmin();} 

if( !isset( $_REQUEST['module'] ) ) $module = NULL;
else $module = $_REQUEST['module'];

//Blocking to execute a script from outside (not Admin-part) 
if ( !$pg->logon->isAccessToScript($module)) exit;

if( !isset($_REQUEST['task']) || empty($_REQUEST['task']) ) $task='show';
else $task=$_REQUEST['task'];

if( isset($_REQUEST['delimg']) and ( !empty($_REQUEST['delimg'])) ) {
    $task='delimg';
    $delimg = $_REQUEST['delimg'];
    $delimg2=NULL;
}
if( isset($_REQUEST['delimg2']) and ( !empty($_REQUEST['delimg2'])) ) {
    $task='delimg';
    $delimg=NULL;
    $delimg2 = $_REQUEST['delimg2'];
}

if( !isset( $_REQUEST['fln'] ) ) $fln= _LANG_ID;
else $fln = $_REQUEST['fln']; 

if( !isset( $_REQUEST['fltr'] ) ) $fltr= NULL;
else $fltr = $_REQUEST['fltr'];
 
if( !isset( $_REQUEST['fltr2'] ) ) $fltr2 = NULL; 
else $fltr2 = $_REQUEST['fltr2'];

if( !isset( $_REQUEST['srch'] ) ) $srch = NULL;
else $srch = $_REQUEST['srch'];

if( !isset( $_REQUEST['srch2'] ) ) $srch2 = NULL; 
else $srch2 = $_REQUEST['srch2'];
//echo '<br> srch='.$srch;

if( !isset( $_REQUEST['sort'] ) ) $sort = NULL;  
else $sort = $_REQUEST['sort'];

if( !isset( $_REQUEST['start'] ) ) $start = 0;  
else $start = $_REQUEST['start'];

if( !isset($_REQUEST['display']) || ( isset($srch) and empty($srch)) ) $display=20;
else $display=$_REQUEST['display'];


if( !isset( $_REQUEST['id'] ) ) $id = NULL;
else $id = $_REQUEST['id']; 

if( !isset( $_REQUEST['visible'] ) ) $visible = NULL;
else $visible = $_REQUEST['visible']; 

if( !isset( $_REQUEST['group_d'] ) ) $group_d = NULL;
else $group_d = $_REQUEST['group_d']; 

if( !isset( $_REQUEST['url'] ) ) $url = NULL;
else $url = $_REQUEST['url']; 

if( !isset( $_REQUEST['name'] ) ) $name = NULL;
else $name = $_REQUEST['name']; 

if( !isset( $_REQUEST['translit'] ) ) $translit = NULL;
else $translit = $_REQUEST['translit'];

if( !isset( $_REQUEST['short'] ) ) $short = NULL;
else $short = $_REQUEST['short'];

if( !isset( $_REQUEST['full'] ) ) $full = NULL;
else $full = $_REQUEST['full']; 

if( !isset( $_REQUEST['title'] ) ) $title = NULL;
else $title = $_REQUEST['title'];

if( !isset( $_REQUEST['description'] ) ) $description = NULL;
else $description = $_REQUEST['description'];

if( !isset( $_REQUEST['keywords'] ) ) $keywords = NULL;
else $keywords = $_REQUEST['keywords'];

if( !isset( $_REQUEST['move'] ) ) $move = NULL;
else $move = $_REQUEST['move']; 

if( !isset( $_REQUEST['img'] ) ) $img = NULL;
else $img = $_REQUEST['img']; 

if( !isset( $_REQUEST['img2'] ) ) $img2 = NULL;
else $img2 = $_REQUEST['img2']; 

if( $task=='savereturn') {$task='save'; $action='return';}
else $action=NULL;

$Obj = new Response($pg->logon->user_id, $module, 10, $sort, $start, '100%');

$Obj->module = $module;
$Obj->display = $display;
$Obj->sort = $sort;
$Obj->start = $start;
$Obj->fln = $fln;
$Obj->srch = $srch;
$Obj->srch2 = $srch2;
$Obj->fltr = $fltr;
$Obj->fltr2 = $fltr2;

$Obj->id=$id; 
$Obj->visible=$visible;
$Obj->group_d=$group_d;
$Obj->url=$Obj->Form->GetRequestTxtData($url);
$Obj->name=$name;
$Obj->translit=$translit;
$Obj->short = $short;
$Obj->full = $full;
$Obj->title=$title;
$Obj->description=$description;
$Obj->keywords=$keywords;
$Obj->img = $Obj->Form->GetRequestTxtData($img);
$Obj->img2 = $Obj->Form->GetRequestTxtData($img2);

$Obj->move=$move;  

$Obj->script=$_SERVER['PHP_SELF']."?module=$Obj->module&display=$Obj->display&start=$Obj->start&sort=$Obj->sort&fltr=$Obj->fltr&fltr2=$Obj->fltr2&srch=$Obj->srch&srch2=$Obj->srch2";

switch( $task ) {
    case 'show':
        $Obj->show();
        break;
    
    case 'edit':
        if (!$Obj->edit()) echo "<script>window.location.href='$Obj->script';</script>";
        break;
    
    case 'new':
        $Obj->edit();
        break;
    
    case 'save':
        if ( $Obj->CheckFields()!=NULL ) {
          $Obj->edit();
          return false;
        }
        //phpinfo();
        if ( isset($_FILES["filename"]["name"]) AND !empty($_FILES["filename"]["name"]) ) {
          //$filename_name = substr($filename, strrpos('\\',$filename));
          //echo '<br>$_FILES["filename"]["name"]='.$_FILES["filename"]["name"];
          $file = substr($_FILES["filename"]["name"],0, strrpos($_FILES["filename"]["name"], "."));
          $ext = substr($_FILES["filename"]["name"],1 + strrpos($_FILES["filename"]["name"], "."));
          $Obj->img = $file.'_'.time().'.'.$ext;
          if ( !file_exists (response_Img_Full_Path) ) mkdir(response_Img_Full_Path,0777);
          else chmod(response_Img_Full_Path,0777);
          $uploaddir = response_Img_Full_Path.$Obj->img;
          //echo '<br>response_Img_Full_Path='.response_Img_Full_Path.' $uploaddir='.$uploaddir;
          if ( !copy($_FILES["filename"]["tmp_name"],$uploaddir) ) {
              chmod(response_Img_Full_Path,0755);
              $Obj->Err = $Obj->Err.$Obj->Msg->show_text('MSG_ERR_FILE_MOVE', TblSysTxt).'<br>';
              $Obj->edit();
              return false;
          }
          chmod(response_Img_Full_Path,0755);
        }                      
        if ( isset($_FILES["filename2"]["name"]) AND !empty($_FILES["filename2"]["name"]) ) {
          //$filename_name = substr($filename, strrpos('\\',$filename));
          //echo '<br>$_FILES["filename"]["name"]='.$_FILES["filename2"]["name"];
          $file = substr($_FILES["filename2"]["name"],0, strrpos($_FILES["filename2"]["name"], "."));
          $ext = substr($_FILES["filename2"]["name"],1 + strrpos($_FILES["filename2"]["name"], "."));
          $Obj->img2 = $file.'_'.time().'.'.$ext;
          if ( !file_exists (response_Img_Full_Path) ) mkdir(response_Img_Full_Path,0777);
          else chmod(response_Img_Full_Path,0777);
          $uploaddir = response_Img_Full_Path.$Obj->img2;
          //echo '<br>response_Img_Full_Path='.response_Img_Full_Path.' $uploaddir='.$uploaddir;
          if ( !copy($_FILES["filename2"]["tmp_name"],$uploaddir) ) {
              chmod(response_Img_Full_Path,0755);
              $Obj->Err = $Obj->Err.$Obj->Msg->show_text('MSG_ERR_FILE_MOVE', TblSysTxt).'<br>';
              $Obj->edit();
              return false;
          }
          chmod(response_Img_Full_Path,0755);
        }  

        if ( $Obj->save() ){
          //  echo 'Ok';
          if( $action=='return' ) { echo "<script>window.location.href='".$Obj->script."&task=edit&id=$Obj->id';</script>";}
          else { echo "<script>window.location.href='".$Obj->script."';</script>";}  
        }
        else{
          echo 'ERROR!';
          //echo "<script>window.location.href='$Obj->script';</script>";
        }
        break;
    
    case 'delete':
        if( !isset($_REQUEST['id_del']) ) $id_del=NULL;
        else $id_del = $_REQUEST['id_del'];
        if ( !empty($id_del) ) {
            $del=$Obj->del( $id_del );
            //if ( $del > 0 ) echo "<script>window.alert('".$pg->Msg->get_msg('_SYS_DELETED_OK')." $del');</script>";
            //else $pg->Msg->show_msg('_ERROR_DELETE');
            if( !$del ) $pg->Msg->show_msg('_ERROR_DELETE');
        }
        else $pg->Msg->show_msg('_ERROR_SELECT_FOR_DEL');
        echo "<script>window.location.href='$Obj->script';</script>";
        break;
    
    case 'cancel':
        echo "<script>window.location.href='$Obj->script';</script>";
        break;
    
    case 'up':
        $Obj->up(TblModresponse);
        echo "<script>window.location.href='$Obj->script';</script>";
        break;

    case 'down':
        $Obj->down(TblModresponse);
        echo "<script>window.location.href='$Obj->script';</script>";
        break;                      
    case 'delimg':
        if ( !$Obj->DelItemImage($delimg, $delimg2)){
            $Obj->Err = $Obj->Msg->show_text('MSG_IMAGE_NOT_DELETED')."<br>";
        }
        $Obj->edit();                      
}

?>