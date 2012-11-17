<?php
/**
 * Class catalog_RelatProp
 * Class to display relat prop positions
 * @author Sergey Panarin <sp@seotm.com>
 * @version 1.0 21.01.12
 * @property FrontSpr $Spr
 * @property Form $Form
 * @property db $db
 * @property SystemCurrencies $Currencies
 * @property Rights $Right
 */
class catalog_RelatProp extends Catalog{

    public $db = NULL;
    public $Currencies = NULL;
    public $Spr = NULL;
    public $Form = NULL;
    public $settings = NULL;
    public $id_prop = NULL;
    public $Right = NULL;
    public $user_id = NULL;
    public $module = NULL;
    public $tbl = NULL;
    public $PropRows = 0;

    function __construct($user_id=NULL, $module=NULL, $display=NULL, $sort=NULL, $start=NULL, $width = NULL) {
        //Check if Constants are overrulled
        ( $user_id  !="" ? $this->user_id = $user_id  : $this->user_id = NULL );
        ( $module   !="" ? $this->module  = $module   : $this->module  = NULL );
        ( $display  !="" ? $this->display = $display  : $this->display = 20   );
        ( $sort     !="" ? $this->sort    = $sort     : $this->sort    = NULL );
        ( $start    !="" ? $this->start   = $start    : $this->start   = 0    );
        ( $width    !="" ? $this->width   = $width    : $this->width   = 750  );

        $this-> lang_id = _LANG_ID;

        if (empty($this->db)) $this->db = DBs::getInstance();
        if (empty($this->Right)) $this->Right = &check_init('RightsPelatProp', 'Rights', "'".$this->user_id."', '".$this->module."'");
        if (empty($this->Form)) $this->Form = &check_init('FormRelatProp', 'Form', "'form_mod_catalog_relat_prop'");
        if (empty($this->Spr)) $this->Spr = &check_init('SysSpr', 'SysSpr', "'$this->user_id', '$this->module'");
        if (empty($this->settings)) $this->settings = $this->GetSettings();

        if (( isset($this->settings['price_currency']) AND $this->settings['price_currency']=='1' ) OR ( isset($this->settings['opt_price_currency']) AND $this->settings['opt_price_currency']=='1' ) ){
            $this->Currencies = &check_init('SystemCurrencies', 'SystemCurrencies');
            $this->Currencies->defCurrencyData = $this->Currencies->GetDefaultData();
            $this->Currencies->GetShortNamesInArray('back');
        }

        if (empty($this->multi)) $this->multi = &check_init_txt('TblBackMulti',TblBackMulti);
   } // End of Catalog_content Constructor

   function show(){
       AdminHTML::PanelSubH( $this->multi['TXT_CONTROL_RELAT_PROP'].' <u><strong>'.$this->Spr->GetNameByCod( TblModCatalogPropSprName, $this->id_prop)."</strong></u>");
       $relatPropArr=$this->GetData();
       ?><div id="RelatPropPositionsBox" class="PanelSimpleL">
           <div style="overflow: hidden">
               <form action="/admin/index.php?module=<?=$this->module?>&id_prop=<?=$this->id_prop?>" method="post" name="form_mod_catalog_relat_prop" id="form_mod_catalog_relat_prop" enctype="multipart/form-data">
                <input type="hidden" name="task" id="task" value="">
                <input type="hidden" name="id_prop" value="<?=$this->id_prop?>">
               <ul id="sortableUl" class="sortableUl" style="margin: 0px;padding: 0px;">
                        <?
                    $propStr="";
                for ($i = 0; $i < count($relatPropArr); $i++) {
                    $row=$relatPropArr[$i];
                    if($i==0) $propStr.=$row['prop_id'];
                    else $propStr.=','.$row['prop_id'];
                    ?><li  class="SingleRelatPropBox " onclick="SelectDeselectProp('#relatPropDel<?=$row['prop_id']?>')">
                        <input id="relatPropDel<?=$row['prop_id']?>" class="relatPropCheck" type="checkbox" name="del[<?=$row['prop_id']?>]" title="<?=$row['propName']?> "/>
                        <?
                        if ( isset($this->settings['img']) AND $this->settings['img']=='1' ) {

                            if(!empty($row['first_img_alt'])) $alt=$row['first_img_alt'];
                            elseif(!empty($row['propName'])) $alt=$row['propName'];

                            if(!empty($row['first_img_title'])) $title=$row['first_img_title'];
                            elseif(!empty($row['propName'])) $title=$row['propName'];
                            ?><div class="imageBox120x114"><?
                            if (isset($row['first_img'])) {
                                    echo $this->ShowCurrentImage($row['first_img_id'], 'size_auto=100', 85, NULL, "border=0 alt='".$alt."' title='".$title."'");
                            }
                            ?></div><?
                        }
                        ?><span><?
                            echo '<b>'.$this->multi['FLD_ID'].'</b>:';
                            ?><a alt="<?=$row['prop_id']?>" href="/admin/index.php?module=21&start=0&task=edit&id=<?=$row['prop_id']?>"><?
                            echo $row['prop_id'];
                            ?></a><?
                        ?></span><?
                        if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) {
                            ?><span><?
                            echo $row['propName'];
                            ?></span><?
                        }
                        if ( isset($this->settings['price']) AND $this->settings['price']=='1' ) {
                            for ($j = 0; $j < count($this->Currencies->allCurrData); $j++) {
                                if($row['price_currency']==$this->Currencies->allCurrData[$j]['id'])
                                    if(!empty($this->Currencies->allCurrData[$j]['suf'])) echo $row['price'].' '.$this->Currencies->allCurrData[$j]['suf']; else echo $this->Currencies->allCurrData[$j]['pref'].' '.$row['price'];
                            }
                        }

                    ?></li><?
                }
                ?>

