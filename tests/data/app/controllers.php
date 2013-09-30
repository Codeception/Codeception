<?php
class index {
    function GET($matches) {
        include __DIR__.'/view/index.php';
    }

    function POST($matches) {
        include __DIR__.'/view/index.php';
    }
}

class info {
    function GET() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) data::set('ajax',array('GET'));
        data::set('params', $_GET);
        include __DIR__.'/view/info.php';
    }

}

class redirect {
    function GET() {
        header('Location: /info');
    }
}

class login {

    function GET($matches) {
        include __DIR__.'/view/login.php';
    }

    function POST() {
        data::set('form', $_POST);
        include __DIR__.'/view/login.php';
    }

}

class cookies {

    function GET($matches) {
        if (isset($_COOKIE['foo']) && $_COOKIE['foo'] === 'bar1') {
            if (isset($_COOKIE['baz']) && $_COOKIE['baz'] === 'bar2') {
                header('Location: /info');
            }
        } else {
            include __DIR__.'/view/cookies.php';
        }
    }

    function POST() {
        setcookie('f', 'b', time() + 60, null, null, false, true);
        setcookie('foo', 'bar1', time() + 60, null, 'sub.localhost', false, true);
        setcookie('baz', 'bar2', time() + 60,  null, 'sub.localhost', false, true);
        data::set('form', $_POST);
        include __DIR__.'/view/cookies.php';
    }

}

class facebookController {
    function GET($matches) {
        include __DIR__.'/view/facebook.php';
    }
}

class form {
    function GET($matches) {
        $object = strtolower($matches[1]);
        include __DIR__.'/view/form/'.$object.'.php';
    }

    function POST() {
        data::set('form', $_POST);
        data::set('files', $_FILES);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) data::set('ajax','post');

        $notice = 'Thank you!';
        include __DIR__.'/view/index.php';
    }
}
