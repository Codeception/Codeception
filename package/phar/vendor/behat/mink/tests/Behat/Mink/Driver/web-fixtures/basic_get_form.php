<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
    <title>Basic Get Form</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
</head>
<body>
    <h1>Basic Get Form Page</h1>

    <div id="serach">
        <?php echo isset($_GET['q']) && $_GET['q'] ? $_GET['q'] : 'No search query' ?>
    </div>

    <form>
        <input name="q" value="" type="text" />

        <input type="submit" value="Find" />
    </form>
</body>
</html>
