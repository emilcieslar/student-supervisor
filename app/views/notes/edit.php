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

    <form action="<?=SITE_URL;?>notes/editPost" method="post" name="saveNote">

        <input type="hidden" name="id" value="<?=$data['note']->getID()?>">

        <div class="note-wrapper large-12 columns">
            <a href="<?=SITE_URL?>notes/note/<?=$data['note']->getID()?>" class="button small info">&larr;</a>
            <input type="submit" class="button small success" value="Save changes">

            <input type="hidden" name="action" value="post">
            <input type="hidden" name="id" value="<?=$data['note']->getID()?>">

            <label>Title:
                <input name="title" placeholder="Meeting notes..." type="text"
                       value="<?php if($data['note']->getTitle()) {echo $data['note']->getTitle();} ?>">
            </label>

            <label>Associate the note with a meeting:
                <select name="meetingId" required>
                    <option value="0">Choose a meeting</option>
                    <?php foreach($data["meetings"] as $meeting): ?>
                        <?php $selected = ($meeting->getID() == $data['note']->getMeetingId()) ? "selected" : ""; ?>
                        <option value="<?=$meeting->getID();?>" <?=$selected?>><?=DatetimeConverter::getUserFriendlyDateTimeFormat($meeting->getDatetime())?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <?php $checked = ($data['note']->getIsPrivate()) ? "checked" : ""; ?>
            <input name="isPrivate" id="checkbox1" type="checkbox" <?=$checked?>><label for="checkbox1">Private note</label>

            <textarea id="editor1" name="text"><?=$data['note']->getText()?></textarea>
        </div><!-- note-wrapper -->

    </form>

</div><!-- dashboard-main columns -->

<script src="<?=SITE_URL?>app/ckeditor/ckeditor.js"></script>

<script>
    // Replace the <textarea id="editor1"> with a CKEditor
    // instance, using default configuration.
    CKEDITOR.replace( 'editor1' );
</script>