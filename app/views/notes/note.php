<div class="note-wrapper large-12 columns">

    <!-- If it's agenda, we want to go back to agenda, not to notes -->
    <?php $agenda = ($data['note']->getIsAgenda()) ? "agenda" : "notes"; ?>

    <a href="<?=SITE_URL?><?=$agenda?>" class="button small info">&larr;</a>

    <?php if(HTTPSession::getInstance()->GetUserID() == $data['note']->getUserId()): ?>
    <!-- Display option for editing and removing only for the creator of the note -->
        <!-- If it's agenda, we have to add /agenda to the url -->
        <?php $agenda = ($data['note']->getIsAgenda()) ? "/agenda" : ""; ?>
        <a href="<?=SITE_URL?>notes/edit/<?=$data['note']->getID()?><?=$agenda?>" class="fa fa-edit button small"></a>
        <a href="<?=SITE_URL?>notes/remove/<?=$data['note']->getID()?><?=$agenda?>" class="fa fa-trash-o button small alert"></a>
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
