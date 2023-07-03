<!DOCTYPE html>
<html>

    <head>
        <title>EmulatorJS Library - Arcade</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>

    <body>

        <!-- Navbar -->
        <nav>
            <ul>
                <li><a href="#">Arcade</a></li>
                <li><a href="upload.php">Upload</a></li>
            </ul>
        </nav>

        <br />
        <!-- Game Arcade -->
        <div class="arcadelist">
            <?php
                $files = scandir("./roms/");
                foreach($files as $file) {
                    if(!in_array($file, array('.', '..'))) {
                        $file_url = 'play.php?game=' . urlencode($file);
                        if (file_exists("./img/$file.png")) {
                            echo("<a href='".$file_url."'><img class='linkimg' src='img/".$file.".png '/></a>");
                        } else {
                            echo("<a href='$file_url' class='link'><p>$file</p></a>");
                        }
                        
                    }
                }
            ?>
        
        </div>
    </body>
</html>