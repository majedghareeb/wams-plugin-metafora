<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<h4> $header </h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th scope="col">plugin</th>
            <th scope="col">Name</th>
            <th scope="col">Version</th>
            <th scope="col">Installation</th>
            <th scope="col">Activation</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($plugins as $slug => $plugin) {
            $activated = '';
            $installed = '';
            if ($plugin['installed'] && !$plugin['activated']) {
                $installed = 'Installed';
                $activated = 'Not Activated';
                $class = 'warning';
            } else {
                $class = 'danger';
            }
            if ($plugin['activated']) {
                $installed = 'Installed';
                $activated = 'Activated';
                $class = 'success';
            }

        ?>
            <tr class="table- $class ">
                <th scope="row"> <?php echo $slug; ?> </th>
                <td> <?php echo $plugin['name']; ?></td>
                <td><?php echo $plugin['version']; ?></td>
                <td><?php echo $installed; ?></td>
                <td><?php echo $activated; ?></td>
            </tr>
        <?php    } ?>
    </tbody>
</table>';