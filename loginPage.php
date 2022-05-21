<?php //loginPage.php   
    require_once 'login.php';

    echo <<< _END
    <style> 
    .offscreen {position: absolute; left: -9999px;} 
    .onscreen {position: relative;}
    p {margin: 0;}
    .container { 
        max-width: 480px;
        margin: 20px auto 20px auto;}
    </style>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <title> Login Page</title>
    <body>
    _END;

    echo "<div style='text-align: center;'><h1>Task Master</h1></div>";
    $connection = new mysqli($hn, $un, $pw, $db);

    if ($connection->connect_error) {
        echo $connection->connect_error;
    }
    else {
        session_start();
        if( isset($_SESSION['userid'])) {
            echo "<div class='container'> <h3>Hi ".$_SESSION['username'].", you are now logged in!</h3>";
            echo "<h3><a href=homepage.php>Click here to continue</a></h3></div>";
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
                        echo "<h3 class='container'>Something went wrong. Please Try again!</h3>";
                    }
                    elseif ($result->num_rows) {
                        $row = $result->fetch_array(MYSQLI_NUM);
                        $result->close();
        
                        if (password_verify($pw_temp, $row[3])) {
                            $_SESSION['userid'] = $row[0];
                            $_SESSION['username'] = $row[1];
                            $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                            $_SESSION['check'] = hash('ripemd128', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

                            echo "<div class='container'><h3>Hi $row[1], you are now logged in!</h3>";
                            echo "<h3><a href=homepage.php>Click here to continue</a></h3><br/></div>";
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

    echo "</body>";
    function validatePassword($pw)
    {
        $passwordRegex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%]).{8,24}$/";
        return preg_match($passwordRegex, $pw) === 1;
    }

    function validateUsername($un)
    {
        $nameRegex = "/^[a-z,A-Z,0-9]{4,24}$/";
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
        <div class="container">
        <h1 style="text-align: center;">Login</h1>
        _END;

        echo <<< _END
        <form action="loginPage.php" method="post">
        <div class="form-group">
        <label for="login_userid">Username</label>
        <input type="text" class="form-control" id="login_userid" name="login_userid" value="" value="" placeholder="Enter Username" required>
        </div>
        <div class="form-group">
        <label for="login_password">Password</label>
        <input type="password" class="form-control" id="login_password" name="login_password" value="" placeholder="Enter Password" required>
        </div>
        <input type="submit" id="login_submitBtn" class="btn btn-primary" name="loginBtn" value="Login">
        </form>
        </div>
        _END;
    }

    function displayRegisterForm()
    {
        echo <<< _END
        <hr/>
        <div class="container">
        <h1 style="text-align: center;">Sign Up</h1>
        _END;

        echo <<<_END
        <form action="loginPage.php" method="post">
        <div class="form-group">
        <b><label for="register_username">Username</label></b>
        <p id='register_username_info' class="offscreen"> Username must be under between 4-24 characters and contain only alphanumeric characters. </p>
        <input type="text" class="form-control" id="register_username" name="register_username" value="" size='50' placeholder='Enter a Username' required>
        </div>
        <div class="form-group">
        <b><label for="register_email">Email</label></b>
        <p id='register_email_info' class="offscreen"> Please enter a valid email. </p>
        <input type="email" class="form-control" id='register_email_textbox' name="register_email" value="" size='50' placeholder='Enter Email' required>
        </div>
        <div class="form-group">
        <b><label for="register_password">Password</label></b> 
        <p id='register_password_info' class="offscreen">Password should be at least 8 characters and contain at least <br/>1 Upper case, 1 Lower case, 1 number, and 1 special character. </p>
        <input type="password" class="form-control" id='register_password_textbox' name="register_password" value="" size='50' placeholder='Enter Password' required>
        </div>
        <input type="submit" id='registerBtn' class="btn btn-info" name="registerBtn" value="Sign Up">
        </form>
        </div>
        <script>
        (() => {
            var elRegisterUsername = document.getElementById('register_username');
            var elRegisterUsernameInfo = document.getElementById('register_username_info');
            var usernameValid = false;
            var emailValid = false;
            var passwordValid = false;
        
            elRegisterUsername.addEventListener('keyup', (e) => {  
                console.log(e.target.value);
                if( !validateUsername(e.target.value) )
                {   
                    console.log("Not Valid Full Name");
                    usernameValid = false;
                    elRegisterUsernameInfo.classList.remove('offscreen');
                    elRegisterUsernameInfo.classList.add('onscreen');
                }
                else
                {
                    console.log("Valid Full Name");
                    usernameValid = true;
                    elRegisterUsernameInfo.classList.remove('onscreen');
                    elRegisterUsernameInfo.classList.add('offscreen');
                }
                toggleDisableRegisterForm();
            });
        
            var elRegisterEmail = document.getElementById('register_email_textbox');
            var elRegisterEmailInfo = document.getElementById('register_email_info');
            elRegisterEmail.addEventListener('keyup', (e) => {  
                console.log(e.target.value.toLowerCase());
                if( !validateEmail(e.target.value) )
                {   
                    console.log("Not Valid Email");
                    emailValid = false;
                    elRegisterEmailInfo.classList.remove('offscreen');
                    elRegisterEmailInfo.classList.add('onscreen');
                }
                else
                {
                    console.log("Valid Email");
                    emailValid = true;
                    elRegisterEmailInfo.classList.remove('onscreen');
                    elRegisterEmailInfo.classList.add('offscreen');
                }
                toggleDisableRegisterForm();
            });
        
            var elRegisterPassword = document.getElementById('register_password_textbox');
            var elRegisterPasswordInfo = document.getElementById('register_password_info');
            elRegisterPassword.addEventListener('keyup', (e) => {  
                console.log(e.target.value);
                if( !validatePassword(e.target.value) )
                {   
                    console.log("Not Valid Password");
                    passwordValid = false;
                    elRegisterPasswordInfo.classList.remove('offscreen');
                    elRegisterPasswordInfo.classList.add('onscreen');
                }
                else
                {
                    console.log("Valid Password");
                    passwordValid = true;
                    elRegisterPasswordInfo.classList.remove('onscreen');
                    elRegisterPasswordInfo.classList.add('offscreen');
                }
                toggleDisableRegisterForm();
            });
        
            function validateUsername(username) {
                const unRegex = /^[A-Z,a-z,0-9]{4,24}$/;
                return unRegex.test(username);
            }
        
            function validateEmail(email) {
                const emailRegex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return emailRegex.test(email);
            }
        
            function validatePassword(password) {
                const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%]).{8,24}$/;
                return passwordRegex.test(password);
            }
        
            var elRegisterSubmitBtn = document.getElementById('registerBtn');
            elRegisterSubmitBtn.disabled=true;
            function toggleDisableRegisterForm() {
                if(usernameValid && emailValid && passwordValid)
                {
                    elRegisterSubmitBtn.disabled = false;
                }
                else
                {
                    elRegisterSubmitBtn.disabled = true;
                }
            }
        }) ();
        </script>
        _END;
    }

    function displaySignInBtn()
    {
        echo "<div class='container'>";
        echo "Account created successfully, click the button below to log in! <br/>";

        echo <<< _END
        <form action="loginPage.php" method="post">
        <input type="hidden" name="signInBtnClicked" value="yes">
        <input type="submit" class="btn btn-primary" name="signInBtn" value="Sign In">
        </form>
        _END;
        echo "</div>";
    }

    function displaySignUpBtn()
    {
        echo "<div class='container'>";
        echo "Don't have an account? Click the button below to sign up! <br/>";

        echo <<< _END
        <form action="loginPage.php" method="post">
        <input type="hidden" name="signUpBtnClicked" value="yes">
        <input type="submit" class="btn btn-info" name="signUpBtn" value="Sign Up">
        </form>
        _END;
        echo "</div>";
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
