<?php
class index {
    function GET($matches) {
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

class login {

    function GET($matches) {
        include __DIR__.'/view/login.php';
    }

    function POST() {
        data::set('form', $_POST);
        include __DIR__.'/view/login.php';
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
?>