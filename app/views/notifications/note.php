<?php

$object = $notification->getObject();
# Get text from the object
$text = $object->getTitle();

?>

<li class="notif">
    <div class="large-12 columns">
        <?=$notification->getObjectType()?>
        <strong>
            <!-- Display link to the notification object only if it wasn't removed -->
            <?php if(!$notification->getObject()->getIsDeleted()): ?>
                <a class="notification-object" href="<?=$notification->getController()?>/note/<?=$notification->getObjectId()?>"><?=$text?></a>
            <?php else: ?>
                <?=$text?>
            <?php endif; ?>
        </strong>
        has been <?=$notification::getActionText($notification->getAction())?> by <?=$notification->getUsername()?>
        <br><span class="label info round"><?=DatetimeConverter::getUserFriendlyDateTimeFormat($notification->getDatetimeCreated())?></span>
        <!-- If the note is deleted, display a warning -->
        <?php if($notification->getObject()->getIsDeleted()): ?>
            <span class="label alert round">deleted</span>
        <?php endif; ?>
    </div>

    <a href="<?=SITE_URL?>notifications/done/<?=$notification->getID()?>"><i class="notif-close fa fa-times"></i></a>
</li>