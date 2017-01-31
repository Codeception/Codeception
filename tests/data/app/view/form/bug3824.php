<html><body>

<form method="post">
    <select name="test">
        <option value="_none">- None -</option>
    </select>
</form>

<?php
    if (isset($_POST['test']) && $_POST['test'] !== '_none') {
        echo 'ERROR';
    }
?>

</body></html>
