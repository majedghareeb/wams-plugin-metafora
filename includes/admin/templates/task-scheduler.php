<form method="POST" action="">
    <h2>Action Scheduler</h2>
    <div class="content">
        <table class="table text-wrap striped table-bordered">
            <thead>
                <td>Hook</td>
                <td>Time In Seconds</td>
            </thead>
            <tbody>
                <?php if (is_array($avaiable_hooks) && !empty($avaiable_hooks)) : ?>
                    <tr>

                        <?php
                        foreach ($avaiable_hooks as $key => $value) :
                            // if ($value == 'on') :
                        ?>
                            <td>
                                <label for="hook[<?php echo $key; ?>]"><?php echo $value; ?></label>
                                <input type="hidden" name="hook[<?php echo $key; ?>]" id="hook[<?php echo $value; ?>]">
                            </td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="" checked />
                                    <label class="form-check-label" for=""> Checked checkbox </label>
                                </div>
                            </td>
                            <td>
                                <select name="interval[<?php echo $key; ?>]" id="interval[<?php echo $value; ?>]">
                                    <?php

                                    foreach ($avaiable_intervals as $interval => $interval_name) {
                                    ?>
                                        <option value="<?php echo $interval; ?>"><?php echo $interval_name; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </td>




                    </tr>
                <?php
                        // endif;
                        endforeach;
                ?>




            <?php endif; ?>
            </tbody>
        </table>

        <input type="submit" name="submit" class="button button-primary" value="Add">
        <?php
        // $as = new ActionScheduler_AdminView;
        // $as->render_admin_ui();
        ?>
    </div>
</form>