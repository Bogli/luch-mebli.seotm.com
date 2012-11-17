    <div class="commetns-count-panel">
        <?=$commentsCount?> комментариев

        <?=FormH::button('makeComment','Комментировать',array('class'=>'grey-btn gradient show-comments-form-btn btn','type'=>'button','id'=>'showCommentsForm')) ?>
    </div>

    <div id='commentsTreeBoxId' class="comments-tree-box">
        <?=$commentsByPages?>
    </div>
    <div class="comments-add-footer">
    <?
        if($commentsCount>5) echo FormH::button('makeComment','Комментировать',array('class'=>'grey-btn gradient show-comments-form-btn btn','type'=>'button','id'=>'showCommentsFormBottom')) ?>
    </div>
