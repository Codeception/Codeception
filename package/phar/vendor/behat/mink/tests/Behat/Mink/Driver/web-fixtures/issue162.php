<!DOCTYPE html>
<html lang="en">
<body>
    <?php
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            var_dump($_POST['some']);
        }
    ?>

    <form method="post" action="issue162.php">
        <label for=check1>
            Checkbox 1
            <input id=check1 type="checkbox" checked="checked" name="some[options][]" value="val1" />
        </label>
        <label for=check2>
            Checkbox 2
            <input id=check2 type="checkbox" name="some[options][]" value="val2" />
        </label>

        <input type="submit" value="submit" />
    </form>
</body>
</html>
