<?php
/**
* CatalogLayout.class.php
* class for display interface of Catalog module
* @package Catalog Package of SEOCMS
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 05.04.2011
* @copyright (c) 2010+ by SEOTM
*/

include_once( SITE_PATH.'/modules/mod_catalog/catalog.defines.php' );

/**
* Class CatalogLayout
* class for display interface of Catalog module.
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 05.04.2011
* @property FrontSpr $Spr
* @property FrontForm $Form
* @property db $db
* @property SystemCurrencies $Currency
*
*/
class CatalogLayout extends Catalog  {

    public $db = NULL;
    public $Msg = NULL;
    public $Form = NULL;
    public $Spr = NULL;
    public $Currency = NULL;
    public $is_tags = NULL;
    public $is_comments = NULL;

    public $task = NULL;


    /**
    * Class Constructor
    *
    * @param $user_id - id of the user
    * @param $module - id of the module
    * @return true/false
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.1, 05.04.2011
    */
    function __construct($user_id=NULL, $module=NULL) {
        //Check if Constants are overrulled
        ( $user_id  !="" ? $this->user_id = $user_id  : $this->user_id = NULL );
        ( $module   !="" ? $this->module  = $module   : $this->module  = 21 );

        $this->lang_id = _LANG_ID;
        ( defined("USE_TAGS") ? $this->is_tags = USE_TAGS : $this->is_tags=0 );
        ( defined("USE_COMMENTS") ? $this->is_comments = USE_COMMENTS : $this->is_comments = 0 );

        if (empty($this->db)) $this->db = DBs::getInstance();
        //if (empty($this->Msg)) $this->Msg = &check_init('ShowMsg', 'ShowMsg');
        if (empty($this->Form)) $this->Form = &check_init('FormCatalog', 'FrontForm', '"form_mod_catalogLayout"');
        if (empty($this->Spr)) $this->Spr = &check_init('FrontSpr', 'FrontSpr', "'$this->user_id', '$this->module'");
        if (empty($this->Currency)) $this->Currency =  &check_init('SystemCurrencies', 'SystemCurrencies');
        if (empty($this->settings)) $this->settings = $this->GetSettings(1);

        $this->multi = &check_init_txt('TblFrontMulti', TblFrontMulti);

        // for folders links
        $this->mod_rewrite = 1;

        //load all data of categories to arrays $this->treeCat, $this->treeCatLevels, $this->treeCatData
        $this->loadTree();
        //echo '<br />treeCatLevels=';print_r($this->treeCatLevels);
        //echo '<br />treeCatData=';print_r($this->treeCatData);

    } // End of CatalogLayout Constructor





    /**
    * Class method ShowCatalogTree
    * Checking show tree of catalog
    * @return html
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.1, 05.01.2011
    */
    function ShowCatalogTree($main = true)
    {
         $this->main_top_level = $this->getTopLevel($this->id_cat);
         ?><div><?$this->showTree(0,0,0,$main);?></div><?
    } //end of function ShowCatalogTree()

    /**
    * Class method showTree
    * Write in html tree of catalog
    * @param array $tree - pointer to array with index as counter
    * @param integer $level - level of catalog
    * @param bool $flag - flag for lyaout
    * @param integer $cnt_sub - count of sublevels
    * @return array with index as counter
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.1, 05.01.2011
    */
    function showTree($level = 0, $flag = 0, $cnt_sub=0,$main = true)
    {
        if( !$this->GetTreeCatLevel($level) ) return $flag;
        $a_tree = $this->GetTreeCatLevel($level);
        //print_r($a_tree);
        if(empty($a_tree)) return $flag;
        $punkt='';
        $class_li='close';
        $parent_level = 0;
        if ($flag == 0)
            $class="";
        else {
            $class="hidden";
            if(!empty($this->id_cat)){
                $res = $this->isCatASubcatOfLevel($this->id_cat, $level);
                //echo '<br />$res = '.$res;
                if($res) $class="active";
            }
        }
        if ($class!= "hidden") {
            echo "<ul>\r\n";
            if($this->id_cat>0) $parent_level = $this->treeCatData[$this->id_cat]['level'];
            //echo '<br/>$parent_level = '.$parent_level;
            //echo '<br/>$class='.$class;
            $keys = array_keys($a_tree);
            $n = count($keys);
            for($i = 0; $i <$n; $i++) {
                if($i%9==0 && $i!=0 && $main){echo "</ul><ul>\r\n";}
                //echo '<br />$keys[$i]='.$keys[$i];
                $row = $this->treeCatData[$keys[$i]];
                //echo '<br />$row=';print_r($row);
                if($row['id']==$this->main_top_level){
                    $class_li="open";
                }
                else{
                    if($row['id']==$this->id_cat OR $row['id']==$parent_level) $class_li="active".$cnt_sub;
                }
                //echo '<br/>$class_li='.$class_li;
                //$href = $this->Link($a_tree[$i]['id']);
                $href = $this->getUrlByTranslit($row['path']);
                $name = $row['name'];
                echo '<li class="'.$class_li.'">';
                $class_a='';
                if($class_li=='open') $class_a='openA';
                $class_li = '';
                if ($this->id_cat == $row['id']){
                    //echo '<br>$cnt_sub='.$cnt_sub;
                    if($cnt_sub>0) echo '<a class="selected '.$class_a.'" href="'.$href.'">'.$name.'</a>';
                    else
                    echo '<a class="selected '.$class_a.'" href="'.$href.'">'.$name.'</a>';
                }
                else
                    echo $punkt.'<a class="'.$class_a.'" href="'.$href.'">'.$name.'</a>';
                //echo '<br>$level='.$level.' $this->id_cat='.$this->id_cat.' $a_tree['.$i.'][level]='.$a_tree[$i]['level'];
                $flag = $this->showTree($row['id'], 1, ($cnt_sub+1));
                echo "</li>\r\n";
            }
            //echo '<br />$flag='.$flag;
            if ($flag != 0)
                echo "</ul>\r\n";
        }
        return $flag;
    } //end of function showTree()

   // ================================================================================================
   // Function : ShowPathToLevel()
   // Version : 1.0.0
   // Date : 21.03.2006
   //
   // Parms :        $id - id of the record in the table
   // Returns :      $str / string with name of the categoties to current level of catalogue
   // Description :  Return as links path of the categories to selected level of catalogue
   // ================================================================================================
   // Programmer :  Igor Trokhymchuk
   // Date : 10.01.2006
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowPathToLevel($level, $str = NULL, $make_link = NULL )
   {
     $devider = '&nbsp/&nbsp';
     if($level>0){
         $tmp_db = DBs::getInstance();
         $row = $this->treeCatData[$level];
         $name = stripslashes($row['name']);
         $link = $this->getUrlByTranslit($row['path']);

         if ( !empty($str) ) {
             $str = '<a href="'.$link.'">'.$name.'</a> '.$devider.' <span class="spanShareName">'.$str."</span>";
         }
         else {
             if( $make_link==1 ) {
                 $str = '<a href="'.$link.'">'.$name.'</a>';
             }
             else $str = $name;
         }
         if ( $row['level']>0 ) {
            return $this->ShowPathToLevel($row['level'], $str, $make_link = NULL );
         }
         //$str = '<a href="'.$script.'&level=0">'.$this->Msg->show_text('TXT_ROOT_CATEGORY').' > </a>'.$str;
         $str = '<a href="'._LINK.'">'.$this->multi['TXT_FRONT_HOME_PAGE'].'</a> '.$devider.' <span class="spanShareName">'.$str."</span>";
     }
     else{
         $str = '<a href="'._LINK.'">'.$this->multi['TXT_FRONT_HOME_PAGE'].'</a> ';
     }
     //echo $str;
     return $str;

     //echo '<a href="'._LINK.'">'.$this->multi['TXT_FRONT_HOME_PAGE'].'</a> '.$devider.' <a href="'._LINK.'catalog/">'.$this->multi['TXT_CATALOG'].'</a> '.$devider.' '.$str;
   } // end of function ShowPathToLevel()

   // ================================================================================================
   // Function : GetSubLevelsLayout()
   // Version : 1.0.0
   // Returns : true,false / Void
   // Description : show sublevels (categories) of catalogue on the front-end
   // Programmer : Yaroslav Gyryn
   // Date : 26.05.2010
   // ================================================================================================
   function GetSubLevelsLayout($level){
       $tmp_db = DBs::getInstance();
       $q = "SELECT
                    `".TblModCatalogSprName."`.name
                FROM
                    `".TblModCatalog."`, `".TblModCatalogSprName."`
                WHERE
                    `".TblModCatalog."`.id=`".TblModCatalogSprName."`.cod
                AND
                    `".TblModCatalogSprName."`.lang_id='"._LANG_ID."'
                AND
                    `".TblModCatalog."`.level = '$level'
                AND
                    `".TblModCatalog."`.visible = '2'
                ORDER BY
                    `".TblModCatalog."`.move ";
       $res = $tmp_db->db_Query( $q );
       //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
       //if ( !$res OR !$tmp_db->result ) return false;
       $rows = $tmp_db->db_GetNumRows();
       $arr_row='';
       for ($i=0; $i<$rows; $i++) {
           $row = $tmp_db->db_FetchAssoc();
           $name = stripslashes($row['name']);    //$this->Spr->GetNameByCod( TblModCatalogSprName, $row ['id'] );
           if ( empty($arr_row) )
                $arr_row = $name ;
           else
            $arr_row = $arr_row.', '.$name ;
       }
        return $arr_row;
   }//end of function GetSubLevelsLayout()

    // ================================================================================================
    // Function : ShowMainCategories()
    // Version : 1.0.0
    // Date : 21.10.2009
    // Parms: $level
    // Returns : true,false / Void
    // Description : show main levels (categories) of catalogue on the front-end
    // Programmer : Yaroslav Gyryn
    // Date : 21.10.2009
    // ================================================================================================
    function ShowMainCategories($cols = 5)
    {
        switch($cols){
            case '1':
                $width = 'width100';
                break;
            case '2':
                $width = 'width50';
                break;
            case '3':
                $width = 'width32';
                break;
            case '4':
                $width = 'width25';
                break;
            case '5':
                $width = 'width20';
                break;

        }
        $path = $this->ShowPathToLevel(0);
        $this->Form->WriteContentHeader($this->multi['TXT_FRONT_CATALOG_MAIN_TEXT'], false,$path);
        
        $this->Form->WriteContentFooter();

    } // end of function ShowMainCategories()

    function ShowSubLevels($id_cat = NULL){
        $array_sub = $this->GetTreeCatLevel($id_cat);
        $n = 0;
        if(is_array($array_sub)){
            ?><div class="listCat"><?
            $keys = array_keys($array_sub);
            $n = count($keys);
            for($i=0;$i<$n;$i++){
                $row = $this->GetTreeCatData($keys[$i]);
                $img_cat = $row['img_cat'];
                $name = stripslashes($row['name']);
                $link = $this->getUrlByTranslit($row['path']);
                if(($i)%3==0 && $i!=0){?></div><div class="BlockPolosa" style="margin-bottom: 25px;"></div><div class="listCat"><?}
                ?><div class="listCatOneItem"<?if(($i+1)%3==0){?> style="margin: 0;"<?}?>>
                    <div class="listCatOneItemImgFon">
                        <div class="listCatOneItemImg">
                            <a href="<?=$link;?>" title="<?=htmlspecialchars($name);?>">
                                <?=$this->ShowCurrentImageCat($img_cat,224,224);?>
                            </a>
                        </div>    
                    </div>
                    <div class="listCatOneItemText">
                        <div class="listCatOneItemName">
                            <a href="<?=$link;?>" title="<?=htmlspecialchars($name);?>"><?=$name;?></a>
                        </div>
                        <div class="listCatOneItemCnt">В комплект входит: <span>5 элементов</span></div>
                        <div class="listCatOneItemDetail">
                            <a href="<?=$link;?>" title="<?=$this->multi['TXT_DETAILS']?>">
                                <span><?=$this->multi['TXT_DETAILS']?></span>
                            </a>
                        </div>
                    </div>
                </div><?
            }// end for
            ?></div><?
        }
        return $n;
    }
    
    // ================================================================================================
    // Function : ShowContentCurentLevel()
    // Date : 05.04.2006
    // Returns : true,false / Void
    // Description : show content of curent level of catalogue on the front-end
    // Programmer : Igor Trokhymchuk
    // ================================================================================================
    function ShowContentCurentLevel()
    {
        $cat_data = $this->GetTreeCatData($this->id_cat);
        $this->Form->WriteContentHeader(false, false,$this->ShowPathToLevel($this->id_cat));
        //print_r($this->treeCatData);
        if ( isset($this->settings['cat_descr']) AND $this->settings['cat_descr']=='1' AND $this->page<2 ){
            $descr = stripslashes($this->treeCatData[$this->id_cat]['descr']);
            if(!empty($descr)){
                ?><div class="categoryShort"><?=$descr?></div><?
            }
        }
        ?><div><?=$this->treeCatData[$this->id_cat]['descr']?></div><?
        $cnt = $this->ShowSubLevels($this->id_cat);
        if(!isset($this->isContent))    
            $this->isContent = $this->IsContent($this->id_cat);
         if( $this->isContent > 0) {
             $this->showSorePanel();?>
             
            <div id="sort_content">
               <?
                $this->ShowListOfContentByPages($this->GetListPositionsSortByDate($this->id_cat, 'limit', $this->sort, $this->asc_desc, true, $this->id_param));

               //показывем доп. описание категории только для первой странцы. Если же при постраничности перешли на вторую страницу и далее,
               //то описание не показывать, что бы один и тот же текст не дублитровался при постраничености.
               if ( isset($this->settings['cat_descr2']) AND $this->settings['cat_descr2']=='1' AND $this->page<2 ){
                   ?><br /><?=stripslashes($this->treeCatData[$this->id_cat]['descr2']);
               }
               ?>
            </div>
         <?
         }
         else {
             if(empty($cnt)){
                ?><div class="err"><?
                    echo $this->multi['MSG_ERR_NO_ANY_POSITIONS_IN_CATEGORY'].' <br/><a href="javascript:history.back()">'.$this->multi['TXT_FRONT_GO_BACK'].'</a>';
                ?></div><?
             }
         }

        $this->Form->WriteContentFooter();
    } //end of function ShowContentCurentLevel()

    function showSorePanel(){
        ?><div id="sorePanel">
            <div class="sortPrice">
                <div class="nameSorePanel">Показать:</div>
                <div class="contentSorePanel">
                    <div class="nameSore">от дешевых к дорогим</div>
                </div>
            </div>
            <div class="sortStatus">
                <div class="nameSoreFon"><div class="nameStatus">Топ</div></div>
                <div class="nameSoreFon">&nbsp;|&nbsp;</div>
                <div class="nameSoreFon"><div class="nameStatus">Новинки</div></div>
                <div class="nameSoreFon">&nbsp;|&nbsp;</div>
                <div class="nameSoreFon"><div class="nameStatus">Распродажа</div></div>
            </div>
        </div><?
    }
    
    // ================================================================================================
    // Function : ShowLevelsName()
    // Date : 04.05.2006
    // Returns : true,false / Void
    // Description : show levels (categories) of catalogue on the front-end
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function ShowLevelsName(&$tree, $cols = 5)
    {
        if( !is_array($tree)) return;

        $settings = $this->settings;
        switch($cols){
            case '1':
                $width = 'width100';
                break;
            case '2':
                $width = 'width50';
                break;
            case '3':
                $width = 'width32';
                break;
            case '4':
                $width = 'width25';
                break;
            case '5':
                $width = 'width20';
                break;

        }
        ?>
        <div class="floatContainer">
        <?
        $rows =count($tree);
        $keys = array_keys($tree);
        for($i=0; $i<$rows; $i++){
          $id_cat = $keys[$i];
          //echo '<br />$id_cat='.$id_cat;
          $cat_data = $this->GetTreeCatData($id_cat);
          $name = stripslashes($cat_data['name']);
          $img_cat = stripslashes($cat_data['img_cat']);
          $descr = stripslashes($cat_data['descr']);
          //$descr2 = stripslashes($row['descr2']);
          ?>
              <!-- show Name of the category -->
              <?
               $link = $this->getUrlByTranslit($cat_data['path'], NULL);
              ?>
              <div class="item floatToLeft <?=$width;?>">
                <a href="<?=$link;?>" title="<?=addslashes($name);?>"><?=$name;?></a>
              </div>
              <!-- show Image of the category -->
              <?//if (!empty($img_cat)) { echo $this->ShowCurrentImage($settings['img_path']."/categories/".$img_cat, 'size_auto=75', 85, NULL, "border=0");}?>
              <!-- show Description of the category -->
              <?//=$descr;?>
          <?
        }// end for
        ?>
        </div>
        <?
    } // end of function  ShowLevelsName()


    // ================================================================================================
    // Function : ShowLevelsNameShort()
    // Date : 04.05.2006
    // Returns : true,false / Void
    // Description : show levels (categories) of catalogue on the front-end
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function ShowLevelsNameShort(&$tree, $cols = 5)
    {
        if( !is_array($tree)) return;

        $settings = $this->settings;
        switch($cols){
            case '1':
                $width = 'width100';
                break;
            case '2':
                $width = 'width50';
                break;
            case '3':
                $width = 'width32';
                break;
            case '4':
                $width = 'width25';
                break;
            case '5':
                $width = 'width20';
                break;

        }
        ?>
        <div class="listCategoryItemShort">
        <?
        $rows =count($tree);
        $keys = array_keys($tree);
        for($i=0; $i<$rows; $i++){
          $id_cat = $keys[$i];
          //echo '<br />$id_cat='.$id_cat;
          $cat_data = $this->GetTreeCatData($id_cat);
          $name = stripslashes($cat_data['name']);
          $img_cat = stripslashes($cat_data['img_cat']);
          //$descr = stripslashes($cat_data['descr']);
          //$descr2 = stripslashes($row['descr2']);
          ?>
              <!-- show Name of the category -->
              <?
               if(!empty($cat_data['href'])) $link = _LINK.$cat_data['href'];
               else $link = $this->getUrlByTranslit($cat_data['path'], NULL);
              ?>
              <div class="item floatToLeft <?=$width;?>">
                <a href="<?=$link;?>" title="<?=addslashes($name);?>"><?=$name;?></a>
              </div>
              <!-- show Image of the category -->
              <?//if (!empty($img_cat)) { echo $this->ShowCurrentImage($settings['img_path']."/categories/".$img_cat, 'size_auto=75', 85, NULL, "border=0");}?>
              <!-- show Description of the category -->
              <?//=$descr;?>
          <?
        }// end for
        ?>
        </div>
        <?
    } // end of function  ShowLevelsNameShort()


/*************************************************************************************************************/

    /**
     * CatalogLayout::ParamShowPricePanel()
     * Show Price Panel filter
     * @author Yaroslav Gyryn
     * @return void
     */
    function ParamShowPricePanel(){
        $btnSaveChanges = 'Ок';
        ?>
        <div class="paramBlock">
            <div class="paramName">Цена:</div>
            <form action="" method="post" name="priceLevels">
            <table cellpadding="2" cellspacing="0" border="0" class="tblPriceLevel">
                <tr>
                    <td title="Грн"> От:<input type="text" value="<?=$this->from;?>" name="from"  maxlength="8" size="4" onkeypress="return me()"/></td>
                    <td title="Грн"> до:<input type="text" value="<?=$this->to;?>" name="to"  maxlength="8" size="4" onkeypress="return me()"/></td>
                    <td>&nbsp;<input type="image" src="/images/design/btnOk.gif" alt="<?=$btnSaveChanges;?>" title="<?=$btnSaveChanges;?>"/></td>
                </tr>
            </table>
            </form>
        </div>
        <?
    }

    /**
     * CatalogLayout::makeIdPropStr()
     *
     * @param mixed $propArrNoLimit
     * @return
     */
    function makeIdPropStr($propArrNoLimit){
       $str="";
       if($propArrNoLimit!=false){
           $str=implode(",", $propArrNoLimit);
//       foreach ($propArrNoLimit as $key=>$value) {
//           $str.=",".$value['id'];
//           echo $value;
//       }
//       $str[0]=" ";
       }else return false;
       return $str;
   }


