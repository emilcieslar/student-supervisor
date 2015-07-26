<div class="large-12 columns">
    <h3>Dashboard</h3>
</div>

<div class="large-12 columns dashboard-links">
    <ul class="inline-list">
        <li><a href="<?=SITE_URL;?>actionpoints">Action Points</a></li>
        <li><a href="<?=SITE_URL;?>notes">Notes</a></li>
        <li><a href="<?=SITE_URL;?>meetings">Meetings</a></li>
        <li class="active"><a href="<?=SITE_URL;?>notifications">Notifications</a></li>
    </ul>
</div>

<div class="large-12 columns dashboard-main">

    <!-- LIST OF NOTIFICATIONS -->
    <div class="large-12 columns action-points">
        <ul class="notifications">
            <?php if(empty($data['notifications'])): ?>
                <li>No new notifications to display</li>
            <?php else: ?>
                <?php foreach ($data['notifications'] as $notification): ?>

                    <li>
                        <div class="large-6 columns">
                            <a class="notification-object" href="<?=$notification->getController()?>/<?=$notification->getObjectId()?>"><?=$notification->getObjectType()?></a>
                            has been <?=$notification->getAction()?> by <?=$notification->getUsername()?>
                            <br><span class="label info round"><?=$notification->getDatetimeCreated()?></span>
                        </div>

                        <div class="large-6 columns right">
                            <?php if(!$notification->getObject()->getIsApproved()): ?>
                                <a href="<?php echo SITE_URL.$notification->getController()."/approve/" . $notification->getObjectId(); ?>" class="button success small top-10 right">Approve</a>
                            <?php endif; ?>
                            <a href="<?php echo SITE_URL.$notification->getController()."/edit/" . $notification->getObjectId(); ?>" class="button small top-10 right">Edit</a>
                        </div>
                    </li>

                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>