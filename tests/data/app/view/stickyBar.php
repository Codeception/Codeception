<!DOCTYPE html>
<html>
    <head>
        <title>testClickWithStickyBar()</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body { margin:0; }
            header, footer { display:none; position:fixed; width:100%; height:100px; opacity:0.5; }
            header { top:0; background:greenyellow; }
            footer { bottom:0; background:red; }
            header a, footer a { display:block; height:100px; text-align:center; }
            main a { font-weight:bold; font-size:2em; }
        </style>
    </head>
    <body>
        <header id="header"><a href="/sticky-bar/link-in-header">Bad link</a></header>
        <main>
            <?php
            for ($i=0; $i<=50; $i++)
            {
                echo "<p>Scroll down ↓</p>\n";
            }
            ?>
            <a id="good-link" href="/sticky-bar/good-link">Click this link!</a>
            <?php
            for ($i=0; $i<=50; $i++)
            {
                echo "<p>Scroll up ↑</p>\n";
            }
            ?>
        </main>
        <footer id="footer"><a href="/sticky-bar/link-in-footer">Bad link</a></footer>
        <script>
            document.getElementById('header').style.display = 'block';
            document.getElementById('footer').style.display = 'block';
        </script>
    </body>
</html>
