<html>
<body>
<form action="/form/complex" method="POST">
    <label for="like">What do you like the most?</label>
    <select name="like[]" id="like" multiple="multiple">
        <option value="eat">Eat and Drink</option>
        <option value="play">Play Video Games</option>
        <option value="adult">Have Sex</option>
        <option value="drugs">Take some drugs</option>
        <option value="code">Fuck that shit, just CODE!</option>
    </select>
    <input type="submit" value="Submit" />
</form>
</body>
</html>