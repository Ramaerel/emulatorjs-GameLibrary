<!DOCTYPE html>
<html>
    <head>
        <title>EJSLibrary Arcade</title>

    </head>

    <body style="background-color:#333333">
        <div style='width:100vw;height:100vh;max-width:100%'>
            <div id='game'></div>
        </div>

        <script type='text/javascript'>
            EJS_player = '#game';
            EJS_core = '<?php echo($_GET['system']); ?>';
            EJS_gameUrl = '/users/<?php echo($_COOKIE['user']); ?>/roms/<?php echo($_GET['system'] . "/" . $_GET['rom']); ?>';
            EJS_pathtodata = 'data/';
        </script>
        <script src='data/loader.js'></script>
    </body>
</html>