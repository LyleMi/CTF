This is a Python Flask Webapp. There are some pykemon on screen and we can click on them to catch it.

With looking at the [source code](https://github.com/LyleMi/CTF/blob/master/2017/PlaidCTF/Pykemon/pykemon.zip), we can see the probability to catch a pykemon is related with a property which names rarity. But flag's rarity is 0, so seems it's not possible to catch the flag.

However, there has solution which not intended. This site store flag in the session, and by default Flask store session in the cookie, so when we decode it, we will get flag.

Another way is use format string in python. In run.py, there are some vulnerability code.

```python
@app.route('/rename/', methods=['POST'])
def rename():
    name = request.form['name']
    new_name = request.form['new_name']
    if not name:
        return 'Error'

    p = check(name, 'caught')
    if not p:
        return "Error: trying to name a pykemon you haven't caught!"
    
    r = session.get('room')   
    s = session.get('caught')
    for pykemon in s['pykemon']:
        if pykemon['pid'] == name:
            pykemon['nickname'] = new_name
            session['caught'] = s
            print session['caught']
            return "Successfully renamed to:\n" + new_name.format(p)
    
    return "Error: something went wrong"
```

In here, we can control ``new_name``, if we pass ``{0.__class__.pykemon}`` to it, will print all the pykemon, and we can get flag.