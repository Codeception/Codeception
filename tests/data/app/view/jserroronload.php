<!DOCTYPE html>
<html>
<head>
    <title>Page with JavaScript errors on load</title>
    <script>
        function loadError() {
            var xx = document.propertyThatDoesNotExist.xyz;
        }
    </script>
</head>
<body onload="loadError()">
<p>
    This page has a JavaScript error in the onload event.
    This is often a problem to using normal Javascript injection
    techniques.
</p>
</body>
</html>
