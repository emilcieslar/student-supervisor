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
  taken_place BOOLEAN DEFAULT 0,
  arrived_on_time BOOLEAN DEFAULT 0,
  google_event_id VARCHAR(255) DEFAULT '',
  project_id INT(11) NOT NULL,
  is_deleted BOOLEAN DEFAULT 0,
  FOREIGN KEY (project_id) REFERENCES Project(id),
  PRIMARY KEY (id)
) ENGINE=MyISAM;

CREATE TABLE MeetingTemp (
  id int(11) AUTO_INCREMENT NOT NULL,
  datetime DATETIME NOT NULL,
  is_repeating BOOLEAN DEFAULT 0,
  repeat_until DATETIME NOT NULL,
  is_approved BOOLEAN DEFAULT 0,
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
  action VARCHAR(50) NOT NULL DEFAULT '',
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

# INSERT SOME VALUES
INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('emil','955db0b81ef1989b4a4dfeae8061a9a6','Emil','Cieslar',0,'emil.cieslar@gmail.com');
INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('rose','955db0b81ef1989b4a4dfeae8061a9a6','Rosanne','English',1,'cieslaremil@gmail.com');
INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('rose_gmail','955db0b81ef1989b4a4dfeae8061a9a6','Rosanne','English',1,'rosanneenglish@gmail.com');
INSERT INTO User(username,password,first_name,last_name,type,email) VALUES('joe','955db0b81ef1989b4a4dfeae8061a9a6','Joe','Doe',0,'cieslar@webkreativ.cz');

INSERT INTO Project(name,description) VALUES('SSMS','Student Supervisor Management System');
INSERT INTO Project(name,description) VALUES('Quizzes','Quizzes Management System');

INSERT INTO UserProject(user_id,project_id) VALUES(1,1);
INSERT INTO UserProject(user_id,project_id) VALUES(2,1);

INSERT INTO UserProject(user_id,project_id) VALUES(2,2);
INSERT INTO UserProject(user_id,project_id) VALUES(3,2);

SET @now_meeting = CONCAT(CURRENT_DATE(),' 11:00:00');


INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
    VALUES(@now_meeting - INTERVAL 7 DAY,0,0,1,1,0,1);
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
    VALUES(@now_meeting + INTERVAL 7 DAY,0,0,1,0,0,1);
INSERT INTO Meeting(datetime, is_repeating, repeat_until, is_approved, taken_place, arrived_on_time, project_id)
VALUES(@now_meeting + INTERVAL 14 DAY,0,0,0,0,0,1);

INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting + INTERVAL 7 DAY,NOW(),'Finish first version of UML',0,0,0,0,1,1,1);
INSERT INTO ActionPoint(deadline, datetime_created, text, is_approved, is_done, sent_for_approval, grade, meeting_id, user_id, project_id)
    VALUES(@now_meeting + INTERVAL 7 DAY,NOW(),'User stories first iteration',1,0,1,0,1,1,1);

INSERT INTO Note(text, is_agenda, datetime_created, meeting_id, user_id, project_id)
    VALUES("<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla a ornare tellus, at mollis ante. Maecenas pulvinar tempus vestibulum. Fusce pharetra sagittis turpis non luctus. Mauris aliquam, libero vitae pretium sollicitudin, purus felis cursus leo, sed accumsan erat velit in justo. Mauris nibh nisl, tempus nec venenatis nec, mattis eget metus. Cras et rutrum metus, vitae bibendum diam. Praesent pulvinar blandit lorem vitae molestie. Donec nec facilisis ante. Maecenas sed hendrerit lorem. Sed dapibus lacus id mauris dignissim, id vehicula erat pharetra. Donec nisi quam, vehicula non faucibus sed, semper non diam.</p><ol><li>Something</li><li>Another thing</li><li>Last thing</li></ol><p><strong>This is gonna be bold&nbsp;<em>and this is gonna be bold and italic.&nbsp;</em></strong><em>Whereas this is only italic.</em></p>",0,NOW(),1,1,1);
