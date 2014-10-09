Raygun Integration for Magento - free
=====================================


Exceptional Error Tracking Integration. Easy integrate raygun into magento.


* Async helper client

```
$client = new \Raygun4php\RaygunClient("apiKey", $useAsyncSending);
```

* Debug mode 

```
$client = new \Raygun4php\RaygunClient("apiKey", $useAsyncSending, $debugMode);
```


* Custom error handler

```
customRaygunErrorHandler
```

* Raygun client helper

```
getRaygunClient
```


Compatibility
-------------
- Magento >= 1.5.1


Todo
----

- Customer env information

