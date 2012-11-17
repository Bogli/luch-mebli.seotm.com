<? for ($i = $start; $i < $rows; $i++) :
    $row = $arr[$i];
    $margin = $row['level_show'] * 50;
    if ($row['level_show'] > 4)
        $margin = 4 * 50;
    ?>
<div class="single-comment">
    <div class="single-comment-inner" style="margin-left: <?=$margin?>px">
        <img src="<?=$row['avatar']?>" alt="<?=$row['show_name']?>" title="<?=$row['show_name']?>"/>

        <div class="comment-content">
            <div class="comments-arrow"></div>
            <div class="comments-data-name-box">
                <span class="comments-name"><?=$row['show_name']?></span>
                <span class="comments-data"><?=$row['dt']?></span>
            </div>
            <?=$row['text']?>
            <div class="comments-response-box">
                <span class="comments-time-past"><?=$row['date_past'] . ' назад'?> |</span>
                <a class='comments-response-link' href="/comments/get_form/?level=<?=$row['id']?>&page=<?=$page?>"
                   title="Ответить">Ответить</a>
            </div>
        </div>
    </div>
</div>
<? endfor; ?>
<div id="paginationBoxId" class="pagination-box">
    <?=$pagination?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        initResponseLink();
        initPaginationLink();
    });
</script>