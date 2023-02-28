<?php 
    if (isset($_POST["un"])) {

        if (!is_dir("./users/".$_POST["un"])) {
            $errors = "User does not exist";
        } else {
            $pass = file_get_contents("./users/".$_POST["un"]."/pw.d");
            if (md5($_POST["pw"]) == $pass) {
                setcookie("user", $_POST["un"], time() + (30 * 24 * 60 * 60));
                header("Location: index.php");
                exit();
            } else {
                $errors = "Incorrect Password";
            }
        }
        print($errors);
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>EJSLibrary - Login</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>

    <body>
        <nav>
            <ul>
                <li><a href="index.php">Index</a></li>

                <?php 
                    if (isset($_COOKIE["user"])) {
                        print("
                            <li><a href='upload.php'>Upload ROMs</a></li>
                            <li><a href='arcade.php'>Play Games</a></li>
                            <li><a href='logout.php'>Log Out</a></li>
                        ");
                    } else {
                        print("
                            <li><a href='register.php'>Register</a></li>
                            <li><a href='#'>Login</a></li>
                        ");
                    }
                ?>
            </ul>
        </nav>
        <br />
        <br />
        <div class="updates">
                <br />
                <form action="login.php" method="POST">

                    <label for="un">Username: </label>
                    <input type="text" id="un" name="un" />
                    <br />
                    <br />
                    <label for="pw">Password: </label>
                    <input type="password" id="pw" name="pw" />
                    <hr />
                    <input type="submit" value="Login" class="submit" />

                </form>

                <br />
                
        </div>
    </body>
</html>