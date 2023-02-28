<?php 
    if (isset($_POST["un"])) {
        if (!is_dir("./users")) { 
            mkdir("./users");
        }
        if (is_dir("./users/".$_POST["un"])) {
            $errors = "User already exists";
        }

        if (strtolower($_POST["test"]) !== "f") {
            $errors = "Bot test Wrong";
        }

        if (isset($errors)) {
            print($errors);
        } else {
            mkdir("./users/".$_POST["un"]);
            mkdir("./users/".$_POST["un"]."/roms");
            mkdir("./users/".$_POST["un"]."/img");

            //Make System Directories
            mkdir("./users/".$_POST["un"]."/snes");

            
            $pwd = fopen("./users/".$_POST["un"]."/pw.d", "w");
            fwrite($pwd, md5($_POST["pw"]));
            fclose($pwd);

            setcookie("user", $_POST["un"], time() + (30 * 24 * 60 * 60));

            header("Location: index.php");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>EJS Library - Register</title>
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
                            <li><a href='#'>Register</a></li>
                            <li><a href='login.php'>Login</a></li>
                        ");
                    }
                ?>
            </ul>
        </nav>
        <br />
        <br />
        <div class="updates">
                <br />

                    <form action="register.php" method="POST">

                        <label for="un">Username: </label>
                        <input type="text" id="un" name="un" />
                        <br />
                        <br />
                        <label for="pw">Password: </label>
                        <input type="password" id="pw" name="pw" />
                        <br />
                        <br />
                        <label for="test">Bot Test: What is the the 6th letter of the English alphabet?</label>
                        <br />
                        <input type="text" id="test" name="test" />
                        <hr />
                        <input type="submit" value="Register" class="submit" />

                    </form>

                <br />
                
        </div>
    </body>
</html>