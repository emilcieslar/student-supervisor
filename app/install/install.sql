DROP TABLE IF EXISTS User;
DROP TABLE IF EXISTS Project;
DROP TABLE IF EXISTS UserProject;
DROP TABLE IF EXISTS Meeting;
DROP TABLE IF EXISTS MeetingTemp;
DROP TABLE IF EXISTS ActionPoint;
DROP TABLE IF EXISTS ActionPointTemp;
DROP TABLE IF EXISTS Note;
DROP TABLE IF EXISTS Notification;
DROP TABLE IF EXISTS http_session;
DROP TABLE IF EXISTS session_variable;

CREATE TABLE User (
  id INT(11) AUTO_INCREMENT NOT NULL,
  username VARCHAR(25) NOT NULL,
  password VARCHAR(32) NULL,
  first_name VARCHAR(25) NOT NULL,
  last_name VARCHAR(25) NOT NULL,
  type INT(1) DEFAULT 0,
  email VARCHAR(50) NOT NULL UNIQUE,
  is_deleted BOOLEAN DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

CREATE TABLE Project (
  id INT(11) AUTO_INCREMENT NOT NULL,
  short_name VARCHAR(10) NOT NULL,
  name VARCHAR(50) NOT NULL,
  datetime_created DATETIME DEFAULT NOW(),
  description TEXT NOT NULL,
  is_deleted BOOLEAN DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=MyISAM;

CREATE TABLE UserProject (
  user_id INT(11) NOT NULL,
  project_id INT(11) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES User(id),
  FOREIGN KEY (project_id) REFERENCES Project(id)
) ENGINE=MyISAM;

CREATE TABLE Meeting (
  id int(11) AUTO_INCREMENT NOT NULL,
  datetime DATETIME NOT NULL,
  is_repeating BOOLEAN DEFAULT 0,
  repeat_until DATETIME NOT NULL,
  is_approved BOOLEAN DEFAULT 0,
  is_cancelled BOOLEAN DEFAULT 0,
  reason_for_cancel VARCHAR(255) DEFAULT '',
  taken_place BOOLEAN DEFAULT 0,
  arrived_on_time BOOLEAN DEFAULT 0,
  google_event_id VARCHAR(255) DEFAULT '',
  project_id INT(11) NOT NULL,
  is_deleted BOOLEAN DEFAULT 0,
  FOREIGN KEY (project_id) REFERENCES Project(id),
  PRIMARY KEY (id)
) ENGINE=MyISAM;

CREATE TABLE MeetingTemp (
  id int(11) NOT NULL,
  datetime DATETIME NOT NULL,
  is_repeating BOOLEAN DEFAULT 0,
  repeat_until DATETIME NOT NULL,
  is_approved BOOLEAN DEFAULT 0,
  is_cancelled BOOLEAN DEFAULT 0,
  reason_for_cancel VARCHAR(255) DEFAULT '',
  taken_place BOOLEAN DEFAULT 0,
  arrived_on_time BOOLEAN DEFAULT 0,
  google_event_id VARCHAR(255) DEFAULT '',
  project_id INT(11) NOT NULL,
  is_deleted BOOLEAN DEFAULT 0,
  FOREIGN KEY (project_id) REFERENCES Project(id),
  PRIMARY KEY (id)
) ENGINE=MyISAM;

CREATE TABLE ActionPoint (
  id INT(11) AUTO_INCREMENT NOT NULL,
  deadline DATETIME,
  datetime_created DATETIME DEFAULT NOW(),
  text VARCHAR(255) NOT NULL,
  is_approved BOOLEAN DEFAULT 0,
  is_done BOOLEAN DEFAULT 0,
  sent_for_approval BOOLEAN DEFAULT 0,
  datetime_done DATETIME DEFAULT 0,
  grade INT(1) NOT NULL DEFAULT 0,
  meeting_id int(11) NOT NULL DEFAULT 0,
  user_id int(11) NOT NULL DEFAULT 0,
  project_id INT(11) NOT NULL DEFAULT 0,
  is_deleted BOOLEAN DEFAULT 0,
  FOREIGN KEY (meeting_id) REFERENCES Meeting(id),
  FOREIGN KEY (user_id) REFERENCES User(id),
  FOREIGN KEY (project_id) REFERENCES Project(id),
  PRIMARY KEY (id)
) ENGINE=MyISAM;

CREATE TABLE ActionPointTemp (
  id INT(11)  NOT NULL,
  deadline DATETIME,
  datetime_created DATETIME DEFAULT NOW(),
  text VARCHAR(255) NOT NULL,
  is_approved BOOLEAN DEFAULT 0,
  is_done BOOLEAN DEFAULT 0,
  sent_for_approval BOOLEAN DEFAULT 0,
  datetime_done DATETIME DEFAULT 0,
  grade INT(1) NOT NULL DEFAULT 0,
  meeting_id int(11) NOT NULL DEFAULT 0,
  user_id int(11) NOT NULL DEFAULT 0,
  project_id INT(11) NOT NULL DEFAULT 0,
  is_deleted BOOLEAN DEFAULT 0,
  FOREIGN KEY (meeting_id) REFERENCES Meeting(id),
  FOREIGN KEY (user_id) REFERENCES User(id),
  FOREIGN KEY (project_id) REFERENCES Project(id),
  PRIMARY KEY (id)
) ENGINE=MyISAM;

CREATE TABLE Note (
  id INT(11) AUTO_INCREMENT NOT NULL,
  text TEXT NOT NULL,
  title VARCHAR(255) DEFAULT '',
  is_agenda BOOLEAN DEFAULT 0,
  is_private BOOLEAN DEFAULT 0,
  datetime_created DATETIME DEFAULT NOW(),
  meeting_id INT(11) NOT NULL DEFAULT 0,
  user_id INT(11) NOT NULL DEFAULT 0,
  project_id INT(11) NOT NULL DEFAULT 0,
  is_deleted BOOLEAN DEFAULT 0,
  FOREIGN KEY (meeting_id) REFERENCES Meeting(id),
  FOREIGN KEY (user_id) REFERENCES User(id),
  FOREIGN KEY (project_id) REFERENCES Project(id),
  PRIMARY KEY (id)
) ENGINE=MyISAM;

CREATE TABLE Notification (
  id INT(11) AUTO_INCREMENT NOT NULL,
  datetime_created DATETIME DEFAULT NOW(),
  is_done BOOLEAN DEFAULT 0,
  controller VARCHAR(50) NOT NULL DEFAULT '',
  object_type VARCHAR(50) NOT NULL DEFAULT '',
  object_id INT(11) NOT NULL DEFAULT 0,
  action INT(1) NOT NULL DEFAULT 0,
  project_id INT(11) NOT NULL,
  reason_for_action VARCHAR(255) NOT NULL DEFAULT '',
  creator_user_id INT(11) NOT NULL DEFAULT 0,
  is_deleted BOOLEAN DEFAULT 0,
  PRIMARY KEY (id)
);

# Schema for session management
CREATE TABLE http_session (
  id INT(11) AUTO_INCREMENT NOT NULL,
  ascii_session_id VARCHAR(32),
  logged_in BOOLEAN,
  user_id INT(11),
  last_impression timestamp,
  created timestamp,
  user_agent  VARCHAR(256),
  PRIMARY KEY(id)
);

CREATE TABLE session_variable (
  id INT(11) AUTO_INCREMENT NOT NULL,
  session_id INT(11),
  variable_name VARCHAR(64),
  variable_value TEXT,
  PRIMARY KEY(id)
);

###################### LET'S CREATE SOME DATA #########################

######### USERS
INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('emil','955db0b81ef1989b4a4dfeae8061a9a6','Emil','Cieslar',0,'emil.cieslar@gmail.com');
INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('rose','955db0b81ef1989b4a4dfeae8061a9a6','Rosanne','English',1,'cieslaremil@gmail.com');
INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('rose_gmail','955db0b81ef1989b4a4dfeae8061a9a6','Rosanne','English',1,'rosanneenglish@gmail.com');
INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('joe','955db0b81ef1989b4a4dfeae8061a9a6','Joe','Doe',0,'cieslar@webkreativ.cz');

######### PROJECTS
INSERT INTO Project(short_name, name,description) VALUES('SSMS', 'Student Supervisor Management System','Web-based application with a database back end which allows a supervisor to monitor the progress of their student and the students to engage with the supervisor');
INSERT INTO Project(short_name, name,description) VALUES('TRS','Online Table Reservation System','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut leo lorem, blandit vitae turpis non, fermentum faucibus massa. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur dignissim, massa ac bibendum maximus, velit tortor lobortis libero, at congue ex lorem nec erat. Ut vel lobortis purus. Proin sit amet risus consequat, varius diam vehicula, iaculis est.');

######### ASSIGNING USERS TO PROJECTS
INSERT INTO UserProject(user_id,project_id) VALUES(1,1);
INSERT INTO UserProject(user_id,project_id) VALUES(2,1);

INSERT INTO UserProject(user_id,project_id) VALUES(2,2);
INSERT INTO UserProject(user_id,project_id) VALUES(4,2);

SET @now_meeting = CONCAT(CURRENT_DATE(),' 16:00:00');

######### MEETINGS
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
    VALUES(@now_meeting - INTERVAL 42 DAY,0,0,1,1,1,1);
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
    VALUES(@now_meeting - INTERVAL 35 DAY,0,0,1,1,1,1);

# One cancelled meeting
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id, is_cancelled, reason_for_cancel)
VALUES(@now_meeting - INTERVAL 32 DAY,0,0,1,0,0,1,1,'Holiday');

INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
    VALUES(@now_meeting - INTERVAL 28 DAY,0,0,1,1,0,1);
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
    VALUES(@now_meeting - INTERVAL 21 DAY,1,@now_meeting + INTERVAL 21 DAY,1,1,1,1);
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
    VALUES(@now_meeting - INTERVAL 14 DAY,1,@now_meeting + INTERVAL 21 DAY,1,1,1,1);
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
    VALUES(@now_meeting - INTERVAL 7 DAY,1,@now_meeting + INTERVAL 21 DAY,1,1,1,1);
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
    VALUES(@now_meeting + INTERVAL 1 DAY,1,@now_meeting + INTERVAL 21 DAY,1,0,0,1);

# One not repeating meeting and not approved (requested by a student)
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
VALUES(@now_meeting + INTERVAL 9 DAY,0,0,0,0,0,1);

INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
    VALUES(@now_meeting + INTERVAL 7 DAY,1,@now_meeting + INTERVAL 21 DAY,1,0,0,1);
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
    VALUES(@now_meeting + INTERVAL 14 DAY,1,@now_meeting + INTERVAL 21 DAY,1,0,0,1);
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
    VALUES(@now_meeting + INTERVAL 21 DAY,1,@now_meeting + INTERVAL 21 DAY,1,0,0,1);


######### ACTION POINTS
# First week
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting - INTERVAL 35 DAY,@now_meeting - INTERVAL 42 DAY,'Questionnaire',1,1,1,19,1,1,1);
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting - INTERVAL 35 DAY,@now_meeting - INTERVAL 42 DAY,'Requirements draft (User stories, MoSCoW)',1,1,1,21,1,1,1);
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting - INTERVAL 35 DAY,@now_meeting - INTERVAL 42 DAY,'What am I going to implement',1,1,1,20,1,1,1);

