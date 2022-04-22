
# Game Library [![Badge License]][License]

*A game library extension for* ***[EmulatorJS]***

<br>

This add - on reads the contents of your **ROM**s <br>
folder and uses it to display a games library.


<h1>Adding to the Library</h1>
To add to the library, use the following template: 
<hr />

case "file_MD5":

  $data["Name"] = Game_Name;
  
  $data["Console"] = Game_Console; (According to the EmulatorJS system abbreviations)
  
  $data["Region"] = Game_Region;
  
  break;
  
  <hr />
Example:
<hr />

case "3d45c1ee9abd5738df46d2bdda8b57dc":

  $data["Name"] = "Pokemon Red";
  
  $data["Console"] = "gb";
  
  $data["Region"] = "USA";
  
  break;
  
  
  
Add the  case statement underneath the last break; you see in Library.php. Additions to the Library on here are very welcome!

<!----------------------------------------------------------------------------->

[Badge License]: https://img.shields.io/badge/License-Unknown-darkgray

[EmulatorJS]: https://github.com/ElectronicsArchiver/emulatorjs

[License]: #
                
