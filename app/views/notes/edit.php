<form action="<?=SITE_URL;?>notes/editPost" method="post" name="saveNote">

    <input type="hidden" name="id" value="<?=$data['note']->getID()?>">

    <div class="note-wrapper large-12 columns">

        <!-- If it's agenda, we want to go back to agenda, not to notes -->
        <?php $agenda = ($data['note']->getIsAgenda()) ? "/agenda" : ""; ?>

        <a href="<?=SITE_URL?>notes/note/<?=$data['note']->getID()?><?=$agenda?>" class="button small info">&larr;</a>


        <input type="submit" class="button small success" value="Save changes">

        <input type="hidden" name="id" value="<?=$data['note']->getID()?>">

        <!-- display only if it's agenda note being created -->
        <?php if(isset($data['agenda'])): ?>
            <input type="hidden" name="isAgenda" value="1">
        <?php endif; ?>

        <label>Title:
            <!-- display only if it's agenda note being created -->
            <?php if(isset($data['agenda'])): ?>
                <select name="title">
                    <option value="Upcoming Meeting Agenda" <?=($data['note']->getTitle()) == "Upcoming Meeting Agenda" ? "selected" : ""?>>Upcoming meeting agenda</option>
                    <option value="Issues & Problems" <?=($data['note']->getTitle()) == "Issues & Problems" ? "selected" : ""?>>Issues & Problems</option>
                    <option value="Other" <?=($data['note']->getTitle()) == "Other" ? "selected" : ""?>>Other</option>
                </select>
            <?php else: ?>
                <input name="title" placeholder="Meeting notes..." type="text"
                       value="<?php if($data['note']->getTitle()) {echo $data['note']->getTitle();} ?>">
            <?php endif; ?>
        </label>

        <!-- display only if it's not an agenda note being created -->
        <?php if(!isset($data['agenda'])): ?>
            <label>Choose a meeting this note has been taken on:
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
        <?php endif; ?>

        <textarea id="editor1" name="text"><?=$data['note']->getText()?></textarea>
    </div><!-- note-wrapper -->

</form>


<script src="<?=SITE_URL?>app/ckeditor/ckeditor.js"></script>

<script>
    // Replace the <textarea id="editor1"> with a CKEditor
    // instance, using default configuration.
    CKEDITOR.replace( 'editor1' );
</script>