# Second week
INSERT INTO ActionPoint(deadline, datetime_created, datetime_done, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting - INTERVAL 28 DAY,@now_meeting - INTERVAL 35 DAY, @now_meeting - INTERVAL 22 DAY,'First version of UML',1,1,1,19,2,1,1);
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting - INTERVAL 28 DAY,@now_meeting - INTERVAL 35 DAY,'First iteration',1,1,1,21,2,1,1);

# Third week
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting - INTERVAL 21 DAY,@now_meeting - INTERVAL 28 DAY,'Second iteration',1,1,1,21,3,1,1);

# Forth week
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting - INTERVAL 14 DAY,@now_meeting - INTERVAL 21 DAY,'Start writing the introduction',1,1,1,19,4,1,1);
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting - INTERVAL 14 DAY,@now_meeting - INTERVAL 21 DAY,'Implement discussed Action Point functionality',1,0,1,21,4,1,1);
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting - INTERVAL 14 DAY,@now_meeting - INTERVAL 21 DAY,'Update User stories with progress',1,1,1,20,4,1,1);
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting - INTERVAL 14 DAY,@now_meeting - INTERVAL 21 DAY,'Third iteration',1,1,1,20,4,1,1);

# Fifth week
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting - INTERVAL 7 DAY,@now_meeting - INTERVAL 14 DAY,'Update of the introduction',1,1,1,19,5,1,1);
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting - INTERVAL 7 DAY,@now_meeting - INTERVAL 14 DAY,'Forth iteration',1,1,1,21,5,1,1);
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting - INTERVAL 7 DAY,@now_meeting - INTERVAL 14 DAY,'Write test cases',1,0,1,20,5,1,1);

