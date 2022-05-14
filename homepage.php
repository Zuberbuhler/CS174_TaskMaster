<?php // firstPage.php
    require_once 'login.php';
    require_once 'Task.php';
    echo "homepage.php<br/><hr/>";

    echo <<< _END
    <style> 
    .offscreen {position: absolute; left: -9999px;} 
    .onscreen {position: relative;}
    p {margin: 0;}
    </style>

    <title>Homepage</title>
    _END;

    $connection = new mysqli($hn, $un, $pw, $db);

    if ($connection->connect_error) {
        echo $connection->connect_error;
    }
    else {
        session_start();

        if ( isset($_SESSION['username']) && strval($_SESSION['ip']) == strval($_SERVER['REMOTE_ADDR'])
            && $_SESSION['check'] == hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])) {
            if ( isset($_POST['logout'])) {
                destroy_session_and_data();

                echo "Please <a href='loginPage.php'>click here</a> to log in.";
            }
            else {
                $username = $_SESSION['username'];
                $userid = $_SESSION['userid'];
                echo "Hello, $username($userid)!<br/>";
                displayAddTask();

                handleAddTask($connection, $userid);

                displayLogout();

                handleDeleteTask($connection);

                displayTasks($connection, $userid);
            }
        }
        else echo "Please <a href='loginPage.php'>click here</a> to log in.";
    }

// A handy function to destroy a session and its data
    function destroy_session_and_data() {
        session_start();
        $_SESSION = array(); // Delete all the information in the array
        setcookie(session_name(), '', time() - 2592000, '/');
        session_destroy();
    }

    function getAllTasks($conn, $username)
    {
        $query = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($query);

        if (!$result) 
        {
            echo "Error Trying to Access Tasks!";
        }
        elseif ($result->num_rows) {
            $row = $result->fetch_array(MYSQLI_NUM);
            $result->close();

            
        }
        else 
        {
            echo "Failed to signin!<br/>";
        }
    }

    function handleAddTask($conn, $userid)
    {
        if(isset($_POST['add_task_category']) && isset($_POST['add_task_title']) &&
        isset($_POST['add_task_content']) && isset($_POST['add_task_due_date']) &&
        isset($_POST['add_task_priority']))
        {
            $category = mysql_entities_fix_string($conn, $_POST['add_task_category']);
            $title = mysql_entities_fix_string($conn, $_POST['add_task_title']);
            $content = mysql_entities_fix_string($conn, $_POST['add_task_content']);
            $due_date = mysql_entities_fix_string($conn, $_POST['add_task_due_date']);
            $priority = mysql_entities_fix_string($conn, $_POST['add_task_priority']);

            $task = new Task($category, $title, $content, $due_date, $priority);

            insertTaskIntoDataBase($conn, $userid, $task);
        }
    }

    function displayTasks($conn, $userid)
    {
        $query = "SELECT * FROM tasks WHERE userid=$userid";
        $result = $conn->query($query);

        $rows = $result->num_rows;
		if($rows === 0) 
		{
			echo "No Content Available: Task Table is Empty.<br/>";
		}
		for ($j = 0 ; $j < $rows ; ++$j)
		{
			$result->data_seek($j);
			$row = $result->fetch_array(MYSQLI_NUM);
            $priorityString;
            switch($row[6]) {
                case 1: 
                    $priorityString = "Low";
                    break;
                case 2: 
                    $priorityString = "Medium";
                    break;
                case 3: 
                    $priorityString = "High";
                    break;
            }
			echo <<< _END
            <pre>
                Task: $row[3]
                User Id: $userid
                Task Id: $row[1]
                Priority: $priorityString
                Category: $row[2]
                Due Date: $row[5]
                Content: $row[4]
                File Contents: $row[3]
            </pre>
            <form action="homepage.php" method="post">
                <input type="hidden" name="delete_task" value="yes">
                <input type="hidden" name="del_task_userid" value=$userid>
                <input type="hidden" name="del_task_taskid" value=$row[1]>
                <input type="submit" value="DELETE TASK">
            </form>
            _END;
		}
		$result->close();
    }

    function displayAddTask() {
        $d=strtotime("next Monday");
        echo <<< _END
        <form action="homepage.php" method="post">
        <pre>
        <label for='add_task_category'>Category</label>
        <input type='text' id='add_task_category' name='add_task_category' placeholder='Enter Category' required>
        <label for='add_task_title'>Title</label>
        <input type='text' id='add_task_title' name='add_task_title' placeholder='Enter Title' required>
        <label for='add_task_content'>Content</label>
        <input type='textarea' id='add_task_content' name='add_task_content' placeholder='Enter Content' required>
        <label for='add_task_due_date'>Due Date (MM/DD/YYYY)</label>
        _END;
        echo "<br/><input type='date' id='add_task_due_date' name='add_task_due_date' value='".date("Y-m-d", $d)."'required><br/>";
        echo <<< _END
        <label for="add_task_priority">Priority</label>
        <select name="add_task_priority" id="add_task_priority">
        <option value="1">Low</option>
        <option value="2">Medium</option>
        <option value="3">High</option>
        </select>
        <input type="submit" name="addTaskButton" value="Add Task">
        </pre>
        </form>
        _END;
    }

    function displayLogout() {
        echo <<< _END
        <form action="homepage.php" method="post">
        <pre>
        <input type="hidden" name="logout" value="yes">
        <input type="submit" class="button" name="logoutBtn" value="Logout">
        </pre>
        </form>
        _END;
    }

    function handleDeleteTask($conn)
    {
        if(isset($_POST['delete_task']) && isset($_POST['del_task_userid']) &&
        isset($_POST['del_task_taskid']))
        {
            $userid_tmp = mysql_entities_fix_string($conn, $_POST['del_task_userid']);
            $taskid_tmp = mysql_entities_fix_string($conn, $_POST['del_task_taskid']);

            $query = "DELETE FROM tasks where userid=$userid_tmp AND taskid=$taskid_tmp";
            $result = $conn->query($query);
            if(!result)
            {
                echo "Could not remove task!<br/>";
            }
            else {
                echo "Successfully Removed Task!<br/>";
            }
        }
    }

    function insertTaskIntoDataBase($conn, $userid, $task = null)
    {
        if($task === null)
        {
            echo "Provided Task is null<br/>";
            return;
        }

        $query = "INSERT INTO tasks (userid, category, title, content, due_date, priority, completed) VALUES (". 
                $userid . 
                ", '" . $task->getCategory() . "'" .
                ", '" . $task->getTitle() .  "'" .
                ", '" . $task->getContent() .  "'" .
                ", '" . $task->getDue_Date() .  "'" .
                ", " . $task->getPriority() . 
                ", " . $task->getCompletedString() . ")";
        
        $result = $conn->query($query);
        if (!$result) 
        {
            echo "Something went wrong. Please Try again!<br/>";
        }
        else 
        {
            echo "Added Task :D<br/>";
        }

    }

    // SANTIZE FUNCTIONS
	function mysql_entities_fix_string($conn, $string) {
		return htmlentities(mysql_fix_string($conn, $string));
	}

	function mysql_fix_string($conn, $string) {
		return $conn->real_escape_string($string);
	}

	function get_post($conn, $var) {
		return $conn->real_escape_string($_POST[$var]);
	}
?>