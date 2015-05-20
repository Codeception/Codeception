<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test submitting a form with default radio/checkbox values</title>
</head>
<body>
    <form method="POST" action="/form/example16">
        <input type="text" name="test" value="" />
        <input type="checkbox" name="checkbox1" value="testing" checked="checked" />
        <input type="radio" name="radio1" value="to be sent" checked="checked" />
        <input type="radio" name="radio1" value="not to be sent" />
        <input type="submit" name="submit" value="Submit" />
    </form>
</body>
</html>