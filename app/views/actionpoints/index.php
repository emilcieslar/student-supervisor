<?php
# Get current date + 7 days
$date = DateTime::createFromFormat('d-m-Y', date("d-m-Y"));
$date->modify('+7 day');
?>

<div class="large-12 columns">
    <h3>Dashboard</h3>
</div>

<div class="large-12 columns dashboard-links">
    <ul class="inline-list">
        <li class="active"><a href="<?=SITE_URL;?>actionpoints">Action Points</a></li>
        <li><a href="#">Notes</a></li>
        <li><a href="<?=SITE_URL;?>meetings">Meetings</a></li>
        <li><a href="#">Notifications</a></li>
    </ul>
</div>

<div class="large-12 columns dashboard-main">

<!-- LIST OF ACTION POINTS -->
<div class="large-8 columns action-points">
    <ul class="action-points">
    <?php foreach ($data['actionpoints'] as $actionpoint): ?>

        <?php if(isset($data['id'])) { if($actionpoint->getID() == $data['id']->getID()) { ?>
            <li class="active"><a href="<?=SITE_URL;?>actionpoints/<?=$actionpoint->getID();?>">
                    <!-- If action point is set to done -->
                    <?php if($actionpoint->getIsDone()): ?>
                        <i class="fa fa-check inline"></i>&nbsp;&nbsp;
                    <?php endif; ?>

                    <?=$actionpoint->getText();?></a>
            </li>
        <?php } else { ?>
            <li><a href="<?=SITE_URL;?>actionpoints/<?=$actionpoint->getID();?>">
                    <!-- If action point is set to done -->
                    <?php if($actionpoint->getIsDone()): ?>
                    <i class="fa fa-check inline"></i>&nbsp;&nbsp;
                    <?php endif; ?>

                    <?=$actionpoint->getText();?></a>
            </li>
        <?php }} else { ?>
            <li><a href="<?=SITE_URL;?>actionpoints/<?=$actionpoint->getID();?>"><?=$actionpoint->getText();?></a></li>
        <?php } ?>

    <?php endforeach; ?>

        <li class="add-new-action-point<?php if(isset($data['add'])) echo ' active';?>"><a class="fa fa-plus" href="<?=SITE_URL;?>actionpoints/add">&nbsp;&nbsp;Add a new action point</a></li>
    </ul>
</div>
<!-- DETAIL OF ACTION POINT -->
<?php if(!isset($data['meetings']) AND !isset($data['edit'])): ?>
<div class="large-4 columns action-point">
    <div class="row action-point-detail">
        <div class="large-12 columns">
            <h3><?=$data['id']->getText();?></h3>
        </div>
        <div class="large-12 columns">
            <i class="fa fa-calendar icon" title="This action point was agreed at this meeting"></i>
            <span id="dp2"><?=DatetimeConverter::getUserFriendlyDateTimeFormat($data['meeting']->getDatetime());?></span>
        </div>

        <div class="large-12 columns">
            <i class="fa fa-thumb-tack icon" title="Deadline"></i>
            <span><?=$data['id']->getDeadlineUserFriendly();?></span>
        </div>

        <?php if($data['id']->getIsDone()): ?>
            <div class="large-12 columns">
                <i class="fa fa-check icon" title="Deadline"></i>
                <span>This action point has been marked as done</span>
            </div>
        <?php endif; ?>

        <?php if(!$data['id']->getIsApproved()): ?>
            <div class="large-12 columns">
                <div class="panel">This action point hasn't been approved yet.</div>
            </div>
        <?php endif; ?>

        <div class="large-12 columns left top-20">
            <a href="<?=SITE_URL;?>actionpoints/edit/<?=$data['id']->getID();?>" class="fa fa-edit button"> Edit</a>
            <a href="<?=SITE_URL;?>actionpoints/remove/<?=$data['id']->getID();?>" class="fa fa-trash-o button alert"></a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- FORM FOR EDITING EXISITNG ACTION POINT -->