# Sixth week
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting + INTERVAL 1 DAY,@now_meeting - INTERVAL 7 DAY,'Fifth iteration: agenda',1,0,1,0,6,1,1);
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting + INTERVAL 1 DAY,@now_meeting - INTERVAL 7 DAY,'Meeting filtering',1,0,1,0,6,1,1);


######### NOTES
# Texts
SET @note1 = "<ol><li><p>Rose is allocated a student for a project</p></li><li><p>She sends him/her an email that informs the student that Rose is his/her project supervisor and that they need to set up a meeting<br />- meetings are set up using email<br />- Rose uses gmail calendar to manage meetings<br />- If the system had it&rsquo;s own meeting management module, Rose would check it daily to see what the student is up to, when is the next meeting, &hellip; &ndash;&gt; but it would be nice to add meetings automatically to google calendar [NICE TO HAVE] (app could possibly use google login which would make a lot of things easier)</p></li><li><p>Rose sets up a meeting with the student and the first meeting is a an overview of the masters project</p></li><li><p>In the meeting, Rose and the student agree on some action points that have to be done until the next time<br />For example:<br />- by this Friday, write up a requirements document<br />- writing up a specific chapter<br />- summarization of some literature<br />- implementation of a functionality</p></li><li><p>At the end of the meeting, there should be a list of points a student has to do by the next time<br />- if it&rsquo;s writing or similar, Rose would like to look at it before the following meeting (which means system could provide a functionality to share document &ndash;&gt; Google Drive?) and set a deadline which is closer than the next meeting<br />- Studentsupervisor.com has outcomes (and suboutcomes), Rose is not interested in such functionality, she just wants to know a list of tasks that have been agreed on the meeting<br />- at the end of the meeting, student should say, here&rsquo;s what we&rsquo;ve talked about and these are the action points; Rose will approve them or adjust them (sometimes there is a miscommunication)<br />- these actions points should be added to the system by the STUDENT (and will be shared with the supervisor)<br />- action points must have deadlines<br />- don&rsquo;t have to contain attachments (however we have to have documents to upload somewhere for the next meeting for Rose to check before the meeting)<br />- action points are very short (implement functionality 1;&nbsp;write chapter 2)<br />- Rose should be able to say whether these are the action points that they agreed on<br />- that means AGREE or AMEND them (when they are amended, they should flag up for student to see that this has been amended)<br />- each action point could be &ldquo;graded&rdquo; according to how it was accomplished (and this could be used for overview of the whole progress - red amber green)</p></li><li><p>During the meeting, Rose is taking notes as well (it would be NICE to have this on the system), she&rsquo;s just writing the notes on paper at the moment (doesn&rsquo;t use any cloud notes)<br />- Notes would be related to the particular student and a meeting as well in order to for supervisor see what has been discussed on a particular meeting (these notes could be sorted by date so it&rsquo;s easy to trace back what&rsquo;s happened)</p></li><li><p>Student should have their private notes (not shared with a supervisor) in the system as well (because a student should take notes during the meeting)</p></li><li><p>Agreeing on the meetings should be done through the system<br />- for example if the student wants to cancel the meeting (student cannot cancel two meetings in a row &ndash;&gt;&nbsp;system should show a warning when the student tries to do that);&nbsp;if there&rsquo;s nothing they want to talk about they can cancel<br />- initially, there should be a set up for a date and time for a regular meeting (this will be agreed on the first meeting and Rose will upload the regular meeting time afterwards)<br />- student can also request a different date and time different from the regular meeting time (so guess there could be a regular meeting time displayed at student page and when student clicks on it, a menu pops up with couple of options to change the meeting or cancel it); this request will be sent to Rose and she has to approve it</p></li><li><p>Scheduling system<br />- it&rsquo;s basically the action points that have deadlines (we pick a task and say when it&rsquo;s due)<br />- when the student finishes the action points, he ticks that he&rsquo;s finished<br />- in the next meeting, Rose will actually have a look at what he&rsquo;s done and review it (she can either say it&rsquo;s ok, or not ok and remove the finished flag off)</p></li><li><p>Current system<br />- current system just enables supervisor to tick whether the supervisor had a meeting with a student or not</p></li><li><p>Professionalism<br />- according to the Gethin Norman presentation, conduct is 10% of the mark<br />- visualisation of the progress of the student (Red, Amber, Green)<br />Green - turning up for meetings (regular enough), student meets action points and deadlines<br />- Rose should be able to override that if she thinks that doesn&rsquo;t really meets the reality</p></li><li><p>Before the meeting<br />- Rose ask a student to email her and say what it is they want to talk about<br />- it gives her an idea what student has been doing<br />- it also gives her time to clear if there are certain things to look up<br />- This should be in a little section on the dashboard<br />- your next meeting is at this date and time...<br />- the action points for that meeting are&hellip;<br />- progress of these action points is&hellip;<br />- agenda for the meeting is&hellip; (what they want to discuss, issues, ...)</p></li><li><p>Methodology<br />- agile<br />- figure out user stories (must, should, could, would)<br />- next sprint (what user stories will be in the next prototype, &hellip;)<br />- how long will be the sprints (2 weeks)<br />&nbsp;</p></li></ol><p><strong>FOR THE NEXT WEEK</strong></p><p>- Questionnaire</p><p>- Requirements draft (user stories, MoSCoW)</p><p>- What am I going to implement</p>";


