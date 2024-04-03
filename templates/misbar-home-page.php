<?php

/**
 * Template for the Home
 *
 *
 * Call Tasks: function wams_home()
 *
 * @version 1.0.1
 *
 * @var array $user_tasks
 * @var array  $user_team_tasks
 * @var array  $user_requests
 */
if (!defined('ABSPATH')) {
    exit;
}
$blog_url = get_bloginfo('url');
$my_team_tasks_count = $my_tasks_count = [];
if (!empty($my_tasks) && isset($my_tasks)) {
    foreach ($my_tasks as $my_task) {
        if (is_array($my_task) && isset($my_task['step_name'])) {
            $my_tasks_count[] =  $my_task['step_name'];
        }
    }
}
if (!empty($my_team_tasks) && isset($my_team_tasks)) {
    foreach ($my_team_tasks as $my_team_task) {
        if (is_array($my_team_task) && isset($my_team_task['step_name'])) {
            $my_team_tasks_count[] = $my_team_task['step_name'];
        }
    }
}
// $my_team_tasks_count = (!empty($my_team_tasks) && isset($my_team_tasks_count)) ?  wp_list_pluck($my_team_tasks, 'step_name') : [];
$my_tasks_summary =  array_count_values($my_tasks_count);
$team_summary =  array_count_values($my_team_tasks_count);

?>

