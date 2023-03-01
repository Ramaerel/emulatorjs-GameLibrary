<!DOCTYPE html>
<html>
    <head>
        <title>EJS Library</title>
        <link rel="stylesheet" type="text/css" href="style.css">

        <?php
            include 'fnc.php';

            $snes = ["smc", "sfc", "fig", "swc", "bs", "st"];
            $gba = ["gba"];
            $gb = ["gb", "gbc", "dmg"];
            $nes = ["fds", "nes", "unif", "unf"];
            $vb = ["vb", "vboy"];
            $nds = ["nds"];
            $n64 = ["n64", "z64", "v64", "u1", "ndd"];

            $sms = ["sms"];
            $smd = ["smd", "md"];
            $gg = ["gg"];

        ?>
    </head>

    <body>
        <nav>
            <ul>
                <li><a href="index.php">Index</a></li>

                <?php 
                    if (isset($_COOKIE["user"])) {
                        print("
                            <li><a href='#'>Upload ROMs</a></li>
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
            <h1>Upload ROM Files</h1>

            <?php
                if(isset($_COOKIE["user"])) {
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        // Multiple file upload
                        if ($_FILES['rom-files']['error'][0] == UPLOAD_ERR_OK) {
                            foreach ($_FILES['rom-files']['tmp_name'] as $key => $tmp_name) {
                                $name = basename($_FILES['rom-files']['name'][$key]);
                                $ext = explode(".", $name);
                                $ext = end($ext);

                                if (in_array($ext, $snes)) {
                                    if(!is_dir("users/" . $_COOKIE["user"] . "/roms/snes")) {
                                        mkdir("users/" . $_COOKIE['user'] . "/roms/snes");
                                    }  
                                    move_uploaded_file($tmp_name, "users/" . $_COOKIE["user"] . "/roms/snes/" . $name);
                                    echo("<p>SNES rom $name successfully uploaded.<br /></p>");

                                } else if (in_array($ext, $gba)) {
                                    if(!is_dir("users/" . $_COOKIE["user"] . "/roms/gba")) {
                                        mkdir("users/" . $_COOKIE['user'] . "/roms/gba");
                                    }  
                                    move_uploaded_file($tmp_name, "users/" . $_COOKIE["user"] . "/roms/gba/" . $name);
                                    echo("<p>GBA rom $name successfully uploaded.<br /></p>");
                                
                                } else if (in_array($ext, $gb)) {
                                    if(!is_dir("users/" . $_COOKIE["user"] . "/roms/gb")) {
                                        mkdir("users/" . $_COOKIE['user'] . "/roms/gb");
                                    }  
                                    move_uploaded_file($tmp_name, "users/" . $_COOKIE["user"] . "/roms/gb/" . $name);
                                    echo("<p>GB rom $name successfully uploaded.<br /></p>");

                                } else if (in_array($ext, $nes)) {
                                    if(!is_dir("users/" . $_COOKIE["user"] . "/roms/nes")) {
                                        mkdir("users/" . $_COOKIE['user'] . "/roms/nes");
                                    }  
                                    move_uploaded_file($tmp_name, "users/" . $_COOKIE["user"] . "/roms/nes/" . $name);
                                    echo("<p>NES rom $name successfully uploaded.<br /></p>");

                                } else if (in_array($ext, $vb)) {
                                    if(!is_dir("users/" . $_COOKIE["user"] . "/roms/vb")) {
                                        mkdir("users/" . $_COOKIE['user'] . "/roms/vb");
                                    }  
                                    move_uploaded_file($tmp_name, "users/" . $_COOKIE["user"] . "/roms/vb/" . $name);
                                    echo("<p>VB rom $name successfully uploaded.<br /></p>");

                                } else if (in_array($ext, $nds)) {
                                    if(!is_dir("users/" . $_COOKIE["user"] . "/roms/nds")) {
                                        mkdir("users/" . $_COOKIE['user'] . "/roms/nds");
                                    }  
                                    move_uploaded_file($tmp_name, "users/" . $_COOKIE["user"] . "/roms/nds/" . $name);
                                    echo("<p>NDS rom $name successfully uploaded.<br /></p>");
                                    
                                } else if (in_array($ext, $n64)) {
                                    if(!is_dir("users/" . $_COOKIE["user"] . "/roms/n64")) {
                                        mkdir("users/" . $_COOKIE['user'] . "/roms/n64");
                                    }  
                                    move_uploaded_file($tmp_name, "users/" . $_COOKIE["user"] . "/roms/n64/" . $name);
                                    echo("<p>N64 rom $name successfully uploaded.<br /></p>");

                                } else if (in_array($ext, $sms)) {
                                    if(!is_dir("users/" . $_COOKIE["user"] . "/roms/segaMS")) {
                                        mkdir("users/" . $_COOKIE['user'] . "/roms/segaMS");
                                    }  
                                    move_uploaded_file($tmp_name, "users/" . $_COOKIE["user"] . "/roms/segaMS/" . $name);
                                    echo("<p>SMS rom $name successfully uploaded.<br /></p>");
                                
                                } else if (in_array($ext, $smd)) {
                                    if(!is_dir("users/" . $_COOKIE["user"] . "/roms/segaMD")) {
                                        mkdir("users/" . $_COOKIE['user'] . "/roms/segaMD");
                                    }  
                                    move_uploaded_file($tmp_name, "users/" . $_COOKIE["user"] . "/roms/segaMD/" . $name);
                                    echo("<p>SMD rom $name successfully uploaded.<br /></p>");
                                
                                } else if (in_array($ext, $gg)) {
                                    if(!is_dir("users/" . $_COOKIE["user"] . "/roms/segaGG")) {
                                        mkdir("users/" . $_COOKIE['user'] . "/roms/segaGG");
                                    }  
                                    move_uploaded_file($tmp_name, "users/" . $_COOKIE["user"] . "/roms/segaGG/" . $name);
                                    echo("<p>SGG rom $name successfully uploaded.<br /></p>");

                                } else {
                                    print("<p>Error: Unsupported filetype!</p>");
                                }
                            }
                        }
                    }
                }
            ?>

            <form action="upload.php" method="post" enctype="multipart/form-data">
                <label for="rom-files">Select a ROM file (Max 20):</label>
                <br />
                <input type="file" id="rom-files" name="rom-files[]" multiple>
                <br />
                <br />
                <input type="submit" value="Upload">
            </form>

                
        </div>
    </body>
</html>
