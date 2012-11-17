<?php
include_once( SITE_PATH.'/modules/mod_response/response.defines.php' );

 /**
  * responseLayout
  * 
  * @package 
  * @author Yaroslav
  * @copyright 2011
  * @version $Id$
  * @access public
  */
 class ResponseLayout extends Response {
   var $db = NULL;
   var $Msg = NULL;
   var $Spr = NULL;
   var $Form = NULL;
   var $lang_id = NULL;
   
   var $title = NULL;
   var $description = NULL;
   var $keywords = NULL;
   var $group_id = NULL;
   var $item = NULL;
   var $task=NULL;
   
   /**
    * responseLayout::responseLayout()
    * 
    * @param mixed $user_id
    * @param mixed $module
    * @param mixed $display
    * @param mixed $sort
    * @param mixed $start
    * @param mixed $width
    * @return void
    */
   function ResponseLayout($user_id=NULL, $module=NULL, $display=NULL, $sort=NULL, $start=NULL, $width = NULL) {
            //Check if Constants are overrulled
            ( $user_id  !="" ? $this->user_id = $user_id  : $this->user_id = NULL );
            ( $module   !="" ? $this->module  = $module   : $this->module  = NULL );
            ( $display  !="" ? $this->display = $display  : $this->display = 10   );
            ( $sort     !="" ? $this->sort    = $sort     : $this->sort    = NULL );
            ( $start    !="" ? $this->start   = $start    : $this->start   = 0    );
            ( $width    !="" ? $this->width   = $width    : $this->width   = 750  );

            if(defined("_LANG_ID")) $this->lang_id = _LANG_ID;
            
            $this->db =  DBs::getInstance();
            if(empty($this->Spr)) $this->Spr = &check_init('FrontSpr', 'FrontSpr');
            if(empty($this->multi)) $this->multi = &check_init_txt('TblFrontMulti', TblFrontMulti);
            if (empty($this->Form))  $this->Form = Singleton::getInstance('FrontForm','form_response');
            if (empty($this->Crypt)) $this->Crypt = &check_init('Crypt', 'Crypt');
   } // End of responseLayout Constructor
   

   function GetGroups( )
   {
       $q = "SELECT `".TblModresponseSprGroup."`.*
             FROM `".TblModresponseSprGroup."`
             WHERE `".TblModresponseSprGroup."`.`name`!=''
             AND `".TblModresponseSprGroup."`.`lang_id`='".$this->lang_id."'
             ORDER BY `".TblModresponseSprGroup."`.`move`
            ";   
       $res = $this->db->db_Query( $q );
       //echo '<br>$q='.$q.' $res='.$res.' $dbr->result='.$dbr->result;
       if ( !$res or !$this->db->result) return false;
       $rows = $this->db->db_GetNumRows();
       //echo '<br>rows='.$rows;
       $arr = NULL;
       for( $i = 0; $i < $rows; $i++ ){
           $row = $this->db->db_FetchAssoc();
           $arr[$i] = $row;
       }                      
       return $arr;
   } //end of function GetGroups()    
       

   function GetresponseShort($group_id='')
   {
       $q = "SELECT `".TblModresponse."`.*, `".TblModresponseSprName."`.`name`, `".TblModresponseSprShort."`.`name` AS `name_short`
             FROM `".TblModresponse."`, `".TblModresponseSprName."`, `".TblModresponseSprShort."`
             WHERE `".TblModresponse."`.`visible`='1'
             AND `".TblModresponseSprName."`.`name`!=''
             AND `".TblModresponseSprName."`.`cod`=`".TblModresponse."`.`id`
             AND `".TblModresponseSprName."`.`lang_id`='".$this->lang_id."'
             AND `".TblModresponseSprShort."`.`name`!=''
             AND `".TblModresponseSprShort."`.`cod`=`".TblModresponse."`.`id`
             AND `".TblModresponseSprShort."`.`lang_id`='".$this->lang_id."'
            ";
       if( !empty($group_id)) $q = $q."AND `".TblModresponse."`.`group_d`='".$group_id."'" ;
       $q = $q."ORDER BY RAND() LIMIT 1";   
       
       $res = $this->db->db_Query( $q );
       //echo '<br>$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
       if ( !$res or !$this->db->result) return false;
       $rows =  $this->db->db_GetNumRows();
       //echo '<br>rows='.$rows;
       
       $arr = NULL;
       for( $i = 0; $i < $rows; $i++ ){
           $row=$this->db->db_FetchAssoc();
           //echo '<br>$i = '.$i.' arr='.$row['city_d'];  
           //$name = $this->Spr->GetNameByCod(TblModDealers, $row['name'], $this->lang_id, 1);
           $arr[$i] = $row;
       }                      
       return $arr;
       
   } //end of fuinction GetresponseShort()
       
   function ShowresponseShort()
   {
       $multi_lang=array("'MAIN_PAGE_FOOT_SITES'","'MAIN_PAGE_FOOT_SEO'","'MAIN_PAGE_FOOT_DESIGN'","'TXT_DETAILS'");
       $this->multi=$this->Spr->GetArrNameByArrayCod('sys_spr_txt',$multi_lang);
       $groups = $this->GetGroups();
       //print_r($groups);
       $cnt_groups = count($groups);
       
       ?>
      <div class="tabs">
       <ul class="tab_header">
       <?
       //show tabs headers
       for($i=0;$i<$cnt_groups;$i++){
            if($i==0) $class="select";
            else $class="";
            ?>
              <li class="<?=$class;?>" target="<?=$groups[$i]['translit'];?>">
                <ins class="center top"></ins> 
                <div>
                  <span><?=$groups[$i]['name'];?></span>
                </div>
                <ins class="round tl"></ins>
                <ins class="round tr"></ins>        
              </li>
              <?
       }
       ?>
       </ul>
       <div class="tab_content">
        <h3><a href="<?=_LINK;?>clients/interview/"><?=$this->multi['MAIN_PAGE_FOOT_SITES']?></a></h3>
        <?
        //show content by every of tabs
        for($i=0;$i<$cnt_groups;$i++){
            $comm = $this->GetresponseShort($groups[$i]['cod']);
            //print_r($comm);
            $cnt_comm = count($comm);
            if($i==0) $style='';
            else $style = 'style="display:none"';
            for($j=0;$j<$cnt_comm;$j++){
                $link = $this->Link($comm[$j]['id'], 'item');
                //$link_goto = $this->Link($comm[$j]['id'], 'goto');
                ?>
                <div class="<?=$groups[$i]['translit'];?>" <?=$style;?>>
                  <dl>
                    <dt><?=$comm[$j]['name'];?></dt>
                    <dd style="font-size:10px;"><?=$comm[$j]['url'];?></dd>
                    <dd class="user_comment"><?=$comm[$j]['name_short'];?><br/><a href="<?=$link;?>"><?=$this->multi['TXT_DETAILS']?>...</a></dd>
                  </dl> 
                </div>
                <?
            }
       }
       ?> 
       </div>
       <ins class="center bottom"></ins> 
      </div>         
     <?
   } //end of fuinction ShowresponseShort()
   
   
   
   /**
    * responseLayout::GetresponseAll()
    * @author Yaroslav 
    * @param string $group_id
    * @return $arr - array of response
    */
   function GetresponseAll($limit='limit')
   {
       $q = "SELECT 
                    `".TblModresponse."`.*,
                    `".TblModresponseTxt."`.name,
                    `".TblModresponseTxt."`.short,
                    `".TblModresponseTxt."`.full
             FROM `".TblModresponse."`,`".TblModresponseTxt."`
             WHERE `".TblModresponse."`.`visible`='1'
             AND `".TblModresponseTxt."`.name !=''
             AND `".TblModresponseTxt."`.`cod`=`".TblModresponse."`.`id`
             AND `".TblModresponseTxt."`.`lang_id`='".$this->lang_id."'
            ";
       if( !empty($this->group_id)) 
            $q = $q."AND `".TblModresponse."`.`group_d`='".$this->group_id."'" ;
       
       $q = $q."ORDER BY `".TblModresponse."`.`move`";
       if($limit=='limit') $q = $q." LIMIT ".$this->start.",".$this->display."";   
       
       $res = $this->db->db_Query( $q );
       //echo '<br>'.$q.'<br/> $res='.$res.' $this->db->result='.$this->db->result;
       if ( !$res or !$this->db->result) return false;
       $rows =  $this->db->db_GetNumRows();
       //echo '<br>rows='.$rows;
       
       $arr = NULL;
       for( $i = 0; $i < $rows; $i++ ){
           $row=$this->db->db_FetchAssoc();
           $arr[$i] = $row;
       }                      
       return $arr;
       
   } //end of function GetresponseAll()
   

   
   /**
    * responseLayout::ShowresponseByPages()
    * Show list of response on the front-end
    * @return void
    */
   function ShowresponseByPages()
   {
       $array = $this->GetresponseAll('limit');
       $rows = count($array);
       for( $i = 0; $i < $rows; $i++ ){
            $value = $array[$i];
            $name = stripslashes($value['name']);
            //$short = $this->Crypt->TruncateStr(strip_tags(stripslashes($value['short'])),1000);
            $short = stripslashes($value['short']);
            $link = $this->Link( $value['id']);
            //$link_goto = stripslashes($value['url']);
            ?>
            <div class="videoNew">
                <div class="videoNewTitle"><a href="<?=$link;?>" title="<?=$name;?>"><?=$name;?></a></div>
            <?/*if( !empty($comm[$i]['img'])){ 
                echo $this->ShowImage($comm[$i]['img'], "size_width=100", 85, NULL, 'align="left" style="margin: 0px 10px 10px 0px;"');
            }*/?>
            <div class="responseData">
                <div class="responseShort">
                <?if( !empty($value['img2']))
                {
                    $path = response_Img_Path.$value['img2'];
                    ?><div align="center">
                        <a href="<?=$path;?>" class="highslide" onclick="return hs.expand(this);"><?=$this->ShowImage($value['img2'], "size_height=300", 85, NULL, null);?></a>
                    </div><?
                }
                else 
                    echo $short;?>
                </div>
                <a class="detail" href="<?=$link;?>"><?=$this->multi['TXT_DETAILS'];?>&nbsp;&rarr;</a>
            </div>
            <?/*<a href="<?=$link_goto;?>" target="_blank"><?=$link_goto;?></a>*/?>
           </div>
           <?
       }//end for
       
     if($rows>0){
     ?>
     <div class="clear">&nbsp;</div>
     <div class="pageNaviClass"><?
         $comm = $this->GetresponseAll('nolimit');
         $n_rows = $rows = count($comm);
         $link = $this->Link();
         $this->Form->WriteLinkPagesStatic( $link, $n_rows, $this->display, $this->start, $this->sort, $this->page );
     ?></div>
     <div class="clear">&nbsp;</div>
     <?
     }
     
   } //end of fuinction ShowresponseByPages()


   /**
    * responseLayout::ShowresponseDetails()
    * @author Yaroslav
    * @return
    */
   function ShowresponseDetails()
   {
       $q = "SELECT `".TblModresponse."`.*,
                    `".TblModresponseTxt."`.`name`,
                    `".TblModresponseTxt."`.`short`,
                     `".TblModresponseTxt."`.`full`
             FROM `".TblModresponse."`, `".TblModresponseTxt."`
             WHERE `".TblModresponse."`.`id`='".$this->item."'
             AND `".TblModresponse."`.`visible`='1'
             AND `".TblModresponseTxt."`.`name`!=''
             AND `".TblModresponseTxt."`.`cod`=`".TblModresponse."`.`id`
             AND `".TblModresponseTxt."`.`lang_id`='".$this->lang_id."'
            ";
       $res = $this->db->db_Query( $q );
       //echo '<br>$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
       if ( !$res or !$this->db->result) return false;
       $row=$this->db->db_FetchAssoc();
       $link_goto = $this->Link($row['id'], 'goto');
       $name = stripslashes($row['name']);
       ?>
       <div class="right"><a href="javascript:history.back()">←<?=$this->multi['TXT_FRONT_GO_BACK'];?></a></div>
         <div class="videoNewTitle"><?=$name;?></div>
        <?if( !empty($row['img'])){ echo $this->ShowImage($row['img'], "size_width=100", 85, NULL, 'align="left" style="margin: 0px 10px 10px 0px;"');}?>
        <br/><a href="<?=$link_goto;?>" target="_blank"><?=stripslashes($row['url']);?></a>
        <p><?=stripslashes($row['full']);?></p>
        <?if( !empty($row['img2'])){ echo $this->ShowImage($row['img2'], "size_width = 598", 85, NULL, null);}?>
       <?
   }//end of function ShowresponseDetails()


    /**
     * responseLayout::GetMap()
     * Show map of response
     * @author Yaroslav
     * @return void
     */
    function GetMap() {
     $q = "SELECT * FROM `".TblModresponseSprGroup."` WHERE `lang_id`='"._LANG_ID."'ORDER BY `cod` ASC ";
     $res = $this->db->db_Query( $q );
     $rows = $this->db->db_GetNumRows();
     $arr = array();
     for( $i = 0; $i < $rows; $i++ )
       $arr[] = $this->db->db_FetchAssoc();
     
     for( $i = 0; $i < $rows; $i++ )
     {
       $row = $arr[$i];
       $name = $row['name'];
       $q1 = "SELECT
                `".TblModresponse."`.id,
                `".TblModresponse."`.group_d,
                `".TblModresponseTxt."`.name,
                `".TblModresponseLinks."`.link
              FROM `".TblModresponse."` ,`".TblModresponseTxt."`, `".TblModresponseLinks."`
              WHERE
                `".TblModresponse."`.group_d ='".$row['cod']."'
              AND
                `".TblModresponse."`.id = `".TblModresponseTxt."`.cod
              AND 
                `".TblModresponseTxt."`.lang_id = '".$this->lang_id."'
              AND
               `".TblModresponse."`.visible = 1
               AND
               `".TblModresponseTxt."`.name !=''
              AND
                `".TblModresponse."`.id = `".TblModresponseLinks."`.comment_id
               AND 
                `".TblModresponseLinks."`.lang_id = '".$this->lang_id."'
              ORDER BY 
                `".TblModresponse."`.move DESC
       ";
          
       $res1 = $this->db->db_Query( $q1 );
       //echo '<br/>'.$q1;
       $rows1 = $this->db->db_GetNumRows();
       if( $rows1 )
       {
        ?><ul><?
        $arr2= array();
        for( $j = 0; $j < $rows1; $j++ )
            $arr2[] = $this->db->db_FetchAssoc();
        
        for( $j = 0; $j < $rows1; $j++ )
        {
          $row1 = $arr2[$j];
          $name1 = stripslashes($row1['name']);
          $link = $this->Link($row1['id']);
          //$link = $linkCat.$row1['link'].'.html';
          ?><li><a href="<?=$link;?>"><?=$name1;?></a></li><?
        }
        ?></ul><?
       }
     }
    }


   /**
    * responseLayout::Link()
    * @author Yaroslav
    * @param mixed $id - id of the comment
    * @return $link
    */
   function Link($id=NULL)
   {
       $link='';
       if( !empty($id)) {
           $q = "SELECT `".TblModresponseLinks."`.`link`, `".TblModresponseSprGroup."`.`translit`
                 FROM `".TblModresponseLinks."`, `".TblModresponseSprGroup."`, `".TblModresponse."`
                 WHERE `".TblModresponseLinks."`.`comment_id`='".$id."'
                 AND `".TblModresponseLinks."`.`lang_id`='".$this->lang_id."'
                 AND `".TblModresponse."`.`id`=`".TblModresponseLinks."`.`comment_id`
                 AND `".TblModresponseSprGroup."`.`cod`=`".TblModresponse."`.`group_d`
                 AND `".TblModresponseSprGroup."`.`lang_id`='".$this->lang_id."'
                ";   
           $res = $this->db->db_Query( $q );
           //echo '<br>'.$q.'<br/> $res='.$res.' $dbr->result='.$this->db->result;
           if ( !$res or !$this->db->result) return false;
           $rows = $this->db->db_GetNumRows();
           //echo '<br>rows='.$rows;
           $row=$this->db->db_FetchAssoc();
           $link =  _LINK.'response/'.$row['translit'].'/'.$row['link'].'.html';
        }
        else
            $link =  _LINK.'response/';
       return $link;
   }//end of function Link();

   
  /**
   * responseLayout::SetMetaData()
   * Set title, description and keywords for this module or for current category or position
   * @author Yaroslav 
   * @return void
   */
  function SetMetaData()
   {
       if( !empty($this->item)){
           $q = "SELECT `".TblModresponseTxt."`.`name`,
                         `".TblModresponseTxt."`.`title` AS `mtitle`,
                         `".TblModresponseTxt."`.`description` AS `mdescr`,
                         `".TblModresponseTxt."`.`keywords` AS `mkeywords`
                 FROM `".TblModresponseTxt."`,  `".TblModresponse."`
                 WHERE `".TblModresponse."`.`id`='".$this->item."'
                 AND `".TblModresponseTxt."`.`cod`=`".TblModresponse."`.`id`
                 AND `".TblModresponseTxt."`.`lang_id`='".$this->lang_id."'
                ";
           $res = $this->db->db_Query($q);
           //echo '<br>$q='.$q.' $res='.$res;
           $row = $this->db->db_FetchAssoc();
           if(!empty($row['mtitle'])) $this->title = $row['mtitle'];
           else $this->title = $row['name'];
           
           if(!empty($row['mdescr'])) $this->description = $row['mdescr'];
           else $this->description = $row['mtitle'];
           
           if(!empty($row['mkeywords'])) $this->keywords = $row['mkeywords'];
           else $this->keywords = $row['mtitle'];            
       }
       elseif( !empty($this->group_id)){
           $row = $this->Spr->GetMetaDataByCod(TblModresponseSprGroup, $this->group_id, $this->lang_id );
           if(!empty($row['mtitle'])) $this->title = $row['mtitle'];
           else $this->title = $this->multi['TXT_FRONT_USERS_RESPONSES'];
           
           if(!empty($row['mdescr'])) $this->description = $row['mdescr'];
           else $this->description = $this->multi['TXT_FRONT_USERS_RESPONSES'];
           
           if(!empty($row['mkeywords'])) $this->keywords = $row['mkeywords'];
           else $this->keywords = $this->multi['TXT_FRONT_USERS_RESPONSES'];
       }
       else{
           $this->title = '';
           $this->description = '';
           $this->keywords = '';
       }
   } //end of function  SetMetaData()    
       
} // End of class responseLayout
