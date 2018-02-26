This challenge give us [source code](https://github.com/LyleMi/CTF/blob/master/2018/Insomnihack/VulnShop/index.php) and phpinfo.

After looking at source code, seems ``eval`` function is interesting.

```php
function verifyFromMath($file, $response) {
    if(eval("return ".$_SESSION['challenge']." ;") === $response) return true;
    else return false;
}
```

However, we can not control ``$_SESSION['challenge']`` now, so we need find another useful things. Notice there has an interesting branch in ``captcha-verify``.

```php
if(isset($_REQUEST['answer']) && isset($_REQUEST['method']) && function_exists($_REQUEST['method'])){
    $_REQUEST['method']("./".$_SESSION['challenge'], $_REQUEST['answer']);
}
```

With ``$_REQUEST['method']("./".$_SESSION['challenge'], $_REQUEST['answer']);``, we can call some useful function, such as ``file_put_contents`` and ``copy``.

We can use ``file_put_contents`` to put arbitrary contents to SESSION file, and use copy to overwrite SESSION file. Use this trick, we can control ``$_SESSION['challenge']`` and ``eval`` anything to get flag.

Apart from this, I have notice that this challenge do not disable ``popen``, so we can write bash command in session file, and use ``popen`` to execute it.