In this challenge, users have five actions: home / upload / changename / open /reset.

There has an interesting serialize function:

```php
function s_serialize($a, $secret) { $b = serialize($a); $b = str_replace("../","./",$b); return $b.hash_hmac('sha256', $b, $secret); };
function s_unserialize($a, $secret) { $hmac = substr($a, -64); if($hmac === hash_hmac('sha256', substr($a, 0, -64), $secret)) return unserialize(substr($a, 0, -64)); }
```

We can notice that in this function, do ``str_replace`` after ``serialize``.
So if there are some ``../`` in ``$a``, unserialize will be confused.

For example, let ``$a=["../../../", "test"]``, result will be ``a:2:{i:0;s:9:"./././";i:1;s:4:"test";}``, this would trigger an error. But if we construct a special ``$a`` such as ``$a=[str_repeat("../", 13), 'A";i:1;s:4:"test']``, we will get an array ``['"./././././././././././././";i:1;s:16:"A"', 'test']``. With this trick, we can unserialize any object we want.

After this, notice that there are not magic function or other interesting functions, but in line 82 of [index.php](https://github.com/LyleMi/CTF/blob/master/2018/Insomnihack/FileVault/index.php), it calls ``open``.

```php
$files = s_unserialize($_COOKIE['files'], $secret);
echo nl2br($files[$_GET['i']]->open($files[$_GET['i']]->fakename, $files[$_GET['i']]->realname));
```

So if we find an object which hava a special ``open`` function, maybe we can do something. After looking at PHP document, we find ``ZipArchive`` can delete traget file with ``9`` as second parameter.

Now we can write exploition, just use ``ZipArchive`` delete ``.htaccess``, and you webshell will work.
