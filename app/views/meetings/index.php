
    <div class="large-12 columns">
        <h3>Dashboard</h3>
    </div>

    <div class="large-12 columns dashboard-links">
        <ul class="inline-list">
            <li><a href="<?=SITE_URL;?>actionpoints">Action Points</a></li>
            <li><a href="#">Notes</a></li>
            <li class="active"><a href="<?=SITE_URL;?>meetings">Meetings</a></li>
            <li><a href="#">Notifications</a></li>
        </ul>
    </div>

    <div class="large-12 columns dashboard-main">

        <!-- LIST OF ACTION POINTS -->
        <div class="large-8 columns action-points">
            <ul class="action-points">
                <?php foreach ($data['meetings'] as $meeting): ?>

                    <?php if(isset($data['id'])): ?>

                        <?php if($meeting->getID() == $data['id']->getID()): ?>
                        <li class="active">
                            <a href="<?=SITE_URL;?>meetings/<?=$meeting->getID();?>">
                                <!-- If action point is set to done -->
                                <?php if($meeting->getTakenPlace()): ?>
                                    <i class="fa fa-check inline"></i>&nbsp;&nbsp;
                                <?php endif; ?>

                                <?=$meeting->getDatetimeUserFriendly();?>
                            </a>
                        </li>
                        <?php else: ?>
                        <li>
                            <a href="<?=SITE_URL;?>meetings/<?=$meeting->getID();?>">
                                <!-- If action point is set to done -->
                                <?php if($meeting->getTakenPlace()): ?>
                                    <i class="fa fa-check inline"></i>&nbsp;&nbsp;
                                <?php endif; ?>

                                <?=$meeting->getDatetimeUserFriendly();?>
                            </a>
                        </li>
                        <?php endif; ?>

                    <?php else: ?>
                        <li><a href="<?=SITE_URL;?>meetings/<?=$meeting->getID();?>"><?=$meeting->getDatetimeUserFriendly();?></a></li>
                    <?php endif; ?>

                <?php endforeach; ?>

                <li class="add-new-action-point<?php if(isset($data['add'])) echo ' active';?>"><a class="fa fa-plus" href="<?=SITE_URL;?>meetings/add">&nbsp;&nbsp;Add a new meeting</a></li>
            </ul>
        </div>

        <!-- DETAIL OF ACTION POINT -->
        <?php if(!isset($data['add']) AND !isset($data['edit'])): ?>
        <div class="large-4 columns action-point">
            <div class="row action-point-detail">
                <div class="large-12 columns">
                    <h3><?=$data['id']->getText();?></h3>
                </div>

                <div class="large-12 columns">
                    <i class="fa fa-calendar icon" title="This meeting was scheduled to"></i>
                    <span><?=$data['id']->getDatetimeUserFriendly();?></span>
                </div>

                <?php if($data['id']->getIsRepeating()): ?>
                    <div class="large-12 columns">
                        <i class="fa fa-repeat icon" title="This meeting is repeating every week until"></i>
                        <span id="dp2"><?=$data['id']->getRepeatUntilUserFriendly()?></span>
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

                <?php if(!$data['id']->getIsApproved()): ?>
                    <div class="large-12 columns">
                        <div class="panel">This meeting hasn't been approved yet.</div>
                    </div>
                <?php endif; ?>

                <div class="large-12 columns left top-20">
                    <a href="<?=SITE_URL;?>actionpoints/edit/<?=$data['id']->getID();?>" class="fa fa-edit button"> Edit</a>
                    <a href="<?=SITE_URL;?>actionpoints/remove/<?=$data['id']->getID();?>" class="fa fa-trash-o button alert"></a>
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

                    <form action="<?=SITE_URL;?>meetings/addPost" method="post" name="addMeeting">

                        <!-- hidden input to tell router that it's a post request -->
                        <input name="action" type="hidden">

                        <div class="large-12 columns">
                            <label>Choose date and time:
                                <input name="deadline" placeholder="Choose date and time" type="text" id="dp1">
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

                        <hr>

                        <div class="large-12 columns">
                            <input name="isRepeating" id="checkbox0" type="checkbox"><label for="checkbox0">Shoult this meeting repeat?</label>
                        </div>

                        <div class="large-12 columns">
                            <label>Choose repeat until date:
                                <input name="repeatUntil" placeholder="Choose date" type="text" id="dp2">
                            </label>
                        </div>

                        <hr>

                        <div class="large-12 columns">
                            <input name="isApproved" id="checkbox1" type="checkbox"><label for="checkbox1">Is this meeting approved?</label>
                        </div>

                        <div class="large-12 columns">
                            <input name="arrivedOnTime" id="checkbox2" type="checkbox"><label for="checkbox2">Has student arrived on time?</label>
                        </div>

                        <div class="large-12 columns">
                            <input name="takenPlace" id="checkbox3" type="checkbox"><label for="checkbox3">Has meeting taken place?</label>
                        </div>

                        <div class="large-12 columns top-10">
                            <input class="button" type="submit" name="addMeeting" value="Add">
                        </div>
                    </form>
                </div><!-- row -->
            </div>

        <?php endif; ?>

    </div><!-- dashboard-main -->


    <!-- DATE PICKER SCRIPT -->
    <script type="text/javascript">
        $(document).ready(function() {

            $('#dp1, #dp2').fdatepicker({
                format: 'dd-mm-yyyy',
                disableDblClickSelection: true,
                closeButton: true
            });

        })
    </script>