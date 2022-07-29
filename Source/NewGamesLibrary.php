<?php

    require './fnc.php';
    if(file_exists("./config.ini")) {
        $conf = parse_ini_file("config.ini");
        echo('<html style="background-color:'.$conf["bgcolor"].';">');
        $romdirectory = $conf["rom_dir"];
        if(is_dir($romdirectory)) {
            $gamedata = buildRomList($romdirectory);
        } else {
            mkdir($romdirectory);
            $gamedata = buildRomList($romdirectory);
        }
    } else {
        echo('<html style="background-color:#333333;">');
        $romdirectory = './roms/';
        $gamedata = buildRomList($romdirectory);

    }
    $romlist = scandir($romdirectory);
    for($i=0; $i<count($romlist)-2; $i++) {
        $romlistfin[$i] = $romlist[$i + 2];
    }

    
    
?>



<?php 
    if($conf["core"]=="ejs") { include("./ejs.php"); } 
    elseif($conf["core"]=="ljs") { include("./ljs.php"); } 
    else { include("./ejs.php"); }
?>
