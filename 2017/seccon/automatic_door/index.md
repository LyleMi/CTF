## Automatic_door Web 500

Desc

```
Get shell, and execute /flag_x

http://automatic_door.pwn.seccon.jp/0b503d0caf712352fc200bc5332c4f95/
```

This site can upload file, but filename can't have "ph", so we can upload a .htacess and enable other extension for php.

```
# .htacess rewrite
AddType application/x-httpd-php .html
php_flag engine 1
```

And then we can upload a shell, but here disable most of shell-like functions according to phpinfo page. After reading php's documentation, I find we can use proc_open to get flag.

The overall code is [here](https://github.com/LyleMi/CTF/blob/master/2017/seccon/automatic_door/index.py).