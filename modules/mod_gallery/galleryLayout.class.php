<?php
// ================================================================================================
// System : SEOCMS
// Module : GalleryLayout.class.php
// Version : 2.0.0
// Date : 28.01.2009
// Licensed To:
// Igor  Trokhymchuk  ihoru@mail.ru
// Yaroslav Gyryn    las_zt@mail.ru
//
// Purpose : Class definition for all actions with Layout of Gallery on the Front-End
//
// ================================================================================================
include_once( SITE_PATH.'/modules/mod_gallery/gallery.defines.php' );

class GalleryLayout extends Gallery{

    public $id = NULL;
    public $title = NULL;
    public $category = NULL;
    public $catData = NULL;
    public $pageData = NULL;
    // ================================================================================================
    //    Function          : GalleryLayout (Constructor)
    //    Version           : 1.0.0
    //    Date              : 01.07.2010
    //    Parms             : sort      / field by whith data will be sorted
    //                        display   / count of records for show
    //                        start     / first records for show
    //    Returns           : Error Indicator
    //
    //    Description       : Opens and selects a dabase
    // ================================================================================================
    function GalleryLayout($user_id = NULL, $module=NULL, $display=NULL, $sort=NULL, $start=NULL) {

        //Check if Constants are overrulled
        ( $user_id   !="" ? $this->user_id = $user_id  : $this->user_id = NULL );
        ( $module   !="" ? $this->module  = $module   : $this->module  = NULL );
        ( $display  !="" ? $this->display = $display  : $this->display = 10   );
        ( $sort     !="" ? $this->sort    = $sort     : $this->sort    = NULL );
        ( $start    !="" ? $this->start   = $start    : $this->start   = 0    );

        if(defined("_LANG_ID")) $this->lang_id = _LANG_ID;

        $this->db =  DBs::getInstance();
        $this->Right =  &check_init('Rights', 'Rights');
        $this->Form = &check_init('FormGallery', 'FrontForm', "'form_gallery'");
        if(empty($this->multi)) $this->multi = &check_init_txt('TblFrontMulti', TblFrontMulti);
        if(empty($this->Spr)) $this->Spr = &check_init('SysSpr', 'SysSpr');
        if (empty($this->Crypt)) $this->Crypt = &check_init('Crypt', 'Crypt');
        if(empty($this->settings)) $this->settings = $this->GetSettings();
        //if (empty($this->Msg))  $this->Msg = Singleton::getInstance('ShowMsg');
        //$this->Msg->SetShowTable(TblModGallerySprTxt);
        //if (empty($this->Spr))  $this->Spr = Singleton::getInstance('FrontSpr');
        if (empty($this->Form))  $this->Form = Singleton::getInstance('FrontForm','form_gallery');

        $this->UploadImages = new UploadImage(149, null, $this->settings['img_path'],'mod_gallery_img');

        ( defined("USE_TAGS")       ? $this->is_tags = USE_TAGS         : $this->is_tags=0      );
        ( defined("USE_COMMENTS")   ? $this->is_comments = USE_COMMENTS : $this->is_comments=0  );
    } // End of GalleryLayout Constructor


   /**
     * Catalog::SetMetaData()
     * Set title, description and keywords for current category or position of photogallery
     * @author Ihor Trokhymchuk
     * @return void
     */
    function SetMetaData() {
        //for current photo page
        if(!empty($this->id)){
            $mtitle = stripslashes($this->pageData['title']);
            $mdescr = stripslashes($this->pageData['description']);
            $mkeywords = stripslashes($this->pageData['keywords']);

            if(empty($mtitle))
                $this->title = stripslashes($this->pageData['name']);
            else
                $this->title = $mtitle;

            if(empty($mdescr))
                $this->description = stripslashes($this->pageData['name']);
            else
                $this->description = $mtitle;

            if(empty($mkeywords))
                $this->keywords = '';
            else
                $this->keywords = $mtitle;
        }
        //for current cateogy page
        elseif(!empty($this->category)){
            $mtitle = stripslashes($this->catData['mtitle']);
            $mdescr = stripslashes($this->catData['mdescr']);
            $mkeywords = stripslashes($this->catData['mkeywords']);

            if(empty($mtitle))
                $this->title = stripslashes($this->catData['name']);
            else
                $this->title = $mtitle;
            if ($this->page > 1)
                $this->title .= ' - ' . $this->multi['TXT_META_PAGING'] . $this->page;

            if(empty($mdescr))
                $this->description = stripslashes($this->catData['name']);
            else
                $this->description = $mtitle;

            if(empty($mkeywords))
                $this->keywords = '';
            else
                $this->keywords = $mtitle;
        }
        //for photogallery main page
        else{
            $this->title = '';
            $this->description = '';
            $this->keywords = '';
        }
        return true;
    } //end of function  SetMetaData()

