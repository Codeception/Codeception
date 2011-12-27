<?php
chdir(__DIR__.'/..');
system('php bin/update_docs.php');
system('php bin/build_site.php');
system('php bin/build_pear.php');
system('php bin/build_phar.php');