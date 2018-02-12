In this challenge, when we submit an url of CSS file, robot will generate a ``flag.html`` with CSS url which we submit and access it.

This code snippet showing the main process.

```js
app2.get('/flag.html', function (req, res) {
    console.log(req.connection.remoteAddress);
    req.query.css = req.query.css || "";
    if (req.query.css.startsWith("http://") || req.query.css.startsWith("https://")) {
        res.send(`<html>
            <link rel="stylesheet" href="${encodeURI(req.query.css)}" />
            <body>
                <div id="flag">
                        HarekazeCTF{${fs.readFileSync("flag.txt")}}
                </div>
            </body>
        </html>`);
    } else {
        res.send("Bad URI");
    }
});
```

Seems we can only control a CSS file, it's hard to read flag which is in innerHTML. But with this [trick](http://mksben.l0.cm/2015/10/css-based-attack-abusing-unicode-range.html), we can know the charset.

I have try some times, and get requests as below.

```
C F T b a d e o r z { - t l f s n k u _ m c } i H
C H F a d e k o r z - { m T b t f l i n s _ c } u
C F H a b T d e k z o r - { m t f i n u s _ c } l
```

This site also give an information of flag

```
The flag format is the two CSS3 properties connected by a underscore ().
Example: HarekazeCTF{background-image_font-size}
```

So we can filter CSS properties use previous result, and order is not exactly, but also give some hint.

Then we get the possible properties for the first word.

```
border-bottom-color
border-bottom-left-radius
border-bottom
border-left-color
border-left
```

The second list is 

```
animation-direction
animation-iteration-count
column-fill
counter-increment
direction
outline-color
unicode-bidi
```

At last, flag is ``HarekazeCTF{border-bottom-left-radius_animation-direction}``

Apart from this, a better method is use ``::first-line`` pseudo-element and ``animation`` to know the exactly order, you can know it with this [blog](http://blog.esora.xyz/HarekazeCTF2018-CSS).