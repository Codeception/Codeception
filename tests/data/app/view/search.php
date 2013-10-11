<html>
<head>

</head>
<body>
    <div id="searchInputHeader">
        
        <?php if (isset($result)): echo $result; endif; ?>
        <form action="/search" method="get" target="_self" name="searchInputHeaderForm">
            <input 
                type="text"
                name="searchQuery" 
                value="Input" />
        </form>
        <div id="searchInputHeaderSubmit" onclick="searchInputHeaderForm.submit()"></div>
    </div>
</body>
</html>