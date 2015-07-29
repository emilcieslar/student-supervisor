<form action="<?=SITE_URL;?>notes/createPost" method="post" name="createPost">

    <div class="note-wrapper large-12 columns">

        <a href="<?=SITE_URL?>notes" class="button small info">&larr;</a>
        <input type="submit" class="button small success" name="save" value="Save changes">

        <input type="hidden" name="action" value="post">

        <!-- display only if it's agenda note being created -->
        <?php if(isset($data['agenda'])): ?>
            <input type="hidden" name="isAgenda" value="1">
        <?php endif; ?>

        <label>Title:
            <!-- display only if it's agenda note being created -->
            <?php if(isset($data['agenda'])): ?>
                <select name="title">
                    <option value="Upcoming Meeting Agenda">Upcoming meeting agenda</option>
                    <option value="Issues & Problems">Issues & Problems</option>
                    <option value="Other">Other</option>
                </select>
            <?php else: ?>
                <input name="title" placeholder="Meeting notes..." type="text">
            <?php endif; ?>
        </label>

        <!-- display only if it's not an agenda note being created -->
        <?php if(!isset($data['agenda'])): ?>
            <label>Associate the note with a meeting:
                <select name="meetingId" required>
                    <option value="0">Choose a meeting</option>
                    <?php foreach($data["meetings"] as $meeting): ?>
                        <option value="<?=$meeting->getID();?>"><?=DatetimeConverter::getUserFriendlyDateTimeFormat($meeting->getDatetime())?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <input name="isPrivate" id="checkbox1" type="checkbox"><label for="checkbox1">Private note</label>
        <?php endif; ?>

        <textarea id="editor1" name="text"></textarea>
    </div><!-- note-wrapper -->

</form>

<script src="<?=SITE_URL?>app/ckeditor/ckeditor.js"></script>

<script>
    // Replace the <textarea id="editor1"> with a CKEditor
    // instance, using default configuration.
    CKEDITOR.replace( 'editor1' );

</script>