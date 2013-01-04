## Code Coverage

At some point you want to review which parts of your appliaction are tested well and which are not. 
Just for this case the [CodeCoverage](http://en.wikipedia.org/wiki/Code_coverage) is used. When you execute your tests to collect coverage report, 
you will receive statisitcs of all classes, methods, and lines triggered by these tests. 
The ratio between all lines in script and all touched lines is a main coverage criteria. In the ideal world you should get a 100% code coverage,
but in reality 80% are just enough. And even 100% code coverage rate doesn't save you from fatal errors and crashes. 

**Codeception has CodeCoverage tools since 1.5. To collect coverage information `xdebug` is required**.

Coverage data can be collected manually for local tests and remote tests. Remote tests may be executed on different node, 
or locally, but behind the web server. It may look hard to collect code coverage for Selenium tests or PhpBrowser tests. But Codeception
supports remote codecoverage as well as local.

### Configuration

To enable codecoverge put these lines to the global configuration file `codeception.yml`:

```
coverage:
    enabled: true
```

that's ok for now. But what files should be present in final coverage report? You can filter files by providing blacklist and whitelist filters.

```
coverage:
    enabled: true
    whitelist:
        include:
            - app/*            
        exclude:
            - app/cache/*
    blacklist:
        include:
            - app/controllers/*
        exclude:
            - app/cache/CacheProvider.php
    
```
What are whitelists and blacklists?

* A **whitelist** is a list of files that should be included in report even they were not touched.
* A **blacklist** is a list of files that should be excluded from report even they were touched.

Pass an array of files or directory to include/exclude sections. The path ending with '*' matches the directory.
Also you can use '*' mask in a file name, i.e. `app/models/*Model.php` to match all models.

There is a shortcut if you don't need that complex filters:

```
coverage:
    enabled: true
    include:
        - app/*
    exclude:
        - app/cache/*
```

`include` and `exclude` options add or remove files from whitelist. We will stay with them in this scena

All these settings can be redefined for each suite in their config files. 

## Local CodeCoverage

The basic codecoverage can be collected for functional and unit tests.
If you performed configurations steps from above you are ready to go.
All you need is to execute codeception with `--coverage` option.
To generate a clover xml report or a tasty html report append also `--xml` and `--html` options.

```
codecept run --coverage --xml --html
```

XML and HTML reports are stored to the `_logs` directory. The best way to review report is to open `index.html` from `tests/_logs/coverage` in your browser.
XML clover reports are used by IDEs (like PHPStorm) or Conitinious Integration servers (Like Jenkins).

## Remote CodeCoverage

If you run your application via Webserver (Apache, Nginx, PHP WebServer) you don't have a direct access to tested code, 
so collecting coverage becomes a non-trivial task. The same goes to scripts that are tested on different node. 
To get access to this code you need `xdebug` installed with `remote_enable` option turned on. 
Codeception also requires a little spy to interact  with your application. As your application run standalone, 
without even knowing it is being tested, a small file should be included in order to collecto coverage info. 

This file is called `c3.php` and is [available on GitHub](https://github.com/Codeception/c3). 
`c3.php` should be downloaded and included in your application in a very first line of it's from controller. 
By sending special headers Codeception will command your appliaction when to start codecoverage collection and when to stop it.
After the suite is finished, a report will be stored and Codeception will grab it from your application. 

Please, follow installation instructions described in a [readme file](https://github.com/Codeception/c3).

After the `c3.php` file is included in application you can start gather coverage. 
In case you execute your application locally there is nothing to be changed in config.
All codecoverage reports will be collected as usual and marged afterwards.
Think of it: Codeception runs remote coverage in the same way as local. 

It's never been easier to setup remote codecoverage for your application. In ay other framework. Really.

But if you run tests on different server (or your webserver doesn't use code from current directory) a single option `remote` should be added to config.
For example, let's turn on remote coverage for acceptance suite in `acceptance.suite.yml`

```
coverage:
    enabled: true
    remote: true
```

In this case remote Code Coverage results wont't be merged with local ones if this option is enabled. 
Merging is possible only in case a remote and local file have th same path. 
But in case of running tests on a remote server we are not sure of it.

## Conclusion

It's never been easier to setup local and remote code coverage. Just one config and one additional file to incldue! 
**With Codeception you can easily generate CodeCoverage reports for your Selenium tests** (or other acceptance or api tests). Mixing reports for `acceptance`, `functional`, and `unit` suites provides 
you the most complete information on which parts of your applications are tested and which are not.


