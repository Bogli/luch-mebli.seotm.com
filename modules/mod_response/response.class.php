<?php
include_once( SITE_PATH.'/modules/mod_response/response.defines.php' );


 /**
  * response
  * 
  * @package SEOCMS 2011
  * @author Yaroslav
  * @copyright 2011
  * @version 1.0
  * @access public
  */
 class Response {

   var $user_id = NULL;
   var $module = NULL;
   var $lang_id = NULL;
   var $Err = NULL;

   var $sort = NULL;
   var $display = 10;
   var $start = 0;
   var $width = 500;
   var $fln = NULL;
   var $fltr = NULL;
   var $fltr2 = NULL; 
   var $srch = NULL;
   
   var $Msg = NULL;
   var $Rights = NULL;
   var $Form = NULL;
   var $Spr = NULL;
   
   var $id = NULL;
   var $group_d = NULL;
   var $url = NULL;
   var $viible = NULL;
   var $move = NULL;
   var $fio = NULL;
   var $name = NULL;
   var $short = NULL;
   var $descr = NULL;
   var $mtitle = NULL;
   var $mdescr = NULL;
   var $mkeywords = NULL;
   var $translit = NULL;
   var $img = NULL;
   var $img2 = NULL;


   /**
    * response::response()
    * 
    * @param mixed $user_id
    * @param mixed $module
    * @param mixed $display
    * @param mixed $sort
    * @param mixed $start
    * @param mixed $width
    * @return void
    */
   function Response($user_id=NULL, $module=NULL, $display=NULL, $sort=NULL, $start=NULL, $width = NULL) {
        //Check if Constants are overrulled
        ( $user_id   !="" ? $this->user_id = $user_id  : $this->user_id = NULL );
        ( $module   !="" ? $this->module  = $module   : $this->module  = NULL );
        ( $display  !="" ? $this->display = $display  : $this->display = 10   );
        ( $sort     !="" ? $this->sort    = $sort     : $this->sort    = NULL );
        ( $start    !="" ? $this->start   = $start    : $this->start   = 0    );
        ( $width    !="" ? $this->width   = $width    : $this->width   = 750  );

        if(defined("_LANG_ID")) $this->lang_id = _LANG_ID;
        
        if (empty($this->Rights)) $this->Rights = new Rights($this->user_id, $this->module);
        //if (empty($this->Msg)) $this->Msg = new ShowMsg();
        if(empty($this->Msg)) $this->Msg = &check_init('ShowMsg', 'ShowMsg');
        //$this->Msg->SetShowTable(TblModresponseSprTxt);
        if (empty($this->Spr)) $this->Spr = new  SysSpr();
        if (empty($this->Form)) $this->Form = new Form('form_response');
        $this->multi = &check_init_txt('TblBackMulti',TblBackMulti);
   } // End of response Constructor



   /**
    * response::GetContent()
    * Execute SQL query
    * @author Yaroslav
    * @param string $limit
    * @return
    */
   function GetContent($limit='limit')
   {
       // search in name
       //echo '$this->srch ='.$this->srch; 
       if( $this->srch ) {
           $q = "SELECT `cod` FROM `".TblModresponseTxt."` WHERE `name` LIKE '%$this->srch%'";
           $res = $this->Rights->Query($q);
           //echo '<br>$q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
           $rows = $this->Rights->db_GetNumRows();
           $srch_str = NULL;
           for( $i = 0; $i < $rows; $i++ )
           {
               $row = $this->Rights->db_FetchAssoc(); 
               if ( empty($srch_str) ) $srch_str = "'".$row['cod']."'";
               else $srch_str = $srch_str.",'".$row['cod']."'";
           } 
       }
       // search in description
       if( $this->srch2) {
           $q = "SELECT `cod` FROM `".TblModresponseTxt."` WHERE `full` LIKE '%$this->srch2%'";
           $res = $this->Rights->Query($q);
           //echo '<br>$q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
           $rows = $this->Rights->db_GetNumRows();
           $srch_str2 = NULL;
           for( $i = 0; $i < $rows; $i++ )
           {
               $row = $this->Rights->db_FetchAssoc(); 
               if ( empty($srch_str2) ) $srch_str2 = "'".$row['cod']."'";
               else $srch_str2 = $srch_str2.",'".$row['cod']."'";
           } 
       }                 
    
       if( !$this->sort ) $this->sort='move';
     
       $q = "SELECT `".TblModresponse."`.*,
                    `".TblModresponseTxt."`.name,
                    `".TblModresponseTxt."`.short,
                    `".TblModresponseTxt."`.full,
                    `".TblModresponseSprGroup."`.name AS `grpname`
             FROM `".TblModresponse."`, `".TblModresponseTxt."`, `".TblModresponseSprGroup."`
             WHERE 1 
             AND `".TblModresponseTxt."`.`cod`=`".TblModresponse."`.`id`
             AND `".TblModresponseTxt."`.`lang_id`='".$this->lang_id."'
             AND `".TblModresponseSprGroup."`.`cod`=`".TblModresponse."`.`group_d`
             AND `".TblModresponseSprGroup."`.`lang_id`='".$this->lang_id."'
            ";
       if( $this->srch ) $q = $q." AND `".TblModresponse."`.`id` IN (".$srch_str.")";
       if( $this->srch2 ) $q = $q." AND `".TblModresponse."`.`id` IN (".$srch_str2.")";
       if( $this->fltr ) $q = $q." AND `".TblModresponse."`.`group_d`='".$this->fltr."'";
       if( $this->fltr2 ) $q = $q." AND `".TblModresponse."`.`city_d`='".$this->fltr2."'";
       $q = $q." ORDER BY `".TblModresponse."`.".$this->sort;
       $res = $this->Rights->Query( $q, $this->user_id, $this->module );
       //echo '<br>'.$q.'<br/> $res='.$res.' $this->Rights->result='.$this->Rights->result;
       if( !$res )return false;
       return true;
   }//end of function GetContent()    
   

   /**
    * response::show()
    * Show data
    * @author Yaroslav
    * @return
    */
   function show()
   {
       $this->GetContent('nolimit');
       $rows = $this->Rights->db_GetNumRows();
       // echo '<br> this->srch ='.$this->srch.' $script='.$script;

       /* Write Form Header */
       $this->Form->WriteHeader( $this->script );
       $this->ShowContentFilters(); 

       /* Write Table Part */
       AdminHTML::TablePartH();

       /* Write Links on Pages */
       echo '<TR><TD COLSPAN=9>';
       //$script1 = 'module='.$this->module.'&fltr='.$this->fltr.'&fltr2='.$this->fltr2;
       //$script1 = $_SERVER['PHP_SELF']."?$script1";
       $this->Form->WriteLinkPages( $this->script, $rows, $this->display, $this->start, $this->sort );

       echo '<TR><TD COLSPAN=4>';
       $this->Form->WriteTopPanel( $this->script );

       ?><td align="center"><?
       $this->Spr->ShowActSprInCombo(TblModresponseSprGroup, 'fltr', $this->fltr, $_SERVER["QUERY_STRING"]."&start=$this->start&display=$this->display&sort=$this->sort&srch=$this->srch&srch2=$this->srch2");

       ?>
       <tr>
        <th class="THead">*</th>
        <th class="THead"><a href=<?=$this->script?>&sort=id><?=$this->multi['_FLD_ID'];?></a></th>
        <th class="THead"><?=$this->multi['FLD_NAME'];?></th>
        <th class="THead"><a href=<?=$this->script?>&sort=img><?=$this->multi['_FLD_IMAGE'];?></a></th>
        <th class="THead"><a href=<?=$this->script?>&sort=group_d><?=$this->multi['FLD_CATEGORY'];?></a></th>
        <?/*
        <th class="THead"><a href=<?=$this->script?>&sort=city_d><?=$this->Msg->show_text('_FLD_CITY', TblSysTxt)?></A></th>
        */?>
        <th class="THead"><?=$this->multi['FLD_DESCR'];?></th>
        <th class="THead"><?=$this->multi['FLD_VISIBLE'];?></th>        
        <th class="THead"><?=$this->multi['FLD_DISPLAY'];?></th>         
       <?
       $this->GetContent();
       $rows = $this->Rights->db_GetNumRows();
       $arr=array();
       for( $i = 0; $i < $rows; $i++ ){
           $arr[] = $this->Rights->db_FetchAssoc();
       }
       $a = $rows;
       $up = 0;
       $down = 0;
       $style1 = 'TR1';
       $style2 = 'TR2';
       for( $i = 0; $i < $rows; $i++ ){
           $row = $arr[$i];

           if ( (float)$i/2 == round( $i/2 ) ) echo '<TR CLASS="'.$style1.'">';
           else echo '<TR CLASS="'.$style2.'">';

           ?><td><?
           $this->Form->CheckBox( "id_del[]", $row['id'] );

           ?><td><?
           $this->Form->Link( $this->script."&task=edit&id=".$row['id'], stripslashes( $row['id'] ), $this->multi['_TXT_EDIT_DATA'] );

           
           ?><td align="center"><?=stripslashes($row['name']);?></td><?           

           ?><td align="center"><?
              if ( !empty($row['img']) ){
                ?><a href="<?=response_Img_Path.$row['img'];?>" target="_blank" onmouseover="return overlib('<?=$this->multi['TXT_ZOOM_IMG'];?>',WRAP);" onmouseout="nd();" alt="<?=$this->multi['TXT_ZOOM_IMG'];?>" title="<?=$this->multi['TXT_ZOOM_IMG'];?>"><?
                echo $this->ShowImage($row['img'], 'size_width=75', 100, NULL, "border=0");
                ?></a><br/><?
              }          
      
            ?><td align="center"><?=stripslashes($row['grpname']);?></td><?
      
            ?><td align="center"><?
            if( trim( $row['full']!='' ) ) $this->Form->ButtonCheck();

            ?><td align="center"><? 
            if( $row['visible'] == 0 ) $this->Form->Img( 'http://'.NAME_SERVER.'/admin/images/icons/publish_x.png', $this->multi['TXT_UNVISIBLE'], 'border=0' );
            if( $row['visible'] == 1 ) $this->Form->Img( 'http://'.NAME_SERVER.'/admin/images/icons/publish_g.png', $this->multi['TXT_VISIBLE'], 'border=0' );
                  
            ?><td align="center"><?
               if( $up!=0 )
               {
               ?>
                <a href=<?=$this->script?>&task=up&move=<?=$row['move']?>>
                <?=$this->Form->ButtonUp( $row['id'] );?>
                </a>
               <?
               }

               if( $i!=($rows-1) )
               {
               ?>
                 <a href=<?=$this->script?>&task=down&move=<?=$row['move']?>>
                 <?=$this->Form->ButtonDown( $row['id'] );?>
                 </a>
               <?
               }

               $up=$row['id'];
               $a=$a-1;                    
            ?></td><?

    } //-- end for

    AdminHTML::TablePartF();
    $this->Form->WriteFooter();
    return true;

   } //end of fuinction show


   // ================================================================================================
   // Function : ShowContentFilters
   // Version : 1.0.0
   // Date : 05.12.2006 
   //
   // Parms :
   // Returns : true,false / Void
   // Description : Show content of the catalogue
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 05.12.2006 
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowContentFilters()
   { 
     /* Write Table Part */
     AdminHTML::TablePartH();
       //phpinfo();
     ?>
     <table border="0" cellpadding="0" cellspacing="0">
      <tr valign="top">
       <?/*
       <td>
         <table border=0 cellpadding=2 cellspacing=1>
          <tr><td><h4><?=$this->Msg->show_text('TXT_FILTERS');?></h4></td></tr>
          <tr class=tr1>
           <td align=left><?=$this->Msg->show_text('_FLD_GROUP', TblSysTxt);?></td>
           <td align=left><?$this->Spr->ShowActSprInCombo(TblModDealerSprGroup, 'fltr', $this->fltr, $_SERVER["QUERY_STRING"]);?></td>
          </tr>
          <tr class=tr2>
           <td align=left><?=$this->Msg->show_text('_FLD_CITY', TblSysTxt);?></td>
           <td align=left><?$this->Spr->ShowActSprInCombo(TblModDealerSprCity, 'fltr2', $this->fltr2, $_SERVER["QUERY_STRING"]);?></td>
          </tr> 
         </table>
       </td>
       <td width=30></td>
       */?>
       <td>
         <table border="0" cellpadding="2" cellspacing="1">
          <tr><td><h4><?=$this->Msg->show_text('TXT_BUTTON_SEARCH');?></h4></td></tr>
          <tr class="tr1">
           <td><?=$this->Msg->show_text('_FLD_NAME');?></td>
           <td><?$this->Form->TextBox('srch', $this->srch, 20);?></td>
          <tr class="tr1">
           <td><?=$this->Msg->show_text('FLD_DESCR');?></td>
           <td><?$this->Form->TextBox('srch2', $this->srch2, 20);?></td>                  
          <tr class="tr2">
           <td colspan="2"><?$this->Form->Button( '', $this->Msg->show_text('TXT_BUTTON_SEARCH'), 50 );?></td>
          <tr>
          </tr>
         </table>
       </td>
      </tr>
     </table>
     <?
     AdminHTML::TablePartF();
         
   } //end of fuinction ShowContentFilters()       
   
   

   
   /**
    * response::edit()
    * Edit/add records in response module
    * @author Yaroslav 
    * @return
    */
   function edit()
   {
    $Panel = new Panel();
    $ln_sys = new SysLang();
    $Spr = new SysSpr();
    $mas=NULL; 
    if( $this->id!=NULL)
    {
      $q = "SELECT * FROM ".TblModresponse." where id='$this->id'";
      $res = $this->Rights->Query( $q, $this->user_id, $this->module );
      if( !$res ) return false;
      $mas = $this->Rights->db_FetchAssoc();
    }
     
    /* Write Form Header */
    $this->Form->WriteHeaderFormImg( $this->script ); 
    
    $this->Form->Hidden( 'display', $this->display );
    $this->Form->Hidden( 'start', $this->start );
    $this->Form->Hidden( 'sort', $this->sort );
    $this->Form->Hidden( 'srch', $this->srch ); 
    $this->Form->Hidden( 'srch2', $this->srch2 );
    $this->Form->Hidden( 'fltr', $this->fltr );
    $this->Form->Hidden( 'fltr2', $this->fltr2 );
    $this->Form->Hidden( 'fln', $this->fln );
    $this->Form->Hidden( 'delimg', "" );        
    $this->Form->Hidden( 'delimg2', "" );
    
    //$this->Form->IncludeHTMLTextArea();
    $settings=SysSettings::GetGlobalSettings();
    $this->Form->textarea_editor = $settings['editer']; //'tinyMCE';
    $this->Form->IncludeSpecialTextArea( $this->Form->textarea_editor );
    
    if( $this->id!=NULL ) $txt = $this->Msg->show_text('_TXT_EDIT_DATA');
    else $txt = $this->Msg->show_text('_TXT_ADD_DATA');
    
    AdminHTML::PanelSubH( $txt );
    //-------- Show Error text for validation fields --------------
    $this->ShowErrBackEnd();
    //-------------------------------------------------------------          
    AdminHTML::PanelSimpleH();
    $settings=SysSettings::GetGlobalSettings();
    $this->Form->textarea_editor = $settings['editer']; //'tinyMCE'; 
    $this->Form->IncludeSpecialTextArea( $settings['editer']); 
    ?>
    <table border="0" class="EditTable">
     <tr>
      <td>
       <b><?=$this->multi['_FLD_ID'];?>:</b>
       <?
       if( $this->id!=NULL ){
           echo $mas['id'];
           $this->Form->Hidden( 'id', $mas['id'] );
       }
       else $this->Form->Hidden( 'id', '' );
       ?>
      </td>
      <td width="90%">
       <b><?=$this->multi['FLD_VISIBLE']?>:</b>
       <?
       $arr_v[0]=$this->multi['TXT_UNVISIBLE'];
       $arr_v[1]=$this->multi['TXT_VISIBLE'];
        
       if( $this->id!=NULL ) $this->Err!=NULL ? $visible=$this->visible : $visible=$mas['visible'];
       else $visible=$this->visible; 
       $this->Form->Select( $arr_v, 'visible', $visible );
       ?>
       &nbsp;&nbsp;
       <b><?=$this->multi['FLD_CATEGORY'];?>:</b>
       <?
       if( $this->id!=NULL ) $this->Err!=NULL ? $group_d=$this->group_d : $group_d=$mas['group_d'];
       else $group_d=$this->group_d; 
       $this->Spr->ShowInComboBox( TblModresponseSprGroup, 'group_d', $group_d, 40, $this->multi['MSG_FLD_GROUP_EMPTY'] );
       ?>
       <br/><br/>
      </td>
     </tr>
     <tr>
      <td><b><?=$this->multi['FLD_URL'];?>:</b></td>
      <td>
       <?
       if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->url : $val=$mas['url'];
       else $val=$this->url;
       if(empty($val)) $val = 'http://'; 
       $this->Form->TextBox( 'url', stripslashes($val), 80 ); 
       ?><br/><span class="info"><?=$this->multi['TXT_URL_HELP'];?></span>
      </td>
     </tr>
    <TR><TD colspan=2>
    <?
    $this->ShowJS();
    $Panel->WritePanelHead( "SubPanel_" );
    
    $tmp_bd= DBs::getInstance();
    $q1="select * from `".TblModresponseTxt."` where `cod`='".$this->id."'";
     $res = $tmp_bd->db_query( $q1);
     if( !$tmp_bd->result ) return false;
     $rows1 = $tmp_bd->db_GetNumRows();
     $txt= array();
     for($i=0; $i<$rows1;$i++)
     {
        $row1 = $tmp_bd->db_FetchAssoc();
        $txt[$row1['lang_id']]=$row1;
     }
     $ln_arr = $ln_sys->LangArray( _LANG_ID );
 //   $ln_arr = $ln_sys->LangArray( $this->lang_id );
    while( $el = each( $ln_arr ) )
    {
         $lang_id = $el['key'];
         $lang = $el['value'];
         $mas_s[$lang_id] = $lang;
         
         $Panel->WriteItemHeader( $lang );
         echo "\n <table border='0' class='EditTable'>";         
         
         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['FLD_NAME'].":</b>";
         echo "\n <td>";
         $row = NULL;
         if (isset($txt[$lang_id]['name']))
            $row = $txt[$lang_id]['name'];
         else $row='';
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->name[$lang_id] : $val=$row;
         else $this->Err!=NULL ? $val=$this->name[$lang_id] : $val = '';
         /*$row = $this->Spr->GetByCod( TblModresponseSprName, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->name[$lang_id] : $val=$row[$lang_id];
         else $val=$this->name[$lang_id];              */
         
         $this->Form->TextBox( 'name['.$lang_id.']', stripslashes($val), 80 );

         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['_FLD_LINK_NAME'].":</b>";
         echo "\n <td>";
         $link = $this->GetLink( $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->translit[$lang_id] : $val=$link;
         else $val=$this->translit[$lang_id];              
         //$this->Form->TextBox( 'translit['.$lang_id.']', stripslashes($val), 80 );
         if( $this->id ){
             $params = 'disabled';
             $this->Form->Hidden( 'translit['.$lang_id.']', stripslashes($val) ); 
         }
         else {
             $params="onkeyup=\"CheckTranslitField('translit".$lang_id."','tbltranslit".$lang_id."');\"";
         }
         $this->Form->TextBox( 'translit['.$lang_id.']', stripslashes($val), 60, 'id="translit'.$lang_id.'"; style="font-size:10px;" '.$params );
         if( $this->id ){?>&nbsp;<?$this->Form->ButtonSimple("btn", $this->multi['TXT_EDIT'], NULL, "id='button".$lang_id."' onClick=\"EditTranslit('translit".$lang_id."','button".$lang_id."');\"");}

 
         /*echo "\n <tr>";
         echo "\n <td><b>".$this->multi['FLD_F_NAME'].":</b>";
         echo "\n <td>";
         $row = $this->Spr->GetByCod( TblModresponseSprFIO, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->fio[$lang_id] : $val=$row[$lang_id];
         else $val=$this->fio[$lang_id];              
         $this->Form->TextBox( 'fio['.$lang_id.']', stripslashes($val), 80 );*/
                  
         echo "\n <tr>";
         echo "\n <td colspan='2'><b>".$this->multi['FLD_SHORT_DESCR'].":</b>";
         echo "\n <br/>";
         
         
         
         if (isset($txt[$lang_id]['short']))
            $row = $txt[$lang_id]['short'];
        else $row='';
        if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->short[$lang_id] : $val=$row;
        else $this->Err!=NULL ? $val=$this->short[$lang_id] : $val = '';
         /*$row = $this->Spr->GetByCod( TblModresponseSprShort, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->short[$lang_id] : $val=$row[$lang_id];
         else $val=$this->short[$lang_id];*/              
         //$this->Form->HTMLTextArea( 'short['.$lang_id.']', stripslashes($val), 7, 70 );
         $this->Form->TextArea( 'short['.$lang_id.']', stripslashes($val),5, 110 ); 
         
         echo "\n <tr>";
         echo "\n <td colspan='2'><b>".$this->multi['FLD_DESCR'].":</b>";
         echo "\n <br/>";
         
          if (isset($txt[$lang_id]['full']))
            $row = $txt[$lang_id]['full'];
          else $row='';
        if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->full[$lang_id] : $val=$row;
        else $this->Err!=NULL ? $val=$this->full[$lang_id] : $val = '';

         /*$row = $this->Spr->GetByCod( TblModresponseSprDescr, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->descr[$lang_id] : $val=$row[$lang_id];
         else $val=$this->descr[$lang_id];*/              
         $this->Form->SpecialTextArea( $this->Form->textarea_editor, 'full['.$lang_id.']', stripslashes($val), 20, 70, 'style="width:100%;"', $lang_id  );
         
         
         echo "\n<fieldset title='".$this->multi['_TXT_META_DATA']."'> <legend><span style='vetical-align:middle; font-size:15px;'><img src='images/icons/meta.png' alt='".$this->multi['_TXT_META_DATA']."' title='".$this->multi['_TXT_META_DATA']."' border='0' /> ".$this->multi['_TXT_META_DATA']."</span></legend>";
         echo "\n <table border=0 class='EditTable'>";
         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['FLD_PAGES_TITLE'].":</b>";
         echo "\n <br>";
         echo '<span class="help">'.$this->multi['HELP_MSG_PAGE_TITLE'].'</span>';
         echo "\n <br>";

            if (isset($txt[$lang_id]['title']))
                $row = $txt[$lang_id]['title'];
            else $row='';    
            if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->title[$lang_id] : $val=$row;
            else $this->Err!=NULL ? $val=$this->title[$lang_id] : $val = '';
         
         /*$row = $this->Spr->GetByCod( TblModresponseSprMTitle, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->mtitle[$lang_id] : $val=$row[$lang_id];
         else $val=$this->mtitle[$lang_id];*/
         $this->Form->TextBox( 'title['.$lang_id.']', stripslashes($val), 70 );
         echo "<hr width='70%' align='left' size='1'>";


         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['FLD_PAGES_DESCR'].":</b>";
         echo "\n <br>";
         echo '<span class="help">'.$this->multi['HELP_MSG_PAGE_DESCRIPTION'].'</span>';
         echo "\n <br>";
         
         /*$row = $this->Spr->GetByCod( TblModresponseSprMDescr, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->mdescr[$lang_id] : $val=$row[$lang_id];
         else $val=$this->mdescr[$lang_id];*/
         if (isset($txt[$lang_id]['description']))
            $row = $txt[$lang_id]['description'];
         else $row='';
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->description[$lang_id] : $val=$row;
         else $this->Err!=NULL ? $val=$this->description[$lang_id] : $val = '';
         $this->Form->TextArea( 'description['.$lang_id.']', stripslashes($val), 3, 70 );
         echo "<hr width='70%' align='left' size='1'>";

         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['FLD_KEYWORDS'].":</b>";
         echo "\n <br>";
         echo '<span class="help">'.$this->multi['_HELP_MSG_PAGE_KEYWORDS'].'</span>';
         echo "\n <br>";
         
         if (isset($txt[$lang_id]['keywords']))
            $row = $txt[$lang_id]['keywords'];
        else $row='';
        if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->keywords[$lang_id] : $val=$row;
        else $this->Err!=NULL ? $val=$this->keywords[$lang_id] : $val = '';
        //$this->Form->TextArea( 'keywords['.$lang_id.']',  stripslashes($val), 3, 110 );

         /*$row = $this->Spr->GetByCod( TblModresponseSprMKeywords, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->mkeywords[$lang_id] : $val=$row[$lang_id];
         else $val=$this->mkeywords[$lang_id];*/
         $this->Form->TextArea( 'keywords['.$lang_id.']', stripslashes($val),3, 70 );
         echo "\n </table>";
         echo "</fieldset><br>";

         
         echo "\n <td rowspan=3>";
         echo   "\n </table>";
         $Panel->WriteItemFooter();
    }
    $Panel->WritePanelFooter();

    ?>
    <tr><td><b><?=$this->multi['_FLD_IMAGE'];?>:</b>
        <td>
         <table border="0" cellpadding="0" cellspacing="1" class="EditTable">
          <tr>
           <td><?
           if( $this->id!=NULL ) $this->Err!=NULL ? $img=$this->img : $img=$mas['img'];
           else $img=$this->img;                 
            if( !empty($img) ) {
                ?><table border="0" cellpadding="0" cellspacing="5">
                   <tr>
                    <td class='EditTable'><?
                $this->Form->Hidden( 'img', $img);
                ?><a href="<?=response_Img_Path.$img;?>" target="_blank" onmouseover="return overlib('<?=$this->multi['TXT_ZOOM_IMG'];?>',WRAP);" onmouseout="nd();" title="<?=$this->multi['TXT_ZOOM_IMG'];?>"><?
                echo $this->ShowImage($img, 'size_width=75', 100, NULL, "border=0");
                ?></a><br/><?                    
                /*
                <img src="http://<?=NAME_SERVER?>/thumb.php?img=<?=Dealer_Img_Path.$img?>&size_auto=100" border=0 alt="<?=$this->Spr->GetNameByCod( TblModDealerSprName, $mas['id'], $this->lang_id ); ?>">
                */
                ?>
                <td class='EditTable'><?
                echo response_Img_Full_Path.$img.'<br>';
                ?><a href="javascript:form_response.delimg.value='<?=$mas['img'];?>';form_response.submit();"><?=$this->multi['_TXT_DELETE_IMG'];?></a><?
               ?></table><?
               echo '<tr><td><b>'.$this->multi['_TXT_REPLACE_IMG'].':</b>';
            }
              
            ?>
            <input type="file" name="filename"  size="40" value="" alt=""/>                    
            </td>
          </tr>
         </table>
    <?    
    
    ?>
    <tr><td><b><?=$this->multi['FLD_IMAGE2'];?>:</b>
        <td>
         <table border="0" cellpadding="0" cellspacing="1" class="EditTable">
          <tr>
           <td><?
           if( $this->id!=NULL ) $this->Err!=NULL ? $img2=$this->img2 : $img2=$mas['img2'];
           else $img2=$this->img2;                 
            if( !empty($img2) ) {
                ?><table border="0" cellpadding="0" cellspacing="5">
                   <tr>
                    <td class='EditTable'><?
                $this->Form->Hidden( 'img2', $img2);
                ?><a href="<?=response_Img_Path.$img2;?>" target="_blank" onmouseover="return overlib('<?=$this->multi['TXT_ZOOM_IMG'];?>',WRAP);" onmouseout="nd();" alt="<?=$this->multi['TXT_ZOOM_IMG'];?>" title="<?=$this->multi['TXT_ZOOM_IMG'];?>"><?
                echo $this->ShowImage($img2, 'size_width=75', 100, NULL, "border=0");
                ?></a><br/><?                    
                /*
                <img src="http://<?=NAME_SERVER?>/thumb.php?img=<?=Dealer_Img_Path.$img?>&size_auto=100" border=0 alt="<?=$this->Spr->GetNameByCod( TblModDealerSprName, $mas['id'], $this->lang_id ); ?>">
                */
                ?>
                <td class='EditTable'><?
                echo response_Img_Full_Path.$img2.'<br>';
                ?><a href="javascript:form_response.delimg2.value='<?=$mas['img2'];?>';form_response.submit();"><?=$this->multi['_TXT_DELETE_IMG'];?></a><?
               ?></table><?
               echo '<tr><td><b>'.$this->multi['_TXT_REPLACE_IMG'].':</b>';
            }
              
            ?>
            <input type="file" name="filename2"  size="40" value="" alt=""/>                    
            </td>
          </tr>
         </table>
    <?
    if( !empty($this->id) ) $this->Form->Hidden( 'move', $mas['move'] );

    echo '<TR><TD COLSPAN=2 ALIGN=left>';
    $this->Form->WriteSaveAndReturnPanel( $this->script );
    $this->Form->WriteSavePanel( $this->script );
    $this->Form->WriteCancelPanel( $this->script );
    echo '</table>';
    AdminHTML::PanelSimpleF();
    AdminHTML::PanelSubF();

    $this->Form->WriteFooter();
    return true;
   } //end of function edit
   


   /**
    * response::ShowJS()
    * @author Yaroslav
    * @return void
    */
   function ShowJS() 
   {
       ?>
        <script type="text/javascript">
        function EditTranslit(div_id, idbtn){
            Did = "#"+div_id;
            idbtn = "#"+idbtn;
            if( !window.confirm('<?=$this->Msg->get_msg('MSG_DO_YOU_WANT_TO_EDIT_TRANSLIT', TblSysMsg);?>')) return false;
            else{
              $(Did).removeAttr("disabled")
                     .focus();
              $(idbtn).css("display", "none");
            }
        } // end of function EditTranslit
        function CheckTranslitField(div_id, idtbl){
            Did = "#"+div_id;
            idtbl = "#"+idtbl;
            //alert('val='+(Did).val());
            if( $(Did).val()!='') $(idtbl).css("display", "none");
            else $(idtbl).css("display", "block");
        } // end of function EditTranslit
        </script>
        <?       
   }//end of function ShowJS()  
    
   
   /**
    * response::CheckFields()
    * Checking all fields for filling and validation
    * @author  Yaroslav
    * @return
    */
   function CheckFields()
   {
    $this->Err=NULL;
    
    if (empty( $this->group_d)) {
        $this->Err = $this->Err.$this->Msg->show_text('MSG_FLD_GROUP_EMPTY').'<br>';
    }
    
    /*if (empty( $this->client_id)) {
        $this->Err = $this->Err.$this->Msg->show_text('MSG_FLD_CLIENT_EMPTY').'<br>';
    }*/
                 
    if (empty( $this->name[$this->lang_id])) {
        $this->Err = $this->Err.$this->Msg->show_text('MSG_FLD_NAME_EMPTY').'<br>';
    }
    //echo '<br>$this->Err='.$this->Err.' $this->Msg->table='.$this->Msg->table;
    return $this->Err;
   } //end of fuinction CheckFields()         
   

  
   /**
    * response::save()
    * Store data to the table
    * @author Yaroslav
    * @return
    */
   function save()
   {
    $ln_sys = new SysLang();
    $this->Form->Hidden( 'display', $this->display );
    $this->Form->Hidden( 'start', $this->start );
    $this->Form->Hidden( 'sort', $this->sort );
    $this->Form->Hidden( 'srch', $this->srch ); 
    $this->Form->Hidden( 'srch', $this->srch2 ); 
    $this->Form->Hidden( 'fltr', $this->fltr );
    $this->Form->Hidden( 'fltr2', $this->fltr2 ); 
    $this->Form->Hidden( 'fln', $this->fln );       
   
    $q = "SELECT * FROM ".TblModresponse." WHERE `id`='".$this->id."'";
    $res = $this->Rights->Query( $q, $this->user_id, $this->module );
    if( !$res OR !$this->Rights->result) return false;
    $rows = $this->Rights->db_GetNumRows();
    //echo '<br>$q='.$q.'$rows='.$rows;
    if( $rows>0 )   //--- update
    {
         $row = $this->Rights->db_FetchAssoc();
         //Delete old image
         //echo '<br>$row[img]='.$row['img'].' $this->img='.$this->img;
         if ( !empty($row['img']) AND $row['img']!=$this->img) {
            $this->DelItemImage($row['img']);
         }
        
         $q = "UPDATE `".TblModresponse."` SET
              `group_d`='".$this->group_d."',
              `url`='".$this->url."',
              `img` = '".$this->img."',
              `img2` = '".$this->img2."',
              `visible`='".$this->visible."',
              `move`='".$this->move."'
              WHERE `id` = '".$this->id."'
              ";
         $res = $this->Rights->Query( $q, $this->user_id, $this->module );
         //echo '<br>'.$q.'br/> $res='.$res.' $this->Rights->result='.$this->Rights->result;
         if( !$res OR !$this->Rights->result) return false;
    }
    else   //--- insert
    {
        $q="SELECT MAX(`move`) FROM `".TblModresponse."` WHERE 1";
        $res = $this->Rights->Query( $q, $this->user_id, $this->module );
        $row = $this->Rights->db_FetchAssoc();
        $maxx = $row['MAX(`move`)']+1;  //add link with position auto_incremental
        
        $q = "INSERT INTO `".TblModresponse."` SET
              `group_d`='".$this->group_d."',
              `url`='".$this->url."',
              `img` = '".$this->img."',
              `img2` = '".$this->img2."',
              `visible`='".$this->visible."',
              `move`='".$maxx."'
             ";
        $res = $this->Rights->Query( $q, $this->user_id, $this->module );
        //echo '<br>'.$q.' <br/>$res='.$res.' $this->Rights->result='.$this->Rights->result;
        if( !$res OR !$this->Rights->result) return false;
    }
    
    if ( empty($this->id) ){
      $this->id = $this->Rights->db_GetInsertID();
    }
    
     $q="SELECT * from `".TblModresponseTxt."` where `cod`='".$this->id."'";
     $res = $this->Rights->Query( $q, $this->user_id, $this->module );
     if( !$this->Rights->result ) return false;
     $rows = $this->Rights->db_GetNumRows();
     $_txst= array();
     for($i=0; $i<$rows;$i++)
     {
        $row = $this->Rights->db_FetchAssoc();
        $_txst[$row['lang_id']]='1';
     }
     $ln_arr = $ln_sys->LangArray( _LANG_ID );
     while( $el = each( $ln_arr ) )
     {

       if(isset($this->name[ $el['key'] ])) 
        $name_ = addslashes( strip_tags(trim($this->name[ $el['key'] ])) );
       else $name_= NULL;
       
       if(isset($this->short[ $el['key'] ])) $short_ = addslashes( /*strip_tags(*/trim($this->short[ $el['key'] ])/*)*/ );
       else $short_= NULL;

       if(isset($this->full[ $el['key'] ])) $full_ = addslashes( /*strip_tags(*/trim($this->full[ $el['key'] ])/*)*/ );
       else $full_= NULL;

       if(isset($this->title[ $el['key'] ])) $titles = addslashes( strip_tags(trim($this->title[ $el['key'] ])) );
       else $titles= NULL;
       
       if(isset($this->description[ $el['key'] ])) $descriptions = addslashes( strip_tags(trim($this->description[ $el['key'] ])) );
       else $descriptions= NULL;
       
       if(isset($this->keywords[ $el['key'] ])) $keywords = addslashes( strip_tags(trim($this->keywords[ $el['key'] ])) );
       else $keywords= NULL;

       $lang_id = $el['key'];
       if (isset($_txst[$lang_id]))
       {
            $q="UPDATE `".TblModresponseTxt."` set
              `name`='".$name_."',
              `title`='".$titles."',
              `keywords`='".$keywords."',
              `description`='".$descriptions."',
              `short`='".$short_."',
              `full`='".$full_."'
              WHERE `lang_id`='$lang_id' and `cod`='$this->id'";
              $res = $this->Rights->Query( $q, $this->user_id, $this->module );
                  //echo '<br/>'.$q.'<br/> res='.$res.' $this->Rights->result='.$this->Rights->result; 
              if( !$res ) return false; 
              if( !$this->Rights->result ) return false; 
          }
        else
        {
          $q="insert into `".TblModresponseTxt."`  set
              `name`='".$name_."',
              `title`='".$titles."',
              `keywords`='".$keywords."',
              `description`='".$descriptions."',
              `short`='".$short_."',
              `full`='".$full_."',
              `lang_id`='$lang_id',`cod`='$this->id'";
          $res = $this->Rights->Query( $q, $this->user_id, $this->module );
          //echo '<br>'.$q.' <br/>res='.$res.' $this->Rights->result='.$this->Rights->result; 
          if( !$this->Rights->result) return false;
          }
      

     } //--- end while

    //set link
    $res = $this->SaveLinkFromBackEnd($this->id, $this->translit, $this->name, $this->group_d);
    if( !$res ) return false;
    return true;
    
   } //end of fuinction save()

   // ================================================================================================
   // Function : SaveLinkFromBackEnd()
   // Version : 1.0.0
   // Date : 22.09.2009 
   //
   // Parms :
   // Returns :      true,false / Void
   // Description :  set link to comment
   // ================================================================================================
   // Programmer :  Igor Trokhymchuk
   // Date : 22.09.2009 
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================   
   function SaveLinkFromBackEnd($comment_id, $translit_arr, $name_arr, $group_id)
   {
       $ln_sys = new SysLang();
       $ln_arr = $ln_sys->LangArray( _LANG_ID );
       if ( empty($ln_arr) ) $ln_arr[1]='';
       while( $el = each( $ln_arr ) ){
           $lang_id = $el['key'];
           //if not exist translit and name then set trnslit to nothing
           if( !isset($translit_arr[$lang_id]) AND !isset($name_arr[$lang_id]) ){
               $translit = '';
               continue;
           }
           
           $translit = strip_tags(trim($translit_arr[$lang_id]));
           $name = strip_tags(trim($name_arr[$lang_id]));
           if( empty($translit) AND !empty($name) ){ 
               $Crypt = new Crypt();
               $translit = $Crypt->GetTranslitStr($name);
           }
           //if not exist trasnlit for comment on language $lang_id than don't save it
           if( empty($translit)) continue;
           
           //check if already exist same translit in others response
           $q="SELECT `".TblModresponseLinks."`.*
               FROM `".TblModresponseLinks."`, `".TblModresponse."`
               WHERE `".TblModresponseLinks."`.`link`='".$translit."'
               AND `".TblModresponseLinks."`.`comment_id`=`".TblModresponse."`.`id`
               AND `".TblModresponse."`.`group_d`='".$group_id."'
               AND `".TblModresponseLinks."`.`lang_id`='".$lang_id."'
               AND `".TblModresponseLinks."`.`comment_id`!='".$comment_id."'";
           $res = $this->Rights->db_Query( $q );                          
           if(!$res OR !$this->Rights->result ) return false;
           $rows = $this->Rights->db_GetNumRows();
           //echo '<br>$q='.$q.' $res='.$res.' $rows='.$rows.' $this->Rights->result='.$this->Rights->result; 
           //if exist same translit, then ckeck id of comment
           if( $rows>0 ){
               //$row = $this->Rights->db_FetchAssoc();
               //if id of comment not equil with $comment_id than create uniqe translit  
               //if( $row['comment_id']!=$comment_id ) 
               $translit = $translit.'-'.$comment_id;
           }
           //echo '<br>$lang_id='.$lang_id.' $translit='.$translit.' $name='.$name.' $rows='.$rows.' $comment_id='.$comment_id;           
           
           //check if already exist translit for comment with id $comment_id on language $lang_id
           $q="SELECT `".TblModresponseLinks."`.* FROM `".TblModresponseLinks."` WHERE `".TblModresponseLinks."`.`comment_id`='".$comment_id."' AND `".TblModresponseLinks."`.`lang_id`='".$lang_id."'";
           $res = $this->Rights->db_Query( $q );
           $row = $this->Rights->db_FetchAssoc();
           //echo '<br>$q='.$q.' $res='.$res.' $row[link]='.$row['link'];
           //if translit already exist for comment with id $comment_id on language $lang_id and translit differ from $translit then update it.
           if(isset($row['link'])){
               if($row['link']!=$translit){
                   $q = "UPDATE `".TblModresponseLinks."` SET `link`='".$translit."'
                         WHERE `comment_id`='".$comment_id."' AND `lang_id`='".$lang_id."'
                        ";
               }
           }
           //but if not exist translit for $comment_id then create new and insert translit for new comment
           else {
                $q = "INSERT INTO `".TblModresponseLinks."` SET
                     `comment_id`='".$comment_id."',
                     `lang_id`='".$lang_id."',
                     `link`='".$translit."'
                    ";
           }
           $res = $this->Rights->db_Query($q);
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res OR !$this->Rights->result) return false;
       }
       return true;
   }// end of functin SaveLinkFromBackEnd()
   
   
   /**
    * response::del()
    * Delete data from the Dbase
    * @author Yaroslav 
    * @param mixed $id_del
    * @return $del - count of deleted items
    */
   function del( $id_del )
   {
       $kol = count( $id_del );
       $del = 0;
       for( $i=0; $i<$kol; $i++ )
       {
        $u = $id_del[$i];
        $q = "select * from ".TblModresponse." where id='$u'";
        $res = $this->Rights->Query( $q, $this->user_id, $this->module );
        $row = $this->Rights->db_FetchAssoc();
        if ( !empty($row['img']) ){
            if ( !$this->DelItemImage($row['img']) ) return false; 
        }
        if ( !empty($row['img2']) ){
            if ( !$this->DelItemImage(NULL, $row['img2']) ) return false; 
        }
        $q="DELETE FROM `".TblModresponse."` WHERE `id`='".$u."'";
        $res = $this->Rights->Query( $q, $this->user_id, $this->module );
        if (!$res) return false;
        
        //delete comment data
        $q="DELETE FROM `".TblModresponseTxt."` WHERE `cod`='".$u."'";
        $res = $this->Rights->Query( $q, $this->user_id, $this->module );
        if (!$res) return false;
        
        //delete link of comment
        $q="DELETE FROM `".TblModresponseLinks."` WHERE `comment_id`='".$u."'";
        $res = $this->Rights->Query( $q, $this->user_id, $this->module );
        if ( $res )
         $del=$del+1;
        else
         return false;
       }
     return $del;
   } //end of fuinction del()
   
   
   // ================================================================================================
   // Function : DelItemImage
   // Date : 05.12.2006 
   // Parms :   $img   / name of the image
   // Returns : true,false / Void
   // Description :  Remove iamge from table and from the disk
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function DelItemImage($img=NULL, $img2=NULL)
   {       
       if( !empty($img)) $q = "SELECT `img` FROM `".TblModresponse."` WHERE `img`='".$img."'";
       if( !empty($img2)) $q = "SELECT `img2` as `img` FROM `".TblModresponse."` WHERE `img2`='".$img2."'";
       $res = $this->Rights->Query( $q, $this->user_id, $this->module );
       //echo '<br>$q='.$q.' $res='.$res.' $this->Rights->result='.$this->Rights->result;
       if( !$this->Rights->result ) return false;
       $rows = $this->Rights->db_GetNumRows();
       if ($rows == 0) return false;
       $row = $this->Rights->db_FetchAssoc(); 
       //echo '<br>$row'; print_r($row);
       
       
       $path = response_Img_Full_Path;
       $path_file = $path.'/'.$row['img'];
       //echo '<br>$path='.$path.'<br>$path_file='.$path_file;
       // delete file which store in the database
       if (file_exists($path_file)) {
          $res = unlink ($path_file);
          if( !$res ) return false;
       }

       //echo '<br> $path='.$path;
       $handle = @opendir($path);
       //echo '<br> $handle='.$handle; 
       $cols_files = 0;
       while ( ($file = readdir($handle)) !==false ) {
           //echo '<br> $file='.$file;
           $mas_file=explode(".",$file);
           $mas_img_name=explode(".",$row['img']);
           if ( strstr($mas_file[0], $mas_img_name[0].SPR_ADDITIONAL_FILES_TEXT) and $mas_file[1]==$mas_img_name[1] ) {
              $res = unlink ($path.'/'.$file);
              if( !$res ) return false;                    
           }
           if ($file == "." || $file == ".." ) {
               $cols_files++;
           }
       }
       //if ($cols_files==2) rmdir($path);
       closedir($handle);           

       if( !empty($img)) $q = "UPDATE `".TblModresponse."` SET `img`='' WHERE `img`='".$img."'";
       if( !empty($img2)) $q = "UPDATE `".TblModresponse."` SET `img2`='' WHERE `img2`='".$img2."'";
       $res = $this->Rights->Query( $q, $this->user_id, $this->module );
       //echo '<br>$q='.$q.' $res='.$res.' $this->Rights->result='.$this->Rights->result;
       if( !$this->Rights->result ) return false;
       
       return true;                        
  } //end of function DelItemImage()        
   
   
    /**
     * response::up()
     * Up position
     * @author Yaroslav
     * @param mixed $table
     * @return
     */
    function up($table)
    {
     $q="select * from `$table` where `move`='$this->move'";
     $res = $this->Rights->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Rights->result='.$this->Rights->result;
     if( !$res )return false;
     $rows = $this->Rights->db_GetNumRows();
     $row = $this->Rights->db_FetchAssoc();
     $move_down = $row['move'];
     $id_down = $row['id'];


     $q="select * from `$table` where `move`<'$this->move' order by `move` desc";
     $res = $this->Rights->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Rights->result='.$this->Rights->result;
     if( !$res )return false;
     $rows = $this->Rights->db_GetNumRows();
     $row = $this->Rights->db_FetchAssoc();
     $move_up = $row['move'];
     $id_up = $row['id'];

     //echo '<br> $move_down='.$move_down.' $move_up ='.$move_up;
     if( $move_down!=0 AND $move_up!=0 )
     {
     $q="update `$table` set
         `move`='$move_down' where `id`='$id_up'";
     $res = $this->Rights->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Rights->result='.$this->Rights->result; 
     
     $q="update `$table` set
         `move`='$move_up' where `id`='$id_down'";
     $res = $this->Rights->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Rights->result='.$this->Rights->result; 
     
     }
    } // end of function up()


    /**
     * response::down()
     * Down position
     * @author Yaroslav
     * @param mixed $table
     * @return
     */
    function down($table)
    {
     $q="select * from `$table` where `move`='$this->move'";
     $res = $this->Rights->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Rights->result='.$this->Rights->result;
     if( !$res )return false;
     $rows = $this->Rights->db_GetNumRows();
     $row = $this->Rights->db_FetchAssoc();
     $move_up = $row['move'];
     $id_up = $row['id'];


     $q="select * from `$table` where `move`>'$this->move' order by `move` asc";
     $res = $this->Rights->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Rights->result='.$this->Rights->result;
     if( !$res )return false;
     $rows = $this->Rights->db_GetNumRows();
     $row = $this->Rights->db_FetchAssoc();
     $move_down = $row['move'];
     $id_down = $row['id'];

     if( $move_down!=0 AND $move_up!=0 )
     {
     $q="update `$table` set
         `move`='$move_down' where `id`='$id_up'";
     $res = $this->Rights->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Rights->result='.$this->Rights->result;

     $q="update `$table` set
         `move`='$move_up' where `id`='$id_down'";
     $res = $this->Rights->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Rights->result='.$this->Rights->result;
     }
    } // end of function down()       
   
   
   /**
    * response::ShowErrBackEnd()
    * 
    * @return void
    */
   function ShowErrBackEnd()
   {
     if ($this->Err){
       echo '
        <table border=0 cellspacing=0 cellpadding=0 class="err" align="center">
         <tr><td align="left">'.$this->Err.'</td></tr>
        </table>';
     }
   } //end of fuinction ShowErrBackEnd()    
   
   
   /* function GetImgTitle( $img )
    {
        $tmp_db = new DB();
        $q = "SELECT * FROM `".TblModresponse."` WHERE `img`='$img'";
        $res = $tmp_db->db_Query($q);
        //echo '<br>q='.$q.' res='.$res.' $tmp_db->result='.$tmp_db->result;
        if ( !$res OR !$tmp_db->result ) return false;
        $row = $tmp_db->db_FetchAssoc();
        return $this->Spr->GetNameByCod(TblModresponseSprName, $row['id']);
    } //end of function GetImgTitle()*/        
   
    // ================================================================================================
    // Function : ShowImage
    // Version : 1.0.0
    // Date : 05.12.2006 
    //
    // Parms :  $img - path of the picture
    //          $size -  Can be "size_auto" or  "size_width" or "size_height"
    //          $quality - quality of the image
    //          $wtm - make watermark or not. Can be "txt" or "img"
    //          $parameters - other parameters for TAG <img> like border
    // Returns : $res / Void
    // Description : Show images for dealers
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 05.12.2006   
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowImage($img = NULL, $size = NULL, $quality = 85, $wtm = NULL, $parameters = NULL)
    {
        $size_auto = NULL;
        $size_width = NULL;
        $size_height = NULL;
        $alt = NULL;
        $title = NULL;

        $img_with_path = response_Img_Path.$img;
        $img_full_path = response_Img_Full_Path.$img;
       
        //$img_full_path = Spr_Img_Path.$spr.'/'.$img_with_path; // like z:/home/speakers/www/uploads/45/R1800TII_big.jpg
        //echo '<br> $img_full_path='.$img_full_path.'<br> $size_auto='.$size_auto;
        //if ( !file_exists($img_full_path) ) return false;

        $mas_img_name=explode(".",$img_with_path);
        
        if ( strstr($size,'size_width') ){ 
            $size_width = substr( $size, strrpos($size,'=')+1, strlen($size) );
            $img_name_new = $mas_img_name[0].SPR_ADDITIONAL_FILES_TEXT.'width_'.$size_width.'.'.$mas_img_name[1];
        }
        elseif ( strstr($size,'size_auto') ) {
            $size_auto = substr( $size, strrpos($size,'=')+1, strlen($size) );
            $img_name_new = $mas_img_name[0].SPR_ADDITIONAL_FILES_TEXT.'auto_'.$size_auto.'.'.$mas_img_name[1];
        }
        elseif ( strstr($size,'size_height') ) {
            $size_height = substr( $size, strrpos($size,'=')+1, strlen($size) );
            $img_name_new = $mas_img_name[0].SPR_ADDITIONAL_FILES_TEXT.'height_'.$size_height.'.'.$mas_img_name[1];
        }
        elseif(empty($size)) $img_name_new = $mas_img_name[0].'.'.$mas_img_name[1];
        //echo '$img_name_new='.$img_name_new;
        $img_full_path_new = SITE_PATH.$img_name_new; 
        //if exist local small version of the image then use it
        if( file_exists($img_full_path_new)){
            //echo 'exist';
            //echo '<br>$settings_img_path='.$settings_img_path.' $img_full_path='.$img_full_path;
            if ( !strstr($parameters, 'alt') ) $alt = '';//$this->GetImgTitle($img);
            if ( !strstr($parameters, 'title') ) $title = '';//$this->GetImgTitle($img);
            if ( !strstr($parameters, 'alt') )  $parameters = $parameters.' alt="'.$alt.'"';
            if ( !strstr($parameters, 'title') ) $parameters = $parameters.' title=" '.$title.' "';        
            $str = '<img src="'.$img_name_new.'" '.$parameters.' />';
        }
        //else use original image on the server SITE_PATH and make small version on local server
        else {
            //echo 'Not  exist';
            //$img_full_path = SITE_PATH.$img_with_path; // like z:/home/speakers/www/uploads/45/R1800TII_big.jpg
            //echo '<br> $img_full_path='.$img_full_path.'<br> $size_auto='.$size_auto;
            if ( !file_exists($img_full_path) ) return false;     
     
            $thumb = new Thumbnail($img_full_path);
            //echo '<br>$thumb->img[x_thumb]='.$thumb->img['x_thumb'].' $thumb->img[y_thumb]='.$thumb->img['y_thumb'];
            $src_x = $thumb->img['x_thumb'];
            $src_y = $thumb->img['y_thumb'];
            if ( !empty($size_width ) and empty($size_height) ) $thumb->size_width($size_width);
            if ( !empty($size_height) and empty($size_width) ) $thumb->size_height($size_height);
            if ( !empty($size_width) and !empty($size_height) ) $thumb->size($size_width,$size_height); 
            if ( !$size_width and !$size_height and $size_auto ) $thumb->size_auto($size_auto);                    // [OPTIONAL] set the biggest width and height for thumbnail
            //echo '<br>$thumb->img[x_thumb]='.$thumb->img['x_thumb'].' $thumb->img[y_thumb]='.$thumb->img['y_thumb'];
            
            //if original image smaller than thumbnail then use original image and don't create thumbnail
            if($thumb->img['x_thumb']>=$src_x OR $thumb->img['y_thumb']>=$src_y){
                $img_full_path = $img_with_path; //$settings_img_path.'/'.$img_name;
                //echo '<br>$settings_img_path='.$settings_img_path.' $img_full_path='.$img_full_path;
                if ( !strstr($parameters, 'alt') ) $alt = '';//$this->GetImgTitle($img);
                if ( !strstr($parameters, 'title') ) $title = '';//$this->GetImgTitle($img);
                if ( !strstr($parameters, 'alt') )  $parameters = $parameters.' alt="'.$alt.'"';
                if ( !strstr($parameters, 'title') ) $parameters = $parameters.' title=" '.$title.' "';        
                $str = '<img src="'.$img_full_path.'" '.$parameters.' />';
            }
            else{
                $thumb->quality=$quality;                  //default 75 , only for JPG format 
                //echo '<br>$wtm='.$wtm;
                if ( $wtm == 'img' ) {
                    $thumb->img_watermark = SITE_PATH.'/images/design/m01.png';        // [OPTIONAL] set watermark source file, only PNG format [RECOMENDED ONLY WITH GD 2 ]
                    $thumb->img_watermark_Valing='CENTER';           // [OPTIONAL] set watermark vertical position, TOP | CENTER | BOTTOM
                    $thumb->img_watermark_Haling='CENTER';           // [OPTIONAL] set watermark horizonatal position, LEFT | CENTER | RIGHT
                }
                if ( $wtm == 'txt' ) {
                    if ( defined('WATERMARK_TEXT') ) $thumb->txt_watermark=SPR_WATERMARK_TEXT;        // [OPTIONAL] set watermark text [RECOMENDED ONLY WITH GD 2 ]
                    else $thumb->txt_watermark='';
                    $thumb->txt_watermark_color='000000';        // [OPTIONAL] set watermark text color , RGB Hexadecimal[RECOMENDED ONLY WITH GD 2 ]
                    $thumb->txt_watermark_font=5;                // [OPTIONAL] set watermark text font: 1,2,3,4,5
                    $thumb->txt_watermark_Valing='TOP';           // [OPTIONAL] set watermark text vertical position, TOP | CENTER | BOTTOM
                    $thumb->txt_watermark_Haling='LEFT';       // [OPTIONAL] set watermark text horizonatal position, LEFT | CENTER | RIGHT
                    $thumb->txt_watermark_Hmargin=10;          // [OPTIONAL] set watermark text horizonatal margin in pixels
                    $thumb->txt_watermark_Vmargin=10;           // [OPTIONAL] set watermark text vertical margin in pixels     
                }
                
                $mas_img_name=explode(".",$img_with_path);
                if(!empty($size_width )) 
                        $img_name_new = $mas_img_name[0].SPR_ADDITIONAL_FILES_TEXT.'width_'.$size_width.'.'.$mas_img_name[1];
                elseif(!empty($size_auto )) 
                        $img_name_new = $mas_img_name[0].SPR_ADDITIONAL_FILES_TEXT.'auto_'.$size_auto.'.'.$mas_img_name[1];
                elseif(!empty($size_height )) 
                        $img_name_new = $mas_img_name[0].SPR_ADDITIONAL_FILES_TEXT.'height_'.$size_height.'.'.$mas_img_name[1];
                $img_full_path_new = SITE_PATH.$img_name_new; 
                $img_src = $img_name_new;
                //$uploaddir = substr($img_with_path, 0, strrpos($img_with_path,'/'));
                $uploaddir = SITE_PATH.substr($img_with_path, 0, strrpos($img_with_path,'/')).'/';
                
                //echo '<br>$img_name_new='.$img_name_new;  
                //echo '<br>$img_full_path_new='.$img_full_path_new;
                //echo '<br>$img_src='.$img_src;
                //echo '<br>$uploaddir='.$uploaddir;
                
                if ( !strstr($parameters, 'alt') )  $parameters = $parameters.' alt="'.$alt.'"';
                if ( !strstr($parameters, 'title') ) $parameters = $parameters.' title=" '.$title.' "';

                //echo '<br>$uploaddir='.$uploaddir; 
                if ( !file_exists($img_full_path_new) ) {
                    if( !file_exists ($uploaddir) ) mkdir($uploaddir,0777);
                    if( file_exists($uploaddir) ) @chmod($uploaddir,0777);
                    $thumb->process();       // generate image  
                    //make new image like R1800TII_big.jpg -> R1800TII_big_autozoom_100x84.jpg 
                    $thumb->save($img_full_path_new);
                    @chmod($uploaddir,0755);
                    $params = "img=".$img."&".$size;
                }
                $str = '<img src="'.$img_src.'" '.$parameters.' />';
            }//end else  
        }//end else  
        return $str;
    } // end of function ShowImage()       

    
    /**
     * response::GetIdGroupByTranslit()
     * Return id of response group by it translit
     * @author Yaroslav
     * @param mixed $translit - $translit of the comment group
     * @param string $lang_id
     * @return
     */
    function GetIdGroupByTranslit($translit, $lang_id='')
    { 
        $dbr = new DB();
        if( empty($lang_id)) $lang_id = $this->lang_id;
        $q="SELECT `cod` FROM `".TblModresponseSprGroup."` WHERE `translit`='".$translit."' AND `lang_id`='".$lang_id."'";
        $res = $dbr->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $dbr->result='.$dbr->result;
        if( !$res OR ! $dbr->result) return false;
        //$rows = $dbr->db_GetNumRows();
        $row=$dbr->db_FetchAssoc();                       
        return $row['cod'];
    } // end of function GetIdGroupByTranslit() 


    // ================================================================================================
    // Function : GetIdCommentByTranslit
    // Version : 1.0.0
    // Date :    28.09.2009
    // Parms :       $translit  - $translit of the comment group
    // Returns :     
    // Description : return id of response group by it translit
    // ================================================================================================
    // Programmer : Ihor Trokhimchuk  
    // Date : 28.09.2009
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function GetIdCommentByTranslit($translit, $lang_id='')
    { 
        $dbr = new DB();
        if( empty($lang_id)) $lang_id = $this->lang_id;
        $q="SELECT `comment_id` FROM `".TblModresponseLinks."` WHERE `link`='".$translit."' AND `lang_id`='".$lang_id."'";
        $res = $dbr->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $dbr->result='.$dbr->result;
        if( !$res OR ! $dbr->result) return false;
        //$rows = $dbr->db_GetNumRows();
        $row=$dbr->db_FetchAssoc();                       
        return $row['comment_id'];
    } // end of function GetIdCommentByTranslit() 
 
 
    // ================================================================================================
    // Function : GetLink
    // Version : 1.0.0
    // Date :    28.09.2009
    // Parms :       $id  - id of the comment
    //               $lang_id  - language id
    // Returns :     $out_arr
    // Description : return link of page for comment
    // ================================================================================================
    // Programmer : Ihor Trokhimchuk  
    // Date : 28.09.2009  
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function GetLink($id, $lang_id)
    { 
        $dbr = new DB();
        $q="SELECT `link` FROM `".TblModresponseLinks."` WHERE `comment_id`='".$id."' AND `lang_id`='".$lang_id."'";
        $res = $dbr->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $dbr->result='.$dbr->result;
        if( !$res OR ! $dbr->result) return false;
        //$rows = $dbr->db_GetNumRows();
        $row=$dbr->db_FetchAssoc();                       
        return $row['link'];
    } // end of function GetLink()
    
    
    // ================================================================================================
    // Function : GetURLCommentByTranslit
    // Version : 1.0.0
    // Date :    30.09.2009
    // Parms :       $translit  - $translit of the comment group
    // Returns :     
    // Description : return url of response by it translit
    // ================================================================================================
    // Programmer : Ihor Trokhimchuk  
    // Date : 30.09.2009
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function GetURLCommentByTranslit($translit, $lang_id='')
    { 
        $dbr = new DB();
        if( empty($lang_id)) $lang_id = $this->lang_id;
        $q="SELECT `".TblModresponse."`.`url`
            FROM `".TblModresponse."`, `".TblModresponseLinks."`
            WHERE `".TblModresponseLinks."`.`link`='".$translit."'
            AND `".TblModresponseLinks."`.`lang_id`='".$lang_id."'
            AND `".TblModresponseLinks."`.`comment_id`=`".TblModresponse."`.`id`";
        $res = $dbr->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $dbr->result='.$dbr->result;
        if( !$res OR ! $dbr->result) return false;
        //$rows = $dbr->db_GetNumRows();
        $row=$dbr->db_FetchAssoc();                       
        return $row['url'];
    } // end of function GetURLCommentByTranslit()      
       
 } // End of class response
