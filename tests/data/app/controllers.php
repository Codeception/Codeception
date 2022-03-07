<?php

class index
{
    public function GET($matches)
    {
        include __DIR__ . '/view/index.php';
    }

    public function POST($matches)
    {
        include __DIR__ . '/view/index.php';
    }
}

class info
{
    public function GET()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            data::set('ajax', ['GET']);
        }

        data::set('params', $_GET);
        include __DIR__ . '/view/info.php';
    }
}

class redirect
{
    public function GET()
    {
        header('Location: /info');
    }
}

class redirect4
{
    public function GET()
    {
        header('Location: /search?ln=test@gmail.com&sn=testnumber');
    }
}

class redirect_relative
{
    public function GET()
    {
        header('Location: info');
    }
}

class redirect2
{
    public function GET()
    {
        include __DIR__ . '/view/redirect2.php';
    }
}

class redirect3
{
    public function GET()
    {
        header('Refresh:0;url=/info');
    }
}

class redirect_twice
{
    public function GET()
    {
        header('Location: /redirect3');
    }
}

class redirect_params
{
    public function GET()
    {
        include __DIR__ . '/view/redirect_params.php';
    }
}

class redirect_interval
{
    public function GET()
    {
        include __DIR__ . '/view/redirect_interval.php';
    }
}

class redirect_meta_refresh
{
    public function GET()
    {
        include __DIR__ . '/view/redirect_meta_refresh.php';
    }
}

class redirect_header_interval
{
    public function GET()
    {
        include __DIR__ . '/view/index.php';
        header('Refresh:1800;url=/info');
    }
}

class redirect_base_uri_has_path
{
    public function GET()
    {
        header('Refresh:0;url=/somepath/info');
    }
}

class redirect_base_uri_has_path_302
{
    public function GET()
    {
        header('Location: /somepath/info', true, 302);
    }
}

class location_201
{
    public function GET()
    {
        header('Location: /info', true, 201);
    }
}

class external_url
{
    public function GET()
    {
        include __DIR__ . '/view/external_url.php';
    }
}


class login
{
    public function GET($matches)
    {
        include __DIR__ . '/view/login.php';
    }

    public function POST()
    {
        data::set('form', $_POST);
        include __DIR__ . '/view/login.php';
    }
}

class cookies
{
    public function GET($matches)
    {
        if (isset($_COOKIE['foo']) && $_COOKIE['foo'] === 'bar1') {
            if (isset($_COOKIE['baz']) && $_COOKIE['baz'] === 'bar2') {
                header('Location: /info');
            }
        } else {
            include __DIR__ . '/view/cookies.php';
        }
    }

    public function POST()
    {
        setcookie('f', 'b', ['expires' => time() + 60, 'path' => null, 'domain' => null, 'secure' => false, 'httponly' => true]);
        setcookie('foo', 'bar1', ['expires' => time() + 60, 'path' => null, 'domain' => 'sub.localhost', 'secure' => false, 'httponly' => true]);
        setcookie('baz', 'bar2', ['expires' => time() + 60, 'path' => null, 'domain' => 'sub.localhost', 'secure' => false, 'httponly' => true]);
        data::set('form', $_POST);
        include __DIR__ . '/view/cookies.php';
    }
}

class cookiesHeader
{
    public function GET()
    {
        header("Set-Cookie: a=b;Path=/;");
        header("Set-Cookie: c=d;Path=/;", false);
        include __DIR__ . '/view/index.php';
    }
}

class iframe
{
    public function GET()
    {
        include __DIR__ . '/view/iframe.php';
    }
}

class form
{
    public function GET($matches)
    {
        data::set('query', $_GET);
        $url = strtolower($matches[1]);
        if (empty($matches[1])) {
            $url = 'index';
        }

        include __DIR__ . '/view/form/' . $url . '.php';
    }

    public function POST()
    {
        data::set('query', $_GET);
        data::set('form', $_POST);
        data::set('files', $_FILES);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            data::set('ajax', 'post');
        }

        $notice = 'Thank you!';
        include __DIR__ . '/view/index.php';
    }
}

class articles
{
    public function DELETE()
    {
    }

    public function PUT()
    {
    }
}

class search
{
    public function GET($matches)
    {
        $result = null;
        if (isset($_GET['searchQuery']) && $_GET['searchQuery'] == 'test') {
            $result = 'Success';
        }

        data::set('params', $_GET);
        include __DIR__ . '/view/search.php';
    }
}

class httpAuth
{
    public function GET()
    {
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

class register
{
    public function GET()
    {
        include __DIR__ . '/view/register.php';
    }

    public function POST()
    {
        $this->GET();
    }
}

class contentType1
{
    public function GET()
    {
        header('Content-Type:', true);
        include __DIR__ . '/view/content_type.php';
    }
}

class contentType2
{
    public function GET()
    {
        header('Content-Type:', true);
        include __DIR__ . '/view/content_type2.php';
    }
}

class unsetCookie
{
    public function GET()
    {
        header('Set-Cookie: a=; Expires=Thu, 01 Jan 1970 00:00:01 GMT');
    }
}

class basehref
{
    public function GET()
    {
        include __DIR__ . '/view/basehref.php';
    }
}

class jserroronload
{
    public function GET()
    {
        include __DIR__ . '/view/jserroronload.php';
    }
}

class userAgent
{
    public function GET()
    {
        echo $_SERVER['HTTP_USER_AGENT'];
    }
}

class minimal
{
    public function GET()
    {
        include __DIR__ . '/view/minimal.php';
    }
}
