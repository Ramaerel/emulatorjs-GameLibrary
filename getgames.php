<?php

// get query and dir parameters from url
$query = $_REQUEST["query"];
$dir = $_REQUEST["dir"];

// scan directory into files array
$files = array_diff(scandir("./roms/$dir"), array('..', '.'));

// if base dir scan all folders for games into array
if ($dir == ""){
    foreach ($files as $folder){
        $romfiles = array_diff(scandir("./roms/$folder"), array('..', '.'));
        foreach ($romfiles as $file){
            $games[$file] = $folder;
        }
    }
} else {
    foreach ($files as $file){
        $games[$file] = $dir;
    }
}

// search games array for query
if ($query !== "") {
    foreach($games as $name => $console) {
        if (stristr($name, $query)) {
            $matches[$name] = $console;
        }
    } 
} 

// Output matches with links
foreach ($matches as $name => $console){
  	$fileurl = 'play.php?game=/' . urlencode($console) . '/' . urlencode($name);
    $output = "<a href='$fileurl' class='link'>";
    if ($dir == ""){
        $output = $output ."<b>".strtoupper($console)."</b><p>";
    }
    $output = $output . "<li>$name</li></a>";
	  echo($output);
}

?>
