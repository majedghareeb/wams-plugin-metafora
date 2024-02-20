<div id="notifications-list-box" data-simplebar style="max-height: 230px;">
    <?php if ($notifications) : ?>
        <?php foreach ($notifications as $notification) : ?>

            <a href="<?php echo $notification->url; ?>" class="text-reset notification-item" data-id="<?php echo $notification->id; ?>">
                <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                        <img src="<?php echo  esc_url($notification->photo) ?>" class="rounded-circle avatar-sm" alt="user-pic">
                    </div>
                    <div class="flex-grow-1">
                        <div class="float-end"><?php echo ($notification->status == 'read') ? '<i class="far fa-envelope-open"></i>' : '<i class="far fa-envelope"></i>'; ?>
                        </div>
                        <h6 class="mb-1 <?php echo ($notification->status == 'read') ? 'text-muted' : ''; ?>"><?php echo $notification->type ?></h6>
                        <div class="font-size-13 text-muted">
                            <p class="mb-1"><?php echo $notification->content ?></p>
                            <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span><?php echo nice_time($notification->time); ?></span></p>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>