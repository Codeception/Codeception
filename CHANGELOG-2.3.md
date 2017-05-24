#### 2.3.1

* Updated composer constraints to include PHPUnit 6.x

#### 2.3.0

* **PHPUnit 6.x** support #4142 by @MontealegreLuis. Class aliases are used, so PHPUnit 4.x and 5.x (for PHP <7) are still supported as well.  
* Suite customization. [Announcement](/05-22-2017/codeception-2-3.html#configuration-improvements)
* Installation Templates. [Announcement](/05-22-2017/codeception-2-3.html#installation-templates) 
* DotReporter introduced. Use it with 
```
codecept run --ext DotReporter
```
* `--ext` parameter added to load extensions dynamically.
* Db Populator [Announcement](/05-22-2017/codeception-2-3.html#db-populator) by @brutuscat
* [Db] New configuration defaults, cleanups are disabled: `cleanup: false`, `populate: false`. Enable them to load dumps between tests. 
* [Redis] New configuration defaults, cleanups are disabled: `cleanupBefore: 'never'` by @hchonan 
* Command `generate:phpunit` removed.
* Bootstrap `_bootstrap.php` files are disabled by default.
* Configuration changes: `actor` replaced with `actor_suffix` in global config
* Configuration changes: `class_name` replaced with `actor` in suite config


