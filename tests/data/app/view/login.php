<html>
<body>

<form id="user_form_login" enctype="application/x-www-form-urlencoded" class="global_form_box" action="/login" method="post">
	<div>
		<div>
			<div class="form-elements">
				<div id="email-wrapper" class="form-wrapper">
					<div id="email-label" class="form-label"><label for="email" class="required">Email Address</label></div>
					<div id="email-element" class="form-element">
						<input type="email" name="email" id="email" value="" tabindex="1" autofocus="autofocus" class="text">
					</div>
				</div>
				<div id="password-wrapper" class="form-wrapper">
					<div id="password-label" class="form-label"><label for="password" class="required">Password</label></div>
					<div id="password-element" class="form-element">
						<input type="password" name="password" id="password" value="" tabindex="2">
					</div>
				</div>
				<div class="form-wrapper" id="buttons-wrapper">
					<fieldset id="fieldset-buttons">
						<div id="submit-wrapper" class="form-wrapper">
							<div id="submit-label" class="form-label"> </div>
							<div id="submit-element" class="form-element">
								<button name="submit" id="submit" type="submit" tabindex="3">Sign In</button>
							</div>
						</div>
						<div id="remember-wrapper" class="form-wrapper">
							<div class="form-label" id="remember-label"> </div>
							<div id="remember-element" class="form-element">
								<input type="hidden" name="remember" value="">
								<input type="checkbox" name="remember" id="remember" value="1" tabindex="4">
								<label for="remember" class="optional">Remember Me</label>
							</div>
						</div>
					</fieldset>
				</div>
				<input type="hidden" name="return_url" value="" id="return_url">
			</div>
		</div>
	</div>
</form>

</body>
</html>
