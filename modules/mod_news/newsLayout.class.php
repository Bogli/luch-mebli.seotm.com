<?php
// ================================================================================================
// System : CMS
// Module : newsLayout.class.php
// Date : 01.03.2011
// Licensed To:  Yaroslav Gyryn
// Purpose : Class definition for all actions with Layout of News on the Front-End
// ================================================================================================
include_once( SITE_PATH.'/modules/mod_news/news.defines.php' );
/**
* Class User
* Class definition for all Pages - user actions
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 22.02.2011
* @property Right $Right
* @property FrontendPages $FrontendPages
* @property UserAuthorize $Logon
* @property FrontForm $Form
* @property db $db
* @property TblFrontMulti $multi
*/
class NewsLayout extends News{

   var $id = NULL;
   var $is_tags = NULL;
   var $module = NULL;
   var $is_comments = NULL;
   var $fltr = NULL;
   var $db = NULL;
   var $Form = NULL;
   var $Right = NULL;

    // ================================================================================================
    //    Function          : NewsLayout (Constructor)
    //    Date              : 01.03.2011
    //    Parms             : sort      / field by whith data will be sorted
    //                        display   / count of records for show
    //                        start     / first records for show
    //    Returns           : Error Indicator
    //    Description       : Opens and selects a dabase
    // ================================================================================================
    function __construct($display=NULL, $sort=NULL, $start=NULL, $module=NULL) {
        //Check if Constants are overrulled
        ( $display  !="" ? $this->display = $display  : $this->display = 10   );
        ( $sort     !="" ? $this->sort    = $sort     : $this->sort    = NULL );
        ( $start    !="" ? $this->start   = $start    : $this->start   = 0    );
        ( $module   !="" ? $this->module  = $module   : $this->module  = 24 );

        if(defined("_LANG_ID")) $this->lang_id = _LANG_ID;

        $this->db =  DBs::getInstance();
        $this->Right =  &check_init('Rights', 'Rights');
        $this->Form = &check_init('FormNews', 'FrontForm', "'form_news'");
        if(empty($this->multi)) $this->multi = &check_init_txt('TblFrontMulti', TblFrontMulti);
        if(empty($this->Spr)) $this->Spr = &check_init('SysSpr', 'SysSpr');
        if (empty($this->Crypt)) $this->Crypt = &check_init('Crypt', 'Crypt');
        if(empty($this->settings)) $this->settings = $this->GetSettings();

        //$this->CheckStatus();
        ( defined("USE_TAGS")       ? $this->is_tags = USE_TAGS         : $this->is_tags=0      );
        ( defined("USE_COMMENTS")   ? $this->is_comments = USE_COMMENTS : $this->is_comments=0  );
    } // End of NewsLayout Constructor


     // ================================================================================================
    // Function : ShowNewsLinks()
    // Date : 01.03.2011
    // Returns :      true,false / Void
    // Description :  Show News Links
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
     function ShowNewsLinks()
    {
        if(empty($this->settings))
            $this->settings = $this->GetSettings();

        $q = "select * from `".TblModNews."`, `".TblModNewsSprSbj."`
              where `".TblModNews."`.id = `".TblModNewsSprSbj."`.cod
              and `".TblModNewsSprSbj."`.lang_id='".$this->lang_id."'
              and  `".TblModNewsSprSbj."`.name != ''
              and status='a'";
        $res = $this->db->db_Query( $q );
        $rows = $this->db->db_GetNumRows();
        //echo 'q='.$q.' res='.$res;
        ?>
        <div align="center">
        <div class="newsLinks">
         <div class="newsLinksBlock" align="left">
             <div class="m_title">
                <?=$this->multi['TXT_NOVIGATE'];?>:
              </div>
              <?
              if($rows>0){
                  ?><br/><a href="<?=_LINK;?>news/last/" class="t_link"><?=$this->multi['MOD_NEWS_LATEST'];?>&nbsp;→</a>&nbsp;<span class="inacive_txt"><?=$rows;?></span><?
              }
              else {
                  ?><br/><span class="inacive_txt"><?=$this->multi['MOD_NEWS_LATEST'];?></span><?
              }

              $q = "select * from `".TblModNews."`, `".TblModNewsSprSbj."`
                    where `".TblModNews."`.id = `".TblModNewsSprSbj."`.cod
                    and `".TblModNewsSprSbj."`.lang_id='".$this->lang_id."'
                    and  `".TblModNewsSprSbj."`.name != ''
                    and status='e'";
              $res = mysql_query( $q );
              $rows = mysql_num_rows( $res );

              if($rows>0){?><br/><a href="<?=_LINK;?>news/arch/" class="t_link"><?=$this->multi['MOD_NEWS_ARCH'];?>&nbsp;→</a>&nbsp;<span class="inacive_txt"><?=$rows;?></span><?}
              else{?><br/><span class="inacive_txt"><?=$this->multi['MOD_NEWS_ARCH'];?></span><?}

              $q = "select * from `".TblModNews."`, `".TblModNewsSprSbj."`
                    where `".TblModNews."`.id = `".TblModNewsSprSbj."`.cod
                    and `".TblModNewsSprSbj."`.lang_id='".$this->lang_id."'
                    and  `".TblModNewsSprSbj."`.name != ''
                    and status!='i'";
              $res = mysql_query( $q );
              $rows = mysql_num_rows( $res );

              if($rows>0){?><br/><a href="<?=_LINK;?>news/all/" class="t_link"><?=$this->multi['MOD_NEWS_ALL'];?>&nbsp;→</a>&nbsp;<span class="inacive_txt"><?=$rows;?></span><?}
              else{?><br/><span class="inacive_txt"><?=$this->multi['MOD_NEWS_ALL'];?></span><?}
              ?>
          </div>
          <?
          $this->NewsCategory();

          if ( isset($this->settings['subscr']) AND $this->settings['subscr']=='1' ) {
            ?>
            <div class="newsLinksBlock" align="left">
             <div class="m_title">
                <a href="<?=_LINK;?>news/subscribe/"><?=$this->multi['TXT_SUBSCRIBE'];?></a>
              </div>
             </div>
            <?
          }
        ?>
        </div>
    </div>
    <?
    } // end of function ShowNewsLinks


