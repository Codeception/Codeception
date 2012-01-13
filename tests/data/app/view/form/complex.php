<html>
<body>
<form action="/form/complex" method="POST">

    <input type="hidden" name="action" value="kill_all" />

    <label for="description">Description</label>
    <textarea name="description" id="description" cols="30" rows="10"></textarea>

    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="" />

    <label for="age">Select your age</label>
    <select name="age" id="age">
        <option value="child">below 13</option>
        <option value="teenage">13-21</option>
        <option value="adult">21-60</option>
        <option value="oldfag">60-100</option>
        <option value="dead">100-210</option>
    </select>

    <label for="checkin">I Agree</label>
    <input type="checkbox" id="checkin" name="terms" value="agree" checked="checked" />
    <input type="submit" value="Submit" />
</form>
</body>
</html>