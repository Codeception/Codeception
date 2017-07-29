<html>
<body>
<form action="/form/complex" method="POST">

    <input type="hidden" name="action" value="kill_all" />
    <fieldset disabled="disabled">
        <input type="text" id="disabled_fieldset" name="disabled_fieldset" value="disabled_fieldset" />
        <textarea id="disabled_fieldset_textarea" name="disabled_fieldset_textarea"></textarea>
        <select id="disabled_fieldset_select" name="disabled_fieldset_select">
            <option value="alpha">Alpha</option>
            <option value="bravo">Bravo</option>
        </select>
    </fieldset>
    <input  type="text" id="disabled_field" name="disabled_field" value="disabled_field" disabled="disabled" />
    <label for="description">Description</label>
    <textarea name="description" id="description" cols="30" rows="10"></textarea>

    <label for="name">Name</label>
    <input type="text" id="name" name="name" value="" />

    <label for="age">Select your age</label>
    <select name="age" id="age">
        <option value="child">below 13</option>
        <option value="teenage">13-21</option>
        <option value="adult">21-60</option>
        <option value="oldfag">60-100</option>
        <option value="dead">100-210</option>
    </select>

    <select name="no_salutation" id="salutation" disabled="disabled" id="age">
        <option value="mr" selected="selected">Mr</option>
        <option value="ms">Mrs</option>
    </select>


    <input type="password" name="password" >
    <label for="checkin">I Agree</label>
    <input type="checkbox" id="checkin" name="terms" value="agree" checked="checked" />
    <input type="submit" value="Submit" />

    <?php print_r($_SERVER); ?>
</form>
</body>
</html>