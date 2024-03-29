<?php
// ================================================================================================
// System : CMS
// Module : userShow.class.php
// Date : 22.02.2011
// Licensed To: Yaroslav Gyryn 
// Purpose : Class definition For display interface of External users
// ================================================================================================

include_once( SITE_PATH.'/modules/mod_user/user.defines.php' );

/**
* Class User
* Class definition for all Pages - user actions
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 22.02.2011
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
*/  
 class UserShow extends User {
       var $db=NULL;
       var $Msg=NULL;
       var $logon=NULL;
       var $Spr=NULL;
       var $Form = NULL;
       
       var $whattodo = NULL;
       var $referer_page = NULL;
       var $TextMessages = NULL;

       // ================================================================================================
       //    Function          : UserShow (Constructor)
       //    Date              : 22.02.2011
       //    Parms             : session_id / id of the ssesion
       //                          user_id    / User ID
       //    Returns           : Error Indicator
       //    Description       : Init variables
       // ================================================================================================
        function UserShow( $session_id=NULL, $user_id=NULL) {
                ( $session_id   !="" ? $this->session_id  = $session_id   : $this->session_id  = NULL );
                ( $user_id      !="" ? $this->user_id     = $user_id      : $this->user_id     = NULL );

                if (empty($this->db)) $this->db = DBs::getInstance();
                if (empty($this->Msg)) $this->Msg = &check_init('ShowMsg', 'ShowMsg');
                if (empty($this->Logon)) $this->Logon = &check_init('UserAuthorize', 'UserAuthorize');
                if (empty($this->Spr)) $this->Spr = &check_init('FrontSpr', 'FrontSpr');
                if (empty($this->Form)) $this->Form = &check_init('FrontForm', 'FrontForm');
                $this->multiUser = &check_init_txt('TblFrontMulti',TblFrontMulti); //$this->Msg->GetMultiTxtInArr(TblModUserSprTxt);
                if(empty($this->Catalog)) $this->Catalog = Singleton::getInstance('Catalog'); 
                
       } // End of UserShow Constructor

    
    // ================================================================================================
    // Function : LoginPage
    // Date : 22.02.2011
    // Returns : true,false / Void
    // Description : Show form for logon of the user on the front-end
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function LoginPage()
    {
        if( !$this->Logon->user_id ){
     if (empty($this->whattodo)) $this->whattodo=2;
     $this->Form->WriteFrontHeader( 'Login', _LINK.'login.html', NULL, NULL );
     //echo '<br>$this->referer_page='.$this->referer_page;
     if ( !isset($this->referer_page) OR empty($this->referer_page) ) {
        if ( isset($_SERVER['HTTP_REFERER']) ) {
            $this->referer_page = str_replace('&','AND',$_SERVER['REQUEST_URI']);
            $title = $this->multiUser['TXT_FRONT_PLEASE_LOGIN'];
        }
        else {
            $this->referer_page='/login.php?task=makelogon';
            $title = $this->multiUser['TXT_TITLE_LOGIN_PAGE'];
        }

     }
     else{
         $title = $this->multiUser['TXT_TITLE_LOGIN_PAGE'];
     }
     $this->Form->Hidden('referer_page', "/myaccount/");
     $this->Form->Hidden('whattodo', $this->whattodo);
     
                  
                  ?>
<div id="catalogBox">
            <span class="MainHeaderText">Авторизація</span>
            
            <div id="catalogBody">
                <?if(!empty($this->Err) || !empty($this->TextMessages)){?>
                <div class="err" style="margin-top: 25px;">
                <?
                $this->ShowErr();
                  $this->ShowTextMessages(); 
                ?>
               
                    </div>
                 <?}?>
                  <table border="0" cellspacing="8" cellpadding="0" class="tblRegister" style="margin-top: 20px;margin-bottom: 20px;">
                   <tr align="right">
                        <td><span style="color:#515151;font-weight: bold;">Ім'я користувача</span><span class="redStar">* </span></td>
                        <td align="left"><?=$this->Form->TextBox( 'login', $this->login);?></td>
                   </tr>
                   <tr align="right">
                        <td><span style="color:#515151;font-weight: bold;"><?=$this->multiUser['FLD_PASSWORD']?></span><span class="redStar">* </span></td>
                        <td align="left"><?=$this->Form->Password( 'pass', '', 20 );?></td>
                   </tr>
                   <tr>
                       <td></td>
                       <td><a href="<?=_LINK;?>forgotpass.html" class="a02"><?=$this->multiUser['TXT_FORGOT_PASS'];?></a> <a style="float: right;margin-right: 20px;" href="/registration/" title="Реєстрація">Реєстрація</a></td>
                   </tr>
                  </table> 
                  <div class="submit">
                    <?$btnSubmit = $this->multiUser['BTN_SUBMIT']; ?>
                    <input class="btnCatalogImgUpload" style="margin-top: 0px;" type="submit" value="<?=$btnSubmit;?>"/>
                  </div>
                  
                
            </div></div>
                  <?
     
     
      
    $this->Form->WriteFrontFooter();
        }else{
            echo "<script type='text/javascript'>location.href='/'</script>";
        }
    } //end of function LoginPage()
           
   
    // ================================================================================================
    // Function : LoginPageOrder
    // Date : 22.02.2011
    // Returns : true,false / Void
    // Description : Show form for logon of the user on the front-end
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function LoginPageOrder( $referer_page )
    {
        if(!empty($referer_page)) $this->referer_page = $referer_page;
        if (empty($this->whattodo)) $this->whattodo=2;
    ?> 
      <h1><?=$this->multi['TXT_AUTHORIZATION'];?></h1>
      <div class="body">
       <div class="orderFirstStepTxt">
        <?=$this->multi['TXT_SECOND_STEP'];?>
       </div>  

       <div class="rightHeader">
        <div class="orderStep">
         <?=$this->multi['TXT_STEP_2'];?>
        </div>
        <div class="orderStepImage">
         <img src="/images/design/step2.gif">
        </div>
       </div>

          <?
         $this->Form->WriteFrontHeader( 'Login', _LINK.'login.html', NULL, NULL );
         //echo '<br>$this->referer_page='.$this->referer_page;
         if ( !isset($this->referer_page) OR empty($this->referer_page) ) {
            if ( isset($_SERVER['HTTP_REFERER']) ) 
                $this->referer_page = str_replace('&','AND',$_SERVER['REQUEST_URI']);
            else 
                $this->referer_page='/login.php?task=makelogon';
         }
         //echo '<br>$this->referer_page='.$this->referer_page;
         $this->Form->Hidden('referer_page', $this->referer_page);
         $this->Form->Hidden('whattodo', $this->whattodo);      
               
           if( !$this->Logon->user_id ){
               echo $this->ShowErr();
               echo $this->ShowTextMessages(); 

           ?>
          <div class="orderHelpText">
           <?=$this->multi['TXT_HELP_NEW_USER'];?>
          </div>
          
          <div class="registerLinks">
              <a href="<?=_LINK;?>registration/" class="registerLink"><?=$this->multiUser['IMG_FRONT_SIGN_UP'];?></a>
          </div>
          
          <div class="orderHelpText">
           <?=$this->multiUser['TXT_FRONT_RETURNING_USER_DESCRIPTION'];?>
          </div>
                             
           <div id="content2Box">
               <div class="subBody" align="left" style="padding-top:15px;">
                  <table border="0" cellspacing="2" cellpadding="0" class="regTable" width="100%">

                   <tr>
                     <td width="200">
                        <?=$this->multiUser['FLD_LOGIN'];?>
                        &nbsp;
                        <?=$this->Form->TextBox( 'login', $this->login, 'size="10"' );?>
                     </td>
                     <td width="170">
                        <?=$this->multiUser['FLD_PASSWORD'];?>
                        <?=$this->Form->Password( 'pass', '', 10 );?>
                     </td>
                     <td>
                        <?$btnSubmit = $this->multiUser['BTN_SUBMIT']; ?>
                        <input type="image" src="/images/design/submit.png" alt="<?=$btnSubmit;?>" title="<?=$btnSubmit;?>"/>
                     </td>
                   </tr>
                  </table>
                  <div style="float:right; margin: 0px 20px 10px 0px;"><a href="<?=_LINK;?>forgotpass.html" class="registerLink"><?=$this->multiUser['TXT_FORGOT_PASS'];?></a></div>
                  
                </div>
           </div>
                <?
           }
          /* else{
                $title = 'Зайти в мой профайл';
           ?>
                <div class="categoryTxt"><?=$title;?></div>
           </div>
           <div id="content2Box">
               <div class="subBody">
                    Для Вашего компьютера уже создана сессия с логином <?=$this->Logon->login;?>. Вы можете <a href="<?=_LINK;?>myaccount/" title="перейти в профайл">перейти в свой профайл</a> или <a href="<?=_LINK;?>logout.html" title="завершить сеанс">завершить сеанс</a>.
                </div>
           </div>
           <?
              }*/
           ?>
     
       <div class="orderHelpInfo" align="left">
          <?=$this->multi['TXT_HELP_INFO'];?>:
          <div class="orderHelpText">
             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$this->multi['TXT_HELP_FORGET_PSW'];?>
          </div>  
          
          <div class="orderHelpText">
             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$this->multi['TXT_HELP_SECURITY'];?>
          </div>
       </div>
       <?$this->Form->WriteFrontFooter(); ?>
      </div>   
    <?        
    } //end of function LoginPageOrder()


    // ================================================================================================
    // Function : ShowRegForm
    // Date : 22.02.2011
    // Parms : $new_stat_id - id of the new created records of user stat.
    // Returns : true,false / Void
    // Description : Show the second step of regidstration. This is the personal and contact information.
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function checkAjaxFields(){
        if(empty ($this->val)){ echo 3;return false;}
        switch ($this->wichField) {
            case "login":
                
                $q="SELECT `login` FROM sys_user WHERE `login`='".$this->val."'";
                $this->db->db_Query($q);
                if($this->db->db_GetNumRows()>0) echo 1; else echo 0;
                break;
            case "email":
                $q="SELECT `email` FROM mod_user WHERE `email`='".$this->val."'";
                $this->db->db_Query($q);
                if($this->db->db_GetNumRows()>0) echo 1; else echo 0;
                break;

            default:
                break;
        }
    }
    
    
    
    function ShowRegForm()
    {
       ?>
        <div id="catalogBox">
            <span class="MainHeaderText">Реєстрація</span>
            <div id="CatformAjaxLoader"></div>
            <div id="catalogBody">
        
        <div class="registerBoxDiv">
            
         <div align="center"><?$this->ShowErr();?></div>
 <?
         $this->Form->WriteFrontHeader(NULL, "#", 'save_reg_data');
         //$this->Form->Hidden( 'save_reg_data', 'save_reg_data' );
         $this->Form->Hidden( 'subscr', $this->subscr );
         $this->Form->Hidden( 'referer_page', $this->referer_page );
         ?>
         <input type="hidden" id="UserImageFilePath" value="" name="userImage"/>
                <ul class="CatFormUl">
                  <li>Нікнейм:<span class="redStar"> *</span><br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="login" onkeyup="chekFildsTimer('login',this.value,'resultofChek1')" onchange="chekFildsTimer('login',this.value,'resultofChek1')" onblur="chekFildsTimer('login',this.value,'resultofChek1')"  value="<?=$this->login?>"/>
                      <div id="resultofChek1"></div>
                  </li>
                  <li>Пароль:<span class="redStar"> *</span><br/>
                      <input id="nameOfPred" type="password" class="CatinputFromForm" onblur="passChek();" name="password" value=""/>
                  </li>
                  <li>Повторіть пароль:<span class="redStar"> *</span><br/>
                      <input id="nameOfPred" type="password" onblur="passChek();" class="CatinputFromForm" name="password2" value=""/>
                      <div id="passchek" class="redStar"></div>
                  </li>
                  <li>Прізвище:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="country" value="<?=$this->country?>"/>
                  </li>
                  <li>Ім'я:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="name" value="<?=$this->name?>"/>
                  </li>
                  <li>
                      Стать:<br/>
                      <select class="CatSelectFromForm" name="state">
                          <option <?if($this->state=="m" || $this->state==NULL) echo "selected";?> selected  value="m">Чоловіча</option>
                          <option <?if($this->state=="w") echo "selected";?> value="w">Жіноча</option>
                      </select>
                  </li>
                  <li>
                      День:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,1)"  type="text" class="dataInput" name="day" value="<?=$this->day?>"/>
                      Місяць:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,2)" type="text" class="dataInput" name="month" value="<?=$this->month?>"/>
                      Рік:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,3)" type="text" class="dataInput" style="width: 30px;" name="year" value="<?=$this->year?>"/>
                       <div id="dateChek" class="redStar"></div>
                  </li>
                  <li>
                      Email:<span class="redStar"> *</span><br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="email" value="<?=$this->email?>" onkeyup="chekFildsTimer('email',this.value,'resultofChek2')" onchange="chekFildsTimer('email',this.value,'resultofChek2')" onblur="chekFildsTimer('email',this.value,'resultofChek2')" />
                      <div id="resultofChek2"></div>
                  </li>
                  <li>Сайт:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="www" value="<?=$this->www?>"/>
                  </li>
                  <li>Телефон:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="phone" value="<?=$this->phone?>"/>
                  </li>
                  <li>Facebook:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="phone_mob" value="<?=$this->phone_mob?>"/>
                  </li>
                  <li>Вконтакті:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="fax" value="<?=$this->fax?>"/>
                  </li>
                  <li>Twitter:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="bonuses" value="<?=$this->bonuses?>"/>
                  </li>
                  
                 </ul>
         <input type="hidden" name="user_status" value="3"/>
         <span style="font-weight: bold;"> Коротко про себе:<br/></span>
                       <textarea name="aboutMe" class="aboutMeText tinyProfile"><?=$this->aboutMe?></textarea>
         <?$this->Form->WriteFrontFooter(); ?>
                 <ul class="CatFormUl" id="imgLoaderConteiner">
                            <li id="imgLoaderConteiner" style="height: auto;">
                                <div id="catImgAjaxLoader"></div>
                        <div id="CatImageUploadBox">
                            <form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe">
                                         <input type="hidden" value="addImage" name="task"/>
                                         <input type="hidden" value="true" name="ajax"/>
                                      Виберіть зображення для аватари:<br/>
                                      <input id="catUserFileUploader" type="file" name="image" size="80"/>
                                      <input class="btnCatalogImgUpload" type="button" onclick="loadImage();" value="Завантажити"/>
                            </form>
                        </div>
                            <iframe id="hiddenframe" name="hiddenframe" style="width:0px; height:0px; border:0px"></iframe>
                            </li>
                            <li>
                              
                            </li>
                        </ul>
         <br/><input type="button" style="float: right" class="btnCatalogImgUpload" onclick="verify()" name="save_reg_data" class="submitBtn<?=_LANG_ID?>" value="Реєстрація" />
        </div>
         <div class="needFields">
          <span class="redStar"> *</span> - поля з зірочкою обов'язкові для заповнення при реєстрації на порталі.
          <br/><br/>
         </div>

        </div>
        </div>
                
       <?
       
    } //end of function ShowRegForm()
    
   function showRegJS(){
       ?>
       <script type="text/javascript" src="/include/js/tinymce/tiny_mce.js"></script>
        <script language="JavaScript"> 
            
            
            
            var tinyMCE;
                    function tinyMceInit(){
                        tinyMCE.init({
                                // General options
                                mode : "textareas",
                theme : "advanced",
                theme_advanced_buttons1 : "mymenubutton,bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,undo,redo,link,unlink",
                theme_advanced_buttons2 : "",
                theme_advanced_buttons3 : "",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                               skin : "o2k7",
                               skin_variant : "silver",
                                //Path
                                relative_urls : false,
                                remove_script_host : true,

                                //extended_valid_elements : "tcut",

                                language : "ru"
                        });
                    }
                    tinyMceInit();
            
            var unikLogin=false;
            var unikEmail=false;
            var chekTimer;
            var wichFiledG,valG,resultBoxG;
            
            function chekFildsTimer(wichFiled,val,resultBox){
                window.clearInterval(chekTimer);
                wichFiledG=wichFiled;
                valG=val;
                resultBoxG=resultBox;
                chekTimer=window.setTimeout("chekFields();", 1000);
            }
            function chekFields(){
                wichFiled=wichFiledG;
                val=valG;
                resultBox=resultBoxG;
                $.ajax({
                   type: "GET",
                   url: "<?=_LINK;?>checkReg?wichField="+wichFiled+"&val="+val,
                   beforeSend : function(){ 
                       $("#"+resultBox).html("");
                       $("#"+resultBox).css("background","url('/images/design/reg/ajax-loader.gif')no-repeat");
                       
                    },
                   success: function(html){
                       result=parseInt(html);
                       $("#"+resultBox).css("background","none");
                       if(result==1){
                           if(wichFiled=="login") $("#"+resultBox).html("<span class='redStar'>Такий нікнейм вже існує!</span>");
                           else $("#"+resultBox).html("<span class='redStar'>Такий E-mail вже зареэстрований!</span>");
                       } 
                       if(result==3){
                            $("#"+resultBox).html("<span class='redStar'>Це поле потрібно заповнити!</span>");
                       } 
                       if(result==0){ 
                           $("#"+resultBox).html("");
                           if(wichFiled=="login") unikLogin=true;
                           if(wichFiled=="email") unikEmail=true;
                       }
                   }
                });
            }
            function emailCheck (emailStr) {
                if (emailStr=="") return true;
                var emailPat=/^(.+)@(.+)$/;
                var matchArray=emailStr.match(emailPat);
                if (matchArray==null) 
                {
                    return false;
                }
                return true;
            }
            function passChek(){
                if (document.forms.form_mod_user.password.value!=document.forms.form_mod_user.password2.value) {
                    $("#passchek").html("Введені паролі не співпадають!");
                }else $("#passchek").html("");
            }
            function check(input,elem,wich) {     //метод, проверяющий значение поля input
               var resultint="";   //здесь сохранит итоговый результат
               var accept = "1234567890";   //допустимые символы, в данном случае числа

               for (var i = 0; i < input.length; i++) {   //проходим циклом по введенному в поле значению

               var symbol=""; //текущий символ
                  for (var j = 0; j < accept.length; j++){   //вложенный цикл, проверяем каждый символ поля на допустимость
                     if(input.charAt(i)==accept.charAt(j)) {    //если символ разрешен
                        symbol=input.charAt(i);
                        resultint+=symbol;   //добавляем его к resultint, таким образом, формируя его
                     }
                  }
               }
               if(wich==1) if(resultint>31) resultint="";
               if(wich==2) if(resultint>12) resultint="";
               if(wich==3) if(resultint<1900 || resultint>2020) resultint="";
               if(resultint=="") $("#dateChek").html("Введіть корректну дату народження!"); else $("#dateChek").html("")
               elem.value=resultint;
            }
            function verify() {
                var themessage = "<div style='text-align: left;'>Перевірте правильність заповнення полів реестрації.<br/> Зверніть увагу на слідуючі помилки:<br/><br/><span style='color:red;'>";
                if (document.forms.form_mod_user.login.value=="") {
                    themessage = themessage + " - Ви не ввели обов'язкове поле логіну!<br/>";
                }
                if ((!emailCheck(document.forms.form_mod_user.email.value))||(document.forms.form_mod_user.email.value=='')) {
                    themessage = themessage + " - Введіть будь ласка ваш E-mail!<br/>";
                }
                if (document.forms.form_mod_user.day.value=="" || document.forms.form_mod_user.month.value=="" || document.forms.form_mod_user.year.value=="") {
                    themessage = themessage + " - Ви не ввели свою дату народження!<br/>";
                }
                
                if (document.forms.form_mod_user.password.value!=document.forms.form_mod_user.password2.value || document.forms.form_mod_user.password.value=="") {
                    themessage = themessage + " - Введені паролі не співпадають або пусті!<br/>";
                }
                
                if(!unikLogin){
                    themessage = themessage + " - Ви ввели не унікальний нікнейм. Такий нікнейм вже існує!<br/>";
                }
                if(!unikEmail){
                    themessage = themessage + " - Ви ввели не унікальний E-mail. Такий E-mail вже існує!<br/>";
                }
                if (themessage == "<div style='text-align: left;'>Перевірте правильність заповнення полів реестрації.<br/> Зверніть увагу на слідуючі помилки:<br/><br/><span style='color:red;'>")
                {
                    $("#aboutMe").val(tinyMCE.get('aboutMe').getContent());
                    SaveForm();
                    return true;
                }
                else 
                   $.fancybox(themessage+"</span></div>");
                return false;
            }
         function SaveForm(){
              $.ajax({
                   type: "POST",
                   data: $("#form_mod_user").serialize() ,
                   url: "<?=_LINK;?>registration/result.html",
                   beforeSend : function(){ 
                       $("#CatformAjaxLoader").width($("#catalogBody").width()+64).height($("#catalogBody").height()+20).fadeTo("fast", 0.4);
                        //$(Did).show("fast");
                        if (tinyMCE) {
                              for (n in tinyMCE.instances) {
                                inst = tinyMCE.instances[n];
                                if (tinyMCE.isInstance(inst)) {
                                  tinyMCE.execCommand('mceRemoveControl', false, inst.editorId);
                                }
                                }
                        }
                    },
                   success: function(html){
                       $("#CatformAjaxLoader").fadeOut("fast",function(){
                           $("#contentBox").html(html);
                           tinyMceInit();
                       });
                   }
              });
         }
         function loadImage(){
                if($('#catUserFileUploader').val()!=""){
                    loader=$("#imgLoaderConteiner");
                    $("#catImgAjaxLoader").width(loader.width()+10).height(loader.height()+30).fadeTo("fast", 0.4);
                    $('#catLoadImageForm').submit();
                }else $.fancybox('Виберіть зображення для завантаження');
            }
            function del(){
                $("#catImgAjaxLoader").fadeOut("fast", function(){
                    $('#CatImageUploadBox').html('<form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe">                               <input type="hidden" value="addImage" name="task"/><input type="hidden" value="true" name="ajax"/>                              Виберіть зображення:<br/>                              <input id="catUserFileUploader" type="file" name="image" size="80"/>                              <input class="btnCatalogImgUpload" type="button" onclick="loadImage();" value="Завантажити"/>                    </form>');
                });
            }
            function response(err,filePath,file){
              $("#catImgAjaxLoader").fadeOut("fast", function(){
                if(err==''){
                    $("#UserImageFilePath").val(file);
                    $('#CatImageUploadBox').html('<img class="avatarImage" width="120" src="'+filePath+'"/><form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe"><input type="hidden" value="deleteImage" name="task"/><input type="hidden" value="true" name="ajax"/><input type="hidden" value="'+filePath+'" name="fileDel"/><input type="button" class="btnCatalogFormDel" onclick="loadImage();" value="Видалити"/></form>');
                }else{
                    $.fancybox(err);
                }
              });
            }
        </script> 
      <?
   }
    // ================================================================================================
    // Function : ShowRegFinish
    // Date : 22.02.2001
    // Returns : true,false / Void
    // Description : Show finish of registraion
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function ShowRegFinish($res=NULL)
    {
       ?><div><?
       if($res) $this->ShowTextMessages($this->Msg->show_text('MSG_PROFILE_SENT_OK'));
       else $this->ShowTextMessages($this->Msg->show_text('MSG_PROFILE_NOT_SENT'));
       ?></div><?
    } //end of function ShowRegFinish()
    

    // ================================================================================================
    // Function : CheckFields()
    // Date : 22.02.2011
    // Parms :        $id - id of the record in the table
    // Returns :      true,false / Void
    // Description :  Checking all fields for filling and validation
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function CheckFields($id = NULL)
    {
        $this->Err=NULL;
        //echo '$this->email ='.$this->email;
       $q="SELECT `login` FROM sys_user WHERE `login`='".$this->login."'";
                $this->db->db_Query($q);
                if($this->db->db_GetNumRows()>0) $this->Err.="Користувач з таким нікнеймом вже існує<br/>";
                $q="SELECT `email` FROM mod_user WHERE `email`='".$this->email."'";
                $this->db->db_Query($q);
                if($this->db->db_GetNumRows()>0) $this->Err.="Користувач з такою електронною поштою вже зареестрованый<br/>";
        //echo '<br>$this->Err='.$this->Err.' $this->Msg->table='.$this->Msg->table;
        return $this->Err;
    } //end of function CheckFields()       

    
    // ================================================================================================
    // Function : EditProfile
    // Date : 22.02.2001
    // Returns : true,false / Void
    // Description : Show the form for editig data of profile of the user on front-end.
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
   function Show_JS(){
       ?>
        <script type="text/javascript" src="/include/js/tinymce/tiny_mce.js"></script>
        <script language="JavaScript"> 
            var unikEmail=false;
            var tinyMCE;
                    function tinyMceInit(){
                        tinyMCE.init({
                                // General options
                                mode : "textareas",
                theme : "advanced",
                theme_advanced_buttons1 : "mymenubutton,bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,undo,redo,link,unlink",
                theme_advanced_buttons2 : "",
                theme_advanced_buttons3 : "",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                               skin : "o2k7",
                               skin_variant : "silver",
                                //Path
                                relative_urls : false,
                                remove_script_host : true,

                                //extended_valid_elements : "tcut",

                                language : "ru"
                        });
                    }
                    tinyMceInit();
                
            function chekFields(wichFiled,val,resultBox){
                $.ajax({
                   type: "GET",
                   url: "<?=_LINK;?>checkReg?wichField="+wichFiled+"&val="+val,
                   beforeSend : function(){ 
                       $("#"+resultBox).html("");
                       $("#"+resultBox).css("background","url('/images/design/reg/ajax-loader.gif')no-repeat");
                    },
                   success: function(html){
                       result=parseInt(html);
                       $("#"+resultBox).css("background","none");
                       if(result==1){
                           if(wichFiled=="login") $("#"+resultBox).html("<span class='redStar'>Такий нікнейм вже існує!</span>");
                           else $("#"+resultBox).html("<span class='redStar'>Такий E-mail вже зареэстрований!</span>");
                       } 
                       if(result==3){
                            $("#"+resultBox).html("<span class='redStar'>Це поле потрібно заповнити!</span>");
                       } 
                       if(result==0){ 
                           $("#"+resultBox).html("");
                           if(wichFiled=="login") unikLogin=true;
                           if(wichFiled=="email") unikEmail=true;
                       }
                   }
                });
            }
            function emailCheck (emailStr) {
                if (emailStr=="") return true;
                var emailPat=/^(.+)@(.+)$/;
                var matchArray=emailStr.match(emailPat);
                if (matchArray==null) 
                {
                    return false;
                }
                return true;
            }
            function verify() {
                var themessage = "<div style='text-align: left;'>Перевірте правильність заповнення полів реестрації.<br/> Зверніть увагу на слідуючі помилки:<br/><br/><span style='color:red;'>";
                
//                if ((!emailCheck(document.forms.profile.email.value))||(document.forms.profile.email.value=='')) {
//                    themessage = themessage + " - Введіть будь ласка ваш E-mail!<br/>";
//                }
                if (document.forms.profile.day.value=="" || document.forms.profile.month.value=="" || document.forms.profile.year.value=="") {
                    themessage = themessage + " - Ви не ввели свою дату народження!<br/>";
                }
                
//                if(!unikEmail){
//                    themessage = themessage + " - Ви ввели не унікальний E-mail. Такий E-mail вже існує!<br/>";
//                }
                if (themessage == "<div style='text-align: left;'>Перевірте правильність заповнення полів реестрації.<br/> Зверніть увагу на слідуючі помилки:<br/><br/><span style='color:red;'>")
                {
                    $("#aboutMe").val(tinyMCE.get('aboutMe').getContent());
                    SaveForm();
                    return true;
                }
                else 
                   $.fancybox(themessage+"</span></div>");
                return false;
            }
            

         function SaveForm(){
              $.ajax({
                   type: "POST",
                   data: $("#profile").serialize() ,
                   url: "<?=_LINK;?>myaccount/update/",
                   beforeSend : function(){ 
                       $("#CatformAjaxLoader").width($("#catalogBody").width()).height($("#catalogBody").height()+20).fadeTo("fast", 0.4);
                        //$(Did).show("fast");
                        if (tinyMCE) {
                              for (n in tinyMCE.instances) {
                                inst = tinyMCE.instances[n];
                                if (tinyMCE.isInstance(inst)) {
                                  tinyMCE.execCommand('mceRemoveControl', false, inst.editorId);
                                }
                                }
                        }
                    },
                   success: function(html){
                       $("#CatformAjaxLoader").fadeOut("fast",function(){
                           $("#catalogBox").html($("#catalogBox",html).html());
                            tinyMceInit();
                       });
                   }
              });
         }
         function check(input,elem,wich) {     //метод, проверяющий значение поля input
               var resultint="";   //здесь сохранит итоговый результат
               var accept = "1234567890";   //допустимые символы, в данном случае числа

               for (var i = 0; i < input.length; i++) {   //проходим циклом по введенному в поле значению

               var symbol=""; //текущий символ
                  for (var j = 0; j < accept.length; j++){   //вложенный цикл, проверяем каждый символ поля на допустимость
                     if(input.charAt(i)==accept.charAt(j)) {    //если символ разрешен
                        symbol=input.charAt(i);
                        resultint+=symbol;   //добавляем его к resultint, таким образом, формируя его
                     }
                  }
               }
               if(wich==1) if(resultint>31) resultint="";
               if(wich==2) if(resultint>12) resultint="";
               if(wich==3) if(resultint<1900 || resultint>2020) resultint="";
               if(resultint=="") $("#dateChek").html("Введіть корректну дату народження!"); else $("#dateChek").html("")
               elem.value=resultint;
            }
         function loadImage(){
                if($('#catUserFileUploader').val()!=""){
                    loader=$("#imgLoaderConteiner");
                    $("#catImgAjaxLoader").width(loader.width()+10).height(loader.height()).fadeTo("fast", 0.4);
                    $('#catLoadImageForm').submit();
                }else $.fancybox('Виберіть зображення для завантаження');
            }
            function del(){
                $("#catImgAjaxLoader").fadeOut("fast", function(){
                    $('#CatImageUploadBox').html('<form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe">                               <input type="hidden" value="addImage" name="task"/><input type="hidden" value="true" name="ajax"/>                              Виберіть зображення:<br/>                              <input id="catUserFileUploader" type="file" name="image" size="15"/>                              <input class="btnCatalogImgUpload" type="button" onclick="loadImage();" value="Завантажити"/>                    </form>');
                });
            }
            function response(err,filePath,file){
              $("#catImgAjaxLoader").fadeOut("fast", function(){
                if(err==''){
                    $("#UserImageFilePath").val(file);
                    $('#CatImageUploadBox').html('Аватар:<br/><img class="avatarImage" style="border:white solid 3px;" width="120" height="120" src="'+filePath+'"/><form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe"><input type="hidden" value="deleteImage" name="task"/><input type="hidden" value="true" name="ajax"/><input type="hidden" value="'+filePath+'" name="fileDel"/><input type="button" class="btnCatalogFormDel" onclick="loadImage();" value="Видалити"/></form>');
                }else{
                    $.fancybox(err);
                }
              });
            }
        </script>
       <?
   }
   
   function EditProfile()   
    {
     
     $SysGroup = new SysUser();
    // echo 'this->login = '.$this->login ;
     //echo '<br/>this->Logon->login = '.$this->Logon->login ;
     $q="SELECT * FROM `".TblModUser."`,`".TblSysUser."` WHERE `".TblModUser."`.`sys_user_id`=".$this->Logon->user_id." AND `".TblSysUser."`.id=".$this->Logon->user_id."";
     $res = $this->db->db_Query($q);
     //echo '<br>'.$q.' $res='.$res.' $this->db->result='.$this->db->result;
     if ( !$res OR !$this->db->result ) return false;
     $mas = $this->db->db_FetchAssoc();

     ?>
        <div>
       <div id="catalogBox">
            <span class="MainHeaderText">Редагування профілю</span>
            
            <div id="profileMenuHandler">
                <div id="leftProfileMenuPart">
                    <? if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->Logon->user_id."/".$mas['discount'])){?>
                    <br/>
                    <img class="avatarImage profileAvatar" src="<?=$this->Catalog->ShowCurrentImageExSize("/images/mod_blog/".$this->Logon->user_id."/".$mas['discount'], 70, 70, 'center', 'center', '85', NULL, NULL, NULL, true);?>" alt="" />
                           <?}else{?>
                      <br/><img class="avatarImage profileAvatar" width="70" height="70" src="/images/design/noAvatar.gif"/>     
                           <?}?>
                           <?if(empty($mas['name'])){?>
                    <span class="profileName"><?=$mas['login']?></span>
                    <?}else{?>
                    <span class="profileName"><?=$mas['name']." ".$mas['country']?></span>
                    <?}?>
                </div>
                <div id="centerProfileMenuPart">
                    <a class="blogBtnUserProfile" href="/myaccount/blog/">Блог</a>
                    <a class="editProfile selectedPunktClass" href="/myaccount/">Редагувати Профіль</a>
                    <a class="commentsProfile" href="/myaccount/comments/">Коментарі</a>
                </div>
                <div id="rightProfileMenuPart"></div>
            </div>
            
            
            <div id="catalogBody" style="background: #fafafa">
                <div id="CatformAjaxLoader"></div>
        <div class="registerBoxDiv">
            <?
            $this->Form->WriteFrontHeader('profile', '#', 'update');
     //$this->Form->Hidden( 'user_id', $mas['sys_user_id'] );
     $this->Form->Hidden( 'user_status', $mas['user_status'] );
     $this->Form->Hidden( 'email', $mas['email'] );
     ?>
           <input type="hidden" id="UserImageFilePath" value="" name="userImage"/>
                <ul class="CatFormUl">
                  <li>Прізвище:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="country" value="<?=$mas['country']?>"/>
                  </li>
                  <li>Ім'я:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="name" value="<?=$mas['name']?>"/>
                  </li>
                  <li>
                      Стать:<br/>
                      <select class="CatSelectFromForm" name="state">
                          <option <?if($mas['state']=='m') echo "selected";?>  value="m">Чоловіча</option>
                          <option <?if($mas['state']=='w') echo "selected";?> value="w">Жіноча</option>
                      </select>
                  </li>
                  <li>
                      День:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,1)"  type="text" class="dataInput" name="day" value="<?=$mas['city'][5].$mas['city'][6]?>"/>
                      Місяць:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,2)" type="text" class="dataInput" name="month" value="<?=$mas['city'][8].$mas['city'][9]?>"/>
                      Рік:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,3)" type="text" class="dataInput" style="width: 30px;" name="year" value="<?=$mas['city'][0].$mas['city'][1].$mas['city'][2].$mas['city'][3]?>"/>
                       <div id="dateChek" class="redStar"></div>
                  </li>
