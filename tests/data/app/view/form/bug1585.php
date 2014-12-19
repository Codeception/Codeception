<html>
<body>
<h1>Hello world</h1>
<form action="/form/complex" method="post">
    <textarea name="captions[]" class="caption"></textarea>
    <input class="input-quantity row-1" name="items[1][quantity]" type="text" value="1" id="items[1][quantity]">
    <textarea name="items[1][]" class="caption"></textarea>
    <input type="text" name="users[]" />
    <input type="file" name="files[]" />
    <input type="submit" value="Submit"/>
</form>
</body>
</html>