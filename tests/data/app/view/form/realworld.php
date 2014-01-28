<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Profile</title>
    <meta charset="utf-8">
</head>
<body>
<div class="container-fluid main-body">
    <div class="row-fluid">
        <div class="main-wrapper">
<div class="signup-wrapper login-step3 signup center all-corners">

<div class="container">
    <div class="span5">
    <div class="bootstrap-widget">
        <div class="bootstrap-widget-header">
            <h3>Two Factor Authentication</h3>
        </div>
        <form class="form-vertical" id="totp-change" action="/form/realworld" method="post">        <div class="bootstrap-widget-content">
            <div class="inline-controls">
                <div class="control">
                    <label class="control-label">Two Factor Authentication is required</label>
                    <div id="wtogglebutton-User_totpRequired" class=""><input class="" checked="checked" type="checkbox" value="1" name="User[totpRequired]" id="User_totpRequired" /></div>                </div>
                <div id="totp-data">
                                        <div class="control">
                        <label for="totp-secret-value">Secret key</label>                        <input readonly="readonly" class="input-block-level" id="totp-secret-value" name="User[totpSecret]" type="text" value="FKADC5UGGWXC4H64" />                    </div>
                    <div class="inline-controls">
                        <input id="regenerateSecret" type="checkbox" value="1" name="User[regenerateSecret]" />                        <label for="regenerateSecret">Generate a new secret code</label>                    </div>
                    <div>
                        You must install <a href="https://support.google.com/accounts/answer/1066447">the application Google Authenticator and set it up</a>
                        by QR-code below or manually by the code above to log in Rebilly.
                    </div>
                    <div class="alert alert-info"><strong>Attention</strong> You won't be able to log in without your setting up</div>
                    <img src="https://chart.googleapis.com/chart?chs=200x200&amp;chld=M|0&amp;cht=qr&amp;chl=otpauth%3A%2F%2Ftotp%2Ftintudisableafterlogin%40rebilly.com%3Fsecret%3DFKADC5UGGWXC4H64" alt="" />                                    </div>
            </div>
            <button type="submit" class="btn btn-success btn-large"><i class="icon-ok"></i>Apply Changes</button>
        </div>
        </form>    </div>
    </div>
   <div class="span5">
    <div class="login-title">
        <h3><i class="log-icons log-guy-plus"></i>&nbsp;Reset Password</h3>
    </div>
      <div class="container-fluid signup-container">
        <form class="form-vertical" id="password-change" action="/form/realworld" method="post">
        <div class="control-group float-error inline-controls">
            <label for="FPasswordChangeForm_currentPassword">Current Password</label>            <input placeholder="Your current password" class="input-block-level" name="FPasswordChangeForm[currentPassword]" id="FPasswordChangeForm_currentPassword" type="password" />            <span class="help-block error" id="FPasswordChangeForm_currentPassword_em_" style="display: none"></span>        </div>

        <div class="control-group float-error inline-controls">
            <label for="FPasswordChangeForm_newPassword">New Password</label>            <input placeholder="New Password" class="input-block-level" name="FPasswordChangeForm[newPassword]" id="FPasswordChangeForm_newPassword" type="password" maxlength="50" />            <span class="help-block error" id="FPasswordChangeForm_newPassword_em_" style="display: none"></span>        </div>

        <div class="control-group float-error inline-controls">
            <label for="FPasswordChangeForm_passwordRepeat">Repeat New Password</label>            <input placeholder="Confirm New Password" class="input-block-level" name="FPasswordChangeForm[passwordRepeat]" id="FPasswordChangeForm_passwordRepeat" type="password" maxlength="50" />            <span class="help-block error" id="FPasswordChangeForm_passwordRepeat_em_" style="display: none"></span>        </div>

            <button type="submit" class="btn btn-success btn-large btn-block login-button">&nbsp;<i class="icon-plus-sign"></i>&nbsp;&nbsp;Reset Password</button>

            </form>        </div>
   </div>
</div>

</div>

        </div>
    </div>
</div>
<div class="clear"></div>

</body>
</html>