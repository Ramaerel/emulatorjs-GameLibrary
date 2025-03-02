<!DOCTYPE html>
<html>
    <head>
        <title>EmulatorJS Library - Arcade</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>

    <body onload="showGames('')">
        <!-- Navbar -->
		<?php include 'navbar.php'; ?>

        <br />
        <!-- Game Arcade -->	
        <div class="arcadelist">
        	<script>
        		function showAll(){
        			var games = "<?php
        			    $current_dir = $_SERVER['REQUEST_URI'];
        			    $files = array_diff(scandir("./roms$current_dir"), array('..', '.'));
        			    foreach ($files as $file) {
							// if base dir show consoles, else show games
        			        if ($current_dir=='/') {
								if (file_exists("./img/$file.png")){
									echo("<a href='$file'><img class='linkimg' src='img/$file.png'/></a>");
								} else {
									echo("<a href='$file' class='link'><li>$file</li></a>");
								}
							} else {
     			        		$file_url = 'play.php?game=' . urlencode($current_dir) . '/' . urlencode($file);
        			        	echo("<a href='$file_url' class='link'><li>$file</li></a>");
							}
   
        			    }
        			?>"
        			return games
        		}

				function showGames(str){
					if (str.length == 0){
						document.getElementById("gameResults").innerHTML = showAll();
						return;
					} else {
						var xmlhttp = new XMLHttpRequest();
						xmlhttp.onreadystatechange = function() {
							if (this.readyState == 4 && this.status == 200){
								document.getElementById("gameResults").innerHTML = this.responseText;
							}
						};
						var dir = "<?php echo(substr($_SERVER['REQUEST_URI'],1)); ?>";
						xmlhttp.open("GET", "getgames.php?query=" + str + "&dir=" + dir, true);
						xmlhttp.send();
					}
				}
        	</script>		
			
			<br>
        	<form action="">
        		<input type="text" id="name" name="name" onkeyup="showGames(this.value)">
        	</form>	
        	<style media="screen">
        		ul.b {
        			display: grid;
        			grid-template-columns: auto auto auto;
        			grid-auto-flow: row;
        			list-style: none;
        		}
        	</style>
			
	       	<ul id="gameResults" class="b"></ul>
        </div>
    </body>
</html>
