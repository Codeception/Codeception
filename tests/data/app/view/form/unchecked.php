<html>
<body>
<form action="/form/unchecked">
    <input type="hidden" name="data" value="0" />
    <input type="checkbox" id="checkbox" name="data" checked="checked" />
    <input type="submit" value="submit" id="submit" />
</form>

<div id="notice">
    <?php
        if (isset($_GET['data'])) {
            echo $_GET['data'] ? 1 : 0;
        }
    ?>
</div>
</body>
</html>