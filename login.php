<?php

session_start();
$msg = "";

if (isset($_POST['Submit'])) {
    
    $username = "admin";
    $password = "12345";
    

    if (isset($_POST['Username']) && $_POST['Password'] == $password) {
        
        $_SESSION['UserData']['Username'] = $username;
        header("location:index.php");
        exit;
    } else {
        
        $msg = "<p class='alert alert-danger'>Username or password is incorrect</p>";
    }
}
?>


<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="https://bootswatch.com/paper/bootstrap.min.css" rel="stylesheet">
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
    <title>Login Page</title>

    <style type="text/css">
        .cntr {
            width: 350px;
            height: 200px;
            position: absolute;
            left: 50%;
            top: 50%;
            margin: -350px 0 0 -200px;
        }
    </style>

</head>

<body>
    <div class="container">
        <div class="row">

            <div class="col-md-6 col-md-offset-3 cntr">

                <img class="img-responsive" src="images/bau.png" alt="Chania">
                <div align="center" valign="top"><?php echo $msg;?></div>

                <form action="" method="post">

                    <div class="form-group">
                        <input type="text" class="form-control" id="inputEmail" placeholder="Username" name="Username">
                       
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" id="inputPassword" placeholder="Password" name="Password">
                      
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox"> Remember me</label>
                    </div>
                    <button type="submit" name="Submit" class="btn btn-primary">Login</button>

                </form>
            </div>
        </div>
    </div>
    </div>
</body>

</html>