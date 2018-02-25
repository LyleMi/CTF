This challenge is aim to find a pair of username and password which satisfy ``$name == $password`` and ``sha1($name) === sha1($password)``.

Think of [Google Shatter sha1](https://shattered.io/), so download samples from site and start a request, you will get flag.

One thing to note is ``GET``'s parameter have length limit, so only need use part of that sample. Whole exploit is below.


```python
import requests
from urllib import quote

slen = 800

x = open("shattered-1.pdf", "rb").read()[:slen]
y = open("shattered-2.pdf", "rb").read()[:slen]

url = "http://54.202.82.13/?" + "name=" + quote(x) + "&password=" + quote(y)
print requests.get(url).content
```