    // ================================================================================================
    // Function : NewsCategory()
    // Date : 01.03.2011
    // Returns :      true,false / Void
    // Description :  Show News Category ...
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function NewsCategory()
    {
        $this->db = new DB();

        $q = "SELECT `".TblModNewsCat."`.*
              FROM `".TblModNewsCat."`
              WHERE `lang_id`='"._LANG_ID."'
              ORDER BY `move` ASC ";
        $res = $this->db->db_Query( $q );
        $rows = $this->db->db_GetNumRows();

        if ($rows>0){
            //if( isset($settings['subscr']) AND $settings['subscr']=='1' ) {$style_add='float:left;';}
            ?>
            <div class="newsLinksBlock" align="left">
             <div class="m_title">
                <?=$this->multi["_FLD_NEWS_CATEGORY"];?>:
              </div>
            <?
            for( $i = 0; $i < $rows; $i++ ){
                $row = $this->db->db_FetchAssoc();
                $name = $row['name'];
                $q1 = "select * from ".TblModNews." where id_category='".$row['cod']."' and status!='i' ";
                $res1 = $this->db->db_Query( $q1 );
                $rows1 = $this->db->db_GetNumRows();

                if( $rows1 ) {
                    $link =  $this->Link($row['cod'], NULL);
                    ?><br/><a class="t_link" href="<?=$link;?>"><?=$name;?>&nbsp;→</a>&nbsp;<span class="inacive_txt"><?=$rows1;?></span><?
                } // end if
            } // end for
            ?>
            </div>
            <?
        }
    } //end of function NewsCategory

    // ================================================================================================
    // Function : GetNewsForTags()
    // Date : 23.05.2011
    // Returns :      true,false / Void
    // Description :  Get News For Tags
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function GetNewsForTags( $idNews=null, $idModule = 83)
    {
        if($idNews== null)
            return;
        $q = "
        SELECT
             `".TblModNews."`.id,
             `".TblModNews."`.start_date,
             `".TblModNews."`.id_category,
             `".TblModNewsSprSbj."`.name,
             `".TblModNewsSprShrt."`.name as shrt,
             `".TblModNewsCat."`.name as category,
             `".TblModNews."`.display,
             `".TblModNewsLinks."`.link
        FROM `".TblModNews."`, `".TblModNewsSprSbj."`, `".TblModNewsCat."`, `".TblModNewsSprShrt."` , `".TblModNewsLinks."`
        WHERE
                     `".TblModNews."`.id = `".TblModNewsSprSbj."`.cod
              AND `".TblModNews."`.id_category = `".TblModNewsCat."`.cod
              AND `".TblModNews."`.id = `".TblModNewsSprShrt."`.cod
              AND `".TblModNews."`.id = `".TblModNewsLinks."`.cod
              AND `".TblModNewsSprSbj."`.lang_id='".$this->lang_id."'
              AND `".TblModNewsSprShrt."`.lang_id='".$this->lang_id."'
              AND `".TblModNewsCat."`.lang_id='".$this->lang_id."'
              AND `".TblModNewsSprSbj."`.name!=''
              AND  `".TblModNews."`.id  IN (".$idNews.")
              ";
         if( $this->fltr!='' ) $q = $q.$this->fltr;
         //$q = $q." order by `".TblModNews."`.id DESC ";
         //$q = $q." order by `".TblModNews."`.id DESC limit ".$this->start.",".$this->display."";
         $res = $this->db->db_Query( $q );
         $rows = $this->db->db_GetNumRows();
         //echo "<br><br>".$q."<br/> res=".$res." rows=".$rows;
         if($rows==0)
             return;

         $array = array();
         for( $i = 0; $i <$rows; $i++ ){
             $row = $this->db->db_FetchAssoc();
             $array[$row['id']] = $row;
             $array[$row['id']]['module'] = $idModule;
             //$array[$row['id']] = $i;
         }
         return $array;

    }

    /**
     * NewsLayout::ShowNewsByPages()
     * Show all news by Pages
     * @author Yaroslav
     * @return
     */
    function ShowNewsByPages()
    {
         $array = $this->GetNRows(true);
         $rows = count($array);
         if($rows==0){
             echo $this->multi['MSG_NO_NEWS'];
             return;
         }
         $oldCat = '';
         $oldCatId = '';
         for( $i = 0; $i <$rows; $i++ ){
             $value = $array[$i];
             $name = stripslashes($value['name']);
             $date = $this->ConvertDate($value['start_date'],false,true);
             $short = stripslashes($value['shrt']);
             //$short = strip_tags(stripslashes($value['shrt']), '<a>');
             //$short  = $this->Crypt->TruncateStr($short, 450);
             if($value['id_category']== $oldCatId )
                $linkCat = $oldCat;
             else {
                $linkCat = $this->Link( $value['id_category']);
                $oldCat = $linkCat;
             }
             $oldCatId = $value['id_category'];
             //$link = $this->Link( $value['id_category'], $value['id']);
             $link = _LINK.'news/'.$value['link'].'.html';
              ?>
            <div class="newsItem">
                <?$img_path = $this->GetMainImage($value['id'], 'front');
                if(!empty($img_path)) {
                    ?><div class="newsItemImg">
                    <?= $this->ShowImage( $img_path, $value['id'], 'size_width=90', 85, NULL, "align='left' alt='".$name."' title='".$name."' style='margin: 0;'");
                    ?></div><?
                  }?>
                <div class="newsItemContent">
                    <div class="newsItemDate"><?=$date;?></div>
                    <div class="newsItemName">
                        <a href="<?=$link;?>" title="<?=$name;?>"><?=$name;?></a>
                    </div>
                    <div class="newsItemShort"><?=$short;?></div>
                </div>
            </div><?
         }
         if($rows>0){
         ?><div class="pageNaviClass"><?
             $array_all = $this->GetNRows();
             $n_rows = count($array_all);
             $link = $this->Link( $this->category, NULL );
             $this->Form->WriteLinkPagesStatic( $link, $n_rows, $this->display, $this->start, $this->sort, $this->page );
         ?></div>
         <?
         }
    } // end of functtion ShowNewsByPages


