<?php echo("\r\n<div style='width:". 640*$conf["scale"] ."px;height:". 480*$conf["scale"] ."px;max-width:100%;'>"); ?>
<button onclick="load()">Load Game</button>
<div id="game"></div>
<script type="text/javascript">
        <?php if (isset($_GET['games'])) {
            $gid = $_GET['games'];
        } else {
            $gid = 0;
        }
    ?>
    EJS_player = '#game';
    EJS_gameUrl = <?php echo('"./roms/'.$romlistfin[$gid].'"'); ?>;
    EJS_core = <?php echo("'".$gamedata[$gid]["Core"]."'"); ?>;
    function load() {
      var script = document.createElement('script');
      script.src = 'js/libretro.js'
      document.getElementsByTagName('head')[0].appendChild(script);
    }
    var retroArchCfg = {
        
    }
</script>