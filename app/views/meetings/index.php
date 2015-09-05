<!-- If isset delete, display panel to revert delete -->
<?php if(isset($data['delete'])): ?>
    <div class="alert-box info large-12 columns text-center">
        <a class="button warning tiny" href="<?=SITE_URL?>meetings/revertRemoval/<?=$data['delete']?>">Cancel removal</a>
        <a class="button success tiny" href="<?=SITE_URL?>meetings">Ok</a><br>
        Meeting has been removed
    </div>
<?php endif; ?>

<!-- LIST OF MEETINGS -->
<div class="large-8 columns action-points">
    <ul class="action-points">

        <!-- Add a new meeting -->
        <li class="add-new-action-point<?php if(isset($data['add'])) echo ' active';?>"><a class="fa fa-plus" href="<?=SITE_URL;?>meetings/add">&nbsp;&nbsp;Add a new meeting</a></li>

        <!-- If there are any meetings.. -->
        <?php if($data['meetings']): ?>
        <!-- Display a list of meetings -->
        <?php foreach ($data['meetings'] as $meeting): ?>

            <!-- get the ID and decide which one should be active -->
            <?php $active = (isset($data['id']) && $meeting->getID() == $data['id']->getID()) ? 'class="active"' : ''; ?>

            <li <?=$active?>>
                <a href="<?=SITE_URL;?>meetings/<?=$meeting->getID();?>">

                    <!-- If meeting has taken place -->
                    <?php if($meeting->getTakenPlace()): ?>
                        <i class="fa fa-check inline"></i>&nbsp;&nbsp;
                    <?php endif; ?>

                    <!-- Display date and time of the meeting -->
                    <?=$meeting->getDatetimeUserFriendly();?>

                    <!-- If the meeting is not approved, display a notice -->
                    <?php if(!$meeting->getIsApproved()): ?>
                        &nbsp;<span class="label warning round no-indent">Not approved yet</span>
                    <?php endif; ?>

                    <!-- If the student hasn't arrived on meeting (no show), display a notice after a week -->
                    <?php if($meeting->getIsNoShow()): ?>
                        &nbsp;<span class="label alert round no-indent">No show</span>
                    <!-- If it's not a week yet, display just that the meeting has not taken place
                         (to remind user it's waiting for editing that it has actually taken place) -->
                    <?php elseif($meeting->getIsNoShow(false)): ?>
                        &nbsp;<span class="label alert round no-indent">Not taken place yet</span>
                    <?php endif; ?>

                    <!-- If the meeting is the next meeting, display a notice -->
                    <?php if($meeting->getIsNext()): ?>
                        &nbsp;<span class="label info round no-indent">Next meeting</span>
                    <?php endif; ?>

                    <!-- If the meeting has been cancelled, display a notice -->
                    <?php if($meeting->getIsCancelled()): ?>
                        &nbsp;<span class="label alert round no-indent">Cancelled</span>
                    <?php endif; ?>
                </a>
            </li>

        <?php endforeach; ?>
        <?php endif; ?>

    </ul>
</div>

<!-- DETAIL OF MEETING -->
<?php if(!isset($data['add']) AND !isset($data['edit']) AND !isset($data['cancel'])): ?>
<div class="large-4 columns action-point">
    <div class="row action-point-detail">
        <div class="large-12 columns">
            <h3><?=$data['id']->getText();?></h3>
        </div>

        <div class="large-12 columns">
            <span data-tooltip aria-haspopup="true" class="has-tip" title="This meeting was scheduled to"><i class="fa fa-calendar icon"></i>
            <span><?=$data['id']->getDatetimeUserFriendly();?></span></span>
        </div>

        <?php if($data['id']->getIsRepeating()): ?>
            <div class="large-12 columns">
                <span data-tooltip aria-haspopup="true" class="has-tip" title="This meeting is repeating every week until"><i class="fa fa-repeat icon"></i>
                <span><?=$data['id']->getRepeatUntilUserFriendly()?></span></span>
            </div>
        <?php endif; ?>

        <?php if($data['id']->getTakenPlace()): ?>
            <div class="large-12 columns">
                <i class="fa fa-check icon" title="Taken place"></i>
                <span>This meeting has taken place</span>
            </div>
        <?php endif; ?>

        <?php if($data['id']->getArrivedOnTime()): ?>
            <div class="large-12 columns">
                <i class="fa fa-clock-o icon" title="Taken place"></i>
                <span>Student arrived on time</span>
            </div>
        <?php endif; ?>

        <?php if($data['id']->getIsCancelled()): ?>
            <div class="large-12 columns">
                <i class="fa fa-times icon" title="Cancelled"></i>
                <span class="reason-for-cancel">
                    This meeting has been cancelled:<br>
                    <em>"<?=$data['id']->getReasonForCancel()?>"</em>
                </span>
            </div>
        <?php endif; ?>

        <?php if(!$data['id']->getIsApproved()): ?>
            <div class="large-12 columns">
                <div class="panel">
                    This meeting is waiting for approval.
                    <?php if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR): ?>
                        <!-- If it's the supervisor, display approve button -->
                        <br><a href="<?=SITE_URL?>meetings/approve/<?=$data['id']->getID()?>" class="fa fa-check button tiny success top-10"> Approve</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>


        <div class="large-12 columns left top-20">
            <!-- Don't allow to edit or remove if:
                 1. User is a student and this meeting has been approved
                 2. User is a student and this meeting hasn't been approved, however waiting for approval because of cancellation
                 4. Meeting is cancelled and cancellation is approved
                 5. Meeting has taken place
                 6. Meeting is no show -->
            <?php if(!((HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_STUDENT && $data['id']->getIsApproved())
                    || (HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_STUDENT && !$data['id']->getIsApproved() && $data['id']->getIsCancelled())
                    || ($data['id']->getIsCancelled() && $data['id']->getIsApproved())
                    || ($data['id']->getTakenPlace())
                    || $data['id']->getIsNoShow())): ?>

                <a href="<?=SITE_URL;?>meetings/edit/<?=$data['id']->getID();?>" class="fa fa-edit button"></a>
                <a href="<?=SITE_URL;?>meetings/remove/<?=$data['id']->getID();?>" class="fa fa-trash-o button alert"></a>

                <?php if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR): ?>

                <?php endif; ?>

            <?php endif; ?>

            <!-- User can cancel a meeting anytime apart from:
                 1. when it has already taken place
                 2. hasn't been approved yet
                 3. is not already cancelled
                 4. is no show -->
            <?php if(!$data['id']->getTakenPlace() && $data['id']->getIsApproved() && !$data['id']->getIsCancelled() && !$data['id']->getIsNoShow()): ?>
                <a href="<?=SITE_URL;?>meetings/cancel/<?=$data['id']->getID();?>" class="fa fa-times button alert"> Cancel meeting</a>
            <?php endif; ?>

            <?php if($data['id']->getIsNext()): ?>
                <a href="<?=SITE_URL;?>agenda#agenda-notes" class="fa fa-briefcase info button"> Show agenda</a>
            <?php endif; ?>

        </div>

    </div>
