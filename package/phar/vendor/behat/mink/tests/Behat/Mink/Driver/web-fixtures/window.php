<html>
<body>

<script>
function newPopup(url, title) {
    popupWindow = window.open(
        url, title,'height=100,width=200,left=10,top=10,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes'
    );
}
</script>

<a href="javascript:newPopup('popup1.php', 'popup_1');">
    Popup #1
</a>

<a href="javascript:newPopup('popup2.php', 'popup_2');">
    Popup #2
</a>

<div id="text">
    Main window div text
</div>

</body>
</html>
