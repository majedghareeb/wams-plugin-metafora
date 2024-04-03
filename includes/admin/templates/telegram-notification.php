<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <div class="mb-2">
                <h5 class="card-title">Test Notification</h5>
            </div>
            <form id="notifications-test">
                <div class="mb-3">
                    <label for="" class="form-label">Message</label>
                    <textarea class="form-control" name="message" id="message" rows="6">This is a Test Message only</textarea>
                </div>
                <div class="mb-3">
                    <label for="" class="form-label">Channel ID</label>
                    <input type="text" class="form-control" name="channel-id" id="channel-id"></input>
                </div>
                <div class="mb-3">
                    <label for="" class="form-label">User</label>
                    <select class="form-select form-select-lg" name="user" id="user-id">
                        <option selected>Select User</option>
                        <?php
                        foreach ($users as $user) {
                            $chat_id = get_user_meta($user->ID, 'telegram_chat_id', true);
                            echo '<option value="' . $user->ID . '">' . $chat_id . ' | ' . $user->display_name . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div><button type="submit" id="send-test-notification" class="btn btn-primary">Submit</button></div>
            </form>
        </div>
    </div>
</div>