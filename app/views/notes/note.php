<div class="large-12 columns">
    <h3>Dashboard</h3>
</div>

<div class="large-12 columns dashboard-links">
    <ul class="inline-list">
        <li><a href="<?=SITE_URL;?>actionpoints">Action Points</a></li>
        <li class="active"><a href="<?=SITE_URL;?>notes">Notes</a></li>
        <li><a href="<?=SITE_URL;?>meetings">Meetings</a></li>
        <li><a href="<?=SITE_URL;?>notifications">Notifications</a></li>
    </ul>
</div>

<div class="large-12 columns dashboard-main">

    <div class="note-wrapper large-12 columns">

        <a href="<?=SITE_URL?>notes" class="button small info">&larr;</a>

        <?php if(HTTPSession::getInstance()->GetUserID() == $data['note']->getUserId()): ?>
        <!-- Display option for editing and removing only for the creator of the note -->
            <a href="<?=SITE_URL?>notes/edit/<?=$data['note']->getID()?>" class="fa fa-edit button small"></a>
            <a href="<?=SITE_URL?>notes/remove/<?=$data['note']->getID()?>" class="fa fa-trash-o button small alert"></a>
        <?php endif; ?>

        <div class="note">
            <h4>
                <?php if(!$data['note']->getTitle() && $data['note']->getMeetingId() != 0): ?>
                    Note from meeting of <?=$data['note']->getMeetingDatetime()?>
                <?php elseif(($data['note']->getMeetingId() == 0) && !$data['note']->getTitle()): ?>
                    Untitled note
                <?php else: ?>
                    <?=$data['note']->getTitle()?>
                <?php endif; ?>
                <!-- Is the note private? Display a label -->
                <?php if($data['note']->getIsPrivate()): ?>
                    <span class="label info round">Private</span>
                <?php endif; ?>
            </h4>

            <div><?=$data['note']->getText()?></div>
            <hr>
            <span class="created">Created by <?=$data['note']->getUsername()?> on <?=DatetimeConverter::getUserFriendlyDateTimeFormat($data['note']->getDatetimeCreated())?></span>
        </div><!-- note -->
    </div>

</div><!-- dashboard-main columns -->