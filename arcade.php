<!DOCTYPE html>
<html>
    <head>
        <title>EJSLibrary</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <?php include 'fnc.php'; ?>
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
                            <li><a href='login.php'>Login</a></li>
                        ");
                    }
                ?>
            </ul>
        </nav>
        <br />
        <br />
        <div class="updates">
            <div class="grid-container">
                <?php
                    // Get console type - Print console select if no console

                    if ((isset($_GET["console"])) and ($_GET["console"] !== "")) {
                        buildRomList($_GET["console"]);

                    } else {
                        echo('<a class="rounded-square" href="./arcade.php?console=nes">Nintendo Entertainment System</a>');
                        echo('<a class="rounded-square" href="./arcade.php?console=snes">Super Nintendo</a>');
                        echo('<a class="rounded-square" href="./arcade.php?console=n64">Nintendo 64</a>');

                        echo('<a class="rounded-square" href="./arcade.php?console=gb"> Gameboy (Color)</a>');
                        echo('<a class="rounded-square" href="./arcade.php?console=gba">Gameboy Advance</a>');
                        echo('<a class="rounded-square" href="./arcade.php?console=nds">Nintendo DS</a>');

                        echo('<a class="rounded-square" href="./arcade.php?console=vb">Nintendo Virtual Boy</a>');

                        echo('<a class="rounded-square" href="./arcade.php?console=segaMS">Sega Master System</a>');
                        echo('<a class="rounded-square" href="./arcade.php?console=segaMD">Sega Mega Drive</a>');
                        echo('<a class="rounded-square" href="./arcade.php?console=segaGG">Sega Game Gear</a>');

                    }


                    
                ?>
            </div>    
        </div>
    </body>
</html>