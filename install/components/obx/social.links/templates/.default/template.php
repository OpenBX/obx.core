<? if (count($arResult) > 0): ?>
    <div class="social-links">
        <? foreach ($arResult as $id => $link): ?>
            <a class="<?=strtolower($id)?>_ico" href="<?= $link ?>"></a>
        <?endforeach;?>
    </div>
<?endif;