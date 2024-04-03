<pre>
    <?php print_r($header); ?>
</pre>
<div class="row g-4">
    <div class=" col-lg-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h4 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo __('URLs List', 'wams'); ?>">
                </div>

                <table id="wams-table" data-form="14" data-searchable-fields="1" data-sortable="true" data-page-size="5" data-toolbar=".toolbar" data-height="100%" data-pagination="true" data-side-pagination="server" data-search="true" data-search-align="right" data-pagination="true" data-loading-template='<i class="fa fa-spinner fa-spin fa-fw fa-3x"></i>'>

                    <thead>
                        <tr>
                            <th data-field="1" data-sortable="true"><?php echo __('Title', 'wams'); ?></th>
                            <th data-field="10" data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Creator', 'wams'); ?></th>
                            <th data-field="9" data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Pub_date', 'wams'); ?></th>
                            <th data-field="8" data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('thumbnail', 'wams'); ?></th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>