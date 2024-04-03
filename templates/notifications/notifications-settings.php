<?php //print_r($output_arrays); 
$user_settings = get_user_meta(get_current_user_id(), 'notification-settings', true);
print_r($user_settings);
// $prefs = get_user_meta($user_id, 'notification-settings', true);
$telegram_chat_id =   get_user_meta(get_current_user_id(), 'telegram_chat_id', true);
?>
<p>
    <a class="btn btn-primary" data-bs-toggle="collapse" href="#notifications_settings" aria-expanded="false" aria-controls="notifications_settings">
        <?php echo __('Change Settings', 'wams'); ?>
    </a>
</p>
<div class="collapse" id="notifications_settings">
    <div class="bg-white">
        <form id="notification-settings">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0"><?php echo __('Telegram Notifications', 'wams'); ?></h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="telegram-chat-id" class="form-label font-size-13 text-muted"><?php echo __('Telegram Chat ID', 'wams'); ?></label>
                                <input class="form-control" name="telegram-chat-id" id="telegram-chat-id" type="number" value="<?php echo $telegram_chat_id; ?>" placeholder="Enter something" />
                            </div>
                            <div class="form-check form-switch-md">
                                <input <?php echo (isset($user_settings['telegram']['notification-enabled'])  && $user_settings['telegram']['notification-enabled'] == 'on') ? 'checked' : ''; ?> name="telegram[notification-enabled]" type="checkbox" class="form-check-input" id="telegram[notification-enabled]">
                                <label class="form-check-label" for="telegram[notification-enabled"><?php echo __('Enable Telegram Notifications', 'wams'); ?></label>
                            </div>
                            <div class="form-check form-switch form-switch-md">
                                <input <?php echo (isset($user_settings['telegram']['worflow-update'])  && $user_settings['telegram']['worflow-update'] == 'on') ? 'checked' : ''; ?> name="telegram[worflow-update]" type="checkbox" class="form-check-input" id="telegram[worflow-update]">
                                <label class="form-check-label" for="telegram[worflow-update]"><?php echo __('When Workflow Get Updated', 'wams'); ?> </label>
                            </div>
                            <div class="form-check form-switch form-switch-md">
                                <input <?php echo (isset($user_settings['telegram']['new-task'])  && $user_settings['telegram']['new-task'] == 'on') ? 'checked' : ''; ?> name="telegram[new-task]" type="checkbox" class="form-check-input" id="telegram[new-task]">
                                <label class="form-check-label" for="telegram[new-task]"><?php echo __('When I Get New Task', 'wams'); ?></label>
                            </div>
                        </div>
                        <div class="card-footer p-2">
                            <button type="submit" class="btn btn-primary w-md"><?php echo __('Save', 'wams'); ?></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0"><?php echo __('Web Notifications', 'wams'); ?></h4>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch-md">
                                <input <?php echo (isset($user_settings['web']['notification-enabled'])  && $user_settings['web']['notification-enabled'] == 'on') ? 'checked' : ''; ?> name="web[notification-enabled]" type="checkbox" class="form-check-input" id="web[notification-enabled]">
                                <label class="form-check-label" for="web[notification-enabled]"><?php echo __('Enable Web Notifications', 'wams'); ?></label>
                            </div>
                            <div class="form-check form-switch form-switch-md">
                                <input <?php echo (isset($user_settings['web']['worflow-update'])  && $user_settings['web']['worflow-update'] == 'on') ? 'checked' : ''; ?> name="web[worflow-update]" type="checkbox" class="form-check-input" id="web[worflow-update]">
                                <label class="form-check-label" for="web[worflow-update]"><?php echo __('When Workflow Get Updated', 'wams'); ?> </label>
                            </div>
                            <div class="form-check form-switch form-switch-md">
                                <input <?php echo (isset($user_settings['web']['new-task'])  && $user_settings['web']['new-task'] == 'on') ? 'checked' : ''; ?> name="web[new-task]" type="checkbox" class="form-check-input" id="web[new-task]">
                                <label class="form-check-label" for="web[new-task]"><?php echo __('When I Get New Task', 'wams'); ?></label>
                            </div>
                        </div>
                        <div class="card-footer p-2">
                            <button type="submit" class="btn btn-primary w-md"><?php echo __('Save', 'wams'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>