<?php

/**
 * Template for the Home
 *
 *
 * Call: function wams_home()
 *
 * @version 1.0.0
 *
 * @var array $user_tasks
 * @var array  $user_team_tasks
 * @var array  $user_requests
 */
if (!defined('ABSPATH')) {
    exit;
}
$blog_url = get_bloginfo('url');
?>
<div class="container">
    <div class="row g-4">

        <div class=" col-lg-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h4 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo __('Tasks that assigned to me only', 'wams'); ?>">
                            <?php echo __('My Latest Tasks', 'wams'); ?></h4>
                        <button data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo __('Refresh', 'wams'); ?>" id="tasks-refresh" class="btn"><i class="fas fa-sync"></i></button>
                    </div>

                    <hr>
                    <?php if (!empty($my_tasks)) : ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?php echo __('Entry ID', 'wams'); ?></th>
                                    <th><?php echo __('Step', 'wams'); ?></th>
                                    <th><?php echo __('Form', 'wams'); ?></th>
                                    <th class="d-none d-xl-table-cell"><?php echo __('Created By', 'wams'); ?></th>
                                    <th class="d-none d-xl-table-cell"><?php echo __('Date Created', 'wams'); ?></th>
                                    <th class="d-none d-xl-table-cell"><?php echo __('Date Updated', 'wams'); ?></th>
                                </tr>
                            </thead>
                            <?php foreach ($my_tasks as $entry_id => $task) : ?>
                                <tr>
                                    <td class="tdst-group-item"><a href="<?php echo $blog_url; ?>/inbox/?page=gravityflow-inbox&view=entry&id=<?php echo @$task['form_id']; ?>&lid=<?php echo @$entry_id; ?>"><?php echo @$entry_id; ?></a>
                                    </td>
                                    <td class="tdst-group-item"><?php echo @$task['step_name']; ?></td>
                                    <td class="tdst-group-item"><?php echo @$task['form_name']; ?></td>
                                    <td class="d-none d-xl-table-cell tdst-group-item"><?php echo @$task['created_by_name']; ?>
                                    </td>
                                    <td class="d-none d-xl-table-cell tdst-group-item"><?php echo @$task['date_created']; ?>
                                    </td>
                                    <td class="d-none d-xl-table-cell tdst-group-item"><?php echo @$task['date_updated']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else : ?>
                        <div class="text-center">
                            <h3><?php echo __('No Tasks', 'wams'); ?></h3>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h4 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo __('Tasks that assigned to me all my team members', 'wams'); ?>">
                            <?php echo __('My Team Tasks', 'wams'); ?></h4>
                        <button id="team-tasks-refresh" class="btn"><i class="fas fa-sync"></i></button>
                    </div>

                    <hr>
                    <?php if (!empty($my_team_tasks)) : ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?php echo __('Entry ID', 'wams'); ?></th>
                                    <th><?php echo __('Step', 'wams'); ?></th>
                                    <th><?php echo __('Form', 'wams'); ?></th>
                                    <th class="d-none d-xl-table-cell"><?php echo __('Created By', 'wams'); ?></th>
                                    <th class="d-none d-xl-table-cell"><?php echo __('Date Created', 'wams'); ?></th>
                                    <th class="d-none d-xl-table-cell"><?php echo __('Date Updated', 'wams'); ?></th>
                                </tr>
                            </thead>
                            <?php foreach ($my_team_tasks as $entry_id => $task) : ?>
                                <tr>
                                    <td class="tdst-group-item"><a href="<?php echo $blog_url; ?>/inbox/?page=gravityflow-inbox&view=entry&id=<?php echo @$task['form_id']; ?>&lid=<?php echo @$entry_id; ?>"><?php echo @$entry_id; ?></a>
                                    </td>
                                    <td class="tdst-group-item"><?php echo @$task['step_name']; ?></td>
                                    <td class="tdst-group-item"><?php echo @$task['form_name']; ?></td>
                                    <td class="d-none d-xl-table-cell tdst-group-item"><?php echo @$task['created_by_name']; ?>
                                    </td>
                                    <td class="d-none d-xl-table-cell tdst-group-item"><?php echo @$task['date_created']; ?>
                                    </td>
                                    <td class="d-none d-xl-table-cell tdst-group-item"><?php echo @$task['date_updated']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else : ?>
                        <div class="text-center">
                            <h3><?php echo __('No Tasks', 'wams'); ?></h3>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">
                        <h4 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo __('My Requests', 'wams'); ?>">
                            <?php echo __('My Requests', 'wams'); ?></h4>
                        <button id="requests-refresh" class="btn"><i class="fas fa-sync"></i></button>
                    </div>
                    <hr>
                    <?php if (!empty($my_requests)) : ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?php echo __('Entry ID', 'wams'); ?></th>
                                    <th><?php echo __('Step', 'wams'); ?></th>
                                    <th><?php echo __('Form', 'wams'); ?></th>
                                    <th class="d-none d-xl-table-cell"><?php echo __('Created By', 'wams'); ?></th>
                                    <th class="d-none d-xl-table-cell"><?php echo __('Date Created', 'wams'); ?></th>
                                    <th class="d-none d-xl-table-cell"><?php echo __('Date Updated', 'wams'); ?></th>
                                </tr>
                            </thead>
                            <?php foreach ($my_requests as $entry_id => $task) : ?>
                                <tr>
                                    <td class="tdst-group-item"><a href="<?php echo $blog_url; ?>/status/?page=gravityflow-inbox&view=entry&id=<?php echo @$task['form_id']; ?>&lid=<?php echo @$entry_id; ?>"><?php echo @$entry_id; ?></a>
                                    </td>
                                    <td class="tdst-group-item"><?php echo @$task['step_name']; ?></td>
                                    <td class="tdst-group-item"><?php echo @$task['form_name']; ?></td>
                                    <td class="d-none d-xl-table-cell tdst-group-item"><?php echo @$task['created_by_name']; ?>
                                    </td>
                                    <td class="d-none d-xl-table-cell tdst-group-item"><?php echo @$task['date_created']; ?>
                                    </td>
                                    <td class="d-none d-xl-table-cell tdst-group-item"><?php echo @$task['date_updated']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else : ?>
                        <div class="text-center">
                            <h3><?php echo __('No Requests', 'wams'); ?></h3>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>