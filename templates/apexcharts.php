<h1>Charts</h1>
<?php print_r($tasks);
// print_r($forms);
// echo json_encode($tasks['data']);
if (!empty($tasks) && !empty($forms)) :
?>
    <div class="row">
        <?php foreach ($forms as $form) : ?>
            <div class="col-xl-2 col-md-3 col-sm-4 col-6">
                <!-- card -->
                <div class="card card-h-100">
                    <div class="card-header text-center h-100 p-2">
                        <h6><?php echo  $form->title; ?></h6>
                    </div>
                    <!-- card body -->
                    <div class="card-body p-2">
                        <div class="d-flex text-center align-items-center">
                            <div class="flex-grow-1">
                                <h2 class="mb-2">
                                    <span data-form-title="<?php echo $form->title; ?>" data-form-id="<?php echo $form->id; ?>" style="cursor: pointer" class="form-chart d-block"><?php echo $form->entry_count; ?></span>
                                </h2>
                            </div>
                        </div>
                    </div><!-- end card body -->
                </div><!-- end card -->
            </div><!-- end col -->
        <?php endforeach; ?>
    </div><!-- end col -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Pie</h4>
                </div>
                <div class="card-body">
                    <div id="pie_chart" data-colors='["--bs-primary"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div><!--end card-->
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Bar Chart</h4>
                </div>
                <div class="card-body">
                    <div id="bar_chart" data-colors='["--bs-success"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div><!--end card-->
        </div>
    </div>
<?php endif; ?>