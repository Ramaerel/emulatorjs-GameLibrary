# emulatorjs-GameLibrary
An addition to ethan's EmulatorJS, which is a basic games library that reads the contents of a roms folder for its library (?) as requested. Not the prettiest, but it works


<h1>Adding to the Library</h1>
To add to the library, use the following template: 

case "file_MD5":
  $data["Name"] = Game_Name;
  $data["Console"] = Game_Console; (According to the EmulatorJS system abbreviations)
  $data["Region"] = Game_Region;
  break;
  
Example:

case "3d45c1ee9abd5738df46d2bdda8b57dc":
  $data["Name"] = "Pokemon Red";
  $data["Console"] = "gb";
  $data["Region"] = "USA";
  break;
  
  
Add the  case statement underneath the last break; you see in Library.php. Additions to the Library on here are very welcome!
                
