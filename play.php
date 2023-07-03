<!DOCTYPE html>
<html>
    <head>
        <?php

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
            //  Other
            $psx = ["pbp", "chd"];


            //Find console
            $name = basename($_GET['game']);
            $ext = explode(".", $name);
            $ext = end($ext);

            $inidata = parse_ini_file("./inis/$name.ini");
            
            if (in_array($ext, $nes)) { $console = 'nes'; }
            else if (in_array($ext, $snes)) { $console = 'snes'; }
            else if (in_array($ext, $n64)) { $console = 'n64'; }
            
            else if (in_array($ext, $gb)) { $console = 'gb'; }
            else if (in_array($ext, $vb)) { $console = 'vb'; }
            else if (in_array($ext, $gba)) { $console = 'gba'; }
            else if (in_array($ext, $nds)) { $console = 'nds'; }
            
            else if (in_array($ext, $sms)) { $console = 'segaMS'; }
            else if (in_array($ext, $smd)) { $console = 'segaMD'; }
            else if (in_array($ext, $gg)) { $console = 'segaGG'; }

            else if (in_array($ext, $psx)) { $console = 'psx';};
        ?>
        <title><?php echo($inidata["name"]); ?></title>
        <style>
            body {
                background-color: #333;
            }

            nav {
                background-color: #5f5f5f;
                border-radius: 5px;
            }
            
            nav ul {
                margin: 0;
                padding: 0;
                list-style: none;
                display: flex;
                justify-content: center;

            }
            
            nav a {
                display: block;
                padding: 1rem;
                text-decoration: none;
                color: #c5c5c5;
                border-radius: 5px;
            }
            
            nav a:hover {
                background-color: #868686;
                color: #fff;
            }
        </style>
    </head>

    <body>

        <nav>
            <ul>
                <li><a href="index.php">Arcade</a></li>
                <li><a href="upload.php">Upload</a></li>
                <li style="width:10%;"></li>
                <li><p>Playing: <?php echo($inidata["name"]); ?></p></li>
            </ul>
        </nav>

    <div style='width:640px;height:480px;max-width:100%;margin: auto auto;'>
        <div id='game'></div>
    </div>

    <?php

        if (file_exists("./bios/$console.bin")) {
            $bios = "EJS_biosUrl = './bios/$console.bin';";
        } else {
            $bios = "";
        }

        if ($settings["core"] == "osejs") {
            echo("
                <script type='text/javascript'>
                    EJS_player = '#game';
                    EJS_core = '$console';
                    $bios
                    EJS_gameUrl = '/roms/".$_GET['game']."';
                    EJS_pathtodata = 'data/';
                </script>
                <script src='data/loader.js'></script>
            ");
        } else if ($settings["core"] == "ejs") {
            echo("
            <script type='text/javascript'>
                EJS_player = '#game';
                EJS_gameUrl = '/roms/".$_GET['game']."';
                EJS_core = '$console';
                $bios
                EJS_mouse = false;
                EJS_multitap = false;
            </script>
            <script src='https://www.emulatorjs.com/loader.js'></script>
            ");
        }
    ?>
    
    </body>
</html>