<?php
$chatBasePath = $basePath ?? '';
?>
<div id="chatbot-widget" class="chatbot-widget">
    <button type="button" id="chatbot-toggle" class="chatbot-toggle" aria-label="<?php echo __('chatbot_open_label'); ?>">
        <i class="fas fa-robot"></i>
        <span class="chatbot-badge">AI</span>
    </button>

    <div id="chatbot-panel" class="chatbot-panel" aria-hidden="true">
        <div class="chatbot-header">
            <div class="chatbot-header-info">
                <i class="fas fa-robot"></i>
                <div>
                    <strong><?php echo __('chatbot_title'); ?></strong>
                    <small><?php echo __('chatbot_subtitle'); ?></small>
                </div>
            </div>
            <button type="button" id="chatbot-close" class="chatbot-close" aria-label="<?php echo __('chatbot_close'); ?>">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="chatbot-messages" class="chatbot-messages">
            <div class="chat-msg bot">
                <div class="chat-bubble">
                    <?php echo __('chatbot_greeting'); ?>
                </div>
            </div>
        </div>

        <div class="chatbot-quick">
            <button type="button" data-msg="<?php echo __('chatbot_quick_newbie'); ?>"><?php echo __('chatbot_new_user'); ?></button>
            <button type="button" data-msg="<?php echo __('chatbot_quick_grade_info'); ?>"><?php echo __('chatbot_grade_info'); ?></button>
            <button type="button" data-msg="<?php echo __('chatbot_quick_shipping'); ?>"><?php echo __('chatbot_shipping'); ?></button>
        </div>

        <form id="chatbot-form" class="chatbot-form">
            <input type="text" id="chatbot-input" placeholder="<?php echo __('chatbot_input_placeholder'); ?>" maxlength="500" autocomplete="off">
            <button type="submit" id="chatbot-send" aria-label="<?php echo __('chatbot_send'); ?>">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>
