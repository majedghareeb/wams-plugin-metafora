<table id="forms_table" class="table table-striped table-hover widefat">
    <thead>
        <tr>
            <th>Year</th>
            <th>approved</th>
            <th>complete</th>
            <th>rejected</th>
            <th>cancelled</th>
            <th>pending</th>
            <th>NULL</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($result as $year => $count) : ?>
            <tr>
                <td><?php echo $year; ?></td>
                <td class="fw-bold"><?php echo $count['approved']; ?></td>
                <td class="fw-bold"><?php echo $count['complete']; ?></td>
                <td class="fw-bold"><?php echo $count['rejected']; ?></td>
                <td class="fw-bold"><?php echo $count['cancelled']; ?></td>
                <td class="fw-bold"><?php echo $count['pending']; ?></td>
                <td><?php echo $count['']; ?></td>

                <td>
                    <button class="btn btn-danger btn-sm cancel_workflow" data-year="<?php echo $year; ?>" data-form_id="<?php echo $form_id; ?>">Cancel Workflow</button>
                    <?php if ($archivable_forms && $archivable_forms[$form_id] == 'on') : ?>
                        <button class="btn btn-warning btn-sm archive_entries" data-year="<?php echo $year; ?>" data-form_id="<?php echo $form_id; ?>">Archive Entries</button>
                    <?php endif; ?>
                </td>

            </tr>
        <?php endforeach; ?>
    </tbody>
</table>