    // ================================================================================================
    // Function : ShowGalleryTask()
    // Date : 01.07.2010
    // Parms :
    // Returns :      true,false / Void
    // Description :  Show Gallery navigation by tasks
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ShowGalleryTask( $task = NULL )
    {
        ?>
        <div style="margin: 13px 0px 0px 0px; float:none; min-height:90px;">
         <div style="width:320px; float:left;">
          <?=$this->multi['TXT_OFFER_TASK'];?>:
          <ul>
           <li>
          <?
          $this->fltr = " AND `status`='a'";
          $rows = $this->GetGallerysRows('limit');
          if($rows>0){?><a href="<?=_LINK;?>gallerys/last/" class="t_link"><?=$this->multi['TXT_FRONT_TITLE_LATEST'];?>&nbsp;→</a>&nbsp;<span class="inacive_txt"><?=$rows;?></span><?}
          else{?><span class="inacive_txt"><?=$this->multi['TXT_FRONT_TITLE_LATEST'];?></span><?}
          ?>
           </li>
          <?
          $this->fltr = " AND `status`='e'";
          $rows = $this->GetGallerysRows('limit');
          ?>
          <li>
          <?
          if($rows>0){?><a href="<?=_LINK;?>gallerys/arch/" class="t_link"><?=$this->multi['TXT_FRONT_TITLE_ARCH'];?>&nbsp;→</a>&nbsp;<span class="inacive_txt"><?=$rows;?></span><?}
          else{?><span class="inacive_txt"><?=$this->multi['TXT_FRONT_TITLE_ARCH'];?></span><?}
          ?>
          </li>
          <?
          $this->fltr = " AND `status`!='i'";
          $rows = $this->GetGallerysRows('limit');
          ?>
          <li>
          <?
          if($rows>0){?><a href="<?=_LINK;?>gallerys/all/" class="t_link"><?=$this->multi['TXT_ALL_OFFERS'];?>&nbsp;→</a>&nbsp;<span class="inacive_txt"><?=$rows;?></span><?}
          else{?><span class="inacive_txt"><?=$this->multi['TXT_ALL_OFFERS'];?></span><?}
          ?>
          </li>
          </ul>
         </div>
         <?
         $this->ShowGalleryCat();
         ?>
        </div>
        <?
    }

    // ================================================================================================
    // Function : ShowGalleryCat()
    // Date : 01.07.2010
    // Parms :
    // Returns :      true,false / Void
    // Description :  Show Gallery Category
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ShowGalleryCat( $cat = NULL )
    {
        $db = new DB();
        $q = "SELECT `".TblModGalleryCat."`.*
              FROM `".TblModGalleryCat."`
              WHERE `lang_id`='".$this->lang_id."'
              AND `name`!=''
              ORDER BY `move` DESC ";
        $res = $db->db_Query( $q );
        $rows = $db->db_GetNumRows();
        if ($rows>0){
            ?><div class="catalogLevels">
            <ul><?
            for( $i = 0; $i < $rows; $i++ ){
                $row = $db->db_FetchAssoc();
                $name = $row['name'];
                $link =  $this->Link($row['translit'], NULL);
                ?><li><a href="<?=$link;?>"><?=$name;?></a></li><?
            } // end for
            ?>
            </ul>
            </div>
            <?
        }
        else {
            ?><div class="err" align="center"><?
                echo $this->multi['TXT_NO_DATA'];
            ?></div><?
        }
    }//end of function ShowGalleryCat()

    // ================================================================================================
    // Function : ShowGalleryNavigation()
    // Version : 1.0.0
    // Date : 01.07.2010
    // Parms :
    // Returns :      true,false / Void
    // Description :  Show News Navigation
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function ShowGalleryNavigation() {
        $q = "SELECT `".TblModGalleryCat."`.*
              FROM `".TblModGalleryCat."`
              WHERE `lang_id`='".$this->lang_id."'
              AND `name`!=''
              ORDER BY `move` DESC ";
        $res = $this->db->db_Query( $q );
        $rows = $this->db->db_GetNumRows();
        if ($rows>0){
        ?>
        <div class="gall-categs">
            <?

            for( $i = 0; $i < $rows; $i++ ){
                $row = $this->db->db_FetchAssoc();
                $name = $row['name'];
                $link =  $this->Link($row['translit'], NULL);
		$class='';
		if(isset($this->category) && $this->category==$row['cod'])
		    $class='selected';
                ?><a href="<?=$link;?>" title="<?=$name;?>" class="<?=$class?>"><?=$name;?></a><?
            } // end for
            ?>
        </div>
        <?
        }
    }

