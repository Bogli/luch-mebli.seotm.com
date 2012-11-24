<?php
/**
* PageUser.class.php
* Class definition for all Pages - user actions
* @package Package of SEOCMS
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.0, 30.09.2011
* @copyright (c) 2010+ by SEOTM
*/

include_once( SITE_PATH.'/include/defines.php' );

/**
* Class PageUser
* Class definition for all Pages - user actions
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 30.09.2011
* @property ShareLayout $Share
* @property FrontendPages $FrontendPages
* @property UserAuthorize $Logon
* @property UserShow $UserShow
* @property OrderLayout $Order
* @property FrontSpr $Spr
* @property FrontForm $Form
* @property db $db
* @property TblFrontMulti $multi
* @property CatalogLayout $Catalog
* @property SysLang $Lang
* @property NewsLayout $News
* @property ArticleLayout $Article
*/
class PageUser extends Page {

    public $user_id = NULL;
    public $module = NULL;
    public $multi = NULL;

    public $db = NULL;
    public $Lang = NULL;
    public $Form = NULL;
    public $Spr = NULL;
    public $Share= NULL;
    public $FrontendPages= NULL;
    public $Logon= NULL;
    public $UserShow= NULL;
    public $Order= NULL;
    public $Catalog= NULL;
    public $News= NULL;
    public $Article= NULL;
    public $Gallery= NULL;