INSERT INTO Note(text, is_agenda, datetime_created, meeting_id, user_id, project_id)
    VALUES(@note1,0,NOW(),1,1,1);
INSERT INTO Note(text, is_agenda, datetime_created, meeting_id, user_id, project_id)
    VALUES("<p>Donec commodo odio in molestie tincidunt. Etiam sit amet facilisis arcu, ac fringilla risus. Praesent eget elit at sapien vulputate euismod ac a arcu. Phasellus lacus ante, finibus nec dolor non, mollis hendrerit sem. Cras dictum sed felis et tincidunt. Curabitur ullamcorper rhoncus nisi id blandit. Fusce dolor risus, elementum in augue vitae, ultrices rutrum lorem. Etiam gravida volutpat urna in porttitor. Mauris condimentum dolor dolor, et facilisis velit dictum vitae. Nullam sit amet lorem non dolor hendrerit pellentesque. Praesent mollis tempor orci nec interdum. Morbi a imperdiet eros. Vivamus nec enim vel odio fringilla rhoncus sed eu nisi. Donec ultrices in eros non mattis. Duis nec efficitur ante, ut ornare risus. Ut mi libero, mattis eget leo sed, accumsan venenatis lacus.</p><p>&nbsp;</p><p>Donec commodo odio in molestie tincidunt. Etiam sit amet facilisis arcu, ac fringilla risus. Praesent eget elit at sapien vulputate euismod ac a arcu. Phasellus lacus ante, finibus nec dolor non, mollis hendrerit sem. Cras dictum sed felis et tincidunt. Curabitur ullamcorper rhoncus nisi id blandit. Fusce dolor risus, elementum in augue vitae, ultrices rutrum lorem. Etiam gravida volutpat urna in porttitor. Mauris condimentum dolor dolor, et facilisis velit dictum vitae. Nullam sit amet lorem non dolor hendrerit pellentesque. Praesent mollis tempor orci nec interdum. Morbi a imperdiet eros. Vivamus nec enim vel odio fringilla rhoncus sed eu nisi. Donec ultrices in eros non mattis. Duis nec efficitur ante, ut ornare risus. Ut mi libero, mattis eget leo sed, accumsan venenatis lacus.</p>",0,NOW(),1,2,1);
