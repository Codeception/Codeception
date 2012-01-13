<html>
<body>

<h1>Information</h1>

<p>Lots of valuable data here

    <a href="/" id="back"><img src="blank.gif" alt="Back" /></a>
</p>

<div class="notice"><?php if (isset($notice)) echo $notice; ?></div>


Is that interesting?
<form action="/">
<input type="checkbox" name="interesting" value="1" checked="checked" />
    <input type="submit" />
</form>

</body>
</html>
