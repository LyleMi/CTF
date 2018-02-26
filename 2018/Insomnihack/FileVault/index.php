 <?php

include('secret.php');
error_reporting(0);

if(isset($_GET['hl'])){ highlight_file(__FILE__); exit; }

$sandbox_dir = 'sandbox/'.sha1($_SERVER['REMOTE_ADDR']);

global $sandbox_dir;

class VaultFile {
    function upload($init_filename, $content) {
        global $sandbox_dir;
        $fileinfo = pathinfo($init_filename);
        $fileext = isset($fileinfo['extension']) ? ".".$fileinfo['extension'] : '.txt';
        file_put_contents($sandbox_dir.'/'.sha1($content).$fileext, $content);
        $this->fakename = $init_filename;        
        $this->realname = sha1($content).$fileext;
    }

    function open($fakename, $realname){
        global $sandbox_dir;
        $fp = fopen($sandbox_dir.'/'.$realname, 'r');
        $analysis = "The file named ".htmlspecialchars($fakename)." is located in folder $sandbox_dir/$realname. Here all the informations about this file : ".print_r(fstat($fp),true);
        return $analysis;
    }
}

function s_serialize($a, $secret) { $b = serialize($a); $b = str_replace("../","./",$b); return $b.hash_hmac('sha256', $b, $secret); };
function s_unserialize($a, $secret) { $hmac = substr($a, -64); if($hmac === hash_hmac('sha256', substr($a, 0, -64), $secret)) return unserialize(substr($a, 0, -64)); }
   
if(!is_dir($sandbox_dir)) mkdir($sandbox_dir);
if(!is_file($sandbox_dir.'/.htaccess')) file_put_contents($sandbox_dir.'/.htaccess', "php_flag engine off");
if(!isset($_GET['action'])) $_GET['action'] = 'home';
if(!isset($_COOKIE['files'])){
    setcookie('files', s_serialize([], $secret));
    $_COOKIE['files'] = s_serialize([], $secret);
}

switch($_GET['action']){
    case 'home':
    default:
        $content =  "<form method='post' action='index.php?action=upload' enctype='multipart/form-data'><input type='file' name='vault_file'><input type='submit'/></form>";
        $files = s_unserialize($_COOKIE['files'], $secret);
        if($files) {
            $content .= "<ul>";
            $i = 0;
            foreach($files as $file) {
                $content .= "<li><form method='POST' action='index.php?action=changename&i=".$i."'><input type='text' name='newname' value='".htmlspecialchars($file->fakename, ENT_QUOTES)."'><input type='submit' value='Click to edit name'></form><a href='index.php?action=open&i=".$i."' target='_blank'>Click to show file informations</a></li>";
                $i++;
            }
            $content .= "</ul>";
        }
        break;
    case 'upload':
        if($_SERVER['REQUEST_METHOD'] === "POST") {
            if(isset($_FILES['vault_file'])) {
                $vaultfile = new VaultFile;
                $vaultfile->upload($_FILES['vault_file']['name'], file_get_contents($_FILES['vault_file']['tmp_name']));
                $files = s_unserialize($_COOKIE['files'], $secret);
                $files[] = $vaultfile;
                setcookie('files', s_serialize($files, $secret));
                header("Location: index.php?action=home");
                exit;
            }
        }
        break;
    case 'changename':
        if($_SERVER['REQUEST_METHOD'] === "POST") {        
            $files = s_unserialize($_COOKIE['files'], $secret);
            if(isset($files[$_GET['i']]) && isset($_POST['newname'])){
                $files[$_GET['i']]->fakename = $_POST['newname'];
            }
            setcookie('files', s_serialize($files, $secret));            
        }
        header("Location: index.php?action=home");
        exit;
    case 'open':
        $files = s_unserialize($_COOKIE['files'], $secret);
        if(isset($files[$_GET['i']])){
            echo nl2br($files[$_GET['i']]->open($files[$_GET['i']]->fakename, $files[$_GET['i']]->realname));
        }
        exit;
    case 'reset':
        setcookie('files', s_serialize([], $secret));
        $_COOKIE['files'] = s_serialize([], $secret);
        array_map('unlink', glob("$sandbox_dir/*"));
        header("Location: index.php?action=home");
        exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            background-color:#aaa;
        }
        input {
            display:block;
            margin:10px 0;
        }

        ul {
            display:block;
            border:2px solid #aaa;
        }

        li {
            list-style-type:none;
        }

        input[type="text"], input[type="submit"], form {
            display:inline-block;
            margin:5px 5px;
        }
    </style>
</head>
<body>
<div class="content">
<h2>File manager</h2>
<p>Upload a file that will be stored in your file vault.</p>
<?=isset($content)?$content:"" ?>
<p><a href="index.php?action=reset">Reset my vault</a></p>
<p><a href="index.php?hl">Get my source code</a></p>
<!--<p><a href="phpinfo.php">Debug info</a></p>-->
</div>
</body>
</html>