INSERT INTO Note(title, text, is_agenda, datetime_created, meeting_id, user_id, project_id)
    VALUES("Just another note", "<p>Donec commodo odio in molestie tincidunt. Etiam sit amet facilisis arcu, ac fringilla risus. Praesent eget elit at sapien vulputate euismod ac a arcu. Phasellus lacus ante, finibus nec dolor non, mollis hendrerit sem. Cras dictum sed felis et tincidunt. Curabitur ullamcorper rhoncus nisi id blandit. Fusce dolor risus, elementum in augue vitae, ultrices rutrum lorem. Etiam gravida volutpat urna in porttitor. Mauris condimentum dolor dolor, et facilisis velit dictum vitae. Nullam sit amet lorem non dolor hendrerit pellentesque. Praesent mollis tempor orci nec interdum. Morbi a imperdiet eros. Vivamus nec enim vel odio fringilla rhoncus sed eu nisi. Donec ultrices in eros non mattis. Duis nec efficitur ante, ut ornare risus. Ut mi libero, mattis eget leo sed, accumsan venenatis lacus.</p><p>&nbsp;</p><p>Donec commodo odio in molestie tincidunt. Etiam sit amet facilisis arcu, ac fringilla risus. Praesent eget elit at sapien vulputate euismod ac a arcu. Phasellus lacus ante, finibus nec dolor non, mollis hendrerit sem. Cras dictum sed felis et tincidunt. Curabitur ullamcorper rhoncus nisi id blandit. Fusce dolor risus, elementum in augue vitae, ultrices rutrum lorem. Etiam gravida volutpat urna in porttitor. Mauris condimentum dolor dolor, et facilisis velit dictum vitae. Nullam sit amet lorem non dolor hendrerit pellentesque. Praesent mollis tempor orci nec interdum. Morbi a imperdiet eros. Vivamus nec enim vel odio fringilla rhoncus sed eu nisi. Donec ultrices in eros non mattis. Duis nec efficitur ante, ut ornare risus. Ut mi libero, mattis eget leo sed, accumsan venenatis lacus.</p>",0,NOW(),1,2,1);
