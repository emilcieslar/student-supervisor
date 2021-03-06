<?php

$object = $notification->getObject();
# Get text from the object
$text = DatetimeConverter::getUserFriendlyDateTimeFormat($object->getDatetime());

# If action is not add or cancelled, then we can have larger field for text
if($notification->getAction() != NotificationMeeting::ADDED && $notification->getAction() != NotificationMeeting::CANCELLED)
    $large = 12;
else
    $large = 8;

?>

<li class="notif">
    <div class="large-<?=$large?> columns">
        <?=$notification->getObjectType()?>
        <strong>
            <!-- Display link to the notification object only if it wasn't removed -->
            <?php if(!$notification->getObject()->getIsDeleted()): ?>
                <a class="notification-object" href="<?=$notification->getController()?>/<?=$notification->getObjectId()?>"><?=$text?></a>
            <?php else: ?>
                <?=$text?>
            <?php endif; ?>
        </strong>
        has <?=($notification->getAction() != NotificationMeeting::TAKEN_PLACE) ? 'been' : ''?> <?=$notification::getActionText($notification->getAction())?> by <?=$notification->getUsername()?>
        <br><span class="label info round"><?=DatetimeConverter::getUserFriendlyDateTimeFormat($notification->getDatetimeCreated())?></span>
        <!-- If the meeting is deleted, display a warning -->
        <?php if($notification->getObject()->getIsDeleted()): ?>
            <span class="label alert round">deleted</span>
        <?php endif; ?>
    </div>

    <div class="large-4 columns right">
        <!-- Display options based on action that has been done on the object -->
        <?php

        switch($notification->getAction())
        {
            case NotificationMeeting::ADDED:
            case NotificationMeeting::CANCELLED:
                ?>
                <!-- Display Edit and Approve only if the meeting is not cancelled and removed -->
                <?php if(!$notification->getObject()->getIsCancelled() && !$notification->getObject()->getIsDeleted()): ?>
                    <!-- Display the options only for a supervisor -->
                    <?php if(!HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_STUDENT): ?>
                        <!-- Display edit and approve only if it hasn't been approved -->
                        <?php if(!$notification->getObject()->getIsApproved()): ?>
                            <a href="<?php echo SITE_URL.$notification->getController()."/edit/" . $notification->getObjectId(); ?>" class="button small top-10 right">Edit</a>
                            <a href="<?php echo SITE_URL.$notification->getController()."/approve/" . $notification->getObjectId(); ?>" class="button success small top-10 right">Approve</a>
                        <!-- Otherwise display only edit if it was approved -->
                        <?php else: ?>
                            <a href="<?php echo SITE_URL.$notification->getController()."/edit/" . $notification->getObjectId(); ?>" class="button small top-10 right">Edit</a>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif;
                break;
            default:
                break;
        }

        ?>

    </div>
    <a href="<?=SITE_URL?>notifications/done/<?=$notification->getID()?>"><i class="notif-close fa fa-times"></i></a>
</li>