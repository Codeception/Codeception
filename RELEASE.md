# Release process

1. Checkout this repo
2. Add remote for base repo: git remote add base git@github.com:Codeception/base.git
3. Run composer install
5. Download robo.phar file `wget https://robo.li/robo.phar`
4. Disable phar.readonly in your php.ini file, it must be `phar.readonly = Off`
6. Set VERSION in src/Codeception/Codecept.php to version number you want to release and commit this change(if it wasn't updated earlier).
7. Run `php robo.phar release` 
8. (Optional) Commit updated Codecept.php with VERSION set to next version.
