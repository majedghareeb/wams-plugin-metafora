<div id="tabs" class="wrap">
    <nav id="navbar-debug">
        <ul>
            <li>
                <a href="#logs">System Logs</a>
            </li>
            <li>
                <a href="#checkup">Plugin Checkup</a>
            </li>
            <li>
                <a href="#shortcodes">Available shortcodes</a>
            </li>
            <li>
                <a href="#options">Site Options</a>
            </li>
        </ul>
    </nav>
    <div id="logs">

        <h4 id="logs">System Logs</h4>
        <?php
        $this->system_logs();
        ?>
    </div>
    <div id="shortcodes">
        <h4 id="shortcodes">Available shortcodes</h4>
        <table class="table table-boardered">

            <thead>
                <td><b>Shortcode</b></td>
                <td><b>Description</b></td>
                <td><b>Callback Function</b></td>
            </thead>

            <?php
            foreach ($shortcodes as $shortcode) {
                echo '<tr>';
                echo '<td>[' . $shortcode['shortcode'] . ']</td>';
                echo '<td>' . $shortcode['description'] . '</td>';
                echo '<td>' . $shortcode['callback_func'] . '</td>';
                echo '<tr>';
            }
            ?>
        </table>

    </div>
    <div id="checkup">

        <h4 id="Checkup">Plugins checkup</h4>
        <?php
        $this->system_checkup();
        ?>
    </div>
    <div id="options">
        <h4 id="Options">Options</h4>

        <div id="accordion">

            <?php
            if ($options) :
                foreach ($options as $option) {
                    echo '<h1>' . $option->option_name . '</h1>';
                    echo '<div><p>';
                    $option_value = get_option($option->option_name);
                    if (!is_array($option_value)) {
                        $var = print_r((array) json_decode($option_value), true);
                    } else {
                        $var = print_r($option_value, true);
                    }
                    echo "\n<pre style=\"font-size: 12px;\">\n";
                    echo $var . "\n</pre>\n";
                    echo '</p>';
                    echo '<button class="btn btn-danger clear-option" data-option-id="' . $option->option_name . '">Delete</button>';
                    echo '</div>';
                }
            endif;
            ?>
        </div>

    </div>
</div>