    // ================================================================================================
    // Function : GetGalleryForTags()
    // Date : 23.05.2011
    // Returns :      true,false / Void
    // Description :  Get Gallery For Tags
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function GetGalleryForTags( $idNews=null, $idModule = 156)
    {
        if($idNews== null)
            return;

    $q = "
        SELECT
             `".TblModGallery."`.id,
             `".TblModGallery."`.dttm as start_date,
             `".TblModGallery."`.category as id_category,
             `".TblModGalleryTxt."`.name,
             `".TblModGalleryTxt."`.short as shrt,
             `".TblModGalleryTxt."`.translit,
             `".TblModGalleryCat."`.name as category,
             `".TblModGalleryCat."`.translit as cat_translit,
             `".TblModGallery."`.position
        FROM `".TblModGallery."`, `".TblModGalleryTxt."`, `".TblModGalleryCat."`
        WHERE  `".TblModGallery."`.category = `".TblModGalleryCat."`.cod
              AND `".TblModGallery."`.id = `".TblModGalleryTxt."`.cod
              AND `".TblModGalleryTxt."`.lang_id='".$this->lang_id."'
              AND `".TblModGalleryCat."`.lang_id='".$this->lang_id."'
              AND `".TblModGalleryTxt."`.name!=''
              AND  `".TblModGallery."`.id  IN (".$idNews.")
              ";
        if( $this->fltr!='' ) $q = $q.$this->fltr;
        $q = $q." ORDER BY `".TblModGallery."`.id DESC LIMIT ".$this->start.",".$this->display."";

        $res = $this->db->db_Query( $q );
        $rows = $this->db->db_GetNumRows();
        //echo "<br>".$q."<br/> res=".$res." rows=".$rows;

        if($rows==0)
             return;

         $array = array();
         for( $i = 0; $i <$rows; $i++ ){
             $row = $this->db->db_FetchAssoc();
             $array[$row['id']] = $row;
             $array[$row['id']]['module'] = $idModule;
         }

         return $array;
    }

// ================================================================================================
// Function : ShowGallerysByPages()
// Parms :
// Returns :      true,false / Void
// Description :  Show Gallery width img by pages
// Programmer : Yaroslav Gyryn
// Date : 06.01.2010
// ================================================================================================
function ShowGallerysByPages()
{
     $this->ShowGalleryNavigation();

     $cat_descr = '<div>'.stripslashes($this->catData['descr']).'</div>';

     $gallData = $this->GetGallerysRows('limit');
     $rows=count($gallData);
     if($rows==0){
         ?><div class="err"><?=$this->multi['MSG_NO_GALLERY'];?></div><?
         echo '<br/><br/>'.$cat_descr;
         return;
     }
     $counter=0;
     ?><div class="gall-parent"><?
     for( $i = 0; $i <$rows; $i++ ){
         $row = $gallData[$i];
         $name = stripslashes($row['sbj']);
         $date = $this->ConvertDate($row['start_date']);
         $short = $this->Crypt->TruncateStr(strip_tags(stripslashes($row['shrt'])),280);
         $link_cat = $this->Link( $row['cat_translit']);
         $link = $this->Link( $row['cat_translit'], $row['translit']);
	 $counter++;
         ?>
            <div class="gall-elem <?if($counter==6){ echo 'gall-elem-end';$counter=0;}?>">
                <?
		 if(!empty($row['title']))
		    $title = $row['title'];
		 else
		    $title = $name;
		 if(!empty($row['alt']))
		    $alt = $row['alt'];
		 else
		    $alt = $name;
                 if(!empty($row['img'])) {
		 $img='/uploads/images/gallery/'.$row['id'].'/'.$row['img'];
		 $img_thumb=$this->FrontendPages->ShowCurrentImageExSize($img,140,140,true,true,85,NULL,false,NULL,true);
                    $items_count =1; // Ограничение для видео не более 1 картинки

		    ?><a class="image" href="<?=$link;?>" title="<?=$title;?>"><img src="<?=$img_thumb;?>" title="<?=$title;?>" alt="<?=$alt;?>" /></a><?
                 }
                 else {
                    ?><a href="<?=$link;?>" title="<?=$title;?>"><img  src="/images/design/no-image.jpg" width="310" height="210" alt="<?=$alt?>"  title="<?=$title;?>"/></a><?
                 }
		 if(!empty($name)){
		    $name_show=  explode(' ', $name);
		    if(isset($name_show[0])){
			$name_show[0]="<span>".$name_show[0]."</span>";
			$name=  implode(' ', $name_show);

		    }


		 }
                ?>
		    <a href="<?=$link;?>" title="<?=  strip_tags($name);?>" class="gall-name"><?=$name;?></a>
         </div><?
     }
     ?></div><?
     if($rows>0){
     ?>
     <div class="pageNaviClass"><?
         $rows = $this->GetGallerysRows('nolimit');
	 $n_rows=count($rows);
         $link = $this->Link(null, null );
	 if($n_rows>$this->display){
	    for($i = 0; $i < ($n_rows/$this->display); $i++)
	    {
		$p=$i+1;
		$link_cur=$link;
		if($i!=0)
		    $link_cur.="page".$p."/";
		?><a href="<?=$link_cur?>" title="" class="pagination <?if($this->page==$p) echo 'selected-page';?>"></a><?
	    }
	 }
//         $this->Form->WriteLinkPagesStatic( $link, $n_rows, $this->display, $this->start, $this->sort, $this->page );
     ?>
     </div>
     <?=$cat_descr;?>
	 <script type="text/javascript">
	     $(".gall-elem").fadeTo(0, 0.65).hover(function(){
		 $(this).stop().fadeTo('fast', 1).children('.gall-name').css('visibility','visible');
	     }, function(){
		 $(this).stop().fadeTo('fast', 0.65).children('.gall-name').css('visibility','hidden');
	     });

	 </script>
	 <?
     }
} // end of function ShowGallerysByPages




