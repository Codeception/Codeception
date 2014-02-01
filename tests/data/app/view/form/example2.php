<html><body>
<form class="well loginform" method="post">
    <label>Username</label>
    <div class="input-prepend">
        <span class="add-on"><i class="icon-user"></i></span>
        <input type="text" name="username" placeholder="Your username …">
    </div>

    <div class="password">
        <label>
            Password
            <span class="togglepass show"><i class="icon-eye-open"></i> Show</span>
            <span class="togglepass hide"><i class="icon-eye-close"></i> Hide</span>
        </label>
        <div class="input-prepend">
            <span class="add-on"><i class="icon-key"></i></span>
            <input type="password" name="password" placeholder="Your password …">

        </div>
    </div>

    <br>
    <p style="margin-top: 0px;" class="login">
        <button type="submit" class="btn btn-primary" name="action" value="login"><i class="icon-signin"></i> Log on</button>
        <button type="button" class="btn btn-link forgot" style="float: right;"> I forgot my password…</button>
    </p>
    <p style="margin-top: 0px; display: none;" class="reset">
        <button type="submit" class="btn btn-primary" name="action" value="reset"><i class="icon-envelope"></i> Reset my password</button>
        <button type="button" class="btn btn-link remembered" style="float: right;"> Back to login</button>
    </p>

</form>
</body></html>