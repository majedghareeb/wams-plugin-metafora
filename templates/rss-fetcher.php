<div class="row g-4">
    <div class="row-auto">
        <button id="fetch-rss-feed" class="btn btn-primary mb-2"><span class="mx-2"><i class="fas fa-rss"></i></span>Fetch RSS</button>
    </div>
    <div class=" col-lg-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h4 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo __('Links Fetched From RSS', 'wams'); ?>">
                        <?php echo __('Fetched Links', 'wams'); ?></h4>
                </div>

                <hr>
                <?php if (!empty($posts)) : ?>
                    <table class="table table-responsive table-striped">
                        <thead>
                            <tr>
                                <th><?php echo __('Title', 'wams'); ?></th>
                                <th><?php echo __('Creator', 'wams'); ?></th>
                                <th><?php echo __('Pub Date', 'wams'); ?></th>
                                <th><?php echo __('Thumbnail', 'wams'); ?></th>
                                <th><?php echo __('Saved!!', 'wams'); ?></th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post) : ?>
                                <tr>
                                    <td class="tdst-group-item"><a href="<?php echo @$post['link']; ?>" target="_blank" rel="noopener noreferrer"><?php echo @$post['title']; ?></a></td>
                                    <td class="tdst-group-item"><?php echo @$post['creator']; ?></td>
                                    <td class="tdst-group-item"><?php echo @$post['pub_date']; ?></td>
                                    <td class="tdst-group-item"><img class="img-thumbnail rounded mx-auto d-block" src="<?php echo @$post['thumbnail']; ?>" alt=""></td>
                                    <td class="tdst-group-item"><?php echo @$post['pub_date']; ?></td>
                                    <td class="tdst-group-item"><?php echo @$post['saved']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="text-center">
                        <h3><?php echo __('No Links Found', 'wams'); ?></h3>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>