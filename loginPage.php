<?php //loginPage.php   
    require_once 'login.php';
    echo "loginPage.php<br/><hr/>";
    echo "<h1>Task Master</h1>";
    
    echo <<< _END
    <style> 
    .offscreen {position: absolute; left: -9999px;} 
    .onscreen {position: relative;}
    p {margin: 0;}
    </style>

    <title> Login Page</title>
    _END;

    $connection = new mysqli($hn, $un, $pw, $db);

    if ($connection->connect_error) {
        echo $connection->connect_error;
    }
    else {
        session_start();
        if( isset($_SESSION['userid'])) {
            echo "Hi ".$_SESSION['username'].", you are now logged in!<br/>";
            echo "<p><a href=homepage.php>Click here to continue</a></p><br/>";
        }
        else {
            
            if( isset($_POST['login_userid']) && isset($_POST['login_password']) ) {
                // checks if login successful and sets cookie
                $un_temp = mysql_entities_fix_string($connection, $_POST['login_userid']);
                $pw_temp = mysql_entities_fix_string($connection, $_POST['login_password']);
        
                if(validateUsername($un_temp) && validatePassword($pw_temp))
                {
                    $query = "SELECT * FROM users WHERE username='$un_temp'";
                    $result = $connection->query($query);
        
                    if (!$result) 
                    {
                        echo "Something went wrong. Please Try again!";
                    }
                    elseif ($result->num_rows) {
                        $row = $result->fetch_array(MYSQLI_NUM);
                        $result->close();
        
                        if (password_verify($pw_temp, $row[3])) {
                            $_SESSION['userid'] = $row[0];
                            $_SESSION['username'] = $row[1];
                            $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                            $_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
                            echo "Hi $row[1], you are now logged in!<br/>";
                            echo "<p><a href=homepage.php>Click here to continue</a></p><br/>";
                        }
                        else 
                        {
                            echo "Wrong ID/Password Combination!<br/>";
                            displaySignInForm();
                            displaySignUpBtn();
                        }
                    }
                    else 
                    {
                        echo "Failed to signin!<br/>";
                    }
                }
                else {
                    echo "Wrong ID/Password Combination<br/>";
                    displaySignInForm();
                    displaySignUpBtn();
                }
            }
            else if( isset($_POST['register_username']) && isset($_POST['register_email']) && isset($_POST['register_password']) ) {
                $un_temp = mysql_entities_fix_string($connection, $_POST['register_username']);
                $email_temp = mysql_entities_fix_string($connection, $_POST['register_email']);
                $pw_temp = mysql_entities_fix_string($connection, $_POST['register_password']);
                
                $email_temp = strtolower($email_temp);

                if(validateUsername($un_temp) && validateEmail($email_temp) && validatePassword($pw_temp))
                {
                    $hashedPassword = password_hash($pw_temp, PASSWORD_DEFAULT);
                
                    $query = "SELECT * FROM users WHERE username='$un_temp' OR email='$email_temp'";
                    $result = $connection->query($query);
            
                    if(!$result) 
                    {
                        echo "Something went wrong during registration, try again!<br/>";
                        displayRegisterForm();
                        displaySignInBtn();
                    }
                    elseif($result->num_rows > 0) {
                        echo "This account may already be registered, try to login!<br/>";
                        displayRegisterForm();
                        displaySignInBtn();
                    }
                    else {
                        $query = "INSERT INTO users (username, email, token)".
                                " VALUES ('$un_temp', '$email_temp', '$hashedPassword')";
                        
                        $result = $connection->query($query);
                        if(!$result)
                        {
                            echo $connection->error;
                            displayRegisterForm();
                            displaySignInBtn();
                        }
                        else {
                            echo "Registered Successful!<br/>";
                            displaySignInBtn();
                        }
                    }
                } else {
                    echo "An error occurred when registering<br/>";
                    displayRegisterForm();
                    displaySignInBtn();
                }
            }
            else {
                if(isset($_POST['signUpBtnClicked'])) {
                    displayRegisterForm();
                    displaySignInBtn();
                }
                else {
                    displaySignInForm();
                    displaySignUpBtn();
                }
            }
        }
        $connection->close();
    }

    function validatePassword($pw)
    {
        $passwordRegex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%]).{8,24}$/";
        return preg_match($passwordRegex, $pw) === 1;
    }

    function validateUsername($un)
    {
        $nameRegex = "/^[a-z,A-Z,0-9]{1,24}$/";
        return preg_match($nameRegex, $un) === 1;
    }

    function validateEmail($email)
    {
        $emailRegex = '/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
        return preg_match($emailRegex, $email) === 1;
    }

    function displaySignInForm()
    {
        echo <<<_END
        <hr/>
        <pre>
        █░░ █▀█ █▀▀ █ █▄░█
        █▄▄ █▄█ █▄█ █ █░▀█
        </pre>
        _END;

        echo <<< _END
        <form action="loginPage.php" method="post">
        <pre>
        <label for="login_userid">Username</label>
        <input type="text" id="login_userid" name="login_userid" value="" value="" placeholder="Enter Username" required>
        <label for="login_password">Password</label>
        <input type="password" id="login_password" name="login_password" value="" placeholder="Enter Password" required>
        <input type="submit" id="login_submitBtn" name="loginBtn" value="Login">
        </pre>
        </form>
        _END;
    }

    function displayRegisterForm()
    {
        echo <<< _END
        <hr/>
        <pre>
        █▀█ █▀▀ █▀▀ █ █▀ ▀█▀ █▀█ ▄▀█ ▀█▀ █ █▀█ █▄░█
        █▀▄ ██▄ █▄█ █ ▄█ ░█░ █▀▄ █▀█ ░█░ █ █▄█ █░▀█
        </pre>
        _END;

        echo <<< _END
        <form action="loginPage.php" method="post">
        <pre>
        <b><label for="register_username">Username</label></b>
        <p id='register_username' class="offscreen"> Username must be under 24 characters and contain only alphanumeric characters. </p>
        <input type="text" id="register_username" name="register_username" value="" size='50' placeholder='Enter a Username' required>
        
        <b><label for="register_email">Email</label></b>
        <p id='register_email_info' class="offscreen"> Please enter a valid email. </p>
        <input type="email" id='register_email_textbox' name="register_email" value="" size='50' placeholder='Enter Email' required>
        
        <b><label for="register_password">Password</label></b> 
        <p id='register_password_info' class="offscreen">Password should be at least 8 characters and contain at least <br/>1 Upper case, 1 Lower case, 1 number, and 1 special character. </p>
        <input type="password" id='register_password_textbox' name="register_password" value="" size='50' placeholder='Enter Password' required>
        <input type="submit" id='registerBtn' name="registerBtn" value="Register">
        </pre>
        </form>
        _END;
    }

    function displaySignInBtn()
    {
        echo "Already have an account, click the sign in button below! <br/>";

        echo <<< _END
        <form action="loginPage.php" method="post">
        <pre> <input type="hidden" name="signInBtnClicked" value="yes">
        <input type="submit" name="signInBtn" value="Sign In">
        </pre>
        </form>
        _END;
    }

    function displaySignUpBtn()
    {
        echo "Don't have an account? Click the register button below! <br/>";

        echo <<< _END
        <form action="loginPage.php" method="post">
        <pre>
        <input type="hidden" name="signUpBtnClicked" value="yes">
        <input type="submit" name="signUpBtn" value="Sign Up">
        </pre>
        </form>
        _END;
    }


    // SANTIZE FUNCTIONS
	function mysql_entities_fix_string($conn, $string) {
		return htmlentities(mysql_fix_string($conn, $string));
	}

	function mysql_fix_string($conn, $string) {
		return $conn->real_escape_string($string);
	}

	function get_post($conn, $var)
	{
		return $conn->real_escape_string($_POST[$var]);
	}
?>
