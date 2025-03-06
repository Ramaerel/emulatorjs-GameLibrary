# RetroHub [![Badge_License]][License]
*Formerly EJS Library*
*A game library and rom management tool using ***[EmulatorJS]***

<br>

This site allows users to run and manage their ROMs, using EmulatorJS and the RetroAchievements website.

## Disclaimer
I will no longer be working on this project; this final update was purely me learning how to program better for my new project

If you wish for functionality like this, but are unsure how to set it up yourself, check out ***[Temporus]***.

## Features

### Modern Game Library Interface

Grid and list views for your ROM collection
Search and filter by console type
Game cards with cover art and metadata


### Powerful ROM Management

Bulk upload capability for ROMs
Automatic console detection based on file extension
Support for various ROM formats including ZIP files


### RetroAchievements Integration

Automatic game metadata and images from RetroAchievements
Game info including developer, publisher, release date
Cover art, screenshots, and title screens


### Profile System

Netflix-style profile switching for family sharing
Custom avatars for each profile
Independent save states per profile


### Advanced Save State System

Multiple save slots per game
Screenshot preview of each save state
Seamless saving/loading during gameplay


### BIOS Management

Upload and manage BIOS files for various systems
Visual indication of installed BIOS files
System-specific BIOS requirements reference


### Cloud Save Support

Server-side save state management
Persistent game progress across sessions
Backup protection for your progress



### Installation

Requires a PHP-enabled web server (XAMPP, WAMP, or similar)
Copy all files to your web server's document root
Ensure proper permissions for cache and save directories
Access the site via your web browser

### RetroAchievements Setup
(This is for direct access. If using a Proxy, I have one set up already and linked in :)
Go to Settings → RetroAchievements
Enable RetroAchievements integration
Choose between direct API access (requires your own API key) or proxy mode
If using direct access, enter your RetroAchievements API key from your account's control panel
Save settings and enjoy enhanced game metadata and images

### BIOS Requirements
Some systems require BIOS files to function correctly. Upload your BIOS files through the BIOS management page. Common requirements include:

PlayStation: SCPH5500.bin, SCPH5501.bin, SCPH5502.bin
Game Boy Advance: gba_bios.bin
Nintendo DS: bios7.bin, bios9.bin, firmware.bin
Sega CD: bios_CD_U.bin, bios_CD_J.bin, bios_CD_E.bin

### Support
This project is no longer actively maintained. For similar functionality with professional support, please visit Temporus.
License
RetroHub is released under the GPL license.

### Credits

EmulatorJS - Emulation core  <br>
RetroAchievements - Game metadata and images

[Badge_License]: https://img.shields.io/badge/license-GPL-blue

[EmulatorJS]: https://github.com/EmulatorJS/emulatorjs

[Temporus]: https://temporus.one/

[License]: #
                
