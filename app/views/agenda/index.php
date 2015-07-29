<!-- LIST OF ACTION POINTS -->
<div class="large-6 columns">
    <h5>Action Points to be finished by the next meeting</h5>
    <?php if($data['actionPoints']): ?>
        <ul class="action-points top-10">
            <?php foreach ($data['actionPoints'] as $actionpoint): ?>

                <li>
                    <a href="<?=SITE_URL;?>actionpoints/<?=$actionpoint->getID();?>">
                        <!-- If action point is set to done, display a tick -->
                        <?php if($actionpoint->getIsDone()): ?>
                            <i class="fa fa-check inline"></i>&nbsp;&nbsp;
                        <?php endif; ?>

                        <!-- Display text of the action point -->
                        <?=$actionpoint->getText();?>

                        <!-- If the action point is not approved, display a notice -->
                        <?php if(!$actionpoint->getIsApproved()): ?>
                            &nbsp;<span class="label warning round no-indent">Not approved yet</span>
                        <?php endif; ?>

                        <!-- If the action point has run over deadline, display a notice -->
                        <?php if($actionpoint->hasRunOverDeadline()): ?>
                            &nbsp;<span class="label alert round no-indent">Deadline passed</span>
                        <?php endif; ?>
                    </a>
                </li>

            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="panel top-10">
            <p>There are no Action Points for the next meeting</p>
            <a class="button small" href="<?=SITE_URL?>actionpoints/add">Add a new one</a>
        </div>
    <?php endif; ?>
</div>

<div class="large-6 columns">
    <h5>Your next meeting</h5>
    <div class="panel top-10">
        <?php if($data['nextMeeting']): ?>
            <?=$data['nextMeeting']->getDatetimeUserFriendly()?>
            <br>
            <a class="button small top-10" href="<?=SITE_URL?>meetings/<?=$data['nextMeeting']->getID()?>">Edit meeting</a>
        <?php else: ?>
            <p>You have no schedulled meeting.</p>
            <a class="button small" href="<?=SITE_URL?>meetings/add">Add a new one</a>
        <?php endif; ?>
    </div>
</div>

<hr>


<div class="large-12 columns">

    <h5>Agenda notes for the next meeting</h5>

    <!-- If isset delete, display panel to revert delete -->
    <?php if(isset($data['delete'])): ?>
        <div class="alert-box info large-12 columns text-center">
            <a class="button warning tiny" href="<?=SITE_URL?>notes/revertRemoval/<?=$data['delete']?>/agenda">Cancel removal</a>
            <a class="button success tiny" href="<?=SITE_URL?>agenda">Ok</a><br>
            A note has been removed
        </div>
    <?php endif; ?>

    <!-- DISPLAY ALL THE NOTES -->
    <?php if($data['notes']): ?>
    <?php foreach($data['notes'] as $note): ?>

        <a href="<?=SITE_URL?>notes/note/<?=$note->getID()?>/agenda">
            <div class="note-wrapper large-4 medium-6 small-12 columns">
                <div class="note">
                    <div>
                        <h4>
                            <?php if(!$note->getTitle() && $note->getMeetingId() != 0): ?>
                                Note from meeting of <?=$note->getMeetingDatetime()?>
                            <?php elseif(($note->getMeetingId() == 0) && !$note->getTitle()): ?>
                                Untitled note
                            <?php else: ?>
                                <?=$note->getTitle()?>
                            <?php endif; ?>
                        </h4>
                        <p><?=$note->getExcerpt()?></p>
                    </div>
                    <hr>
                    <span class="created">Created by <?=$note->getUsername()?> on <?=DatetimeConverter::getUserFriendlyDateTimeFormat($note->getDatetimeCreated())?></span>
                </div><!-- note -->
            </div>
        </a>

    <?php endforeach; ?>
    <?php else: ?>
        <p>No notes to display</p>
        <a class="button small" href="<?=SITE_URL?>notes/create/agenda">Add new agenda</a>
    <?php endif; ?>

</div>
