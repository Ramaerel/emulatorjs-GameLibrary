
# Game Library [![Badge License]][License]

*A game library extension for* ***[EmulatorJS]***

<br>

This add - on reads the contents of your **ROM**s <br>
folder and uses it to display a games library. Roms <br>
with common endings, like .iso, .bin or .zip, you <br>
must also include the applicable extension before it. <br>
A list is included in this readme with the additional<br>
extensions, and will be updated as I go. <br>

**Example:** Crash Bandicoot.psx.zip

To use systems that require a bios, like psx, <br>
add the bios under the **BIOS** folder and name <br>
it the according system .bin <br>

**Example:** psx.bin 

<br>

## Installation

Please use the following template and add <br>
the code underneath the last `break` , like <br>
it is done in [`Library.php`]

<br>

### Template

```php
case "file_MD5":

$data["Name"] = Game_Name;

$data["Console"] = Game_Console; // According to the EmulatorJS system abbreviations

$data["Region"] = Game_Region;

break;
```

<br>

### Example

```php
case "3d45c1ee9abd5738df46d2bdda8b57dc":

$data["Name"] = "Pokemon Red";

$data["Console"] = "gb";

$data["Region"] = "USA";

break;
```

<br>

### Extensions
The following extensions are used in <br>
addition to common file extensions <br>

PlayStation - .psx.*

## Contributions

Additions to the library on here are very welcome!


<!----------------------------------------------------------------------------->

[Badge License]: https://img.shields.io/badge/license-GPL-blue

[EmulatorJS]: https://github.com/ElectronicsArchiver/emulatorjs

[`Library.php`]: Source/Library.php

[License]: #
                
