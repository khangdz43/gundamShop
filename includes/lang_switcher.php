<?php $curLang = currentLang(); ?>
<div class="lang-toggle" role="group" aria-label="<?php echo __('lang_vi'); ?> / <?php echo __('lang_en'); ?>">
    <button type="button" class="lang-toggle-btn<?php echo $curLang === 'vi' ? ' is-active' : ''; ?>" data-lang="vi" aria-pressed="<?php echo $curLang === 'vi' ? 'true' : 'false'; ?>">VI</button>
    <button type="button" class="lang-toggle-btn<?php echo $curLang === 'en' ? ' is-active' : ''; ?>" data-lang="en" aria-pressed="<?php echo $curLang === 'en' ? 'true' : 'false'; ?>">EN</button>
</div>
