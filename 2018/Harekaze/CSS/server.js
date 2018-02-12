'use strict';

const express = require("express");
const bodyParser = require('body-parser');
const puppeteer = require('puppeteer');
const https = require('https');
const fs = require("fs");
const app = express();
const request = require("request");

app.use(bodyParser.urlencoded({ extended: true }))
app.use(bodyParser.json())

async function crawl(req, res) {
    if(!req.body['g-recaptcha-response']) {
        res.send("ReCAPTCHA error.");
        return;
    }
    var verificationUrl = `https://www.google.com/recaptcha/api/siteverify?secret=${process.env.RECAPTCHA_SECRET}&response=${req.body['g-recaptcha-response']}&remoteip=${req.connection.remoteAddress}`
    request(verificationUrl,async function(error,response,body) {
        const recaptcha = JSON.parse(body);
        if( recaptcha.success === true ) {
            res.send("Crawling");
            const browser = await puppeteer.launch({executablePath: '/usr/bin/chromium'});
            const page = await browser.newPage();
            await page.goto( "http://127.0.0.1:3002/flag.html?css=" + req.body.css, { waitUntil: "load" });
            await page.waitFor(20000);
            await browser.close();
        }else{
            res.send("ReCAPTCHA error.");
        }
    });
};

app.get('/server.js',function (req, res) { res.sendFile("/app/server.js") });
app.post('/crawl.html', crawl);
app.use('/', express.static('public'));
var server = app.listen(3001, function () {
    var host = server.address().address;
    var port = server.address().port;
    console.log('CSS-Injection http://%s:%s', host, port);
});

const app2 = express();
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
var server2 = app2.listen(3002,"localhost");