               </ul>
               <div class="addNewProp" onclick="showCatalog()">
                   <div class="addPropPlus"></div>
                   <div class="ZP">Добавить</div>
               </div>
                <input id='propStr' type="hidden" name="propStr" value="<?=$propStr?>"/>
               <script type="text/javascript">

                   function showCatalog(){
                    $.fancybox({
                            href : "<?=$this->script."&task=CatalogShow&ajax=1&propStr="?>"+$("#propStr").val(),
                            onComplete:function(){
                                makeTree();
                            }
                        });
                   }
                   function makeTree(){
                             $("#tree").treeview({
                                    collapsed: true,
                                    animated: "medium",
                                    control:"#sidetreecontrol",
                                    persist: "cookie",
                                    cookieId: "catalogTreeView"
                            });
                   }
                   function SelectDeselectProp($id){
                            $checkObj=$($id);
                            $checkObj.attr('checked',!$checkObj.attr('checked'));
                            if($checkObj.attr('checked')=='checked') $checkObj.parent().addClass("propSelected");
                            else $checkObj.parent().removeClass("propSelected");
                   }

                   function AddRelatPropsTo(){
                       $url="<?=$this->script."&ajax=1&propStr="?>"+$("#propStr").val();
                       $.ajax({
                           url : $url,
                           type : "POST",
                           data : $("#addPropTo").serialize(),
                           beforeSend : function(){
                               $("#loader").show();
                           },
                           success : function(data){
                               $("#loader").hide();
                               $("#RelatPropPositionsBox").html($("#RelatPropPositionsBox",data).html());
                               $("#relatPropCatalogBox").html($("#relatPropCatalogBox",data).html());
                               initSortable();
                               makeTree();
                           }
                       });
                   }

                   function reloadCatalogInner($catId,$url){
                       if($catId=='') $catId=<?=$this->id_cat?>;
                       if(!$url) $url="<?=$this->script."&task=CatalogInnerShow&ajax=1&propStr="?>"+$("#propStr").val()+"&id_cat="+$catId;
                       $.ajax({
                           url : $url,
                           type : "POST",
                           beforeSend : function(){
                               $("#loader").show();
                           },
                           success : function(data){
                               $("#loader").hide();
                               $("#relatPropCatalog").html(data);
                           }
                       });
                   }
                   function initSortable(){
                    $( "#sortableUl" ).sortable({
                                    placeholder: "ui-state-highlight",
                                        update:function(event,ui){
                                            $propOrderStr='';
                                            $('#sortableUl li a').each(function(){
                                                if($propOrderStr.length==0) $propOrderStr+=$(this).attr("alt");
                                                else $propOrderStr+=','+$(this).attr("alt");
                                            });
                                            $("#propStr").val($propOrderStr);
                                        }
                                });
                                $( "#sortableUl" ).disableSelection();
                   }
                   $(document).ready(function(){
                       initSortable();
                   });

               </script>
               </form>
           </div>

