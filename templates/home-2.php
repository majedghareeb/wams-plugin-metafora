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
wp_enqueue_style("bootstrap-table", WAMS_URL . 'assets/css/bootstrap-table.min.css', array(), WAMS_VERSION);
wp_enqueue_script("bootstrap-table", WAMS_URL . 'assets/js/bootstrap-table.min.js', array(), WAMS_VERSION, false);
$blog_url = get_bloginfo('url');

echo '<pre>' . print_r($my_team_tasks['2176'], true) . '</pre>';
?>
<div class="container">
    <div class="row g-4">
        <div class=" col-lg-12">
            <div class="card shadow-sm">
                <?php //echo do_shortcode('[ultimatemember_online]'); 
                ?>
            </div>
        </div>
        <div class=" col-lg-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h4 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo __('Tasks that assigned to me only', 'wams'); ?>">
                            <?php echo __('My Latest Tasks', 'wams'); ?>
                            <span class=" mx-2 badge rounded-pill bg-danger float-end"><?php if (!empty($my_tasks)) echo count($my_tasks); ?></span>
                        </h4>
                        <button data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo __('Refresh', 'wams'); ?>" id="tasks-refresh" class="btn"><i class="fas fa-sync"></i></button>
                    </div>

                    <hr>
                    <?php if (!empty($my_tasks)) : ?>
                        <table id="my-tasks-table" data-sortable="true" data-page-size="5" data-toolbar=".toolbar" data-toggle="table" data-height="100%" data-pagination="true" data-search="true" data-search-align="right" data-pagination="true">
                            <thead>
                                <tr>
                                    <th data-sortable="true" data-field="entry_id"><?php echo __('Entry ID', 'wams'); ?></th>
                                    <th data-sortable="true" data-field="form"><?php echo __('Form', 'wams'); ?></th>
                                    <th data-sortable="true" data-field="step"><?php echo __('Current Step', 'wams'); ?></th>
                                    <th data-sortable="true" data-field="final_status"><?php echo __('Final Status', 'wams'); ?></th>
                                    <th data-sortable="true" data-field="created_by" class="d-none d-xl-table-cell"><?php echo __('Created By', 'wams'); ?></th>
                                    <th data-sortable="true" data-field="date_created" class="d-none d-xl-table-cell"><?php echo __('Date Created', 'wams'); ?></th>
                                    <th data-sortable="true" data-field="date_updated" class="d-none d-xl-table-cell"><?php echo __('Date Updated', 'wams'); ?></th>
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
                                        <td class="d-none d-xl-table-cell"><?php echo implode("<br>", $entry['assignees']); ?>
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
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h4 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo __('Tasks that assigned to me all my team members', 'wams'); ?>">
                            <?php echo __('My Team Tasks', 'wams'); ?>
                            <span class=" mx-2 badge rounded-pill bg-danger float-end"><?php if (!empty($my_team_tasks)) echo count($my_team_tasks); ?></span>
                        </h4>
                        <button id="team-tasks-refresh" class="btn"><i class="fas fa-sync"></i></button>
                    </div>

                    <hr>
                    <?php if (!empty($my_team_tasks)) : ?>
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
                                    <td class="d-none d-xl-table-cell"><?php echo date('Y-m-d H:m:s', $entry['workflow_timestamp']); ?>
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
            <div class="card shadow-sm">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">
                        <h4 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo __('My Requests', 'wams'); ?>">
                            <?php echo __('My Requests', 'wams'); ?>
                            <span class=" mx-2 badge rounded-pill bg-danger float-end"><?php if (!empty($my_requests)) echo count($my_requests); ?></span>
                        </h4>
                        <button id="requests-refresh" class="btn"><i class="fas fa-sync"></i></button>
                    </div>
                    <hr>
                    <?php if (!empty($my_requests)) : ?>
                        <table id="my-requests-table" data-sortable="true" data-page-size="10" data-toolbar=".toolbar" data-toggle="table" data-height="100%" data-pagination="true" data-search="true" data-search-align="right" data-pagination="true">
                            <thead>
                                <tr>
                                    <th data-sortable="true"><?php echo __('Entry ID', 'wams'); ?></th>
                                    <th data-sortable="true"><?php echo __('Step', 'wams'); ?></th>
                                    <th data-sortable="true"><?php echo __('Form', 'wams'); ?></th>
                                    <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Created By', 'wams'); ?></th>
                                    <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Date Created', 'wams'); ?></th>
                                    <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Date Updated', 'wams'); ?></th>
                                </tr>
                            </thead>
                            <?php foreach ($my_requests as $entry_id => $entry) : ?>
                                <tr>
                                    <td><a href="<?php echo $blog_url; ?>/inbox/?page=gravityflow-inbox&view=entry&id=<?php echo @$entry['form_id']; ?>&lid=<?php echo @$entry_id; ?>"><?php echo @$entry_id; ?></a>
                                    </td>
                                    <td><?php echo @$entry['step_name']; ?></td>
                                    <td><?php echo @$entry['form_name']; ?></td>
                                    <td class="d-none d-xl-table-cell"><?php echo @$entry['created_by_name']; ?>
                                    </td>
                                    <td class="d-none d-xl-table-cell"><?php echo @$entry['date_created']; ?>
                                    </td>
                                    <td class="d-none d-xl-table-cell"><?php echo @$entry['date_created']; ?>
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