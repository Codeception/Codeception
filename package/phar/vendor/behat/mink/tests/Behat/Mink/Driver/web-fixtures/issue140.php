<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<html>
<body>
    <?php if (!empty($_POST)) {
        setcookie ("tc", $_POST['cookie_value']);
    }
    else if (isset($_GET["show_value"])) {
        echo $_COOKIE["tc"];
        die();
    } ?>
    <form method="post">
        <input name="cookie_value">
        <input type="submit" value="Set cookie">
    </form>
</body>
