<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Workflow Details</h4>
            </div>
            <div class="card-body">
                <div id="workflow-wizard" class="twitter-bs-wizard">
                    <ul class="twitter-bs-wizard-nav nav nav-pills nav-justified">
                        <?php foreach ($steps as $step) :
                            if ($step['is_active'] != '1') continue;
                            $step_status = rgar($entry, 'workflow_step_status_' . $step['id'], __('Not Started', 'wams'));

                            $active = (rgar($entry, 'workflow_step', '') == $step['id']) ? 'active' : '';
                            switch ($step_status) {
                                case 'complete':
                                case 'approved':
                                    $icon  = '<i class="far fa-check-circle fa-lg" style="color: green;"></i>';
                                    break;
                                case 'pending':
                                    $icon  = '<i class="far fa-clock fa-lg" style="color: orange;"></i>';
                                    break;
                                case 'rejected':
                                    $icon  = '<i class="far fa-times-circle fa-lg" style="color: red;"></i>';
                                    break;
                                default:
                                    $icon  = '<i class="far fa-question-circle fa-lg" style="color: gray;"></i>';
                                    break;
                            }
                            // $icon  = ($step['icon'] != '' && !strpos($step['icon'], 'fa-pencil'))  ? $step['icon'] : '<i class="far fa-cogs"></i>';
                        ?>
                            <li class="nav-item ">
                                <div class="nav-link <?php echo $active; ?>" data-toggle="tab">
                                    <div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo $step['label']; ?>">
                                        <?php echo $icon; ?>
                                    </div>
                                </div>
                                <div><span><?php echo $step['name']; ?></span></div>
                                <div><span><?php echo $step_status; ?></span></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <!-- end card body -->
        </div>
        <!-- end card -->
    </div>
    <!-- end col -->
</div>