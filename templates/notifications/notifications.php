<?php

/**
 * Template for the Notifications
 *
 *
 * Call: function show_notifications()
 *
 * @version 1.0.0
 *
 * @var array  $notifications
 */


?>

<?php $notifications = WAMS()->web_notifications()->get_notifications(100); ?>
<?php $unread_count = WAMS()->web_notifications()->unread_count() ?? 0;  ?>

<div id="ajax-notifications-list">
    <?php //WAMS()->web_notifications()->show_notification_box();  
    ?>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="card-title"><?php echo __('Notifications List', 'wams'); ?><span class="text-muted fw-normal ms-2">(<?php echo !empty($notifications) ? count($notifications) : '0'; ?>)</span></h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                            <div>
                                <button data-action-type="read" class="multi-action-button btn btn-light"><i class="bx bx-check-double"></i><?php echo __('Read', 'wams'); ?></button>
                                <button data-action-type="unread" class="multi-action-button btn btn-light"><i class="bx bx-check-double"></i><?php echo __('Unread', 'wams'); ?></button>
                                <button data-action-type="delete" class="multi-action-button btn btn-light"><i class="bx bxs-minus-square"></i><?php echo __('Delete', 'wams'); ?></button>
                            </div>
                        </div>

                    </div>
                    <!-- end row -->
                    <div class="table-responsive mb-4">
                        <table id="notifications-table" class="table align-middle datatable dt-responsive table-check nowrap" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 50px;">
                                        <div class="form-check font-size-16">
                                            <input type="checkbox" class="form-check-input table-checkbox" id="checkAll">
                                            <label class="form-check-label" for="checkAll"></label>
                                        </div>
                                    </th>
                                    <th scope="col"></th>
                                    <th scope="col"><?php echo __('Type', 'wams'); ?></th>
                                    <th scope="col"><?php echo __('Message', 'wams'); ?></th>
                                    <th scope="col"><?php echo __('Time', 'wams'); ?></th>
                                    <th scope="col"><?php echo __('Status', 'wams'); ?></th>
                                    <th style="width: 80px; min-width: 80px;"><?php echo __('Action', 'wams'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($notifications)) : ?>
                                    <?php foreach ($notifications as $notification) : ?>

                                        <tr>
                                            <td scope="row">
                                                <div class="form-check font-size-16">
                                                    <input type="checkbox" class="form-check-input table-checkbox" id="<?php echo $notification->id; ?>">
                                                    <label class="form-check-label" for="notification_item_<?php echo $notification->id; ?>"></label>
                                                </div>
                                            </td>
                                            <td> <img src="<?php echo $notification->photo; ?>" alt="" class="avatar-sm rounded-circle me-2">
                                            </td>
                                            <td>
                                                <a href="<?php echo $notification->url; ?>" class="text-body"><?php echo $notification->type ?></a>
                                            </td>
                                            <td><?php echo $notification->content ?></td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <p><?php echo wams_nice_time($notification->time); ?></p>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="status d-flex gap-2">
                                                    <p><?php echo ($notification->status); ?></p>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-link font-size-16 shadow-none py-0 text-muted dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bx bx-dots-horizontal-rounded"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><button class="action-button dropdown-item" data-row-id="<?php echo $notification->id; ?>" data-action-type="read" href="#"><?php echo __('Mark as read', 'wams'); ?></button></li>
                                                        <li><button class="action-button dropdown-item" data-row-id="<?php echo $notification->id; ?>" data-action-type="unread"><?php echo __('Mark as unread', 'wams'); ?></button></li>
                                                        <li><button class="action-button dropdown-item" data-row-id="<?php echo $notification->id; ?>" data-action-type="delete"><?php echo __('Delete', 'wams'); ?></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="table-responsive mb-4">
                                        <tr>
                                            <td colspan="6">
                                                <h2 class="text-center"><?php echo __('There is no notifications', 'wams'); ?></h2>
                                            </td>
                                        </tr>

                                    </div>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <!-- end table -->
                    </div>
                    <!-- end table responsive -->
                </div>
            </div>
        </div>
    </div>
</div>