    /**
    * Class Constructor
    * Set the variabels
    * @return true/false
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 30.09.2011
    */
    function __construct()
    {
        if( defined("MAKE_DEBUG") AND MAKE_DEBUG==1 ){
            $this->time_start = $this->getmicrotime();
        }
        //======================== Define Language START =============================

        // if change language, then save it new value to COOKIE and to the session
        if(isset($_GET['lang_pg']))
        {
         setcookie('lang_pg', "", time()-60*60*24*31, '/');
         setcookie('lang_pg', intval($_GET['lang_pg']),time()+60*60*24*30, '/');
         //$_SESSION['lang_pg'] = $_GET['lang_pg'];
        }

        // if change language with using .htaccess, then save it new value to COOKIE and to the session
        if( isset( $_GET['lang_st'] ) )
        {
            $new_lang_id = SysLang::GetLangCodByShortName($_GET['lang_st']);
            setcookie('lang_pg', "", time()-60*60*24*31, '/');
            setcookie('lang_pg', $new_lang_id, time()+60*60*24*30, '/');
            //$_SESSION['lang_pg'] = $new_lang_id;
        }

        // if exist language in COOKIE and language is not set in session then set it in session
        //if( isset($_COOKIE['lang_pg']) AND !empty($_COOKIE['lang_pg'])  ) $_SESSION['lang_pg'] = $_COOKIE['lang_pg'];

        // if change language then set it in session
        if( isset($_GET['lang_pg']) AND !empty($_GET['lang_pg']) ) $_SESSION['lang_pg'] = intval($_GET['lang_pg']);

        // if change language with using .htaccess then set it in session
        if( isset($_GET['lang_st']) AND !empty($_GET['lang_st']) ) $_SESSION['lang_pg'] = $new_lang_id;

        // if language set in session then define this language for a site
        $tmp_lang = SysLang::GetDefFrontLangID();
        if(isset($_SESSION['lang_pg']) AND !empty($_SESSION['lang_pg'])) { if (!defined("_LANG_ID")) define("_LANG_ID", $_SESSION['lang_pg']); }
        // if language not set in session, then get default language from database
        else {
            // if default language set in the database then define this language for a site
            if( !empty($tmp_lang) ){
                if (!defined("_LANG_ID")) define("_LANG_ID", $tmp_lang);
            }
            // if default language not set in the database then define constant DEBUG_LANG from script /include/defines.php for a site
            else{
                if (!defined("_LANG_ID")) define("_LANG_ID", DEBUG_LANG);
            }
        }

        if (defined("_LANG_ID")){
            $this->SetLang(_LANG_ID);
            if( (SysLang::GetCountLang('front')>1 OR isset($_GET['lang_st'])) AND _LANG_ID!=$tmp_lang) define("_LINK", "/".SysLang::GetLangShortName(_LANG_ID)."/");
            else define("_LINK", "/");
        }
        else {
            define("_LINK", "/en/");
        }
        //======================== Define Language END =============================


        //======================== Define Currency START =============================
        // if change Currency, then save it new value to COOKIE and to the session
        if(isset($_GET['curr_ch']))
        {

         setcookie('curr_ch', "", time()-60*60*24*31, '/');
         setcookie('curr_ch', intval($_GET['curr_ch']), time()+60*60*24*30, '/');
         if (!defined("_CURR_ID")) define("_CURR_ID", intval($_GET['curr_ch']));
        }

        if(isset($_POST['curr_ch']))
        {

         setcookie('curr_ch', "", time()-60*60*24*31, '/');
         setcookie('curr_ch', intval($_POST['curr_ch']), time()+60*60*24*30, '/');
         if (!defined("_CURR_ID")) define("_CURR_ID", intval($_POST['curr_ch']));
        }

        //echo "<br>_COOKIE['curr_ch'] = ".$_COOKIE['curr_ch'];
        if( isset($_COOKIE['curr_ch']) AND !empty($_COOKIE['curr_ch'])  ){
           if (!defined("_CURR_ID")) define("_CURR_ID", intval($_COOKIE['curr_ch']));
        }
        else {
            $this->Currency = new SystemCurrencies();
            $def_currency = $this->Currency->GetDefaultCurrency();
            // if default Currency set in the database then define this Currency for a site
            if( !empty($def_currency) ) if (!defined("_CURR_ID")) define("_CURR_ID", $def_currency);
            // if default Currency not set in the database then define constant DEBUG_CURRENCY from script /include/defines.php for a site
            else { if (!defined("_CURR_ID")) define("_CURR_ID", DEBUG_CURR); }
        }
        //======================== Define Currency END =============================


        // for feedback httpreferer
        if( isset( $_SERVER['HTTP_REFERER'] ) AND !strstr($_SERVER['REQUEST_URI'], 'favicon.ico') ){
            $pos = strpos( $_SERVER['HTTP_REFERER'], 'http://'.$_SERVER['HTTP_HOST']);
            //echo '<br />$pos='.$pos;
            if($pos!==0){
                setcookie('refpage', $_SERVER['HTTP_REFERER'], time()+60*60*24*1, '/');
                //echo '<br />set cookie!';
            }
        }
        //for contol user serfing by pages of site
        //if( isset($_SERVER['REQUEST_URI']) AND !strstr($_SERVER['REQUEST_URI'], 'images/design') AND !strstr($_SERVER['REQUEST_URI'], 'favicon.ico') ){
        //    setcookie('serfing['.time().']', $_SERVER['REQUEST_URI'], time()+60*60*24*3, '/');
        //}



        //================= Display amount of pages for catalog START ========================
        if(isset($_GET['display']))
        {
            //echo 'GET[display] = '.$_GET['display'];
         setcookie('display', "", time()-60*60*24*31, '/');
         setcookie('display', intval($_GET['display']),time()+60*60*24*30, '/');
         if (!defined("_DISPLAY")) define("_DISPLAY", intval($_GET['display']));
        }
        if(isset($_POST['display']))
        {
            //echo 'POST[display] = '.$_POST['display'];
         setcookie('display', "", time()-60*60*24*31, '/');
         setcookie('display', intval($_POST['display']), time()+60*60*24*30, '/');
         if (!defined("_DISPLAY")) define("_DISPLAY", intval($_POST['display']));
        }
        if( isset($_COOKIE['display']) AND !empty($_COOKIE['display'])  ){
             //echo 'COOKIE = '.$_COOKIE['display'];
           if (!defined("_DISPLAY")) define("_DISPLAY", intval($_COOKIE['display']));
        }
        //================= Display amount of pages for catalog END ========================


        //Считываем кол-во запровос к базе данных до старта сессии, так как после старта сессии в переменную $_SESSION['cnt_db_queries']
        //подтянуться старые значения. Их нужно обновить новыми данными. Для этого сохраним текущее сзначение во временную переменную $tmp_cnt_db_queries,
        //а после старта сессии присвоим это значение в переменную $_SESSION['cnt_db_queries'].
        if(isset($_SESSION['cnt_db_queries'])) $tmp_cnt_db_queries = intval($_SESSION['cnt_db_queries']);
        else $tmp_cnt_db_queries = 0;

        //if session not started then start new session
        if ( !isset($_SESSION['session_id']) ){
            //Если в куки сохранена сессия, то уставаливаем ее как текущюю. Это необходимо
            //для подтягивания данных по сессии при закрытии и последующем открытии браузера.
            if( isset($_COOKIE[SEOCMS_SESSNAME]) ) {
                $sss = addslashes(strip_tags($_COOKIE[SEOCMS_SESSNAME]));
                //session_id($sss);
            }
            if( !headers_sent() ) session_start();
        }

        //Устанавливаем кол-во завросов к базе данных, которое произошло да страта сессии.
        if( defined("MAKE_DEBUG") AND MAKE_DEBUG==1 ){
            $_SESSION['cnt_db_queries'] = $tmp_cnt_db_queries;
        }

        //set encoding of the site
        $this->page_encode = SysLang::GetDefLangEncoding($this->GetLang());

        //============ Init all objects START ============
        $DBPDO = DBPDO::getInstance();
        if(empty($this->db)) $this->db = DBs::getInstance();
        if(empty($this->Lang)) $this->Lang = &check_init('SysLang', 'SysLang', _LANG_ID.', "front"');
        if(empty($this->multi)) $this->multi = &check_init_txt('TblFrontMulti', TblFrontMulti);
        if(empty($this->Form)) $this->Form = &check_init('FrontForm', 'FrontForm');
        if(empty($this->Spr)) $this->Spr = &check_init('FrontSpr', 'FrontSpr');

        if(defined("MOD_USER") AND MOD_USER AND empty($this->Logon) )
            $this->Logon = &check_init('UserAuthorize', 'UserAuthorize');

        if(defined("MOD_ORDER") AND MOD_ORDER AND empty($this->Order) )
            $this->Order = &check_init('OrderLayout', 'OrderLayout');

        if(defined("MOD_PAGES") AND MOD_PAGES AND empty($this->FrontendPages) )
            $this->FrontendPages = &check_init('FrontendPages', 'FrontendPages');

        if(defined("MOD_CATALOG") AND MOD_CATALOG AND empty($this->Catalog) )
            $this->Catalog = &check_init('CatalogLayout', 'CatalogLayout');

        if(defined("MOD_NEWS") AND MOD_NEWS AND empty($this->News) )
            $this->News = &check_init('NewsLayout', 'NewsLayout');

        //============ Init all objects END ============

        //Set default Meta data for site
        $this->SetTitle( META_TITLE );
        $this->SetDescription( META_DESCRIPTION );
        $this->SetKeywords( META_KEYWORDS );

    } // end of constructor PageUser()

