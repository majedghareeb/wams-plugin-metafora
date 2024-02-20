<div class="table table-responsive">
    <form method="post">
        <input type="hidden" name="clear_debug_log" value="1">
        <button type="submit" class="clear-log-button">Clear Log</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
            <pre><?php
                    // print_r($log_entries);
                    foreach ($log_entries as $logLine) {
                        if (!empty($logLine)) {
                            $pattern = '/\[(.*?)\] (.*?)$/';

                            // Use preg_match to extract matches
                            if (preg_match($pattern, $logLine, $matches)) {
                                // $matches[1] will contain the date, $matches[2] will contain the message
                                $date = $matches[0];
                                $message = $matches[1];
                            }
                    ?>
                    <tr>
                        <td><?php echo  esc_html($date); ?></td>
                        <td><?php esc_html($message); ?></td>
                    </tr>
            <?php
                        }
                    }
            ?>
        </tbody>
    </table>
</div>