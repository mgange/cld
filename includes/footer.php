    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.7.2.min.js"><\/script>')</script>
    <script src="<?php echo $_SESSION['base_url']; ?>js/plugins.js"></script>
    <script src="<?php echo $_SESSION['base_url']; ?>js/bootstrap.js"></script>
    <script src="<?php echo $_SESSION['base_url']; ?>js/main.js"></script>

<?php
if(isset($config['GAcode']) && $config['GAcode'] !== '') {
?>
    <!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
    <script>
        var _gaq=[['_setAccount','<?php echo $config['GAcode']; ?>'],['_trackPageview']];
        (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
        g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
        s.parentNode.insertBefore(g,s)}(document,'script'));
    </script>
<?php
}
?>
</body>
</html>