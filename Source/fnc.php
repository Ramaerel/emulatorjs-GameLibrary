
<?php

    function buildRomList($romdir) {
        $romlist = scandir($romdir);

        for($i=0; $i<count($romlist)-2; $i++) {
            $romlistfin[$i] = $romlist[$i + 2];
        }

        echo("<form action='NewGamesLibrary.php' method='GET' style='text-align:center;'><select name='games'>");
        for($i=0;$i<count($romlistfin);$i++) {
            $rom[$i] = getRomExtension($romlistfin[$i]);
            $gamedata[$i]["Name"] = $rom[$i][0];
            $gamedata[$i]["Console"] = $rom[$i][1];
            echo("\r\n<option value='".$i."'>".$gamedata[$i]["Name"]." (".$gamedata[$i]["Console"].")</option>");
        }
        echo("\r\n</select>\r\n<input type='submit' value='Play'></form>");
        return $gamedata;

    }

    function getRomExtension($rom) {
            $romdata = explode(".", $rom);
            $romdata[2] = $rom;

            switch ($romdata[1]) {

                //NES formats
                case "nes":
                    $romdata[1] = "nes";
                    break;
                case "fds":
                    $romdata[1] = "nes";
                    break;
                case "unif":
                    $romdata[1] = "nes";
                    break;
                case "unf":
                    $romdata[1] = "nes";
                    break;

                //SNES formats
                case "smc":
                    $romdata[1] = "snes";
                    break;
                case "fig":
                    $romdata[1] = "snes";
                    break;
                case "sfc":
                    $romdata[1] = "snes";
                    break;
                case "gd3":
                    $romdata[1] = "snes";
                    break;
                case "gd7":
                    $romdata[1] = "snes";
                    break;
                case "dx2":
                    $romdata[1] = "snes";
                    break;
                case "bsx":
                    $romdata[1] = "snes";
                    break;
                case "swc":
                    $romdata[1] = "snes";
                    break;
                
                //Gameboy Formats
                case "gb":
                    $romdata[1] = "gb";
                    break;
                case "gbc":
                    $romdata[1] = "gb";
                    break;

                //Gameboy Advanced Formats
                case "gba":
                    $romdata[1] = "gba";
                    break;
                
                //Nintendo DS Formats
                case "nds":
                $romdata[1] = "nds"; 
                break;

                //Nintendo 64 Formats
                case "n64":
                    $romdata[1] = "n64";
                    break;
                case "z64";
                    $romdata[1] = "n64";
                    break;

                //Visual Boy Formats
                case "vb":
                    $romdata[1] = "vb";
                    break;

                //Sega Master System Formats
                case "sms":
                    $romdata[1] = "segaMS";
                    break;
                
                //Sega Mega Drive Formats
                case "md":
                    $romdata[1] = "segaMD";
                    break;
                
                //Sega Game Gear Formats
                case "gg":
                    $romdata[1] = "segaGG";
                    break;
                
                //Sega 32x Formats
                case "sega32x":
                    $romdata[1] = "sega32x";
                    break;
                
                //Atari 2600 Formats
                case "a26":
                    $romdata[1] = "atari2600";
                    break;
                
                //Atari 7800 Formats
                case "a78":
                    $romdata[1] = "atari7800";
                    break;

                //Atari Lynx Formats
                case "lnx":
                    $romdata[1] = "lynx";
                    break;

                //Atari Jaguar Formats
                case "j64":
                    $romdata[1] = "jaguar";
                    break;

                //PSX Formats
                case "psx":
                    $romdata[1] = "psx";
                    break;


            }

            return $romdata;
        }
?>