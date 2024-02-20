<?php $notifications = WAMS()->web_notifications()->get_notifications();  ?>
<?php
$unread_count = WAMS()->web_notifications()->unread_count() ?? 0;
$new_notifications_formatted = (absint($unread_count) > 9) ? __('9+', 'wams') : absint($unread_count);
?>
<div class="dropdown d-inline-block">
    <button type="button" class="btn header-item noti-icon position-relative" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i data-feather="bell" class="icon-lg"></i>
        <?php if ($unread_count > 0) : ?>
            <span class="badge bg-danger rounded-pill notification-live-count"><?php echo $new_notifications_formatted; ?></span>
        <?php endif; ?> </button>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown">
        <div class="p-3">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="m-0"> Notifications </h6>
                </div>
                <div class="col-auto">
                    <a href="/notifications" class="small text-reset text-decoration-underline"> <?php echo __('Unread:', 'wams'); ?><span id="unread-notifications-count"><?php echo $unread_count; ?></span></a>
                </div>
            </div>
        </div>
        <?php WAMS()->get_template('notifications/notifications-box.php', '', ['notifications' => $notifications], true); ?>

        <div class="p-2 border-top d-grid">
            <a class="btn btn-sm btn-link font-size-14 text-center" href="/notifications">
                <i class="mdi mdi-arrow-right-circle me-1"></i> <span><?php echo __('Show All', 'wams'); ?></span>
            </a>
        </div>
    </div>
</div>