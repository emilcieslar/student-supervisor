<!-- ACTION POINTS AND MEETINGS STATISTICS + RAG -->
<div class="large-12 columns agenda-statistics">
    <h5>Red amber green status</h5>

    <div class="large-6 medium-6 columns">
        <h6>Action Points</h6>

        <div class="large-6 columns">
            <div class="number">
                <span class="number"><?=$data['rag']->getActionPointsToBeDone()?></span>
                <span class="text">to be done</span>
            </div>
        </div>

        <div class="large-6 columns">
            <div class="number">
                <span class="number"><?=$data['rag']->getActionPointsRunningOverDeadline()?></span>
                <span class="text">running over<br>deadline</span>
            </div>
        </div>

        <div class="large-6 columns">
            <div class="number">
                <span class="number"><?=$data['rag']->getActionPointsFinished()?></span>
                <span class="text">finished</span>
            </div>
        </div>

        <div class="large-6 columns">
            <div class="number">
                <span class="number"><?=$data['rag']->getActionPointsFinishedAfterDeadline()?></span>
                <span class="text">finished after<br>deadline</span>
            </div>
        </div>

        <div class="large-6 columns left">
            <div class="number">
                <span class="number"><?=$data['rag']->getActionPointsAvgGrade()?></span>
                <span class="text">average grade</span>
            </div>
        </div>

    </div>

    <div class="large-6 medium-6 columns">
        <h6>Meetings</h6>

        <div class="large-6 columns">
            <div class="number">
                <span class="number"><?=$data['rag']->getMeetingsTakenPlace()?></span>
                <span class="text">taken place</span>
            </div>
        </div>

        <div class="large-6 columns">
            <div class="number">
                <span class="number"><?=$data['rag']->getMeetingsStudentArrivedOnTime()?></span>
                <span class="text">student arrived<br>on time</span>
            </div>
        </div>

        <div class="large-6 columns">
            <div class="number">
                <span class="number"><?=$data['rag']->getMeetingsCancelled()?></span>
                <span class="text">cancelled</span>
            </div>
        </div>

        <div class="large-6 columns">
            <div class="number">
                <span class="number"><?=$data['rag']->getMeetingsNoShow()?></span>
                <span class="text">no show</span>
            </div>
        </div>

        <div class="large-6 columns left">
            <div class="number">
                <span class="number"><?=$data['rag']->getMeetingsTotal()?></span>
                <span class="text">in total</span>
            </div>
        </div>
    </div>

    <?php
        $status = $data['rag']->getStatus();
    ?>

    <div class="large-12 columns top-20">
        <h6 class="text-center">Project is running on</h6>
        <div class="rag-status">
            <span><?=$status['percentage']?>%</span>
            <div class="rag-status-p" style="width: <?=$status['percentage']?>%; background: <?=$status['color']?>"></div>
        </div>
    </div>
</div>

<hr>

<!-- NEXT MEETING -->
<div class="large-6 columns">
    <h5>Your next meeting</h5>
    <div class="panel top-10">
        <?php if($data['nextMeeting']): ?>
            <?=$data['nextMeeting']->getDatetimeUserFriendly()?>
            <br>
            <a class="button small top-10" href="<?=SITE_URL?>meetings/<?=$data['nextMeeting']->getID()?>">Edit meeting</a>
        <?php else: ?>
            <p>You have no schedulled meeting</p>
            <a class="button small" href="<?=SITE_URL?>meetings/add">Add a new one</a>
        <?php endif; ?>
    </div>
</div>

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
            <p>You have no Action Points for the next meeting</p>
            <a class="button small" href="<?=SITE_URL?>actionpoints/add">Add a new one</a>
        </div>
    <?php endif; ?>
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

    <!-- Display button to create new agenda and all the notes only if there's a next meeting scheduled -->
    <?php if($data['nextMeeting']): ?>
        <div class="clearfix">
            <a class="button small" href="<?=SITE_URL?>notes/create/agenda">Add new agenda</a>
        </div>

        <!-- Display all the notes -->
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
            <p>You have no agenda notes for the next meeting</p>
        <?php endif; ?>

    <?php else: ?>
        <p>You have no meeting scheduled, therefore you cannot add any agenda notes at the moment</p>
    <?php endif; ?>

</div>
