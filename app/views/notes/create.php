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

    <form action="<?=SITE_URL;?>notes/createPost" method="post" name="createPost">

        <div class="note-wrapper large-12 columns">

            <a href="<?=SITE_URL?>notes" class="button small info">&larr;</a>
            <input type="submit" class="button small success" name="save" value="Save changes">

            <input type="hidden" name="action" value="post">

            <label>Title:
                <input name="title" placeholder="Meeting notes..." type="text">
            </label>

            <label>Associate the note with a meeting:
                <select name="meetingId" required>
                    <option value="0">Choose a meeting</option>
                    <?php foreach($data["meetings"] as $meeting): ?>
                        <option value="<?=$meeting->getID();?>"><?=DatetimeConverter::getUserFriendlyDateTimeFormat($meeting->getDatetime())?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <input name="isPrivate" id="checkbox1" type="checkbox"><label for="checkbox1">Private note</label>

            <textarea id="editor1" name="text"></textarea>
        </div><!-- note-wrapper -->

    </form>

</div><!-- dashboard-main columns -->

<script src="<?=SITE_URL?>app/ckeditor/ckeditor.js"></script>

<script>
    // Replace the <textarea id="editor1"> with a CKEditor
    // instance, using default configuration.
    CKEDITOR.replace( 'editor1' );

</script>