<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tests for seeInField</title>
</head>
<body>
    <form method="POST" action="/form/complex">
        <input type="checkbox" name="checkbox[]" value="not seen one">
        <input type="checkbox" name="checkbox[]" value="see test one" checked>
        <input type="checkbox" name="checkbox[]" value="not seen two">
        <input type="checkbox" name="checkbox[]" value="see test two" checked>
        <input type="checkbox" name="checkbox[]" value="not seen three">
        <input type="checkbox" name="checkbox[]" value="see test three" checked>
        
        <input type="radio" name="radio1" value="not seen one">
        <input type="radio" name="radio1" value="see test one" checked>
        <input type="radio" name="radio1" value="not seen two">
        <input type="radio" name="radio1" value="not seen three">
        
        <select name="select1">
            <option value="not seen one">Not selected</option>
            <option value="see test one" selected>Selected</option>
            <option value="not seen two">Not selected</option>
            <option value="not seen three">Not selected</option>
        </select>
        
        <select name="select2" multiple>
            <option value="not seen one">Not selected</option>
            <option value="see test one" selected>Selected</option>
            <option value="not seen two">Not selected</option>
            <option value="see test two" selected>Selected</option>
            <option value="not seen three">Not selected</option>
            <option value="see test three" selected>Selected</option>
        </select>
        
        <input type="submit" name="submit" value="Submit" />
    </form>
</body>
</html>