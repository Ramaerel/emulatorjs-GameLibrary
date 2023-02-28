<!DOCTYPE html>
<html>
    <head>
        <title>EJSLibrary</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>

    <body>
        <nav>
            <ul>
                <li><a href="#">Index</a></li>

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
                            <li><a href='login.php'>Login</a></li>
                        ");
                    }
                ?>
            </ul>
        </nav>
        <br />
        <br />
        <div class="updates">
                <h1>I dunno. Put news or something here.</h1>

                
        </div>
    </body>
</html>