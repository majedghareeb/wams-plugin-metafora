<h1>CSV Importer Template</h1>

<?php
// echo '<pre>' . print_r($first_row, true) . '</pre>';

// Handle the CSV file upload
$option = get_option('wams_csv_template_vendors');
if ($option) {

?>
    <div class="table-responsive">
        <h5>Current Template</h5>
        <form method="post" class="" enctype="multipart/form-data">
            <input name="template" type="hidden" value="<?php echo $option['template']; ?>">
            <input name="file_path" type="hidden" value="<?php echo $option['path']; ?>">
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Template</th>
                        <th>Path</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $option['template']; ?></td>
                        <td><?php echo $option['path']; ?></td>
                        <td><input type="submit" name="delete_template" class="btn btn-danger" value="Delete"></td>
                    </tr>
                </tbody>

            </table>
        </form>
    </div>
    <hr>


<?php
}
if (isset($header)) { ?>
    <div class="table-responsive">
        <form method="post" enctype="multipart/form-data">
            <input name="file_path" type="hidden" value="<?php echo ($target_file); ?>">
            <table class="">
                <thead>
                    <tr>
                        <td>Form</td>
                        <td>Field</td>
                        <td>Field Type</td>
                        <td>CSV Cell</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms_fields as $field) { ?>

                        <tr>
                            <td><?php echo $field['form_title'] ?></td>
                            <td><?php echo $field['field_label'] ?></td>
                            <td><?php echo $field['field_type']; ?></td>
                            <td>
                                <select name="select[<?php echo $field['form_id'] . '.' . $field['field_id']; ?>]" id="select_<?php echo $field['form_id']; ?>.<?php echo $field['field_id']; ?>">
                                    <option value="">___</option>
                                    <?php
                                    $slected = '';
                                    foreach ($header as $id => $column) {
                                    ?>
                                        <option <?php echo $slected; ?> value="<?php echo $id; ?>.<?php echo $column; ?>"><?php echo $column; ?>(<?php echo $first_row[$id]; ?>)</option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>



            </table>
            <input type="submit" name="save_map" class="btn btn-primary" value="Save Map">
        </form>
    </div>
<?php
};

if (!isset($_POST['upload_csv']) && !isset($_POST['edit_template'])) {
?>
    <div class="wrap">
        <h2>Upload New Template</h2>
        <form method="post" enctype="multipart/form-data">
            <label for="csv_file">Choose CSV file:</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv">
            <p class="description">Upload a CSV file.</p>

            <?php wp_nonce_field('csv_upload_nonce', 'csv_upload_nonce'); ?>

            <input type="submit" name="upload_csv" class="button button-primary" value="Upload CSV">
        </form>
    </div>

<?php
}



?>

<hr>
<div class="wrap">

    <?php
    $option = get_option('import_vendor_csv_template');
    if ($option) {
        $header = [];
        $fields = [];
        foreach ($option as $key => $value) {
            $header[] = $value['csv_col'];
            $fields[] = $value['field_id'];
        }
    }
    ?>
</div>
<hr>