    /**
    * Class method WriteHeader
    * Write HTML - Header of page
    * @return true/false or arrays:
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 30.09.2011
    */
    function WriteHeader()
    {
    $this->LangShortName = $this->Lang->GetLangShortName(_LANG_ID);
    $this->send_headers();
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$this->LangShortName;?>">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?=$this->page_encode;?>" />
        <meta http-equiv='Content-Type' content="application/x-javascript; charset=<?=$this->page_encode;?>" />
        <meta http-equiv="Content-Language" content="<?=$this->LangShortName;?>" />
        <title><?=htmlspecialchars($this->title);?></title>
        <meta name="Description" content="<? if( $this->Description ) echo htmlspecialchars($this->Description);else echo '';?>" />
        <meta name="Keywords" content="<? if( $this->Keywords ) echo htmlspecialchars($this->Keywords);else echo '';?>" />

        <?
        //echo '<br>$_SERVER["QUERY_STRING"]='.$_SERVER["QUERY_STRING"];
        //если это страница каталога с фмльтрами, то для гугла указывем дополнительные параметры
        //if( strstr($_SERVER["QUERY_STRING"], "parcod")){
        //более того, проверяем, есть ли любые дополнительные параметры в УРЛ,
        //и если есть, то будем закрыать от индексации и прописыать каноникал.
        if( strstr($_SERVER['REQUEST_URI'], '?')){
            //закрываем от индексации страницы результатов работы фильтров каталога товаров
            ?>
            <meta name="robots" content="noindex, nofollow"/>
            <?

            if(!isset($_SERVER['REDIRECT_URL'])) {
                $link = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')+1);
            }
            else{ $link = $_SERVER['REDIRECT_URL']; }
            $canonical = 'http://'.NAME_SERVER.$link;
            //echo '<br>$canonical='.$canonical;
            //Добавление этой ссылки и атрибута позволяет владельцам сайтов определять наборы идентичного содержания и сообщать Google:
            //"Из всех страниц с идентичным содержанием эта является наиболее полезной.
            //Установите для нее наивысший приоритет в результатах поиска."
            ?>
            <link rel="canonical" href="<?=$canonical;?>"/>
            <?
        }
        ?>

        <!--<link rel="Shortcut Icon" type="image/x-icon" href="/images/design/favicon.ico"/>-->
        <link rel="icon" type="image/vnd.microsoft.icon"  href="/images/design/favicon.ico" />
        <link rel="SHORTCUT ICON" href="/images/design/favicon.ico" />
        <link href="/include/css/main.css" type="text/css" rel="stylesheet" />
        <link href="/include/css/style.css" type="text/css" rel="stylesheet" />
        <!--[if IE ]>
        <link href="/include/css/browsers/ie.css" rel="stylesheet" type="text/css" media="screen" />
        <![endif]-->
        <!--[if lt IE 8]>
        <link href="/include/css/browsers/ie7.css" rel="stylesheet" type="text/css" media="screen" />
        <![endif]-->
        <!--[if lt IE 7]>
        <script type="text/javascript" src="/include/js/iepngfix_tilebg.js"></script>
        <![endif]-->

        <!--Include AJAX scripts-->
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <?/*<script type="text/javascript" src='http://<?=NAME_SERVER."/sys/js/jQuery/jquery.js";?>'></script>*/?>
        <?/*<script type="text/javascript" src="/include/js/cssWatch.js"></script>*/?>
        <?/*
        <script type="text/javascript" src="/include/js/highslide/highslide.js"></script>
        <script src="/include/js/cms_lib/popup.js" type="text/javascript" charset="windows-1251"></script>
        <script src="/include/js/cms_lib/comments.js" type="text/javascript" charset="windows-1251"></script>
        <link rel="stylesheet" type="text/css" href="/include/css/comments.css" media="screen" />

        <!------------------------------Старт валидации----------------------------------------->
        <script type="text/javascript" src="/include/js/validator/js/jquery.validationEngine.js"></script>
        <script type="text/javascript" src="/include/js/validator/js/languages/jquery.validationEngine-ru.js"></script>
        <link href="/include/js/validator/css/validationEngine.jquery.css" type="text/css" rel="stylesheet" media="screen"/>
        <!------------------------------Конец валидации валидации------------------------------->
        
        <script src="/include/js/cms_lib/lib.js" type="text/javascript" charset="windows-1251"></script>
        */?>
        <!-- Enable HTML5 tags for old browsers -->
        <script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        
        <script type="text/javascript" src="/include/js/cart.js"></script>
        
        <link href='http://webfonts.ru/import/charis.css' rel='stylesheet' type='text/css' />
        
        <!--Photo Gallery-->
        <script type="text/javascript" src="/include/js/gallery.js"></script>
        <link rel="stylesheet" type="text/css" href="/include/css/jquery.jcarousel.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="/include/css/jcarousel.gallery.css" media="screen" />
        <script type="text/javascript" src="/include/js/jquery.jcarousel.pack.js"></script>
        <!--End Photo Gallery-->
        
         <script type="text/javascript" src="/include/js/highslide/highslide.js"></script>
         <link rel="stylesheet" type="text/css" href="/include/js/highslide/highslide.css" media="screen" />

    </head>

    <body>

        <!--[if lt IE 8]>
        <div style=" margin:10px auto 0px auto; padding:20px; background:#DDDDDD; border:1px solid gray; width:980px; font-size:14px;">
        Уважаемый Пользователь!</br>
        Вы используете <span class="red">устаревший WEB-браузер</span>.</br>
        Предлагаем Вам установить и использовать последние версии WEB-браузеров, например:<br/>
        <ul>
            <li>Google Chrome <a href="https://www.google.com/chrome">https://www.google.com/chrome</a></li>
            <li>Mozilla Firefox <a href="http://www.mozilla.org/ru/firefox/new/">http://www.mozilla.org/ru/firefox/new/</a></li>
            <li>Opera <a href="http://www.opera.com/download/">http://www.opera.com/download/</a></li>
        </ul>
        Последние версии WEB-браузеров доступны для установки на сайтах разработчиков и содержат улучшенные свойства безопасности, повышенную скорость работы, меньшее количество ошибок. Эти простые действия помогут Вам максимально использовать функциональность сайта, избежать ошибок в работе, повысить уровень безопасности.
        </div>
        <![endif]-->
        <div class="HeaderFon">
            <div id="Header">
                <div class="top_icons">
                        <a class="icoHome" title="<?=$this->multi['TXT_FRONT_HOME_PAGE'];?>" href="<?=_LINK;?>">&nbsp;</a>
                        <a class="icoMail" title="<?=$this->multi['TXT_FEEDBACK'];?>" href="<?=_LINK;?>contacts/">&nbsp;</a>
                        <a class="icoMap"  title="<?=$this->multi['_TXT_SITE_MAP'];?>" href="<?=_LINK;?>sitemap/">&nbsp;</a>
                </div>
                <div class="user_order">
                    <div class="userForm">
                        <div class="userEnter">Вход в магазин</div>
                        <div class="userReg"><a href="/registration/">Регистрация</a></div>
                    </div>
                    <div class="OrderBlock" id="cart">
                        <?$this->Order->cart();?>
                    </div><?/*<?$this->Logon->LoginForm();?>*/
                ?></div>
            </div>
        </div>
        <div class="LogoFindContacts">
            <div class="Logo">
                <a href="/" title="Мебель, которая делает жизнь ЛУЧше">
                    <img src="/images/design/logo.png" title="Мебель, которая делает жизнь ЛУЧше" alt="Мебель, которая делает жизнь ЛУЧше" />
                </a>
            </div>
            <div class="ShortFind">
                <div class="Short">Краткое описание. Стяжение самопроизвольно. Структура почв, как того требуют законы термодинамики, переносит эксикатор в полном соответствии с законом Дарси.</div>
                <div id="findBox">
                    <form name="quick_find" method="get" action="<?=_LINK?>search/result/">
                        <input type="text"  name="query" placeholder="Например, шкаф-купе" value="" size="30" maxlength="100"/>
                    </form>
                </div>
            </div>
            <div id="headerPhone"><?=$this->FrontendPages->GetContentByCod(9);?></div>
        </div>
        <div class="MenuFon">
            <div id="Menu">
                <div class="MenuRightFon"><?$this->FrontendPages->ShowHorisontalMenu();?></div>
            </div>
        </div>
        <div id="wrapper">
            <?
                switch ($this->FrontendPages->page){
                    case PAGE_CATALOG:
                        ?><div class="bodyLeft">
                            <div>
                                <div class="NameLeftMenu">Каталог</div>
                                <div class="CatalogMenuLeftFon"><?$this->Catalog->ShowCatalogTree(false);?></div>
                            </div>
                            <div class="CatalogShowFilter">
                                <?$action = $this->Catalog->Link($this->Catalog->id_cat); ?>
                                <form name="filter" id="filter" action="<?=$action?>">
                                <div class="CatalogShowFilterName">Подобрать товар</div><?
                                //echo $this->Catalog->ShowSelectedFilters();
                                $this->Catalog->ShowFilterManufac($action);
                                echo $this->Catalog->ShowAllFilters();
                                $this->Catalog->ParamShowPricePanel();
                            ?></form>
                            </div>
                        </div><?
                        break;
                    default:
                        break;
                }?>
            <div class="bodyCenter">
                <div id="my_d_basket" class="my_d_basket">
                <?
                //=== if set error page 404 then show error ===
                if( $this->Is_404() ){
                        $txt =  $this->multi['MSG_404_PAGE_NOT_FOUND'];
                        if( empty($txt) ) $txt = 'Error 404 - Page Not Found';
                        $this->Form->WriteContentHeader($this->multi['MSG_404_PAGE_NOT_FOUND'], false,false);
                        ?><div class="err"><?=$txt;?></div><?
                        $this->Form->WriteContentFooter();
                        $this->WriteFooter();
                        exit;
                }
            //==================================================
    } // end of function WriteHeader()

    
    function MainPage(){
        ?><div class="CatalogMenuActionFon">
            <div class="CatalogMenuAction">
                <div id="fonForCatalogMenuMainPage"><?$this->Catalog->ShowCatalogTree();?></div>
                <div class="actinMainPage"></div>
            </div>
        </div>
        <div class="CatalogNewBest">
            <div class="BlockNew"><?=$this->Catalog->BestProducts(4,1);?></div>
            <div class="BlockPolosa"></div>
            <div class="BlockNew"><?=$this->Catalog->BestProducts(4);?></div>
            <div class="BlockPolosa"></div>
        </div>
        <div class="MainPageShortNews">
            <div class="MainPageShort">
                <div class="MainPageHead">
                    <div class="MainPageHeadName">Коротко о нас</div>
                    <div class="MainPageHeadHref"><a href="/o-fabrike/">Подробнее</a></div>
                </div>
                <div class="MainPageConten">
                    <div class="MainPageContenHref">
                        <a href="/o-fabrike/">Мебельная фабрика “Луч”</a>
                    </div>
                    <div class="MainPageContenText"><?=$this->FrontendPages->page_txt['content']?></div>
                </div>
            </div>
            <div class="MainPageNews">
                <div class="MainPageHead">
                    <div class="MainPageHeadName">Новости и акции</div>
                    <div class="MainPageHeadHref"><a href="/news/">Все новости</a></div>
                </div>
                <div class="MainPageConten">
                    <div class="MainPageContenText"><?=$this->News->NewsLast(2)?></div>
                </div>
            </div>
        </div><?
    }

