Get source code from ``index.phps``, main code is

```php
if (eregi("[^0-9,.]", $_COOKIE[user_lv])) {
    $_COOKIE[user_lv] = 1;
}

if ($_COOKIE[user_lv] >= 6) {
    $_COOKIE[user_lv] = 1;
}

if ($_COOKIE[user_lv] > 5) {
    @solve();
}
```

So set ``user_lv=5.5``, get points.