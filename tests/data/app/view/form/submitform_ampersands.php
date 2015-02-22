<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Testing submitForm with field values and ampersand</title>
</head>
<body>
<form method="POST" action="/form/submitform_ampersands">
    <input type="text" name="test" value="this &amp; that" />
    <input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>