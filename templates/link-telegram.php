<?php

/**
 * Template for the search form
 *
 * This template can be overridden by copying it to yourtheme/wams/searchform.php
 *
 * Call: function wams_searchform()
 *
 * @version 1.0.0
 *
 * @var int $current_user_id
 * @var string  $user_display_name
 * @var string  $chat_id
 * @var array  $api [ ]
 */
echo ($user_display_name ?? 'No Chat ID fount!!');
echo ($user_display_name ?? 'No Chat ID fount!!');
echo ($current_user_id ?? 'No Chat ID fount!!');
if (isset($chat_id)) {
    echo '<div class="alert alert-success" role="alert">You have already activated your Telegram account <br>Your Activation Code is ' . $chat_id . ' </div>';
?>
    <h1>Link Telegram Account</h1>
    <div class="row g-3">
        <div id="form-group" class="row-auto">
            <label for="testMessage">Message</label>
            <?php
            $test_message = 'Hello ' . $user_display_name . PHP_EOL . 'This is a test message on your telegram account';
            ?>
            <textarea class="form-control" id="testMessage" rows="3"><?php echo $test_message;  ?></textarea>

        </div>
        <div class="row-auto">
            <button id="sendTestMessage" type="submit" chat-id="<?php echo $chat_id;  ?>" class="btn btn-primary mb-2">Send</button>
        </div>
    </div>
    <hr>
<?php
} else {
    echo '<div class="alert alert-warning" role="alert">You did not set your Chat Activation Code yet!</div>';
}
?>
<div class="container">
    <div class="row">
        <div class="col-sm-6 align-self-start">
            <div id="send-code-tutorial" class="mb-3">
                <div class="row">
                    <div class="col">
                        <p class="card-text">Please use this link to activate telegram notification: <a target=" _blank" href="https://t.me/<?php echo $bot_username; ?>"> https://t.me/<?php echo $bot_username; ?> </a></p>
                        <p class="card-text">Press on <b>start</b> and then write <b>/activate</b> as message</p>
                        <p class="card-text">Once done press <b>Next</b></p>
                        <button id="next" class="btn btn-primary" type="button">Next </button>
                    </div>

                </div>
            </div>
            <div id="send-code-form" class="col-auto g-3 d-none">
                <p>
                    <button id="btn-send-chat-id" class="btn btn-primary" data-chat_id="<?php echo $chat_id;  ?>" type="button">
                        Send Activation Code
                    </button>
                </p>
            </div>
            <br>
            <div id="activate-form" class="row g-2 d-none">
                <p class="card-text">Please insert your activation code:</p>
                <div class="col-auto ">
                    <label for="chat-id" class="visually-hidden"></label>
                    <input type="text" class="form-control" id="chat-id" name="chat_id" placeholder="Activation Code" value="<?php echo $chat_id;  ?>">
                </div>
                <div class="col-auto">
                    <button id="btn-save-chat-id" data-user_id="<?php echo $current_user_id; ?>" data-user_name="<?php echo $user_display_name; ?>" class="btn btn-primary">Save</button>
                </div>
            </div>




        </div>
        <div class="col-sm-6 align-self-start">
            <div class="row">
                <img style="max-width: 330px" src="<?php echo WAMS_URL . '/assets/images/telegram1.jpg'; ?>">
            </div>
            <div class="row">
                <img style="max-width: 330px" src="<?php echo WAMS_URL . '/assets/images/telegram2.jpg'; ?>">
            </div>
        </div>
    </div>


</div>