   /**
    * CatalogLayout::GetParamsPropVal()
    *
    * @param mixed $table
    * @param mixed $lang_id
    * @param mixed $paramId
    * @param mixed $IdOfProps
    * @return
    */
   function GetParamsPropVal($lang_id, $id_cat, $paramId,$IdOfProps){
       $q="SELECT `".TblModCatalogParamsVal."`.*";
       if(strlen($IdOfProps)!=0){
        $q.="  , (
                SELECT count(*)
                FROM `".TblModCatalogParamsProp."`
                WHERE (`".TblModCatalogParamsProp."`.`id_param`=".$paramId."
                AND `".TblModCatalogParamsProp."`.`val`=`".TblModCatalogParamsVal."`.`cod`)
                AND `".TblModCatalogParamsProp."`.`id_prop` IN (".$IdOfProps.")
                AND `".TblModCatalogParamsVal."`.`id_cat`='".$id_cat."'
                AND `".TblModCatalogParamsVal."`.`id_param`='".$paramId."'
             ) AS countOfProp ";
       }
         $q.= "FROM
                `".TblModCatalogParamsVal."`
           WHERE
                `lang_id`=".$lang_id."
                AND `id_cat`='".$id_cat."'
                AND `id_param`='".$paramId."'
           ";
       $res = $this->db->db_Query( $q );
       //echo $q."<br/><br/><br/>";
       //echo '<br> $q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
       if ( !$res or !$this->db->result ) return false;
       $rows = $this->db->db_GetNumRows();
       $arr=array();
       for($i=0;$i<$rows;$i++){
           $row = $this->db->db_FetchAssoc();
           $arr[$row['move']]['cod']=$row['cod'];
           $arr[$row['move']]['name']=$row['name'];
           $arr[$row['move']]['move']=$row['move'];
           $arr[$row['move']]['mtitle']=$row['mtitle'];
           $arr[$row['move']]['mdescr']=$row['mdescr'];
           if(isset($row['countOfProp']))
           $arr[$row['move']]['countOfProp']=$row['countOfProp'];

       }
       //print_r($arr);
       return $arr;

   }



    /**
     * CatalogLayout::generateIdPropArra()
     *
     * @return
     */
    function generateIdPropArra(){
       $array[0]=$this->GetListPositionsSortByDateCount($this->id_cat, 'nolimit', true);

       if(is_array($this->arr_current_img_params_value)){
           foreach($this->arr_current_img_params_value as $key=>$value) {
               $array[$key]=$this->GetListPositionsSortByDateCount($this->id_cat, 'nolimit', true,$key);
           }
       }
       return $array;
   }

   /**
    * CatalogLayout::makeParamLink()
    *
    * @param mixed $arr
    * @param mixed $paramId
    * @param mixed $paramVal
    * @return
    */
   function makeParamLink($arr,$paramId,$paramVal){
       //print_r($arr);
       $param_str="";
       if(isset($arr[$paramId])){
         //  echo '$paramId='.$paramId.' $paramVal='.$paramVal;
         $subarr=explode(",", $arr[$paramId]);
         $flag=false;
         foreach ($subarr as $key => $value) {
             if($value==$paramVal){
                 unset($subarr[$key]);
                 $flag=true;
             }
         }
         $arr[$paramId]=implode(",", $subarr);
         if(!$flag)
           $arr[$paramId].=",".$paramVal;
       }
       else{
           $arr[$paramId]=$paramVal;
       }
       //print_r($arr);
       foreach ($arr as $key => $value) {
           if(!empty($value)){
                  $param ='&'.PARAM_VAR_NAME.PARAM_VAR_SEPARATOR.$key.'='.$value;
                  $param_str .= $param;
           }
       }
       return $param_str;
   }


    /**
     * CatalogLayout::ShowSelectedFilters()
     *
     * @return
     */
    function ShowSelectedFilters()
    {
       $str = NULL;
       $param_str = NULL;
       $this->url_param = NULL;
       $param = NULL;
       $filtr = NULL;
       $id_cat = $this->id_cat;

       if(!isset($this->params_row))
            $this->params_row = $this->GetParams($id_cat);
       //print_r($this->params_row);
       if(!empty($this->treeCatData[$id_cat]['href']))
            $this->catLink = $this->getUrlByTranslit($this->treeCatData[$id_cat]['href']);
       else
            $this->catLink = $this->getUrlByTranslit($this->treeCatData[$id_cat]['path']);

       if(!isset($this->propArrNoLimit))
            $this->propArrNoLimit = $this->generateIdPropArra();
       $this->countOfPropNoLimit=count($this->propArrNoLimit[0]);
       $IdOfProps=$this->makeIdPropStr($this->propArrNoLimit[0]);

       $n = count($this->params_row);
       $counter=0;
       for ($i=0; $i<$n; $i++) {
           if($this->params_row[$i]['modify']!=1) //Отображать в блоке параметров
                continue;
           $val = NULL;
           switch ($this->params_row[$i]['type'] ) {
                case '1':
                    //$val = $v;
                    break;
               case '2':
                    $val = $this->Spr->GetListName( TblSysLogic, $this->lang_id, 'array', 'move', 'asc', 'all' );
                    break;
               case '3':
               case '4':
                   if(isset($this->propArrNoLimit[$this->params_row[$i]['id']])){
                       $IdOfProps1=$this->makeIdPropStr($this->propArrNoLimit[$this->params_row[$i]['id']]);
                       $val = $this->GetParamsPropVal($this->lang_id, $this->params_row[$i]['id_categ'], $this->params_row[$i]['id'],$IdOfProps1);
                   }
                   else{
                       $val = $this->GetParamsPropVal($this->lang_id, $this->params_row[$i]['id_categ'], $this->params_row[$i]['id'],$IdOfProps);
                   }
                    break;
           /*  case '5':
                    $val = $v;
                    break;*/
           }
           $prefix ='';
           $sufix = '';
          /*$prefix = $this->Spr->GetNameByCod(TblModCatalogParamsSprPrefix,($this->params_row[$i]['id']), $this->lang_id, 1);
          $sufix = $this->Spr->GetNameByCod(TblModCatalogParamsSprSufix,($this->params_row[$i]['id']), $this->lang_id, 1);*/
          if( is_array($val) ) {

              $showAll=false;
              // Формирование строки параметров
              $param_arr=array();
              if( is_array($this->arr_current_img_params_value) ) {
                  $param_str = NULL;
                  foreach($this->arr_current_img_params_value as $key=>$value) {
                    $param_arr[$key]=$value;

                    // Формирование ссылки для постраничности
                    if($key!=$this->params_row[$i]['id'] AND !empty($value)) {
                           $param ='&'.PARAM_VAR_NAME.PARAM_VAR_SEPARATOR.$key.'='.$value;
                           $param_str .= $param;
                           //echo '<br/>$param = '.$param.'<br/>';
                           if(substr_count($this->url_param, $param)==0)
                                $this->url_param .= $param;
                      }
                      elseif ($key==$this->params_row[$i]['id'] AND !empty($value) and count($this->arr_current_img_params_value)==1){
                           $this->url_param =PARAM_VAR_NAME.PARAM_VAR_SEPARATOR.$key.'='.$value;
                      }
                      if(!empty($this->url_param) and $str ==null) {
                        $str ='<div class="selectedParams">';
                        $str.='<div id="allCount"></div>';
                        /*$tow = $this->countOfPropNoLimit;
                        if($tow==1 or $tow==21 or $tow==31 or $tow==41 or $tow==51  or $tow==61 or $tow==71 or $tow==81 or $tow==91)
                            $tovar = $this->multi['FLD_PRODUCT'];
                        elseif ($tow==2 or $tow==3 or $tow==4 or $tow==22 or $tow==23 or $tow==24 or $tow==32 or $tow==33 or $tow==34 or $tow==42 or $tow==43 or $tow==44 )
                               $tovar = $this->multi['FLD_PRODUCTA'];
                            else
                                $tovar = $this->multi['FLD_PRODUCTOV'];

                        $str.='<div class="countParams">Выбрано <b>'.$tow.'</b> '.$tovar.'</div>';*/
                      }
                  }
              }

              foreach($val as $k=>$v) {

                 // Форматированный вывод текста либо ссылки параметра
                $checked = false;
                if( is_array($this->arr_current_img_params_value) ){
                    foreach($this->arr_current_img_params_value as $key=>$value){
                        $subArr=explode (",", $value);
                            foreach ($subArr as $key1=>$value1) {
                                if($key==$this->params_row[$i]['id'] AND $value1==$v['cod'] ) {
                                    $checked=true;
                                    //break;
                                }
                            }
                    }
                }
                //echo '<br>$v[countOfProp]='.$v['countOfProp'];
                if(isset($v['countOfProp'])){
                    if(isset($this->arr_current_img_params_value[$this->params_row[$i]['id']]))
                    $countOfProp="+".($v['countOfProp']);
                    else $countOfProp=$v['countOfProp'];
                }
                else $countOfProp=0;
                if($checked==true) {
                    $paramLink=$this->makeParamLink($param_arr,$params_row[$i]['id'],$v['cod']);
                    if(strlen($paramLink)>0) $paramLink[0]="?";
                    $str .='<a class="btnDeleteParam" title="Сбросить" href="'.$this->catLink.$paramLink.'">'.$prefix.' '.$v['name'].' '.$sufix.'</a><br/> ';
                }
              }
          }
       }

      if(isset($this->from) and isset($this->to)) {
            $str .='<a class="btnDeleteParam" title="Сбросить" href="">'.'от '.$this->from.' до '.$this->to.' грн.</a><br/>';
        }

      // Вывод ссылки "Все"
      if(!empty($this->url_param)) {
        $str .='<a class="filters_off" href="'.$this->catLink.'">Сбросить все фильтры</a>';
        $str .= '</div>';
      }
       return $str;
   } //end of function ShowSelectedFilters()



    /**
     * CatalogLayout::ShowAllFilters()
     * Description : return names & values of parameters in string
     * @return $str
     */
    function ShowAllFilters()
    {
       $str = NULL;
       $param_str = NULL;
       $this->url_param = NULL;
       $param = NULL;
       $filtr = NULL;
       $sorting ='';
       $this->priceLevels='';
       $id_cat = $this->id_cat;
       if(!isset($this->params_row))
            $this->params_row = $this->GetParams($id_cat);
       //$this->catLink = $this->Link($this->id_cat);
       if(!isset($this->catLink)) {
           if(!empty($this->treeCatData[$id_cat]['href']))
                $this->catLink = $this->getUrlByTranslit($this->treeCatData[$id_cat]['href']);
           else
                $this->catLink = $this->getUrlByTranslit($this->treeCatData[$id_cat]['path']);
       }

       if(!isset($this->propArrNoLimit))
            $this->propArrNoLimit = $this->generateIdPropArra();
         $this->countOfPropNoLimit=count($this->propArrNoLimit[0]);
        $IdOfProps=$this->makeIdPropStr($this->propArrNoLimit[0]);

      if(!empty($this->sort) )
        $sorting ='&sort='.$this->sort.'&asc_desc='.$this->asc_desc.'&exist='.$this->exist;

      if(!empty($this->from)  and !empty($this->to) )
            $this->priceLevels = '&from='.$this->from.'&to='.$this->to;

       $n = count($this->params_row);
       $counter=0;

       for ($i=0; $i<$n; $i++) {
           if($this->params_row[$i]['modify']!=1) //Отображать в блоке параметров
                continue;
           $val = NULL;
           $paramName = $this->Spr->GetNameByCod(TblModCatalogParamsSprName,($this->params_row[$i]['id']), $this->lang_id, 1);

           $str .='<div class="paramBlock"><div class="paramName">'.$paramName .':</div>';
           //$tblname = $this->BuildNameOfValuesTable($this->params_row[$i]['id_categ'], $this->params_row[$i]['id']);
           switch ($this->params_row[$i]['type'] ) {
               case '1':
                        //$val = $v;
                        break;
                   case '2':
                        $val = $this->Spr->GetListName( TblSysLogic, $this->lang_id, 'array', 'move', 'asc', 'all' );
                        break;
                   case '3':
                   case '4':
                       if(isset($this->propArrNoLimit[$this->params_row[$i]['id']])){
                           $IdOfProps1=$this->makeIdPropStr($this->propArrNoLimit[$this->params_row[$i]['id']]);
                           $val = $this->GetParamsPropVal($this->lang_id, $this->params_row[$i]['id_categ'], $this->params_row[$i]['id'],$IdOfProps1);
                       }else{

                           $val = $this->GetParamsPropVal($this->lang_id, $this->params_row[$i]['id_categ'], $this->params_row[$i]['id'],$IdOfProps);
                       }
                        break;
                 /*  case '5':
                        $val = $v;
                        break;*/
           }

          $prefix = $this->Spr->GetNameByCod(TblModCatalogParamsSprPrefix,($this->params_row[$i]['id']), $this->lang_id, 1);
          $sufix = $this->Spr->GetNameByCod(TblModCatalogParamsSprSufix,($this->params_row[$i]['id']), $this->lang_id, 1);

          $str .= '<div class="paramKey">';
          if( is_array($val) ) {
              $showAll=false;

              // Формирование строки параметров
              $param_arr=array();
              if( is_array($this->arr_current_img_params_value) ) {
                  $param_str = NULL;
                  foreach($this->arr_current_img_params_value as $key=>$value) {
                    $param_arr[$key]=$value;

                    // Формирование ссылки для постраничности
                    if($key!=$this->params_row[$i]['id'] AND !empty($value)) {
                           $param ='&'.PARAM_VAR_NAME.PARAM_VAR_SEPARATOR.$key.'='.$value;
                           $param_str .= $param;
                           //echo '<br/>$param = '.$param.'<br/>';
                           if(substr_count($this->url_param, $param)==0)
                                $this->url_param .= $param;
                      }
                      elseif ($key==$this->params_row[$i]['id'] AND !empty($value) and count($this->arr_current_img_params_value)==1){
                           $this->url_param =PARAM_VAR_NAME.PARAM_VAR_SEPARATOR.$key.'='.$value;
                      }
                  }
              }

              foreach($val as $k=>$v) {
                 // Форматированный вывод текста либо ссылки параметра
                $checked = false;
                if( is_array($this->arr_current_img_params_value) ){
                    foreach($this->arr_current_img_params_value as $key=>$value){
                        $subArr=explode (",", $value);//print_r($subArr);
                            foreach ($subArr as $key1=>$value1) {
                                if($key==$this->params_row[$i]['id'] AND $value1==$v['cod'] ) {
                                    $checked=true;
                                    //break;
                                }
                            }
                    }
                }
                if(isset($v['countOfProp'])){
                    if(isset($this->arr_current_img_params_value[$this->params_row[$i]['id']]))
                    $countOfProp="+".($v['countOfProp']);
                    else $countOfProp=$v['countOfProp'];
                }else $countOfProp=0;
                $paramLink=$this->makeParamLink($param_arr,$this->params_row[$i]['id'],$v['cod']);
                if(strlen($paramLink)>0) $paramLink[0]="?";
                if($countOfProp>0){
                if($checked==true) {
                    $showAll=true;
                    $str .='<a class="paramSelected" href="'.$this->catLink.$paramLink.'">'.$prefix.' '.$v['name'].' '.$sufix.'</a><br/> ';
                }
                else $str .='<a href="'.$this->catLink.$paramLink.'">'.$prefix.' '.$v['name'].' '.$sufix.' ('.$countOfProp.')</a><br/> ';
                }
                else{
                    if($checked){
                        $str .='<a class="paramSelected" href="'.$this->catLink.$paramLink.'">'.$prefix.' '.$v['name'].' '.$sufix.'</a><br/> ';
                    }else $str .='<span class="param_all" href="'.$this->catLink.$paramLink.'">'.$prefix.' '.$v['name'].' '.$sufix.' (0)</span><br/> ';
                }
              }
          }

          $str .= '</div></div>';
       }

       return $str;
   } //end of function ShowAllFilters()


    // ================================================================================================
    // Function : GetListPositionsSortByDateCount()
    // Date : 23.05.2010
    // Parms :       $level - id of the category
    //               $limit - select all rows or with limit (for show by pages)
    //               $show_sublevels - select posotion from sublevels of $level or not (can be treu or false)
    // Returns :      true/false
    // Description :  get list of positions sort by date
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
     function GetListPositionsSortByDateCount($level=0, $limit='limit', $show_sublevels=false , $vithoutParam=NULL)
    {
         $paramQuery=NULL;
         $flag=true;
          if( is_array($this->arr_current_img_params_value) ) { // Выборка по параметрам
              if($vithoutParam!=NULL && count($this->arr_current_img_params_value)==1 && isset($this->arr_current_img_params_value[$vithoutParam])) $flag=false;
              if($flag){
              $paramQuery = "
                SELECT `".TblModCatalogParamsProp."`.id_prop
                    FROM  `".TblModCatalogParamsProp."`
                    WHERE ";
                $i=0;
                foreach($this->arr_current_img_params_value as $key=>$value) {
                    //if($vithoutParam!=NULL && $vithoutParam==$key && count($this->arr_current_img_params_value)==1) $flag=false;
                    if($vithoutParam==$key) continue;
                    if($i==0) {
                        $paramQuery.=" (`".TblModCatalogParamsProp."`.id_param='".$key."' AND
                                        `".TblModCatalogParamsProp."`.val in (".$value."))";
                    }
                    else {
                        $paramQuery.=" AND `".TblModCatalogParamsProp."`.id_prop IN (
                        SELECT `".TblModCatalogParamsProp."`.id_prop
                        FROM  `".TblModCatalogParamsProp."`
                        WHERE `".TblModCatalogParamsProp."`.id_param='".$key."' AND
                              `".TblModCatalogParamsProp."`.val in (".$value."  ))";
                    }
                    $i++;

                }
                $res = $this->db->db_Query( $paramQuery );
                //echo '<br>'.$paramQuery.'<br> res='.$res.'<br> $this->db->result='.$this->db->result;
//                if( !$res) return false;
                $num_rows = $this->db->db_GetNumRows($res);

                $my_id_prop ='';
                for($i=0;$i<$num_rows;$i++){
                   $value = $this->db->db_FetchAssoc();

                   if($i!=0)
                        $my_id_prop = $my_id_prop.', ';
                    $my_id_prop .= $value['id_prop'];
                }
        }
    }
        //echo $my_id_prop."<br/>";
        //echo '$paramQuery="'.$paramQuery.'"';
        if(isset($this->settings['multi_categs']) AND $this->settings['multi_categs']==1){
            $multi_levels_left_join = " LEFT JOIN `".TblModCatalogPropMultiCategs."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropMultiCategs."`.`id_prop`)";
        }
        else $multi_levels_left_join = '';
        $q = "SELECT DISTINCT
                `".TblModCatalogProp."`.id
              FROM `".TblModCatalogProp."`
                $multi_levels_left_join,
                `".TblModCatalogPropSprName."`,`".TblModCatalogSprName."`, `".TblModCatalog."`, `".TblModCatalogTranslit."`
              WHERE `".TblModCatalogProp."`.`id_cat`=`".TblModCatalog."`.`id`
              AND `".TblModCatalogProp."`.visible='2'
              AND `".TblModCatalog."`.`visible`='2'
              AND `".TblModCatalogProp."`.id=`".TblModCatalogPropSprName."`.cod
              AND `".TblModCatalogProp."`.id_cat=`".TblModCatalogSprName."`.cod
              AND `".TblModCatalogPropSprName."`.lang_id='".$this->lang_id."'
              AND `".TblModCatalogSprName."`.lang_id='".$this->lang_id."'
              AND `".TblModCatalogProp."`.id=`".TblModCatalogTranslit."`.`id_prop`
              AND `".TblModCatalogTranslit."`.`lang_id`='".$this->lang_id."'
             ";

        if(!empty($paramQuery))
              $q.="AND `".TblModCatalogProp."`.id IN (".$my_id_prop.") ";
        if($show_sublevels) {
            $str_sublevels = $this->getSubLevels($level);
            //echo '<br />$str_sublevels='.$str_sublevels;
            if(empty($str_sublevels)) $str_sublevels = $level;
            else $str_sublevels = $level.','.$str_sublevels;
            $categ_filter = " `".TblModCatalogProp."`.id_cat IN (".$str_sublevels.")";
        }
        elseif($level>0) $categ_filter = " `".TblModCatalogProp."`.id_cat='".$level."'";
        else $categ_filter = '';

        if( isset($this->settings['multi_categs']) AND $this->settings['multi_categs']==1 ){
            if( !empty($categ_filter) ) $q = $q."  AND (".$categ_filter." OR `".TblModCatalogPropMultiCategs."`.`id_cat`='".$level."') ";
            else $q = $q."  AND  `".TblModCatalogPropMultiCategs."`.`id_cat`='".$level."' ";
        }
        else $q = $q." AND ".$categ_filter;

        $q = $q." ORDER BY `".TblModCatalogProp."`.`dt` desc, `".TblModCatalogProp."`.`move` asc ";
        if($limit=='limit') $q = $q." LIMIT ".$this->start.", ".($this->display);
        $res = $this->db->db_Query( $q );
        //echo '<br>'.$q.'<br> res='.$res.'<br> $this->db->result='.$this->db->result;
        if( !$res or !$this->db->result) return false;
        $rows = $this->db->db_GetNumRows($res);
        //echo '<br>$rows='.$rows;
        $arr=array();
        for($i=0;$i<$rows;$i++){
            $row = $this->db->db_FetchAssoc();
            $arr[$i]=$row['id'];
        }
        //print_r($arr);
        return $arr;
    }//end of function GetListPositionsSortByDateCount()

    // ================================================================================================
    // Function : GetListPositionsSortByDate()
    // Date : 23.05.2010
    // Parms :       $level - id of the category
    //               $limit - select all rows or with limit (for show by pages)
    //               $show_sublevels - select posotion from sublevels of $level or not (can be treu or false)
    // Returns :      true/false
    // Description :  get list of positions sort by date
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function GetListPositionsSortByDate($level=0, $limit='limit',  $sort = NULL, $asc_desc = "asc", $show_sublevels=false, $idParam = NULL)
    {
          $paramQuery=NULL;
          if( is_array($this->arr_current_img_params_value) ) { // Выборка по параметрам
                $paramQuery = "
                SELECT `".TblModCatalogParamsProp."`.id_prop
                    FROM  `".TblModCatalogParamsProp."`
                    WHERE ";
                $i=0;
                foreach($this->arr_current_img_params_value as $key=>$value) {
                    if($i==0) {
                        $paramQuery.=" (`".TblModCatalogParamsProp."`.id_param='".$key."' AND
                                        `".TblModCatalogParamsProp."`.val in (".$value."))";
                    }
                    else {
                        $paramQuery.=" AND `".TblModCatalogParamsProp."`.id_prop IN (
                        SELECT `".TblModCatalogParamsProp."`.id_prop
                        FROM  `".TblModCatalogParamsProp."`
                        WHERE `".TblModCatalogParamsProp."`.id_param='".$key."' AND
                              `".TblModCatalogParamsProp."`.val in (".$value."  ))";
                    }
                    $i++;

                }
                $res = $this->db->db_Query( $paramQuery );
                //echo '<br>'.$paramQuery.'<br> res='.$res.'<br> $this->db->result='.$this->db->result;

                if( !$res) return false;
                $num_rows = $this->db->db_GetNumRows($res);

                $my_id_prop ='';
                for($i=0;$i<$num_rows;$i++){
                   $value = $this->db->db_FetchAssoc();

                   if($i!=0)
                        $my_id_prop = $my_id_prop.', ';
                    $my_id_prop .= $value['id_prop'];
                }
        }
        if(isset($this->settings['multi_categs']) AND $this->settings['multi_categs']==1){
            $multi_levels_left_join = " LEFT JOIN `".TblModCatalogPropMultiCategs."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropMultiCategs."`.`id_prop`)";
        }
        else $multi_levels_left_join = '';

        if( !empty($this->id_param)){
            $select_param_val = ", `".TblModCatalogParamsProp."`.val";
            $left_join_param_val = " LEFT JOIN `".TblModCatalogParamsProp."` ON (`".TblModCatalogProp."`.`id`=`".TblModCatalogParamsProp."`.`id_prop` AND `".TblModCatalogParamsProp."`.`id_param`='".$this->id_param."')";
        }
        else {
            $select_param_val = '';
            $left_join_param_val = '';
        }

        $q = "SELECT DISTINCT
                `".TblModCatalogProp."`.*,
                `".TblModCatalogPropSprName."`.name,
                `".TblModCatalogSprName."`.name as cat_name,
                `".TblModCatalogTranslit."`.`translit`,
                `".TblModCatalogPropImg."`.`path` AS `first_img`,
                `".TblModCatalogPropImgTxt."`.`name` AS `first_img_alt`,
                `".TblModCatalogPropImgTxt."`.`text` AS `first_img_title`
                $select_param_val
              FROM `".TblModCatalogProp."`
                LEFT JOIN `".TblModCatalogPropImg."` ON (`".TblModCatalogProp."`.`id`=`".TblModCatalogPropImg."`.`id_prop` AND `".TblModCatalogPropImg."`.`move`='1' AND `".TblModCatalogPropImg."`.`show`='1')
                LEFT JOIN `".TblModCatalogPropImgTxt."` ON (`".TblModCatalogPropImg."`.`id`=`".TblModCatalogPropImgTxt."`.`cod` AND `".TblModCatalogPropImgTxt."`.lang_id='".$this->lang_id."')
                $multi_levels_left_join
                $left_join_param_val,
                `".TblModCatalogPropSprName."`,`".TblModCatalogSprName."`, `".TblModCatalog."`, `".TblModCatalogTranslit."`
              WHERE `".TblModCatalogProp."`.`id_cat`=`".TblModCatalog."`.`id`
              AND `".TblModCatalogProp."`.visible='2'
              AND `".TblModCatalog."`.`visible`='2'
              AND `".TblModCatalogProp."`.id=`".TblModCatalogPropSprName."`.cod
              AND `".TblModCatalogProp."`.id_cat=`".TblModCatalogSprName."`.cod
              AND `".TblModCatalogPropSprName."`.lang_id='".$this->lang_id."'
              AND `".TblModCatalogSprName."`.lang_id='".$this->lang_id."'
              AND `".TblModCatalogProp."`.id=`".TblModCatalogTranslit."`.`id_prop`
              AND `".TblModCatalogTranslit."`.`lang_id`='".$this->lang_id."'
             ";

        $str_sublevels = '';
        if($show_sublevels) {
            $str_sublevels = $this->getSubLevels($level);
            //echo '<br />$str_sublevels='.$str_sublevels;
            if(empty($str_sublevels)) $str_sublevels = $level;
            else $str_sublevels = $level.','.$str_sublevels;
            $categ_filter = " `".TblModCatalogProp."`.id_cat IN (".$str_sublevels.")";
            $categ_multi_filter = " `".TblModCatalogPropMultiCategs."`.`id_cat` IN (".$str_sublevels.")";
        }
        elseif($level>0){
            $categ_filter = " `".TblModCatalogProp."`.id_cat='".$level."'";
            $categ_multi_filter = " `".TblModCatalogPropMultiCategs."`.`id_cat`='".$level."'";
        }
        else{
            $categ_filter = '';
            $categ_multi_filter = '';
        }

        if( isset($this->settings['multi_categs']) AND $this->settings['multi_categs']==1 ){
            if( !empty($categ_filter) ) $q = $q."  AND (".$categ_filter." OR ".$categ_multi_filter.") ";
            else $q = $q."  AND  `".TblModCatalogPropMultiCategs."`.`id_cat`='".$level."' ";
        }
        else $q = $q." AND ".$categ_filter;

        if(!empty($paramQuery))
              $q.="AND `".TblModCatalogProp."`.id IN (".$my_id_prop.") ";

        $q .= " GROUP BY `".TblModCatalogProp."`.`id`";

        if($this->sort=='name')
            $q .= ' ORDER BY `'.TblModCatalogPropSprName.'`.name '.$asc_desc;
        else
            $q = $q." ORDER BY  `".TblModCatalogProp."`.`move` asc ";

        if($this->sort!=='price') {
            if($limit=='limit') $q = $q." LIMIT ".$this->start.", ".($this->display);
        }
        $res = $this->db->db_Query( $q );
        //echo '<br>'.$q.'<br> res='.$res.'<br> $this->db->result='.$this->db->result;
        if( !$res or !$this->db->result) return false;
        $rows = $this->db->db_GetNumRows($res);
        for($i=0; $i<$rows; $i++) {
            $arrRows[] = $this->db->db_FetchAssoc();
        }
        // Если есть фильтр по уровню цен
        if(isset($this->from) and isset($this->to)) {
            $from = $this->from*1000;
            $to = $this->to*1000;
        }
        else {
            $from=null;
            $to = null;
        }

        $arr = array();
        if($this->sort=='price' and  $limit=='limit')  {
            $tmpArr = array();
            $offset = array();
            for($i=0; $i<$rows; $i++){
                $row = $arrRows[$i];
                $price = $this->Currency->Converting($row['price_currency'], _CURR_ID, $row['price'], 2)*1000;

                if(isset($from) and isset($to)) {
                    if($price >= $from and $price <= $to )
                        ; // Фильтрация по диапазону цен
                    else
                        continue;
                }
                if($price==0)
                    $price = 999999999;
                if(!isset($offset[$price]))
                    $offset[$price] = 0;
                $tmpArr[$price+$offset[$price]] = $row;
                $offset[$price]+=1;
            }

            if($asc_desc !='asc') {
                ksort($tmpArr); // возростание
            }
            else
                krsort($tmpArr); // в обратном порядке убывание

            $keys = array_keys($tmpArr);
            $limit = $this->start+$this->display;
            if($rows < $limit)
                $limit = $rows;
            for($i=$this->start; $i<$limit; $i++) {
                if(isset($keys[$i]))
                    $arr[$keys[$i]] = $tmpArr[$keys[$i]];
            }
            return $arr;
        }
        elseif($this->sort=='param' and  $limit=='limit')  { // Сортировка по параметру с типом 1 - числовое значение либо 5 - текстовое значение

            $paramData =  $this->GetParamById($idParam);
            if($paramData['type'] == 1) { // Число
                $tmpArr=array();
                for($i=0; $i<$rows; $i++) {
                    $offset = 0.0001;
                    $row = $arrRows[$i];
                    $index  = doubleval(str_replace(',','.',$row['val']))*1000;
                    //echo '    <br/>'.$index;
                    if(!isset($tmpArr[$index]))
                        $tmpArr[$index] = $row;
                    else {
                        while(isset($tmpArr[$index+$offset])) {
                             $offset+=0.0001;
                        }
                        $tmpArr[$index+$offset] = $row;
                    }

                }
                //if($asc_desc == "asc")
                    ksort($tmpArr);
                /*elseif($asc_desc == "desc")
                    rsort($tmpArr);*/
                $keys = array_keys($tmpArr);
                //print_r($keys);
                $rows = count($keys);
                $limit = $this->start + $this->display;
                if($rows < $limit)
                    $limit = $rows;
                for($i=$this->start; $i<$limit; $i++) {
                    if(isset($keys[$i]))
                        $arr[$keys[$i]] = $tmpArr[$keys[$i]];
                }
            }
            elseif($paramData['type'] == 3 or $paramData['type'] == 4)  {   // 3 - Выбор из списка,  4 - Множество значений
                $tblname = $this->BuildNameOfValuesTable($paramData['id_cat'], $paramData['id']);
                //$val = $this->Spr->GetListName( TblSysLogic, $this->lang_id, 'array', 'move', 'asc', 'all' );
                $paramSpr = $this->Spr->GetListName( $tblname, $this->lang_id, 'array', 'move', 'asc', 'all' );
                $paramSprKeys = array_keys($paramSpr);
                //print_r($paramSprKeys);

                $tmpArr=array();
                //echo '$rows ='.$rows;
                for($i=0; $i<$rows; $i++) {
                    $offset = 0.0001;
                    $row = $arrRows[$i];
                    $index  = doubleval(str_replace(',','.',$row['val']))*1000;
                    if(!isset($tmpArr[$index]))
                        $tmpArr[$index] = $row;
                    else {
                        while(isset($tmpArr[$index+$offset])) {
                             $offset+=0.0001;
                        }
                        $tmpArr[$index+$offset] = $row;
                    }

                }
                //if($asc_desc == "asc")
                    ksort($tmpArr);
                /*elseif($asc_desc == "desc")
                    rsort($tmpArr);*/
                $keys = array_keys($tmpArr);
                $rows = count($keys);
                $limit = $this->start + $this->display;
                if($rows < $limit)
                    $limit = $rows;
                for($i=$this->start; $i<$limit; $i++) {
                    if(isset($keys[$i]))
                        $arr[$keys[$i]] = $tmpArr[$keys[$i]];
                }

            }
            return $arr;
        }

        for($i=0; $i<$rows; $i++){
            $row = $arrRows[$i];
            if(defined("_CURR_ID")) $curr_id = _CURR_ID;
            else $curr_id = DEBUG_CURR;
            $price = $this->Currency->Converting($row['price_currency'], $curr_id, $row['price'], 2)*1000;
            if(isset($from) and isset($to)) {
                if($price >= $from and $price <= $to )
                    ; // Фильтрация по диапазону цен
                else
                    continue;
            }
            $arr[$i]=$row;
        }
        return $arr;
    }//end of function GetListPositionsSortByDate()

    
    function ShowTopIcons($name = NULL){
        if(empty($name)) return false;
        ?><div class="icons_new_fon">
            <div class="icons_new"><?=$name?></div>
            <div class="icons_new_left"></div>
        </div><?
    }

    /**
     * CatalogLayout::ShowListOfContentByPages()
     * Show list of positions by pages
     * @author Yaroslav
     * @param mixed $arr
     * @return void
     */
    function ShowListOfContentByPages($arr=NULL)
    {
        $rows = count($arr);
        //echo '<br>$rows='.$rows;
        if ($rows==0 or !is_array($arr)) {
            if ($this->task=='make_advansed_search' or $this->task=='quick_search' or $this->task=='make_search_by_params' ) {
                ?><div class="err"><?
                $this->ShowErr( $this->multi['MSG_ERR_NO_ANY_POSITIONS_BY_REQUEST'] .'<br /><a href="javascript:history.back()">'.$this->multi['TXT_FRONT_GO_BACK'].'</a>' );
                ?></div><?
            }
            else {
                if (isset($this->treeCatData[$this->id_cat]['name'])) {
                    $category_name = stripslashes($this->treeCatData[$this->id_cat]['name']);
                    if( !$this->isSubLevels($this->id_cat, $this->treeCatLevels, $this->id_cat ) ) {
                        ?><div class="err"><?
                        echo $this->multi['MSG_ERR_NO_ANY_POSITIONS_BY_REQUEST'].' <strong>'.$category_name.'</strong><br/><a href="javascript:history.back()">'.$this->multi['TXT_FRONT_GO_BACK'].'</a>';
                        ?></div><?
                    }
                    else {
                        // Выбор по параметрам фильтра
                        ?><div class="err"><?
                        echo $this->multi['MSG_ERR_NO_POSITIONS_BY_PARAM_IN_CATEGORY'].' <strong>'.$category_name.'</strong><br/><a href="javascript:history.back()">'.$this->multi['TXT_FRONT_GO_BACK'].'</a>';
                        ?></div><?
                    }
                }
                else {
                    ?><div class="err"><?
                    echo $this->multi['MSG_ERR_NO_ANY_POSITIONS_IN_CATEGORY'].' <br/><a href="javascript:history.back()">'.$this->multi['TXT_FRONT_GO_BACK'].'</a>';
                    ?></div><?
                }
            }
        }
        else {
           $settings = $this->settings;
           $this->ShofJSFoParam($rows);
           ?><div class="categoryContent"><?
                $keys = array_keys($arr);
                for($i=0;$i<$rows;$i++){
                    $row = $arr[$i];
                    $name = stripslashes($row['name']);
                    $img = '';
                    $img = stripslashes($row['first_img']);
                    $alt = htmlspecialchars(stripcslashes($row['first_img_alt']));
                    $title = htmlspecialchars(stripcslashes($row['first_img_title']));
                    if(empty($alt)) $alt = $name;
                    if(empty($title)) $title = $name;
                    $link = $this->getUrlByTranslit(false, $row['translit']);
                    $price = stripslashes($row['price']);
                    if(($i)%3==0 && $i!=0){?>
           </div>
        <div class="BlockPolosa" style="margin-bottom: 25px;"></div>
        <div class="<?switch($rows-$i){
            case 1:
                echo 'categoryContent1prop';
                break;
            case 2:
                echo 'categoryContent2prop';
                break;
            default:
                echo 'categoryContent';
                break;
        }?>"><?}?>
                    <div class="prod"<?if(($i+1)%3==0){?> style="margin: 0;"<?}?>>
                      <div class="prodInside">
                        <div class="nameProd"><?=$name;?></div>
                        <div class="imgProd">
                            <div class="icons_new_fon_fon">
                                <?if($row['popular']==1)$this->ShowTopIcons('Распродажа');
                                if($row['new']==1)$this->ShowTopIcons('Новинка');
                                if($row['best']==1)$this->ShowTopIcons('Топ');?>
                            </div>
                            <div class="imgProdTable">
                            <?if ( isset($img) AND !empty($img)) {
                                ?><a href="<?=$link;?>" title="<?=$name?>">
                                <?=$this->ShowCurrentImage($img, 'size_auto=195', 85, NULL, 'alt="'.$alt.'" title="'.$title.'"', $row['id']);?>
                                </a><?
                            }
                            else {
                               ?><a href="<?=$link;?>" title="<?=$this->multi['TXT_NO_IMAGE']?>"><img src="/images/design/no-image.jpg"/></a><?
                            }?>
                            </div>
                        </div>
                        <div class="paramProdFon">
                            <div class="paramProd">
                                <?$this->ShowParamsOfPropInList($row['id']);?>
                            </div>
                        </div>
                        <div class="priceBuyProd">
                        <?if(!empty($price)){//&& $row['exist']==1?>
                            <div class="priceProd"><span id="priceProp_<?=$row['id']?>"><?=$price?></span> грн.</div>
                            <div class="buyProd">
                                <form action="#" method="post" name="catalog<?=$row['id']?>" id="catalog<?=$row['id']?>">
                                    <input type="hidden" value="0" id="colorId<?=$row['id']?>" name="colorId" />
                                    <input type="hidden" size="2" value="1" id="prod_id[<?=$row['id']?>]" name="prod_id[<?=$row['id']?>]"/>
                                    <div class="buybutton">
                                        <a href="#" id="rez<?=$row['id']?>" onclick="addToCart('<?=$row['id']?>');return false;" title="<?=$this->multi['TXT_BUY'];?>"><?=$this->multi['TXT_BUY']?></a>
                                    </div>
                                </form>
                            </div>
                        <?}else{
                            ?><div class="net_nalishie_fon"><div class="net_nalishie">Нет в наличии</div></div><?
                        }
                        ?></div><?
                    ?></div></div><?
                }
                ?>
           </div>
            <?

           if(empty($this->search_keywords)) {
            if($rows >= $this->display or $this->page>1) {
                $link = $this->Link($this->id_cat, NULL);
                $rows = count($this->GetListPositionsSortByDate($this->id_cat, 'nolimit', null, 'asc' ,true, $this->id_param));
                //echo '$this->url_param ='.$this->url_param;
                if(!empty ($this->url_param))
                    $this->url_param ='?'.$this->url_param;
                else
                    $this->url_param ='?';

                if(!empty ($this->id_param))
                    $this->url_param.='&id_param='.$this->id_param;

                if(isset($this->priceLevels))
                    $this->url_param.=$this->priceLevels;

                if(!empty($this->sort) ) {
                    if ( $this->asc_desc=='asc')
                         $asc_desc= 'desc';
                    else
                         $asc_desc = 'asc';
                    if(!empty ($this->url_param))
                        $this->url_param .='&sort='.$this->sort.'&asc_desc='.$asc_desc.'&exist='.$this->exist;
                    else
                        $this->url_param ='?sort='.$this->sort.'&asc_desc='.$asc_desc.'&exist='.$this->exist;
                }

                ?><div class="links"><?
                    $this->Form->WriteLinkPagesStatic( $link, $rows, $this->display, $this->start, $this->sort, $this->page, $this->url_param);
                ?></div>
                <div class="clearing"></div>
                <?
            }
           }
           //показываем постраничность для результатов поиска в каталоге
           elseif( $this->task=='quick_search'){
               $rows_all = count($this->QuickSearch( $this->search_keywords, 'nolimit'));
               $link = _LINK.'catalog/search/result/'.htmlentities(urlencode($this->search_keywords)).'/';
               ?><div class="links"><?
                    $this->Form->WriteLinkPagesStatic( $link, $rows_all, $this->display, $this->start, $this->sort, $this->page );
               ?></div><?
           }
        }
    } //--- end of ShowListOfContentByPages()

    // ================================================================================================
    // Function : ShowListOfContentByPages()
    // Version : 1.0.0
    // Date : 03.03.2008
    // Parms : $id - id of the position
    // Returns : true,false / Void
    // Description : show list of positions by pages
    // ================================================================================================
    // Programmer : Ihor Trokhymchuk
    // Date : 17.02.2011
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowListShortByPages($arr=NULL)
    {
        $rows = count($arr);
        //echo '<br>$rows='.$rows;
        $cat_data = $this->GetTreeCatData($this->id_cat);
        if ($rows==0) {
           ?><div class="err" align="center"><?
            $category_name = stripslashes($cat_data['name']);
            if ($this->task=='make_advansed_search' or $this->task=='quick_search' or $this->task=='make_search_by_params' ) {
                $this->ShowErr( $this->multi['MSG_ERR_NO_ANY_POSITIONS_BY_REQUEST'] .'<br /><a href="javascript:history.back()">'.$this->multi['TXT_FRONT_GO_BACK'].'</a>' );
            }
            else {
                if( !$this->isCatASubcatOfLevel($this->id_cat, $this->treeCatLevels, $this->id_cat ) ) { echo $this->multi['MSG_ERR_NO_ANY_POSITIONS_IN_CATEGORY'].' <strong>'.$category_name.'</strong><br/><a href="javascript:history.back()">'.$this->multi['TXT_FRONT_GO_BACK'].'</a>';}
            }
           ?></div><?
        }
        else {
           $settings = $this->settings;
           ?>
           <ul class="categoryContent">
                <?
                for($i=0;$i<$rows;$i++){
                    $row = $arr[$i];
                    $name = stripslashes($row['name']);
                    $price = stripslashes($row['price']);
                    $old_price = stripslashes($row['opt_price']);
                    $link = $this->getUrlByTranslit($this->treeCatData[$row['id_cat']]['path'], $row['translit']);
                        $cur_from = $row['price_currency'];
                        $price = $this->Currency->Converting($cur_from, _CURR_ID, $price, 2 );
                        ?>
                        <!-- Show Name of Position -->
                        <li><a href="<?=$link;?>" title="<?=htmlspecialchars($name);?>"><?=$name;?></a> <span ><?=$this->Currency->ShowPrice($price);?></span></li>
                    <?
                }
                ?>
           </ul>
            <?
            /*
            $arr = $this->GetListPositionsSortByDate($this->id_cat, 'nolimit', true);
            $rows = count($arr);
            $link = $this->Link($this->id_cat, NULL);
            */

            //РїРѕРєР°Р·С‹РІР°РµРј РїРѕСЃС‚СЂР°РЅРёС‡РЅРѕСЃС‚СЊ РґР»СЏ СЂРµР·СѓР»СЊС‚Р°С‚РѕРІ РїРѕРёСЃРєР° РІ РєР°С‚Р°Р»РѕРіРµ
            if( $this->task=='quick_search'){
                $rows_all = $this->QuickSearch( $this->search_keywords, 'nolimit');
                $link = _LINK.'catalog/search/result/'.htmlentities(urlencode($this->search_keywords)).'/';
                ?><div style="margin-top:30px; text-align:center;"><?$this->FrontForm->WriteLinkPagesStatic( $link, $rows_all, $this->display, $this->start, $this->sort, $this->page );?></div><?
            }
        }
    } //--- end of ShowListShortByPages()

   // ================================================================================================
   // Function : ShowRatingInfo()
   // Version : 1.0.0
   // Date : 08.04.2006
   // Parms :
   // Returns : true,false / Void
   // Description : show details of curent position of catalogue on the front-end
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 08.04.2006
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowRatingInfo($id)
   {
       $rating = $this->GetAverageRatingByIdProp($id, 'front');
       if($rating<1) $rating=0;
       ?>
        <span class="rat01">
        Р’СЃРµРіРѕ Р±Р°Р»РѕРІ: <?=$this->GetRatingByIdProp($id);?>
        <br/>Р“РѕР»РѕСЃРѕРІ: <?=$this->GetVotesByIdProp($id);?>
        <br/><?=$this->Msg->show_text('FLD_RATING').': '.$rating;?>
        </span>
       <?

   }//end of function ShowRatingInfo()

   function ShowDescrBlock($row = NULL){
       if(empty($row)) return false;
       $i=0;
                $text = stripslashes($row['short']);
                if($text){
                    $arrKeyName[$i] = $this->multi['_TXT_META_DESCRIPTION'];
                    $arrKeyText[$i] = $text;
                    $i++;
                }
                
                $text = stripslashes($row['full']);
                if($text){
                    $arrKeyName[$i] = $this->multi['TXT_COMPLECTATION'];
                    $arrKeyText[$i] = $text;
                    $i++;
                }
                
                $text = stripslashes($row['specif']);
                if($text){
                    $arrKeyName[$i] = $this->multi['TXT_DOSTAVKA_I_GARANTIA'];
                    $arrKeyText[$i] = $text;
                    $i++;
                }
                
                $text = stripslashes($row['reviews']);
                if($text){
                    $arrKeyName[$i] = $this->multi['TXT_GARANTIA'];
                    $arrKeyText[$i] = $text;
                    $i++;
                }
                $arrKeyName[$i] = $this->multi['TXT_OTZIVI'];
                $i++;?>
                <div class="fullDescText">
                    <div class="fullDescKey">
                        <?
                        for($j = 0;$j<$i;$j++){
                            $name = $arrKeyName[$j];
                            ?><div class="fullDescKeyOne" id="KeyOne<?=$j?>" onclick="selKey(<?=$j?>)">
                                <div class="fullDescKeyOneName <?if($j==0){?>selName<?}?>"><?=$name?></div>
                                <div class="fullDescKeyOneLeft <?if($j==0){?>selLeft<?}?>"></div>
                            </div><?
                        }?>
                    </div>
                    <div class="fullDescContent">
                        <?for($j = 0;$j<$i-1;$j++){
                            $text = $arrKeyText[$j];
                            ?><div class="fullDescContentText<?if($j!=0){?> disable<?}?>" id="ContentText<?=$j?>"><?=$text?></div><?
                        }?>
                        <div class="fullDescContentText <?if($i>1){?>disable<?}?>" id="ContentText<?=$j?>"><?
                        $this->Comments = new FrontComments($this->module, $this->id);
                        $this->Comments->ShowCommentsByModuleAndItem();?></div>
                    </div>
                </div>
            </div> 
            <script type="text/javascript">
                function  selKey(id){
                    $('.fullDescKeyOneName').removeClass('selName');
                    $('.fullDescKeyOneLeft').removeClass('selLeft');
                    $('#KeyOne'+id+' .fullDescKeyOneName').addClass('selName');
                    $('#KeyOne'+id+' .fullDescKeyOneLeft').addClass('selLeft');
                    
                    $('.fullDescContentText').addClass('disable');
                    $('#ContentText'+id).removeClass('disable');
                }
            </script><?
   }
   
    // ================================================================================================
    // Function : ShowDetailsCurrentPosition()
    // Version : 1.0.0
    // Date : 08.04.2006
    // Parms :
    // Returns : true,false / Void
    // Description : show details of curent position of catalogue on the front-end
    // ================================================================================================
    // Programmer : Yaroslav Gyryn
    // Date : 25.10.2009
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowDetailsCurrentPosition( $id_img = NULL )
    {
        $settings = $this->settings;
        $tmp_db = DBs::getInstance();
        $catData = $this->GetTreeCatData($this->id_cat);

        $filed_list = '';
        $left_join = '';

        if ( isset($settings['short_descr']) AND $settings['short_descr']=='1' ){
            $filed_list .= ", `".TblModCatalogPropSprShort."`.`name` AS `short`";
            $left_join .= "\n LEFT JOIN `".TblModCatalogPropSprShort."` ON (`".TblModCatalogProp."`.`id`=`".TblModCatalogPropSprShort."`.`cod` AND `".TblModCatalogPropSprShort."`.`lang_id`='".$this->lang_id."')";
        }
        if ( isset($settings['full_descr']) AND $settings['full_descr']=='1' ){
            $filed_list .= ", `".TblModCatalogPropSprFull."`.`name` AS `full`";
            $left_join .= "\n LEFT JOIN `".TblModCatalogPropSprFull."` ON (`".TblModCatalogProp."`.`id`=`".TblModCatalogPropSprFull."`.`cod` AND `".TblModCatalogPropSprFull."`.`lang_id`='".$this->lang_id."')";
        }
        if ( isset($settings['specif']) AND $settings['specif']=='1' ) {
            $filed_list .= ", `".TblModCatalogPropSprSpecif."`.`name` AS `specif`";
            $left_join .= "\n LEFT JOIN `".TblModCatalogPropSprSpecif."` ON (`".TblModCatalogProp."`.`id`=`".TblModCatalogPropSprSpecif."`.`cod` AND `".TblModCatalogPropSprSpecif."`.`lang_id`='".$this->lang_id."')";

        }
        if ( isset($settings['reviews']) AND $settings['reviews']=='1' ) {
            $filed_list .= ", `".TblModCatalogPropSprReviews."`.`name` AS `reviews`";
            $left_join .= "\n LEFT JOIN `".TblModCatalogPropSprReviews."` ON (`".TblModCatalogProp."`.`id`=`".TblModCatalogPropSprReviews."`.`cod` AND `".TblModCatalogPropSprReviews."`.`lang_id`='".$this->lang_id."')";

        }
        if ( isset($settings['support']) AND $settings['support']=='1' ) {
            $filed_list .= ", `".TblModCatalogPropSprSupport."`.`name` AS `support`";
            $left_join .= "\n LEFT JOIN `".TblModCatalogPropSprSupport."` ON (`".TblModCatalogProp."`.`id`=`".TblModCatalogPropSprSupport."`.`cod` AND `".TblModCatalogPropSprSupport."`.`lang_id`='".$this->lang_id."')";
        }

        $q = "SELECT
                `".TblModCatalogProp."`.*,
                `".TblModCatalogPropSprName."`.name
                $filed_list
             FROM `".TblModCatalogProp."`
                $left_join ,
                `".TblModCatalogPropSprName."`
             WHERE
                `".TblModCatalogProp."`.id = `".TblModCatalogPropSprName."`.cod
             AND
                `".TblModCatalogPropSprName."`.lang_id='".$this->lang_id."'
             AND
                `".TblModCatalogProp."`.id ='".$this->id."'";

        $res = $tmp_db->db_Query( $q );
        //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
        if ( !$res or !$tmp_db->result )
            return false;

        $row = $tmp_db->db_FetchAssoc();
        if( isset($settings['img']) AND $settings['img']=='1' ) $row_img = $this->GetPicture($row['id']);
        if( isset($settings['files']) AND $settings['files']=='1' ) $row_files = $this->GetFiles($row['id']);
        $name = stripslashes($row['name']);
        $price = $row['price'];
        //$name_manufac = stripslashes($row['manufac']);
        $manufac = $this->Spr->GetNameByCod( TblModCatalogSprManufac, $row['id_manufac'], $this->lang_id, 1 );
        $this->Form->WriteContentHeader(false, false,$this->ShowPathToLevel($this->id_cat,NULL,1).'  /  '.$name);
        ?>
        <div class="body">
            <div class="tovarDescriptHeader">
            <div class="tovarImage">
            <!-- display image start-->
                <div id="main_image_prop"><?
                if ( isset($row_img['0']['id']) ) {
                    if ( empty($id_img) ) $id_img = $row_img[0]['id']; 
                    $path = "http://".NAME_SERVER.$settings['img_path']."/".$this->id."/".$row_img[0]['path'];
                    $alt = htmlspecialchars(stripslashes($row_img[0]['alt']));
                    $title = htmlspecialchars(stripslashes($row_img[0]['title']));
                    ?>
                    <div class="main_image_prop_fon" id="imageLarge">
                        <a href="<?=$path;?>" title="<?=$title;?>" class="highslide" onclick="return hs.expand(this);">
                        <?=$this->ShowCurrentImage($row_img[0]['path'], 'size_auto=272', 85, NULL, " border='0' alt='".$alt."' title='".$title."'", $this->id);?>
                        </a>
                    </div>
                    <?
                }
                else { ?><img src="/images/design/no-image.gif" alt="no-photo" width="250" title="no-photo" border="0"/><? }
                ?>
                </div><?
                if ( isset($row_img['1']['id']) ) {?>
                    <div id="carouselFon">
                        <ul id="carousel"><?
                        $cnt = count($row_img);
                        for($i=0;$i<$cnt;$i++){
                            $path = $settings['img_path']."/".$this->id."/".$row_img[$i]['path'];
                            $alt = htmlspecialchars(stripslashes($row_img[$i]['alt']));
                            $title = htmlspecialchars(stripslashes($row_img[$i]['title']));
                            $small_path=$this->ShowCurrentImage($row_img[$i]['path'],'size_auto=272',85,NULL,null,$this->id,true);
                            $link="javascript:showImage('".$small_path."', '".$path."', '".$alt."',  '".$title."')";
                            ?><li>
                                <a href="<?=$link?>" title="<?=$alt;?>">
                            <?=$this->ShowCurrentImage($row_img[$i]['path'], 'size_auto=62', 85, NULL, " border='0' alt='".$alt."' title='".$title."'", $this->id);?>
                                </a>
                            </li><?
                        }?>
                        </ul>
                        <?if($cnt>4){?>
                            <script type="text/javascript">
                                $(document).ready(function(){
                                /*Галерея  зображень, прокрутка*/ 
                                    $("#carousel").jcarousel({
                                        scroll: 1,
                                        auto: 0,
                                        wrap: "last"
                                    });
                                    $("#carousel").removeClass("vhidden");
                                });
                            </script>
                        <?}?>
                    </div>
                    <?
                }            
                ?>
                <!--display image end-->
            </div>
            <div id="tovarDetail">
                <h1 class="tovarDetailName"><?=$name;?></h1>
                <div class="tovarDetailBuyRating">
                    <div class="tovarDetailBuy"><?
                    if(!empty($price)){?>
                         <div class="tovarDetailPrice"><span id="priceProp_<?=$row['id']?>"><?=$price?></span> грн.</div>
                         <div class="buyProd">
                             <form action="#" method="post" name="catalog<?=$row['id']?>" id="catalog<?=$row['id']?>">
                                 <input type="hidden" value="0" id="colorId<?=$row['id']?>" name="colorId" />
                                 <input type="hidden" size="2" value="1" id="prod_id[<?=$row['id']?>]" name="prod_id[<?=$row['id']?>]"/>
                                 <div class="tovarDetailBuyButton">
                                     <a href="#" id="rez<?=$row['id']?>" onclick="addToCart('<?=$row['id']?>');return false;" title="<?=$this->multi['TXT_BUY'];?>"><?=$this->multi['TXT_BUY']?></a>
                                 </div>
                             </form>
                         </div>
                     <?}else{
                         ?><div class="net_nalishie_fon"><div class="net_nalishie">Нет в наличии</div></div><?
                     }
                    ?></div>
                    <div class="tovarDetailRatingTmp"></div>
                </div>
                <div class="tovarDetailProp">
                    <?$this->ShowParamsOfProp($this->id,$manufac);?>
                </div>
            </div>
            </div>
            <div class="fullDesc">
                <?$this->ShowDescrBlock($row);?>
                <div class="tovarDetailActionPredlozenie">
                    <div class="tovarDetailActionHeader">Акционное предложение</div>
                    <div class="tovarDetailActionTmp"></div>
                </div>
            </div>
            <div class="tovarDetailPohozie">
                <div class="tovarDetailPohozieName">Похожие товары</div>
                <div class="pohozhieTovariTmp"></div>
            </div>
        <?
        $this->Form->WriteContentFooter();

    } //end of function ShowDetailsCurrentPosition()


   // ================================================================================================
   // Function : ShowPrintVersion()
   // Version : 1.0.0
   // Date : 23.07.2008
   // Parms :
   // Returns : true,false / Void
   // Description : show print version of page
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 23.07.2008
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowPrintVersion()
   {
       $title = NULL;
       $description = NULL;
       $keywords = NULL;
       ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- <html xmlns="http://www.w3.org/1999/xhtml" lang="ru" xml:lang="ru"> -->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv='Content-Type' content="application/x-javascript; charset=utf-8" />
<meta http-equiv="Content-Language" content="ru" />
<title>$title</title>
<meta name="Description" content="<?=$description;?>" />
<meta name="Keywords" content="<?=$keywords;?>" />
  <link href="/include/css/main.css" type="text/css" rel="stylesheet" />
  <link href="/include/css/screen.css" type="text/css" rel="stylesheet"  media="screen" />
  <!--[if IE ]>
    <link href="/include/css/browsers/ie.css" rel="stylesheet" type="text/css" media="screen" />
  <![endif]-->
  <!--[if lt IE 8]>
    <link href="/include/css/browsers/ie7.css" rel="stylesheet" type="text/css" media="screen" />
  <![endif]-->
  <!--[if lt IE 7]>
    <link href="/include/css/browsers/ie6.css" rel="stylesheet" type="text/css" media="screen" />
    <script type="text/javascript" src="/include/js/iepngfix_tilebg.js"></script>
  <![endif]-->
  <!--[if lt IE 6]>
    <script src="/include/js/ie5.js" type="text/javascript"></script>
  <![endif]-->
</head>

<body style="background-color: white;">
<?
       $settings = $this->GetSettings();
       $q = "SELECT
                `".TblModCatalogProp."`.id,
                `".TblModCatalogProp."`.id_cat,
                `".TblModCatalogProp."`.id_manufac,
                `".TblModCatalogProp."`.number_name,
                `".TblModCatalogProp."`.price,
                `".TblModCatalogProp."`.opt_price,
                `".TblModCatalogProp."`.art_num,
                `".TblModCatalogProp."`.barcode,
                `".TblModCatalogPropSprName."`.name
             FROM `".TblModCatalogProp."`, `".TblModCatalogPropSprName."`
             WHERE
                `".TblModCatalogProp."`.id = `".TblModCatalogPropSprName."`.cod
             AND
                `".TblModCatalogPropSprName."`.lang_id='".$this->lang_id."'
             AND
                `".TblModCatalogProp."`.id ='".$this->id."'";

       $res = $this->db->db_Query( $q );
       //echo '<br> $q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
       if ( !$res or !$this->db->result ) return false;

       $rows = $this->db->db_GetNumRows();
       $row = $this->db->db_FetchAssoc();
       $row_img = $this->GetPicture($row['id']);
       $row_files = $this->GetFiles($row['id']);
       $name = stripslashes($row['name']);
       ?>
       <h1 class="bgrnd"><?=$name;?></h1>
        <div class="subBody">
            <div class="path"><?$this->ShowPathToLevel($this->id_cat, NULL, 1);?></div>
            <div class="tovarImage floatToLeft">
                <!-- display image start-->
                <?
                if ( isset($row_img['0']['id']) ) {
                    if ( empty($id_img) ) $id_img = $row_img['0']['id'];
                    $path = "http://".NAME_SERVER.$settings['img_path']."/".$this->id."/".$row_img['0']['path'];
                    ?>
                    <div class="floatToLeft"><a href="<?=$path;?>" rel="itemImg" title="<?=$name;?>" target="_blank"><?=$this->ShowCurrentImage($id_img, 'size_auto=300', 85, NULL, "");?></a></div>
                    <div id="thumb">
                    <?
                    $cnt = count($row_img);
                    for($i=1;$i<$cnt;$i++){
                        $path = "http://".NAME_SERVER.$settings['img_path']."/".$this->id."/".$row_img[$i]['path'];
                        ?>
                        <a href="<?=$path;?>" rel="itemImg" title="<?=$name;?>" target="_blank"><?=$this->ShowCurrentImage($row_img[$i]['id'], 'size_auto=50', 85, NULL, "");?></a><br />
                        <?
                    }
                    ?>
                    </div>
                    <script type="text/javascript">
                       $("a[rel='itemImg']").colorbox();
                    </script>
                    <?

                }
                else { ?><img src="/images/design/no-photo<?=_LANG_ID;?>.gif" alt="no-photo" title="no-photo" border="0"/><? }
                ?>
                <!--display image end-->
            </div>

            <div class="tovarDetail">
                <?
                echo $this->Spr->GetNameByCod( TblModCatalogPropSprShort, $this->id, $this->lang_id, 1 );
                if(!empty($row['art_num'])){
                    ?><br /><?=$this->multi['FLD_ART_NUM'];?> <?=stripslashes($row['art_num']);
                }
                if(!empty($row['barcode'])){
                    ?><br /><?=$this->multi['FLD_BARCODE'];?> <?=stripslashes($row['barcode']);
                }

                if ( isset($settings['price']) AND $settings['price']=='1' ){
                   $price = $this->Currency->Converting($this->GetPriceCurrency($row['id']), _CURR_ID, $row['price'], 2);
                   ?>
                   <span class="price"><?=$this->Currency->ShowPrice($price);?></span>
                   <br/>
                   <?
                }
                ?>
            </div>
            <hr/>

           <div class="fullDesc">
               <!-- description -->
                <?
                if ( isset($settings['full_descr']) AND $settings['full_descr']=='1' ) {
                    $val = $this->Spr->GetNameByCod( TblModCatalogPropSprFull, $this->id, $this->lang_id, 1 );
                    if ( !empty($val) ) {
                        ?><h3><?=$this->multi['FLD_FULL_DESCR'];?></h3>
                        <div><?=$val;?></div><?
                    }
                }
                ?>
                <!-- description -->
                <?
                if ( isset($settings['full_descr']) AND $settings['full_descr']=='1' ) {
                    $val = $this->Spr->GetNameByCod( TblModCatalogPropSprSpecif, $this->id, $this->lang_id, 1 );
                    if ( !empty($val) ) {
                        ?><h3><?=$this->multi['FLD_SPECIF'];?></h3>
                        <div><?=$val;?></div>
                        <hr/>
                        <?
                    }
                }
                ?>
           </div>
        </div>
        <a href="javascript:window.close()"><u><?=$this->multi['TXT_CLOSE'];?></u></a>

</body>
</html>

       <?
   } // end of function ShowPrintVersion()

    /**
    * Class method LoadCatParams
    * load all parameters for category $id_cat in class property
    * @param $id_cat - id of the category
    * @param $use_parent_params - use or not parametrs of parent categories
    * @return true/false or arrays:
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.1, 05.04.2011
    */
    function LoadCatParams($id_cat, $use_parent_params=true)
    {
       $tmp_db = new DB();
       if($use_parent_params==1) $arr_top_levels = $this->get_top_levels_in_array( $id_cat, NULL );
       else $arr_top_levels[$id_cat]='';
       //echo '<br>$arr_top_levels='.$arr_top_levels;
       foreach($arr_top_levels as $v=>$k){
         $q = "SELECT * FROM `".TblModCatalogParams."` WHERE `id_cat`='".$v."' order by `move`";
         $res = $tmp_db->db_Query( $q );
         //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
         if ( !$res OR !$tmp_db->result ) return false;
         $rows = $tmp_db->db_GetNumRows();
         for ($i=$rows;$i<$rows;$i++){
           $row = $tmp_db->db_FetchAssoc();
           $this->paramsList[$row['id']] = $tmp_db->db_FetchAssoc();
           //echo '<br>$row['.$i.']='.$row[$i];
         }
       }
       //print_r($this->paramsList);
       return true;
    }//end of funcion LoadCatParams()

    /**
    * Class method CheckExistOfParams
    * chekc exist or not list of param/ If not exist one o more parametres - return false
    * @return true/false or arrays:
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.1, 01.02.2012
    */
   function CheckExistOfParamsFilter($arr_params)
   {
        $tmp_db = new DB();
        $rows = NULL;
        $keys = array_keys($arr_params);
        $cnt = count($keys);
        $q = "SELECT * FROM `".TblModCatalogParams."` WHERE `modify`='1' AND ";
        for($i=0;$i<$cnt;$i++){
            if($i==0) $q .= "`id`='".$keys[$i]."'";
            else $q .= " OR `id`='".$keys[$i]."'";
        }
        $res = $tmp_db->db_Query( $q );
        //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
        if ( !$res OR !$tmp_db->result ) return false;
        $rows = $tmp_db->db_GetNumRows();
        if($rows!=count($arr_params)) return false;
        //$row = $tmp_db->db_FetchAssoc();
        return true;
   }//end of function CheckExistOfParams()


   // ================================================================================================
   // Function : GetLinksToParamsNames ()
   // Version : 1.0.0
   // Programmer : Yaroslav Gyryn
   // Date : 15.06.2009
   // Parms :   $id_cat         // id of current category
   // Returns : str
   // Description : return names & values of parameters in string for current catalogue
   // ================================================================================================
   function GetLinksToParamsNames ( $id_cat, $spacer = ' - ', $showLink = true)
   {
          // echo '<br>$params='.$params.' $id_cat='.$id_cat;
           //if ( $params==0 ) return;
           $str = NULL;
           $params_row = $this->GetParams($id_cat);
           $link = $this->Link($id_cat);
           $param = NULL;
           //echo '<br>$params_row=';print_r($params_row);

           for ($i=0;$i<count($params_row);$i++){
               if($params_row[$i]['modify']!=1)
                    continue;
               $paramCategory  = $this->Spr->GetNameByCod(TblModCatalogParamsSprName,($params_row[$i]['id']), $this->lang_id, 1);
               if($paramCategory!='РџСЂРѕРёР·РІРѕРґРёС‚РµР»СЊ')
                    continue;
               $val=NULL;
               $str .='';
               //echo '<br>$params_row['.$i.'][id]='.$params_row[$i]['id'];
               $tblname = $this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);
               switch ($params_row[$i]['type'] ) {
                       case '2':
                            $val = $this->Spr->GetListName( TblSysLogic, $this->lang_id, 'array', 'move', 'asc', 'all' );
                            break;
                       case '3':
                       case '4':
                            $val = $this->Spr->GetListName( $tblname, $this->lang_id, 'array', 'move', 'asc', 'all' );
                            break;
               }

              //$prefix = $this->Spr->GetNameByCod(TblModCatalogParamsSprPrefix,($params_row[$i]['id']), $this->lang_id, 1);
              //$sufix = $this->Spr->GetNameByCod(TblModCatalogParamsSprSufix,($params_row[$i]['id']), $this->lang_id, 1);
              //echo '<br> $val='.$val;print_r($val);
              if( is_array($val) )
                  foreach($val as $k=>$v) {
                    // Р¤РѕСЂРјР°С‚РёСЂРѕРІР°РЅРЅС‹Р№ РІС‹РІРѕРґ С‚РµРєСЃС‚Р° Р»РёР±Рѕ СЃСЃС‹Р»РєРё РїР°СЂР°РјРµС‚СЂР°
                    if($str=='') {
                        if($showLink)
                            $str =' <a href="'.$link.'?'.PARAM_VAR_NAME.PARAM_VAR_SEPARATOR.$params_row[$i]['id'].'='.$v['cod'].'">'.$v['name'].'</a>';
                        else
                           $str = ' '.$v['name'];
                    }
                    else {
                        if($showLink)
                            $str .= $spacer.'<a href="'.$link.'?'.PARAM_VAR_NAME.PARAM_VAR_SEPARATOR.$params_row[$i]['id'].'='.$v['cod'].'">'.$v['name'].'</a>';
                        else
                            $str .= $spacer.$v['name'];
                    }
                  }
                  $str .=' ';
           }
           return $str;
   } //end of function GetLinksToParamsNames ()


    // ================================================================================================
    // Function : GetParamsNamesValuesOfPropInStr()
    // Version : 1.0.0
    // Programmer : Yaroslav Gyryn
    // Date : 15.06.2009
    // Parms :   $id_cat         // id of current category
    // Returns : str
    // Description : return names & values of parameters in string for current catalogue
    // ================================================================================================
    function GetParamsNamesValuesOfPropInStr( $id_cat )
    {
       //$params = $this->IsParams( $id_cat );
      // echo '<br>$params='.$params.' $id_cat='.$id_cat;
       //if ( $params==0 ) return;
       $str = NULL;
       $params_row = $this->GetParams($id_cat);
       $link = $this->Link($this->id_cat);
       $param_str = NULL;
       $this->url_param = NULL;
       $param = NULL;
       $filtr = NULL;
       $sorting ='';
       //echo '<br>$params_row=';print_r($params_row);
      if(!empty($this->sort) ) {
        $sorting ='&sort='.$this->sort.'&asc_desc='.$this->asc_desc.'&exist='.$this->exist;
      }
       $n = count($params_row);
       for ($i=0; $i<$n; $i++) {
           if($params_row[$i]['modify']!=1) continue;
           $val = NULL;
           $paramName = $this->Spr->GetNameByCod(TblModCatalogParamsSprName,($params_row[$i]['id']), $this->lang_id, 1);
           /*if($paramName=="РџСЂРѕРёР·РІРѕРґРёС‚РµР»СЊ")
                continue;*/
           $str .='<div class="paramBlock"><div class="paramName">'.$paramName .':</div>';
           //echo '<br>$params_row['.$i.'][id]='.$params_row[$i]['id'];
           $tblname = $this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);
           switch ($params_row[$i]['type'] ) {
               case '1':
                        //$val = $v;
                        break;
                   case '2':
                        $val = $this->Spr->GetListName( TblSysLogic, $this->lang_id, 'array', 'move', 'asc', 'all' );
                        break;
                   case '3':
                   case '4':
                        $val = $this->Spr->GetListName( $tblname, $this->lang_id, 'array', 'move', 'asc', 'all' );
                        break;
                 /*  case '5':
                        $val = $v;
                        break;*/
           }

          $prefix = $this->Spr->GetNameByCod(TblModCatalogParamsSprPrefix,($params_row[$i]['id']), $this->lang_id, 1);
          $sufix = $this->Spr->GetNameByCod(TblModCatalogParamsSprSufix,($params_row[$i]['id']), $this->lang_id, 1);
          //echo '<br> $val='.$val;print_r($val);
          $str .= '<div class="paramKey">';
          if( is_array($val) ) {
              $showAll=false;

              // Р¤РѕСЂРјРёСЂРѕРІР°РЅРёРµ СЃС‚СЂРѕРєРё РїР°СЂР°РјРµС‚СЂРѕРІ
              //print_r($this->arr_current_img_params_value);
              if( is_array($this->arr_current_img_params_value) ) {
                      $param_str = NULL;
                      //echo' <br>$params_row[$i][id] ='.$params_row[$i]['id'];
                      foreach($this->arr_current_img_params_value as $key=>$value) {
                          //echo' $key ='.$key;
                          if($key!=$params_row[$i]['id']) {
                               $param ='&'.PARAM_VAR_NAME.PARAM_VAR_SEPARATOR.$key.'='.$value;
                               $param_str .= $param;
                               if(substr_count($this->url_param, $param)==0)
                                    $this->url_param .= $param;
                          }
                      }
              }

              foreach($val as $k=>$v) {

                // РџСЂРѕРІРµСЂРєР° РёР»Рё РІС‹Р±СЂР°РЅ РєРѕРЅРєСЂРµС‚РЅС‹Р№ РїР°СЂР°РјРµС‚СЂ
                $checked = false;
                if( is_array($this->arr_current_img_params_value) )
                    foreach($this->arr_current_img_params_value as $key=>$value)
                         if($key==$params_row[$i]['id'] AND $value==$v['cod'] ) {
                            $checked=true;
                            break;
                         }

                // Р¤РѕСЂРјР°С‚РёСЂРѕРІР°РЅРЅС‹Р№ РІС‹РІРѕРґ С‚РµРєСЃС‚Р° Р»РёР±Рѕ СЃСЃС‹Р»РєРё РїР°СЂР°РјРµС‚СЂР°
                if($checked==true) {
                    $str .='<span class="paramSelected">'.$prefix.' '.$v['name'].' '.$sufix.'</span> | ';
                    $showAll=true;
                }
                else if ($param_str!=NULL)
                        $str .='<a href="'.$link.'?'.$param_str.'&'.PARAM_VAR_NAME.PARAM_VAR_SEPARATOR.$params_row[$i]['id'].'='.$v['cod'].$sorting.'">'.$prefix.' '.$v['name'].' '.$sufix.'</a> | ';
                     else
                        $str .='<a href="'.$link.'?'.PARAM_VAR_NAME.PARAM_VAR_SEPARATOR.$params_row[$i]['id'].'='.$v['cod'].$sorting.'">'.$prefix.' '.$v['name'].' '.$sufix.'</a> | ';
              }

              // Р’С‹РІРѕРґ СЃСЃС‹Р»РєРё "Р’СЃРµ"
              if($showAll==true) {
                  if ($param_str!=NULL)
                        $str .='<a href="'.$link.'?'.$param_str.$sorting.'">Р’СЃРµ</a>';

                  else
                        $str .='<a href="'.$link.'?'.$sorting.'">Р’СЃРµ</a>';
                  $filtr =true;
              }
              else
                    $str .='<span class="param_all">Р’СЃРµ</span>';
          }

          $str .= '</div></div><div class="next_line"></div>';
       }
       if($filtr)
            $str .='<div class="paramClear" align="right"><a href="'.$link.'?'.$sorting.'"<img src="/images/design/paramClearBtn.gif"</a></div>';
       return $str;
   } //end of function GetParamsNamesValuesOfPropInStr()
   
   
   // ================================================================================================
   // Function : ShowParamsOfProp()
   // Version : 1.0.0
   // Date : 21.04.2006
   // Parms :
   // Returns : true,false / Void
   // Description : show details parameters of curent position of catalogue on the front-end
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 08.04.2006
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowParamsOfPropInList($id,$id_cat = 0)
   {
    //--------------------------------------------------------------------------------------------------
    //------------------------------------ SHOW PARAMETERS ---------------------------------------------
    //--------------------------------------------------------------------------------------------------
    $this->id = $id;
    if($id_cat == 0) $id_cat = $this->id_cat;
    $params = $this->IsParams( $id_cat );
    if ( $params==0 ) return true;

    $style1 = '';
    $style2 = '';
    $params_row = $this->GetParams($id_cat,1,1);
    $value=$this->GetParamsValuesOfProp( $this->id );
    //print_r($value);
    $resSizeName='Размеры:';
    $resSize='';
    $resSizeNameSize='';
    for ($i=0;$i<count($params_row);$i++){

      if ( (float)$i/2 == round( $i/2 ) )
      {
       echo '<TR CLASS="'.$style1.'">';
      }
      else echo '<TR CLASS="'.$style2.'">';

      isset($value[$params_row[$i]['id']]) ? $val_from_table = $value[$params_row[$i]['id']] : $val_from_table = NULL;
      if( $id!=NULL ) $this->Err!=NULL ? $val=$this->arr_params[$params_row[$i]['id']] : $val=$val_from_table;
      else $val=$this->arr_params[$params_row[$i]['id']];
      if ( count($val)==0 OR empty($val) ) continue;

      if($params_row[$i]['type']!=1){
          if(!empty($resSize)){
              ?><div class="paramProdText"><span><?=$resSizeName?></span> <?=$resSize?> (<?=$resSizeNameSize?>)</div><?
              $resSize = '';
          }
          if($params_row[$i]['type']!=6){
              ?><div class="paramProdText"><span><?=stripslashes($params_row[$i]['name']);?>:</span><?
          }else{
              ?><div class="paramProdColor"><div class="paramProdColorName"><?=stripslashes($params_row[$i]['name']);?>:</div><?
          }
      }else{
          if(strlen($resSizeNameSize)>0) $resSizeNameSize .= 'x';
          $resSizeNameSize .= $params_row[$i]['name'][0].$params_row[$i]['name'][1];
      }

      $tblname = TblModCatalogParamsVal;//$this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);
      //echo '<br> $tblname='.$tblname;

      switch ($params_row[$i]['type'] ) {
        case '1':
                if(strlen($resSize)>0) $resSize .= 'x';
                $resSize .= $val;
                break;
        case '2':
                echo stripslashes($params_row[$i]['prefix']);
                echo $this->Spr->GetNameByCod(TblSysLogic,$val, $this->lang_id, 1);
                echo stripslashes($params_row[$i]['sufix']);
                break;
        case '3':
                echo stripslashes($params_row[$i]['prefix']);
                echo strip_tags($this->GetNameOfParamVal($params_row[$i]['id_categ'], $params_row[$i]['id'],$val, $this->lang_id, 1));
                echo stripslashes($params_row[$i]['sufix']);
                break;
        case '4':
                echo stripslashes($params_row[$i]['prefix']);
                //echo strip_tags($this->GetNameOfParamVal($params_row[$i]['id_categ'], $params_row[$i]['id'],$val, $this->lang_id, 1));
                //echo $this->GetListNameOfParamVal( $params_row[$i]['id_categ'], $params_row[$i]['id'], $this->lang_id );
                echo ' '.$this->GetListNameOfParamValNew( $params_row[$i]['id_categ'], $params_row[$i]['id'], $this->lang_id ,$val);
                echo stripslashes($params_row[$i]['sufix']);
                break;
        case '5':
                echo stripslashes($params_row[$i]['prefix']);
                echo $val;
                echo stripslashes($params_row[$i]['sufix']);
                break;
         
         case '6':
                $this->GetListColorsOfParamVal( $params_row[$i]['id_categ'], $params_row[$i]['id'], $this->lang_id ,$val,$id);
                break;
      }
      if($params_row[$i]['type']!=1){
          ?></div><?
      }
    }
    if(!empty($resSize)){
              ?><div class="paramProdText"><span><?=$resSizeName?></span> <?=$resSize?> (<?=$resSizeNameSize?>)</div><?
              $resSize = '';
          }
    //--------------------------------------------------------------------------------------------------
    //---------------------------------- END SHOW PARAMETERS -------------------------------------------
    //--------------------------------------------------------------------------------------------------
   } // end of function ShowParamsOfProp()
   
   function ShofJSFoParam($rows = 0,$cnt_img = 5){
       ?><script type="text/javascript">
        var mutex = false;
        var timeOut = 250;
        var item = 32;
        var left_stop = 0;
        var rows = [<?=$rows?>];
        var left_item = [<?=$rows?>];
        var keys = [<?=$rows?>];
        
        function addSize(rowser,id){
            //alert(id);
            rows[id] = rowser;
            left_item[id] = 0;
        }
        
        function left(id,id_prop){
            left_item[id] = left_item[id] + item;
            chengLeft(id);
            chekStop(id,id_prop);
        }
        function right(id,id_prop){
            left_item[id] = left_item[id] - item;
            chengLeft(id);
            chekStop(id,id_prop);
        }
        function chekStop(id,id_prop){
            var right_stop = (rows[id]-<?=$cnt_img?>)*(-1*item);
            //alert('right_stop='+right_stop+'left_stop='+left_stop+'rows[id]='+rows[id]);
            //alert(left_item[id]);
            if(right_stop<=left_stop){
                if(left_stop == left_item[id]){
                    $('#color_fon'+id_prop+' #left'+id).css('display','none');
                }else{
                    $('#color_fon'+id_prop+' #left'+id).css('display','block');
                }

                if(right_stop == left_item[id]){
                    $('#color_fon'+id_prop+' #right'+id).css('display','none');
                }else{
                    $('#color_fon'+id_prop+' #right'+id).css('display','block');
                }
            }else{
                $('#color_fon'+id_prop+' #right'+id).css('display','none');
                $('#color_fon'+id_prop+' #left'+id).css('display','none');
            }
        } 

        function chengLeft(id){
            if(!mutex){
                mutex = true;
                $('#block'+id).animate({left: + left_item[id]+'px'},timeOut);
                setTimeout("mutex = false;", timeOut);
            }
        }
        
        function showColorId(id_prop,colorId){
            $('#colorId'+id_prop).val(colorId);
            $('#color_fon'+id_prop+' .colorId'+id_prop).removeClass('sel_div');
            $('#color_fon'+id_prop+' #colorId'+colorId).addClass('sel_div');
            
            price = parseInt($('#priceFoIdProp_'+id_prop+'_'+colorId).val());
            //alert(price);
            if(price!=0)$('#priceProp_'+id_prop).html(price);
        }
    </script><?
   }
   // ================================================================================================
   // Function : ShowParamsOfProp()
   // Version : 1.0.0
   // Date : 21.04.2006
   // Parms :
   // Returns : true,false / Void
   // Description : show details parameters of curent position of catalogue on the front-end
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 08.04.2006
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowParamsOfProp($id,$manufac = NULL)
   {
    //--------------------------------------------------------------------------------------------------
    //------------------------------------ SHOW PARAMETERS ---------------------------------------------
    //--------------------------------------------------------------------------------------------------
    $this->id = $id;

    $params = $this->IsParams( $this->id_cat );
    if ( $params==0 ) return true;

    $style1 = 'tovarDetaiPropGray';
    $style2 = 'tovarDetaiPropWhite';
    $params_row = $this->GetParams($this->id_cat);
    $value=$this->GetParamsValuesOfProp( $this->id );
    $cnt=0;
    $tmp_row = array();
    if($manufac){?><div class="tovarDetaiPropGray">Производитель: <?=$manufac?></div><?}
    else{$style1 = 'tovarDetaiPropWhite';$style2 = 'tovarDetaiPropGray';}
    for ($i=0;$i<count($params_row);$i++){
      $cnt++;
      if ( $cnt%2==0 )
      {
       $class =$style1;
      }
      else $class =$style2;

      isset($value[$params_row[$i]['id']]) ? $val_from_table = $value[$params_row[$i]['id']] : $val_from_table = NULL;
      if( $id!=NULL ) $this->Err!=NULL ? $val=$this->arr_params[$params_row[$i]['id']] : $val=$val_from_table;
      else $val=$this->arr_params[$params_row[$i]['id']];
      if ( count($val)==0 OR empty($val) ) continue;

      if($params_row[$i]['type']!=6){
          ?><div class="<?=$class?>"><?=stripslashes($params_row[$i]['name']);?>: <?
      }
      else{
         $cnt--;
         $tmp_row[] = $val; 
         $tmp_row_name[] = stripslashes($params_row[$i]['name']);
         $params_row_tmp[] = $params_row[$i];
      }
      $tblname = TblModCatalogParamsVal;//$this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);
      //echo '<br> $tblname='.$tblname;

      switch ($params_row[$i]['type'] ) {
        case '1':
                echo stripslashes($params_row[$i]['prefix']).' '.$val.' '.stripslashes($params_row[$i]['sufix']);
                break;
        case '2':
                echo stripslashes($params_row[$i]['prefix']).' ';
                echo $this->Spr->GetNameByCod(TblSysLogic,$val, $this->lang_id, 1).' ';
                echo stripslashes($params_row[$i]['sufix']);
                break;
        case '3':
                echo stripslashes($params_row[$i]['prefix']).' ';
                echo strip_tags($this->GetNameOfParamVal($params_row[$i]['id_categ'], $params_row[$i]['id'],$val, $this->lang_id, 1));
                echo stripslashes($params_row[$i]['sufix']);
                break;
        case '4':
                echo stripslashes($params_row[$i]['prefix']).' ';
                echo $this->GetListNameOfParamValNew( $params_row[$i]['id_categ'], $params_row[$i]['id'], $this->lang_id ,$val); //$this->Spr->GetNamesInStr( $tblname, _LANG_ID, $val, ',' );// ShowInCheckBox( $tblname, 'arr_params['.$params_row[$i]['id'].']', 3, $val, 'right','disabled' );
                echo stripslashes($params_row[$i]['sufix']);
                break;
        case '5':
                echo stripslashes($params_row[$i]['prefix']);
                echo $val;
                echo stripslashes($params_row[$i]['sufix']);
                break;
         }
         if($params_row[$i]['type']!=6){?></div><?}
    }
    ?><div class="tovarDetailColor"><?
    if(is_array($tmp_row) && !empty($tmp_row)){
        $size_of = sizeof($tmp_row);
        $this->ShofJSFoParam($size_of,11);
        for($i = 0;$i<$size_of;$i++){
            $val = $tmp_row[$i];
            $name = $tmp_row_name[$i];
            $params_row = $params_row_tmp[$i];
            ?><div class="tovarDetailColor">
                <div class="tovarDetailColorName"><?=$name?></div>
                <div class="tovarDetailColorCenter"><?
                    $this->GetListColorsOfParamVal( $params_row['id_categ'], $params_row['id'], $this->lang_id ,$val,$id,11);
                ?></div>
            </div><?
        }
    }
    ?></div><?
    //--------------------------------------------------------------------------------------------------
    //---------------------------------- END SHOW PARAMETERS -------------------------------------------
    //--------------------------------------------------------------------------------------------------
   } // end of function ShowParamsOfProp()


   // ================================================================================================
   // Function : GetParamsValuesOfPropInTable()
   // Version : 1.0.0
   // Date : 18.04.2006
   // Parms :   $id         / id of curent position
   //           $divider    / symbol to divide parameters one from one. (default defider is <br>)
   //           $id_img     / id of the image (for image influence on parameters)
   // Returns : true,false / Void
   // Description : return values of parameters in string for current position of catalogue
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 18.04.2006
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function GetParamsValuesOfPropInTable( $id, $id_img = NULL )
   {
       $id_cat = $this->GetCategory($id);
       $params = $this->IsParams( $id_cat );
       if ( $params==0 ) return;

       $params_row = $this->GetParams($id_cat);
       $value=$this->GetParamsValuesOfProp( $id );
       $str=NULL;
       ?>
       <table border="0" cellpadding="0" cellspacing="0">
        <tr><td></td></tr>
       <?
       $j=0;
       for ($i=0;$i<count($params_row);$i++){
          $tblname = $this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);

          if ( !empty($id_img) ){
            $value_param_img = $this->GetParamsValuesOfPropForImg($id_img, $params_row[$i]['id'] );
            //echo '<br> $value_param_img='; print_r($value_param_img);
            isset($value_param_img[$params_row[$i]['id']]) ? $val_from_table = $value_param_img[$params_row[$i]['id']] : $val_from_table = NULL;
            if ( empty($val_from_table)) {
                isset($value[$params_row[$i]['id']]) ? $val_from_table = $value[$params_row[$i]['id']] : $val_from_table = NULL;
            }
          }
          else {
            isset($value[$params_row[$i]['id']]) ? $val_from_table = $value[$params_row[$i]['id']] : $val_from_table = NULL;
          }
          $val=$val_from_table;

          //echo '<br> $val='.$val;

          $prefix = stripslashes($params_row[$i]['prefix']);
          $sufix = stripslashes($params_row[$i]['sufix']);
          switch ($params_row[$i]['type'] ) {
           case '1':
                $val = $val;
                break;
           case '2':
                $val = $this->Spr->GetNameByCod(TblSysLogic,$val, $this->lang_id, 1);
                break;
           case '3':
                $val = $this->Spr->GetNameByCod($tblname,$val, $this->lang_id, 1);
                break;
           case '4':
                $val = $this->Spr->GetNamesInStr( $tblname, _LANG_ID, $val, ',' );
                break;
           case '5':
                $val = str_replace("\n","<br>",$val);
                break;
          }
          if (empty($val)) continue;
          $j++;
          ?><tr>
             <td><?=stripslashes($params_row[$i]['name']);?>:&nbsp;<?=$prefix;?></td>
             <td><img src="/images/design/spacer.gif" width="5" alt="" title=""/></td>
             <td><?=$val.' '.$sufix;?></td></tr><?
       }
       ?></table><?
       if ($j==0) return false;
       //echo '<br> $str='.$str;
   } //end of function  GetParamsValuesOfPropInTable()


   // ================================================================================================
   // Function : GetParamsValuesOfPropInStr()
   // Version : 1.0.0
   // Date : 18.04.2006
   // Parms :   $id / id of curent position
   //           $divider /  symbol to divide parameters one from one. (default defider is <br>)
   // Returns : true,false / Void
   // Description : return values of parameters in string for current position of catalogue
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 18.04.2006
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function GetParamsValuesOfPropInStr( $id, $divider='<br>' )
   {
       $id_cat = $this->GetCategory($id);
       $params = $this->IsParams( $id_cat );
       if ( $params==0 ) return;

       $params_row = $this->GetParams($id_cat);
       $value=$this->GetParamsValuesOfProp( $id );
       $str=NULL;
       for ($i=0;$i<count($params_row);$i++){
          $tblname = $this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);

          isset($value[$params_row[$i]['id']]) ? $val_from_table = $value[$params_row[$i]['id']] : $val_from_table = NULL;

          if( $id!=NULL ) $this->Err!=NULL ? $val=$this->arr_params[$params_row[$i]['id']] : $val=$val_from_table;
          else $val=$this->arr_params[$params_row[$i]['id']];

          $prefix = stripslashes($params_row[$i]['prefix']);
          $sufix = stripslashes($params_row[$i]['sufix']);
          switch ($params_row[$i]['type'] ) {
           case '1':
                $val = $val;
                break;
           case '2':
                $val = $this->Spr->GetNameByCod(TblSysLogic,$val, $this->lang_id, 1);
                break;
           case '3':
                $val = $this->Spr->GetNameByCod($tblname,$val, $this->lang_id, 1);
                break;
           case '4':
                $val = $this->Spr->GetNamesInStr( $tblname, _LANG_ID, $val, ',' );
                break;
           case '5':
                $val = $val;
                break;
          }
          $tmp_str = '<b>'.stripslashes($params_row[$i]['name']).':</b>&nbsp;'.$prefix.' '.$val.' '.$sufix;
          if ( empty($str) ) $str = $tmp_str;
          else $str = $str.$divider.$tmp_str;
       }
       //echo '<br> $str='.$str;
       return $str;
   } //end of function  GetParamsValuesOfPropInStr()