       </div>
           <div class="space"></div>
             <?$this->Form->WriteSavePanel("")?>
             <?$this->Form->WriteTopPanel("",2)?>
           <?
       $this->propStr=$propStr;
   }

   function showCatalogByPages(){
       ?>
       <div class="loader" id="loader" style="display: none; "><?=$this->multi['TXT_SAVE_TEXT_LOADER']?></div>
        <div id="relatPropCatalogBox" class="relatPropCatalogBox">
            <div class="relatPropCatalogMenu">
            <?$this->showCategManu();?>
            </div>
            <div id="relatPropCatalog" class="relatPropCatalog">
            <?$this->showCatalogPropPart();?>
            </div>
        </div>

        <?
   }

   function showCatalogPropPart(){
       $catName=$this->Spr->GetNameByCod(TblModCatalogSprName, $this->id_cat, 1, $this->lang_id);
       ?>

           <h1 style="margin-top: 25px;"><? empty($catName) ? print($this->multi['TXT_CATALOG']) : print($catName);?></h1>
           <div class="pages">
                <?
                    $CatalogData=$this->GetData('catalog');
                    $this->GetData('catalog','nolimit');
                    $this->Form->WriteLinkPages($this->script."&task=CatalogInnerShow&ajax=1&propStr=".$this->propStr."&id_cat=".$this->id_cat,$this->PropRows,$this->display,$this->start,'',true);
                ?>
            </div>
           <form action="#" name="addPropTo" method="post" id="addPropTo" enctype="multipart/form-data">
               <input type="hidden" name="task" value="addProp"/>
               <input type="hidden" name="id_cat" value="<?=$this->id_cat?>"/>
       <?
       for ($i = 0; $i < count($CatalogData); $i++) {
            $row=$CatalogData[$i];
            ?><div class="SingleRelatPropBox " onclick="SelectDeselectProp('#PropCheckBoxAdd<?=$row['prop_id']?>')">
                <input id="PropCheckBoxAdd<?=$row['prop_id']?>" class="relatPropCheck" type="checkbox" name="add[<?=$row['prop_id']?>]" title="<?=$row['propName']?> "/>

                <?
                if ( isset($this->settings['img']) AND $this->settings['img']=='1' ) {

                    if(!empty($row['first_img_alt'])) $alt=$row['first_img_alt'];
                    elseif(!empty($row['propName'])) $alt=$row['propName'];

                    if(!empty($row['first_img_title'])) $title=$row['first_img_title'];
                    elseif(!empty($row['propName'])) $title=$row['propName'];
                    ?><div class="imageBox120x114"><?
                    if (isset($row['first_img'])) {
                         echo $this->ShowCurrentImage($row['first_img_id'], 'size_auto=100', 85, NULL, "border=0 alt='".$alt."' title='".$title."'");
                    }
                    ?></div><?
                }
                ?><span><?
                    echo '<b>'.$this->multi['FLD_ID'].'</b>:';
                    ?><a alt="<?=$row['prop_id']?>" href="/admin/index.php?module=21&start=0&task=edit&id=<?=$row['prop_id']?>"><?
                    echo $row['prop_id'];
                    ?></a><?
                ?></span><?
                if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) {
                    ?><span><?
                    echo $row['propName'];
                    ?></span><?
                }
                if ( isset($this->settings['price']) AND $this->settings['price']=='1' ) {
                    for ($j = 0; $j < count($this->Currencies->allCurrData); $j++) {
                        if($row['price_currency']==$this->Currencies->allCurrData[$j]['id'])
                            if(!empty($this->Currencies->allCurrData[$j]['suf'])) echo $row['price'].' '.$this->Currencies->allCurrData[$j]['suf']; else echo $this->Currencies->allCurrData[$j]['pref'].' '.$row['price'];
                    }
                }

            ?>

            </div><?
        }
        ?>
           </form>
               <div class="addPropBtnBox"><input type="button" class="btn0" value="<?=$this->multi['TXT_ADD_RELAT_PROPS']?>" title="<?=$this->multi['TXT_ADD_RELAT_PROPS']?>" onclick="AddRelatPropsTo()"/></div>
    <?
   }

   function showCategManu(){
       $q = "select
                    `".TblModCatalog."`.id,
                    `".TblModCatalog."`.level,
                    `".TblModCatalogSprName."`.name
              from `".TblModCatalog."` LEFT JOIN `".TblModCatalogSprName."`
                    ON ( `".TblModCatalog."`.id = `".TblModCatalogSprName."`.cod AND `".TblModCatalogSprName."`.lang_id = '".$this->lang_id."')
              where 1
                    order by `level` asc, `move` ";
//        $q = "select * from `".TblModCatalog."` where 1 and `level`='".$level."' order by `move` ";
        $res = $this->db->db_Query($q);
//        echo '<br>$q='.$q.' $res='.$res;
        $rows = $this->db->db_GetNumRows($res);
        $levels = array();
        $names = array();
        for($i=0; $i<$rows; $i++)
        {
            $row = $this->db->db_FetchAssoc($res);
            $levels [$row['level']][] = $row ['id'];
            $names [$row['id']] = $row['name'];
        }
       $this->countArr = $this->GetArrayContentCount();
       ?>
        <script src="/admin/include/js/treeView/jquery.treeview.js" type="text/javascript"></script>
        <script src="/admin/include/js/treeView/jquery.cooki.js" type="text/javascript"></script>
        <link rel="stylesheet" href="/admin/include/js/treeView/jquery.treeview.css" />
        <div id="sidetreecontrol"><a href="?#"><?=$this->multi['TXT_COLLAPSE_ALL']?></a> | <a href="?#"><?=$this->multi['TXT_EXPAND_ALL']?></a></div>

        <?
        $this->showCategManuInner(0,$levels,$names);

   }

   function showCategManuInner($level=0,$levels,$names){
       if(!isset($levels[$level])) return;
        $count = count($levels[$level]);
       ?>

          <ul id="tree" class="filetree treeview">
            <?
                   for ($i = 0; $i < $count; $i++) {
                        $id = $levels[$level][$i];
                    $is_sub_level = (isset($levels[$id]) && count($levels[$id]) > 0);
                    if(isset($this->countArr[$id]))
                        $count_content = $this->countArr[$id];
                    else
                        $count_content = 0;

                    ?><li><?

                    if( $is_sub_level ) {
                        ?><a class="folder " href="#" onclick="reloadCatalogInner(<?=$id?>);return false;"><?=$names[$id];?></a><?
                        if( $count_content>0 ) {
                            ?><a href="<?=$link_content;?>"  onclick="reloadCatalogInner(<?=$id?>);return false;"><?=$this->multi['FLD_CONTENT'];?></a><span class="not_href">&nbsp;[<?=$count_content;?>]</span><?
                        }
                    }
                    else {
                        if( $count_content>0 ) {
                            ?><a class="file " href="#"  onclick="reloadCatalogInner(<?=$id?>);return false;"><?=$names[$id];?></a><span class="not_href">&nbsp;[<?=$count_content;?>]</span><?
                        }
                        else {
                            ?><a class="file " href="#"  onclick="reloadCatalogInner(<?=$id?>);return false;"><?=$names[$id];?></a><?
                        }
                    }
                    if( $is_sub_level )
                        $this->showCategManuInner($id, $levels, $names);
                    ?></li><?
                   }
            ?>
          </ul>

           <?
   }


   function GetData($what='relatPorsitions',$limit='limit'){
       if($what=='relatPorsitions'){
        $q="SELECT `".$this->tbl."`.*,
                `".TblModCatalogProp."`.`price`,
                `".TblModCatalogProp."`.`price_currency`,
                `".TblModCatalogProp."`.`id` AS `prop_id`,
                `".TblModCatalogPropSprName."`.`name` AS `propName`,
                `".TblModCatalogPropImg."`.`path` AS `first_img`,
                `".TblModCatalogPropImg."`.`id` AS `first_img_id`,
                `".TblModCatalogPropImgTxt."`.`name` AS `first_img_alt`,
                `".TblModCatalogPropImgTxt."`.`text` AS `first_img_title`
            FROM `".$this->tbl."`,`".TblModCatalogProp."`
                LEFT JOIN `".TblModCatalogPropImg."` ON (`".TblModCatalogProp."`.`id`=`".TblModCatalogPropImg."`.`id_prop` AND `".TblModCatalogPropImg."`.`move`='1' AND `".TblModCatalogPropImg."`.`show`='1')
                LEFT JOIN `".TblModCatalogPropImgTxt."` ON (`".TblModCatalogPropImg."`.`id`=`".TblModCatalogPropImgTxt."`.`cod` AND `".TblModCatalogPropImgTxt."`.lang_id='".$this->lang_id."')
                LEFT JOIN `".TblModCatalogPropSprName."` ON (`".TblModCatalogPropSprName."`.`cod`=`".TblModCatalogProp."`.`id` AND `".TblModCatalogPropSprName."`.`lang_id`='".$this->lang_id."')

            WHERE ((`".$this->tbl."`.`id_prop1`='".$this->id_prop."' AND `".TblModCatalogProp."`.`id`=`".$this->tbl."`.`id_prop2`) OR (`".$this->tbl."`.`id_prop2`='".$this->id_prop."' AND `".TblModCatalogProp."`.`id`=`".$this->tbl."`.`id_prop1`))
                ORDER BY `".$this->tbl."`.`move`
                ";
       }elseif($what=='catalog'){
           $q="SELECT
            `".TblModCatalogProp."`.`price`,
            `".TblModCatalogProp."`.`price_currency`,
            `".TblModCatalogProp."`.`id` AS `prop_id`,
            `".TblModCatalogPropSprName."`.`name` AS `propName`,
            `".TblModCatalogPropImg."`.`path` AS `first_img`,
            `".TblModCatalogPropImg."`.`id` AS `first_img_id`,
            `".TblModCatalogPropImgTxt."`.`name` AS `first_img_alt`,
            `".TblModCatalogPropImgTxt."`.`text` AS `first_img_title`
           FROM `".TblModCatalogProp."`
               LEFT JOIN `".TblModCatalogPropImg."` ON (`".TblModCatalogProp."`.`id`=`".TblModCatalogPropImg."`.`id_prop` AND `".TblModCatalogPropImg."`.`move`='1' AND `".TblModCatalogPropImg."`.`show`='1')
               LEFT JOIN `".TblModCatalogPropImgTxt."` ON (`".TblModCatalogPropImg."`.`id`=`".TblModCatalogPropImgTxt."`.`cod` AND `".TblModCatalogPropImgTxt."`.lang_id='".$this->lang_id."')
               LEFT JOIN `".TblModCatalogPropSprName."` ON (`".TblModCatalogPropSprName."`.`cod`=`".TblModCatalogProp."`.`id` AND `".TblModCatalogPropSprName."`.`lang_id`='".$this->lang_id."')
               ";
           if(!empty($this->propStr)) $q.=" WHERE `".TblModCatalogProp."`.`id` NOT IN (".$this->propStr.") AND `".TblModCatalogProp."`.`id`<>'".$this->id_prop."'";
           else  $q.=" WHERE  `".TblModCatalogProp."`.`id`<>'".$this->id_prop."'";
           if(!empty($this->id_cat)) $q.=" AND `".TblModCatalogProp."`.`id_cat`='".$this->id_cat."'";
           $q.=" ORDER BY `".TblModCatalogProp."`.`move`";
           if($limit=='limit')
            $q.=" LIMIT ".$this->start.", ".($this->display);

       }
       $res=$this->Right->Query($q, $this->user_id, $this->module);
       if(!$res) return false;
       $rows=$this->Right->db_GetNumRows();
       $this->PropRows=$rows;
       if($limit=='nolimit') return;
       $resArr=array();
       for ($i = 0; $i < $rows; $i++) {
           $row=$this->Right->db_FetchAssoc();
           $resArr[]=$row;
       }
       return $resArr;
   }

   function save(){
       if(!empty($this->add) && is_array($this->add) && count($this->add)>0){
           $q="SELECT `id_prop1` FROM `".$this->tbl."` WHERE `id_prop1`='".$this->id_prop."'";
           $this->db->db_Query($q);
           $move=$this->db->db_GetNumRows();

           $keys=  array_keys($this->add);

           for ($i = 0; $i < count($keys); $i++) {
               $move++;
               $q="INSERT INTO `".$this->tbl."`
                   SET
                   `id_prop1`='".$this->id_prop."',
                   `id_prop2`='".$keys[$i]."',
                   `move`='".$move."'";
               $this->db->db_Query($q);
           }
       }
   }

   function saveMove(){

     $q="DELETE FROM `".$this->tbl."` WHERE `id_prop1`='".$this->id_prop."'";
     $this->db->db_Query($q);
     if(isset($this->propStr)){
           $propArr=  explode(',', $this->propStr);
           $move=0;
           for ($i = 0; $i < count($propArr); $i++) {
               $move++;
               $q="INSERT INTO `".$this->tbl."`
                   SET
                   `id_prop1`='".$this->id_prop."',
                   `id_prop2`='".$propArr[$i]."',
                   `move`='".$move."'";
               $this->db->db_Query($q);
           }
       }
   }

   function delete(){
       if(!empty($this->del) && is_array($this->del) && count($this->del)>0){
           $keys=  array_keys($this->del);
           for ($i = 0; $i < count($keys); $i++) {
               $q="DELETE FROM `".$this->tbl."`
                   WHERE `id_prop1`='".$this->id_prop."'
                   AND `id_prop2`='".$keys[$i]."'";
               $this->db->db_Query($q);
           }
       }
   }


}

?>
