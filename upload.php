<!DOCTYPE html>
<html>

    <head>
        <title>EmulatorJS Library - Upload</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>

    <body>

        <!-- Navbar -->
        <nav>
            <ul>
                <li><a href="index.php">Arcade</a></li>
                <li><a href="#">Upload</a></li>
            </ul>
        </nav>

        <br />
        <!-- Game Arcade -->
        <div class="arcadelist">
            
            <?php
            
                include 'fnc.php';

                $settings = parse_ini_file("./settings.ini");

                //Write system extension arrays
                //  Nintendo
                $snes = ["smc", "sfc", "fig", "swc", "bs", "st"];
                $gba = ["gba"];
                $gb = ["gb", "gbc", "dmg"];
                $nes = ["fds", "nes", "unif", "unf"];
                $vb = ["vb", "vboy"];
                $nds = ["nds"];
                $n64 = ["n64", "z64", "v64", "u1", "ndd"];
                //  Sega
                $sms = ["sms"];
                $smd = ["smd", "md"];
                $gg = ["gg"];

                //Upload functionality
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    if($_FILES['rom-files']['error'][0] == UPLOAD_ERR_OK) {
                        foreach ($_FILES['rom-files']['tmp_name'] as $key => $tmp_name) {
                            $name = basename($_FILES['rom-files']['name'][$key]);
                            $ext = explode('.', $name);
                            $ext = end($ext);

                            if(!is_dir("roms")) {
                                mkdir("roms");
                            }
                            //Move File
                            move_uploaded_file($tmp_name, "roms/$name");
                            print("<p>File successfully uploaded. Scraping failed due to no key error.</p>");
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

