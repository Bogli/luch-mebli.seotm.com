<?php
include_once( $_SERVER['DOCUMENT_ROOT'].'/include/defines.php' );
include_once( SITE_PATH.'/modules/mod_search/search.defines.php' );

//========= FIRST DEFINE PAGE LANGUAGE  BEGIN ===========
$Page = new PageUser();
//========= FIRST DEFINE PAGE LANGUAGE BEGIN  ===========

if(defined("MOD_NEWS") AND MOD_NEWS AND empty($Page->News) )
    $Page->News = &check_init('NewsLayout', 'NewsLayout');

if(defined("MOD_PAGES") AND MOD_PAGES AND empty($Page->FrontendPages) )
    $Page->FrontendPages = &check_init('FrontendPages', 'FrontendPages');

if(defined("MOD_CATALOG") AND MOD_CATALOG AND empty($Page->Catalog) )
    $Page->Catalog = &check_init('CatalogLayout', 'CatalogLayout');

if( empty($Page->Article) )
    $Page->Article = &check_init('ArticleLayout', 'ArticleLayout');

$Page->multi = $Page->multi;
$Search = new Search();

if( !isset ( $_REQUEST['task'] ) ) $task = NULL;
else $task = $_REQUEST['task'];

if( !isset ( $_REQUEST['query'] ) ) $query = '';
else {
    $query = addslashes(substr(strip_tags(trim($_REQUEST['query'])), 0,64));
    // cut unnormal symbols
    $query=preg_replace("/[^\w\x7F-\xFF\s\-]/", " ", $query);
    // delete double spacebars
    $query=str_replace(" +", " ", $query);
}

if( !isset ( $_REQUEST['modname'] ) ) $modname = 'all';
else $modname = $_REQUEST['modname'];


if($task==Null){
    $Title = $Page->multi['TXT_FRONT_MOD_SEARCH_ON_SITE'];
}
else{
    $Title = $Page->multi['TXT_FRONT_MOD_SEARCH_RESULT'];
}

$Description = $Page->multi['TXT_FRONT_MOD_SEARCH_ON_SITE'];
$Keywords = $Page->multi['TXT_FRONT_MOD_SEARCH_ON_SITE'];


$Page->SetTitle( $Title );
$Page->SetDescription( $Description );
$Page->SetKeywords( $Keywords );

$Page->WriteHeader();
$Page->Form->WriteContentHeader($Title.' "'.stripslashes($query).'"', false);?>
<div class="subBody">
<?
//echo '<br>$task='.$task.' $query='.$query.' $modname='.$modname;
if($task=='search' and strlen($query)>=3){
    $Search->ip = $_SERVER['REMOTE_ADDR'];
    $Search->query = $query;
    switch( $modname ) {
         case 'all':
                    // pages
                    $arr_rows1 = $Page->FrontendPages->QuickSearch($query);
                    if(count($arr_rows1)>0){
                        $Page->FrontendPages->ShowSearchResHead($Page->multi['TXT_FRONT_MOD_SEARCH_BY_DYNAMIC_PAGES']);
                        $Page->FrontendPages->ShowSearchRes($arr_rows1);
                    }

                    $rows2 = $Page->News->QuickSearch($query);
                    if($rows2>0){
                        $Page->FrontendPages->ShowSearchResHead($Page->multi['TXT_FRONT_MOD_SEARCH_BY_NEWS']);
                        $Page->News->ShowSearchResult($rows2);
                    }

                    $rows3 = $Page->Article->QuickSearch($query,1);
                    if($rows3>0){
                        $Page->FrontendPages->ShowSearchResHead($Page->multi['TXT_FRONT_MOD_SEARCH_BY_ARTICLES']);
                        $Page->Article->ShowSearchResult($rows3);
                    }
                    $arr_rows4 = $Page->Catalog->QuickSearch($query,1);
                    if(count($arr_rows4)>0){
                        $Page->FrontendPages->ShowSearchResHead($Page->multi['TXT_FRONT_MOD_SEARCH_IN_CATALOG']);
                        $Page->Catalog->ShowSearchResult($arr_rows4);
                    }
                    break;

                    if($rows1==0 AND $rows2==0 AND $rows3==0 AND $rows4==0){ $Page->FrontendPages->Form->ShowTextMessages($Page->multi['SEARCH_NO_RES']);}
                    break;
         case 'pages':
                   $Page->FrontendPages->ShowSearchResHead($Page->multi['TXT_FRONT_MOD_SEARCH_BY_DYNAMIC_PAGES']);
                   $Page->FrontendPages->ShowSearchRes($Page->FrontendPages->QuickSearch($query));
                   break;
         case 'news':
                    $Page->FrontendPages->ShowSearchResHead($Page->multi['TXT_FRONT_MOD_SEARCH_BY_NEWS']);
                    $Page->News->ShowSearchResult($Page->News->QuickSearch($query));
                    break;
         case 'articles':
                    $Page->FrontendPages->ShowSearchResHead($Page->multi['TXT_FRONT_MOD_SEARCH_BY_ARTICLES']);
                    $Page->Article->ShowSearchResult($Page->Article->QuickSearch($query));
                    break;
         case 'catalog':
                    $Page->FrontendPages->ShowSearchResHead($Page->multi['TXT_FRONT_MOD_SEARCH_IN_CATALOG']);
                    $Page->Catalog->ShowSearchResult($Page->Catalog->QuickSearch($query,1));
                    break;
    }
    ?>
    <p>
      <a href="javascript:history.back()"><u>← <?=$Page->multi['TXT_FRONT_GO_BACK'];?></u></a>
    </p>
    <?
    $Search->result = '';
    $Search->save_search();
}
else {
    ?>
    <div align="center">
    <br/>
    <?=$Page->multi['MSG_FRONT_MOD_SEARCH_SHORT_QUERY'];?>
    <br/>
    <form action="<?=_LINK;?>search/result/" method="get">
     <table border="0" cellpadding="0" cellspacing="2">
      <tr>
       <td><?=$Page->multi['TXT_FRONT_MOD_SEARCH_PHRASE_SEARCH'];?>:</td>
       <td><input type="text" name="query" size="30" class="formstyle" value="<?=stripslashes($query);?>"></td>
      </tr>
      <tr>
       <td><?=$Page->multi['TXT_FRONT_MOD_SEARCH_IN_TOPIC'];?>:</td>
       <td align="left">
        <select name="modname" class="select2" style="width:200px;">
         <option value="all"><?=$Page->multi['TXT_FRONT_MOD_SEARCH_IN_ALL_TOPICS'];?></option>
         <option value="pages"><?=$Page->multi['TXT_FRONT_MOD_SEARCH_BY_DYNAMIC_PAGES'];?></option>
         <option value="news"><?=$Page->multi['TXT_FRONT_MOD_SEARCH_BY_NEWS'];?></option>
         <option value="articles"><?=$Page->multi['TXT_FRONT_MOD_SEARCH_BY_ARTICLES'];?></option>
         <?/*<option value="catalog"><?=$Page->multi['TXT_FRONT_MOD_SEARCH_IN_CATALOG'];?></option>*/?>
        </select>
       </td>
      </tr>
      <tr>
       <td></td>
       <td align="right"><input class="buttontxt" type="submit" value="<?=$Page->multi['TXT_FRONT_SEARCH'];?>"/></td>
      </tr>
     </table>
     <br/>
    </form>
    </div>
    <?
}
$Page->Form->WriteContentFooter();
?>
</div>
<?

$Page->WriteFooter();
?>