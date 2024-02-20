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
                            echo '<button class="btn btn-light" id="install-pages">Install Pages</button>';
                            ?>
                        </div>
                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 mb-3">

                            <?php
                            echo '<button class="btn btn-light" id="install-views">Install Views</button>';
                            ?>
                        </div>
                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 mb-3">

                            <?php
                            echo '<button class="btn btn-light" id="install-user-menu">Install User Menu</button>';
                            ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="rounded col-lg-6">
            <div id="entry-messages">
                <ul id="messagesList"></ul>
            </div>
            <div class="d-none justify-content-center" id="loadingSpinner">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>


</div>