// ================================================================================================
// Function : ShowSearchForm()
// Version : 1.0.0
// Date : 05.04.2006
// Parms :
// Returns : true,false / Void
// Description : show search form of catalogue on the front-end
// ================================================================================================
// Programmer : Igor Trokhymchuk
// Date : 05.04.2006
// Reason for change : Reason Description / Creation
// Change Request Nbr:
// ================================================================================================
function ShowSearchForm()
{
?>
<h1 class="bgrnd"><?=$this->multi['TXT_SEARCH_CATALOG'];?></h1>
<div class="body">
    <form name="quick_find" method="post" action="<?=_LINK?>catalog/search/result/">
        <input type="hidden" name="task" value="quick_search">
        <!--input type="hidden" name="categ" value=""-->

        <?if(!empty($this->search_keywords))
            $value = $this->search_keywords;
        else
            $value ='РЈРєР°Р¶РёС‚Рµ РЅР°РёРјРµРЅРѕРІР°РЅРёРµ';
        ?>
        <div>
         <input type="text" onblur="if(this.value=='') { this.value='РЈРєР°Р¶РёС‚Рµ РЅР°РёРјРµРЅРѕРІР°РЅРёРµ'; }" onfocus="if(this.value=='РЈРєР°Р¶РёС‚Рµ РЅР°РёРјРµРЅРѕРІР°РЅРёРµ') { this.value=''; }"  name="search_keywords" value="<?=$value;?>" size="50" maxlength="50" >
         <input type="submit" title="<?=$this->multi['TXT_SEARCH'];?>" value="<?=$this->multi['TXT_SEARCH'];?>">
        </div>
    </form>
</div>
<?
return true;
} //end of function ShowSearchForm()



