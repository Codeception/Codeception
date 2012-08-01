<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
    <title>Multiselect Test</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
</head>
<body>
    <h1>Multiselect Test</h1>

    <form method="POST" action="/advanced_form_post.php">
        <select name="select_number">
            <option value="10">ten</option>
            <option selected="selected" value="20">twenty</option>
            <option value="30">thirty</option>
        </select>

        <select name="select_multiple_numbers[]" multiple="multiple">
            <option value="1">one</option>
            <option value="2">two</option>
            <option value="3">three</option>
        </select>

        <input type="submit" name="submit" value="Register" />
    </form>
</body>
</html>
