<?php

$object = $notification->getObject();
# Get text from the object
$text = $object->getText();

# If action is not done or sent_for_approval, then we can have larger field for text
if($notification->getAction() != NotificationAP::SENT_FOR_APPROVAL && $notification->getAction() != NotificationAP::DONE)
    $large = 12;
else
    $large = 8;

?>

<li class="notif">
    <div class="large-<?=$large?> columns">
        <?=$notification->getObjectType()?> <a class="notification-object" href="<?=$notification->getController()?>/<?=$notification->getObjectId()?>"><?=$text?></a>
        has been <?=$notification::getActionText($notification->getAction())?> by <?=$notification->getUsername()?>
        <br><span class="label info round"><?=$notification->getDatetimeCreated()?></span>
    </div>

    <div class="large-4 columns right">
        <!-- Display options based on action that has been done on the object -->
        <?php

        switch($notification->getAction())
        {
            case NotificationAP::SENT_FOR_APPROVAL:
            case NotificationAP::DONE:
                ?>

                <?php if((!$notification->getObject()->getIsApproved() || ($notification->getObject()->getIsApproved() && !$notification->getObject()->getIsDone())) && HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR): ?>
                    <?php if(!$notification->getObject()->getIsApproved()): ?>
                        <a href="<?php echo SITE_URL.$notification->getController()."/approve/" . $notification->getObjectId(); ?>" class="button success small top-10 right">Approve</a>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL.$notification->getController()."/edit/" . $notification->getObjectId(); ?>" class="button small top-10 right">Edit</a>
                <?php endif; ?>
                <?php
                break;
            default:
                break;
        }

        ?>

    </div>
    <i class="notif-close fa fa-times"></i>
</li>