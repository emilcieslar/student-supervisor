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
        <li><a href="<?=SITE_URL;?>notifications">Notifications</a></li>
    </ul>
</div>

<div class="large-12 columns dashboard-main">

<!-- LIST OF ACTION POINTS -->
<div class="large-8 columns action-points">
    <ul class="action-points">
    <?php foreach ($data['actionpoints'] as $actionpoint): ?>

        <!-- If it's the action point in the URL or default one -->
        <?php if(isset($data['id'])): ?>

            <!-- get the ID and decide which one should be active -->
            <?php $active = ($actionpoint->getID() == $data['id']->getID()) ? 'class="active"' : ''; ?>

            <!-- display one line which contains name of the action point with a link to it -->
            <li <?=$active?>>
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

        <!-- Otherwise we have no active (add action point selected) -->
        <?php else: ?>
            <li>
                <a href="<?=SITE_URL;?>actionpoints/<?=$actionpoint->getID();?>">
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
        <?php endif; ?>

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
            <?php if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_STUDENT && !$data['id']->getSentForApproval()): ?>
                <!-- Display only to the student (and only if it hasn't been sent yet), it gives the student an option to finalize the action point -->
                <a href="<?=SITE_URL?>actionpoints/send/<?=$data['id']->getID()?>" class="button small warning">Send for approval &rarr;</a>
            <?php endif; ?>
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
                <i class="fa fa-check icon" title="Done"></i>
                <span>This action point has been marked as done</span>
            </div>
        <?php endif; ?>

        <?php if(!$data['id']->getIsApproved()): ?>
            <div class="large-12 columns">
                <div class="panel">This action point hasn't been approved yet.
                    <?php if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR): ?>
                        <!-- If it's the supervisor, display approve button -->
                        <br><a href="<?=SITE_URL?>actionpoints/approve/<?=$data['id']->getID()?>" class="fa fa-check button tiny success top-10"> Approve</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="large-12 columns left top-20">
            <?php if(!$data['id']->getIsDone() && $data['id']->getIsApproved()): ?>
                <!-- display only if the action point hasn't been marked as done and has been approved -->
                <a href="<?=SITE_URL?>actionpoints/done/<?=$data['id']->getID();?>" class="fa fa-check button success"></a>
            <?php endif; ?>

            <?php if((HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_STUDENT && !$data['id']->getSentForApproval()) OR HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR): ?>
                <!-- display only if the action point hasn't been sent for approval (in case of a student) OR in case of a supervisor display anytime -->
                <a href="<?=SITE_URL?>actionpoints/edit/<?=$data['id']->getID();?>" class="fa fa-edit button"></a>
                <a href="<?=SITE_URL?>actionpoints/remove/<?=$data['id']->getID();?>" class="fa fa-trash-o button alert"></a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- FORM FOR EDITING EXISTING ACTION POINT -->
