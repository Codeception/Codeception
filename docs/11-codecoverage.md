## Code Coverage

At some point you want to review which parts of your appliaction are tested well and which are not. 
Just for this case the [CodeCoverage](http://en.wikipedia.org/wiki/Code_coverage) is used. When you execute your tests to collect coverage report, 
you will receive statisitcs of all classes, methods, and lines triggered by these tests. 
The ratio between all lines in script and all touched lines is a main coverage criteria. In the ideal world you should get a 100% code coverage,
but in reality 80% are just enough. And even 100% code coverage rate doesn't save you from fatal errors and crashes. 

Codeception has codecoverage tools since 1.5. To collect coverage information `xdebug` is required.

Coverage data can be collected manually for local tests and remote tests. Remote tests may be executed on different node, 
or locally, but behind the web server. It may look hard to collect code coverage for Selenium tests or PhpBrowser tests. But Codeception
support remote codevoerage as well as local codecoverage. If your tests are run locally coverage reports are merged.

### Configuration

To enable global codecovergae put these lines to the global configuration file `codeception.yml`:

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

* A whitelist is a list of files that should be included in report even they were not touched.
* A blacklist is a list of files that should be excluded from report even they were touched.

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

## Local CodeCoverage

The basic codecoverage can be collected for functional and unit tests. This requires some configuration.
