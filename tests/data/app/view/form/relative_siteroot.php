<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test submitting a form with a relative site-root URL as its action, and a configured 'Url' with a sub-dir</title>
</head>
<body>
    <form method="POST" action="/form/relative_siteroot">
        <input type="text" name="test" value="" />
        <input type="submit" name="submit" value="Submit" />
    </form>
</body>
</html>