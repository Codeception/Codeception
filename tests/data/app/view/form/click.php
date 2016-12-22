<html>
<body>
<div style="width: 100px;height: 100px;background-color:#99cb84" id="element"></div>
<div style="width: 100px;height: 100px;background-color:#aa0077" id="element2"></div>
<div id="result"></div>


<script type="text/javascript">
    var doc = document.getElementById('result');

    var click = function click(event) {

        doc.textContent =
            "click, " +
//            "offsetX: " + event.offsetX +
//            " - offsetY: " + event.offsetY;
        "offsetX: " + event.pageX +
        " - offsetY: " + event.pageY;

        event.preventDefault();
    }

    var context = function context(event) {
        document.removeEventListener('click', click);

        doc.textContent =
            "context, " +
            "offsetX: " + event.offsetX +
            " - offsetY: " + event.offsetY;

        event.preventDefault();
    }

    document.addEventListener("click", click);
    document.addEventListener("contextmenu", context);
</script>
</body>
</html>
