<html>
    <title>TestEd Beta 2.0</title>
<body>

<h1>Welcome to test app!</h1>

<div class="notice"><?php if (isset($notice)) echo $notice; ?></div>

<p>
    <a href="/info" id="link">More info</a>
</p>


<div id="area1">
    <a href="/form/file"> Test Link </a>
</div>
<div id="area2">
    <a href="/form/hidden">Test</a>
</div>
<div id="area3">
    <a href="info">Document-Relative Link</a>
</div>

<div id="area4">
    Some text
    <span>with        formatting</span>
    <div class="someclass"> on separate
        <span>lines</span>
    </div>
</div>

<a href="/info" title="Link Title">Link Text</a>


A wise man said: "debug!"

Please don&#039;t provide us any personal information.

<?php print_r($_POST); ?>

</body>
</html>