    // ================================================================================================
    // Function : GetNRows()
    // Date : 01.03.2011
    // Returns :      true,false / Void
    // Description :  Get count all news for PagesFront
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function GetNRows($limit=false)
    {
        $q = "SELECT
            `".TblModNews."`.id,
            `".TblModNews."`.start_date,
            `".TblModNews."`.id_category,
            `".TblModNewsSprSbj."`.lang_id,
            `".TblModNewsSprSbj."`.name,
            `".TblModNewsSprShrt."`.name as shrt,
            `".TblModNewsLinks."`.link
        FROM
            `".TblModNews."`, `".TblModNewsSprSbj."`, `".TblModNewsSprShrt."`, `".TblModNewsLinks."`
        WHERE
            `".TblModNews."`.id = `".TblModNewsSprSbj."`.cod and
            `".TblModNews."`.id = `".TblModNewsSprShrt."`.cod and
            `".TblModNews."`.id = `".TblModNewsLinks."`.cod and
            `".TblModNewsSprShrt."`.lang_id='".$this->lang_id."' and
            `".TblModNewsSprSbj."`.`lang_id`='".$this->lang_id."' and
            `".TblModNewsSprSbj."`.`name`!='' and
            `".TblModNews."`.top = '0' and
            `".TblModNews."`.property = '0'
         ";
         if( $this->fltr!='' ) $q = $q.$this->fltr;
         $q = $q." order by `".TblModNews."`.display DESC ";
         if($limit) $q .= " limit ".$this->start.",".$this->display."";
         $res = $this->db->db_Query( $q );
         //echo "<br>q=".$q." res=".$res;
         if( !$res OR !$this->db->result) return false;
         $rows = $this->db->db_GetNumRows();
         //echo "<br>rows=".$rows;
         $array = array();
         for( $i = 0; $i <$rows; $i++ ){
            $array[] = $this->db->db_FetchAssoc();
         }
         return $array;
    } // end of  GetNRows


    // ================================================================================================
    // Function : GetTodayNews()
    // Date : 01.03.2011
    // Returns :      true,false / Void
    // Description :  Write count of today news
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function GetTodayNews()
    {
        $q = "select * from ".TblModNews." where 1 and `start_date`>'".date("Y-m-d")." 0:00:00' and `start_date`<'".date("Y-m-d")." 23:59:59' ";
        $q = $q." order by id DESC";
        $res = $this->db->db_Query( $q );
        //echo "<br> q = ".$q." res = ".$res;
        $rows = $this->db->db_GetNumRows();
        return $this->Msg->show_text('TXT_NEWS_TODAY').": ".$rows;
    } // end of  GetTodayNews


    // ================================================================================================
    // Function : NewsShowFull()
    // Date : 01.04.2011
    // Returns :      true,false / Void
    // Description :  Show Full-text of news
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function NewsShowFull()
    {
        $value = $this->GetNewsData($this->id);
        if( !isset($value['id']) ) return false;
        ?>
        <div class="subBody NewsFull">
         <div class="NewsFulldate"><?=$this->ConvertDate($value['start_date'],false,true);?></div>
         <?/* <a href="<?=$this->Link( $value['id_category']);?>"><?=stripslashes($value['category']);?></a><?*/
         if ( isset($this->settings['img']) AND $this->settings['img']=='1' ){
             $img_arr = $this->GetImagesToShow($value['id']);
             if (is_array($img_arr ) and !empty($img_arr[0]['id'])) {
                ?><div class="newsFullImage"><?
                    $img_path  = $img_arr[0]['path'];
                    $path = "http://".NAME_SERVER.$this->settings['img_path']."/".$this->id."/".$img_path;
                    $imgName = '';
                    if(!empty($img_arr[0]['name']))
                        $imgName = $img_arr[0]['name'];
                    $title = $img_arr[0]['descr'];
                    ?><a href="<?=$path;?>" class="highslide" title="<?=$imgName;?>" onclick="return hs.expand(this);"><?=$this->ShowImage( $img_path , $value['id'], 'size_width=230', 85, NULL, " alt='".$imgName."' title='".$title."' ");?></a>
                    <div class="name">
                        <table border="0" width="100%">
                            <tr>
                                <td align="left"><?=$imgName?></td>
                                <td align="right"><a  href="<?=$path;?>" onclick="return hs.expand(this);"><div class="lupa">&nbsp;</div></a></td>
                            </tr>
                        </table>
                    </div >
                    <?
                    $n = count($img_arr);
                    ?><div class="newsFullImgsmall"><?
                    for($i=1; $i<$n; $i++) {
                        $alt =  $img_arr[$i]['name'];
                        $title = $img_arr[$i]['descr'];
                        $imgName = '';
                        if(!empty($img_arr[0]['name']))
                            $imgName = $img_arr[0]['name'];
                        $title = $img_arr[0]['descr'];
                        ?><div class="<?if($i%2==0){?>left<?}else{?>right<?}?>"><?
                         if( !empty($img_arr[$i]['path']) ) {
                             $path = "http://".NAME_SERVER.$this->settings['img_path']."/".$this->id."/".$img_arr[$i]['path'];
                             //echo '$img_arr[$i][path]'.$img_arr[$i]['path'];
                             ?><a  href="<?=$path;?>" title="<?=$imgName;?> class="highslide" onclick="return hs.expand(this);"><?=$this->ShowImageSquare($img_arr[$i]['path'], $value['id'], true, 110, 85, "  alt='".$alt."' title='".$title."'  ");?></a>
                             <span></span><?
                         }
                         ?></div><?
                    }//end for
                    ?></div><?
                 ?></div><?
             } // end if img
         }
         if ( isset($this->settings['full_descr']) AND $this->settings['full_descr']=='1' ) {
             $full_news = stripslashes($value['full_news']);
             ?><div><?=$full_news;?></div><?
         }
         ?><div><a href="<?=_LINK?>news/">←<?=$this->multi['TXT_NEWS_GO_BACK'];?></a></div><?
         if( $this->is_tags==1 ){
           if (empty($this->Tags))
                $this->Tags = Singleton::getInstance('FrontTags');

           $this->Tags->ShowUsingTags($this->module, $this->id);
        }
         ?></div><?
         if($this->is_comments==1){
             if(!isset($this->Comments)) $this->Comments = new FrontComments($this->module, $this->id);
             $this->Comments->ShowCommentsCountLink();
             $this->Comments->ShowCommentsByModuleAndItem();
         }

         /*?>
         <div align="left"><a href="javascript:window.history.go(-1);">← <?=$this->Msg->show_text('MOD_NEWS_BACK');?></a></div>
        <?*/
    } // end if function NewsShowFull()


