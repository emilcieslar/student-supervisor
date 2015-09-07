<!-- If delete is set (action point has been just deleted), display panel to revert delete -->
<?php if(isset($data['delete'])): ?>
    <div class="alert-box info large-12 columns text-center">
        <a class="button warning tiny" href="<?=SITE_URL?>actionpoints/revertRemoval/<?=$data['delete']?>">Cancel removal</a>
        <a class="button success tiny" href="<?=SITE_URL?>actionpoints">Ok</a><br>
        Action Point has been removed
    </div>
<?php endif; ?>

<!-- LIST OF ACTION POINTS -->
<div class="large-8 columns action-points">
    <ul class="action-points">
        <!-- Add a new action point button -->
        <li class="add-new-action-point<?php if(isset($data['add'])) echo ' active';?>"><a class="fa fa-plus" href="<?=SITE_URL;?>actionpoints/add">&nbsp;&nbsp;Add a new action point</a></li>

        <!-- If there are action points to display.. -->
        <?php if($data['actionpoints']): ?>
            <!-- Display a list of action points -->
            <?php foreach ($data['actionpoints'] as $actionpoint): ?>

                <!-- Get the ID and decide which one should be selected and displayed (active) -->
                <?php $active = (isset($data['id']) && $actionpoint->getID() == $data['id']->getID()) ? 'class="active"' : ''; ?>

                <!-- Display one line which contains name of the action point with a link to it -->
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

            <?php endforeach; ?>
        <?php endif; ?>

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

        <!-- Meeting this action point has been agreed on -->
        <div class="large-12 columns">
            <span data-tooltip aria-haspopup="true" class="has-tip" title="This action point was agreed at this meeting"><i class="fa fa-calendar icon"></i>
            <span id="dp2"><?=DatetimeConverter::getUserFriendlyDateTimeFormat($data['meeting']->getDatetime());?></span></span>
        </div>

        <!-- Deadline of the action point -->
        <div class="large-12 columns">
            <span data-tooltip aria-haspopup="true" class="has-tip" title="Deadline"><i class="fa fa-thumb-tack icon"></i>
            <span><?=$data['id']->getDeadlineUserFriendly();?></span></span>
        </div>

        <!-- When the action point was marked as done -->
        <?php if($data['id']->getIsDone()): ?>
            <div class="large-12 columns">
                <i class="fa fa-check icon" title="Done"></i>
                <span>This action point has been marked as done on <?=DatetimeConverter::getUserFriendlyDateTimeFormat($data['id']->getDatetimeDone())?></span>
            </div>
        <?php endif; ?>

        <!-- Waiting for approval if not approved yet -->
        <?php if(!$data['id']->getIsApproved()): ?>
            <div class="large-12 columns">
                <div class="panel">This action point is waiting for approval.
                    <?php if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR): ?>
                        <!-- If it's the supervisor, display approve button -->
                        <br><a href="<?=SITE_URL?>actionpoints/approve/<?=$data['id']->getID()?>" class="fa fa-check button tiny success top-10"> Approve</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Set of buttons -->
        <div class="large-12 columns left top-20">
            <?php if(!$data['id']->getIsDone() && $data['id']->getIsApproved()): ?>
                <!-- display only if the action point hasn't been marked as done and has been approved -->
                <!-- done button -->
                <a href="<?=SITE_URL?>actionpoints/done/<?=$data['id']->getID();?>" class="fa fa-check button success"></a>
            <?php endif; ?>

            <?php if((!$data['id']->getIsDone() || !$data['id']->getIsApproved()) && ((HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_STUDENT && !$data['id']->getSentForApproval())
                    || HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR)): ?>
                <!-- display only if the action point hasn't been sent for approval (in case of a student) OR in case of a supervisor display anytime -->
                <!-- also doesn't display to any user when an action point has been set as done and the operation has been approved -->
                <!-- edit button -->
                <a href="<?=SITE_URL?>actionpoints/edit/<?=$data['id']->getID();?>" class="fa fa-edit button"></a>
                <!-- remove button -->
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

                <input name="id" type="hidden" value="<?=$data['id']->getID();?>">

                <div class="large-12 columns">
                    <label>Choose deadline: <small>required</small>
                        <input name="deadline" placeholder="Choose deadline" type="text" id="dp_deadline" value="<?=$data['datetime']['date'];?>" required pattern="date_friendly" readonly>
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
                    <label>This action point has been agreed on a meeting:
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

                <?php if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR && ($data['id']->getIsApproved() || (!$data['id']->getIsApproved() && $data['id']->getIsDone()))): ?>
                    <div class="large-12 columns">
                        <label>Choose a grade: <span class="slider-value"></span></label>
                        <div class="range-slider" data-slider data-options="start: 1; end: 22;">
                            <span class="range-slider-handle" role="slider" tabindex="0"></span>
                            <span class="range-slider-active-segment"></span>
                            <input type="hidden" name="grade">
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Display only if it's waiting for approval and has been set as done (this way a supervisor can disaprove it's been done) -->
                <?php if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR && !$data['id']->getIsApproved() && $data['id']->getIsDone()): ?>
                <div class="large-12 columns">
                    <input name="isDone" id="checkbox1" type="checkbox" <?php if($data['id']->getIsDone()) echo "checked";?>><label for="checkbox1">Is this action point done?</label>
                </div>
                <?php endif; ?>

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

        <form action="<?=SITE_URL;?>actionpoints/addPost" method="post" name="addActionPoint" data-abide>

            <div class="large-12 columns">
                <label>Choose deadline date and time: <small>required</small>
                    <input name="deadline" placeholder="Choose date" type="text" id="dp1" required pattern="date_friendly" readonly>
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
                <label>This action point has been agreed on a meeting: <small>required</small>
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

<?php
# Set value for grade before assigning it in jQuery below
$grade = 0;
if(isset($data['id']))
    $grade = $data['id']->getGrade();
?>

<!-- DATE PICKER SCRIPT -->
<script type="text/javascript">

    $(document).ready(function() {

        // Get current date
        nowTemp = new Date();
        // Advance the date by 7 days
        nowTemp.setDate(nowTemp.getDate() + 7);

        // Run datepicker
        $('#dp1, #dp_deadline').fdatepicker({
            format: 'dd-mm-yyyy',
            disableDblClickSelection: true,
            closeButton: true,
            startDate: '+1d'
        });

        // Set default selected date to current + 7 days
        $('#dp1').fdatepicker("setDate", nowTemp);
        // Update datepicker
        $('#dp1').fdatepicker("update");


        // Slider for setting up a grade
        $('[data-slider]').on('change.fndtn.slider', function(){
            $('.slider-value').text($(this).attr('data-slider'));
        });

        // Set value for a slider that's retrieved from DB
        var new_value = <?=$grade?>;
        if(new_value == 0)
            new_value = 22;
        $('.range-slider').foundation('slider', 'set_value', new_value);

    });

</script>