</div>
<?php endif; ?>

<!-- FORM FOR ADDING A NEW MEETING -->
<?php if(isset($data['add'])): ?>

    <div class="large-4 columns action-point">
        <div class="row">
            <div class="large-12 columns">
                <h3>Add Meeting</h3>
            </div>

            <form action="<?=SITE_URL;?>meetings/addPost" method="post" name="addMeeting" data-abide>

                <div class="large-12 columns">
                    <label>Choose date and time: <small>required</small>
                        <input name="deadline" placeholder="Choose date" type="text" id="dp1" required pattern="date_friendly" readonly>
                    </label>
                    <small class="error">Incorrect format</small>
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

                <?php if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR): ?>
                    <!-- If we're logged in as a supervisor -->
                    <hr>

                    <div class="large-12 columns">
                        <input name="isRepeating" id="checkbox0" type="checkbox"><label for="checkbox0">Should this meeting repeat?</label>
                    </div>

                    <div class="large-12 columns hide repeatUntil">
                        <label>Choose repeat until date: <small>required</small>
                            <input name="repeatUntil" placeholder="Choose date" type="text" id="dp2" required pattern="date_friendly" readonly>
                        </label>
                        <small class="error">Incorrect format</small>
                    </div>

                    <!-- Don't display isApproved option for supervisor when adding an action point,
                     or should I? TODO: Decide whether to display it or not -->
                    <!--<div class="large-12 columns">
                        <input name="isApproved" id="checkbox1" type="checkbox"><label for="checkbox1">Is this meeting approved?</label>
                    </div>-->

                    <!-- Actually we don't even need options if student has arrived on time or meeting has taken place,
                    since these doesn't make sense when adding a meeting -->
                    <!--<div class="large-12 columns">
                        <input name="arrivedOnTime" id="checkbox2" type="checkbox"><label for="checkbox2">Has student arrived on time?</label>
                    </div>

                    <div class="large-12 columns">
                        <input name="takenPlace" id="checkbox3" type="checkbox"><label for="checkbox3">Has meeting taken place?</label>
                    </div>-->
                <?php endif; ?>

                <div class="large-12 columns top-10">
                    <input class="button" type="submit" name="addMeeting" value="Add">
                </div>
            </form>
        </div><!-- row -->
    </div>

