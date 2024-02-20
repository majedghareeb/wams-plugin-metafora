<div class="wrap">

    <div>
        <div class="m-3">
            <?php if (!empty($token)) : ?>
                <form name="wams_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                    <?php if (!empty($ga4_profiles_list)) : ?>
                        <div class="mb-3 row">

                            <label for="default_profile">GA4 Main Profile</label>
                            <select name="default_profile" id="default_profile">
                                <?php
                                foreach ($ga4_profiles_list as $profile) {
                                    $selected = ($profile['1'] == $options['default_profile']) ? 'selected' : '';
                                    echo '<option ' . $selected . ' value="' . $profile['1'] . '">';
                                    // echo  $profile['1'];
                                    echo  $profile['2'] . ' - ';
                                    echo  $profile['0'] . ' - ';
                                    echo  $profile['3'];
                                    echo '</option>';
                                    # code...
                                }
                                ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <?php

                    echo '<input type="hidden" name="options[wams_hidden]" value="Y">';
                    wp_nonce_field('wams_form', 'wams_security');
                    ?>
                    <button class="btn btn-primary" type="submit" name="Save" value="Save"><?php _e("Save Settings", 'wams'); ?></button>
                    <button class="btn btn-info" type="submit" name="Refresh" value="Refresh"><?php _e("Refresh Accounts", 'wams'); ?></button>
                    <button class="btn btn-info" type="submit" name="Clear" value="Clear"><?php _e("Clear Cache", 'wams'); ?></button>
                    <button class="btn btn-danger" type="submit" name="Reset" value="Reset"><?php _e("Reset Settings", 'wams'); ?></button>
                    <a href="<?php echo $authUrl ?>" class="btn btn-danger">Re-Autorize</a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php
// $results[] = $ga->get($view_id, $query, $from, $to, $decoded_url);
$options = (array) json_decode(get_option('wams_ga_options'));
// print_r($options['ga4_profiles_list']);
echo '<pre>' . print_r($ga4_profiles_list) . '</pre>';

print_r($result);