    // ================================================================================================
    // Function : ShowSearchForm()
    // Date : 01.03.2011
    // Parms :   $id - poll id
    // Returns : true/false
    // Description : Show Saerch form for search in the news
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function ShowSearchForm()
    {
        ?>
        <form  method="post" action="<?=_LINK?>news/?task=search_result">
        <table cellpadding="0" cellspacing="0" style="padding-bottom:10px; padding-top:10px;" align="center">
            <tr>
                <td colspan="2" class="menu" align="center"><h3>Быстрый поиск:</h3></td>
            </tr>
            <tr>
                <td class="menu" style="padding-left:8px; padding-right:7px;"><input type="text" maxlength="50" class="search_input" name="s_keywords" /></td>
                <td class="menu"><input type="submit" name="submit" value="Поиск"/></td>
            </tr>
        </table>
        </form>
        <?
    }// end of function ShowSearchForm


  // ================================================================================================
  // Function : ShowSearchResult
  // Date : 01.03.2011
  // Parms :   $rows
  // Returns : true/false
  // Description : Show Saerch form for search in the news
  // Programmer : Yaroslav Gyryn
  // ================================================================================================
  function ShowSearchResult($rows)
  {
    if($rows>0){
        $arr = array();
        for($i=0; $i<$rows; $i++ )
        {
            $arr[] = $this->db->db_FetchAssoc();
        }
        ?><ul><?
        for($i=0; $i<$rows; $i++ ) {
            $row= $arr[$i];
            //echo "<br> row = ".print_r($row);
            ?><li><a href="<?=$this->Link($row['id_category'], $row['id']);?>"><?=stripslashes(  $row['news_name']);?></a></li><?
        }
        ?></ul><?
    }
    else{
        echo $this->Msg->show_text('SEARCH_NO_RES', TblModPagesSprTxt);
    }
  }// end of function ShowSearchResult


// ================================================================================================
// Function : NewsLast()
// Date : 01.03.2011
// Parms :   $limit - limit of show
// Description : Show last news
// Programmer : Yaroslav Gyryn
// ================================================================================================
function NewsLast($limit = 10)
{
    $q = "SELECT
            `".TblModNews."`.id,
            `".TblModNews."`.start_date,
            `".TblModNews."`.end_date,
            `".TblModNews."`.id_category,
            `".TblModNewsSprSbj."`.lang_id,
            `".TblModNewsSprSbj."`.name,
            `".TblModNewsSprShrt."`.name as shrt,
            `".TblModNewsLinks."`.link
        FROM
            `".TblModNews."`, `".TblModNewsSprSbj."`, `".TblModNewsSprShrt."`, `".TblModNewsLinks."`
        WHERE
            `".TblModNews."`.id = `".TblModNewsSprSbj."`.cod and
            `".TblModNews."`.id = `".TblModNewsSprShrt."`.cod and
            `".TblModNews."`.id = `".TblModNewsLinks."`.cod and
            `".TblModNewsSprShrt."`.lang_id='".$this->lang_id."' and
            `".TblModNews."`.status='a' and
            `".TblModNewsSprSbj."`.`lang_id`='".$this->lang_id."' and
            `".TblModNewsSprSbj."`.`name`!=''
        ORDER BY `display` desc LIMIT ".$limit;

    $res = $this->db->db_Query($q);
    //echo "<br> q = ".$q." res = ".$res;
    if(!$res) return false;
    $rows = $this->db->db_GetNumRows($res);
    $arr = array();
    for( $i=0; $i<$rows; $i++ )
    {
      $arr[] = $this->db->db_FetchAssoc($res);
    }
    ?><div class="LastNews"><?
    for( $i=0; $i<$rows; $i++ )
    {
      $row = $arr[$i];
      $name = strip_tags(stripslashes($row['name']));
      $link = '/news/'.$row['link'].'.html';
      $shrt = stripslashes($row['shrt']);
      $img_path = $this->GetMainImage($row['id'], 'front');
      $start_date = $this->ConvertDate($row['start_date']);
      $end_date = $this->ConvertDate($row['end_date']);?>
        <div class="LastNewsOne"<?if($i==0){?> style="margin: 0;"<?}?>>
            <?if(!empty($img_path)) {?>
            <div class="LastNewsOneImg">
                <div class="LastNewsOneImgTable">
                <?=$this->ShowImage( $img_path, $row['id'], 'size_width=72', 85, NULL, " alt='".$name."' title='".$name."' style='margin: 0;'");?>
                </div>
            </div>
            <?}?>
            <div class="LastNewsOneContent">
                <div class="LastNewsDate"><?=$start_date?> - <?=$end_date?></div>
                <div class="LastNewsName"><a href="<?=$link;?>"><?=$name;?></a></div>
                <div class="LastNewsShort"><?=$shrt?></div>
            </div>
        </div>
        <?if($i<$rows-1){
            ?><div class="BlockPolosa"></div><?
        }
    }
    ?></div><?
}   //end of function NewsLast



// ================================================================================================
// Function : NewsLastColumn()
// Date : 01.03.2011
// Parms :   $limit - limit of show
// Description : Show last news in right Column
// Programmer : Yaroslav Gyryn
// ================================================================================================
function NewsLastColumn($limit = 10, $str = null)
{
    $q = "SELECT
            `".TblModNews."`.id,
            `".TblModNews."`.start_date,
            `".TblModNews."`.id_category,
            `".TblModNewsSprSbj."`.lang_id,
            `".TblModNewsSprSbj."`.name,
            `".TblModNewsSprShrt."`.name as shrt
        FROM
            `".TblModNews."`, `".TblModNewsSprSbj."`, `".TblModNewsSprShrt."`
        WHERE
            `".TblModNews."`.id = `".TblModNewsSprSbj."`.cod and
            `".TblModNews."`.id = `".TblModNewsSprShrt."`.cod and
            `".TblModNewsSprShrt."`.lang_id='".$this->lang_id."' and
            `".TblModNews."`.status='a' and
            `".TblModNewsSprSbj."`.`lang_id`='".$this->lang_id."' and
            `".TblModNewsSprSbj."`.`name`!=''";
        if(!empty($str))
            $q .= " and `".TblModNews."`.id  IN (".$str.") ";
        else
            $q .=" ORDER BY `display` desc LIMIT ".$limit;

    $res = $this->db->db_Query($q);
    //echo "<br> q = ".$q." res = ".$res;
    $rows = $this->db->db_GetNumRows($res);

    $arr = array();
    $arr2 = array();
    for( $i=0; $i<$rows; $i++ )
    {
      $row = $this->db->db_FetchAssoc($res);
      $arr[$row['id']] = $row;
    }

    $keys = array_keys($arr);
    $title = 'Останні новини';
    if(!empty($str)) {
        $title = 'Популярне з категорії';
        $title = 'Популярне з категорії';
        $sortedKeys =   explode (",", $str );
       for( $i=0; $i<$rows; $i++ ) {
            $arr2[$sortedKeys[$i]] = $arr[$sortedKeys[$i]];
       }
    }

    ?><div class="column">
        <div class="columnTitle"><?=$title;?></div>
    <?
    for( $i=0; $i<$rows; $i++ )
    {
      if(!empty($str))
        $row = $arr2[$sortedKeys[$i]];
      else
        $row = $arr[$keys[$i]];

      $link = $this->Link($row['id_category'], $row['id']);
      $short = strip_tags(stripslashes($row['shrt']));
        ?>
        <div class="columnLast">
            <div class="dateLast"><?=$this->ConvertDate($row['start_date'],true);?></div>
            <div class="textLast"><a href="<?=$link;?>" title="<?=$short?>"><?=strip_tags(stripslashes($row['name']));?></a></div>
        </div>
        <?
    }

    if($str==null) {?>
        <a class="btnMore" href="/news/"></a>
    <?}?>
    </div>
    <?
}   //end of function NewsLastColumn()

// ================================================================================================
// Function : NewsPropertyLast()
// Date : 01.03.2011
// Parms :   $limit - limit of show
// Description : Show last news
// Programmer : Yaroslav Gyryn
// ================================================================================================
function NewsPropertyLast($limit = 10, $property = 1)
{
    $q = "SELECT
            `".TblModNews."`.id,
            `".TblModNews."`.start_date,
            `".TblModNews."`.id_category,
            `".TblModNewsSprSbj."`.lang_id,
            `".TblModNewsSprSbj."`.name,
            `".TblModNewsSprShrt."`.name as shrt,
            `".TblModNewsLinks."`.link
        FROM
            `".TblModNews."`, `".TblModNewsSprSbj."`, `".TblModNewsSprShrt."`, `".TblModNewsLinks."`
        WHERE
            `".TblModNews."`.id = `".TblModNewsSprSbj."`.cod and
            `".TblModNews."`.id = `".TblModNewsSprShrt."`.cod and
            `".TblModNews."`.id = `".TblModNewsLinks."`.cod and
            `".TblModNewsSprShrt."`.lang_id='".$this->lang_id."' and
            `".TblModNews."`.status='a' and
            `".TblModNewsSprSbj."`.`lang_id`='".$this->lang_id."' and
            `".TblModNewsSprSbj."`.`name`!='' and
            `".TblModNews."`.top = '0' and
            `".TblModNews."`.property = ".$property."
        ORDER BY `display` desc LIMIT ".$limit;

    $res = $this->db->db_Query($q);
    //echo "<br> q = ".$q." res = ".$res;
    $rows = $this->db->db_GetNumRows($res);
    if($rows == 0)
        return;
    $arr = array();
    for( $i=0; $i<$rows; $i++ )
    {
      $arr[] = $this->db->db_FetchAssoc($res);
    }
    ?><div class="news_colum1_2">
        <div class="news_colum1_2_title">Новини України</div>
    <?
    for( $i=0; $i<$rows; $i++ )
    {
      $row = $arr[$i];
      //$link = $this->Link($row['id_category'], $row['id']);
      $link = $this->Link($row['id_category'], $row['id'], $row['link']);
        ?>
        <div>
            <div class="news_colum1_2_text_time"><?=$this->ConvertDate($row['start_date'],true);?></div>
            <div class="news_colum1_2_text"><a href="<?=$link;?>"><?=strip_tags(stripslashes($row['name']));?></a></div>
        </div>
        <?
    }
    ?>
    <div class="clear"></div>
    <a class="btnMoreNews" href="/news/">Всі новини </a>
    </div><?
}   //end of function NewsPropertyLast


// ================================================================================================
// Function : NewsLine()
// Date : 01.03.2011
// Parms :   $limit - limit of show
// Description : Show news Line
// Programmer : Yaroslav Gyryn
// ================================================================================================
function NewsLine($limit = 5)
{
    $q = "SELECT
            `".TblModNews."`.id,
            `".TblModNews."`.start_date as start_date,
            `".TblModNews."`.id_category as id_category,
            `".TblModNewsSprSbj."`.lang_id as lang_id,
            `".TblModNewsSprSbj."`.name as name,
            `".TblModNewsSprShrt."`.name as shrt,
            `".TblModNewsLinks."`.link
        FROM
            `".TblModNews."`, `".TblModNewsSprSbj."`, `".TblModNewsSprShrt."`, `".TblModNewsLinks."`
        WHERE
            `".TblModNews."`.id = `".TblModNewsSprSbj."`.cod and
            `".TblModNews."`.id = `".TblModNewsSprShrt."`.cod and
            `".TblModNews."`.id = `".TblModNewsLinks."`.cod and
            `".TblModNewsSprShrt."`.lang_id='".$this->lang_id."' and
            `".TblModNews."`.status='a' and
            `".TblModNews."`.line='1' and
            `".TblModNewsSprSbj."`.`lang_id`='".$this->lang_id."' and
            `".TblModNewsSprSbj."`.`name`!=''
        ORDER BY
            `display` desc LIMIT ".$limit;

    $res = $this->db->db_Query($q);
    //echo "<br>".$q." <br/>res = ".$res;
    $rows = $this->db->db_GetNumRows($res);
    $arr = array();
    for( $i=0; $i<$rows; $i++ )
    {
      $arr[] = $this->db->db_FetchAssoc($res);
    }
    $str ='';
    for( $i=0; $i<$rows; $i++ )
    {
      $row = $arr[$i];
      $link = $this->Link($row['id_category'], $row['id'], $row['link']);
      $name = strip_tags(stripslashes($row['name']));
      $short =strip_tags(stripslashes($row['shrt']));
      $str[$i] = '<a href="'.$link.'" >'.$name.'</a>';
      /*?><a title="<?=$name ;?>" href="<?=$link;?>"><?=$name;?></a><?*/
    }
    if($rows>5)
        $rows = 5;
    else
        $rows = $i;
    switch($rows) {
        case 1:
            echo "<script>setText('".$rows."', '". $str[0]."');</script>";
            break;
        case 2:
            echo "<script>setText('".$rows."', '". $str[0]."','". $str[1]."');</script>";
            break;
        case 3:
            echo "<script>setText('".$rows."', '". $str[0]."','". $str[1]."','". $str[2]."');</script>";
            break;
        case 4:
            echo "<script>setText('".$rows."', '". $str[0]."','". $str[1]."','". $str[2]."','". $str[3]."');</script>";
            break;
        case 5:
            echo "<script>setText('".$rows."', '". $str[0]."','". $str[1]."','". $str[2]."','". $str[3]."','". $str[4]."');</script>";
            break;
    }
    if(!empty($str))
        return $str[$rows-1];
    else
        return $str;
}   //end of function NewsLine()


// ================================================================================================
// Function : NewsTop()
// Date : 01.03.2011
// Description : Show top news
// Programmer : Yaroslav Gyryn
// ================================================================================================
function NewsTop()
{
    if(empty($this->Article)) $this->Article = Singleton::getInstance('ArticleLayout');

    // Масив ТОП-новин
    $arrTopNews = $this->GetTopNews();
    $rowsNews = count($arrTopNews);

    // Масив ТОП-статей
    $arrTopArticles = $this->Article->GetTopArticles();
    $rowsArticles = count($arrTopArticles);

    // Об'єднаний масив ТОП-новин і ТОП-статей
    $arrMergedNews = array();
    $arrMergedNews = array_merge($arrTopNews, $arrTopArticles);
    $rowsArrMergedNews = count($arrMergedNews);
    $keys = array_keys($arrMergedNews);

    // Формування і сортування масиву по даті зі збереженням індекса
    $topNews = null;
    for($i=0; $i<$rowsArrMergedNews; $i++) {
         if($arrMergedNews[$i]['top_main']==1)
            $topNews =  $keys[$i];
         $this->date[$i]['start_date'] = $arrMergedNews[$i]['start_date'];
         $this->date[$i]['index'] = $keys[$i];
    }
    rsort($this->date);

    //Масив із 5 елементів для вивода на сайті
    $arrNews = array();
    if($topNews!=null)
        $arrNews[0] = $arrMergedNews[$topNews]; // Головна Топ новина або Топ стаття

    for($i=0, $n=0; $i<$rowsArrMergedNews; $i++) {
        if($topNews != $arrMergedNews[$this->date[$i]['index']])  {
            $arrNews[] = $arrMergedNews[$this->date[$i]['index']]; // Інші новини
            $n++;
        }
        if($topNews!=null) {
            if($n==4)
                break;
        }
        else {
            if($n==5)
                break;
        }

    }
    $rows = count($arrNews);
    //print_r($arrNews);
    if($rows==0)
        return;
    $str ='';
    $topId ='';
    $topSubject='';
    $toplink='';
    $topShort='';
    $topImage='';
    $type=array();
    $short = array(null,null,null,null,null);
    $link = array(null,null,null,null,null);
    $pathImage = array(null,null,null,null,null);
    //Вивод масиву на сайті
    for( $i=0; $i<$rows; $i++ )
    {
      $row = $arrNews[$i];
      $id[$i] =$row['id'];
      $link[$i] = $row['link'];

      $name[$i] = strip_tags(stripslashes($row['name']));
      $name[$i] = str_replace("'", "`", $name[$i]);

      $short[$i] = strip_tags($row['short']);
      $short[$i] = str_replace("\n", "", $short[$i]);
      //$short[$i] = str_replace("'", "\'", $short[$i]);
      $short[$i] = str_replace("'", "`", $short[$i]);
      $short[$i] = str_replace("", "\'", $short[$i]);
      $short[$i] = str_replace("\r", "", $short[$i]);

      $image[$i]= stripslashes($row['image']);
      $type[$i] = $row['type'];
      if(empty($image[$i]))
          $pathImage[$i] = '/images/design/noTopImage.jpg';
      else {
          if($type[$i]=='news')
            $pathDir = NewsImg_Path;
          if($type[$i]=='articles')
            $pathDir = ArticleImg_Path;
          $pathImage[$i] =  $pathDir.$id[$i].'/'.$image[$i];
      }


      if($row['top_main']==1) {  // Якщо основна топ новина
          $topId = $id[$i];
          $topType = $type[$i];  // news or article
          $topSubject = $name[$i];
          $toplink = $link[$i];
          $topShort = $short[$i];
          $topImage = $image[$i];
      }
      $str[$i] = '<a href="'.$link[$i].'" >'.$name[$i].'</a>';
      /*?><a title="<?=$name ;?>" href="<?=$link;?>"><?=$name;?></a><?*/
    }
    if(empty($topId)) {  // Якщо не виявилось жодної основної топ новини чи топ статті
          $topId = $id[0];
          $topType = $type[0];  // news or article
          $topSubject = $name[0];
          $toplink = $link[0];
          $topShort = $short[0];
          $topImage = $image[0];
    }
    ?>
    <div id="news_galery">
        <div id="galery_grey"></div>
        <div id="galery_left_bg">
            <div id="gal_news_title"><a href="<?=$toplink;?>"><?=$topSubject;?></a></div>
            <div id="gal_news_text"><?=$topShort;?></div>
            <div class="but_full_ver"><a href="<?=$toplink;?>">Повна версія</a></div>
        </div>
        <div id="galery_img">
        <?
        if(!empty($topId)) {
            if($topType=='news') { echo $this->ShowImage( $topImage, $topId, 'size_width=435', 85, NULL, "border=0 alt='' title='' "); }
            if($topType=='articles') { echo $this->Article->ShowImage( $topImage, $topId, 'size_width=435', 85, NULL, "border=0 alt='' title='' ");}
        }
        ?>
        </div>
        <div id="gal_news_short_block">
        <? for($i=0; $i<$rows; $i++) {
            $class= '';
            if($i==0)
                $class= '_active';
            ?><div id="num<?=$i?>" class="galery_news_short<?=$class?>">
                    <span class="galery_short_img" onclick="location.href='<?=$link[$i];?>'">
                    <?
                    if(!empty($image[$i])) {
                        if($type[$i]=='news') {echo $this->ShowImage( $image[$i], $id[$i], 'size_width=121', 85, NULL, "border=0 alt='".$name[$i]."' title='".$name[$i]."'  ");}
                        if($type[$i]=='articles') {echo $this->Article->ShowImage( $image[$i], $id[$i], 'size_width=121', 85, NULL, "border=0 alt='".$name[$i]."' title='".$name[$i]."'  ");}
                    }
                    else {
                        ?><img  src="/images/design/no-image.jpg" width="121" height="82" alt="" /><?
                    }?>
                    </span>
                    <div class="gal_sh_text" onclick="location.href='<?=$link[$i];?>'"><?/*<a title="" href="<?//=$link[$i];?>">*/?><?=$name[$i];?><?/*</a>*/?></div>
                 </div>
            <?
        }?>
        </div>
    </div>
    <?
    echo "<script>setTopShortText('".$rows."', '". $short[0]."','". $short[1]."','". $short[2]."','". $short[3]."','". $short[4]."');</script>";
    echo "<script>setTopHref('".$rows."', '". $link[0]."','". $link[1]."','". $link[2]."','". $link[3]."','". $link[4]."');</script>";
    echo "<script>setTopImage('".$rows."', '".$pathImage[0]."','".$pathImage[1]."','".$pathImage[2]."','".$pathImage[3]."','".$pathImage[4]."');</script>";
}   //end of function NewsTop()