<?php endif; ?>


<!-- FORM FOR EDITING AN EXISTING MEETING -->
<?php if(isset($data['edit'])): ?>

    <div class="large-4 columns action-point">
        <div class="row">
            <div class="large-12 columns">
                <h3>Edit Meeting</h3>
            </div>

            <form action="<?=SITE_URL;?>meetings/editPost" method="post" name="editMeeting" data-abide>

                <input name="id" type="hidden" value="<?=$data['id']->getID()?>">

                <div class="large-12 columns">
                    <label>Choose date and time: <small>required</small>
                        <input name="deadline" placeholder="Choose date and time" type="text" id="dp2" value="<?=$data['datetime']['date']?>" required pattern="date_friendly" readonly>
                    </label>
                    <small class="error">Incorrect format</small>
                </div>

                <div class="large-6 small-6 columns">
                    <select name="deadline_time_hours" placeholder="Choose time" required>
                        <option value>Choose hour</option>
                        <?php for($i=8;$i<19;$i++): ?>
                            <?php if($i==$data['datetime']['hours']): ?>
                                <option value="<?=$i;?>" selected><?=$i;?></option>
                            <?php else: ?>
                                <option value="<?=$i;?>"><?=$i;?></option>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </select>
                    <small class="error">Please choose hour</small>
                </div>
                <div class="large-6 small-6 columns">
                    <select name="deadline_time_minutes" placeholder="Choose time" required>
                        <option value>Choose minute</option>
                        <?php for($i=0;$i<6;$i++): ?>
                            <?php if($i==$data['datetime']['minutes']): ?>
                                <option value="<?=$i.'0';?>" selected><?=$i.'0';?></option>
                            <?php else: ?>
                                <option value="<?=$i.'0';?>"><?=$i.'0';?></option>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </select>
                    <small class="error">Please choose minute</small>
                </div>

                <!--<hr>


                <div class="large-12 columns">
                    <input name="isRepeating" id="checkbox0" type="checkbox" <?=($data['id']->getIsRepeating()) ? "checked" : ""?>><label for="checkbox0">Should this meeting repeat?</label>
                </div>

                <div class="large-12 columns">
                    <label>Choose repeat until date:
                        <input name="repeatUntil" placeholder="Choose date" type="text" id="dp2" value="<?=($data['id']->getIsRepeating()) ? $data['datetime']['dateRepeatUntil'] : ""?>">
                    </label>
                </div>

                <hr>-->

                <?php if(HTTPSession::getInstance()->USER_TYPE == User::USER_TYPE_SUPERVISOR): ?>
                    <!-- If we're logged in as a supervisor -->
                    <!--<div class="large-12 columns">
                        <input name="isApproved" id="checkbox1" type="checkbox" <?=($data['id']->getIsApproved()) ? "checked" : ""?>><label for="checkbox1">Is this meeting approved?</label>
                    </div>-->

                    <!-- Display only if the meeting was in the past -->
                    <?php
                        $thisMeetingDatetime = DateTime::createFromFormat('Y-m-d H:i:s', $data['id']->getDatetime());
                        $timeNow = new DateTime();
                    ?>
                    <?php if($thisMeetingDatetime < $timeNow): ?>

                        <div class="large-12 columns">
                            <input name="arrivedOnTime" id="checkbox2" type="checkbox" <?=($data['id']->getArrivedOnTime()) ? "checked" : ""?>><label for="checkbox2">Has student arrived on time?</label>
                        </div>

                        <div class="large-12 columns">
                            <input name="takenPlace" id="checkbox3" type="checkbox" <?=($data['id']->getTakenPlace()) ? "checked" : ""?>><label for="checkbox3">Has meeting taken place?</label>
                        </div>

                    <?php endif; ?>

                    <?php if($data['id']->getIsCancelled() && !$data['id']->getIsApproved()): ?>
                        <div class="large-12 columns">
                            <input name="isCancelled" id="checkbox4" type="checkbox" <?=($data['id']->getIsCancelled()) ? "checked" : ""?>><label for="checkbox4">Is the meeting cancelled?</label>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="large-12 columns top-10">
                    <input class="button" type="submit" name="editMeeting" value="Save changes">
                </div>
            </form>
        </div><!-- row -->
    </div>

