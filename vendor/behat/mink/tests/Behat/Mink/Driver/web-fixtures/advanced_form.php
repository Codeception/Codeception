<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
    <title>ADvanced Form</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
</head>
<body>
    <h1>ADvanced Form Page</h1>

    <form method="POST" enctype="multipart/form-data" action="/advanced_form_post.php">
        <input name="first_name" value="Firstname" type="text" />
        <input id="lastn" name="last_name" value="Lastname" type="text" />
        <label for="email">
            Your email:
            <input type="email" id="email" name="email" value="your@email.com" />
        </label>

        <select name="select_number">
            <option value="10">ten</option>
            <option selected="selected" value="20">twenty</option>
            <option value="30">thirty</option>
        </select>

        <label for="sex">
            <span><input type="radio" name="sex" value="m" /> m</span>
            <span><input type="radio" name="sex" value="w" checked="checked" /> w</span>
        </label>

        <input type="checkbox" name="mail_list" checked="checked" />
        <input type="checkbox" name="agreement" />

        <input type="file" name="about" />

        <input type="submit" name="submit" value="Register" />
        <input type="submit" name="submit" value="Login" />
    </form>
</body>
</html>