    // ================================================================================================
    // Function : NewsCatLast()
    // Date :    02.03.2011
    // Returns : true/false
    // Description : Show Last news for $cat
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function NewsCatLast( $id_cat=3, $limit=6, $caption = null){
        $arr= $this->GetNewsCatLast( $id_cat, $limit);
        if(is_array($arr)) {
            $rows = count($arr);
            if($rows==0)
                return;
            if(!$caption )
                $caption = $this->multi['MOD_NEWS_CAT_LATEST'];
         ?>
         <div class="clear">&nbsp;</div>
         <div class="lastCategoryNews">
         <div class="news_colum1_1_title"><?=$caption?></div>
         <div class="innerSpacer"><?
            for( $i=0; $i<$rows; $i++ )
            {
              $row = $arr[$i];
              $name = strip_tags(stripslashes($row['name']));
              //$short = $this->Crypt->TruncateStr(strip_tags(stripslashes($row['shrt'])),200);
              //$short = strip_tags(stripslashes($row['shrt']));
              $link = $this->Link($row['id_category'], $row['id']);
              ?>
              <div class="lastNews">
                  <div class="date"><?=$this->ConvertDate($row['start_date'], false, true);?></div>
                  <a class="name" href="<?=$link;?>"><?=$name;?></a>
              </div>
              <?
            }
            //$linkCat = $this->Link($id_cat);
            /*?><a class="allNews" href="<?=$linkCat?>">Всі новини</a><?*/
            ?>
            <div class="clear">&nbsp;</div>
            </div>
            </div><?
         }
    } // end of function NewsCatLast