<?php endif; ?>


<!-- FORM FOR CANCELLING A MEETING -->
<?php if(isset($data['cancel'])): ?>

    <div class="large-4 columns action-point">
        <div class="row">
            <div class="large-12 columns">
                <h3>Cancel Meeting</h3>
            </div>

            <?php if(!isset($data['error'])): ?>
                <form action="<?=SITE_URL;?>meetings/cancelPost" method="post" name="cancelMeeting" data-abide>

                    <!-- hidden input with ID -->
                    <input name="id" type="hidden" value="<?=$data['id']->getID()?>">

                    <div class="large-12 columns">
                        <label>Provide a reason: <small>required</small>
                            <textarea name="reason" rows="3" placeholder="Provide a reason..." required></textarea>
                        </label>
                        <small class="error">Please provide a reason</small>
                    </div>

                    <div class="large-12 columns top-10">
                        <input class="button alert" type="submit" name="cancelMeeting" value="Cancel Meeting">
                    </div>
                </form>
            <?php else: ?>
                <div class="large-12 columns top-10">
                    <p>You cannot cancel two meetings in a row</p>
                </div>
            <?php endif; ?>
        </div><!-- row -->
    </div>

<?php endif; ?>


<!-- DATE PICKER SCRIPT -->
<script type="text/javascript">
    $(document).ready(function() {

        // Get current date
        nowTemp = new Date();
        // Advance the date by 7 days
        nowTemp.setDate(nowTemp.getDate() + 1);

        // Display date picker on click of the input
        $('#dp1, #dp2').fdatepicker({
            format: 'dd-mm-yyyy',
            disableDblClickSelection: true,
            closeButton: true,
            startDate: '+1d'
        });

        // Set default selected date to current + 7 days
        $('#dp1').fdatepicker("setDate", nowTemp);
        // Update datepicker
        $('#dp1').fdatepicker("update");

        // Display repeat until input field only if isRepeating input is checked
        $('input[name=isRepeating]').change(function()
        {
            $('.repeatUntil').toggle();
        });

    });

</script>