INSERT INTO Note(title, text, is_agenda, datetime_created, meeting_id, user_id, project_id)
VALUES("Issues & Problems", "<p>Donec commodo odio in molestie tincidunt. Etiam sit amet facilisis arcu, ac fringilla risus. Praesent eget elit at sapien vulputate euismod ac a arcu. Phasellus lacus ante, finibus nec dolor non, mollis hendrerit sem. Cras dictum sed felis et tincidunt. Curabitur ullamcorper rhoncus nisi id blandit. Fusce dolor risus, elementum in augue vitae, ultrices rutrum lorem. Etiam gravida volutpat urna in porttitor. Mauris condimentum dolor dolor, et facilisis velit dictum vitae. Nullam sit amet lorem non dolor hendrerit pellentesque. Praesent mollis tempor orci nec interdum. Morbi a imperdiet eros. Vivamus nec enim vel odio fringilla rhoncus sed eu nisi. Donec ultrices in eros non mattis. Duis nec efficitur ante, ut ornare risus. Ut mi libero, mattis eget leo sed, accumsan venenatis lacus.</p><p>&nbsp;</p><p>Donec commodo odio in molestie tincidunt. Etiam sit amet facilisis arcu, ac fringilla risus. Praesent eget elit at sapien vulputate euismod ac a arcu. Phasellus lacus ante, finibus nec dolor non, mollis hendrerit sem. Cras dictum sed felis et tincidunt. Curabitur ullamcorper rhoncus nisi id blandit. Fusce dolor risus, elementum in augue vitae, ultrices rutrum lorem. Etiam gravida volutpat urna in porttitor. Mauris condimentum dolor dolor, et facilisis velit dictum vitae. Nullam sit amet lorem non dolor hendrerit pellentesque. Praesent mollis tempor orci nec interdum. Morbi a imperdiet eros. Vivamus nec enim vel odio fringilla rhoncus sed eu nisi. Donec ultrices in eros non mattis. Duis nec efficitur ante, ut ornare risus. Ut mi libero, mattis eget leo sed, accumsan venenatis lacus.</p>",1,NOW(),2,2,1);



