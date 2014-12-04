<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="en" />
    <title>My Web Application - Login</title>
</head>
<body>
<div class="container" id="page">
<div class="form">
<form id="login-form" action="/form/example1" method="post">
    <p class="note">Fields with <span class="required">*</span> are required.</p>
    <div class="row">
        <label for="LoginForm_username" class="required">Username <span class="required">*</span></label>   <input name="LoginForm[username]" id="LoginForm_username" type="text" />        <div class="errorMessage" id="LoginForm_username_em_" style="display:none"></div>   </div>

    <div class="row">
        <label for="LoginForm_password" class="required">Password <span class="required">*</span></label>       <input name="LoginForm[password]" id="LoginForm_password" type="password" />        <div class="errorMessage" id="LoginForm_password_em_" style="display:none"></div>       <p class="hint">
            Hint: You may login with <kbd>demo</kbd>/<kbd>demo</kbd> or <kbd>admin</kbd>/<kbd>admin</kbd>.
        </p>
    </div>

    <div class="row rememberMe">
        <input id="ytLoginForm_rememberMe" type="hidden" value="0" name="LoginForm[rememberMe]" /><input name="LoginForm[rememberMe]" id="LoginForm_rememberMe" value="1" type="checkbox" />        <label for="LoginForm_rememberMe">Remember me next time</label>     <div class="errorMessage" id="LoginForm_rememberMe_em_" style="display:none"></div> </div>


        <button type="submit" name="yt0" >Login</button>

</form></div><!-- form -->
</div><!-- content -->
</div><!-- page -->
</body>
</html>