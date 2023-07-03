
# Game Library [![Badge License]][License]

*A game library extension for* ***[EmulatorJS]***

<br>

This add - on site allows users of EmulatorJS to manage ROMs, including a built-in (albeit basic) data and image scraper.<br>

PSX has returned!
<br>

## Installation

This is a drag and drop extension, with the <br>
exception that it requires something to host <br>
PHP files like XAMPP. After that, simply upload your roms.<br>
Bulk rom uploading has been added to make this easier.

For image scraping, you will need to make an account with <br>
https://rawg.io/apidocs and create an API key. <br>
The API key will then need to be pasted into <br>
the settings.ini file, under "key".

## BIOS setup

To add BIOs for the systems that require it, simply<br />
add the BIOs and rename it to *console name*.bin.<br />
For example, the gba bios would be kept as /bios/gba.bin <br />

For systems with multiple bios files, like psx or nds,<br />
compress the files into a ZIP file and rename it from .zip<br />
to .bin. This will allow the emulator to load the correct file<br />
when required.


<!----------------------------------------------------------------------------->

[Badge License]: https://img.shields.io/badge/license-GPL-blue

[EmulatorJS]: https://github.com/EmulatorJS/emulatorjs

[License]: #
                
