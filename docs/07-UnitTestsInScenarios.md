# Advanced Usage

In this chapter we will cover some technics and options that you can use to improve your testing experience and stay with better organization of your project. 

## Ineractive Console

Interactive console was added to try Codeception commands before executing them inside a test. 
This feature was introduced in 1.6.0 version. 

![console](http://img267.imageshack.us/img267/204/003nk.png)

You can execute console with 

```
php codecept.phar console suitename
```

Now you can execute all commands of appropriate Guy class and see immidiate results. That is especially useful for when used with Selenium modules. It always takes too long to launch Selenium and browser for tests. But with console you can try different selectors, and different commands, and then write a test that would pass for sure when executed.

And special hint: show your boss how you can nicely manipulate web pages with console and Selenium. With this you can convince him that it is easy to automate this steps and introduce acceptance testing to the project.

## Cest Classes

