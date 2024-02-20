<div class="wrap">
    <div class="content">
        <h3>Current Number of workflow rows in DB: <span id="workflow-count"><?php echo $total_rows ?? ''; ?></span>
            <span>
                <button type="button" class="btn btn-info" id="refresh-workflow-count">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"></path>
                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"></path>
                    </svg>
                </button>
            </span>
        </h3>
        <div class="tab-pane border p-3 fade show active" id="nav-settings" role="tabpanel" aria-labelledby="nav-settings-tab">
            <div>
                <div class="content">

                    <nav class="navbar navbar-light bg-light">
                        <table id="forms_table" class="table table-striped table-hover widefat">
                            <thead>
                                <tr>
                                    <th>Form ID</th>
                                    <th>Title</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                foreach ($forms as $form) :

                                    // $entry_count = $this->get_entry_count($form->id);
                                ?>
                                    <tr>
                                        <td>
                                            <?php echo $form->id;
                                            ?></td>
                                        <td class="fw-bold fs-6">
                                            <?php echo $form->title;
                                            ?></td>
                                        <td class="fw-bold fs-6">
                                            <?php echo $form->entry_count;
                                            ?>
                                        </td>
                                        <td>
                                            <p>
                                                <a class="get-entry-breakdown btn btn-primary" data-form-id="<?php echo $form->id; ?>">
                                                    Breakdown
                                                </a>
                                            </p>

                                        </td>



                                    </tr>
                                    <tr>
                                    <tr id="breakdown-row-<?php echo $form->id; ?>" class="d-none">
                                        <td colspan="4">
                                            <div id="loading-breakdown-<?php echo $form->id; ?>" class="justify-content-center d-none">
                                                <div class="spinner-border m-auto" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>
                                            <div id="breakdown-<?php echo $form->id; ?>">

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            </tbody>


                        </table>

                </div>
            </div>

        </div>

    </div>

</div>