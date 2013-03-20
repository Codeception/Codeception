<html>
<body>
<form action="/form/complex" method="POST">
    <label for="checkin">I Agree</label>
    <input type="checkbox" id="checkin" name="terms" value="agree" onclick="document.getElementById('notice').innerHTML = 'ticked'" />
    <input type="submit" value="Submit" />
</form>
<div id="notice"></div>
</body>
</html>