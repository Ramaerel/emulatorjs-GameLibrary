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

                        if(!is_dir("./users/".$_COOKIE["user"]."/roms/nes/")) { mkdir("./users/".$_COOKIE["user"]."/roms/nes"); }
                        if(!is_dir("./users/".$_COOKIE["user"]."/roms/snes/")) { mkdir("./users/".$_COOKIE["user"]."/roms/snes"); }
                        if(!is_dir("./users/".$_COOKIE["user"]."/roms/n64/")) { mkdir("./users/".$_COOKIE["user"]."/roms/n64"); }
                        if(!is_dir("./users/".$_COOKIE["user"]."/roms/gb/")) { mkdir("./users/".$_COOKIE["user"]."/roms/gb"); }
                        if(!is_dir("./users/".$_COOKIE["user"]."/roms/gba/")) { mkdir("./users/".$_COOKIE["user"]."/roms/gba"); }
                        if(!is_dir("./users/".$_COOKIE["user"]."/roms/nds/")) { mkdir("./users/".$_COOKIE["user"]."/roms/nds"); }
                        if(!is_dir("./users/".$_COOKIE["user"]."/roms/vb/")) { mkdir("./users/".$_COOKIE["user"]."/roms/vb"); }
                        if(!is_dir("./users/".$_COOKIE["user"]."/roms/segaMS/")) { mkdir("./users/".$_COOKIE["user"]."/roms/segaMS"); }
                        if(!is_dir("./users/".$_COOKIE["user"]."/roms/segaMD/")) { mkdir("./users/".$_COOKIE["user"]."/roms/segaMD"); }
                        if(!is_dir("./users/".$_COOKIE["user"]."/roms/segaGG/")) { mkdir("./users/".$_COOKIE["user"]."/roms/segaGG"); }


                        if (!(count(scandir("./users/".$_COOKIE["user"]."/roms/nes/")) == 2)) {
                            echo('<a class="rounded-square" href="./arcade.php?console=nes">Nintendo Entertainment System</a>');
                        }

                        if (!(count(scandir("./users/".$_COOKIE["user"]."/roms/snes/")) == 2)) {
                            echo('<a class="rounded-square" href="./arcade.php?console=snes">Super Nintendo</a>');
                        }

                        if (!(count(scandir("./users/".$_COOKIE["user"]."/roms/n64/")) == 2)) {
                            echo('<a class="rounded-square" href="./arcade.php?console=n64">Nintendo 64</a>');
                        }

                        if (!(count(scandir("./users/".$_COOKIE["user"]."/roms/gb/")) == 2)) {
                            echo('<a class="rounded-square" href="./arcade.php?console=gb">Game Boy</a>');
                        }

                        if (!(count(scandir("./users/".$_COOKIE["user"]."/roms/gba/")) == 2)) {
                            echo('<a class="rounded-square" href="./arcade.php?console=gba">Gameboy Advance</a>');
                        }

                        if (!(count(scandir("./users/".$_COOKIE["user"]."/roms/nds/")) == 2)) {
                            echo('<a class="rounded-square" href="./arcade.php?console=nds">Nintendo DS</a>');
                        }

                        if (!(count(scandir("./users/".$_COOKIE["user"]."/roms/vb/")) == 2)) {
                            echo('<a class="rounded-square" href="./arcade.php?console=vb">Nintendo Virtual Boy</a>');
                        }
                        
                        if (!(count(scandir("./users/".$_COOKIE["user"]."/roms/segaMS/")) == 2)) {
                            echo('<a class="rounded-square" href="./arcade.php?console=segaMS">Sega Master System</a>');
                        }
                        
                        if (!(count(scandir("./users/".$_COOKIE["user"]."/roms/segaMD/")) == 2)) {
                            echo('<a class="rounded-square" href="./arcade.php?console=segaMD">Sega Mega Drive</a>');
                        }

                        if (!(count(scandir("./users/".$_COOKIE["user"]."/roms/segaGG/")) == 2)) {
                            echo('<a class="rounded-square" href="./arcade.php?console=nes">Sega Game Gear</a>');
                        }
                    }


                    
                ?>
            </div>    
        </div>
    </body>
</html>
