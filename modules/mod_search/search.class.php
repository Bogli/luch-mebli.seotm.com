<?php
include_once( $_SERVER['DOCUMENT_ROOT'].'/modules/mod_search/search.defines.php' );
	
class Search
{
        var $user_id = NULL;
       var $module = NULL;
       var $Err=NULL;

       var $sort = NULL;
       var $display = 20;
       var $start = 0;
       var $fln = NULL;
       var $width = 500;
       var $spr = NULL;
       var $srch = NULL;

       var $db = NULL;
       var $Msg = NULL;
       var $Right = NULL;
       var $Form = NULL;
       var $Spr = NULL;

  
       var $date = NULL;
       var $time = NULL;
       var $query = NULL;
       var $ip = NULL;
       var $result = NULL;
       

function Search($user_id=NULL, $module=NULL, $display=NULL, $sort=NULL, $start=NULL, $width = NULL) {
                //Check if Constants are overrulled
                ( $user_id   !="" ? $this->user_id = $user_id  : $this->user_id = NULL );
                ( $module   !="" ? $this->module  = $module   : $this->module  = NULL );
                ( $display  !="" ? $this->display = 60  : $this->display = 60   );
                ( $sort     !="" ? $this->sort    = $sort     : $this->sort    = NULL );
                ( $start    !="" ? $this->start   = $start    : $this->start   = 0    );
                ( $width    !="" ? $this->width   = $width    : $this->width   = 750  );

                if (empty($this->db)) $this->db = new DB();
                if (empty($this->Right)) $this->Right = new Rights($this->user_id, $this->module);
                if (empty($this->Msg)) $this->Msg = new ShowMsg();
                $this->Msg->SetShowTable(TblModSearchSprTxt);
                if (empty($this->Form)) $this->Form = new Form('form_mod_links_set');
                if (empty($this->Spr)) $this->Spr = new  SysSpr();
                $this->ip = $_SERVER['REMOTE_ADDR'];    
       }        
// ================================================================================================
// Function : show()
// Version : 1.0.0
// Date : 11.02.2005
// Parms : $id, $cod, $lang_id, $id_category, $subject_, $question_, $answer_, $id_rel, $status, $display
// Returns : true,false / Void
// Description : Store data to the table faq
// ================================================================================================
// Programmer : Andriy Lykhodid
// Date : 11.02.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================
 function show()
       {
        $script = 'module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&sort='.$this->sort.'&fltr='.$this->fltr.'&fln='.$this->fln;
        $script = $_SERVER['PHP_SELF']."?$script";

        if( !$this->sort ) $this->sort='id';
		if($this->sort=='result') $this->sort='`result` desc';
        //if( strstr( $this->sort, 'seria' ) )$this->sort = $this->sort.' desc';
        $q = "SELECT * FROM ".TblModSearchResult." where 1 order by ".$this->sort."";
        //if( $this->srch ) $q = $q." and (name LIKE '%$this->srch%' OR email LIKE '%$this->srch%')";
        if( $this->fltr ) $q = $q." and $this->fltr";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        //echo '<br>$q='.$q.' $res='.$res.'$this->Right->result='.$this->Right->result.' $this->user_id='.$this->user_id;
        if( !$res )return false;
        $rows = $this->Right->db_GetNumRows();

        /* Write Form Header */
        $this->Form->WriteHeader( $script );

        /* Write Table Part */
        AdminHTML::TablePartH();

        /* Write Links on Pages */
        echo '<TR><TD COLSPAN=17>';
        $script1 = 'module='.$this->module.'&fltr='.$this->fltr;
        $script1 = $_SERVER['PHP_SELF']."?$script1";
		if( !$this->display ) $this->display = 20;
        //$this->Form->WriteLinkPages( $script1, $rows, $this->display, $this->start, $this->sort );
		$this->Form->WriteLinkPages( $script1.'&fltr='.$this->fltr, $rows, $this->display, $this->start, $this->sort );

        echo '<TR><TD COLSPAN=5>';
        $this->Form->WriteTopPanel( $script );

        echo '<td colspan=5>';
        echo $this->Form->TextBox('srch', $this->srch, 25);
        echo '<input type=submit value='.$this->Msg->show_text('_BUTTON_SEARCH',TblSysTxt).'>';

        /*
        echo '<td><td><td><td><td colspan=2>';
        $this->Form->WriteSelectLangChange( $script, $this->fln);
        */

        $script2 = 'module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&task=show&fltr='.$this->fltr;
        $script2 = $_SERVER['PHP_SELF']."?$script2";
       ?>
        <TR>
        <td class="THead">*</Th>
        <td class="THead"><A HREF=<?=$script2?>&sort=id><?=$this->Msg->show_text('FLD_ID')?></A></Th>
        <td class="THead"><A HREF=<?=$script2?>&sort=query><?=$this->Msg->show_text('FLD_QUERY')?></A></Th>
        <td class="THead"><A HREF=<?=$script2?>&sort=ip><?=$this->Msg->show_text('FLD_IP')?></A></Th>
        <td class="THead"><A HREF=<?=$script2?>&sort=date><?=$this->Msg->show_text('FLD_DATE')?></A></Th>
        <td class="THead"><A HREF=<?=$script2?>&sort=time><?=$this->Msg->show_text('FLD_TIME')?></A></Th>
        
        <td class="THead"><A HREF=<?=$script2?>&sort=result><?=$this->Msg->show_text('FLD_RESULT')?></A></Th>
        
        <?

        $up = 0;
        $down = 0;
        $a = $rows;
        $j = 0;
        $row_arr = NULL;
        for( $i = 0; $i < $rows; $i++ )
        {
          $row = $this->Right->db_FetchAssoc();
          if( $i >= $this->start && $i < ( $this->start+$this->display ) )
          {
            $row_arr[$j] = $row;
            $j = $j + 1;
          }
        }

        $style1 = 'TR1';
        $style2 = 'TR2';
        for( $i = 0; $i < count( $row_arr ); $i++ )
        {
          $row = $row_arr[$i];

          if ( (float)$i/2 == round( $i/2 ) )
          {
           echo '<TR CLASS="'.$style1.'">';
          }
          else echo '<TR CLASS="'.$style2.'">';

          echo '<TD>';
          $this->Form->CheckBox( "id_del[]", $row['id'] );

          echo '<TD>';
          $this->Form->Link( $script."&task=edit&id=".$row['id'], stripslashes( $row['id'] ) );

          echo '<TD align=center>';
          if( trim( $row['query'] )!='' ) echo $row['query'];

          echo '<TD align=center>';
          if( trim( $row['ip'] )!='' ) echo $row['ip'];

          echo '<TD align=center>';
          if( trim( $row['date'] )!='' ) echo $row['date'];

          echo '<TD align=center>';
          if( trim( $row['time'] )!='' ) echo $row['time'];

          echo '<TD align=center>';
          if( trim($row['result'])!='' ) echo $row['result'];

 } //-- end for

        AdminHTML::TablePartF();
        $this->Form->WriteFooter();
        return true;

       
} //end of fuinction show



// ================================================================================================
// Function : del()
// Version : 1.0.0
// Date : 06.01.2006
//
// Parms :
// Returns :      true,false / Void
// Description :  Remove data from the table
// ================================================================================================
// Programmer :  Igor Trokhymchuk
// Date : 06.01.2006
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

       function del( $id_del )
       {
           $kol = count( $id_del );
           $del = 0;
           for( $i=0; $i<$kol; $i++ )
           {
            $u = $id_del[$i];
            
            $q = "DELETE FROM `".TblModSearchResult."` WHERE id='$u'";
            $res = $this->Right->Query( $q, $this->user_id, $this->module );

            if ( $res )
            $del=$del+1;
            else
            return false;
           }
         return $del;
       } //end of fuinction del()
       
       
// ================================================================================================
// Function : save_search()
// Version : 1.0.0
// Date : 27.03.2008
//
// Parms :
// Returns :      true,false / Void
// Description :  
// ================================================================================================
// Programmer :  Alex Kerest
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================
function save_search(){
  $db = new DB();
    
  $q = "INSERT INTO `".TblModSearchResult."` SET 
  `query` = '".$this->query."',
  `ip` = '".$this->ip."',
  `date` = '".date("Y-m-d")."',
  `time` = '".date("G:i:s")."',
  `result` = '".$this->result."'
  ";
  $res = $db->db_Query( $q );
  //echo "<br> q = ".$q." res = ".$res;
               
} // end of function save_search


} //end of class moderation
?>