# Configuration

Default your global configuration file will be this:

```yaml
# can be changed while bootstrapping project
actor: Tester 

paths:
    # where the modules stored
    tests: tests

    # directory for fixture data    
    data: tests/_data

    # directory for support code
    support: tests/_support

    # directory for output
    log: tests/_output
    
    # directory for environment configuration
    envs: tests/_envs

settings:

    # name of bootstrap that will be used
    # each bootstrap file should be 
    # inside a suite directory.

    bootstrap: _bootstrap.php

    # randomize test order
    shuffle: true

    # by default it's false on Windows
    # use [ANSICON](http://adoxa.110mb.com/ansicon/) to colorize output.
    colors: true

    # Tests (especially functional) can take a lot of memory
    # We set a high limit for them by default.
    memory_limit: 1024M
    
    # This value controls whether PHPUnit attempts to backup global variables
    # See https://phpunit.de/manual/current/en/appendixes.annotations.html#appendixes.annotations.backupGlobals
    backup_globals: true
    

# Global modules configuration.    
modules:
    config:
        Db:
            dsn: ''
            user: ''
            password: ''
            dump: tests/_data/dump.sql
```

Suite configuration acceptance.yml

```yaml
class_name: AcceptanceTester
modules:
    # enabled modules and helpers
    enabled:
        - PhpBrowser
        - AcceptanceHelper
        - Db

    # local module configuration. Overrides the global.        
    config:
        Db:
            dsn:
```
