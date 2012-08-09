<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
    <title>ARIA roles test</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
</head>
<body>
	This page tests selected ARIA roles<br />
	(see <a href="http://www.w3.org/TR/wai-aria/">http://www.w3.org/TR/wai-aria/</a>)

	<div id="hidden-element-toggle-button" role="button">Toggle</div>
	<div id="hidden-element" style="display:none;">This content's visibility is changed by clicking the Toggle Button.</div>

	<!-- This link is created programmatically -->
	<div id="link-element"></div>

    <script language="javascript" type="text/javascript" src="/js/jquery-1.6.2-min.js"></script>
	<script language="javascript" type="text/javascript">
		$(document).ready(function() {
			$('#hidden-element-toggle-button').click(function() {
				$('#hidden-element').toggle();
			});

			$('#link-element').attr('role', 'link').text('Go to Index').click(function() {
        window.location.href = '/index.php';
			});
		});
	</script>
</body>
</html>
