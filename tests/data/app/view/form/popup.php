<html>
<body>

<h1>Watch our popups</h1>

<script type="text/javascript">
    function showConfirm() {
        var res = confirm("Are you sure?");
        var el = document.getElementById('result');
        if (res) {
            el.innerHTML = 'Yes';
        } else {
            el.innerHTML = 'No';
        }
    }

    function showAlert()
    {
        alert("Really?");
    }
</script>

<div>
    <button onclick="showConfirm()">Confirm</button>
    <button onclick="showAlert()">Alert</button>


    <div id="result"></div>
</div>



</body>
</html>
