<div class="row g-4">
    <div class=" col-lg-12">

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h4 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo __('Reporters List', 'wams'); ?>">
                </div>
                <?php if (!empty($reporters)) :   ?>
                    <table id="my-tasks-table" data-sortable="true" data-page-size="10" data-toolbar=".toolbar" data-toggle="table" data-height="100%" data-pagination="true" data-search="true" data-search-align="right" data-pagination="true">

                        <thead>
                            <tr>
                                <th data-sortable="true"><?php echo __('User ID', 'wams'); ?></th>
                                <th data-sortable="true"><?php echo __('Username', 'wams'); ?></th>
                                <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Email', 'wams'); ?></th>
                                <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('First Name', 'wams'); ?></th>
                                <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Last Name', 'wams'); ?></th>
                                <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Phone', 'wams'); ?></th>
                                <th data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Telegram', 'wams'); ?></th>
                                <th data-sortable="true"><?php echo __('Direct Manager', 'wams'); ?></th>
                                <th data-sortable="true"><?php echo __('Roles', 'wams'); ?></th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reporters as $reporter) : ?>
                                <tr>
                                    <td class=""><a href="<?php echo bloginfo('url'); ?>/edit-reporter/?user_id=<?php echo @$reporter['ID']; ?>"><?php echo @$reporter['ID']; ?></a></td>
                                    <td class=""><?php echo @$reporter['user_login']; ?></td>
                                    <td class="d-none d-xl-table-cell"><?php echo @$reporter['user_email']; ?></td>
                                    <td class="d-none d-xl-table-cell"><?php echo @$reporter['first_name']; ?></td>
                                    <td class="d-none d-xl-table-cell"><?php echo @$reporter['last_name']; ?></td>
                                    <td class="d-none d-xl-table-cell"><?php echo @$reporter['phone_number']; ?></td>
                                    <td class="d-none d-xl-table-cell"><?php echo @$reporter['telegram_chat_id']; ?></td>
                                    <td class=""><?php echo @$reporter['direct_manager']; ?></td>
                                    <td class=""><?php echo implode('<br>', $reporter['roles']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="text-center">
                        <h3><?php echo __('No Reporter Found', 'wams'); ?></h3>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>