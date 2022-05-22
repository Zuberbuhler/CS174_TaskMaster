<?php // firstPage.php Code by Matthew Zuberbuhler & Karan Sharma
    require_once 'login.php';
    require_once 'Task.php';

    echo <<< _END
    <style> 
    .offscreen {position: absolute; left: -9999px;} 
    .onscreen {position: relative;}
    p {margin: 0;}
    .container { 
        max-width: 500px;
        margin: 20px auto 20px auto;}

    </style>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <title>Homepage</title>
    _END;
    echo "<div style='text-align: center;'><h1>Task Master</h1></div>";

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

                echo "<h3 class='container'>Please <a href='loginPage.php'>click here</a> to log in.</h3>";
            }
            else {
                handleUpdateTask($connection);
                $username = $_SESSION['username'];
                $userid = $_SESSION['userid'];

                displayLogout($username, $userid);

                if(isset($_POST['show_add_tasks']) && !isset($_POST['close_add_tasks']) )
                {
                    displayAddTask();
                }
                else {
                    displayAddTasksButton();

                    handleAddTask($connection, $userid);

                    handleDeleteTask($connection);

                    displayTasks($connection, $userid);
                }

                echo "<hr/>";
            }
        }
        else echo "<h3 class='container'>Please <a href='loginPage.php'>click here</a> to log in.</h3>";
    }

