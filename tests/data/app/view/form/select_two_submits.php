<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Welcome!</title>
	</head>

	<body>

	<form method="POST" action="/form/select_two_submits">
		<button type="submit">Save</button>
		<label for="sandwich_select">What kind of sandwich would you like?</label>
		<select id="sandwich_select" name="sandwich_select">
			<option value="1">Just a sandwich</option>
			<option value="2">A better sandwich</option>
		</select>
		<button type="submit">Save</button>
	</form>

	</body>
</html>