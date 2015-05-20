<html>
    <title>My Application</title>
<body>
<form method="POST" action="/form/complex" accept-charset="UTF-8" role="form" class="crud-form big-bottom">
    <fieldset>
        <legend>Create New Widget</legend>
        <div class="form-group">
            <label for="title">Widget Title</label>
            <input class="form-control" placeholder="Widget Title" name="title" type="text" id="title">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" placeholder="Description" name="description" cols="50" rows="10" id="description"></textarea>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input class="form-control" placeholder="Price" name="price" type="text" id="price">
        </div>
        <input class="btn btn-primary btn-block" type="submit" value="Create">
    </fieldset>
</form>
</body>
</html>