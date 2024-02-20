<h1>Input Site Setup</h1>

<div id="tabs">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#collapse1">Entry Data</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#collapse2">Copy Forms</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#collapse4">Show Fields Map</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#collapse5">Copy Interview</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#collapse6">Copy Assignment</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#collapse7">Copy website</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#collapse8">Copy programs</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#collapse10">Copy Contracts</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#collapse9">Clients Migration</a>
        </li>
    </ul>
    <div id="collapse1" class="p-3">
        <div>
            <label for="entry_id_details" class="">Entry ID</label>
            <input type="number" name="entry_id_details" data-entry-id="" id="entry_id_details">
            <button id="show-entry" data-nonce="<?php echo esc_attr(wp_create_nonce('wams-admin-nonce')); ?>">Show Entry Details</button>
        </div>
    </div>
    <div id="collapse2" class="p-3">
        <div class="accordion-body">
            <label for="entries_form_id" class="">Form ID</label>
            <select name="entries_form_id" id="entries_form_id">
                <option>__</option>
                <?php foreach ($forms as $key => $form) : ?>
                    <option value="<?php echo $key ?>">
                        <?php echo  $form['title'] . ' (' . $form['entry_count'] . ')'; ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <label for="entries_site_id" class="">Destination Site</label>
            <select name="dest_site_id" id="dest_site_id">
                <option>__</option>
                <?php foreach ($sites as $key => $site) : ?>
                    <option value="<?php echo $key ?>"><?php echo  $site; ?></option>
                <?php endforeach; ?>
            </select>
            <button id="copy-form">Copy Form</button>

        </div>
        <div class="py-4">
            <label for="view_id" class="">View ID</label>
            <select name="view_id" id="view_id">
                <option>__</option>
            </select>

            <label for="dest_form_id" class="">Dest Forms</label>
            <select name="dest_form_id" id="dest_form_id">
                <option>__</option>
            </select>
            <button id="copy-view">Copy View</button>
        </div>
    </div>
    <div id="collapse4" class="p-3">
        <div class="accordion-body">
            <label for="migrate-clients_entries" class="">Show Fields</label>
            <button id="get-all-fields">Get Fields</button>
        </div>
        <div id="fields-output">
            <ul id="messages-list"></ul>
        </div>
    </div>
    <div id="collapse5" class="p-3">
        <div class="accordion-body">
            <label for="copy-interview-entries" class="">Copy Interview Enteries</label>
            <button id="copy-interview-entries">Copy Interview</button>
        </div>
    </div>
    <div id="collapse6" class="p-3">
        <div class="accordion-body">
            <label for="copy-assignment-entries" class="">Copy Assignment Enteries</label>
            <button id="copy-assignment-entries">Copy Assignment</button>
        </div>
    </div>
    <div id="collapse7" class="p-3">
        <div class="accordion-body">
            <label for="copy-website-entries" class="">Copy website Enteries</label>
            <button id="copy-website-entries">Copy website</button>
        </div>
    </div>
    <div id="collapse8" class="p-3">
        <div class="accordion-body">
            <label for="copy-programs-entries" class="">Copy programs Enteries</label>
            <button id="copy-programs-entries">Copy programs</button>
        </div>
    </div>
    <div id="collapse9" class="p-3">
        <div class="accordion-body">
            <label for="migrate-clients-entries" class="">Start Clients Migration Process</label>
            <button id="migrate-clients-entries">Migrate</button>
        </div>
    </div>
    <div id="collapse10" class="p-3">
        <div class="accordion-body">
            <label for="copy-clients-contracts-list" class="">Copy Contracts Enteries</label>
            <button id="copy-clients-contracts-list">Copy Contracts</button>
        </div>
    </div>
</div>
<div class="border rounded m-2 p-2">
    <div id="entry-details"></div>
    <div class="progress">

        <div id="progressor" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
    </div>

    <div id="entry-messages">
        <ul id="messagesList"></ul>
    </div>
    <div class="d-none justify-content-center" id="loadingSpinner">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>

    </div>
    <button id="stop-process">Stop the process</button>
</div>