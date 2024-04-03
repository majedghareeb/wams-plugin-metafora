<div class="row">
    <?php if ($timeline) : ?>
        <?php foreach ($timeline as $step) : ?>
            <?php
            $icon = '';
            // $step_icon = $step ? $step->get_icon_url() : gravity_flow()->get_base_url() . '/images/gravityflow-icon-blue.svg';
            switch ($step->user_id) {
                case '0':
                    $icon = WAMS_URL . 'assets/images/icons/workflow_diagram.png';
                    break;
                default:
                    $icon = um_get_default_avatar_uri($step->user_id);
                    break;
            }
            ?>
            <div class="card">
                <div class="card-body">
                    <div class="float-start ms-2">
                        <h5 class="font-size-15 mb-1"><?php echo $step->value; ?></h5>
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary font-size-12" id="task-status"><?php echo $step->date_created; ?></span>
                    </div>
                    <div class="float-end d-flex align-items-center">
                        <div class="flex-1 me-3">
                            <h5 class="font-size-15 mb-1"><?php echo $step->user_name; ?></h5>
                        </div>
                        <div>
                            <img src="<?php echo $icon; ?>" alt="" class="avatar-lg rounded-circle img-thumbnail">
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <!-- end card -->
</div>