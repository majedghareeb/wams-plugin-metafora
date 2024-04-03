<h1>WEB</h1>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <h5 class="card-title">Installation</h5>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 mb-3">

                            <?php
                            echo '<button class="btn btn-light" id="install-page">Install Page</button>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="mb-2">
                    <h5 class="card-title">Test Notification</h5>
                </div>
                <form id="notifications-test">
                    <div class="mb-3">
                        <label for="" class="form-label">Message</label>
                        <textarea class="form-control" name="message-text" id="message-text" rows="6"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="" class="form-label">User</label>
                        <select class="form-select form-select-lg" name="user" id="user-id">
                            <option selected>Select User</option>
                            <?php
                            foreach ($users as $user) {
                                echo '<option value="' . $user->ID . '">' . $user->display_name . '</option>';
                            }
                            ?>
                        </select>
                    </div>


                    <div><button type="submit" id="send-notification-test" class="btn btn-primary">Submit</button></div>
                </form>
            </div>
        </div>
    </div>

</div>