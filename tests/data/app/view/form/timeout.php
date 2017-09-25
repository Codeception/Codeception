<html>
<body>

<p>Wait for button!</p>

<div id="el">

</div>

<div id="text"></div>

<script>
    function writeText() {
        document.getElementById('text').innerHTML = 'Hello!';
    }

  setTimeout(function () {
    document.getElementById('el').innerHTML = '<button id="btn" onclick="writeText()">Click</button>';
  }, 3000);
</script>
</body>
</html>
<?php
