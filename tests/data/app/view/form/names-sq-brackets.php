<html>
<head><title>Daison tests</title>
</head>
<body>
    <form>
        <input type="text" name="input_text">
        <input type="text" name="input[text][]">

        <textarea name="textarea_name"></textarea>
        <textarea name="textarea[name][]"></textarea>

        <input type="radio" name="input_radio_name" value="1">
        <input type="radio" name="input_radio_name" value="2">

        <input type="radio" name="input[radio][name][]" value="1">
        <input type="radio" name="input[radio][name][]" value="2">

        <input type="checkbox" name="input_checkbox_name" value="1">
        <input type="checkbox" name="input_checkbox_name" value="2">

        <input type="checkbox" name="input[checkbox][name][]" value="1">
        <input type="checkbox" name="input[checkbox][name][]" value="2">

        <select name="select_name">
            <option value="1">Select 1</option>
            <option value="2">Select 2</option>
            <option value="3">Select 3</option>
            <option value="4">Select 4</option>
            <option value="5">Select 5</option>
        </select>
        <select name="select[name][]">
            <option value="1">Select 1</option>
            <option value="2">Select 2</option>
            <option value="3">Select 3</option>
            <option value="4">Select 4</option>
            <option value="5">Select 5</option>
        </select>
    </form>
</body>
</html>