    // ================================================================================================
    // Function : ShowGalleryFull()
    // Date : 01.07.2010
    // Returns :      true,false / Void
    // Description :  Show Gallery Full
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ShowGalleryFull()
    {
        $rows = $this->GetGalleryData($this->id);
        if($rows==0)
            return false;
        $value = $this->db->db_FetchAssoc();
        //print_r($value );
        $name = stripslashes($value['sbj']);
        $title = $name;
        $link= $this->Link( $value['cat_translit'],$value['translit']);
        ?><div class="backContainer">
	    <h2 class="gallery-name"><?=$name;?></h2>
         <?
         $short = strip_tags($value['short']);
         echo $short;?>
            <a class="btnBack" href="javascript:window.history.go(-1);">К списку проектов</a>
        </div>
         <?
        $items = $this->UploadImages->GetPictureInArrayExSize($this->id, $this->lang_id,24,70,70,true,true,85,NULL,640,470,($this->page-1)*24);
//        $items = $this->UploadImages->GetPictureInArrayExSizeAuto($this->id, $this->lang_id,NULL,70,70,true,true,85,NULL,640,470);
//      $items = $this->UploadImages->GetPictureInArray($this->id, $this->lang_id,'size_width=125',85);
        $items_keys = array_keys($items);
        $items_count = count($items);
        if($items_count>0) {
	    ?>

            <div id="gallFullImageBoxId" class="gall-full-image-box" align="center">
		<div id="ajaxLoader" class="ajax-loader"></div>
		<div class="gall-full-img-wrapper">
		    <div id="imageLarge" class="gall-full-img-large">
			<?
			for($j=0; $j<$items_count; $j++){
			    $alt= stripslashes(htmlspecialchars($items[$items_keys[$j]]['name'][$this->lang_id]));  // Заголовок
			    $title= stripslashes(htmlspecialchars($items[$items_keys[$j]]['text'][$this->lang_id]));  // Описание
			    $path = $items[$items_keys[$j]]['path'];                    // Путь уменьшенной копии
			    $path2 = $items[$items_keys[$j]]['path2'];                 // Путь большой копии
			    $path_org = $items[$items_keys[$j]]['path_original'];
			?>
			<a href="<?=$path_org?>" class="fancybox-gallery" id="<?='gall'.$j?>" rel="gal">
			    <img src="<?=$path2?>" alt="<?=$alt?>" title="<?=$title?>"/>
			</a>
			<?}?>
			<?/*<div class="photoCaption"><?=stripslashes(htmlspecialchars($items[$items_keys[0]]['name'][$this->lang_id]));?></div>*/?>
		    </div>
		    <a href="#" id="GallFullCaruselLeftBrn" class="gall-full-carusel-left-btn" title="Назад"></a>
		    <a href="#" id="GallFullCaruselRightBrn" class="gall-full-carusel-right-btn" title="Вперёд"></a>
                </div>

                <div id="carouselBlock" class="gall-gull-thumbs-box">
		    <div id="carouselBlockInner">
                    <?
                    //$responce ='';
		    $counter=0;
                    for($j=0; $j<$items_count; $j++){
                        $alt= stripslashes(htmlspecialchars($items[$items_keys[$j]]['name'][$this->lang_id]));  // Заголовок
                        $title= stripslashes(htmlspecialchars($items[$items_keys[$j]]['text'][$this->lang_id]));  // Описание
                        $path = $items[$items_keys[$j]]['path'];                    // Путь уменьшенной копии
                        $path2 = $items[$items_keys[$j]]['path2'];                 // Путь большой копии
                        $path_org = $items[$items_keys[$j]]['path_original'];   // Путь оригинального изображения
			$counter++;
                        ?>
			    <a href="#<?='gall'.$j?>" title="<?=$title;?>">
                                    <img src="<?=$path;?>" alt="<?=$alt?>" title="<?=$title;?>" class="gall-full-thumb-img <?if($counter==4){ echo "last-gall-thumb-row";$counter=0;}?>"/>
			    </a>
		    <?
                    }
                    ?>
		    </div>
<!--		    page navi begin-->

			<?if($items_count==24 && !$this->ajax):?>

			    <div class="pageNaviClass gall-full-page-navi"><?
				$rows = $items = $this->UploadImages->GetPictureInArrayExSize($this->id, $this->lang_id,NULL,70,70,true,true,85);
				$n_rows=count($rows);

				if($n_rows>$this->display){
				    for($i = 0; $i < ($n_rows/$this->display); $i++)
				    {
					$p=$i+1;
					if($i!=0)
					?><a href="#" title="" class="pagination <?if($this->page==$p) echo 'selected-page';?>"></a><?
				    }
				}
			//         $this->Form->WriteLinkPagesStatic( $link, $n_rows, $this->display, $this->start, $this->sort, $this->page );
			    ?>
			    </div>
			<?endif;?>
<!--		    page navi End-->
                </div>
           </div>
           <?
         }
         ?>


	 <script type="text/javascript">
		 makeViewer();

		$(".pagination").each(function(i){
		   $(this).click(function(){
		       $(".pagination").removeClass('selected-page');
		       $(this).addClass('selected-page');
			getGallPage("<?=$link?>",i+1);
			return false;
		    });
		});
	</script>
	     <?
         /*if( $this->is_tags==1 ){
           if (empty($this->Tags))  $this->Tags = Singleton::getInstance('FrontTags');
           $this->Tags->ShowUsingTags($this->module, $this->id);
         }

         if(!isset($this->Comments))
            $this->Comments = new FrontComments($this->module, $this->id);
         $this->Comments->ShowCommentsCountLink();*/
} // end of function ShowGalleryFull()