// A handy function to destroy a session and its data
    function destroy_session_and_data() {
        $_SESSION = array(); // Delete all the information in the array
        setcookie(session_name(), '', time() - 2592000, '/');
        session_destroy();
    }

    function displayAddTasksButton() {
        echo "<div class='container'>";
        echo <<< _END
        <form action="homepage.php" method="post">
        <input type="hidden" name="show_add_tasks" value="yes">
        <input type="submit" style='width: 100%;' class="btn btn-success" name="show_add_tasks_btn" value="Add New Task">
        </form>
        _END;
        echo "</div>";
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
        isset($_POST['add_task_status']))
        {
            $category = mysql_entities_fix_string($conn, $_POST['add_task_category']);
            $title = mysql_entities_fix_string($conn, $_POST['add_task_title']);
            $content = mysql_entities_fix_string($conn, $_POST['add_task_content']);
            $due_date = mysql_entities_fix_string($conn, $_POST['add_task_due_date']);
            $status = mysql_entities_fix_string($conn, $_POST['add_task_status']);

            
            if( validateText($category, 30) && validateText($title, 50) && 
                validateText($content, 500) && validateDate($due_date) )
            {
                $task = new Task($category, $title, $content, $due_date, $status);
                insertTaskIntoDataBase($conn, $userid, $task);
            }
            else {
                echo "<h3 class='container' style='text-align:center'>Please enter valid data!</h3>";
            }

            $_POST = array();
        }
    }

    function displayTasks($conn, $userid)
    {
        $query = "SELECT * FROM tasks WHERE userid=$userid order by status";
        $result = $conn->query($query);

        $rows = $result->num_rows;
		if($rows === 0) 
		{
			echo "<h3 class='container'>No Content Available: Task Table is Empty.</h3>";
		}
        echo "<div class='container' style='height:60%; overflow-x:auto;'>";
		for ($j = 0 ; $j < $rows ; ++$j)
		{
			$result->data_seek($j);
			$row = $result->fetch_array(MYSQLI_NUM);
            $statusString;
            $backgroundColor;

            switch($row[6]) {
                case 1: 
                    $statusString = "Not Started";
                    $backgroundColor = "#FBF9DF";
                    break;
                case 2: 
                    $statusString = "In Progress";
                    $backgroundColor = "#ECFBDF";
                    break;
                case 3: 
                    $statusString = "Complete";
                    $backgroundColor = "#E6FFB8";
                    break;
            }

			echo <<< _END
            <div class='container' style='width:100%; background-color:$backgroundColor; border-radius:0.3em;'>
                <h4>$row[3]</h4>
                <p><b>Status: </b>$statusString, <b>Category: </b>$row[2], <b>Due Date: </b>$row[5]</p>
                <form action="homepage.php" method="post"> 
                    <input type="hidden" name="delete_task" value="yes"> 
                    <input type="hidden" name="del_task_userid" value=$userid> 
                    <input type="hidden" name="del_task_taskid" value=$row[1]> 
                    <button type="button" data-toggle="modal" class="btn btn-info" data-target="#myModal$row[1]">View / Edit Task</button>
                    <input type="submit" style='float: right' class="btn btn-danger" value="DELETE TASK">
                </form>
            </div>
            <div class="modal fade" id="myModal$row[1]" role="dialog">
                <div class="modal-dialog">
                
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit Task</h4>
                    </div>
                    <div class="modal-body">
            _END;
            $d=strtotime("next Monday");
            echo <<< _END
            <form action="homepage.php" method="post">
            <div class="form-group">
            <label for='update_task_category'>Category</label>
            <p id='update_task_category_info' class="offscreen"> Less than 30 Characters and Limited Special Characters </p>
            <input type='text' class="form-control" id='update_task_category' name='update_task_category' value='$row[2]' placeholder='Enter Category' required>
            </div>
            <div class="form-group">
            <label for='update_task_title'>Title</label>
            <p id='update_task_title_info' class="offscreen"> Less than 50 Characters and Limited Special Characters </p>
            <input type='text' class="form-control" id='update_task_title' name='update_task_title' value='$row[3]' placeholder='Enter Title' required>
            </div>
            <div class="form-group">
            <label for='update_task_content'>Content</label>
            <p id='update_task_content_info' class="offscreen"> Less than 500 Characters and Limited Special Characters </p>
            <textarea class="form-control" style='resize:vertical' id='update_task_content' name='update_task_content' placeholder='Describe task here...' required>$row[4]</textarea>
            </div>
            <div class="form-group">
            <label for='update_task_due_date'>Due Date (MM/DD/YYYY)</label>
            <p id='update_task_due_date_info' class="offscreen">Date Format Only</p>
            _END;
            echo "<br/><input type='date' class='form-control' id='update_task_due_date' name='update_task_due_date' value='$row[5]'required><br/>";
            echo <<< _END
            </div>
            <div class="form-group">
            <label for="update_task_status">Status</label>
            <select class="form-control"  name="update_task_status" id="update_task_status">
            _END;
            $option1Selected = ($row[6] == 1)? "selected": "unselected";
            $option2Selected = ($row[6] == 2)? "selected": "unselected";
            $option3Selected = ($row[6] == 3)? "selected": "unselected";
        
            echo "<option value='1' ".$option1Selected.">Not Started</option>";
            echo "<option value='2' ".$option2Selected.">In Progress</option>";
            echo "<option value='3' ".$option3Selected.">Complete</option>";
            echo <<< _END
            </select>
            </div>
            <input type='hidden' name='update_task_userid' value=$userid>
            <input type='hidden' name='update_task_taskid' value=$row[1]>
            <input type='hidden' name='update_task_completed' value=$row[7]>
            <input type="submit" id='updateTaskButton' class="btn btn-success" name="updateTaskButton" value="Update Task">
            </form>
            <script>
            (() => {
                const categoryLength = 30;
                const titleLength = 50;
                const contentLength = 500;
                
                var categoryValid = true;
                var titleValid = true;
                var contentValid = true;
                var dueDateValid = true;
            
                var elUpdateTaskCategory = document.getElementById('update_task_category');
                var elUpdateTaskCategoryInfo = document.getElementById('update_task_category_info');
            
                var elUpdateTaskTitle = document.getElementById('update_task_title');
                var elUpdateTaskTitleInfo = document.getElementById('update_task_title_info');
            
                var elUpdateTaskContent = document.getElementById('update_task_content');
                var elUpdateTaskContentInfo = document.getElementById('update_task_content_info');
            
                var elUpdateTaskDate = document.getElementById('update_task_due_date');
                var elUpdateTaskDateInfo = document.getElementById('update_task_due_date_info');
            
                function validateText(text, lengthLimit) {
                    if(text.length < 1 || text.length > lengthLimit) return false;
            
                    const unRegex = /^[a-zA-Z0-9!.?;',:\[\]\{\}]+( [a-zA-Z0-9!?.;',:\[\]\{\}]+)*$/;
                    return unRegex.test(text);
                }
            
                function validateDate(date) {
                    if(date.length != 10) return false;
            
                    const dateRegex = /^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/;
                    return dateRegex.test(date);
                }
            
                elUpdateTaskCategory.addEventListener('keyup', (e) => {  
                    console.log(e.target.value);
                    if( !validateText(e.target.value, categoryLength) )
                    {   
                        console.log("Not Category");
                        categoryValid = false;
                        elUpdateTaskCategoryInfo.classList.remove('offscreen');
                        elUpdateTaskCategoryInfo.classList.add('onscreen');
                    }
                    else
                    {
                        console.log("Valid Category");
                        categoryValid = true;
                        elUpdateTaskCategoryInfo.classList.remove('onscreen');
                        elUpdateTaskCategoryInfo.classList.add('offscreen');
                    }
                    toggleDisableUpdateForm();
                });
            
                elUpdateTaskTitle.addEventListener('keyup', (e) => {  
                    console.log(e.target.value);
                    if( !validateText(e.target.value, titleLength) )
                    {   
                        console.log("Not Valid Title");
                        titleValid = false;
                        elUpdateTaskTitleInfo.classList.remove('offscreen');
                        elUpdateTaskTitleInfo.classList.add('onscreen');
                    }
                    else
                    {
                        console.log("Valid Title");
                        titleValid = true;
                        elUpdateTaskTitleInfo.classList.remove('onscreen');
                        elUpdateTaskTitleInfo.classList.add('offscreen');
                    }
                    toggleDisableUpdateForm();
                });
            
                elUpdateTaskContent.addEventListener('keyup', (e) => {  
                    console.log(e.target.value);
                    if( !validateText(e.target.value, contentLength) )
                    {   
                        console.log("Not Valid Content");
                        contentValid = false;
                        elUpdateTaskContentInfo.classList.remove('offscreen');
                        elUpdateTaskContentInfo.classList.add('onscreen');
                    }
                    else
                    {
                        console.log("Valid Content");
                        contentValid = true;
                        elUpdateTaskContentInfo.classList.remove('onscreen');
                        elUpdateTaskContentInfo.classList.add('offscreen');
                    }
                    toggleDisableUpdateForm();
                });
            
                elUpdateTaskDate.addEventListener('focus', (e) => {  
                    console.log(e.target.value);
                    if( !validateDate(e.target.value) )
                    {   
                        console.log("Not Valid Date");
                        dueDateValid = false;
                        elUpdateTaskDateInfo.classList.remove('offscreen');
                        elUpdateTaskDateInfo.classList.add('onscreen');
                    }
                    else
                    {
                        console.log("Valid Date");
                        dueDateValid = true;
                        elUpdateTaskDateInfo.classList.remove('onscreen');
                        elUpdateTaskDateInfo.classList.add('offscreen');
                    }
                    toggleDisableUpdateForm();
                });
            
                elUpdateTaskDate.addEventListener('blur', (e) => {  
                    console.log(e.target.value);
                    if( !validateDate(e.target.value) )
                    {   
                        console.log("Not Valid Date");
                        dueDateValid = false;
                        elUpdateTaskDateInfo.classList.remove('offscreen');
                        elUpdateTaskDateInfo.classList.add('onscreen');
                    }
                    else
                    {
                        console.log("Valid Date");
                        dueDateValid = true;
                        elUpdateTaskDateInfo.classList.remove('onscreen');
                        elUpdateTaskDateInfo.classList.add('offscreen');
                    }
                    toggleDisableUpdateForm();
                });
            
                
                var elUpdateSubmitBtn = document.getElementById('updateTaskButton');
                elUpdateSubmitBtn.disabled=false;
                function toggleDisableUpdateForm() {
                    if(dueDateValid && categoryValid && titleValid && contentValid)
                    {
                        elUpdateSubmitBtn.disabled = false;
                    }
                    else
                    {
                        elUpdateSubmitBtn.disabled = true;
                    }
                }
            }) ();
            </script>
            _END;
            echo <<< _END
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
                </div>
            </div>
            _END;
		}
        echo "</div>";
		$result->close();
    }

    function handleUpdateTask($conn)
    {
        if(isset($_POST['update_task_category']) && isset($_POST['update_task_title']) &&
        isset($_POST['update_task_content']) && isset($_POST['update_task_due_date']) &&
        isset($_POST['update_task_status']) && isset($_POST['update_task_userid']) && 
        isset($_POST['update_task_taskid']) && isset($_POST['update_task_completed']) )
        {
            $category = mysql_entities_fix_string($conn, $_POST['update_task_category']);
            $title = mysql_entities_fix_string($conn, $_POST['update_task_title']);
            $content = mysql_entities_fix_string($conn, $_POST['update_task_content']);
            $due_date = mysql_entities_fix_string($conn, $_POST['update_task_due_date']);
            $status = mysql_entities_fix_string($conn, $_POST['update_task_status']);
            $userid = mysql_entities_fix_string($conn, $_POST['update_task_userid']);
            $taskid = mysql_entities_fix_string($conn, $_POST['update_task_taskid']);

            if( validateText($category, 30) && validateText($title, 50) && 
                validateText($content, 500) && validateDate($due_date) )
            {
                $query = "UPDATE tasks ".
                    "SET category ='$category', title='$title', content='$content', ".
                    "due_date='$due_date', status=$status ".
                    "WHERE userid=$userid AND taskid=$taskid";

                $result = $conn->query($query);
                if (!$result) 
                {
                    echo "Log: Something went wrong. Please Try again!<br/>";
                }
                else 
                {
                    // echo "Log: Updated Task<br/>";
                }
            }
            else {
                echo "<h3 class='container' style='text-align:center'>Please enter valid data!</h3>";
            }
        }
    }

    function displayAddTask() {
        echo "<div class='container'>";
        echo "<h1 style='text-align: center;'>Add Task</h1>";
        $d=strtotime("next Monday");
        echo <<< _END
        <form action="homepage.php" method="post">
        <div class="form-group">
        <label for='add_task_category'>Category</label>
        <p id='add_task_category_info' class="offscreen"> Less than 30 Characters and Limited Special Characters </p>
        <input type='text' class="form-control" id='add_task_category' name='add_task_category' placeholder='Enter Category' required>
        </div>
        <div class="form-group">
        <label for='add_task_title'>Title</label>
        <p id='add_task_title_info' class="offscreen"> Less than 50 Characters and Limited Special Characters </p>
        <input type='text' class="form-control" id='add_task_title' name='add_task_title' placeholder='Enter Title' required>
        </div>
        <div class="form-group">
        <label for='add_task_content'>Content</label>
        <p id='add_task_content_info' class="offscreen"> Less than 500 Characters and Limited Special Characters </p>
        <textarea style='resize: vertical; overflow: auto;' class="form-control" id='add_task_content' name='add_task_content' placeholder="Describe the task here..." required>
        </textarea>
        </div>
        <div class="form-group">
        <label for='add_task_due_date'>Due Date (MM/DD/YYYY)</label>
        <p id='add_task_due_date_info' class="offscreen">Date Format Only</p>
        _END;
        echo "<br/><input type='date' class='form-control' id='add_task_due_date' name='add_task_due_date' value='".date("Y-m-d", $d)."'required><br/>";
        echo <<< _END
        </div>
        <div class="form-group">
        <label for="add_task_status">Status</label>
        <select name="add_task_status" class='form-control' id="add_task_status">
        <option value="1">Not Started</option>
        <option value="2">In Progress</option>
        <option value="3">Completed</option>
        </select>
        </div>
        <input type="submit" id="addTaskButton" style='width:100%;' class="btn btn-primary" name="addTaskButton" value="Add Task">
        </form>
        <form action="homepage.php" method="post">
        <input type="hidden" name="close_add_tasks" value="yes">
        <input type="submit" style='width:100%;' class="btn btn-danger" name="close_add_tasks_btn" value="Cancel">
        </form>
        </div>
        <script>
        (() => {
            const categoryLength = 30;
            const titleLength = 50;
            const contentLength = 500;
            
            var categoryValid = false;
            var titleValid = false;
            var contentValid = false;
            var dueDateValid = true;
        
            var elAddTaskCategory = document.getElementById('add_task_category');
            var elAddTaskCategoryInfo = document.getElementById('add_task_category_info');
        
            var elAddTaskTitle = document.getElementById('add_task_title');
            var elAddTaskTitleInfo = document.getElementById('add_task_title_info');
        
            var elAddTaskContent = document.getElementById('add_task_content');
            var elAddTaskContentInfo = document.getElementById('add_task_content_info');
        
            var elAddTaskDate = document.getElementById('add_task_due_date');
            var elAddTaskDateInfo = document.getElementById('add_task_due_date_info');
        
            function validateText(text, lengthLimit) {
                if(text.length < 1 || text.length > lengthLimit) return false;
        
                const unRegex = /^[a-zA-Z0-9!.?;',:\[\]\{\}]+( [a-zA-Z0-9!?.;',:\[\]\{\}]+)*$/;
                return unRegex.test(text);
            }
        
            function validateDate(date) {
                if(date.length != 10) return false;
        
                const dateRegex = /^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/;
                return dateRegex.test(date);
            }
        
            elAddTaskCategory.addEventListener('keyup', (e) => {  
                console.log(e.target.value);
                if( !validateText(e.target.value, categoryLength) )
                {   
                    console.log("Not Category");
                    categoryValid = false;
                    elAddTaskCategoryInfo.classList.remove('offscreen');
                    elAddTaskCategoryInfo.classList.add('onscreen');
                }
                else
                {
                    console.log("Valid Category");
                    categoryValid = true;
                    elAddTaskCategoryInfo.classList.remove('onscreen');
                    elAddTaskCategoryInfo.classList.add('offscreen');
                }
                toggleDisableAddForm();
            });
        
            elAddTaskTitle.addEventListener('keyup', (e) => {  
                console.log(e.target.value);
                if( !validateText(e.target.value, titleLength) )
                {   
                    console.log("Not Valid Title");
                    titleValid = false;
                    elAddTaskTitleInfo.classList.remove('offscreen');
                    elAddTaskTitleInfo.classList.add('onscreen');
                }
                else
                {
                    console.log("Valid Title");
                    titleValid = true;
                    elAddTaskTitleInfo.classList.remove('onscreen');
                    elAddTaskTitleInfo.classList.add('offscreen');
                }
                toggleDisableAddForm();
            });
        
            elAddTaskContent.addEventListener('keyup', (e) => {  
                console.log(e.target.value);
                if( !validateText(e.target.value, contentLength) )
                {   
                    console.log("Not Valid Content");
                    contentValid = false;
                    elAddTaskContentInfo.classList.remove('offscreen');
                    elAddTaskContentInfo.classList.add('onscreen');
                }
                else
                {
                    console.log("Valid Content");
                    contentValid = true;
                    elAddTaskContentInfo.classList.remove('onscreen');
                    elAddTaskContentInfo.classList.add('offscreen');
                }
                toggleDisableAddForm();
            });
        
            elAddTaskDate.addEventListener('focus', (e) => {  
                console.log(e.target.value);
                if( !validateDate(e.target.value) )
                {   
                    console.log("Not Valid Date");
                    dueDateValid = false;
                    elAddTaskDateInfo.classList.remove('offscreen');
                    elAddTaskDateInfo.classList.add('onscreen');
                }
                else
                {
                    console.log("Valid Date");
                    dueDateValid = true;
                    elAddTaskDateInfo.classList.remove('onscreen');
                    elAddTaskDateInfo.classList.add('offscreen');
                }
                toggleDisableAddForm();
            });
            elAddTaskDate.addEventListener('blur', (e) => {  
                console.log(e.target.value);
                if( !validateDate(e.target.value) )
                {   
                    console.log("Not Valid Date");
                    dueDateValid = false;
                    elAddTaskDateInfo.classList.remove('offscreen');
                    elAddTaskDateInfo.classList.add('onscreen');
                }
                else
                {
                    console.log("Valid Date");
                    dueDateValid = true;
                    elAddTaskDateInfo.classList.remove('onscreen');
                    elAddTaskDateInfo.classList.add('offscreen');
                }
                toggleDisableAddForm();
            });
        
            
        
            var elAddSubmitBtn = document.getElementById('addTaskButton');
            elAddSubmitBtn.disabled=true;
            function toggleDisableAddForm() {
                if(dueDateValid && categoryValid && titleValid && contentValid)
                {
                    elAddSubmitBtn.disabled = false;
                }
                else
                {
                    elAddSubmitBtn.disabled = true;
                }
            }
        }) ();
        </script>
        _END;
    }

    function displayLogout($un, $uid) {
        echo "<div class='container'>";
        echo <<< _END
        <form action="homepage.php" method="post">
        <input type="hidden" name="logout" value="yes">
        <h2 style='display:inline;'>Welcome, <span style='color:#8591FF;'>$un</span></h2><input type="submit" style='float:right;' class="btn btn-primary" name="logoutBtn" value="Logout">
        </form>
        _END;
        echo "</div>";
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
            if(!$result)
            {
                echo "<h4 class='container' style='text-align:center;'>Could not remove task!</h4>";
            }
            else {
                echo "<h4 class='container' style='text-align:center;'>Successfully Removed Task!</h4>";
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

        $query = "INSERT INTO tasks (userid, category, title, content, due_date, status) VALUES (". 
                $userid . 
                ", '" . $task->getCategory() . "'" .
                ", '" . $task->getTitle() .  "'" .
                ", '" . $task->getContent() .  "'" .
                ", '" . $task->getDue_Date() .  "'" .
                ", " . $task->getStatus() .")";
        
        $result = $conn->query($query);
        if (!$result) 
        {
            echo "<h3 class='container' style='text-align:center'>Something went wrong. Please Try again!</h3>";
        }
        else 
        {
            echo "<h3 class='container' style='text-align:center'>Added Task :D</h3>";
        }

    }

    //validation function
    function validateText($text, $textLength)
    {
        if(strlen($text) < 1 || strlen($text) > $textLength) return false;

        $textRegex = "/^[a-zA-Z0-9!.?;',:\[\]\{\}]+( [a-zA-Z0-9!?.;',:\[\]\{\}]+)*$/";
        return preg_match($textRegex, $text) === 1;
    }

    function validateDate($dateString)
    {
        //must be 10 chars long and of format YYYY/MM/DD
        if( strlen($dateString) != 10) return false;

        $dateRegex = "/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/";
        return preg_match($dateRegex, $dateString) === 1;
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
