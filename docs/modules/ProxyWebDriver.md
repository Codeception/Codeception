# ProxyWebDriver

For usage see [WebDriver](./WebDriver.md).

The only difference is, that you can configure a proxy host and port if you are inside a proxy
and want to connect to a selenium hub outside of this proxy.

To specify the proxy, add the configuration options `webdriver_proxy` and `webdriver_port` to your suite config yml inside the ProxyWebDriver module configuration:
```yaml
    modules:
       enabled:
          - ProxyWebDriver:
             url: 'http://localhost/'
             browser: chrome
             webdriver_proxy: http://your-proxy.org
             webdriver_proxy_port: 8080
```