##################################### SCENARIO FOR A SUPERVISOR
######### USERS
/*INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('student','76a2173be6393254e72ffa4d6df1030a','Emil','Cieslar',0,'email1@gmail.com');
INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('student2','76a2173be6393254e72ffa4d6df1030a','Joe','Doe',0,'email2@email.com');
INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('supervisor','76a2173be6393254e72ffa4d6df1030a','Rosanne','English',1,'email3@gmail.com');

######### PROJECTS
INSERT INTO Project(short_name, name,description) VALUES('SP1', 'Sample Project 1','Web-based application with a database back end which allows a supervisor to monitor the progress of their student and the students to engage with the supervisor');
INSERT INTO Project(short_name, name,description) VALUES('SP2','Sample Project 2','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut leo lorem, blandit vitae turpis non, fermentum faucibus massa. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur dignissim, massa ac bibendum maximus, velit tortor lobortis libero, at congue ex lorem nec erat. Ut vel lobortis purus. Proin sit amet risus consequat, varius diam vehicula, iaculis est.');

######### ASSIGNING USERS TO PROJECTS
INSERT INTO UserProject(user_id,project_id) VALUES(5,3);
INSERT INTO UserProject(user_id,project_id) VALUES(7,3);

INSERT INTO UserProject(user_id,project_id) VALUES(6,4);
INSERT INTO UserProject(user_id,project_id) VALUES(7,4);

# Function to get the last monday date
CREATE FUNCTION `LastMonday`() RETURNS DATE
  RETURN DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY);

SET @now_meeting = CONCAT(DATE_FORMAT(LastMonday(),'%Y-%m-%d'),' 11:00:00');

######### MEETINGS
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
VALUES(@now_meeting,0,0,1,1,1,3);

######### ACTION POINTS
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
VALUES(@now_meeting + INTERVAL 7 DAY,@now_meeting,'Market research',0,0,1,0,13,5,3);
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
VALUES(@now_meeting + INTERVAL 7 DAY,@now_meeting,'Read articles',0,0,1,0,13,5,3);

######### AGENDA NOTE
INSERT INTO Note(title, text, is_agenda, datetime_created, meeting_id, user_id, project_id)
VALUES("Issues & Problems", "<p>Donec commodo odio in molestie tincidunt. Etiam sit amet facilisis arcu, ac fringilla risus. Praesent eget elit at sapien vulputate euismod ac a arcu. Phasellus lacus ante, finibus nec dolor non, mollis hendrerit sem. Cras dictum sed felis et tincidunt. Curabitur ullamcorper rhoncus nisi id blandit. Fusce dolor risus, elementum in augue vitae, ultrices rutrum lorem. Etiam gravida volutpat urna in porttitor. Mauris condimentum dolor dolor, et facilisis velit dictum vitae. Nullam sit amet lorem non dolor hendrerit pellentesque. Praesent mollis tempor orci nec interdum. Morbi a imperdiet eros. Vivamus nec enim vel odio fringilla rhoncus sed eu nisi. Donec ultrices in eros non mattis. Duis nec efficitur ante, ut ornare risus. Ut mi libero, mattis eget leo sed, accumsan venenatis lacus.</p><p>&nbsp;</p><p>Donec commodo odio in molestie tincidunt. Etiam sit amet facilisis arcu, ac fringilla risus. Praesent eget elit at sapien vulputate euismod ac a arcu. Phasellus lacus ante, finibus nec dolor non, mollis hendrerit sem. Cras dictum sed felis et tincidunt. Curabitur ullamcorper rhoncus nisi id blandit. Fusce dolor risus, elementum in augue vitae, ultrices rutrum lorem. Etiam gravida volutpat urna in porttitor. Mauris condimentum dolor dolor, et facilisis velit dictum vitae. Nullam sit amet lorem non dolor hendrerit pellentesque. Praesent mollis tempor orci nec interdum. Morbi a imperdiet eros. Vivamus nec enim vel odio fringilla rhoncus sed eu nisi. Donec ultrices in eros non mattis. Duis nec efficitur ante, ut ornare risus. Ut mi libero, mattis eget leo sed, accumsan venenatis lacus.</p>",1,NOW(),14,5,3);
*/


