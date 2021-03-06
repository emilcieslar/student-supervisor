<!-- LIST OF NOTIFICATIONS -->
<div class="large-12 columns action-points">
    <ul class="notifications">
        <?php if(empty($data['notifications'])): ?>
            <li>No new notifications to display</li>
        <?php else: ?>
            <?php foreach ($data['notifications'] as $notification): ?>

                <?php

                switch($notification->getObjectType())
                {
                    case "Action Point": include('action_point.php');
                        break;
                    case "Meeting": include('meeting.php');
                        break;
                    case "Note": include('note.php');
                        break;
                    default:
                        break;
                }

                ?>


            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>