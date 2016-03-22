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

class redirect4 {
    function GET() {
        header('Location: /search?ln=test@gmail.com&sn=testnumber');
    }
}

class redirect_relative {
    function GET() {
        header('Location: info');
    }
}

class redirect2 {
    function GET() {
        include __DIR__.'/view/redirect2.php';
    }
}

class redirect3 {
    function GET() {
        header('Refresh:0;url=/info');
    }
}

class redirect_twice {
    function GET() {
        header('Location: /redirect3');
    }
}

class redirect_params {
    function GET() {
        include __DIR__.'/view/redirect_params.php';
    }
}

class redirect_interval {
    function GET() {
        include __DIR__.'/view/redirect_interval.php';
    }
}

class redirect_self {
    function GET() {
        include __DIR__.'/view/redirect_self.php';
    }
}

class redirect_header_interval {
    function GET() {
        include __DIR__.'/view/index.php';
        header('Refresh:1800;url=/info');
    }
}

class redirect_base_uri_has_path {
    function GET() {
        header('Refresh:0;url=/somepath/info');
    }
}

class redirect_base_uri_has_path_302 {
    function GET() {
        header('Location: /somepath/info', true, 302);
    }
}

class external_url {
    function GET() {
        include __DIR__ . '/view/external_url.php';
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

class cookiesHeader {
    public function GET()
    {
        header("Set-Cookie: a=b;Path=/;");
        header("Set-Cookie: c=d;Path=/;", false);
        include __DIR__.'/view/index.php';
    }
}

class iframe {
    public function GET()
    {
        include __DIR__.'/view/iframe.php';
    }
}

class facebookController {
    function GET($matches) {
        include __DIR__.'/view/facebook.php';
    }
}

class form {
    function GET($matches) {
        $url = strtolower($matches[1]);
        if (empty($matches[1])) {
            $url = 'index';
        }
        include __DIR__.'/view/form/'.$url.'.php';
    }

    function POST() {
        data::set('query', $_GET);
        data::set('form', $_POST);
        data::set('files', $_FILES);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            data::set('ajax','post');
        }

        $notice = 'Thank you!';
        include __DIR__.'/view/index.php';
    }
}

class articles {
    function DELETE() {
    }

    function PUT() {
    }
}

class search {
    function GET($matches) {
        $result = null;
        if (isset($_GET['searchQuery']) && $_GET['searchQuery'] == 'test') {
            $result = 'Success';
        }
        data::set('params', $_GET);
        include __DIR__.'/view/search.php';
    }
}

class httpAuth {
    function GET() {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="test"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Unauthorized';
            return;
        }
        if ($_SERVER['PHP_AUTH_PW'] == 'password') {
            echo "Welcome, " . $_SERVER['PHP_AUTH_USER'];
            return;
        }
        echo "Forbidden";
    }
}

class register {
    function GET() {
        include __DIR__.'/view/register.php';
    }

    function POST() {
        $this->GET();
    }
}

class contentType1 {
    function GET() {
        header('Content-Type:', true);
        include __DIR__.'/view/content_type.php';
    }
}

class contentType2 {
    function GET() {
        header('Content-Type:', true);
        include __DIR__.'/view/content_type2.php';
    }
}

class unsetCookie {
    function GET() {
        header('Set-Cookie: a=; Expires=Thu, 01 Jan 1970 00:00:01 GMT');
    }
}