    // ================================================================================================
    // Function : GalleryCatLast()
    // Date :    02.03.2011
    // Returns : true/false
    // Description : Show Last video for $cat
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function GalleryCatLast( $id_cat=3, $limit=6, $caption = null){
        $arr= $this->GetGalleryCatLast( $id_cat, $limit);
        if(is_array($arr)) {
            $rows = count($arr);
            if(!$rows)
                return;
            if(!$caption )
                $caption = $this->multi['TXT_FRONT_TITLE_LATEST'];
         ?>
         <div class="lastCategoryVideo">
         <div class="news_colum1_1_title"><?=$caption?></div>
         <?
            for( $i=0; $i<$rows; $i++ )
            {
              $row = $arr[$i];
              $name = strip_tags(stripslashes($row['name']));
              //$short = $this->Crypt->TruncateStr(strip_tags(stripslashes($row['shrt'])),200);
              //$short = strip_tags(stripslashes($row['shrt']));
              /*$link = $this->Link($row['category'], $row['id']);
              $link_cat = $this->Link( $value['cat_translit']);*/
              $link = $this->Link( $row['cat_translit'], $row['translit']);

              ?>
              <div class="lastVideos left">
                  <div class="time left"><?=$this->ConvertDate($row['dttm'], true);?></div>
                  <a class="name left" href="<?=$link;?>"><?=$name;?></a>
              </div>
              <?
            }
            //$linkCat = $this->Link($id_cat);
            /*?><a class="allNews" href="<?=$linkCat?>">Всі новини</a><?*/
            ?>
            <div class="clear">&nbsp;</div>
         </div><?
         }
    } // end of function GalleryCatLast()