    /**
     * NewsLayout::GetMap()
     * Show map of News
     * @return void
     */
    function GetMap()
      {
     $q = "SELECT cod, name FROM `".TblModNewsCat."` WHERE `lang_id`='".$this->lang_id."' ORDER BY `cod` ASC ";
     $res = $this->db->db_Query( $q );
     $rows = $this->db->db_GetNumRows();
     $arrCateg = array();
     for( $i = 0; $i < $rows; $i++ )
     {
       $arrCateg[] = $this->db->db_FetchAssoc();
     }
    ?><ul><?
     for( $i = 0; $i < $rows; $i++ )
     {
       $row = $arrCateg[$i];
       $name = $row['name'];
       $q1 = "SELECT
                   `".TblModNews."`.id,
                   `".TblModNews."`.id_category,
                   `".TblModNewsSprSbj."`.name,
                   `".TblModNewsLinks."`.link
               FROM
                    `".TblModNews."`,
                    `".TblModNewsSprSbj."`,
                    `".TblModNewsLinks."`
               WHERE
                    `".TblModNews."`.id_category='".$row['cod']."'
                AND
                    `".TblModNews."`.id=`".TblModNewsSprSbj."`.cod
                AND
                    `".TblModNewsSprSbj."`.name !=''
                AND
                    `".TblModNewsSprSbj."`.lang_id='"._LANG_ID."'
                AND
                    `".TblModNews."`.status!='i'
                AND
                    `".TblModNews."`.id=`".TblModNewsLinks."`.cod
                ORDER BY
                        start_date DESC ";

       $res1 = $this->db->db_Query( $q1 );
       $rows1 = $this->db->db_GetNumRows();
       //echo "<br> ".$q1." <br/>res=".$res1." rows=".$rows1;
       if( $rows1 )
       {
            $arrNews = array();
            for( $k = 0; $k < $rows1; $k++ )
            {
                $arrNews[] = $this->db->db_FetchAssoc();
            }
            $catLink = $this->Link($row['cod'], NULL);
            for( $j = 0; $j < $rows1; $j++ )
            {
              $row1 = $arrNews[$j];
              $link = _LINK.'news/'.$row1['link'].'.html';
              //$link = $this->Link($row1['id_category'], $row1['id'], $row1['link']);
              ?><li><a href="<?=$link;?>"><?=stripslashes($row1['name'])?></a></li><?
            }
           }
       }
     ?></ul><?
    } // end of function GetMap