INSERT INTO Note(text, is_agenda, datetime_created, meeting_id, user_id, project_id)
    VALUES("<p>Donec commodo odio in molestie tincidunt. Etiam sit amet facilisis arcu, ac fringilla risus. Praesent eget elit at sapien vulputate euismod ac a arcu. Phasellus lacus ante, finibus nec dolor non, mollis hendrerit sem. Cras dictum sed felis et tincidunt. Curabitur ullamcorper rhoncus nisi id blandit. Fusce dolor risus, elementum in augue vitae, ultrices rutrum lorem. Etiam gravida volutpat urna in porttitor. Mauris condimentum dolor dolor, et facilisis velit dictum vitae. Nullam sit amet lorem non dolor hendrerit pellentesque. Praesent mollis tempor orci nec interdum. Morbi a imperdiet eros. Vivamus nec enim vel odio fringilla rhoncus sed eu nisi. Donec ultrices in eros non mattis. Duis nec efficitur ante, ut ornare risus. Ut mi libero, mattis eget leo sed, accumsan venenatis lacus.</p><p>&nbsp;</p><p>Donec commodo odio in molestie tincidunt. Etiam sit amet facilisis arcu, ac fringilla risus. Praesent eget elit at sapien vulputate euismod ac a arcu. Phasellus lacus ante, finibus nec dolor non, mollis hendrerit sem. Cras dictum sed felis et tincidunt. Curabitur ullamcorper rhoncus nisi id blandit. Fusce dolor risus, elementum in augue vitae, ultrices rutrum lorem. Etiam gravida volutpat urna in porttitor. Mauris condimentum dolor dolor, et facilisis velit dictum vitae. Nullam sit amet lorem non dolor hendrerit pellentesque. Praesent mollis tempor orci nec interdum. Morbi a imperdiet eros. Vivamus nec enim vel odio fringilla rhoncus sed eu nisi. Donec ultrices in eros non mattis. Duis nec efficitur ante, ut ornare risus. Ut mi libero, mattis eget leo sed, accumsan venenatis lacus.</p>",0,NOW(),1,2,1);
INSERT INTO Note(title, text, is_agenda, datetime_created, meeting_id, user_id, project_id)
    VALUES("Just another note", "<p>Donec commodo odio in molestie tincidunt. Etiam sit amet facilisis arcu, ac fringilla risus. Praesent eget elit at sapien vulputate euismod ac a arcu. Phasellus lacus ante, finibus nec dolor non, mollis hendrerit sem. Cras dictum sed felis et tincidunt. Curabitur ullamcorper rhoncus nisi id blandit. Fusce dolor risus, elementum in augue vitae, ultrices rutrum lorem. Etiam gravida volutpat urna in porttitor. Mauris condimentum dolor dolor, et facilisis velit dictum vitae. Nullam sit amet lorem non dolor hendrerit pellentesque. Praesent mollis tempor orci nec interdum. Morbi a imperdiet eros. Vivamus nec enim vel odio fringilla rhoncus sed eu nisi. Donec ultrices in eros non mattis. Duis nec efficitur ante, ut ornare risus. Ut mi libero, mattis eget leo sed, accumsan venenatis lacus.</p><p>&nbsp;</p><p>Donec commodo odio in molestie tincidunt. Etiam sit amet facilisis arcu, ac fringilla risus. Praesent eget elit at sapien vulputate euismod ac a arcu. Phasellus lacus ante, finibus nec dolor non, mollis hendrerit sem. Cras dictum sed felis et tincidunt. Curabitur ullamcorper rhoncus nisi id blandit. Fusce dolor risus, elementum in augue vitae, ultrices rutrum lorem. Etiam gravida volutpat urna in porttitor. Mauris condimentum dolor dolor, et facilisis velit dictum vitae. Nullam sit amet lorem non dolor hendrerit pellentesque. Praesent mollis tempor orci nec interdum. Morbi a imperdiet eros. Vivamus nec enim vel odio fringilla rhoncus sed eu nisi. Donec ultrices in eros non mattis. Duis nec efficitur ante, ut ornare risus. Ut mi libero, mattis eget leo sed, accumsan venenatis lacus.</p>",0,NOW(),1,2,1)
