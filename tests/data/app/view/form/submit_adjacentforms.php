<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Testing submitForm with an adjacent form</title>
</head>
<body>
<form id="form-1" method="POST" action="/form/complex">
    <input type="text" name="first-field" value="Ptitsa" />
    <input id="submit1" type="submit" value="submit" />
</form>    
<form id="form-2" method="POST" action="/form/complex">
    <input type="text" name="second-field" value="Killgore Trout" />
    <input type="submit" id="submit2" type="submit" value="submit" />
</form>
</body>
</html>
