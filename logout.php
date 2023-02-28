<!DOCTYPE html>
<html>
    <head>
        <title>EJSLibrary - Logout</title>
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
                            <li><a href='#'>Log Out</a></li>
                        ");
                    } else {
                        print("
                            <li><a href='register.php'>Register</a></li>
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
                    <?php
                        if(isset($_COOKIE["user"])) {
                            setcookie("user", "", time() - 3000);

                        } else {
                            print("<p>You must be logged in to log out.</p>");
                        }
                    ?>
                    <script>
                        window.onload = function() {
                            window.location.href = "index.php";
                        }
                    </script>
                <br />
        </div>
    </body>
</html>