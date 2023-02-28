<?php

    function buildRomList($console) {

        //Read all files from the system ROM directory
        $dir = 'users/'.$_COOKIE["user"].'/roms/'.$console.'/';
        $files = scandir($dir);

        foreach($files as $file) {
            if (!in_array($file, array('.', '..'))) {
                $file_url = 'play.php?system='.$console.'&rom=' . urlencode($file);
                echo('<a class="rounded-square" href="'.$file_url.'">'.$file.'</a>');
            }
        }
    }

?>