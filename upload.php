<!DOCTYPE html>
<html>

    <head>
        <title>EmulatorJS Library - Upload</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>

    <body>

        <!-- Navbar -->
        <?php include 'navbar.php'; ?>

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
                $gb = ["gb", "dmg"];
                $gbc = ["gbc"];
                $nes = ["fds", "nes", "unif", "unf"];
                $vb = ["vb", "vboy"];
                $nds = ["nds"];
                $n64 = ["n64", "z64", "v64", "u1", "ndd"];
                //  Sega
                $sms = ["sms"];
                $smd = ["smd", "md"];
                $gg = ["gg"];
                //  Playstation
                $psx = ["chd", "pbp"];

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

                            // find console to mkdir and move to correct folder
                            switch (true){
                                case in_array($ext, $snes):
                                    $console = 'snes';                                    
                                    break;
                                case in_array($ext, $gba):
                                    $console = 'gba';
                                    break;
                                case in_array($ext, $gb):
                                    $console = 'gb';
                                    break;
                                case in_array($ext, $nes):
                                    $console = 'nes';
                                    break;
                                case in_array($ext, $vb):
                                    $console = 'vb';
                                    break;
                                case in_array($ext, $nds):
                                    $console = 'nds';
                                    break;
                                case in_array($ext, $n64):
                                    $console = 'n64';
                                    break;
                                case in_array($ext, $sms):
                                    $console = 'sms';
                                    break;
                                case in_array($ext, $smd):
                                    $console = 'gen';                                   
                                    break;
                                case in_array($ext, $gg):
                                    $console = 'gg';
                                    break;
                                case in_array($ext, $psx):
                                    $console = 'psx';
                                    break;
                            }

                            // make dir and Move File
                            if(!is_dir("roms/$console")) mkdir("roms/$console");
                            move_uploaded_file($tmp_name, "roms/$console/$name");
                            print("<p><font style='text-transform:uppercase;'>[$console]</font> $name successfully uploaded.</p>");
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

