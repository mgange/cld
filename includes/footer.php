<?php
/**
 *------------------------------------------------------------------------------
 * Global Footer File
 *------------------------------------------------------------------------------
 *
 * This file should be included at the beginning of every page that is displayed
 * to the user. This will close the .container div opened in includes/header.php
 * so the opening and closing tags in the files that include header.php and
 * footer.php should be self contained.
 *
 * This page sets up links to javascript files, conditional Google Ananlytics
 * script (based on the global config file), and closes the <body> tag.
 *
 * PHP 5.3.0
 */
?>

    </div><!-- End of .container -->

    <footer class="footer footer-fixed-bottom">
        <div class="container">
            <small>
                Lovingly handcrafted at
                <a href="http://hvtdc.org" title="HVTDC">HVTDC</a>
            </small>

            <div class="pull-right">
            <small>
                <a href="javascript:void(0);"
                            name=""
                            title=""
                            onclick="window.open(
                                '<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>about',
                                'Popup','width=850,height=600,dependent=yes,0,status=0,resizable=1,');">
                            About CLD
                        </a>
            </small>
            |
            <small>
                <a href="javascript:void(0);"
                            name=""
                            title=""
                            onclick="window.open(
                                '<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>contact',
                                'Popup','width=850,height=600,dependent=yes,0,status=0,resizable=1,');">
                            Contact Us
                        </a>
            </small>
            </div>
        </div>
    </footer>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.7.2.min.js"><\/script>')</script>
    <script src="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>js/plugins.js"></script>
    <script src="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>js/bootstrap.js"></script>
    <script src="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>js/main.js"></script>

<?php
if(isset($config['GAcode']) && $config['GAcode'] !== '') {
?>
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
