<?php

    require './Library.php';
    require './fnc.php';

    $romdirectory = './roms/';
    $romlist = scandir($romdirectory);

    for($i=0; $i<count($romlist)-2; $i++) {
        $romlistfin[$i] = $romlist[$i + 2];
    }

    echo("<form action='NewGamesLibrary.php' method='GET' style='text-align:center;'><select name='games'>");
    for($i=0;$i<count($romlistfin);$i++) {
        $filehash = md5_file("./roms/".$romlistfin[$i]);
        $gamedata[$i] = getFromHash($filehash);
        if ($gamedata[$i]["Name"] == "NA") {
            $rom[$i] = getRomExtension($romlistfin[$i]);
            $gamedata[$i]["Name"] = $rom[$i][0];
            $gamedata[$i]["Console"] = $rom[$i][1];
            $gamedata[$i]["Region"] = "NID";
        }
        echo('<option value="'.$i.'">'.$gamedata[$i]["Name"].' ('.$gamedata[$i]["Region"].')</option>');
    }
    echo("</select><input type='submit' value='Play'></form>");

?>

<div style='width:640px;height:480px;max-width:100%;margin:auto auto;'>
    <div id='game'></div>
</div>

<script type='text/javascript'>

    EJS_player = '#game';
    EJS_core = <?php echo("'".$gamedata[$_GET['games']]["Console"]."'"); ?>;
    EJS_gameUrl = <?php echo('"./roms/'.$romlistfin[$_GET['games']].'"'); ?>;
    EJS_gameID = <?php echo($_GET['games']); ?>;
    EJS_pathtodata = 'data/';
    
</script>

<script src='data/loader.js'></script>