    // ================================================================================================
    // Function : GalleryLast()
    // Date :    02.03.2011
    // Returns : true/false
    // Description : Show Last Photo Gallery
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function GalleryLast () {
        $row = $this->GetGalleryLast();
        if(!is_array($row))
            return;
        $name = strip_tags(stripslashes($row['name']));
        $short = $this->Crypt->TruncateStr(strip_tags(stripslashes($row['short'])),300);
        $link = $this->Link( $row['cat_translit'], $row['translit']);
        ?>
        <div class="captionChapter"><span><?=$this->multi['TXT_GALLERY_TITLE'];?></span><span class="icoGallery">&nbsp;</span></div>
         <div class="imageLast">
            <?/*<a href="<?=$link?>"  title="<?=$name;?>" ><?=$name;?></a>*/?>
            <?
             $title = $name;
             $items = $this->UploadImages->GetPictureInArrayExSize($row['id'], $this->lang_id,NULL,300,194,true,true,85,NULL,273,164);
             $items_keys = array_keys($items);
             $items_count = count($items);
             if($items_count>0) {
                $items_count =1; // Ограничение для видео не более 1 картинки
                for($j=0; $j<$items_count; $j++){
                    $alt= $items[$items_keys[$j]]['name'][$this->lang_id];      // Заголовок
                    $titleImg= $items[$items_keys[$j]]['text'][$this->lang_id]; // Описание
                    $path = $items[$items_keys[$j]]['path'];                    // Путь уменьшенной копии
                    //$path2 = $items[$items_keys[$j]]['path2'];                 // Путь большой копии
                    //$path_org = $items[$items_keys[$j]]['path_original'];   // Путь оригинального изображения
                    ?><a class="image" href="<?=$link;?>" title="<?=$title;?>" ><img src="<?=$path;?>" alt="<?=$alt?>" title="<?=$titleImg;?>"/></a><?
                }
             }
             else {
                ?><a href="<?=$link;?>" title="<?=$title;?>"><img src="/images/design/no-image.jpg" width="273" height="164" alt="" /></a><?
             }
            /*<div class="vid_fot_desc"><?=$short;?></div>
            <div class="vid_fot_footer"><a href="/gallery/">Всі фоторепортажі</a></div>*/?>
        </div>
         <?
    } // end of function GalleryLast()


// ================================================================================================
// Function : GetMap()
// Date : 01.07.2010
// Parms :
// Returns :      true,false / Void
// Description :  Show Gallery   Full
// Programmer :  Yaroslav Gyryn
// ================================================================================================
function GetMap() {
 $db = new DB();
 $db1 = new DB();

 $q = "SELECT *
        FROM `".TblModGalleryCat."`
        WHERE `lang_id`='"._LANG_ID."'
        ORDER BY `cod` ASC
 ";
 //echo '$q = '.$q;
 $res = $db->db_Query( $q );
 $rows = $db->db_GetNumRows();
?>
<ul>
<?
 for( $i = 0; $i < $rows; $i++ )
 {
   $row = $db->db_FetchAssoc();
   ?><li><a href="<?=$this->Link( $row['translit']);?>"><?=$row['name'];?></a></li><?
   $q1 = "SELECT
            `".TblModGallery."`.id,
            `".TblModGallery."`.category,
            `".TblModGalleryTxt."`.name,
            `".TblModGalleryTxt."`.translit
          FROM `".TblModGallery."` ,`".TblModGalleryTxt."`
          WHERE
            `".TblModGallery."`.category ='".$row['cod']."'
          AND
            `".TblModGallery."`.id = `".TblModGalleryTxt."`.cod
          AND
            `".TblModGalleryTxt."`.lang_id = '".$this->lang_id."'
          AND
           `".TblModGallery."`.status='a'
           AND
           `".TblModGalleryTxt."`.name != ''
          ORDER BY
            `".TblModGallery."`.dttm DESC
   ";
   $res1 = $db1->db_Query( $q1 );
   $rows1 = $db1->db_GetNumRows();
   if( $rows1 )
   {
    echo '<ul>';
    for( $j = 0; $j < $rows1; $j++ )
    {
      $row1 = $db1->db_FetchAssoc();
      echo '<li><a href="'.$this->Link($row['translit'], $row1['translit']).'">'.stripslashes($row1['name']).'</a></li>';
    }
    echo '</ul>';
   }
 }
?>
</ul>
<?
}

// ================================================================================================
function ShowGalleryLast($cnt=5){
   $db = new DB();
   $q = "select `".TblModGallery."`.*,`".TblModGalleryTxt."`name from  `".TblModGallery."`,`".TblModGalleryTxt."`
   where `".TblModGallery."`.status='a'
   AND `".TblModGallery."`.id=".TblModGalleryTxt."`.cod
   AND `".TblModGalleryTxt."`.lang_id='".$this->lang_id."'
   order by position desc limit ".$cnt;
   $res = $db->db_Query( $q);
   $rows = $db->db_GetNumRows();
   ?><table border="0" cellspacing="0" cellpadding="0"><?
    for( $i=0; $i<$rows; $i++ )
    {
    $row = $db->db_FetchAssoc($res);
    ?>
    <tr>
      <td class="b_marker"><img src="/images/design/block_mark.gif" /></td>
      <td><a href="<?=$this->Link($row['category'], $row['id']);?>"><?=$row['name'];?></a>
              <br><span class="time"><?=$this->ConvertDate($row['dttm'], true);?></span></td>
    </tr>
    <tr>
      <td colspan="2" class="b_spacer"></td>
    </tr>
    <?
    }
?></table><?
}  //



   // ================================================================================================
   // Function : ShowErr()
   // Version : 1.0.0
   // Date : 01.07.2010
   //
   // Parms :
   // Returns :      true,false / Void
   // Description :  Show errors
   // ================================================================================================
   // Programmer :  Yaroslav Gyryn
   // Date : 01.07.2010
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowErr()
   {
     if ($this->Err){
       ?>
        <table border=0 cellspacing=10 cellpadding=0 class="err" width="50%" align=center>
         <tr><td><h2><?=$this->Msg->show_text('MSG_ERR', TblSysTxt);?></h2></td></tr>
         <tr><td><?=$this->Err;?></td></tr>
        </table>
       <?
     }
   } //end of fuinction ShowErr()

   // ================================================================================================
   // Function : ShowTextMessages
   // Version : 1.0.0
   // Date : 19.01.2006
   //
   // Parms :
   // Returns : true,false / Void
   // Description : Show the text messages
   // ================================================================================================
   // Programmer : Yaroslav Gyryn
   // Date : 19.01.2006
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowTextMessages( $text = NULL )
   {
     echo "<H3 align=center class='msg'>$text</H3>";
   } //end of function ShowTextMessages()


    // ================================================================================================
   // Function : ShowNavigation
   // Version : 1.0.0
   // Date : 19.01.2006
   //
   // Parms :
   // Returns : true,false / Void
   // Description : Show navigation of news
   // ================================================================================================
   // Programmer : Yaroslav Gyryn
   // Date : 19.01.2006
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowNavigation(){
     ?>
     <div class="navigation">
     <a href="/"><?=SITE_NAME?></a> »
     <? if(empty($this->id) and empty($this->category)){ ?>
     Статьи
     <?} else { ?>
     <a href="/gallery/">Статьи</a>
     <? } ?>
      <? if(!empty($this->category) and empty($this->id)){ ?>
      » <?=$this->Spr->GetNameByCod( TblModGalleryCat, $this->category );?>
      <? } else {
         if(!empty($this->category)) {
         ?>
         » <a href="<?=$this->Link($this->category);?>"><?=$this->Spr->GetNameByCod( TblModGalleryCat, $this->category );?></a>
         <? }?>
      <? }?>
       <? if(!empty($this->id)){ ?>
         » <?=strip_tags($this->Spr->GetNameByCod( TblModGalleryTxt, $this->id));?>
       <? } ?>
     </div>
     <?
   }
   // ================================================================================================
  // Function : ShowSearchResult()
  // Date :    01.07.2010
  // Parms :   $id - poll id
  // Returns : true/false
  // Description : Show Saerch form for search in the gallery
  // Programmer : Yaroslav Gyryn
  // ================================================================================================
function ShowSearchResult($array)
{
    $rows = count($array);
    ?><ul><?
    for($i=0; $i<$rows; $i++ )
    {
        $row = $array[$i];
        $name = stripslashes($row['sbj']);
        $link = $this->Link( $row['cat_translit'],  $row['translit']);
        ?><li><a href="<?=$link;?>"><?=$name;?></a></li><?
    }
    ?></ul><?
}// end of function ShowSearchForm


   // ================================================================================================
   // Function : GetCategoryIdByTranslit()
   // Version : 1.0.0
   // Date : 01.07.2010
   //
   // Parms :
   // Returns :      true,false / Void
   // Description :  Show errors
   // Programmer :  Yaroslav Gyryn
   // ================================================================================================
   function GetCategoryIdByTranslit($translit=null)
   {
      $db= new DB();
      $q="select cod from `".TblModGalleryCat."` where  BINARY `translit`= BINARY '".$translit."'";
      $res = $db->db_query( $q);
//     echo $q;
     if( !$db->result )
            return false;
     $rows = $db->db_GetNumRows();
     if($rows ==0)
        return false;

     $row = $db->db_FetchAssoc();
     return $row['cod'];

   } //end of function  GetCategoryIdByTranslit()


   // ================================================================================================
   // Function : GetCategoryTranslitByCod()
   // Version : 1.0.0
   // Date : 01.07.2010
   //
   // Parms :
   // Returns :      true,false / Void
   // Description :  Show errors
   // Programmer :  Yaroslav Gyryn
   // ================================================================================================
   function GetCategoryTranslitByCod($cod=null)
   {
      $db= new DB();
      $lang = _LANG_ID;
      $q="select translit from `".TblModGalleryCat."` where  `cod`= '".$cod."' AND `lang_id`= '".$lang."' ";
      $res = $db->db_query( $q);
//     echo $q;
     if( !$db->result )
            return false;
     $rows = $db->db_GetNumRows();
     if($rows ==0)
        return false;

     $row = $db->db_FetchAssoc();
     return $row['translit'];

   } //end of function  GetCategoryTranslitByCod()

   // ================================================================================================
   // Function : GetPositionIdByTranslit()
   // Version : 1.0.0
   // Date : 01.07.2010
   //
   // Parms :
   // Returns :      true,false / Void
   // Description :  Show errors
   // Programmer :  Yaroslav Gyryn
   // ================================================================================================
   function GetPositionIdByTranslit($position=null, $cat = null)
   {
    $db= new DB();
    $lang_id = _LANG_ID;
    $q = "SELECT
                    `".TblModGallery."` .id as id_prop
            FROM `".TblModGalleryTxt."` ,`".TblModGallery."`
            WHERE
                BINARY `translit` = BINARY '".$position."'
                AND
                    `".TblModGallery."`.id = `".TblModGalleryTxt."`.cod
                ";
    if( !empty($lang_id) )
            $q = $q." AND `".TblModGalleryTxt."`.lang_id='".$lang_id."'";
    if( $cat!=NULL )
            $q = $q." AND `".TblModGallery."`.category='".$cat."'";

      $res = $db->db_query( $q);
//     echo $q;
     if( !$db->result )
            return false;
     $rows = $db->db_GetNumRows();
     if($rows ==0)
        return false;

     $row = $db->db_FetchAssoc();
     return $row['id_prop'];

   } //end of function  GetPositionIdByTranslit()
// ================================================================================================
   // Function : GetIdTranslitByCod()
   // Version : 1.0.0
   // Date : 01.07.2010
   //
   // Parms :
   // Returns :      true,false / Void
   // Description :  Show errors
   // Programmer :  Yaroslav Gyryn
   // ================================================================================================
   function GetIdTranslitByCod($position=null, $cat = null)
   {
    $db= new DB();
    $lang_id = _LANG_ID;
    $q = "SELECT
                    `".TblModGalleryTxt."` .translit
            FROM `".TblModGalleryTxt."` ,`".TblModGallery."`
            WHERE
                BINARY `translit` = BINARY '".$position."'
                AND
                    `".TblModGallery."`.id = `".TblModGalleryTxt."`.cod
                ";
    if( !empty($lang_id) )
            $q = $q." AND `".TblModGalleryTxt."`.lang_id='".$lang_id."'";
    if( $id_cat!=NULL )
            $q = $q." AND `".TblModGallery."`.category='".$cat."'";

      $res = $db->db_query( $q);
//     echo $q;
     if( !$db->result )
            return false;
     $rows = $db->db_GetNumRows();
     if($rows ==0)
        return false;

     $row = $db->db_FetchAssoc();
     return $row['translit'];

   } //end of function  GetIdTranslitByCod()


     // ================================================================================================
    // Function : ShowGalleryLink()
    // Version : 1.0.0
    // Date : 01.07.2010
    // Parms :
    // Returns : true,false / Void
    // Description : Show Link to Gallery Chapter
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function ShowGalleryLink()
    {
       ?><div class="leftBlockHead"><?=$this->multi['TXT_GALLERY_TITLE'];?></div>
         <div class="galleryBlock">
            <?$link = _LINK.'gallery/2010/';
                $items = $this->UploadImages->GetFirstRandomPictureInArray($this->lang_id, 'size_width= 223', null);
                $items_keys = array_keys($items);
                $items_count = count($items);
                if($items_count>0) {
                        /*$alt= $items[$items_keys]['name'][$this->lang_id];  // Заголовок
                        $title= $items[$items_keys]['text'][$this->lang_id];  // Описание */
                        $path = $items[$items_keys[0]]['path'];                    // Путь уменьшенной копии
                        //$path_org = $items[$items_keys['path_original'];   // Путь оригинального изображения
                        ?><a href="<?=$link;?>" title="<?=$this->multi['TXT_GALLERY_TITLE'];?>" alt="<?=$this->multi['TXT_GALLERY_TITLE'];?>"><img src="<?=$path;?>" alt="<?=$this->multi['TXT_GALLERY_TITLE'];?>" title="<?=$this->multi['TXT_GALLERY_TITLE'];;?>"></a><?
              }
            /*?>
            <a href="<?=$link?>" title="<?=$this->multi['TXT_GALLERY_TITLE'];?>"><img src="/images/design/videoSmall.jpg"></a>*/?>
         </div><?
     }

   } //end of class galleryLayout
?>
