<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title>Tests for base href tag dependent applications</title>
    <base href="/basehref/subfolder/"/>
</head>
<body>
    <h1>Sub Index For Base Href Tag Tests</h1>
    <div class="notice"><?php if (isset($notice)) echo $notice; ?></div>
    <h2>Note : This page is in subfolder</h2>
    <h2>Links</h2>
    <div id="area1">
        <a href=".">Link Relative Path to self as '.'</a>
    </div>
    <div id="area2">
        <a href="info">Link Relative Path</a>
    </div>
    <div id="area3">
        <a href="/info">Link Root Relative Path</a>
    </div>
    <div id="area4">
        <a href="./info">Link Relative Path with '.'</a>
    </div>
    <div id="area5">
        <a href="../info">Link Root Relative Path with '..'</a>
    </div>
    <div id="area6">
        <a href="./../info">Link Relative Path with both '.' and '..'</a>
    </div>
</body>
</html>