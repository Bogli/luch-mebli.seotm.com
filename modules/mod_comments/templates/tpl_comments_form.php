<div class="comments-popup-form-body gradient">
    <div class='popup-title'>
        <?=$popup_title?>
    </div>
    <? echo FormH::open('#', array('id' => 'commentsMakeFormId')); ?>
    <? if (!empty($level) AND !empty($level)):
        echo FormH::hidden('level', $level);
    ?>
        <div class="comments-response-comment-box">
            <img src='<?=$commentsData['avatar']?>' alt='<?=$commentsData['show_name']?>'
                 title="<?=$commentsData['show_name']?>"/>

            <div class="comments-respons-comment-inner">
                <span class="comments-name"><?=$commentsData['show_name']?></span><br/>
                <?=$commentsData['text']?>
            </div>
        </div>
    <? endif; ?>
    <ul class='comments-inner-box'>
        <? if (empty($user_id)): ?>
        <li>
            <? echo FormH::label('commentsLoginId', 'E-mail:', array('class' => 'comments-label')) ?><br/>
            <? echo FormH::input('login', '', array('class' => 'comments-input', 'id' => 'commentsLoginId')) ?>
        </li>
        <li>
            <? echo FormH::label('commentsPassId', 'Пароль:', array('class' => 'comments-label')) ?><br/>
            <? echo FormH::password('password', '', array('class' => 'comments-input', 'id' => 'commentsPassId')) ?>
        </li>
        <? endif; ?>
        <li>
            <? echo FormH::label('commentsTextId', $textLabel.':', array('class' => 'comments-label')) ?><br/>
            <? echo FormH::textarea('text', '', array('class' => 'comments-textarea', 'id' => 'commentsTextId')) ?>
        </li>
        <li class='buttons-box'>
            <?
            echo FormH::button('submit', 'Отправить', array('class' => 'comments-submit-btn btn gradient', 'id' => 'commentsSubmitBtnId', 'type' => 'button'));
            echo FormH::button('cancel', 'Отмена', array('class' => 'comments-cancel-btn btn gradient', 'id' => 'commentsCancelBtnId', 'type' => 'button'));
            ?>
        </li>
    </ul>
    <? echo FormH::close(); ?>
</div>

<script type="text/javascript ">
    $("#commentsMakeFormId").validationEngine();
    $("#commentsCancelBtnId").click(function () {
        cmspopup.close();
    });
    $("#commentsSubmitBtnId").click(function () {
        $("#commentsMakeFormId").ajaxSubmit({
            url:'/comments/add_comment/?module=<?=$module?>&id_item=<?=$id_item?>&page=<?=$page?>',
            success:function (responseText) {
                if (ajaxResponse(responseText))
                    cmspopup.close();
            }
        });
    });
</script>