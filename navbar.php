<nav>
    <ul>
        <li><a href=".." class='link'>home</a></li>
        <li><a href='upload.php' class='link'>upload</a></li>
        <?php
        	foreach (array_diff(scandir("./roms"), array('..','.')) as $folder) {
        		echo("<li><a href='$folder' class='link'>$folder</a></li>");
                
                if(!is_dir("saves/$console")) mkdir("saves/$console");
                if(!is_dir("img/$console")) mkdir("img/$console");
        	}
        ?>
    </ul>
</nav>