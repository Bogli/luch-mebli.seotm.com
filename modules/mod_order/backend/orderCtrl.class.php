<?php
// ================================================================================================
// System : CMS
// Module : orderCtrl.class.php
// Date : 06.06.2007
// Licensed To:
// Igor Trokhymchuk ihoru@mail.ru
// Purpose : Class definition for all actions with managment of orders
// ================================================================================================

include_once( $_SERVER['DOCUMENT_ROOT'].'/modules/mod_order/order.defines.php' );

// ================================================================================================
//    Class             : OrderCtrl
//    Date              : 06.06.2007
//    Constructor       : Yes
//    Parms             : session_id / session id
//                        usre_id    / UserID
//                        user_      /
//                        user_type  / id of group of user
//    Returns           : None
//    Description       : Class definition for all actions with managment of orders
//    Programmer        :  Igor Trokhymchuk
// ================================================================================================
 class OrderCtrl extends Order {

    public $user_id = NULL;
    public $module = NULL;
    public $Err=NULL;
    public $lang_id = NULL;

    public $sort = NULL;
    public $display = 20;
    public $start = 0;
    public $fln = NULL;
    public $width = 500;
    public $srch = NULL;
    public $fltr = NULL;
    public $fltr2 = NULL;
    public $script = NULL;

    public $db = NULL;
    public $Msg = NULL;
    public $Right = NULL;
    public $Form = NULL;
    public $Spr = NULL;
    public $Currency;

    public $date = NULL;
    public $quantity = NULL;
    public $buyer_is = NULL;
    public $status = NULL;
    public $prod_id = NULL;
    public $from = NULL;
    public $to = NULL;
    public $comment = NULL;
    public $sessid = NULL;
    public $property = NULL;
    public $id_cat = NULL;
    public $sum = NULL;
    public $cost_comment = NULL;
    public $Catalog = NULL;
    public $sysUser = NULL;
    public $user_fltr1 = NULL;
    public $user_fltr2 = NULL;
    public $user_fltr3 = NULL;


   // ================================================================================================
    //    Function          : OrderCtrl (Constructor)
    //    Date              : 21.03.2006
    //    Parms             : usre_id   / User ID
    //                        module    / module ID
    //                        sort      / field by whith data will be sorted
    //                        display   / count of records for show
    //                        start     / first records for show
    //                        width     / width of the table in with all data show
    //    Returns           : Error Indicator
    //
   //    Description       : Opens and selects a dabase
    // ================================================================================================
    function __construct($user_id = NULL, $module = NULL, $display = NULL, $sort = NULL, $start = NULL, $width = NULL, $fltr = NULL) {
        //Check if Constants are overrulled
        ( $user_id != "" ? $this->user_id = $user_id : $this->user_id = NULL );
        ( $module != "" ? $this->module = $module : $this->module = NULL );
        ( $display != "" ? $this->display = $display : $this->display = 20 );
        ( $sort != "" ? $this->sort = $sort : $this->sort = NULL );
        ( $start != "" ? $this->start = $start : $this->start = 0 );
        ( $width != "" ? $this->width = $width : $this->width = 750 );

        $this->lang_id = _LANG_ID;

        if (empty($this->db))
            $this->db = DBs::getInstance();
        if (empty($this->Msg))
            $this->Msg = &check_init('ShowMsg', 'ShowMsg');
        //$this->Msg->SetShowTable(TblModOrderSprTxt);
        if (empty($this->Form))
            $this->Form = &check_init('form_mod_order', 'Form', '"form_mod_order"');
        if (empty($this->Right))
            $this->Right = &check_init('RightsOrder', 'Rights', '"' . $this->user_id . '", "' . $this->module . '"');
        if (empty($this->Spr))
            $this->Spr = &check_init('SysSpr', 'SysSpr', '"' . $this->user_id . '", "' . $this->module . '"');

        if (empty($this->Catalog))
            $this->Catalog = &check_init('Catalog', 'Catalog');
        if (empty($this->User))
            $this->User = &check_init('User', 'User');
        if (empty($this->sysUser))
            $this->sysUser = &check_init('sysUser', 'sysUser');

        $this->AddTbl();
        if (empty($this->multi))
            $this->multi = &check_init_txt('TblBackMulti', TblBackMulti);
        if (empty($this->Currency))
            $this->Currency = &check_init('SystemCurrencies', 'SystemCurrencies');
        $this->Currency->defCurrencyData = $this->Currency->GetDefaultData();
        $this->Currency->GetShortNamesInArray('back');

        $this->statuses['1'] = $this->multi['FLD_TXT_POLUCHENO'];
        $this->statuses['2'] = $this->multi['FLD_TXT_OFORMLENNO'];
        $this->statuses['3'] = $this->multi['FLD_TXT_OTPRAVLENO'];
        $this->statuses['4'] = $this->multi['FLD_TXT_OPLACHENO'];
        $this->statuses['5'] = $this->multi['FLD_TXT_OTMENENO'];
        $this->statuses['6'] = $this->multi['FLD_TXT_DELETED'];
    }

// end of constructor OrderCtrl



    // ================================================================================================
    //    Function          : show
    //    Date              : 28.01.2011
    //    Returns           : Error Indicator
    //    Description       : Show orders
    // ================================================================================================
    function show()
    {
      if( !$this->sort ) $this->sort='id_order';
      $q = "SELECT `".TblModOrderComments."`.* FROM `".TblModOrderComments."` WHERE 1";

      if($this->fltr) $q = $q." AND `".TblModOrderComments."`.`status`='".$this->fltr."'";
      if(!empty($this->srch)) $q .= " AND `".TblModOrderComments."`.`id_order` LIKE '%".$this->srch."%'";
      $q = $q." ORDER BY ".TblModOrderComments.".".$this->sort." desc";
      $res = $this->Right->Query( $q, $this->user_id, $this->module );
      //echo '<br>'.$q.' <br/>$res='.$res.'$this->Right->result='.$this->Right->result.' $this->user_id='.$this->user_id;
      if( !$res )return false;
      $rows = $this->Right->db_GetNumRows();
      $a = $rows;
      $j = 0;
      $row_arr = NULL;
      for( $i = 0; $i < $rows; $i++ ){
          $row = $this->Right->db_FetchAssoc();
          if( $i >= $this->start && $i < ( $this->start+$this->display ) )
          {
            $row_arr[$j] = $row;
            $j = $j + 1;
          }
      }

      /* Write Form Header */
      $this->Form->WriteHeader( $this->script );

      //========= Show Search Panel START ===========
      AdminHTML::TablePartH();
      ?>
      <div><h4 style="padding:0px; margin:0px;"><?=$this->multi['TXT_SEARCH_PANEL'];?></h4></div>
      <div><?=$this->multi['FLD_ORDER_ID'];?><input type="text" name="srch" value="<?=$this->srch;?>"/><input type="submit" value="<?=$this->multi['TXT_BUTTON_SEARCH'];?>"/></div>
      <div><?=$this->multi['HLP_SEARCH_MAKE'];?></div>
      <?
      AdminHTML::TablePartF();
      //========= Show Search Panel END ===========

      /* Write Table Part */
      AdminHTML::TablePartH();

      ?><tr><td colspan="10"><?
        $this->Form->WriteLinkPages( $this->script, $rows, $this->display, $this->start, $this->sort );

      ?><tr><td colspan="4"><?
        if($this->Right->IsUpdate()) $this->Form->WriteSavePanel( $this->script );
        if($this->Right->IsDelete()) {
            if($this->fltr==6) $this->Form->WriteTopPanel( $this->script,2);
            else $this->Form->WriteTopPanel( $this->script,1);

        }

      $script2 = 'module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&task=show&fltr='.$this->fltr;
      $script2 = $_SERVER['PHP_SELF']."?$script2";
       ?>
       <tr>
        <th class="THead" width="30">*</th>
        <?/*
        <th class="THead" width="80"><A HREF=<?=$script2?>&sort=id_order><?=$this->multi['FLD_NUM_ORDER'];?></A></th>
        <th class="THead" width="100"><A HREF=<?=$script2?>&sort=date><?=$this->multi['FLD_DATE'];?></A></th>
        <th class="THead" width="80"><?=$this->multi['FLD_ORDER_STATUS'];?></th>
        */?>
        <th class="THead" width="200"><?=$this->multi['_TXT_COSTOMER'];?></th>
        <th class="THead"><?=$this->multi['_TXT_PRODUCT'];?></th>
        <?

        $style1 = 'TR1';
        $style2 = 'TR2';
        $style_prod1 = 'line1';
        $style_prod2 = 'line2';
        $old_id_order = NULL;

        $mass = $this->statuses;

        // Метод доставки
        $this->deliveryMethod = $this->GetSysSprTableData(TblModOrderSprDelivery);
        //Метод оплаты
        $this->payMethod = $this->GetSysSprTableData(TblModOrderSprPayMethod);

        $n = count( $row_arr );
        for( $i = 0; $i < $n; $i++ ){
            $row = $row_arr[$i];
            if ( (float)$i/2 == round( $i/2 ) ) { $class=$style1; }
            else { $class=$style2; }

            if($row['isread']==0) $new = '<sup class="newItemSup">NEW</sup>';
            else $new = '';
            ?>
            <tr class="<?=$class;?>">
                 <td><?$this->Form->CheckBox( "id_del[]", $row['id_order'], NULL, 'orderId'.$row['id'] );?></td>
                 <td colspan="6" align="left" ><a href="<?=$script2?>&task=edit&id_order=<?=$row['id_order'];?>" style="font-size:14px;"><?=$this->multi['FLD_ORDER_ID'];?><strong><?=$row['id_order'];?></strong></a><?=$new;?>
                 &nbsp;<?=$this->multi['FLD_FROM'].' '.$row['date'];?>
                 <?$status = stripslashes($row['status']);?>
                 &nbsp;<?$this->Form->Select( $mass, 'status['.$row['id_order'].']', $status, 10,'onChange="$(\'#orderId'.$row['id'].'\').attr(\'checked\',true);"');?>
                 <?
                 $link = '/modules/mod_order/print_bill.php?module='.$this->module.'&id_order='.$row['id_order'];
                 $width = '630px';
                 $height = '800px';
                 $params = "OnClick='window.open(\"".$link."\", \"\", \"width=".$width.", height=".$height.", status=0, toolbar=0, location=0, menubar=0, resizable=0, scrollbars=1\"); return false;'";
                 ?>
                 &nbsp;<a href="<?=$link;?>" target="_blank" <?//=$params;?>><?=$this->multi['TXT_PRINT_ORDER']?></a>
                 <?/*&nbsp;<span style="color:#087D2B" align="center"><?=$this->multi['FLD_ORDER_SUM']?>: <?=$row['sum'].' '.$this->Spr->GetNameByCod( TblSysCurrenciesSprSufix, $row['currency'], $this->lang_id, 1 );?></span>*/?>
                 </td>
            </tr>
            <tr class="<?=$class;?>">
            <?

            ?><td></td><?

            // -----------   start user -------------------
            ?>
            <td align="left" valign="top">
            <?
            echo $this->multi['FLD_FIO_FOR_ORDER']?>: <?
            if( $row['buyer_id']!=0 ) {  // Существующий пользователь
                $us = "select * from `".TblSysUser."` where `id`='".$row['buyer_id']."'";
                $res1 = $this->Right->Query( $us, $this->user_id, $this->module );
                $user = $this->Right->db_FetchAssoc($res1);
                //echo  '<br/>$us =' .$us.'<br/>';
                $user_data = $this->User->GetUserDataByUserEmail($user['login']);
                ?><a href="/admin/index.php?module=87&display=20&start=0&sort=&id=<?=$user_data['id']?>&task=edit" target="_blank"><?=stripslashes($row['name']);?></a><br><?
            }
            else {  // Пользователь без регистрации на сайте
                echo stripslashes($row['name']);?><br><?
            }
            ?>
            <?=$this->multi['FLD_EMAIL']?>: <a href="mailto:<?=stripslashes($row['email']);?>"><?=stripslashes($row['email']);?></a><br/>
            <?=$this->multi['FLD_PHONE_MOB']?>: <?=stripslashes($row['phone_mob']);?> <br/>
            <?=$this->multi['FLD_PHONE']?>: <?=stripslashes($row['phone']);?> <br/>
            <?=$this->multi['FLD_CITY']?>: <?=stripslashes($row['city']);?> <br/>
            <?=$this->multi['FLD_ADR']?>: <?=stripslashes($row['addr']);?> <br/>
            <? if(!empty($row['firm'])){?> Фирма: <?=stripslashes($row['firm']);?> <br /><? }
            ?>

            <br/><i><?=$this->multi['TXT_FRONT_DELIVERY_METHOD'];?>:</i><br/>
              <?=$this->deliveryMethod[$row['delivery_method']];?>

            <br/><i><?=$this->multi['TXT_FRONT_PAY_METHOD'];?>:</i><br/>
              <?=$this->payMethod[$row['pay_method']];?>
            <?
            if(!empty($row['comment'])){
                ?>
                <br/><br/><i><?=$this->multi['FLD_COMMENT'];?>:</i><br/>
                <?=stripslashes(str_replace("\n", "<br>", stripslashes($row['comment'])));
            }
            ?>
            </td>
            <?
            // -----------   end user -------------------

            $order_prod = $this->GetProdOrdersByIdOrder($row['id_order']);
            $cnt_o = count($order_prod);
            ?>
            <td align="left" valign="top">
             <table border="0" cellpadding="2" cellspacing="1" class="EditTable" width="100%">
                 <tr valign="top" class="line0">
                  <td width="40"><?=$this->multi['FLD_IMG'];?></td>
                  <td width="40"><?=$this->multi['FLD_NUMBER_NAME'];?></td>
                  <td width="150" align="left"><?=$this->multi['FLD_PROD_ID'];?></td>
                  <td width="50" align="center"><?=$this->multi['FLD_PRICE'];?></td>
                  <td width="40" align="center"><?=$this->multi['FLD_QUANTITY'];?></td>
                  <td width="50" align="center"><?=$this->multi['FLD_SUMA'];?></td>
                  <?/*
                  <td></td>
                  <td></td>
                  */?>
                 </tr>
             <?
             for($j=0; $j<$cnt_o; $j++){
                 if($j==0)
                     $str_prod = $order_prod[$j]['prod_id'];
                 else
                     $str_prod .= ', '.$order_prod[$j]['prod_id'];

             }
             $q_prod = "SELECT DISTINCT
                            `".TblModCatalogProp."`.*,
                            `".TblModCatalogPropSprName."`.name,
                            `".TblModCatalogSprName."`.name as cat_name,
                            `".TblModCatalogTranslit."`.`translit`,
                            `".TblModCatalogPropImg."`.`path` AS `first_img`,
                            `".TblModCatalogPropImgTxt."`.`name` AS `first_img_alt`,
                            `".TblModCatalogPropImgTxt."`.`text` AS `first_img_title`
                        FROM `".TblModCatalogProp."`
                            LEFT JOIN `".TblModCatalogPropImg."` ON (`".TblModCatalogProp."`.`id`=`".TblModCatalogPropImg."`.`id_prop` AND `".TblModCatalogPropImg."`.`move`='1' AND `".TblModCatalogPropImg."`.`show`='1')
                            LEFT JOIN `".TblModCatalogPropImgTxt."` ON (`".TblModCatalogPropImg."`.`id`=`".TblModCatalogPropImgTxt."`.`cod` AND `".TblModCatalogPropImgTxt."`.lang_id='".$this->lang_id."'),
                            `".TblModCatalogPropSprName."`,`".TblModCatalogSprName."`, `".TblModCatalog."`, `".TblModCatalogTranslit."`
                        WHERE `".TblModCatalogProp."`.`id` IN (".$str_prod.")
                        AND `".TblModCatalogProp."`.`id_cat`=`".TblModCatalog."`.`id`
                        AND `".TblModCatalogProp."`.visible='2'
                        AND `".TblModCatalog."`.`visible`='2'
                        AND `".TblModCatalogProp."`.id=`".TblModCatalogPropSprName."`.cod
                        AND `".TblModCatalogProp."`.id_cat=`".TblModCatalogSprName."`.cod
                        AND `".TblModCatalogPropSprName."`.lang_id='".$this->lang_id."'
                        AND `".TblModCatalogSprName."`.lang_id='".$this->lang_id."'
                        AND `".TblModCatalogProp."`.id=`".TblModCatalogTranslit."`.`id_prop`
                        AND `".TblModCatalogTranslit."`.`lang_id`='".$this->lang_id."'
                        ";
             $res_prod = $this->db->db_Query($q_prod);
             //echo '<br>$q_prod='.$q_prod.' $res_prod='.$res_prod;
             $rows_prod = $this->db->db_GetNumRows();
             for($j=0; $j<$rows_prod; $j++){
                 $arr_prod[$j] = $this->db->db_FetchAssoc();
             }
             for($j=0; $j<$rows_prod; $j++){
                 if ( (float)$j/2 == round( $j/2 ) ) { $class=$style_prod1; }
                 else { $class=$style_prod2; }
                 $row_prod = $arr_prod[$j];
                 $CatalogLayout = &check_init('CatalogLayout', 'CatalogLayout');
                 $href = $CatalogLayout->Link($row_prod['id_cat'], $row_prod['id']);
                 /*
                 $q_prod = "SELECT `".TblModCatalogProp."`.`number_name`, `".TblModCatalogPropSprName."`.`name`
                            FROM `".TblModCatalogProp."`, `".TblModCatalogPropSprName."`
                            WHERE `".TblModCatalogProp."`.id = '".$order_prod[$j]['prod_id']."'
                            AND `".TblModCatalogProp."`.id = `".TblModCatalogPropSprName."`.`cod`
                            AND `".TblModCatalogPropSprName."`.lang_id = '"._LANG_ID."'
                            ";
                 $res_prod = $this->db->db_Query($q_prod);
                 echo '<br>$q_prod='.$q_prod.' $res_prod='.$res_prod;
                 $row_prod = $this->db->db_FetchAssoc();
                  *
                  */
                 ?>
                 <tr class="<?=$class;?>">
                  <td valign="top"><?=$this->Catalog->ShowCurrentImage($row_prod['first_img'], 'size_auto=75', 85, NULL, NULL, $row_prod['id'], false);?></td>
                  <td valign="top"><?=$row_prod['number_name'];?></td>
                  <td align="left" valign="top"><a href="<?=$href;?>"><?=stripslashes($row_prod['name']);?></a></td>
                  <td align="right" valign="top"><?=$this->Currency->ShowPrice($order_prod[$j]['price'])?></td>
                  <td valign="top"><?=$order_prod[$j]['quantity'];?></td>
                  <td align="right" valign="top"><?=$this->Currency->ShowPrice($order_prod[$j]['sum'])?></td>
                  <?/*
                  <td><?=stripslashes($order_prod[$j]['comment']);?></td>
                  <td><?=$order_prod[$j]['parameters'];?></td>
                  */?>
                 </tr>
                 <?
             }
             ?>
                 <tr  class="line0" style="font-weight:bold;">
                  <td colspan="4" align="right">ИТОГО: </td>
                  <td align="center"><?=$row['qnt_all']?></td>
                  <td align="right"><?=$this->Currency->ShowPrice($row['sum'])?></td>
                 </tr>
             </table>
            </td>
           </tr>
           <tr><td height="20"></td></tr>
            <?
          $a=$a-1;
        } //-- end for

        AdminHTML::TablePartF();
        if($this->Right->IsUpdate()) $this->Form->WriteSavePanel( $this->script );

        $this->Form->WriteFooter();

    } //end of function show


    // ================================================================================================
    // Function : save()
    // Date : 03.04.2006
    // Parms : $id_order
    // Returns : true,false / Void
    // Description : Store status of the order
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function save($id_order)
    {
        $cnt  = count($id_order);
        for($j=0;$j<$cnt;$j++){
            $res = $this->SendNotificationToUserEmail($id_order[$j], $this->status[$id_order[$j]]);
            //if($res) echo 'Уведомление о смене статуса заказа №'.$id_order[$j].' успешно отправлено заказчику<br/>';
            $q = "UPDATE `".TblModOrderComments."` SET `status` = '".$this->status[$id_order[$j]]."' WHERE `id_order` = '".$id_order[$j]."' ";
            //echo "<br> q = ".$q;
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
        }
        return true;
    } //end of fuinction save()

    /**
    * Class method SendNotificationToUserEmail
    * send notification to user_email with new status of order
    * @return true/false or arrays:
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.1, 30.07.2012
    */
    function SendNotificationToUserEmail( $id_order, $status )
    {
        $this->Err = NULL;
        $mail_user = new Mail();
        $body = '';

        $o_comm = $this->GetOrderCommentInArr($id_order);
        $user_name = stripslashes($o_comm['name']);
        $user_email = stripslashes($o_comm['email']);
        $dt = explode(' ', $o_comm['date']);

        //-------- build body of email message START ----------
	    //вступительная речь
        $body = '
        <div>
	'.$this->multi['TXT_ORDER_DEAR'].' '.$user_name.',
        <br/>'.$this->multi['TXT_ORDER_STATUS_CHANGE_TO'].$id_order.' '.$this->multi['TXT_ORDER_FROM'].' '.$dt[0].' '.$this->multi['TXT_COST'].' '.$this->Currency->ShowPrice($o_comm['sum']).' '.$this->multi['TXT_ORDER_STATUS_CHANGE_TO_2'].' "'.$this->statuses[$status].'".
        <br/>'.$this->multi['TXT_ORDER_TRACKING'].' <a href="http://'.$_SERVER['SERVER_NAME'].'/order/history/">'.$this->multi['TXT_ORDER_TRACKING_2'].'</a>
        <br/>
        </div>
        ';
        //-------- build body of email message END ----------
        //echo '<br>$body='.$body;

        $subject = $this->multi['TXT_ORDER_NOTIFICATION_SBJ'].' '.$id_order.' '.$this->multi['TXT_ORDER_NOTIFICATION_SBJ_2'].' '.$_SERVER['SERVER_NAME'];
        //echo '<br>$subject='.$subject;

        $mail_user->AddAddress($user_email);
        $mail_user->WordWrap = 500;
        $mail_user->IsHTML( true );
        $mail_user->Subject = $subject;
        $mail_user->Body = $body;
        $res_user = $mail_user->SendMail();
        //echo '<br>$res_user='.$r$res_useres;
        return $res_user;

    } //end of function SendNotificationToUserEmail()



    // ================================================================================================
    // Function : edit
    // Date : 03.04.2006
    // Returns : true,false / Void
    // Description : Show data from $module table
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function edit( )
    {
        $order_arr = array();
        if( $this->id_order!=NULL  ){
            $mas = $this->GetOrderCommentInArr($this->id_order);
            $order_arr = $this->GetProdOrdersByIdOrder($this->id_order);

            if($mas['isread']==0){
                $q = "UPDATE `".TblModOrderComments."` SET `isread`='1' WHERE `id_order`='".$this->id_order."'";
                $res = $this->Right->query($q, $this->user_id, $this->module);
            }
        }
        else $mas = NULL;
        //$curr_id = $mas['currency'];

        // Write Form Header
        $this->Form->WriteHeaderFormImg( $this->script );

        if( $this->id_order!=NULL )
            $txt = $this->multi['_TXT_EDIT_DATA'];
        else
            $txt = $this->multi['_TXT_ADD_DATA'];

        AdminHTML::PanelSubH( $txt );

        //-------- Show Error text for validation fields --------------
        $this->ShowErr();
        //-------------------------------------------------------------

        AdminHTML::PanelSimpleH();
        ?>
        <table border="0" class="EditTable"  width="100%">
         <tr>
          <td width="20%">
           <b><?=$this->multi['FLD_ID']?>:</b>
           <?
           if( $this->id_order!=NULL ){
               echo $mas['id'];
           }
           ?>
          </td>
         </tr>
         <tr>
          <td width="20%">
           <b><?=$this->multi['FLD_ORDER_ID']?>:</b>
          </td>
          <td>
           <b>
           <?
           if( $this->id_order!=NULL ){
               echo $mas['id_order'];
               $this->Form->Hidden( 'id_order', $mas['id_order'] );
           }
           else $this->Form->Hidden( 'id_order', '' );
           ?>
           </b>
          </td>
         </tr>
         <tr>
          <td><b><?=$this->multi['FLD_FIO_FOR_ORDER']?></b></td>
          <td>
           <?
           if( $this->id_order!=NULL ) $this->Err!=NULL ? $val=$this->name : $val=$mas['name'];
           else $val=$this->name;
           $this->Form->TextBox( 'name', stripslashes($val), 50 );
           ?>
          </td>
         </tr>
         <tr>
          <td><b><?=$this->multi['FLD_PHONE_MOB']?></b></td>
          <td>
           <?
           if( $this->id_order!=NULL ) $this->Err!=NULL ? $val=$this->phone_mob : $val=$mas['phone_mob'];
           else $val=$this->phone_mob;
           $this->Form->TextBox( 'phone_mob', stripslashes($val), 50 );
           ?>
          </td>
         </tr>
         <tr>
          <td><b><?=$this->multi['FLD_PHONE']?></b></td>
          <td>
           <?
           if( $this->id_order!=NULL ) $this->Err!=NULL ? $val=$this->phone : $val=$mas['phone'];
           else $val=$this->phone;
           $this->Form->TextBox( 'phone', stripslashes($val), 50 );
           ?>
          </td>
         </tr>
         <tr>
          <td><b><?=$this->multi['FLD_EMAIL']?></b></td>
          <td>
           <?
           if( $this->id_order!=NULL ) $this->Err!=NULL ? $val=$this->email : $val=$mas['email'];
           else $val=$this->email;
           $this->Form->TextBox( 'email', stripslashes($val), 50 );
           ?>
          </td>
         </tr>
         <tr>
          <td><b><?=$this->multi['FLD_CITY']?></b></td>
          <td>
           <?
           if( $this->id_order!=NULL ) $this->Err!=NULL ? $val=$this->city : $val=$mas['city'];
           else $val=$this->city;
           $this->Form->TextBox( 'city', stripslashes($val), 50 );
           ?>
          </td>
         </tr>
         <tr>
          <td><b><?=$this->multi['FLD_ADR']?></b></td>
          <td>
           <?
           if( $this->id_order!=NULL ) $this->Err!=NULL ? $val=$this->addr : $val=$mas['addr'];
           else $val=$this->addr;
           $this->Form->Textarea( 'addr', stripslashes($val), 3, 50 );
           ?>
          </td>
         </tr>
         <tr>
          <td><b><?=$this->multi['FLD_COMMENT']?></b></td>
          <td>
           <?
           if( $this->id_order!=NULL ) $this->Err!=NULL ? $val=$this->comment : $val=$mas['comment'];
           else $val=$this->comment;
           $this->Form->Textarea( 'comment', stripslashes($val), 3, 50 );
           ?>
          </td>
         </tr>
         <tr><td colspan="2"><hr /></td></tr>
         <tr>
          <td><b><?=$this->multi['TXT_FRONT_PAY_METHOD']?></b></td>
          <td>
           <?
           if( $this->id_order!=NULL ) $this->Err!=NULL ? $val=$this->pay_method : $val=$mas['pay_method'];
           else $val=$this->pay_method;
           $db = new DB();
           $q = "Select * from `".TblModOrderSprPayMethod."` where `lang_id`='"._LANG_ID."'";
           $res = $db->db_Query( $q );
           if( !$res ) return false;
           $rows = $db->db_GetNumRows();
           $k=1;

           for($i=0; $i<$rows; $i++){
               $row = $db->db_FetchAssoc();
               ?>
               <div style="overflow:hidden; padding-top:8px;">
                   <div style="float:left; margin:0px 4px 0px 0px;"><?$this->Form->Radio('pay_method', '', $row['cod'], $val);?></div>
                   <div><strong><?=$row['cod'];?>. <?=$row['name'];?></strong><br /><?=strip_tags($row['descr'], "<b><a><strong>")?></div>
               </div>
               <?
               $k++;
           }
           ?>
          </td>
         </tr>
         <tr><td colspan="2"><hr /></td></tr>
         <tr>
          <td><b><?=$this->multi['TXT_FRONT_DELIVERY_METHOD']?></b></td>
          <td>
           <?
           if( $this->id_order!=NULL ) $this->Err!=NULL ? $val=$this->delivery_method : $val=$mas['delivery_method'];
           else $val=$this->delivery_method;
           $db = new DB();
           $q = "Select * from `".TblModOrderSprDelivery."` where `lang_id`='"._LANG_ID."'";
           $res = $db->db_Query( $q );
           if( !$res ) return false;
           $rows = $db->db_GetNumRows();
           $k=1;

           for($i=0; $i<$rows; $i++){
               $row = $db->db_FetchAssoc();
               ?>
               <div style="overflow:hidden; padding-top:8px;">
                   <div style="float:left; margin:0px 4px 0px 0px;"><?$this->Form->Radio('delivery_method', '', $row['cod'], $val);?></div>
                   <div><strong><?=$row['cod'];?>. <?=$row['name'];?></strong><br /><?=strip_tags($row['descr'], "<b><a><strong>")?></div>
               </div>
               <?
               $k++;
           }
           ?>
          </td>
         </tr>
         <tr><td colspan="2" height="15"></td></tr>
         <tr>
          <td colspan="2">
             <div id="tableprod">
             <?$this->EditOrdersProd($mas, $order_arr);?>
             </div>
          </td>
         </tr>
        </table>
        <?
        AdminHTML::PanelSimpleF();

        if($this->Right->IsUpdate()) $this->Form->WriteSaveAndReturnPanel( $this->script );?>&nbsp;<?
        if($this->Right->IsUpdate()) $this->Form->WriteSavePanel( $this->script, 'save_order_backend' );?>&nbsp;<?
        $this->Form->WriteCancelPanel( $this->script );?>&nbsp;<?
        AdminHTML::PanelSubF();

        $this->Form->WriteFooter();

        ?>
        <script language="JavaScript">
        function DelProd(div_id, url){
            if( !window.confirm('<?=$this->Msg->get_msg('_SYS_QUESTION_IS_DELETE');?>')) return false;
            else{
              did = "#"+div_id;
              $.ajax({
                  type: "POST",
                  url: url,
                  data: "",
                  success: function(html){
                      $(did).empty();
                      $(did).append(html);
                  },
                  beforeSend: function(html){
                      $(did).html('<img src="images/icons/loading_animation_liferay.gif"/>');
                  }
              });
            }
        } // end of function DelProd

        function ShowAddProdItem(div_id, url){
          did = "#"+div_id;
          $.ajax({
              type: "POST",
              url: url,
              data: "",
              success: function(html){
                  $(did).empty();
                  $(did).append(html);
              },
              beforeSend: function(html){
                  $(did).html('<img src="images/icons/loading_animation_liferay.gif"/>');
              }
          });
        } // end of function ShowAddProdItem

        function AddProdItem(div_id, url, form_name){
          did = "#"+div_id;
          dta = $('#'+form_name).formSerialize();
          $.ajax({
              type: "POST",
              url: url,
              data: dta,
              success: function(html){
                  $(did).empty();
                  $(did).append(html);
              },
              beforeSend: function(html){
                  $(did).html('<img src="images/icons/loading_animation_liferay.gif"/>');
              }
          });
        } // end of function ShowAddProdItem

        function LoadProdData(div_id, url, form_name){
          did = "#"+div_id;
          dta = $('#'+form_name).formSerialize();
          $.ajax({
              type: "POST",
              url: url,
              data: dta,
              success: function(html){
                  $(did).empty();
                  $(did).append(html);
              },
              beforeSend: function(html){
                  $(did).html('<img src="images/icons/loading_animation_liferay.gif"/>');
              }
          });
        } // end of function ShowAddProdItem
        </script>
        <?
    } //end of fuinction edit()

    // ================================================================================================
    // Function : EditOrdersProd()
    // Date : 12.05.2010
    // Parms : $id_order
    // Returns : true,false / Void
    // Description : recalculate order
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function EditOrdersProd($mas, $order_arr)
    {
        $curr_id = $mas['currency'];
        if(empty($curr_id)) $curr_id = $this->Currency->defCurrencyData['id'];
        ?>
           <table border="1" cellspacing="0" cellpadding="5" width="100%" align="left" class="EditTable">
              <tr style="font-weight:bold;">
               <td align="left" width="130"> <?=$this->multi['FLD_NUMBER_NAME'];?></td>
               <td align="left"> <?=$this->multi['_TXT_PRODUCT'];?></td>
               <td align="center" width="75" class="td_border"><?=$this->multi['FLD_PRICE'];?> <??></td>
               <td align="center" width="40" class="td_border"><?=$this->multi['FLD_QUANTITY'];?></td>
               <td align="center" width="75" class="td_border"><?=$this->multi['FLD_SUMA'];?></td>
               <td align="center" width="75" class="td_border"><?=$this->multi['TXT_DELETE'];?></td>
              </tr>
              <?

               $cnt = sizeof($order_arr);
               $summ_all = 0;
               for($i=0;$i<$cnt;$i++){
                   $order_data = $order_arr[$i];
                   //при редактировании заказа все стоимости и суммы отображаются в валюте, в какой валюте пользователь делал заказ
                   $price = $this->Currency->Converting($order_data['currency'], $curr_id, $order_data['price'], 2);
                   $sum = $this->Currency->Converting($order_data['currency'], $curr_id, $order_data['sum'], 2);
                   $summ_all += $sum;
                   $q_prod = "SELECT `".TblModCatalogProp."`.`number_name`, `".TblModCatalogPropSprName."`.`name`
                        FROM `".TblModCatalogProp."`, `".TblModCatalogPropSprName."`
                        WHERE `".TblModCatalogProp."`.id = '".$order_data['prod_id']."'
                        AND `".TblModCatalogProp."`.id = `".TblModCatalogPropSprName."`.`cod`
                        AND `".TblModCatalogPropSprName."`.lang_id = '"._LANG_ID."'
                        ";

                    $res_prod = $this->db->db_Query($q_prod);
                    //echo '<br>$q_prod='.$q_prod.' $res_prod='.$res_prod;
                    $row_prod = $this->db->db_FetchAssoc();
                    $name = stripslashes($row_prod['name']);
                    ?>
                    <tr align="center">
                    <td align="left"><?=$row_prod['number_name'];?></td>
                    <td align="left"><?=$name;?></td>
                    <td><?=$this->Currency->ShowPrice($price);?></td>
                    <td>
                        <?
                        //echo '<br />$this->id='.$this->id.' $this->Err='.$this->Err;
                        if( $this->id_order!=NULL ) {
                            $this->Err!=NULL ? $val = $this->quantity[$order_data['prod_id']] : $val = $order_data['quantity'];
                        }
                        else $val = $order_data['quantity'];
                        $this->Form->TextBox( 'quantity['.$order_data['prod_id'].']', stripslashes($val), 4 );
                        ?>
                    </td>
                    <td><?=$this->Currency->ShowPrice($sum);?></td>
                    <td><a href="" onclick="DelProd('tableprod', '/modules/mod_order/order.backend.php?module=<?=$this->module;?>&task=del_prod_item&id_order=<?=$mas['id_order'];?>&id=<?=$order_data['id'];?>'); return false;"><?=$this->multi['TXT_DELETE'];?></a></td>
                    </tr>
                    <?
              } // end for prod

              if($this->Right->IsUpdate()){
              ?>
              <tr>
               <td colspan="6" align="left">
                <div id="addproditem">
                 <input type="button" value="+" onclick="ShowAddProdItem('addproditem', '/modules/mod_order/order.backend.php?module=<?=$this->module;?>&task=show_add_prod_item&id_order=<?=$mas['id_order'];?>'); return false;">
                </div>
               </td>
              </tr>
              <?
              }
              ?>
              <tr>
               <td colspan="4" style="height:45px; margin-top:15px; padding-right:28px;" align="right">
                <b><?=$this->multi['TXT_TOTAL_COST']?>:</b>
               </td>
               <td>
                   <b>
                   <?
                    if($this->id_order) echo $this->Currency->ShowPrice($mas['sum']);
                    else echo $this->Currency->ShowPrice($summ_all);
                    ?>
                   </b>
               </td>
              </tr>
           </table>
        <?
    }//end of function EditOrdersProd()

    // ================================================================================================
    // Function : save_order_backend()
    // Date : 03.04.2006
    // Parms : $id_order
    // Returns : true,false / Void
    // Description : Store status of the order
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function save_order_backend()
    {
        $sum_all=0;
        $qnt_all=0;
        if($this->id_order){
            $q = "UPDATE `".TblModOrderComments."` SET
               `name`='".$this->name."',
               `phone`='".$this->phone."',
               `phone_mob`='".$this->phone_mob."',
               `email`='".$this->email."',
               `city`='".$this->city."',
               `addr`='".$this->addr."',
               `firm`='".$this->firm."',
               `comment`='".$this->comment."',
               `discount`='".$this->discount."',
               `pay_method`='".$this->pay_method."',
               `delivery_method`='".$this->delivery_method."',
               `date`='".$this->date."',
               `status`='".$this->fltr."'
               WHERE `id_order`='".$this->id_order."'
               ";
            $res = $this->Right->Query($q);
            //echo '<br />$q='.$q.' $res='.$res;
            if(!$res OR ! $this->Right->result) return false;

            $q = "SELECT * FROM `".TblModOrder."` WHERE `id_order`='".$this->id_order."'";
            $res = $this->db->db_Query($q);
            //echo '<br />$q='.$q.' $res='.$res;
            if(!$res OR ! $this->db->result) return false;
            $rows = $this->db->db_GetNumRows();
            for($i=0;$i<$rows;$i++){
                $row = $this->db->db_FetchAssoc();
                //echo '<br />$row[prod_id]='.$row['prod_id'];
                if( isset($this->quantity[$row['prod_id']]) AND $row['quantity']!=$this->quantity[$row['prod_id']] ){
                    $sum = $this->quantity[$row['prod_id']]*$row['price'];
                    $sum_all += $sum;
                    $qnt_all += $this->quantity[$row['prod_id']];
                    $q = "UPDATE `".TblModOrder."` SET
                          `quantity`='".$this->quantity[$row['prod_id']]."',
                          `sum`='".$sum."'
                          WHERE `prod_id`='".$row['prod_id']."' AND `id_order`='".$this->id_order."'
                         ";
                    $res = $this->Right->Query($q, $this->user_id, $this->module);
                    //echo '<br />$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
                    if(!$res OR ! $this->Right->result) return false;

                }
                else{
                    $qnt_all +=$row['quantity'];
                    $sum_all +=$row['sum'];
                }
            }

        }
        else{
            $id_order = $this->GetNewOrderId();
            $q = "INSERT INTO `".TblModOrderComments."` SET
               `id_order` = '".$id_order."',
               `name`='".$this->name."',
               `phone`='".$this->phone."',
               `phone_mob`='".$this->phone_mob."',
               `email`='".$this->email."',
               `city`='".$this->city."',
               `addr`='".$this->addr."',
               `firm`='".$this->firm."',
               `comment`='".$this->comment."',
               `discount`='".$this->discount."',
               `pay_method`='".$this->pay_method."',
               `delivery_method`='".$this->delivery_method."',
               `date`='".$this->date."',
               `status`='".$this->fltr."',
               `isread`='1',
               `currency`='".$this->Currency->defCurrencyData['id']."'
               ";
            $res = $this->Right->Query($q, $this->user_id, $this->module);
            //echo '<br />$q='.$q.' $res='.$res;
            if(!$res OR !$this->Right->result) return false;

            $q = "SELECT * FROM `".TblModOrder."` WHERE `id_order`=''";
            $res = $this->db->db_Query($q);
            //echo '<br />$q='.$q.' $res='.$res;
            if(!$res OR ! $this->db->result) return false;
            $rows = $this->db->db_GetNumRows();
            for($i=0;$i<$rows;$i++){
                $arr_new_order_data[$i] = $this->db->db_FetchAssoc();
            }
            for($i=0;$i<$rows;$i++){
                $row =$arr_new_order_data[$i];
                //echo '<br />$row[prod_id]='.$row['prod_id'];
                if( isset($this->quantity[$row['prod_id']]) AND (!$this->id_order OR ($row['quantity']!=$this->quantity[$row['prod_id']] AND !empty($this->id_order)) ) ){
                    $sum = $this->quantity[$row['prod_id']]*$row['price'];
                    $sum_all += $sum;
                    $qnt_all += $this->quantity[$row['prod_id']];
                    $q = "UPDATE `".TblModOrder."` SET
                          `id_order` = '".$id_order."',
                          `quantity`='".$this->quantity[$row['prod_id']]."',
                          `sum`='".$sum."'
                          WHERE `prod_id`='".$row['prod_id']."' AND `id_order`=''
                         ";
                    $res = $this->Right->Query($q, $this->user_id, $this->module);
                    //echo '<br />$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
                    if(!$res OR ! $this->Right->result) return false;

                }//end if
            }//end for
            $this->id_order = $id_order;
        }//end if
        $res = $this->ReCalculateOrder($qnt_all, $sum_all);
        if(!$res) return false;
        return true;
    }//end of function save_order_backend()

    // ================================================================================================
    // Function : ReCalculateOrder()
    // Date : 12.05.2010
    // Parms :  $qnt_all
    //          $sum_all
    // Returns : true,false / Void
    // Description : recalculate order
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function ReCalculateOrder($qnt_all=0, $sum_all=0)
    {
        $q = "SELECT * FROM `".TblModOrderComments."` WHERE `id_order`='".$this->id_order."'";
        $res = $this->db->db_Query($q);
        //echo '<br />$q='.$q.' $res='.$res;
        if(!$res OR ! $this->db->result) return false;
        $row = $this->db->db_FetchAssoc();
        if($row['sum']!=$sum_all OR $row['qnt_all']!=$qnt_all){
            $q = "UPDATE `".TblModOrderComments."` SET
                  `qnt_all`='".$qnt_all."',
                  `sum`='".$sum_all."'
                  WHERE `id_order`='".$this->id_order."'
                 ";
            $res = $this->Right->Query($q, $this->user_id, $this->module);
            //echo '<br />$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
            if(!$res OR ! $this->Right->result) return false;

        }
        return true;
    }//end of function ReCalculateOrder()

    // ================================================================================================
    // Function : del()
    // Date : 06.01.2006
    // Returns :      true,false / Void
    // Description :  Remove data from the table
    // Programmer :  Igor Trokhymchuk
    // ================================================================================================
    function del( $id_del )
    {
        $kol = count( $id_del );
        $del = 0;
        for( $i=0; $i<$kol; $i++ ){
            $u = $id_del[$i];
            $q = "DELETE
                  FROM `".TblModOrderComments."`, `".TblModOrder."`
                  USING  `".TblModOrderComments."` INNER JOIN `".TblModOrder."`
                  WHERE `".TblModOrderComments."`.id_order='".$u."'
                  AND `".TblModOrderComments."`.id_order=`".TblModOrder."`.id_order
                 ";
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
            //echo '<br>$q='.$q.' $res='.$res;
            if ( $res )
                $del=$del+1;
            else
                return false;
        }
        return $del;
    } //end of fuinction del()

    // ================================================================================================
    // Function : DelProdItemFromOrder()
    // Date : 05.05.2010
    // Returns :      true,false / Void
    // Description :  Remove data from the table
    // Programmer :  Igor Trokhymchuk
    // ================================================================================================
    function DelProdItemFromOrder()
    {
        $q = "DELETE FROM `".TblModOrder."` WHERE `".TblModOrder."`.`id_order`='".$this->id_order."' AND `".TblModOrder."`.`id`='".$this->id."'";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        if( !$res) return false;

        $q = "SELECT * FROM `".TblModOrder."` WHERE `id_order`='".$this->id_order."'";
        $res = $this->db->db_Query($q);
        //echo '<br />$q='.$q.' $res='.$res;
        if(!$res OR ! $this->db->result) return false;
        $rows = $this->db->db_GetNumRows();
        $qnt_all = 0;
        $sum_all = 0;
        for($i=0;$i<$rows;$i++){
            $row = $this->db->db_FetchAssoc();
            $qnt_all += $row['quantity'];
            $sum_all += $row['sum'];
        }
        $this->ReCalculateOrder($qnt_all, $sum_all);
        return true;
    } //end of fuinction DelProdItemFromOrder()

    // ================================================================================================
    // Function : AddProdItemForm()
    // Date : 13.05.2010
    // Parms :
    // Description :  show form for add product to order
    // Programmer :  Igor Trokhymchuk
    // ================================================================================================
    function AddProdItemForm()
    {
        ?>
        <form action="" method="post" name="add_prod_item_form" id="add_prod_item_form">
        <input type="hidden" name="id_order" value="<?=$this->id_order;?>" />
        <div style="float:left;">
        <?
        $params = "style='width:300px;' onchange='LoadProdData(\"prod_dtl\", \"/modules/mod_order/order.backend.php?module=".$this->module."&task=load_prod_data\", \"add_prod_item_form\"); return false';";

        $arr_categs = $this->Catalog->PrepareCatalogForSelect(0, NULL, NULL, 'back', true, true, false, false, NULL, NULL);
        $arr_props = $this->Catalog->PreparePositionsTreeForSelect('all', 'back', 'move', 'asc', NULL);
        echo $this->multi['_TXT_PRODUCT'];
        $this->Catalog->ShowCatalogInSelect($arr_categs, $arr_props, '--- '.$this->multi['TXT_SELECT_PRODUCT'].' ---', 'add_prod_item', '', $params);
        //$this->Catalog->ShowCatalogInSelect(NULL, '--- '.$this->multi['TXT_SELECT_PRODUCT'].' ---', NULL, NULL, true, 'back', true, false, false, 'add_prod_item', NULL, NULL, NULL, $params);
        ?>
        </div>
        <div id="prod_dtl"></div>
        </form>
        <?
        return true;
    } //end of fuinction AddProdItemForm()

    // ================================================================================================
    // Function : LoadProdData()
    // Date : 13.05.2010
    // Returns :      true,false / Void
    // Description :  load product data to order
    // Programmer :  Igor Trokhymchuk
    // ================================================================================================
    function LoadProdData()
    {
        if($this->id_order){
            $q0 = "SELECT `".TblModOrderComments."`.`currency`
                FROM `".TblModOrderComments."`
                WHERE `".TblModOrderComments."`.`id_order`='".$this->id_order."'
                ";
            $res0 = $this->db->db_Query($q0);
            //echo '<br />$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
            if( !$res0 OR !$this->db->result) return false;
            $row0 = $this->db->db_FetchAssoc();
            $order_curr = $this->Currency->GetCurrencyData($row0['currency']);
        }
        else $order_curr = '';

        $q = "SELECT `".TblModCatalogProp."`.`price`, `".TblModCatalogProp."`.`price_currency`
              FROM `".TblModCatalogProp."`
              WHERE `".TblModCatalogProp."`.`id`='".$this->add_prod_item."'
             ";
        $res = $this->db->db_Query($q);
        //echo '<br />$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
        if( !$res OR !$this->db->result) return false;
        $row_prod = $this->db->db_FetchAssoc();
        //echo '<br>$row_prod[price]='.$row_prod['price'].' $row_prod[price_currency]='.$row_prod['price_currency'].' $row0[currency]='.$row0['currency'];

        ?>
        &nbsp;<?=$this->multi['FLD_PRICE'];
        if($this->id_order){
            $price = $this->Currency->Converting($row_prod['price_currency'], $row0['currency'], $row_prod['price']);
            ?>&nbsp;<?=stripslashes($order_curr['prefix']);?><input type="text" size="5" name="add_prod_item_price" value="<?=$price;?>" />&nbsp;<?=stripslashes($order_curr['sufix']);?>
            <input type="hidden" name="add_prod_item_currency" value="<?=$row0['currency'];?>" />
            <?
        }
        else{
            $price = $row_prod['price'];
            ?>&nbsp;<input type="text" size="5" name="add_prod_item_price" value="<?=$price;?>" /><?
            if(isset($this->Catalog->settings['price_currency']) AND $this->Catalog->settings['price_currency']=='1' ){
                ?>&nbsp;<?=$this->Form->Select($this->Currency->listShortNames, 'add_prod_item_currency', $row_prod['price_currency']);
            }
        }
        ?>&nbsp;<?=$this->multi['FLD_QUANTITY'];?>&nbsp;<input type="text" size="5" name="add_prod_item_cnt" value="<?=$this->add_prod_item_cnt;?>" />

        <input type="button" value="<?=$this->multi['TXT_SAVE'];?>" onclick="AddProdItem('tableprod', '/modules/mod_order/order.backend.php?module=<?=$this->module;?>&task=add_prod_item', 'add_prod_item_form');return false;" />
        <?
    } //end of function LoadProdData()


    // ================================================================================================
    // Function : AddProdItemToOrder()
    // Date : 05.05.2010
    // Returns :      true,false / Void
    // Description :  Remove data from the table
    // Programmer :  Igor Trokhymchuk
    // ================================================================================================
    function AddProdItemToOrder()
    {
        if(!empty($this->add_prod_item) AND $this->add_prod_item_cnt>0){

            $q = "SELECT * FROM `".TblModOrder."` WHERE `id_order`='".$this->id_order."'";
            $res = $this->db->db_Query($q);
            //echo '<br />$q='.$q.' $res='.$res;
            if(!$res OR ! $this->db->result) return false;
            $rows = $this->db->db_GetNumRows();
            $qnt_all = 0;
            $sum_all = 0;
            for($i=0;$i<$rows;$i++){
                $row = $this->db->db_FetchAssoc();
                $arr[$row['prod_id']]=$row;
                $qnt_all += $row['quantity'];
                $sum_all += $row['sum'];
            }

            //if( !isset($arr[$this->add_prod_item]) ){
                $sum = $this->add_prod_item_price*$this->add_prod_item_cnt;
                $q = "INSERT INTO `".TblModOrder."` SET
                      `id_order`='".$this->id_order."',
                      `quantity`='".$this->add_prod_item_cnt."',
                      `price`='".$this->add_prod_item_price."',
                      `sum`='".$sum."',
                      `currency`='".$this->add_prod_item_currency."',
                      `prod_id` = '".$this->add_prod_item."'
                      ";
                $res = $this->Right->Query($q, $this->user_id, $this->module);
                //echo '<br />$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
                if(!$res OR ! $this->Right->result) return false;

                $mas = $this->GetOrderCommentInArr($this->id_order);
                $qnt_all += $this->add_prod_item_cnt;
                $sum_all += $sum;
                $res = $this->ReCalculateOrder($qnt_all, $sum_all);
                if(!$res) return false;
                return true;
            //}
        }
    } //end of fuinction AddProdItemToOrder()

    // ================================================================================================
    // Function : PrintOrderBackEnd()
    // Date : 28.01.2011
    // Returns :      true,false / Void
    // Description :  Show Order for Print
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function PrintOrderBackEnd()
    {
        if(!$this->user_id){
         ?>
         <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
         <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
         <head>
            <title><?=$this->multi['TXT_STATEMENT_ACCOUNT']?> <?=$this->multi['TXT_ACCESS_DENIED']?>!</title>
         </head>
         <body>
            <h1><?=$this->multi['TXT_ACCESS_DENIED']?></h1>
          </body>
         </html>
         <?
         return true;
        }

        $OrderLayout = new OrderLayout($this->user_id);
        $OrderLayout->id_order=$this->id_order;

        $o_comm = $this->GetOrderCommentInArr($this->id_order);
        if($o_comm['pay_method']==2)
             $OrderLayout->PrintInvoice($o_comm);
         else
            $OrderLayout->PrintOrderBlank($o_comm);
    } // end of PrintOrderBackEnd

   // ================================================================================================
   // Function : ShowErr()
   // Date : 10.01.2006
   // Returns :      true,false / Void
   // Description :  Show errors
   // Programmer :  Igor Trokhymchuk
   // ================================================================================================
   function ShowErr()
   {
     if ($this->Err){
       echo '
        <fieldset class="err" title="'.$this->Msg->show_text('MSG_ERRORS').'"> <legend>'.$this->Msg->show_text('MSG_ERRORS').'</legend>
        <div class="err_text">'.$this->Err.'</div>
        </fieldset>';
     }
   } //end of function ShowErr()


 } // end of class