<!--                  <li>
                      Email:<span class="redStar"> *</span><br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="email" value="<?=$mas['email']?>" onchange="chekFields('email',this.value,'resultofChek2')" onblur="chekFields('email',this.value,'resultofChek2')"/>
                      <div id="resultofChek2"></div>
                  </li>-->
                  <li>Сайт:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="www" value="<?=$mas['www']?>"/>
                  </li>
                  <li>Телефон:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="phone" value="<?=$mas['phone']?>"/>
                  </li>
                  <li>Facebook:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="phone_mob" value="<?=$mas['phone_mob']?>"/>
                  </li>
                  <li>Вконтакті:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="fax" value="<?=$mas['fax']?>"/>
                  </li>
                  <li>Twitter:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="bonuses" value="<?=$mas['bonuses']?>"/>
                  </li>
                </ul>
            
           <span style="font-weight: bold;"> Коротко про себе:<br/></span>
                       <textarea id="aboutMe" name="aboutMe" class="aboutMeText tinyProfile"><?=$mas['aboutMe']?></textarea>
         <?$this->Form->WriteFrontFooter(); ?>
           
         <br/><input type="button" style="float: right" class="btnCatalogImgUpload" onclick="verify()" name="save_reg_data" class="submitBtn<?=_LANG_ID?>" value="Зберегти" />
        </div>
                 <div style="float: left;display: block;margin-left: 35px;">
                 <ul class="CatFormUl" id="imgLoaderConteiner">
                            <li id="imgLoaderConteiner" style="height: auto;">
                                <div id="catImgAjaxLoader"></div>
                        <div id="CatImageUploadBox">
                           <? if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->Logon->user_id."/".$mas['discount'])){?>
                            Аватар:<br/><img class="avatarImage" width="120" height="120" src="<?="/images/mod_blog/".$this->Logon->user_id."/".$mas['discount']?>"/><form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe"><input type="hidden" value="deleteImage" name="task"/><input type="hidden" value="true" name="ajax"/><input type="hidden" value="/images/mod_blog/<?=$this->user_id."/".$mas['discount']?>" name="fileDel"/><input type="button" class="btnCatalogFormDel" onclick="loadImage();" value="Видалити"/></form>
                           <?}else{?>
                            <form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe">
                                         <input type="hidden" value="addImage" name="task"/>
                                         <input type="hidden" value="true" name="ajax"/>
                                      Виберіть зображення для аватари:<br/>
                                      <input id="catUserFileUploader" type="file" name="image" size="15"/>
                                      <input class="btnCatalogImgUpload" type="button" onclick="loadImage();" value="Завантажити"/>
                            </form>
                           <?}?>
                        </div>
                            <iframe id="hiddenframe" name="hiddenframe" style="width:0px; height:0px; border:0px"></iframe>
                            </li>
                            <li>
                              
                            </li>
                        </ul>
           </div>
        </div>
        </div>
                  </div>
       <?
    } //end of function EditProfile()

    
    
    // ================================================================================================
    // Function : ShowCommentsBlock
    // Date : 22.02.2001
    // Returns : true,false / Void
    // Description : Show the form for editig data of profile of the user on front-end.
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function ShowCommentsBlock()   
    {
     $SysGroup = new SysUser();
    // echo 'this->login = '.$this->login ;
     //echo '<br/>this->Logon->login = '.$this->Logon->login ;
     $q="SELECT * FROM `".TblModUser."`,`".TblSysUser."` WHERE `".TblModUser."`.`sys_user_id`=".$this->Logon->user_id." AND `".TblSysUser."`.id=".$this->Logon->user_id."";
     $res = $this->db->db_Query($q);
     //echo '<br>'.$q.' $res='.$res.' $this->db->result='.$this->db->result;
     if ( !$res OR !$this->db->result ) return false;
     $mas = $this->db->db_FetchAssoc();

     ?>
        <div>
       <div id="catalogBox">
            <span class="MainHeaderText">Редагування профілю</span>
            
            <div id="profileMenuHandler">
                <div id="leftProfileMenuPart">
                    <? if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->Logon->user_id."/".$mas['discount'])){?>
                    <br/>
                    <img class="avatarImage profileAvatar" src="<?=$this->Catalog->ShowCurrentImageExSize("/images/mod_blog/".$this->Logon->user_id."/".$mas['discount'], 70, 70, 'center', 'center', '85', NULL, NULL, NULL, true);?>" alt="" />
                           <?}else{?>
                      <br/><img class="avatarImage profileAvatar" width="70" height="70" src="/images/design/noAvatar.gif"/>     
                           <?}?>
                           <?if(empty($mas['name'])){?>
                    <span class="profileName"><?=$mas['login']?></span>
                    <?}else{?>
                    <span class="profileName"><?=$mas['name']." ".$mas['country']?></span>
                    <?}?>
                </div>
                <div id="centerProfileMenuPart">
                    <a class="blogBtnUserProfile" href="/myaccount/blog/">Блог</a>
                    <a class="editProfile" href="/myaccount/">Редагувати Профіль</a>
                    <a class="commentsProfile selectedPunktClass" href="#">Коментарі</a>
                </div>
                <div id="rightProfileMenuPart"></div>
            </div>
           
            <div id="catalogBody" style="background: #fafafa">
            <?
           if(!isset($this->Comments))
                $this->Comments = new FrontComments();
           $this->Comments->GetUserCommentsTree(10,$this->Logon->user_id);
           ?>
                <?/*<div id="CatformAjaxLoader"></div>
                <div class="registerBoxDiv">
            <?
            $this->Form->WriteFrontHeader('profile', '#', 'update');
     //$this->Form->Hidden( 'user_id', $mas['sys_user_id'] );
     $this->Form->Hidden( 'user_status', $mas['user_status'] );
     $this->Form->Hidden( 'email', $mas['email'] );
     ?>
           <input type="hidden" id="UserImageFilePath" value="" name="userImage"/>
                <ul class="CatFormUl">
                  <li>Прізвище:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="country" value="<?=$mas['country']?>"/>
                  </li>
                  <li>Ім'я:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="name" value="<?=$mas['name']?>"/>
                  </li>
                  <li>
                      Стать:<br/>
                      <select class="CatSelectFromForm" name="state">
                          <option <?if($mas['state']=='m') echo "selected";?>  value="m">Чоловіча</option>
                          <option <?if($mas['state']=='w') echo "selected";?> value="w">Жіноча</option>
                      </select>
                  </li>
                  <li>
                      День:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,1)"  type="text" class="dataInput" name="day" value="<?=$mas['city'][5].$mas['city'][6]?>"/>
                      Місяць:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,2)" type="text" class="dataInput" name="month" value="<?=$mas['city'][8].$mas['city'][9]?>"/>
                      Рік:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,3)" type="text" class="dataInput" style="width: 30px;" name="year" value="<?=$mas['city'][0].$mas['city'][1].$mas['city'][2].$mas['city'][3]?>"/>
                       <div id="dateChek" class="redStar"></div>
                  </li>
<!--                  <li>
                      Email:<span class="redStar"> *</span><br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="email" value="<?=$mas['email']?>" onchange="chekFields('email',this.value,'resultofChek2')" onblur="chekFields('email',this.value,'resultofChek2')"/>
                      <div id="resultofChek2"></div>
                  </li>-->
                  <li>Сайт:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="www" value="<?=$mas['www']?>"/>
                  </li>
                  <li>Телефон:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="phone" value="<?=$mas['phone']?>"/>
                  </li>
                  <li>Facebook:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="phone_mob" value="<?=$mas['phone_mob']?>"/>
                  </li>
                  <li>Вконтакті:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="fax" value="<?=$mas['fax']?>"/>
                  </li>
                  <li>Twitter:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="bonuses" value="<?=$mas['bonuses']?>"/>
                  </li>
                </ul>
            <input type="hidden" name="user_status" value="3"/>
           <span style="font-weight: bold;"> Коротко про себе:<br/></span>
                       <textarea id="aboutMe" name="aboutMe" class="aboutMeText tinyProfile"><?=$mas['aboutMe']?></textarea>
         <?$this->Form->WriteFrontFooter(); ?>
           
         <br/><input type="button" style="float: right" class="btnCatalogImgUpload" onclick="verify()" name="save_reg_data" class="submitBtn<?=_LANG_ID?>" value="Зберегти" />
        </div>
                 <div style="float: left;display: block;margin-left: 35px;">
                 <ul class="CatFormUl" id="imgLoaderConteiner">
                            <li id="imgLoaderConteiner" style="height: auto;">
                                <div id="catImgAjaxLoader"></div>
                        <div id="CatImageUploadBox">
                           <? if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->Logon->user_id."/".$mas['discount'])){?>
                            Аватар:<br/><img class="avatarImage" width="120" height="120" src="<?="/images/mod_blog/".$this->Logon->user_id."/".$mas['discount']?>"/><form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe"><input type="hidden" value="deleteImage" name="task"/><input type="hidden" value="true" name="ajax"/><input type="hidden" value="/images/mod_blog/<?=$this->user_id."/".$mas['discount']?>" name="fileDel"/><input type="button" class="btnCatalogFormDel" onclick="loadImage();" value="Видалити"/></form>
                           <?}else{?>
                            <form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe">
                                         <input type="hidden" value="addImage" name="task"/>
                                         <input type="hidden" value="true" name="ajax"/>
                                      Виберіть зображення для аватари:<br/>
                                      <input id="catUserFileUploader" type="file" name="image" size="80"/>
                                      <input class="btnCatalogImgUpload" type="button" onclick="loadImage();" value="Завантажити"/>
                            </form>
                           <?}?>
                        </div>
                            <iframe id="hiddenframe" name="hiddenframe" style="width:0px; height:0px; border:0px"></iframe>
                            </li>
                            <li>
                              
                            </li>
                        </ul>
           </div> */?>
        </div>
            </div>
        </div>
       <?
    } //end of function EditProfile()
        
    // ================================================================================================
    // Function : ShowChangeEmailPass()
    // Date : 22.02.2001
    // Returns :      true,false / Void
    // Description :  Show form for change password to the new one.
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ShowChangeEmailPass()
    {                  
        $this->Form->WriteFrontHeader( 'ChangeEmailPass', _LINK.'myaccount/changepassword/', 'set_new_email_pass', NULL );
        ?>
        <h1><?=$this->multiUser['TXT_FRONT_EDIT_EMAIL'];?></h1>
         <div class="body">
          <div class="needFields"><?=$this->multiUser['TXT_CHANGE_PASS2'];?></div>
          <div align="center"><?=$this->ShowErr()?></div>
          <table border="0" cellpadding="0" cellspacing="2" class="regTable">           
             <tr>
              <td>
                <?=$this->multiUser['FLD_OLD_PASSWORD'];?>
                <span class="red_point">*</span>
              </td>
              <td><?$this->Form->Password( 'oldpass', stripslashes($this->oldpass), '40' )?></td>
             </tr>
             
             <tr>
              <?if(empty($this->email)) 
                $this->email = $this->Logon->login;?>
              <td>
                <?=$this->multiUser['FLD_NEW_LOGIN'];?>
                <span class="red_point">*</span>
              </td>
              <td><?$this->Form->TextBox( 'email', stripslashes($this->email), 'size="40"' )?></td>
             </tr>
             
             <tr>
              <?if(empty($this->email2)) 
                $this->email2 = $this->Logon->login;?>
              <td>
                <?=$this->multiUser['FLD_CONFIRM_NEW_LOGIN'];?>
                <span class="red_point">*</span>
              </td>
              <td>
                <?$this->Form->TextBox( 'email2', stripslashes($this->email2), 'size="40"' )?>
              </td>
             </tr>
             
             <tr>
              <td>
                <?=$this->multiUser['FLD_NEW_PASSWORD'];?>
                <span class="red_point">*</span>
              </td>
              <td><?$this->Form->Password( 'password', $this->password, 40 )?></td>
             </tr>
             
             <tr>
              <td>
                <?=$this->multiUser['FLD_CONFIRM_PASSWORD'];?>
                <span class="red_point">*</span>
              </td>
              <td>
                <?$this->Form->Password( 'password2', $this->password2, 40 )?>
              </td>
             </tr>
             
          </table>        

          <div class="submit" align="center">
           <?$imgFrontSubmit =  $this->Msg->show_text('IMG_FRONT_SUBMIT', TblSysTxt);?>
           <input type="submit"  class="submitBtn<?=_LANG_ID?>"  value="<?=$imgFrontSubmit?>"/>
           <input type="button" name="cancel" value="<?=$this->Msg->show_text('_BUTTON_CANCEL', TblSysTxt);?>" class="cancelBtn<?=_LANG_ID?>" onClick="javascript:window.location.href='<?=_LINK;?>myaccount/';"/>
          </div>
         </div>
        <?
        $this->Form->WriteFrontFooter();
        return true;
    } //end of function ShowChangeEmailPass()
              
       
    // ================================================================================================
    // Function : ForgotPass()
    // Date : 22.02.2001
    // Returns :      true,false / Void
    // Description :  Show fomr for sending nw passord to the user, who are forgot it.
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ForgotPass()
    {
        $this->Form->WriteFrontHeader( 'forgot_pass', _LINK.'forgotpass.html', 'send_pass', NULL );
        ?>
        <div id="catalogBox">
            <span class="MainHeaderText">Забули пароль?</span>
            <div id="catalogBody" style="background: #fafafa; padding-top: 35px;height: 250px">
               <b><?=$this->multiUser['TXT_FORGOT_PASS2'];?></b>
               <br/>
               <?=$this->ShowErr()?>
               <table border="0" cellspacing="2" cellpadding="0" class="regTable" style="width: 300px;">
                   
                <tr>
                 <td style="height: 100px;"  align="right"><?=$this->multiUser['FLD_EMAIL'];?>:</td>
                 <td style="height: 100px;"><?$this->Form->TextBox( 'email', stripslashes($this->email), '$size=30' )?></td>
                </tr>
                <tr>
                    <td style="width: 250px;" ></td>
                    <td>
                        <div class="submit">
                            <?$imgFrontSubmit = $this->Msg->show_text('IMG_FRONT_SUBMIT', TblSysTxt);?>
                            <input type="submit" value="<?=$imgFrontSubmit;?>"/>
                        </div>
                    </td>
                </tr>
               </table>
               
               
               <?//src="<?=$this->Spr->GetImageByCodOnLang(TblSysTxt, 'IMG_FRONT_SUBMIT', $this->lang_id)?>
           </div>        
        </div>
        <?
        $this->Form->WriteFrontFooter();
        return true;
    } //end of function ForgotPass()
           


    // ================================================================================================
    // Function : ChangeLogin()
    // Date : 22.02.2001
    // Parms :   $old_login  / old login of the user
    //           $new_login  / new login of the user
    // Returns :      true,false
    // Description :  Change login for External user in the table sys_user
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ChangeLogin( $old_login = NULL, $new_login = NULL)
    {
       $q = "UPDATE `".TblSysUser."` set `login`='$new_login' WHERE `login`='$old_login'";
       $res = $this->db->db_Query($q);
       //echo '<br>$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
       if ( !$res OR !$this->db->result) 
            return false;
       
       $q = "UPDATE `".TblModUser."` set `email`='$new_login' WHERE `email`='$old_login'";
       $res = $this->db->db_Query($q);
       if ( !$res OR !$this->db->result) 
            return false;
       
       return true;
    } //end of function ChangeLogin()         

      
    // ================================================================================================
    // Function : ShowErr()
    // Date : 22.02.2011
    // Returns :      void
    // Description :  Show errors
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ShowErr()
    {
        $this->Form->ShowErr($this->Err);
    } //end of function ShowErr()


    // ================================================================================================
    // Function : ShowTextMessages()
    // Date : 22.02.2001
    // Returns :      void
    // Description :  Show text messages
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ShowTextMessages($txt=NULL)
    {
        if( !empty($txt) ) $this->TextMessages = $txt; 
        if ($this->TextMessages){
            $this->Form->ShowTextMessages($this->TextMessages);
        }
    } //end of function ShowTextMessages()

   // ================================================================================================
   // Function : CheckEmailFields()
   // Date : 22.02.2001
   // Returns :      $this->Err
   // Description :  Check fields of email for validation
   // Programmer :  Yaroslav Gyryn
   // ================================================================================================
   function CheckEmailFields( $source=NULL )
   {
     $this->Err=NULL;
     if (empty( $this->email )) 
        $this->Err = $this->Err.$this->multiUser['MSG_FLD_EMAIL_EMPTY'].'<br>';
//     else{
//         if ($source=='forgotpass'){
//             
//         }
//        if ( $this->email!=$this->email2 ) 
//            $this->Err = $this->Err.$this->multiUser['MSG_NOT_MATCH_REENTER_EMAIL'].'<br>';
//        /*if (!ereg("^[a-zA-Z0-9_.\-]+@[a-zA-Z0-9.\-].[a-zA-Z0-9.\-]+$", $this->email)) 
//            $this->Err = $this->Err.$this->Msg->show_text('MSG_NOT_VALID_EMAIL').'<br>';*/
//        if ($source=='forgotpass') return $this->Err;
//        
//        if ( $this->email!=$this->Logon->login AND !$this->unique_login($this->email) ) {
//           //$this->Err=$this->Err.$this->Msg->show_text('MSG_NOT_UNIQUE_LOGIN_1')." ".stripslashes($this->email)." ".$this->Msg->show_text('MSG_NOT_UNIQUE_LOGIN_2').'<br>';
//           $this->Err=$this->Err.$this->multiUser['MSG_NOT_UNIQUE_LOGIN'].'<br>';        
//        }            
//     }         
     return $this->Err; 
   } //end of function CheckEmailFields()    

   
   // ================================================================================================
   // Function : ChangePass()
   // Date : 22.02.2001
   // Returns :      true,false / Void
   // Description :  Show form for change password to the new one.
   // Programmer :  Yaroslav Gyryn
   // ================================================================================================
   /*function ChangePass()
   {
    ?>
    <div align=center><h1>Изменение пароля</h1></div>
   <form action="<?=$_SERVER['PHP_SELF']?>" method=post> 
      <table border=0 cellspacing=1 cellpadding=3>
       <tr><td colspan=2 align=center><H3><?=$this->Msg->show_text('TXT_CHANGE_PASS2');?></H3>
       <tr><td colspan=2 align=center class="UserErr"><?=$this->ShowErr()?>
       <tr><td>
       <tr>
        <td><?=$this->Msg->show_text('FLD_OLD_PASSWORD');?>:
        <td><?$this->Form->Password( 'oldpass', stripslashes($this->oldpass), $size=30 )?>
       <tr>
        <td><?=$this->Msg->show_text('FLD_NEW_PASSWORD');?>:
        <td><?$this->Form->Password( 'password', stripslashes($this->password), $size=30 )?>
       <tr>
        <td><?=$this->Msg->show_text('FLD_CONFIRM_PASSWORD');?>:
        <td><?$this->Form->Password( 'password2', stripslashes($this->password2), $size=30 )?>
       <tr>
        <td colspan=2 align=center>
         <INPUT TYPE="image" src="images/design/button_save.gif">
         <input type=hidden name=set_new_pass value=set_new_pass>
       <tr><td colspan=2 align=center>
      </table>
   </form> 
    <?
    return true;
   } //end of function ChangePass()       */
    
 } //end of class UserShow
?>