<?php if(isset($data['edit'])): ?>

    <div class="large-4 columns action-point">
        <div class="row">
            <div class="large-12 columns">
                <h3>Edit Action Point</h3>
            </div>

            <form action="<?=SITE_URL;?>actionpoints/editPost" method="post" name="addActionPoint" data-abide>

                <!-- hidden input to tell router that it's a post request -->
                <input name="action" type="hidden">

                <input name="id" type="hidden" value="<?=$data['id']->getID();?>">

                <div class="large-12 columns">
                    <label>Choose deadline: <small>required</small>
                        <input name="deadline" placeholder="Choose deadline" type="text" id="dp1" value="<?=$data['datetime']['date'];?>" required pattern="date_friendly">
                    </label>
                    <small class="error">Incorrect format of deadline</small>
                </div>
                <div class="large-6 small-6 columns">
                    <select name="deadline_time_hours" required>
                        <option value>Choose hour</option>
                        <?php for($i=8;$i<19;$i++): ?>
                            <?php if($data['datetime']['hours'] == $i) { ?>
                                <option value="<?=$i;?>" selected><?=$i;?></option>
                            <?php } else { ?>
                                <option value="<?=$i;?>"><?=$i;?></option>
                            <?php } ?>
                        <?php endfor; ?>
                    </select>
                    <small class="error">Please choose hour</small>
                </div>
                <div class="large-6 small-6 columns">
                    <select name="deadline_time_minutes" required>
                        <option value>Choose minute</option>
                        <?php for($i=0;$i<6;$i++): ?>
                            <?php if($data['datetime']['minutes'] == $i.'0') { ?>
                                <option value="<?=$i.'0';?>" selected><?=$i.'0';?></option>
                            <?php } else { ?>
                                <option value="<?=$i.'0';?>"><?=$i.'0';?></option>
                            <?php } ?>
                        <?php endfor; ?>
                    </select>
                    <small class="error">Please choose minute</small>
                </div>
                <div class="large-12 columns">
                    <label>Name the action point: <small>required</small>
                        <input name="text" placeholder="Action Point" type="text" value="<?=$data['id']->getText();?>" required>
                    </label>
                    <small class="error">Name of the action point is required</small>
                </div>
                <div class="large-12 columns">
                    <label>Associate the action point with a meeting:
                        <select name="meetingId" required>
                            <option value>Choose a meeting</option>
                            <?php foreach($data["meetings"] as $meeting): ?>
                                <?php if($data['id']->getMeetingId() == $meeting->getID()) { ?>
                                    <option value="<?=$meeting->getID();?>" selected><?=DatetimeConverter::getUserFriendlyDateTimeFormat($meeting->getDatetime())?></option>
                                <?php } else { ?>
                                    <option value="<?=$meeting->getID();?>"><?=DatetimeConverter::getUserFriendlyDateTimeFormat($meeting->getDatetime())?></option>
                                <?php } ?>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <small class="error">Every action point has to be agreed on a certain meeting</small>
                </div>

                <div class="large-12 columns">
                    <input name="isDone" id="checkbox1" type="checkbox" <?php if($data['id']->getIsDone()) echo "checked";?>><label for="checkbox1">Is this action point done?</label>
                </div>

                <?php if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR && !$data['id']->getIsApproved()): ?>
                    <!-- If we're logged in as a supervisor and the changes hasn't been approved yet
                         TODO: Do I need this? It will be approved automatically since it's the supervisor who's approving it -->
                    <!--<div class="large-12 columns">
                        <input name="isApproved" id="checkboxApproved" type="checkbox" <?php if($data['id']->getIsApproved()) echo "checked";?>><label for="checkboxApproved">Approve changes</label>
                    </div>-->
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

        <form action="<?=SITE_URL;?>actionpoints/add" method="post" name="addActionPoint" data-abide>

            <!-- hidden input to tell router that it's a post request -->
            <input name="action" type="hidden">

            <div class="large-12 columns">
                <label>Choose deadline date and time: <small>required</small>
                    <input name="deadline" placeholder="Choose date" type="text" id="dp1" required pattern="date_friendly">
                </label>
                <small class="error">Incorrect format of a deadline</small>
            </div>
            <div class="large-6 small-6 columns">
                <select name="deadline_time_hours" required>
                    <option value>Choose hour</option>
                    <?php for($i=8;$i<19;$i++): ?>
                        <option value="<?=$i;?>"><?=$i;?></option>
                    <?php endfor; ?>
                </select>
                <small class="error">Please choose hour</small>
            </div>
            <div class="large-6 small-6 columns">
                <select name="deadline_time_minutes" required>
                    <option value>Choose minute</option>
                    <?php for($i=0;$i<6;$i++): ?>
                        <option value="<?=$i.'0';?>"><?=$i.'0';?></option>
                    <?php endfor; ?>
                </select>
                <small class="error">Please choose minute</small>
            </div>
            <div class="large-12 columns">
                <label>Name the action point: <small>required</small>
                    <input name="text" placeholder="e.g. Draft of chapter 6" type="text" required>
                </label>
                <small class="error">Name of the action point is required</small>
            </div>
            <div class="large-12 columns">
                <label>Associate the action point with a meeting: <small>required</small>
                    <select name="meetingId" required>
                        <option value>Choose a meeting</option>
                        <?php foreach($data["meetings"] as $meeting): ?>
                            <option value="<?=$meeting->getID();?>"><?=DatetimeConverter::getUserFriendlyDateTimeFormat($meeting->getDatetime())?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <small class="error">Every action point has to be agreed on a certain meeting</small>
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

        // Get current date
        nowTemp = new Date();
        // Advance the date by 7 days
        nowTemp.setDate(nowTemp.getDate() + 7);

        // Run datepicker
        $('#dp1').fdatepicker({
            format: 'dd-mm-yyyy',
            disableDblClickSelection: true,
            closeButton: true,
            startDate: '+1d'
        });

        // Set default selected date to current + 7 days
        $('#dp1').fdatepicker("setDate", nowTemp);
        // Update datepicker
        $('#dp1').fdatepicker("update");

    });

</script>