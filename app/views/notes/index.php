<!-- CREATE A NEW NOTE -->
<div class="clearfix">
    <div class="large-6 columns note-wrapper">
        <a href="<?=SITE_URL?>notes/create" class="button success small">New note</a>
    </div>


    <div class="large-6 columns note-wrapper">
        <div class="row collapse">
            <div class="large-2 columns">
                <!-- Display only if there's a filter applied -->
                <?php if(isset($data['meeting'])): ?>
                    <a href="<?=SITE_URL?>notes" class="button tiny fa fa-times warning postfix"></a>
                <?php endif; ?>
            </div>
            <div class="large-10 columns">
                <select name="filterByMeeting">
                    <option value="0">Filter by a meeting</option>
                    <?php foreach($data['meetings'] as $meeting): ?>
                        <?php $selected = ($meeting->getID() == $data['meeting']) ? "selected" : ""; ?>
                        <option value="<?=$meeting->getID()?>" <?=$selected?>><?=$meeting->getDatetimeUserFriendly()?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div><!-- clearfix -->

<!-- If isset delete, display panel to revert delete -->
<?php if(isset($data['delete'])): ?>
    <div class="alert-box info large-12 columns text-center">
        <a class="button warning tiny" href="<?=SITE_URL?>notes/revertRemoval/<?=$data['delete']?>">Cancel removal</a>
        <a class="button success tiny" href="<?=SITE_URL?>notes">Ok</a><br>
        A note has been removed
    </div>
<?php endif; ?>

<!-- DISPLAY ALL THE NOTES -->
<?php if($data['notes']): ?>
<?php foreach($data['notes'] as $note): ?>

    <a href="<?=SITE_URL?>notes/note/<?=$note->getID()?>">
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
<?php endif; ?>


<script type="text/javascript">
    $(document).ready(function() {
        // On select change, filter by a meeting
        $('select[name=filterByMeeting]').change(function() {
            // Get site_url from PHP
            var SITE_URL = '<?php echo SITE_URL; ?>';

            // Perform only if the first option wasn't selected
            if($(this).val() != 0) {
                var meetingId = $(this).val();
                window.location = SITE_URL + "notes/meeting/" + meetingId;
            } else {
                window.location = SITE_URL + "notes";
            }
        });
    });
</script>