    /**
    * Class method WriteFooter
    * Write HTML - footer of page
    * @return true/false or arrays:
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 30.09.2011
    */
    function WriteFooter()
    {
                    ?></div>
                </div>
            </div>
        <div class="footerFon">
            <div class="footer">
                <div class="footerHead">Контакты</div>
                <div class="footerCenter">
                    <div class="footerCenterContacts"><?=$this->FrontendPages->treePageData[10]['content']?></div>
                    <div class="footerCenterMenu"><?$this->FrontendPages->ShowFooterMenu();?></div>
                </div>
                <div class="footerFooter">
                    <?$year = date("Y");
                    if($this->FrontendPages->page==$this->FrontendPages->GetMainPage()) $txt = $this->multi['_SITE_DEVELOPER_TEXT_2'];
                    else $txt = $this->multi['_SITE_DEVELOPER_TEXT'];?>
                    <div class="footerFooterCopy">&copy;&nbsp;2009-<?=$year;?> <span>Луч.</span> Все прва защищены. <?=$txt?></div>
                    <div class="footerFooterShotchikFon">
                        <div class="footerFooterShotchik">
                            <div class="footerFooterFonShotchik"><div class="Shotchik"></div></div>
                            <div class="footerFooterFonShotchik"><div class="Shotchik"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </body>
        </html>
    <?
       if( !$this->Is_404() ){
           /* Statistic module */
           $st = new Stat();             //--- create Statistic-Object
           //if set to save front-end statistic then do it.
           if($st->Set->front){
               $st->user = $this->Order->Logon->user_id;  //--- set cuurrent user id
               $res = $st->Set();            //--- set all property's for log and save in database
           }
       }

       if( defined("MAKE_DEBUG") AND MAKE_DEBUG==1 ){
           $this->time_end = $this->getmicrotime();
           ?><div style="font-size:9px; color:#797979;"><?
           printf ("<br/>TIME:%2.3f", $this->time_end - $this->time_start);
           if( isset($_SESSION['cnt_db_queries'])) echo '<br/>QUERIES: '.$_SESSION['cnt_db_queries'];
           ?></div><?
       }
    } // end of function WriteFooter()


} //end of class PageUser
?>