<div class="container">


    <div class="row g-4">
        <div class=" col-lg-12">
            <div class="card shadow-sm bg-body">
                <?php echo do_shortcode('[ultimatemember_online]');
                ?>
            </div>
        </div>
        <div class=" col-lg-12">
            <div class="card border border-dark bg-primary-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h4 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo __('Tasks that assigned to me only', 'wams'); ?>">
                            <?php echo __('My Latest Tasks', 'wams'); ?>
                            <span class="mx-2 badge rounded-pill bg-danger float-end"><?php if (!empty($my_tasks)) echo count($my_tasks); ?></span>
                        </h4>
                        <button data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo __('Refresh', 'wams'); ?>" id="tasks-refresh" class="btn"><i class="fas fa-sync"></i></button>
                    </div>

                    <?php if (!empty($my_tasks_count)) : ?>
                        <div class="row">
                            <div class="col-xl-2 col-md-3 col-sm-4 col-6">
                                <!-- card -->
                                <div class="card bg-light card-h-100">
                                    <div class="card-header text-center h-100 p-2">
                                        <h6><?php echo  __('All Tasks', 'wams') ?></h6>
                                    </div>
                                    <!-- card body -->
                                    <div class="card-body p-2">
                                        <div class="d-flex text-center align-items-center">
                                            <div class="flex-grow-1">

                                                <h2 class="mb-2">
                                                    <span style="cursor: pointer" data-step-name="" class="my-tasks-step-count d-block"><?php echo count($my_tasks) ?></span>
                                                </h2>
                                                <div class="nowrap">
                                                    <span class="p-2 badge bg-success-subtle text-danger"><?php echo __('Pending', 'gravityflow'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col -->
                            <?php foreach ($my_tasks_summary as $step => $count) : ?>
                                <div class="col-xl-2 col-md-3 col-sm-4 col-6">
                                    <!-- card -->
                                    <div class="card card-h-100">
                                        <div class="card-header text-center h-100 p-2">
                                            <h6><?php echo  $step ?></h6>
                                        </div>
                                        <!-- card body -->
                                        <div class="card-body p-2">
                                            <div class="d-flex text-center align-items-center">
                                                <div class="flex-grow-1">
                                                    <h2 class="mb-2">
                                                        <span style="cursor: pointer" data-step-name="<?php echo  $step ?>" class="my-tasks-step-count d-block"><?php echo $count; ?></span>
                                                    </h2>
                                                    <div class="nowrap">
                                                        <span class="p-2 badge bg-success-subtle text-danger"><?php echo __('Pending', 'gravityflow'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!-- end card body -->
                                    </div><!-- end card -->
                                </div><!-- end col -->
                            <?php endforeach; ?>
                        </div>
                        <table id="my-tasks-table" data-sortable="true" data-page-size="5" data-toolbar=".toolbar" data-toggle="table" data-height="100%" data-pagination="true" data-search="true" data-search-align="right" data-pagination="true">
                            <thead>
                                <tr>
                                    <th data-sortable="true"><?php echo __('Entry ID', 'wams'); ?></th>
                                    <th data-sortable="true"><?php echo __('Form', 'wams'); ?></th>
                                    <th data-sortable="true"><?php echo __('Current Step', 'wams'); ?></th>
                                    <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Created By', 'wams'); ?></th>
                                    <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Date Created', 'wams'); ?></th>
                                    <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Last Step Time', 'wams'); ?></th>
                                    <th class="d-none d-xl-table-cell"><?php echo __('Assignees', 'wams'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_tasks as $entry_id => $entry) : ?>
                                    <tr>
                                        <td><a href="<?php echo $blog_url; ?>/inbox/?page=gravityflow-inbox&view=entry&id=<?php echo @$entry['form_id']; ?>&lid=<?php echo @$entry_id; ?>"><?php echo @$entry_id; ?></a>
                                        </td>
                                        <td><?php echo @$entry['form_name']; ?></td>
                                        <td><?php echo @$entry['step_name']; ?></td>
                                        <td><?php echo @$entry['workflow_final_status']; ?></td>
                                        <td class="d-none d-xl-table-cell"><?php echo @$entry['created_by_name']; ?>
                                        </td>
                                        <td class="d-none d-xl-table-cell"><?php echo wams_nice_time($entry['date_created']); ?>
                                        </td>
                                        <td class="d-none d-xl-table-cell"><?php echo isset($entry['assignees']) ?  implode("<br>", $entry['assignees']) : ''; ?>
                                        </td>



                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
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
            <div class="card shadow-sm bg-primary-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h4 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo __('Tasks that assigned to me all Tasks my team members', 'wams'); ?>">
                            <?php echo __('My Team Tasks', 'wams'); ?>
                            <span class=" mx-2 badge rounded-pill bg-danger float-end"><?php if (!empty($my_team_tasks)) echo count($my_team_tasks); ?></span>
                        </h4>
                        <button id="team-tasks-refresh" class="btn"><i class="fas fa-sync"></i></button>
                    </div>
                    <?php if (!empty($my_team_tasks_count)) : ?>
                        <div class="row">
                            <div class="col-xl-2 col-md-3 col-sm-4 col-6">
                                <!-- card -->
                                <div class="card bg-light card-h-100">
                                    <!-- card body -->
                                    <div class="card-header text-center h-100 p-2">
                                        <h6><?php echo  __('All Tasks', 'wams') ?></h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="d-flex text-center align-items-center p-2">
                                            <div class="flex-grow-1">
                                                <h2 class="mb-2">
                                                    <span style="cursor: pointer" data-step-name="" class="my-team-tasks-step-count d-block"><?php echo count($my_team_tasks) ?></span>
                                                </h2>
                                                <div class="text-nowrap">
                                                    <span class="p-2 badge bg-success-subtle text-danger"><?php echo __('Pending', 'gravityflow'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col -->
                            <?php foreach ($team_summary as $step => $count) : ?>
                                <div class="col-xl-2 col-md-3 col-sm-4 col-6">
                                    <!-- card -->
                                    <div class="card card-h-100">
                                        <div class="card-header text-center h-100 p-2">
                                            <h6><?php echo  $step ?></h6>
                                        </div>
                                        <!-- card body -->
                                        <div class="card-body">
                                            <div class="d-flex text-center align-items-center">
                                                <div class="flex-grow-1">
                                                    <h2 class="mb-2">
                                                        <span style="cursor: pointer" data-step-name="<?php echo  $step ?>" class="my-team-tasks-step-count d-block"><?php echo $count; ?></span>
                                                    </h2>
                                                    <div class="text-nowrap">
                                                        <span class="p-2 badge bg-success-subtle text-danger"><?php echo __('Pending', 'gravityflow'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!-- end card body -->
                                    </div><!-- end card -->
                                </div><!-- end col -->
                            <?php endforeach; ?>
                        </div>
                        <table id="my-team-tasks-table" data-sortable="true" data-page-size="10" data-toolbar=".toolbar" data-toggle="table" data-height="100%" data-pagination="true" data-search="true" data-search-align="right" data-pagination="true">

                            <!-- <table id="my-team-tasks-table" class="table table-striped"> -->
                            <thead>
                                <tr>
                                    <th data-sortable="true"><?php echo __('Entry ID', 'wams'); ?></th>
                                    <th data-sortable="true"><?php echo __('Form', 'wams'); ?></th>
                                    <th data-sortable="true"><?php echo __('Current Step', 'wams'); ?></th>
                                    <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Created By', 'wams'); ?></th>
                                    <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Date Created', 'wams'); ?></th>
                                    <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Last Step Time', 'wams'); ?></th>
                                    <th class="d-none d-xl-table-cell"><?php echo __('Assignees', 'wams'); ?></th>
                                </tr>
                            </thead>
                            <?php foreach ($my_team_tasks as $entry_id => $entry) : ?>

                                <tr>
                                    <td><a href="<?php echo $blog_url; ?>/inbox/?page=gravityflow-inbox&view=entry&id=<?php echo @$entry['form_id']; ?>&lid=<?php echo @$entry_id; ?>"><?php echo @$entry_id; ?></a>
                                    </td>
                                    <td><?php echo @$entry['form_name']; ?></td>
                                    <td><?php echo  @$entry['step_name']; ?></td>
                                    <td class="d-none d-xl-table-cell"><?php echo @$entry['created_by_name']; ?></td>
                                    <td class="d-none d-xl-table-cell"><?php echo @$entry['date_created']; ?></td>
                                    <td class="d-none d-xl-table-cell"><?php echo $entry['workflow_timestamp'] != '' ? date('Y-m-d H:m:s', $entry['workflow_timestamp']) : $entry['date_updated']; ?>
                                    </td>
                                    <td class="d-none d-xl-table-cell"><?php echo implode("<br>", $entry['assignees']); ?>
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
            <div class="card shadow-sm bg-light">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">
                        <h4 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo __('My Requests', 'wams'); ?>">
                            <?php echo __('My Requests', 'wams'); ?>
                            <span class=" mx-2 badge rounded-pill bg-danger float-end"><?php if (!empty($my_requests)) echo count($my_requests); ?></span>
                        </h4>
                        <button id="requests-refresh" class="btn"><i class="fas fa-sync"></i></button>
                    </div>

                    <?php if (!empty($my_requests)) : ?>

                        <table id="my-requests-table" data-sortable="true" data-page-size="10" data-toolbar=".toolbar" data-toggle="table" data-height="100%" data-pagination="true" data-search="true" data-search-align="right" data-pagination="true">
                            <thead>
                                <tr>
                                    <th data-sortable="true"><?php echo __('Entry ID', 'wams'); ?></th>
                                    <th data-sortable="true"><?php echo __('Step', 'wams'); ?></th>
                                    <th data-sortable="true"><?php echo __('Form', 'wams'); ?></th>
                                    <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Created By', 'wams'); ?></th>
                                    <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Date Created', 'wams'); ?></th>
                                    <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Final Status', 'wams'); ?></th>
                                </tr>
                            </thead>
                            <?php foreach ($my_requests as $entry_id => $entry) : ?>
                                <tr>
                                    <td><a href="<?php echo $blog_url; ?>/inbox/?page=gravityflow-inbox&view=entry&id=<?php echo @$entry['form_id']; ?>&lid=<?php echo @$entry_id; ?>"><?php echo @$entry_id; ?></a>
                                    </td>
                                    <td><?php echo $entry['step_name'] ?? ''; ?></td>
                                    <td><?php echo $entry['form_name'] ?? ''; ?></td>
                                    <td class="d-none d-xl-table-cell"><?php echo $entry['created_by_name'] ?? ''; ?>
                                    </td>
                                    <td class="d-none d-xl-table-cell"><?php echo $entry['date_created'] ?? ''; ?>
                                    </td>
                                    <td class="d-none d-xl-table-cell"><?php echo $entry['workflow_final_status'] ?? ''; ?>
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