<?php
    

function write_ini_file($assoc_arr, $path, $has_sections=FALSE) { 
    $content = ""; 
    if ($has_sections) { 
        foreach ($assoc_arr as $key=>$elem) { 
            $content .= "[".$key."]\n"; 
            foreach ($elem as $key2=>$elem2) { 
                if(is_array($elem2)) 
                { 
                    for($i=0;$i<count($elem2);$i++) 
                    { 
                        $content .= $key2."[] = \"".$elem2[$i]."\"\n"; 
                    } 
                } 
                else if($elem2=="") $content .= $key2." = \n"; 
                else $content .= $key2." = \"".$elem2."\"\n"; 
            } 
        } 
    } 
    else { 
        foreach ($assoc_arr as $key=>$elem) { 
            if(is_array($elem)) 
            { 
                for($i=0;$i<count($elem);$i++) 
                { 
                    $content .= $key."[] = \"".$elem[$i]."\"\n"; 
                } 
            } 
            else if($elem=="") $content .= $key." = \n"; 
            else $content .= $key." = \"".$elem."\"\n"; 
        } 
    } 

    if (!$handle = fopen($path, 'w')) { 
        return false; 
    }

    $success = fwrite($handle, $content);
    fclose($handle); 

    return $success; 
}

function scrape_data($name)
    {
        $settings = parse_ini_file("./settings.ini");
        $encodename = rawurlencode($name);
        $key = $settings["key"];
        $url =  "https://api.rawg.io/api/games?key=$key&search='$encodename'";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        $result = json_decode($result, true);

        //Download Data to VAR
        $romname = $result['results'][0]['name'];
        $genre = $result['results'][0]['genres'][0]['name'];
        $image = $result['results'][0]['short_screenshots'][0]['image'];

        if ($romname !== "") {
            if ($image !== "") {
                $scrape = "success";
            } else {
                $scrape = "fail";
            }
       } else {
            $scrape = "fail";
       }

        //Save Data
            if ($scrape = "success") {
                if (!is_dir("./inis")) {
                    mkdir("./inis");
                }

                $inidir = "./inis";
                $ini["name"] = $romname;
                $ini["genre"] = $genre;
                write_ini_file($ini, "./inis/$name.ini", false);

                //Save Image
                if (!is_dir("./img")) {
                    mkdir("./img");
                }

                $ch = curl_init($image);
                $fp = fopen("./img/$name.png", 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);
                
                return(true);
            } else {
                return(false);
            }    
    }


?>
