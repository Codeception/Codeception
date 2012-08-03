<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
    <title>JS elements test</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>

    <style>
        #draggable {
            width: 100px; height: 100px; padding: 0.5em; float: left; margin: 10px 10px 10px 0;
            background:#ccc;
            opacity:0.5;
        }
        #droppable {
            width: 150px; height: 150px; padding: 0.5em; float: left; margin: 10px;
            background:#eee;
        }
        #waitable {
            width: 150px; height: 150px; padding: 0.5em; float: left; margin: 10px;
            background:#eee;
        }
    </style>
</head>
<body>
    <div class="elements">
        <div id="clicker">not clicked</div>
        <div id="invisible" style="display: none">invisible man</div>
        <input class="input first" type="text" value="" />
        <input class="input second" type="text" value="" />
        <input class="input third" type="text" value="" />
        <div class="text-event"></div>
    </div>

    <div id="draggable" class="ui-widget-content"></div>

    <div id="droppable" class="ui-widget-header">
        <p>Drop here</p>
    </div>

    <div id="waitable"></div>

    <script src="js/jquery-1.6.2-min.js"></script>
    <script src="js/jquery-ui-1.8.14.custom.min.js"></script>
	<script>
		$(document).ready(function() {
            $('#clicker').click(function() {
                $(this).text('single clicked');
            });

            $('#clicker').dblclick(function() {
                $(this).text('double clicked');
            });

            $('#clicker').bind('contextmenu', function() {
                $(this).text('right clicked');
            });

            $('#clicker').focus(function() {
                $(this).text('focused');
            });

            $('#clicker').blur(function() {
                $(this).text('blured');
            });

            $('#clicker').mouseover(function() {
                $(this).text('mouse overed');
            });

            $('.elements input.input.first').keydown(function(ev) {
                $('.text-event').text('key downed:' + ev.altKey * 1);
            });

            $('.elements input.input.second').keypress(function(ev) {
                $('.text-event').text('key pressed:' + ev.which + ' / ' + ev.altKey * 1);
            });

            $('.elements input.input.third').keyup(function(ev) {
                $('.text-event').text('key upped:' + ev.which + ' / ' + ev.altKey * 1);
            });

            $( "#draggable" ).draggable();
            $( "#droppable" ).droppable({
                drop: function( event, ui ) {
                    $( this ).find( "p" ).html( "Dropped!" );
                }
            });

            var t1, t2;

            $('#waitable').click(function() {
                var el = $(this);

                el.html('');
                clearTimeout(t1);
                clearTimeout(t2);

                t1 = setTimeout(function() {
                    el.html('<div>arrived</div>');
                }, 1000);

                t2 = setTimeout(function() {
                    el.html('<div>timeout</div>');
                }, 2000);
            });
		});
	</script>
</body>
</html>
