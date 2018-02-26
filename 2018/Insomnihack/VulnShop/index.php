 <?php if(isset($_GET['hl'])){ highlight_file(__FILE__); exit; }
    error_reporting(0); session_start(); 
    // Anti XSS filter
    $_REQUEST = array_map("strip_tags", $_REQUEST);
    // For later, when we will store infos about visitors.
    chdir("tmp");
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Work in progress...</title>
        <meta charset="utf-8" />
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <style>
            body {
                background-color: #aaa;
                color:#fff;
            }
            
            .page {
                width: 50%;
                margin: 0 auto;
                margin-top: 75px;
            }
            
            
            .menu ul li {
                display:inline-block;
                vertical-align:top;
                margin-right: 30px;
                
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="menu">
                <ul>
                    <li><a href="?page=default">Home</a></li>
                    <li><a href="?page=introduction">Introduction</a></li>
                    <li><a href="?page=privacy">Privacy</a></li>
                    <li><a href="?page=contactus">Contact</a></li>
                </ul>
            </div>
            
            <div class="content">
                <?php
                        switch($_GET['page']) {
                            case 'default':
                            default:
                                echo "<p>Welcome to our website about infosec. It's still under construction, but you can begin to browse some pages!</p>";
                                break;
                            case 'introduction':
                                echo "<p>Our website will introduce some new vulnerabilities. Let's check it out later!</p>";
                                break;
                            case 'privacy':
                                echo "<p>This website is unbreakable, so don't worry when contacting us about some new vulnerabilities!</p>";
                                break;
                            case 'contactus':
                                echo "<p>You can't contact us for the moment, but it will be available later.</p>";
                                $_SESSION['challenge'] = rand(100000,999999);
                                break;
                            case 'captcha':
                                if(isset($_SESSION['challenge'])) echo $_SESSION['challenge'];
                                // Will make an image later
                touch($_SESSION['challenge']);
                                break;
                            case 'captcha-verify':
                // verification functions take a file for later, when we'll provide more way of verification
                                function verifyFromString($file, $response) {
                                    if($_SESSION['challenge'] === $response) return true;
                                    else return false;
                                }
                                
                                // Captcha from math op
                                function verifyFromMath($file, $response) {
                                    if(eval("return ".$_SESSION['challenge']." ;") === $response) return true;
                                    else return false;
                                }
                                if(isset($_REQUEST['answer']) && isset($_REQUEST['method']) && function_exists($_REQUEST['method'])){
                                    $_REQUEST['method']("./".$_SESSION['challenge'], $_REQUEST['answer']);
                                }
                                break;

                        }
                ?>
            </div>
        </div>
        <p><a href="/?hl">View code source of the file, to be sure we're secure!</a></p>
        <p><a href="/phpinfo.php">Show our configurations</a></p>
    </body>
</html>
