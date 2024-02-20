<div class="row">
    <?php foreach ($forms_fields as $blog_id => $form) : ?>
        <div class="col border">
            <h2>Blog ID: <?php echo $blog_id; ?></h2>
            <div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th colspan="2">
                                <h5><?php echo $form['id'] . ':' .  $form['title']; ?></h5>
                            </th>
                        </tr>
                        <tr>
                            <th>ID</th>
                            <th>Lable</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($form['fields'] as $id => $label) : ?>
                            <tr>
                                <td><?php echo $id; ?></td>
                                <td><?php echo $label; ?></td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>