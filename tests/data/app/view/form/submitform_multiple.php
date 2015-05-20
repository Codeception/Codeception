<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Testing submitForm with select multiple</title>
</head>
<body>
<form method="POST" action="/form/complex">
    <select name="select[]" multiple>
        <!-- a comment node here -->
        <optgroup label="first part" disabled>
            <option value="not seen one">Not selected</option>
            <option value="not seen two" selected>Selected</option>
        </optgroup>
        <option value="not seen three" selected disabled>Not selected</option>
        <option value="see test one" selected>Selected</option>
        <option value="not seen four">Not selected</option>
        <option value="see test two" selected>Selected</option>
    </select>
    <input type="submit" name="submit" value="Submit" />
</form>
</body>
</html>