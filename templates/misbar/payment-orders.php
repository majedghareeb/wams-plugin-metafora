<pre>
    <?php print_r($columns);


    ?>
</pre>

<div class="row g-4">
    <div class=" col-lg-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h4 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo __('Links List', 'wams'); ?>">
                </div>
                <div id="toolbar" class="dropdown">
                    <span>
                        <?php echo 'order:' . $po_name;  ?>
                    </span>
                    <button id="add-to-order" data-order-id="<?php echo $po_id;  ?>" class="btn btn-secondary">Add To Order</button>
                </div>

                <table id="wams-table" data-buttons="buttons" data-form="1" data-searchable-fields="<?php echo $columns['title'] . ',' . $columns['orderId'] ?>" data-page-size="10">

                    <thead>
                        <tr>
                            <th data-field="state" data-checkbox="true"></th>
                            <th data-field="id" data-sortable="true" class="d-xl-table-cell"><?php echo __('ID', 'wams'); ?></th>
                            <th data-field="<?php echo $columns['orderId']; ?>" data-sortable="true" class="d-xl-table-cell"><?php echo __('Order ID', 'wams'); ?></th>
                            <th data-field="<?php echo $columns['title']; ?>" data-sortable="true" class="d-xl-table-cell"><?php echo __('Title', 'wams'); ?></th>
                            <th data-field="<?php echo $columns['cost']; ?>" data-visible="true" data-sortable="true"><?php echo __('Cost', 'wams'); ?></th>
                            <th data-field="<?php echo $columns['vendor_name']; ?>" data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Vendor', 'wams'); ?></th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>