// ================================================================================================
// Function : ShowSearchResult()
// Version : 1.0.0
// Date : 25.04.2006
// Parms :  $rows - rows with data of result of search
// Returns : true,false / Void
// Description : show all images of current position of catalogue
// ================================================================================================
// Programmer : Igor Trokhymchuk
// Date : 25.04.2006
// Reason for change : Reason Description / Creation
// Change Request Nbr:
// ================================================================================================
function ShowSearchResult($rows, $search_keywords=NULL)
{
?><div class="catalogBorder">
 <div class="categoryContent">
   <div class="categoryCaptionRed">
       <div class="categoryTxt"></div>
   </div>
    <?
    //$this->ShowListOfContentByPages($rows, $search_keywords);
    $this->ShowListShortByPages($rows, $search_keywords);
    ?>
 </div>
</div>
<?
} //end of function ShowSearchResult()


    /**
    * Class method MAP
    * create catalog map for sitemap
    * @return true/false
    * @author Yaroslav Gyryn  <yaroslav@seotm.com>
    * @version 1.0, 17.01.2011
    */
   function MAP()
   {
       $this->catalogProducts = $this->GetProductsArrForSiteMap();  // РЎРїРёСЃРѕРє С‚РѕРІР°СЂРѕРІ РІ РєР°Р¶РґРѕР№ РєР°С‚РµРіРѕСЂРёРё РєР°С‚Р°Р»РѕРіР°
       $this->ShowCatalogMap();
   } // end of function  MAP()

    /**
    * Class method ShowCatalogMap
    * show catalog map for sitemap
    * @param $topLevel - level of category
    * @return true/false
    * @author Yaroslav Gyryn  <yaroslav@seotm.com>
    * @version 1.0, 17.01.2011
    */
    function ShowCatalogMap($topLevel = 0)
    {
        if( !isset($this->treeCatLevels[$topLevel])) return;
        $a_tree = $this->treeCatLevels[$topLevel];
        ?><ul><?
        $keys = array_keys($a_tree);
        $n = count($keys);
        for($i = 0; $i <$n; $i++) {
            $row = $this->treeCatData[$keys[$i]];
            $href = $this->getUrlByTranslit($row['path']);
            $name = stripslashes($row['name']);
            ?><li ><a href="<?=$href;?>"><?=$name;?></a><?
                $this->ShowCatalogMap($row['id']);

              //----------------- show content of the level ----------------------
               if(array_key_exists($row['id'], $this->catalogProducts)) {
                   ?><ul><?
                   $keys2 = array_keys($this->catalogProducts[$row['id']]);
                   $n2 = count($keys2);
                   //foreach($this->catalogProducts[$row['id']] as $k=>$v){
                   for($j=0;$j<$n2;$j++){
                       $v = $this->catalogProducts[$row['id']][$keys2[$j]];
                       $link = $this->getUrlByTranslit($row['path'],$v['translit']);
                       $name = stripslashes($v['name']);
                       if( !empty($name) ) {
                           ?><li><a href="<?=$link;?>" title="<?=$name?>"><?=$name;?></a><?
                       }
                   }
                   ?></ul><?
               }
             //------------------------------------------------------------------
            ?></li><?
        }
        ?></ul><?
    }// end of function ShowCatalogMap()


   // ================================================================================================
   // Function : ShowErr()
   // Version : 1.0.0
   // Date : 10.01.2006
   //
   // Parms :
   // Returns :      true,false / Void
   // Description :  Show errors
   // ================================================================================================
   // Programmer :  Igor Trokhymchuk
   // Date : 10.01.2006
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowErr($txt=NULL)
   {
       if( empty($txt)) $txt = $this->Err;
       if ($txt){
       echo '
        <table border=0 cellspacing=0 cellpadding=0 class="err" width="98%" align=center>
         <tr><td>'.$txt.'</td></tr>
        </table>';
     }
   } //end of fuinction ShowErr()


    // ================================================================================================
    // Function : ShowLastPositions
    // Version : 1.0.0
    // Date : 14.05.2007
    //
    // Parms :  $rows - count of rows
    // Returns : $res / Void
    // Description : show last positions from catalogue
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 14.05.2007
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowLastPositions( $rows )
    {

       if (count($rows)==0 or !is_array($rows)) return false;
       $settings = $this->GetSettings();
       $cols_in_row = 2;
       //echo '<br> count($rows)='.count($rows);
       //print_r($rows);

       ?>
        <table border="0" cellspacing="0" cellpadding="0">
         <tr>
       <?
       $j=0;
       $i=0;
       foreach($rows as $key=>$value){
          $i++;
          $img = $this->GetFirstImgOfProp($value['id']);

          if ( $j==$cols_in_row )
          {
           ?></tr><tr valign="top"><?
           $j=0;
          }
          $name = $value['name'];

          // for folders links
          if( $this->mod_rewrite==1 ) $link = $this->Link($value['id_cat'], NULL);
          else $link = "catalogcat_".$value['id_cat']."_".$this->lang_id.".html";

          //count($rows)>2 ? $width="34%" : $width="50%";
           ?>

           <td>
            <table border="0" cellspacing="0" cellpadding="0">
             <tr>
              <td>
                <a href="<?=$link;?>" title="<?=addslashes($name);?>" >
                <?
                if( !empty($img) ) { echo $this->ShowCurrentImage($img, 'size_auto=150', '85', NULL, "border=0"); }
                ?>
                </a>
              </td>
             </tr>
            </table>
           </td>
           <?
           $j++;
       } //end foreach

       ?>
        </tr>
       </table>
       <?
    } //end of function ShowLastPositions()

   // ================================================================================================
   // Function : ShowRelatCategs()
   // Version : 1.0.0
   // Date : 07.05.2007
   // Parms :
   // Returns : true,false / Void
   // Description : show relation categories for current category
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 07.05.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowRelatCategs($arr)
   {
       if( !is_array($arr) OR count($arr)==0 ) return false;
       $col_in_row = 3;
       count($arr)==1 ? $width="100%" : ( count($arr)>2 ? $width="33%" : $width="50%" );

       ?>
       <table border="0" cellpadding="0" cellspacing="0">
        <tr>
         <td><h3>Р РЋР ??Р С•РЎвЂљРЎР‚Р С‘РЎвЂљР Вµ РЎвЂљР В°Р С”Р В¶Р Вµ:</h3></td>
        </tr>
        <tr>
       <?
       $i=0;
       foreach($arr as $key=>$value){
           if($i==$col_in_row){
               ?></tr><tr><?
               $i=0;
           }
           if ($value['id_cat1']==$this->id_cat) $id_relat_cat = $value['id_cat2'];
           else $id_relat_cat = $value['id_cat1'];
           $str = $this->GetPathToLevel($id_relat_cat);
           ?>
           <td width="<?=$width;?>" align="center" valign="middle">
            <?=$str;?>
            <?
            $this->ShowRandomContent($this->GetRandomContent2($id_relat_cat, 1, 100000));?>
           </td>
           <?
           $i++;
       }
       ?>
        </tr>
       </table>
       <?
   } //end of function ShowRelatCategs()

   // ================================================================================================
   // Function : GetPathToLevel()
   // Version : 1.0.0
   // Date : 07.05.2007
   //
   // Parms :        $level - id of the category
   // Returns :      $str / string with name of the categoties to current level of catalogue
   // Description :  Return a path to current category
   // ================================================================================================
   // Programmer :  Igor Trokhymchuk
   // Date : 07.05.2007
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function GetPathToLevel($level, $devider = ' > ', $str=NULL)
   {
       $name = $this->Spr->GetNameByCod( TblModCatalogSprName, $level, $this->lang_id, 1 );
       //echo '<br>$str='.$str.' $name='.$name.' <br>';
       if ( !empty($str) ) $str = $name.$devider.$str;
       else $str = '<a href="catalogcat_'.$level.'.html" title="'.addslashes($name).'" > '.$name.'</a>'.$str;

       $tmp_db = DBs::getInstance();
       $q="SELECT * FROM ".TblModCatalog." WHERE `id`='$level'";
       $res = $tmp_db->db_Query( $q );
       //echo '<br>q='.$q.' res='.$res.' $tmp_db->result='.$tmp_db->result;
       if( !$res OR !$tmp_db->result ) return false;
       $row = $tmp_db->db_FetchAssoc();
       if ( $row['level']>0 ) {
         $str = $this->GetPathToLevel($row['level'], $devider, $str);
       }
       //$str = '<a href="'.$script.'&level=0">'.$this->Msg->show_text('TXT_ROOT_CATEGORY').' > </a>'.$str;
       return $str;
   } // end of function GetPathToLevel()

   // ================================================================================================
   // Function : ShowRelatProp()
   // Version : 1.0.0
   // Date : 14.05.2007
   // Parms :
   // Returns : true,false / Void
   // Description : show relation positiona for current positionf of catalog
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 07.05.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowRelatProp($arr)
   {
       //echo '<br>$arr='; print_r($arr);
       if( !is_array($arr) OR count($arr)==0 ) return false;
       $col_in_row = 2;
       count($arr)==1 ? $width="100%" : ( count($arr)>2 ? $width="33%" : $width="50%" );
       ?>
       <h3>Р РЋР ??Р С•РЎвЂљРЎР‚Р С‘РЎвЂљР Вµ РЎвЂљР В°Р С”Р В¶Р Вµ:</h3>
       <table border="0" cellpadding="0" cellspacing="0">
        <tr>
       <?
       $i=0;
       foreach($arr as $key=>$value){
           if($i==$col_in_row){
               ?></tr><tr><?
               $i=0;
           }
           //echo '<br>$value[id_prop1]='.$value['id_prop1'].' $value[id_prop2]='.$value['id_prop2'].' $this->id='.$this->id;
           if ($value['id_prop1']==$this->id) $id_relat = $value['id_prop2'];
           else $id_relat = $value['id_prop1'];
           //echo '<br>$id_relat='.$id_relat;
           //$str = $this->GetPathToLevel($id_relat_prop);
           ?>
           <td width="<?=$width;?>" align="center" valign="bottom">
           <?/*<div align="center"><?=$this->GetPathToLevel($this->GetCategory($id_relat), ' -> ', NULL).' -> '.$this->Spr->GetNameByCod(TblModCatalogPropSprName, $id_relat);?></div>*/?>
           <div align="center"><?=$this->Spr->GetNameByCod(TblModCatalogPropSprName, $id_relat);?></div>
            <table border="0" cellpadding="0" cellspacing="0">
             <tr>
              <td align="center" valign="bottoom">
               <?
               // for folders links
               if( $this->mod_rewrite==1 ) $link = $this->Link($this->GetCategory($id_relat), $id_relat);
               else $link = "catalog_".$this->GetCategory($id_relat)."_".$id_relat."_".$this->lang_id.".html";
               ?>
               <a href="<?=$link;?>">
              <?
               $img = $this->GetFirstImgOfProp($id_relat);
               if( !empty($img) ) echo $this->ShowCurrentImage($img, 'size_auto=150', '85', NULL, 'border=0');
               else echo $this->Spr->GetNameByCod(TblModCatalogPropSprName, $id_relat);
               ?>
               </a>
              </td>
             </tr>
            </table>
           </td>
           <?
           $i++;
       }
       ?>
        </tr>
       </table>
       <?
   } //end of function ShowRelatProp()



   // ================================================================================================
   // Function : ShowResponsesByIdProp()
   // Version : 1.0.0
   // Date : 08.08.2007
   // Parms : $id_prop - id of the position
   // Returns : true,false / Void
   // Description : show form with responses from users about goods
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 08.08.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowResponsesByIdProp($id_prop)
   {
    $tmp_db = DBs::getInstance();
    if ( empty($id_prop) ) return;

    $q = "SELECT * FROM `".TblModCatalogResponse."` WHERE `id_prop`='$id_prop' AND `status`='3' order by `dt` desc";
    $res = $tmp_db->db_Query( $q );
    //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
    if ( !$res ) return false;
    if ( !$tmp_db->result ) return false;
    $rows = $tmp_db->db_GetNumRows();
    if ($rows>0) {
        ?>
        <h2><?=$this->Msg->show_text('TXT_FRONT_USERS_RESPONSES');?></h2>
        <table border="0" cellpadding="0" cellspacing="0">
         <?
            for($i=0;$i<$rows;$i++){
                $row = $tmp_db->db_FetchAssoc();
                ?>
             <tr>
             <td>
               [<?=$row['dt']?>]&nbsp;<?=stripslashes($row['name']);
               if($row['rating']>0) { echo $this->Msg->show_text('TXT_FRONT_USER_RATING_IS'); ?><b><?=$row['rating'];?></b><?}
               ?>
              </td>
             </tr>
             <tr>
              <td><?=stripslashes($row['response'])?></td>
             </tr>
             <tr><td height="10"></td></tr>
                <?
            }
        ?>
        </table>
        <?
    }
   return true;
   } //end of function ShowResponsesByIdProp()

   // ================================================================================================
   // Function : ShowResponses()
   // Version : 1.0.0
   // Date : 08.08.2007
   // Parms :
   // Returns : true,false / Void
   // Description : show form with responses from users about goods
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 08.08.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowResponses()
   {
    $tmp_db = DBs::getInstance();
    ?><h1><?=$this->Msg->show_text('TXT_FRONT_USERS_RESPONSES');?></h1><?
    $mas = $this->GetCatalogInArray(NULL, '--- '.$this->Msg->show_text('TXT_SELECT_POSITIONS').' ---', NULL, NULL, 1, 'front');
    $name_fld = 'val';

    $scriplink = '/response.php?task=show_responses';                                     //'onChange="CheckCatalogPosition(this, this.value, '."'".$this->Msg->show_text('ERR_SELECT_POSITION')."'".'); location='.$scriplink.'&'.$name_fld.'=this.value"'
    ?><div><?$this->Form->SelectAct( $mas, $name_fld, 'curcod='.$this->id,  "onChange=\"ret = CheckCatalogPosition(this, this.value, '".$this->Msg->show_text('ERR_SELECT_POSITION')."'); if( ret== true) {location='$scriplink&$name_fld='+this.value} \""   );?></div><?


    if ( empty($this->id) ) return;

    $q = "SELECT * FROM `".TblModCatalogResponse."` WHERE `id_prop`=$this->id AND `status`='3' order by `dt` desc";
    $res = $tmp_db->db_Query( $q );
    //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
    if ( !$res ) return false;
    if ( !$tmp_db->result ) return false;
    $rows = $tmp_db->db_GetNumRows();
   ?>
    <table border="0" cellpadding="0" cellspacing="0">
     <?
    if ($rows==0) {?><tr><td><?=$this->Msg->show_text('TXT_FRONT_NO_RESPONSES');?></td></tr><?}
    /*if ($this->task=="save_response") {?><tr><td><?=$this->Msg->show_text('TXT_FRONT_RESPONSES_IS_ADDED');?></td></tr><?}*/
    if ($this->task=="save_response") {?><tr><td><?=$this->Msg->show_text('TXT_FRONT_RESPONSES_IS_ADDED_NOW');?></td></tr><?}

    for($i=0;$i<$rows;$i++){
        $row = $tmp_db->db_FetchAssoc();
        ?>
     <tr>
      <td>
       [<?=$row['dt']?>]&nbsp;<?=stripslashes($row['name']);
       if($row['rating']>0) { echo $this->Msg->show_text('TXT_FRONT_USER_RATING_IS'); ?><b><?=$row['rating'];?></b><?}
       ?>
      </td>
     </tr>
     <tr>
      <td><?=stripslashes($row['response'])?></td>
     </tr>
     <tr><td height="10"></td></tr>
        <?
    }
     ?>
    </table>
    <?=$this->ShowResponseForm();?>
   <?
   return true;
   } //end of function ShowResponses()

   // ================================================================================================
   // Function : ShowResponseForm()
   // Version : 1.0.0
   // Date : 08.08.2007
   // Parms :
   // Returns : true,false / Void
   // Description : show form to leave responses and rating
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 08.08.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowResponseForm()
   {
       $settings = $this->GetSettings();
       if ( isset($settings['responses']) AND $settings['responses']=='1' ) $is_response = true;
       else $is_response = 0;
       if ( isset($settings['rating']) AND $settings['rating']=='1' ) $is_rating = true;
       else $is_rating = 0;

       // for folders links
       if( $this->mod_rewrite==1 ) $link = $this->Link($this->GetCategory($this->id), $this->id, 'response');
       else $link = "leave_comments.html";

       $v1 = rand(1,9);
       $v2 = rand(1,9);
       $sum = $v1+$v2;

       $this->ShowJS();
       $this->Form->WriteFrontHeader( 'save_response', $link, $task = 'save_response', 'onsubmit="return check_form_response(this, this.my_gen_v.value, '.$is_response.', '.$is_rating.' );"' )
       ?>
     <table border="0" cellpadding="1" cellspacing="0">
      <input type="hidden" name="curcod" value="<?=$this->id?>">
      <input type="hidden" name="my_gen_v" value="<?=$sum;?>" />
      <tr>
       <td><h2><?=$this->Msg->show_text('TXT_FRONT_LEAVE_RESPONSES');?></h2></td>
      </tr>
      <tr>
       <td>
        <table border="0" cellpadding="2" cellspacing="2">
        <tr>
         <td><?=$this->Msg->show_text('TXT_FRONT_USER_NAME');?>:&nbsp;<span class="inputRequirement">*</span></td>
         <td><?$this->Form->TextBox( 'name',$this->name, 'size="40"' );?></td>
        </tr>
        <tr>
         <td><?=$this->Msg->show_text('TXT_FRONT_USER_EMAIL');?>:&nbsp;<span class="inputRequirement">*</span></td>
         <td><?$this->Form->TextBox( 'email',$this->email, 'size="40"' );?></td>
        </tr>
        <?
        if ( $is_response ) {
        ?>
        <tr>
         <td><?=$this->Msg->show_text('TXT_FRONT_USER_RESPONSE');?>:&nbsp;<span class="inputRequirement">*</span></td>
         <td><?$this->Form->TextArea( 'response', $this->response, 9, 60, NULL );?></td>
        </tr>
        <?}?>
        <?
        if ( $is_rating ) {
        ?>
        <tr>
         <td><?=$this->Msg->show_text('TXT_FRONT_USER_RATING');?>:&nbsp;<span class="inputRequirement">*</span></td>
         <td>
          <?
          $this->Form->Radio( 'rating', 1, "0",  "1" );?>&nbsp;&nbsp;&nbsp;<?
          $this->Form->Radio( 'rating', 2, "0",  "2" );?>&nbsp;&nbsp;&nbsp;<?
          $this->Form->Radio( 'rating', 3, "0",  "3" );?>&nbsp;&nbsp;&nbsp;<?
          $this->Form->Radio( 'rating', 4, "0",  "4" );?>&nbsp;&nbsp;&nbsp;<?
          $this->Form->Radio( 'rating', 5, "0",  "5" );
          ?>
         </td>
        </tr>
        <?}?>
        <tr>
         <td colspan="2"><b><?=$this->Msg->show_text('TXT_FRONT_SPAM_PROTECTION');?>:&nbsp;<span class="inputRequirement">*</span></b> <b><?=$this->Msg->show_text('TXT_FRONT_SPAM_PROTECTION_SPECIFY_SUM');?>&nbsp;<?=$v1;?>+<?=$v2;?>?</b> <?$this->Form->TextBox( 'usr_v', NULL, 'size="2"' );?></td>
        </tr>
        <tr>
         <td colspan="2" align="left"><span class="inputRequirement">*</span> <?=$this->Msg->show_text('TXT_FRONT_REQUIREMENT_FIELDS');?></td>
        </tr>
        </table>
       </td>
      </tr>
      <tr>
       <td><?$this->Form->Button( 'save_response', $this->Msg->show_text('TXT_FRONT_ADD_RESPONSE') );?></td>
      </tr>
     </form>
     </table>
       <?
       $this->Form->WriteFrontFooter();
   } //end of function ShowResponseForm()


   // ================================================================================================
   // Function : ShowJS()
   // Version : 1.0.0
   // Date : 08.08.2007
   // Parms :
   // Returns : true,false / Void
   // Description : show form with rating from users about goods
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 08.08.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowJS()
   {
       ?>
     <script type="text/javascript">
    var form = "";
    var submitted = false;
    var error = false;
    var error_message = "";

    function check_input(field_name, field_size, message) {
        if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
        var field_value = form.elements[field_name].value;

        if (field_value == '' || field_value.length < field_size) {
          error_message = error_message + "* " + message + "\n";
          error = true;
        }
      }
    }

    function check_radio(field_name, message) {
      var isChecked = false;

      if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
        var radio = form.elements[field_name];

        for (var i=0; i<radio.length; i++) {
          if (radio[i].checked == true) {
            isChecked = true;
            break;
          }
        }

        if (isChecked == false) {
          error_message = error_message + "* " + message + "\n";
          error = true;
        }
      }
    }

    function check_select(field_name, field_default, message) {
      if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
        var field_value = form.elements[field_name].value;

        if (field_value == field_default) {
          error_message = error_message + "* " + message + "\n";
          error = true;
        }
      }
    }

    function check_antispam(field_name, usr_v, message) {
        if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
        var field_value = form.elements[field_name].value;

        if (field_value == '' || field_value != usr_v) {
          error_message = error_message + "* " + message + "\n";
          error = true;
        }
      }
    }

    function check_form_response(form_name, my_gen_v, response, rating) {
      error_message = '';
      if (submitted == true) {
        alert("<?=$this->Msg->show_text('MSG_FRONT_ERR_FORM_ALREADY_SUBMITED');?>");
        return false;
      }

      error = false;
      form = form_name;

      check_input("name", 2, "<?=$this->Msg->show_text('MSG_FRONT_ERR_SPECIFY_YOUR_NAME');?>");
      check_input("email", 2, "<?=$this->Msg->show_text('MSG_FRONT_ERR_SPECIFY_YOUR_EMAIL');?>");
      if( response == true) check_input("response", 5, "<?=$this->Msg->show_text('MSG_FRONT_ERR_SPECIFY_YOUR_RESPONSE');?>");
      if( rating == true ) check_radio("rating", "<?=$this->Msg->show_text('MSG_FRONT_ERR_SPECIFY_YOUR_RATING');?>");
      check_antispam("usr_v", my_gen_v, "<?=$this->Msg->show_text('MSG_FRONT_ERR_SPECIFY_ANTISMAP_SUM');?>");

      if (error == true) {
        alert(error_message);
        return false;
      } else {
        submitted = true;
        return true;
      }
    }
     </script>
    <?
   } // end of functin ShowJS()

    // ================================================================================================
    // Function : Link()
    // Version : 1.0.0
    // Date : 19.05.2007
    // Parms :  $id_cat     - id of the category
    //          $id_prop    - id of the current position
    //          $param      - parameter for build link (may be for example 'print', 'zoom', 'goto')
    //          $id_img     - id of the image or path od the image
    //          $watermark  - watermark for image
    // Returns : true,false / Void
    // Description :  build link with translit name
    // ================================================================================================
    // Programmer : Ihor Trokhymchuk
    // Date : 19.05.2007
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function Link($id_cat = NULL, $id_prop = NULL, $param = NULL, $id_img = NULL, $watermark = NULL, $id_file = NULL )
    {
        if( $this->mod_rewrite==1){
            //$arr_categs = $this->get_top_levels_in_array($id_cat);
            $arr_categs = $this->GetTopLevelsTranslit($id_cat, $this->lang_id);

            //echo '<br>$arr_categs=';print_r($arr_categs);
            $link=NULL;
            $translit_str = NULL;
            foreach($arr_categs as $key=>$value){
                //echo '<br>$key='.$key.' $value='.$value;
                if( empty($key)) continue;
                // get translit name for category
                $translit_categ = $value;
                $translit_str = $translit_categ.'/'.$translit_str;
            }
            //echo '<br>$translit_str ='.$translit_str ;

            $link = $translit_str;
            if( !empty($id_prop) ) {
                // get translit name for current position
                $translit_prop = $this->GetTranslitById($id_cat, $id_prop, $this->lang_id);
                //echo '<br>$translit_prop='.$translit_prop.' $id_prop='.$id_prop;
                $link = $link.$translit_prop.'.html';
            }

            if( !defined("_LINK")) {
                $Lang = &check_init('SysLang', 'SysLang', 'NULL, "front"');
                if( _LANG_ID!=$Lang->GetDefFrontLangID() ) define("_LINK", "/".$Lang->GetLangShortName(_LANG_ID)."/");
                else define("_LINK", "/");
            }

            switch($param){
                case 'zoom':
                    $link = _LINK.'catalog/'.$translit_str.$translit_prop.'/make_zoom/'.$id_img.'_wtm_'.$watermark.'.html';
                    break;
                case 'goto':
                    $link = _LINK.'goto/'.$id_cat.'/'.$id_prop;
                    break;
                case 'response':
                    $link = _LINK.'leave_comments/'.$id_cat.'/'.$id_prop;
                    break;
                case 'show_files':
                    //$Logon = new UserAuthorize();
                    $link = NULL;
                    //echo '<br>$id_file='.$id_file;
                    if( !empty($id_file) ){
                        if( !empty($this->Logon->user_id) AND !empty($id_file) ){
                            $tmp = $this->GetFileData($id_file);
                            $link = _LINK.'catalog/'.$translit_str.$translit_prop.'/files/'.$id_file;
                            //$link = _LINK.Catalog_Upload_Files_Path.'/'.$id_prop.'/'.$tmp['path'];
                        }
                        else {
                            //$referer_page = str_replace('&','AND',$_SERVER['REQUEST_URI']);
                            //$link = _LINK.'login.php?referer_page='.$referer_page.'';
                            $link = _LINK.'catalog/'.$translit_str.$translit_prop.'/files/'.$id_file;
                        }
                    }
                    else $link='#111111';
                    //echo '<br>$id_prop='.$id_prop.' $translit_prop='.$translit_prop.' $link='.$link;
                    break;
                case 'print':
                    $link = _LINK.'print-it/catalog/'.$id_cat.'/'.$id_prop.'.html';
                    break;
                default:
                    if(CATALOG_TRASLIT){
                        $link = _LINK.$link;
                    }else
                        $link = _LINK.'catalog/'.$link;
                    
                    break;
            }
        }//end if
        else{
            if( !empty($id_cat) AND empty($id_prop) ) $link = "catalogcat_".$id_cat.'_'.$this->lang_id.'.html';
            elseif( !empty($id_cat) AND !empty($id_prop) ) $link = "catalog_".$id_cat.'_'.$id_prop.'_'.$this->lang_id.'.html';
            else $link = 'catalog.html';
        }
        return $link;
    }// end of function Link()

    // ================================================================================================
    // Function : GetLink()
    // Version : 1.0.0
    // Date : 23.05.2010
    // Parms :  $id_cat     - id of the category
    //          $translit_prop - translit of the position
    //          $param      - parameter for build link (may be for example 'print', 'zoom', 'goto')
    // Returns : true,false / Void
    // Description :  build link with translit name
    // ================================================================================================
    // Programmer : Ihor Trokhymchuk
    // Date : 23.05.2010
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function GetLink($id_cat, $translit_prop = NULL, $param = NULL )
    {
        //echo '<br>$translit_prop='.$translit_prop.' $id_prop='.$id_prop;
        if( !isset($this->arrCategsTranslit[$id_cat]) ) $this->arrCategsTranslit[$id_cat] = $this->GetTopLevelsTranslit($id_cat, $this->lang_id);

        $link=NULL;
        $translit_str = NULL;
        foreach($this->arrCategsTranslit[$id_cat] as $key=>$value){
            //echo '<br>$key='.$key.' $value='.$value;
            if( empty($key)) continue;
            // get translit name for category
            $translit_categ = $value;
            $translit_str = $translit_categ.'/'.$translit_str;
        }
        //echo '<br>$translit_str ='.$translit_str ;

        $link = $translit_str;
        $link = $link.$translit_prop.'.html';


        if( !defined("_LINK")) {
            $Lang = &check_init('SysLang', 'SysLang', 'NULL, "front"');
            if( _LANG_ID!=$Lang->GetDefFrontLangID() ) define("_LINK", "/".$Lang->GetLangShortName(_LANG_ID)."/");
            else define("_LINK", "/");
        }

        switch($param){
            default:
                $link = _LINK.'catalog/'.$link;
        }
        return $link;
    }// end of function GetLink()


   // ================================================================================================
   // Function : BuildNumberNameByParams()
   // Version : 1.0.0
   // Date : 28.08.2007
   // Parms :
   // Returns : true,false / Void
   // Description : save parameters values of current position in catalogue to the field numder_name
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 28.08.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function BuildNumberNameByParams()
   {
    $str_out = NULL;
    $params_row = $this->GetParams($this->id_cat);
    $value=$this->GetParamsValuesOfProp( $this->id );
    for ($i=0;$i<count($params_row);$i++){
        $tblname = $this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);

        isset($value[$params_row[$i]['id']]) ? $val_from_table = $value[$params_row[$i]['id']] : $val_from_table = NULL;

        if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->arr_params[$params_row[$i]['id']] : $val=$val_from_table;
        else $val=addslashes($this->arr_params[$params_row[$i]['id']]);

        $prefix = $this->Spr->GetNameByCod(TblModCatalogParamsSprPrefix,($params_row[$i]['id']), $this->lang_id, 1);
        $sufix = $this->Spr->GetNameByCod(TblModCatalogParamsSprSufix,($params_row[$i]['id']), $this->lang_id, 1);

        switch ($params_row[$i]['type'] ) {
           case '1':
                $val = $val;
                break;
           case '2':
                $val = $this->Spr->GetShortNameByCod(TblSysLogic,$val, $this->lang_id, 1);
                break;
           case '3':
                $val = $this->Spr->GetShortNameByCod($tblname,$val, $this->lang_id, 1);
                break;
           case '4':
                $val = $this->Spr->GetShortNamesInStr( $tblname, _LANG_ID, $val, '' );
                break;
           case '5':
                $val = $val;
                break;
        }//end switch
        if( !empty($val)) $str_out = $str_out.$prefix.$val.$sufix;
    }//end for
    //echo '<br>$str_out='.$str_out;
    if ( !empty($str_out) ) $str_out = 'LTW'.$str_out;
    return $str_out;
   }// end of function BuildNumberNameByParams()

   // ================================================================================================
   // Function : SaveParamsValuesToNumberName()
   // Version : 1.0.0
   // Date : 27.08.2007
   // Parms :
   // Returns : true,false / Void
   // Description : save parameters values of current position in catalogue to the field numder_name
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 27.08.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function SaveParamsValuesToNumberName()
   {
    $tmp_db = DBs::getInstance();
    $this->number_name = $this->BuildNumberNameByParams();
    $q = "UPDATE `".TblModCatalogProp."` set
    `number_name`='".$this->number_name."' WHERE `id`='$this->id'";
    $res = $tmp_db->db_Query( $q );
    echo '<br>q='.$q.' res='.$res.' $tmp_db->result='.$tmp_db->result;
    if ( !$res OR !$tmp_db->result ) return false;
    return $this->number_name;
   }//end of function  SaveParamsValuesToNumberName()


    // ================================================================================================
    // Function : BestProducts()
    // Date : 01.17.2011
    // Programmer : Yaroslav Gyryn
    // Description : Shows best products
    // ================================================================================================
    function BestProducts($limit=null, $fltr_id = 2)
    {
    $title = $this->multi['TXT_TOP_PRODUCT'];
    switch ($fltr_id){
        case '1':
                $ftl = "`".TblModCatalogProp."`.new ='1'"; // Display new
                $title = $this->multi['FLD_NEW'];
                break;
        case '2':
                $ftl = "`".TblModCatalogProp."`.best ='1'"; // Display best
                $title = $this->multi['FLD_BEST'];
                break;
    }   
        $q = "SELECT
                `".TblModCatalogProp."`.id,
                `".TblModCatalogProp."`.id_cat,
                `".TblModCatalogProp."`.price,
                `".TblModCatalogProp."`.price_currency,
                `".TblModCatalogPropSprName."`.name,
                `".TblModCatalogSprName."`.name as cat_name,
                `".TblModCatalogTranslit."`.`translit`,
                `".TblModCatalogPropImg."`.`path` AS `first_img`,
                `".TblModCatalogPropImgTxt."`.`name` AS `first_img_alt`,
                `".TblModCatalogPropImgTxt."`.`text` AS `first_img_title`,
                `".TblModCatalogProp."`.popular,
                `".TblModCatalogProp."`.new,
                `".TblModCatalogProp."`.best
              FROM `".TblModCatalogProp."`
                LEFT JOIN `".TblModCatalogPropImg."` ON (`".TblModCatalogProp."`.`id`=`".TblModCatalogPropImg."`.`id_prop` AND `".TblModCatalogPropImg."`.`id`= (
                    SELECT
                    `".TblModCatalogPropImg."`.`id`
                    FROM `".TblModCatalogPropImg."`
                    WHERE
                    `".TblModCatalogPropImg."`.`id_prop`=`".TblModCatalogProp."`.id
                    AND `".TblModCatalogPropImg."`.`show`='1'
                    ORDER BY `".TblModCatalogPropImg."`.`move` asc LIMIT 1
                    ) )
                LEFT JOIN `".TblModCatalogPropImgTxt."` ON (`".TblModCatalogPropImg."`.`id`=`".TblModCatalogPropImgTxt."`.`cod` AND `".TblModCatalogPropImgTxt."`.lang_id='".$this->lang_id."'),
                `".TblModCatalogPropSprName."`,`".TblModCatalogSprName."`, `".TblModCatalog."`, `".TblModCatalogTranslit."`
              WHERE `".TblModCatalogProp."`.`id_cat`=`".TblModCatalog."`.`id`
              AND `".TblModCatalogProp."`.visible='2'
              AND `".TblModCatalog."`.`visible`='2'
              AND `".TblModCatalogProp."`.id=`".TblModCatalogPropSprName."`.cod
              AND `".TblModCatalogProp."`.id_cat=`".TblModCatalogSprName."`.cod
              AND `".TblModCatalogPropSprName."`.lang_id='".$this->lang_id."'
              AND `".TblModCatalogSprName."`.lang_id='".$this->lang_id."'
              AND `".TblModCatalogProp."`.id=`".TblModCatalogTranslit."`.`id_prop`
              AND `".TblModCatalogTranslit."`.`lang_id`='".$this->lang_id."'
              ORDER BY RAND()";
              if($limit)$q.="limit ".$limit;
            $res = $this->db->db_Query( $q );
            //echo '<br>'.$q.'<br/> $res='.$res.' $this->db->result='.$this->db->result;
            if ( !$res ) return false;
            $rows = $this->db->db_GetNumRows();
            if ($rows==0) {
                return false;
            }
            ?><div class="contenttitle1"><h3><?=$title?></h3></div>
            <div class="fonProd"><?
            for($i=0; $i<$rows; $i++) {
                  $row = $this->db->db_FetchAssoc();
                  //$row = $arr[$i];
                    $name = stripslashes($row['name']);
                    $img = '';
                    $img = stripslashes($row['first_img']);
                    $alt = htmlspecialchars(stripcslashes($row['first_img_alt']));
                    $title = htmlspecialchars(stripcslashes($row['first_img_title']));
                    if(empty($alt)) $alt = $name;
                    if(empty($title)) $title = $name;
                    $link = $this->getUrlByTranslit(false, $row['translit']);
                    $price = stripslashes($row['price']);
                    if(($i)%$limit==0 && $i!=0){?></div><div class="BlockPolosa" style="margin-bottom: 25px;"></div><div class="categoryContent"><?}?>
                    <div class="prod"<?if(($i+1)%3==0){?> style="margin: 0;"<?}?>>
                      <div class="prodInside">
                        <div class="nameProd"><?=$name;?></div>
                        <div class="imgProd">
                            <div class="icons_new_fon_fon">
                                <?if($row['popular']==1)$this->ShowTopIcons('Распродажа');
                                if($row['new']==1)$this->ShowTopIcons('Новинка');
                                if($row['best']==1)$this->ShowTopIcons('Топ');?>
                            </div>
                            <div class="imgProdTable">
                            <?if ( isset($img) AND !empty($img)) {
                                ?><a href="<?=$link;?>" title="<?=$name?>">
                                <?=$this->ShowCurrentImage($img, 'size_auto=195', 85, NULL, 'alt="'.$alt.'" title="'.$title.'"', $row['id']);?>
                                </a><?
                            }
                            else {
                               ?><a href="<?=$link;?>" title="<?=$this->multi['TXT_NO_IMAGE']?>"><img src="/images/design/no-image.jpg"/></a><?
                            }?>
                            </div>
                        </div>
                        <div class="paramProdFon">
                            <div class="paramProd">
                                <?$this->ShowParamsOfPropInList($row['id'],$row['id_cat']);?>
                            </div>
                        </div>
                        <div class="priceBuyProd">
                        <?if(!empty($price)){//&& $row['exist']==1?>
                            <div class="priceProd"><?=$price?> грн.</div>
                            <div class="buyProd">
                                <form action="#" method="post" name="catalog<?=$row['id']?>" id="catalog<?=$row['id']?>">
                                    <input type="hidden" value="0" id="colorId<?=$row['id']?>" name="colorId" />
                                    <input type="hidden" size="2" value="1" id="prod_id[<?=$row['id']?>]" name="prod_id[<?=$row['id']?>]"/>
                                    <div class="buybutton">
                                        <a href="#" id="rez<?=$row['id']?>" onclick="addToCart('<?=$row['id']?>');return false;" title="<?=$this->multi['TXT_BUY'];?>"><?=$this->multi['TXT_BUY']?></a>
                                    </div>
                                </form>
                            </div>
                        
                        <?}else{
                            ?><div class="net_nalishie_fon"><div class="net_nalishie">Нет в наличии</div></div><?
                        }
                        ?></div><?
                    ?></div></div><?
               } //end for
               ?></div><?
    } //end of function BestProducts

    // ================================================================================================
    // Function : ShowActionsProducts()
    // Version : 1.0.0
    // Date : 20.10.2009
    //
    // Programmer : Yaroslav Gyryn
    // Params :
    // Returns : $res / Void
    // Description : Shows best products
    // ================================================================================================
    function ShowActionsProducts($limit=null)
    {
        $q = "SELECT
                    `".TblModCatalogProp."`.id,
                    `".TblModCatalogProp."`.id_cat,
                    `".TblModCatalogProp."`.price,
                    `".TblModCatalogProp."`.price_currency,
                    `".TblModCatalogProp."`.opt_price,
                    `".TblModCatalogProp."`.opt_price_currency,
                    `".TblModCatalogPropSprName."`.name,
                    `".TblModCatalogSprName."`.name as category
                 FROM
                    `".TblModCatalogProp."`, `".TblModCatalogPropSprName."`, `".TblModCatalogSprName."`
                 WHERE
                    `".TblModCatalogProp."`.id = `".TblModCatalogPropSprName."`.cod
                 AND
                    `".TblModCatalogPropSprName."`.lang_id='".$this->lang_id."'
                 AND
                    `".TblModCatalogProp."`.id_cat = `".TblModCatalogSprName."`.cod
                 AND
                    `".TblModCatalogSprName."`.lang_id='".$this->lang_id."'
                 AND
                    `".TblModCatalogProp."`.visible ='2'
        ";

        $q  = $q." AND ABS(`".TblModCatalogProp."`.opt_price) >0 AND ABS(`".TblModCatalogProp."`.opt_price) > ABS(`".TblModCatalogProp."`.price)";
        $q = $q." ORDER BY RAND()";
        if($limit) $q = $q." limit ".$limit;

        $res = $this->db->db_Query( $q );
        //echo '<br> $q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
        if ( !$res ) return false;
        $rows = $this->db->db_GetNumRows();
        //echo '<br>$rows='.$rows;
        //$Currency = &check_init('SystemCurrencies', ''();
        $currentValuta = $this->Spr->GetNameByCod( TblSysCurrenciesSprSufix, _CURR_ID, $this->lang_id, 1 );
        ?>
                <!--Begin: list1-->
                <div class="list1">
                    <h2>
                        <img src="/images/design/list1.png" alt="" title="" />
                    </h2>
                    <div class="body">
                     <?
                     for($i=0; $i<$rows; $i++) {
                        $row = $this->db->db_FetchAssoc();
                        $name = stripslashes($row['name']);
                        $price = stripslashes($row['price']);
                        $old_price = stripslashes($row['opt_price']);
                        $link = $this->Link($row['id_cat'], $row['id']);
                        ?>
                        <form action="#" method="post" name="catalog" id="catalog<?=$row['id']?>">
                        <input type="hidden" name="productId[<?=$row['id']?>]" value="1"/>
                        <div class="item">
                            <div class="left_2">
                                <h3><?=$name;?></h3>
                                <div class="text">
                                </div>
                                <div class="items">
                                    <div class="left_3">
                                        <div class="old_price">
                                            <?
                                            if(!empty($old_price)) {
                                                 $cur_from = $row['price_currency'];
                                                 if($cur_from==0) $cur_from = $this->def_currency;
                                                 $old_price = $this->Currency->Converting($cur_from, _CURR_ID, $old_price, 2 );
                                                 echo $this->Currency->ShowPrice($old_price);
                                            }
                                            ?>
                                        </div>
                                        <div class="price">
                                            <?
                                            if(!empty($price)) {
                                                 $cur_from = $row['opt_price_currency'];
                                                 if($cur_from==0) $cur_from = $this->def_currency;
                                                 $price = $this->Currency->Converting($cur_from, _CURR_ID, $price, 2 );
                                                 echo $this->Currency->ShowPrice($price);
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="right_3">
                                        <ul>
                                            <li>
                                                <a href="#" onclick="addToCart('catalog<?=$row['id']?>', 'cart');return false;" title="Р—Р°РєР°Р·Р°С‚СЊ"><img src="/images/design/zakaz.png" alt="Р—Р°РєР°Р·Р°С‚СЊ" title="Р—Р°РєР°Р·Р°С‚СЊ" /></a>
                                            </li>
                                            <li>
                                                <a href="<?=$link;?>" title="РџРѕРґСЂРѕР±РЅРµРµ"><img src="/images/design/all.png" alt="РџРѕРґСЂРѕР±РЅРµРµ" title="РџРѕРґСЂРѕР±РЅРµРµ" /></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="right_2">
                                <table cellspacing="0" cellpadding="0">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <?
                                                $img = $this->GetFirstImgOfProp($row['id']);
                                                if($img) echo $this->ShowCurrentImageSquare($img, true, 100, 85);
                                                else echo 'РќРµС‚ С„РѕС‚Рѕ';
                                                ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?/*
                                <div class="action">
                                    <img class="png" src="/images/design/action.png" alt="" title="" />
                                </div>
                                */?>
                            </div>
                        </div>
                        </form>
                     <?
                     }
                     ?>
                    </div>
                </div>
                <!--End: list1-->
        <?
    }//end of function ShowActionsProducts

     /**
     * Catalog::SetMetaData()
     * Set title, description and keywords for current category or position of catalog
     * @author Ihor Trokhymchuk
     * @return void
     */
    function SetMetaData() {
        //for current product page
        if (!empty($this->id)) {
            $q2 = "SELECT
                 `" . TblModCatalogPropSprName . "`.`name`,
                 `" . TblModCatalogPropSprMTitle . "`.`name` AS `title`,
                 `" . TblModCatalogPropSprMDescr . "`.`name` AS `descr`,
                 `" . TblModCatalogPropSprMKeywords . "`.`name` AS `keywords`
                 FROM `" . TblModCatalogPropSprName . "`,`" . TblModCatalogProp . "`
                 LEFT JOIN `" . TblModCatalogPropSprMTitle . "` ON (`" . TblModCatalogProp . "`.`id` = `" . TblModCatalogPropSprMTitle . "`.`cod` AND `" . TblModCatalogPropSprMTitle . "`.`lang_id`='" . $this->lang_id . "')
                 LEFT JOIN `" . TblModCatalogPropSprMDescr . "` ON (`" . TblModCatalogProp . "`.`id` = `" . TblModCatalogPropSprMDescr . "`.`cod` AND `" . TblModCatalogPropSprMDescr . "`.`lang_id`='" . $this->lang_id . "')
                 LEFT JOIN `" . TblModCatalogPropSprMKeywords . "` ON (`" . TblModCatalogProp . "`.`id` = `" . TblModCatalogPropSprMKeywords . "`.`cod` AND `" . TblModCatalogPropSprMKeywords . "`.`lang_id`='" . $this->lang_id . "')
                 WHERE `" . TblModCatalogProp . "`.`id`='" . $this->id . "'
                 AND `" . TblModCatalogProp . "`.`id` = `" . TblModCatalogPropSprName . "`.`cod`
                 AND `" . TblModCatalogPropSprName . "`.`lang_id`='" . $this->lang_id . "'
                ";
            $res = $this->db->db_Query($q2);
            //echo '<br>$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
            if (!$res OR !$this->db->result)
                return false;
            $row = $this->db->db_FetchAssoc();
            $item_name = stripslashes($row['name']);
            $this->tovarName = $item_name;
            $item_title = stripslashes($row['title']);
            $item_descr = stripslashes($row['descr']);
            $item_keywords = stripslashes($row['keywords']);
            //echo '<br>$row[name]='.$row['name'].' $item_name='.$item_name;

            if (empty($item_title))
                $this->title = strtoupper($item_name) . ' ' . $this->multi['TXT_META_PRICE'] . ' | ' . $this->multi['TXT_META_BUY'] . ' ' . $item_name . ' | ' . NAME_SERVER;
            else
                $this->title = $item_title;

            if (empty($item_descr))
                $this->description = $item_name;
            else
                $this->description = $item_descr;

            if (empty($item_keywords))
                $this->keywords = '';
            else
                $this->keywords = $item_keywords;
        }
        //for current category page
        elseif (!empty($this->id_cat)) {
            $q = "SELECT
                 `" . TblModCatalogSprName . "`.`name`,
                 `" . TblModCatalogSprMTitle . "`.`name` AS `title`,
                 `" . TblModCatalogSprMDescr . "`.`name` AS `descr`,
                 `" . TblModCatalogSprKeywords . "`.`name` AS `keywords`
                 FROM `" . TblModCatalogSprName . "`,`" . TblModCatalog . "`
                 LEFT JOIN `" . TblModCatalogSprMTitle . "` ON (`" . TblModCatalog . "`.`id` = `" . TblModCatalogSprMTitle . "`.`cod` AND `" . TblModCatalogSprMTitle . "`.`lang_id`='" . $this->lang_id . "')
                 LEFT JOIN `" . TblModCatalogSprMDescr . "` ON (`" . TblModCatalog . "`.`id` = `" . TblModCatalogSprMDescr . "`.`cod` AND `" . TblModCatalogSprMDescr . "`.`lang_id`='" . $this->lang_id . "')
                 LEFT JOIN `" . TblModCatalogSprKeywords . "` ON (`" . TblModCatalog . "`.`id` = `" . TblModCatalogSprKeywords . "`.`cod` AND `" . TblModCatalogSprKeywords . "`.`lang_id`='" . $this->lang_id . "')
                 WHERE `" . TblModCatalog . "`.`id`='" . $this->id_cat . "'
                 AND `" . TblModCatalog . "`.`id` = `" . TblModCatalogSprName . "`.`cod`
                 AND `" . TblModCatalogSprName . "`.`lang_id`='" . $this->lang_id . "'
                ";
            $res = $this->db->db_Query($q);
            //echo '<br>$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
            if (!$res OR !$this->db->result)
                return false;
            $row = $this->db->db_FetchAssoc();
            $cat_name = stripslashes($row['name']);
            $this->categoryName = $cat_name;
            $cat_title = stripslashes($row['title']);
            $cat_descr = stripslashes($row['descr']);
            $cat_keywords = stripslashes($row['keywords']);

            //set title
            if (empty($cat_title))
                $this->title = strtoupper($cat_name) . ' | ' . $this->multi['TXT_META_BUY'] . ' ' . $cat_name . ' | ' . $cat_name . ' ' . $this->multi['TXT_META_PRICE'] . ' | ' . NAME_SERVER;
            else
                $this->title = $cat_title;
            if ($this->page > 1)
                $this->title .= ' - ' . $this->multi['TXT_META_PAGING'] . $this->page;

            //set description
            if (empty($cat_descr))
                $this->description = $cat_name;
            else
                $this->description = $cat_descr;

            //set keywords
            if (empty($cat_keywords))
                $this->keywords = '';
            else
                $this->keywords = $cat_keywords;
        }
        //for catalog main page
        else{
             $this->title = '';
             $this->description = '';
             $this->keywords = '';
        }
        return true;
    } //end of function  SetMetaData()

    /**
     * CatalogLayout::ShowHeaderSEO()
     * @author Yaroslav
     * @return string $upSEOMsg
     */
    function ShowHeaderSEO() {
        $upSEOMsg ='';
        if(!empty($this->id)) {
            $upSEOMsg = $this->tovarName.' купить в Сумах';
            //Название_подкатегории название_продукта купить в Сумах.
        }
        elseif(isset($this->id_cat)) {
            if(isset($this->treeCatLevels[$this->id_cat])) { // категория, подкатегория
                $count = count($this->treeCatLevels[$this->id_cat]);
                $keys = array_keys($this->treeCatLevels[$this->id_cat]);
                $this->strCategories = '';
                for($i = 0; $i<$count; $i++ ){
                    if($i==0)
                        $this->strCategories = $this->treeCatLevels[$this->id_cat][$keys[$i]];
                    else
                        $this->strCategories .= ', '.$this->treeCatLevels[$this->id_cat][$keys[$i]];
                }

                if($this->parent_level== 0 ) {
                    $upSEOMsg = '"SEOCMS" (Житомир): '.$this->categoryName.' купить '.$this->strCategories.' в Житомире ';
                }
                else {
                    $upSEOMsg = '"SEOCMS" (Житомир): '.$this->categoryName.' купить в Житомире '.$this->strCategories;
                }
            }
         }
        return $upSEOMsg;
    }

    /**
     * CatalogLayout::ShowFooterSEO()
     * @author Yaroslav
     * @return void
     */
    function ShowFooterSEO() {
        $dnSEOMsg ='';
        if(!empty($this->id)) {
            $dnSEOMsg = $this->tovarName.' купить с доставкой по всей Украине '.$this->tovarName.' - "SEOCMS", Житомир';
        }
        elseif(isset($this->id_cat)) {
            if(isset($this->treeCatLevels[$this->id_cat])) { // категория, подкатегория
                if($this->parent_level== 0 ) {
                    $dnSEOMsg = 'Интернет магазин "SEOCMS" (Житомир) -  '.$this->categoryName.' купить в Житомире с доставкой '.$this->strCategories;
                }
                else {
                    $dnSEOMsg = 'Интернет магазин "SEOCMS" (Житомир) -  '.$this->categoryName.' купить в Житомире с доставкой. '.$this->categoryName.' '.$this->strCategories;
                }
            }

        }
        return $dnSEOMsg;
    }

 } // end of class CatalogLayout
?>