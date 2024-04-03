<pre>
    <?php print_r($_GET);

    if (!isset($_GET['payment-order'])) {
        echo '<div class="alert alert-danger">Please select Payment Order First</div>';
        return;
    } else {
        $po_entry = GFAPI::get_entry($_GET['payment-order']);
        if ($po_entry) {
            $po_id = rgar($po_entry, 'id', $_GET['payment-order']);
            $po_name = rgar($po_entry, 1, 'No Name');
            $po_assignee = rgar($po_entry, 2);
            $po_assignee_name = get_user_meta($po_assignee, 'first_name', true);

            // print_r($po_entry);
        } else {
            echo '<div class="alert alert-danger">The Payment Order is not valid!!</div>';
            return;
        }
    }
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

                <table id="wams-table" data-buttons="buttons" data-form="19" data-searchable-fields="1,3" data-page-size="10">

                    <thead>
                        <tr>
                            <th data-field="state" data-checkbox="true"></th>
                            <th data-field="3" data-formatter="linkFormatter" data-sortable="true" class="d-xl-table-cell"><?php echo __('Name', 'wams'); ?></th>
                            <th data-field="1" data-visible="false" data-sortable="true"><?php echo __('Link', 'wams'); ?></th>
                            <th data-field="gpnf_entry_nested_form_field" data-sortable="true" class="d-none d-xl-table-cell"><?php echo __('Type', 'wams'); ?></th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>