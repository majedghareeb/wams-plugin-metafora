<?php
if (!isset($entries)) return;
?>
<div class="row">

    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="card-title"><?php echo __('Entrys List', 'wams'); ?><span class="text-muted fw-normal ms-2">(<?php echo isset($entrys) ? count($entrys) : '0'; ?>)</span></h5>
                        </div>
                    </div>

                    <div class="toolbar">
                        <button id="get-analytics" data-action-type="get-multiple-analytics" class="btn btn-light"><i class="fa-solid fa-chart-simple"></i><?php echo __('Get Analtyics Data', 'wams'); ?></button>
                    </div>
                    <!-- end row -->
                    <div class="table-responsive mb-4">
                        <table id="urls-list-table" data-id-field="id" data-click-to-select="true" data-sortable="true" data-page-size="5" data-toolbar=".toolbar" data-toggle="table" data-height="100%" data-pagination="true" data-search="true" data-search-align="right" data-pagination="true">
                            <thead>
                                <tr>
                                    <th data-checkbox="true"></th>
                                    <th data-field="id" data-visible="false"></th>
                                    <th data-field="entry_id"></th>
                                    <th data-sortable="true" data-field="title"><?php echo __('Title'); ?></th>
                                    <th><?php echo __('Link'); ?></th>
                                    <th data-field="pub-date"><?php echo __('Pub Date'); ?></th>
                                    <th data-field="author"><?php echo __('Author'); ?></th>
                                    <th data-field="pageview"><?php echo __('Pageviews'); ?></th>
                                    <th data-field="sessions"><?php echo __('Sessions'); ?></th>
                                    <th><?php echo __('Thumbnail'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($entries)) : ?>
                                    <?php foreach ($entries as $index => $entry) : ?>
                                        <tr>
                                            <td></td>
                                            <td><?php echo $index; ?></td>
                                            <td><?php echo $entry['id']; ?></td>
                                            <td><?php echo $entry[$header['title']]; ?></td>
                                            <td>
                                                <a target="_blank" href="<?php echo $entry[$header['link']]; ?>" class="text-body">Link</a>
                                            </td>
                                            <td><?php echo wams_nice_time($entry[$header['pub_date']]); ?></td>
                                            <td><?php echo ($entry[$header['creator']]); ?></td>
                                            <td><?php echo ($entry[$header['pageviews'] ?? 'id'] ?? 0); ?> </td>
                                            <td><?php echo ($entry[$header['sessions'] ?? 'id'] ?? 0); ?></td>
                                            <td><img src="<?php echo $entry[$header['thumbnail']]; ?>" alt="" style="max-width:100px;" class="img-thumbnail me-2"></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="table-responsive mb-4">
                                        <tr>
                                            <td colspan="6">
                                                <h2 class="text-center"><?php echo __('There is no entrys', 'wams'); ?></h2>
                                            </td>
                                        </tr>

                                    </div>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <!-- end table -->
                    </div>
                    <!-- end table responsive -->
                </div>
            </div>
        </div>
    </div>
</div>