    //====================================== SubSribe START ===========================================

    // ================================================================================================
    // Function : SubscrForm()
    // Date : 01.03.2011
    // Description : subscribers registration simply form
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function SubscrForm()
    {
        ?>
        <h2><?=$this->multi['TXT_SUBSCRIBE'];?></h2>
        <form action="<?=_LINK?>news/" method=post>
        <input type=hidden name="task" value="save_reg_data">
        <input type=hidden name="categories" value="all">
        <?=$this->ShowErr();?>
        <table border="0" cellspacing="3" cellpadding="0">
         <tr>
           <td aling="right"><?=$this->multi['_FLD_EMAIL'];?>:</td>
           <td><?=$this->Form->TextBox('subscriber', $this->subscriber, 15 );?></td>
           <td colspan="2" align="right"><input type="submit" value="<?=$this->multi['TXT_SAVE'];?>"></td>
         </tr>
        </table>
        </form>
        <?
    } // end of function  SubscrForm


    // ================================================================================================
    // Function : SubscrRegForm()
    // Date : 01.03.2011
    // Description : subscribers registration form
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function SubscrRegForm()
    {
        ?>
        <form action="<?=_LINK?>news/subscribe/add/" method=post>
        <input type=hidden name="task" value="save_reg_data">
        <?=$this->ShowErr();?>
        <table border="0" cellspacing="3" cellpadding="0">
         <tr>
           <td aling="right"><?=$this->multi['FLD_NEWS_SUBSCR_EMAIL'];?>:</td>
           <td><?=$this->Form->TextBox('subscriber', $this->subscriber, 30 );?></td>
         </tr>
         <tr>
          <td aling="right"><?=$this->multi['FLD_NEWS_SUBSCR_PASSWORD'];?>:</td>
          <td><?=$this->Form->Password('subscr_pass', $this->subscr_pass, 30 );?></td>
         </tr>
         <tr>
          <td colspan="2">
         <?
         $this->Err!=NULL ? $val=$this->categories : $val = $val = $this->Spr->GetListName( TblModNewsCat, $this->lang_id, 'arr', 'cod', 'asc', 'cod' );
         $cnt_cat_in_row = 4;
         $cnt_rows = ceil($this->Spr->GetCountValuesInSprOnLang( TblModNewsCat, $this->lang_id )/$cnt_cat_in_row);
         $this->Spr->ShowInCheckBox( TblModNewsCat, 'categories', $cnt_rows, $val, 'left' );?>
          </td>
         </tr>
         <tr>
          <td colspan="2" align="center"><input type="submit" value="<?=$this->multi['TXT_SAVE'];?>"></td>
         </tr>
        </table>
        </form>
        <?
    } // end of function  SubscrRegForm

