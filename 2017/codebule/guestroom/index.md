Download source code from site. And we can see this site is a simple PHP frame work with login and register function.

The function used to cat flag is below.

```php
$app->get('/flag', function () use ($app) {
    if (isset($_SESSION['is_logined']) === false || isset($_SESSION['is_guest']) === true) {
        $app->redirect('/#try+harder');
    }
    return $app->flag;
});
```

Seems we need to login not as guest, so let's look at register function.

```
$app->post('/register', function () use ($app) {
    $id = (isset($_POST['id']) === true && $_POST['id'] !== '') ? (string)$_POST['id'] : die('Missing id');
    $pw = (isset($_POST['pw']) === true && $_POST['pw'] !== '') ? (string)$_POST['pw'] : die('Missing pw');
    $code = (isset($_POST['code']) === true) ? (string)$_POST['code'] : '';

    if (strlen($id) > 32 || strlen($pw) > 32) {
        die('Invalid input');
    }

    $sth = $app->pdo->prepare('SELECT id FROM users WHERE id = :id');
    $sth->execute([':id' => $id]);
    if ($sth->fetch() !== false) {
        $app->redirect('/#duplicate+id');
    }

    $sth = $app->pdo->prepare('INSERT INTO users (id, pw) VALUES (:id, :pw)');
    $sth->execute([':id' => $id, ':pw' => $pw]);

    preg_match('/\A(ADMIN|USER|GUEST)--((?:###|\w)+)\z/i', $code, $matches);
    if (count($matches) === 3 && $app->code[$matches[1]] === $matches[2]) {
        $sth = $app->pdo->prepare('INSERT INTO acl (id, authorize) VALUES (:id, :authorize)');
        $sth->execute([':id' => $id, ':authorize' => $matches[1]]);
    } else {
        $sth = $app->pdo->prepare('INSERT INTO acl (id, authorize) VALUES (:id, "GUEST")');
        $sth->execute([':id' => $id]);
    }

    $app->redirect('/#registered');
});
```

The key code is ``$app->code[$matches[1]] === $matches[2]``, seems when we know code, we can register as admin or user, but the code's value is null.

```
$app->code = [
    'ADMIN' => null, // TODO: Set code
    'USER' => null, // TODO: Set code
    'GUEST' => '###GUEST###'
];
```

Seems we will never got right code to register.
And i notice another function:

```

$app->post('/login-2fa', function () use ($app) {
    if (isset($_SESSION['id']) === false) {
        $app->redirect('/#missing+login');
    }

    $code = (isset($_POST['code']) === true && $_POST['code'] !== '') ? (string)$_POST['code'] : die('Missing code');

    require_once('libs/PHPGangsta/GoogleAuthenticator.php');
    $ga = new PHPGangsta_GoogleAuthenticator();

    $sth = $app->pdo->prepare('SELECT secret FROM users WHERE id = :id');
    $sth->execute([':id' => $_SESSION['id']]);
    $secret = $sth->fetch()[0];
    if ($ga->verifyCode($secret, $code) === false) {
        $app->redirect('/login-2fa#invalid+auth');
    }

    $sth = $app->pdo->prepare('SELECT authorize FROM acl WHERE id = :id');
    $sth->execute([':id' => $_SESSION['id']]);
    if ($sth->fetch()[0] === 'GUEST') {
        $_SESSION['is_guest'] = true;
    }

    $_SESSION['is_logined'] = true;
    $app->redirect('/#logined');
});
```

key point is ``$sth->fetch()[0] === 'GUEST'``, maybe we do not need to register as admin or user, we just need not have a acl record. ``preg_match('/\A(ADMIN|USER|GUEST)--((?:###|\w)+)\z/i', $code, $matches);`` reminder me. Regexp is resource consuming, so we can construct a very long code, and php will timeout and die.

so try a code like ``ADMIN--###A###A....(repeat many times)``, and than login with login-2fa, will get flag.