##################################### SCENARIO FOR A STUDENT
######### USERS
INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('student','76a2173be6393254e72ffa4d6df1030a','Emil','Cieslar',0,'email1@gmail.com');
INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('student2','76a2173be6393254e72ffa4d6df1030a','Joe','Doe',0,'email2@email.com');
INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('supervisor','76a2173be6393254e72ffa4d6df1030a','Rosanne','English',1,'email3@gmail.com');

######### PROJECTS
INSERT INTO Project(short_name, name,description) VALUES('SP1', 'Sample Project 1','Web-based application with a database back end which allows a supervisor to monitor the progress of their student and the students to engage with the supervisor');
INSERT INTO Project(short_name, name,description) VALUES('SP2','Sample Project 2','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut leo lorem, blandit vitae turpis non, fermentum faucibus massa. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur dignissim, massa ac bibendum maximus, velit tortor lobortis libero, at congue ex lorem nec erat. Ut vel lobortis purus. Proin sit amet risus consequat, varius diam vehicula, iaculis est.');

######### ASSIGNING USERS TO PROJECTS
INSERT INTO UserProject(user_id,project_id) VALUES(5,3);
INSERT INTO UserProject(user_id,project_id) VALUES(7,3);

INSERT INTO UserProject(user_id,project_id) VALUES(6,4);
INSERT INTO UserProject(user_id,project_id) VALUES(7,4);

# Function to get the last monday date
CREATE FUNCTION `LastMonday`() RETURNS DATE
  RETURN DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY);

SET @now_meeting = CONCAT(DATE_FORMAT(LastMonday(),'%Y-%m-%d'),' 11:00:00');

######### MEETINGS
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
VALUES(@now_meeting,0,0,1,1,1,3);
# Scenario for student has two more next meetings since one of them will be cancelled in the scenario and there
# must be at least one more approved to be able to add agenda for the next meeting
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
VALUES(@now_meeting + INTERVAL 7 DAY,0,0,1,0,0,3);
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
VALUES(@now_meeting + INTERVAL 14 DAY,0,0,1,0,0,3);