    // ================================================================================================
    // Function : CheckFields()
    // Date : 01.03.2011
    // Returns :      true,false / Void
    // Description :  check fields
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function CheckFields()
    {
         $this->Err=NULL;

         if (empty( $this->subscriber )) $this->Err = $this->Err.$this->multi['MSG_FLD_EMAIL_EMPTY'].'<br>';
         else{
           // if ( $this->email!=$this->email2 ) $this->Err = $this->Err.$this->Msg->show_text('MSG_NOT_MATCH_REENTER_EMAIL').'<br>';
            if (!preg_match("^[a-zA-Z0-9_.\-]+@[a-zA-Z0-9.\-].[a-zA-Z0-9.\-]+$", $this->subscriber)) $this->Err = $this->Err.$this->multi['MSG_NOT_VALID_EMAIL'].'<br>';
            else{
                $q = "SELECT * FROM ".TblModNewsSubscr." WHERE `login`='".$this->subscriber."'";
                $res = $this->db->db_Query( $q );
                //echo "<br>11 q=".$q." res=".$res;
                if( !$res ) return false;
                $rows = $this->db->db_GetNumRows();
                if( $rows>0 ) $this->Err = $this->Err.$this->multi['MSG_EMAIL_ALREADY_EXIST'].'<br/>';
            }
         }
         return $this->Err;
    } //end of function CheckFields()
    //====================================== SubSribe END =============================================


    // ================================================================================================
    // Function : ShowErr()
    // Date : 01.03.2011
    // Returns :      true,false / Void
    // Description :  Show errors
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ShowErr()
    {
        if ($this->Err){
        ?>
        <div class="err"  align=center>
            <h2><?=$this->Msg->show_text('MSG_ERR', TblSysTxt);?></h2>
            <?=$this->Err;?>
        </div>
        <?
        }
    } //end of function ShowErr()

    // ================================================================================================
    // Function : ShowTextMessages
    // Date : 01.03.2011
    // Returns : true,false / Void
    // Description : Show the text messages
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function ShowTextMessages( $text = NULL )
    {
        echo "<H3 align=center class='msg'>$text</H3>";
    } //end of function ShowTextMessages()

 // ================================================================================================
 // Function : ShowNewsPath()
 // Returns :      true,false / Void
 // Description :  Show News Path
 // Programmer : Yaroslav Gyryn
 // Date :   01.03.2011
 // ================================================================================================
 function ShowNewsPath () {
    ?>
    <div class="path">
        <a href="<?=_LINK;?>"><?=$this->multi['TXT_HOME_PAGE'];?></a> → <a href="<?=_LINK;?>news/"><?=$this->multi["_TXT_NEWS_TITLE"];?></a>
        <?
         switch( $this->task ){
            case 'showall':
                    if(!empty($this->category))
                        echo ' → '.$this->Spr->GetNameByCod( TblModNewsCat, $this->category);
                    else
                        echo ' → '.$this->multi['MOD_NEWS_ALL'];
                    break;
            case 'arch':
                    echo $this->multi['MOD_NEWS_ARCH'];
                    break;
            case 'showa':
                    echo ' → '.$this->multi['MOD_NEWS_LATEST'];
                    break;
            case 'showfull':
                    if(!empty ($this->id)) {
                        $link = $this->Link($this->category, NULL);
                        ?> → <a href="<?=$link?>"><?=$this->Spr->GetNameByCod( TblModNewsCat, $this->category);?></a> → <?=$this->Spr->GetNameByCod( TblModNewsSprSbj, $this->id);
                     }
                    break;
            default:
                    break;
         }
    ?></div><?
 }//end of function ShowNewsPath()

} //end of class NewsLayout
?>
