<?php

if (isset($message)) {
?>
    <div class="alert alert-<?php echo $message['type'] ?? 'info'; ?>">
        <h5><?php echo $message['text'] ?? ''; ?></h5>
    </div>
<?php
}
if ($option) {
    $existing_file = get_transient('wams_imported_csv');
?>
    <div class="table-responsive">
        <h5>Current Template</h5>
        <form method="post" enctype="multipart/form-data">
            <input name="template" type="hidden" value="<?php echo  $option['template']; ?>">
            <input name="file_path" type="hidden" value="<?php echo  $option['path']; ?>">
            <table class="table">
                <thead>
                    <tr>
                        <th>Template</th>
                        <th>File Name</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $option['template']; ?></td>
                        <td><?php echo htmlspecialchars(basename($option['path'])); ?></td>
                        <td><input type="submit" name="export_csv" class="btn btn-primary" value="Download"></td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
    <hr>
<?php } ?>

<div class="wrap">
    <h2>Import Vendors</h2>

    <form method="post" enctype="multipart/form-data">
        <label for="csv_file">Choose CSV file:</label>
        <input type="file" name="csv_file" id="csv_file" accept=".csv">

        <?php wp_nonce_field('csv_upload_nonce', 'csv_upload_nonce'); ?>

        <input type="submit" name="upload_csv" class="btn btn-primary" value="Upload CSV">
    </form>

</div>
<hr>
<?php if ($existing_file) : ?>
    <div class="alert alert-success">Last Uploaded file : <?php echo basename($existing_file['file_path']); ?> with # <?php echo $existing_file['count']; ?> rows</div>
    <div class="wrap">
        <button id="import-start" class="btn btn-primary">Start</button>
        <button id="stop-import" class="btn btn-danger">Stop</button>
        <div class=" border rounded m-2 p-2">
            <div id="details"></div>
            <div class="progress">
                <div id="progressor" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
            </div>
            <div id="messages">
                <ul id="messages-list"></ul>
            </div>
        </div>
    </div>
<?php endif; ?>