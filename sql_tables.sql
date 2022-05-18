CREATE TABLE users (
    userid INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(32) NOT NULL UNIQUE,
    email VARCHAR(256) NOT NULL,
    token VARCHAR(60) UNIQUE NOT NULL
) ENGINE INNODB;

CREATE TABLE tasks (
    userid INT UNSIGNED NOT NULL,
    taskid INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE  PRIMARY KEY,
    category VARCHAR(32) NOT NULL,
    title VARCHAR(80) NOT NULL,
    content VARCHAR(500) NOT NULL,
    due_date DATE NOT NULL,
    status int NOT NULL,
    FOREIGN KEY (userid) REFERENCES users (userid) ON DELETE CASCADE
) ENGINE INNODB;

INSERT INTO tasks (userid, category, title, content, due_date, priority, completed) VALUES 
(1, 'Cat', 'title', 'content', '2022-05-16', 1, 0);

INSERT INTO tasks (userid, category, title, content, due_date, priority, completed) VALUES 
(1, 'Cat', 'Title', 'Content', '2022-05-16', 2, false) 

UPDATE tasks 
SET category ='$Cat_2', title='Title_2', content='Content_2',
due_date='2022-05-20', priority=3, completed=1
WHERE userid=1 AND taskid=26;