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

    <!-- CREATE A NEW NOTE -->
    <div class="clearfix">
        <div class="large-6 columns note-wrapper">
            <a href="<?=SITE_URL?>notes/create" class="button success small">New note</a>
        </div>

        <!--<div class="large-6 columns note-wrapper">
            <select name="filterByMeeting">
                <option value="0">Filter by a meeting</option>
                <option value="1">Meeting</option>
            </select>
        </div>-->
    </div><!-- clearfix -->

    <!-- If isset delete, display panel to revert delete -->
    <?php if(isset($data['delete'])): ?>
        <div class="panel large-12 columns text-center">
            <a class="button warning tiny" href="<?=SITE_URL?>notes/revertRemoval/<?=$data['delete']?>">Cancel removal</a>
            <a class="button success tiny" href="<?=SITE_URL?>notes">Ok</a><br>
            A note has been removed
        </div>
    <?php endif; ?>

    <!-- DISPLAY ALL THE NOTES -->
    <?php foreach($data['notes'] as $note): ?>

        <a href="<?=SITE_URL?>notes/note/<?=$note->getID()?>">
            <div class="note-wrapper large-4 medium-6 small-12 columns">
                <div class="note">
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
                    <hr>
                    <span class="created">Created by <?=$note->getUsername()?> on <?=DatetimeConverter::getUserFriendlyDateTimeFormat($note->getDatetimeCreated())?></span>
                </div><!-- note -->
            </div>
        </a>

    <?php endforeach; ?>

</div><!-- dashboard-main columns -->

<script type="text/javascript">
    $(document).ready(function() {
        // On select change, filter by a meeting
        $('select[name=filterByMeeting]').change(function() {
            var meetingId = $(this).val();
            var SITE_URL = '<?php echo SITE_URL; ?>';
            window.location = SITE_URL+"notes/meeting/"+meetingId;
        });
    });
</script>