<?php if(isset($data['edit'])): ?>

    <div class="large-4 columns action-point">
        <div class="row">
            <div class="large-12 columns">
                <h3>Edit Action Point</h3>
            </div>

            <form action="<?=SITE_URL;?>actionpoints/editPost" method="post" name="addActionPoint">

                <!-- hidden input to tell router that it's a post request -->
                <input name="action" type="hidden">

                <input name="id" type="hidden" value="<?=$data['id']->getID();?>">

                <div class="large-12 columns">
                    <label>Choose deadline:
                        <input name="deadline" placeholder="Choose deadline" type="text" id="dp1" value="<?=$data['datetime']['date'];?>">
                    </label>
                </div>
                <div class="large-6 columns">
                    <select name="deadline_time_hours" placeholder="Choose time">
                        <?php for($i=1;$i<24;$i++): ?>
                            <?php if($data['datetime']['hours'] == $i) { ?>
                                <option value="<?=$i;?>" selected><?=$i;?></option>
                            <?php } else { ?>
                                <option value="<?=$i;?>"><?=$i;?></option>
                            <?php } ?>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="large-6 columns">
                    <select name="deadline_time_minutes" placeholder="Choose time">
                        <?php for($i=0;$i<6;$i++): ?>
                            <?php if($data['datetime']['minutes'] == $i.'0') { ?>
                                <option value="<?=$i.'0';?>" selected><?=$i.'0';?></option>
                            <?php } else { ?>
                                <option value="<?=$i.'0';?>"><?=$i.'0';?></option>
                            <?php } ?>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="large-12 columns">
                    <label>Name the action point:
                        <input name="text" placeholder="Action Point" type="text" value="<?=$data['id']->getText();?>">
                    </label>
                </div>
                <div class="large-12 columns">
                    <label>Associate the action point with a meeting:
                        <select name="meetingId">
                            <option value="0">Choose a meeting</option>
                            <?php foreach($data["meetings"] as $meeting): ?>
                                <?php if($data['id']->getMeetingId() == $meeting->getID()) { ?>
                                    <option value="<?=$meeting->getID();?>" selected><?=DatetimeConverter::getUserFriendlyDateTimeFormat($meeting->getDatetime())?></option>
                                <?php } else { ?>
                                    <option value="<?=$meeting->getID();?>"><?=DatetimeConverter::getUserFriendlyDateTimeFormat($meeting->getDatetime())?></option>
                                <?php } ?>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="large-12 columns">
                    <input name="isDone" id="checkbox1" type="checkbox" <?php if($data['id']->getIsDone()) echo "checked";?>><label for="checkbox1">Is this action point done?</label>
                </div>

                <?php if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR): ?>
                    <!-- If we're logged in as a supervisor -->
                    <div class="large-12 columns">
                        <input name="isApproved" id="checkboxApproved" type="checkbox" <?php if($data['id']->getIsApproved()) echo "checked";?>><label for="checkboxApproved">Approve changes</label>
                    </div>
                <?php endif; ?>
                <div class="large-12 columns top-10">
                    <input class="button" type="submit" name="addActionPoint" value="Save changes">
                </div>
            </form>
        </div><!-- row -->
    </div>

<?php endif; ?>

<!-- FORM FOR ADDING A NEW ACTION POINT -->
<?php if(isset($data['add'])): ?>

    <div class="large-4 columns action-point">
        <div class="row">
        <div class="large-12 columns">
            <h3>Add Action Point</h3>
        </div>

        <form action="<?=SITE_URL;?>actionpoints/add" method="post" name="addActionPoint">

            <!-- hidden input to tell router that it's a post request -->
            <input name="action" type="hidden">

            <div class="large-12 columns">
                <label>Choose deadline:
                    <input name="deadline" placeholder="Choose deadline" type="text" id="dp1">
                </label>
            </div>
            <div class="large-6 columns">
                <select name="deadline_time_hours" placeholder="Choose time">
                    <?php for($i=1;$i<24;$i++): ?>
                        <option value="<?=$i;?>"><?=$i;?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="large-6 columns">
                <select name="deadline_time_minutes" placeholder="Choose time">
                    <?php for($i=0;$i<6;$i++): ?>
                        <option value="<?=$i.'0';?>"><?=$i.'0';?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="large-12 columns">
                <label>Name the action point:
                    <input name="text" placeholder="Action Point" type="text">
                </label>
            </div>
            <div class="large-12 columns">
                <label>Associate the action point with a meeting:
                    <select name="meetingId">
                        <option value="0">Choose a meeting</option>
                        <?php foreach($data["meetings"] as $meeting): ?>
                            <option value="<?=$meeting->getID();?>"><?=DatetimeConverter::getUserFriendlyDateTimeFormat($meeting->getDatetime())?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="large-12 columns top-10">
                <input class="button" type="submit" name="addActionPoint" value="Add">
            </div>
        </form>
        </div><!-- row -->
    </div>

<?php endif; ?>

</div><!-- large-12 columns -->


<!-- DATE PICKER SCRIPT -->
<script type="text/javascript">
    $(document).ready(function() {

        $('#dp1').fdatepicker({
            format: 'dd-mm-yyyy',
            disableDblClickSelection: true,
            closeButton: true
        });

    })
</script>