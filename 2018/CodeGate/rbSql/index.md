This chanllage is open source, main file is [index.php](https://github.com/LyleMi/CTF/blob/master/2018/CodeGate/rbSql/index.php) and [dbconn.php](https://github.com/LyleMi/CTF/blob/master/2018/CodeGate/rbSql/dbconn.php) .

In this site, we can join / login / get our info. It store data in a specially constructed file. The first byte of file is ``\x01`` or ``\x02``, which means store a string or an array, The second byte store the length of string or array.

When we login as admin, we can get flag, but it seems we can only join as guest with following code.

```
$uid = $_POST['uid'];
$umail = $_POST['umail'];
$upw = $_POST['upw'];
if(($uid) && ($upw) && ($umail)){
  if(strlen($uid) < 3) error("id too short");
  if(strlen($uid) > 16) error("id too long");
  if(!ctype_alnum($uid)) error("id must be alnum!");
  if(strlen($umail) > 256) error("email too long");
  include "dbconn.php";
  $upw = md5($upw);
  $uip = $_SERVER['REMOTE_ADDR'];
  if(rbGetPath("member_".$uid)) error("id already existed");
  $ret = rbSql("create","member_".$uid,["id","mail","pw","ip","lvl"]);
  if(is_string($ret)) error($ret);
  $ret = rbSql("insert","member_".$uid,[$uid,$umail,$upw,$uip,"1"]);
  if(is_string($ret)) error($ret);
  exit("<script>location.href='./?page=login';</script>");
}
```

After source code audit, I notice that the following code use recursive in store, but not parse recursively.

```php
function rbParse($rawData){
    $parsed = array();
    $idx = 0;
    $pointer = 0;

    while(strlen($rawData)>$pointer){
        if($rawData[$pointer] == STR){
            // str
            $pointer++;
            $length = ord($rawData[$pointer]);
            $pointer++;
            $parsed[$idx] = substr($rawData,$pointer,$length);
            $pointer += $length;
        }
        elseif($rawData[$pointer] == ARR){
            // array
            $pointer++;
            $arrayCount = ord($rawData[$pointer]);
            $pointer++;
            for($i=0;$i<$arrayCount;$i++){
                if(substr($rawData,$pointer,1) == ARR){
                    $pointer++;
                    $arrayCount2 = ord($rawData[$pointer]);
                    $pointer++;
                    for($j=0;$j<$arrayCount2;$j++){
                        $pointer++;
                        $length = ord($rawData[$pointer]);
                        $pointer++;
                        $parsed[$idx][$i][$j] = substr($rawData,$pointer,$length);
                        $pointer += $length;
                    }
                }
                else{
                    $pointer++;
                    $length = ord(substr($rawData,$pointer,1));
                    $pointer++;
                    $parsed[$idx][$i] = substr($rawData,$pointer,$length);
                    $pointer += $length;
                }
            }
        }
        $idx++;
        if($idx > 2048) break;
    }
    return $parsed[0];
}

function rbPack($data){
    $rawData = "";
    if(is_string($data)){
        $rawData .= STR . chr(strlen($data)) . $data;
    }
    elseif(is_array($data)){
        $rawData .= ARR . chr(count($data));
        for($idx=0;$idx<count($data);$idx++) $rawData .= rbPack($data[$idx]);
    }
    return $rawData;
}

function rbGetPath($table){
    $schema = rbReadFile(SCHEMA);
    for($i=3;$i<count($schema);$i++){
        if(strtolower($schema[$i][0]) == strtolower($table)) return $schema[$i][1];
    }
}
```

So if we construct a complicated array, parser will miss parse it, then we can generate any value we want.

The final exploit is in [here](https://github.com/LyleMi/CTF/blob/master/2018/CodeGate/rbSql/exp.py)
