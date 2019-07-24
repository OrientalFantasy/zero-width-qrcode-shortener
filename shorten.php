<?php
//10进制转62进制
function from10_to62($num) {
    $to = 62;
    $dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $ret = '';
    do {
        $ret = $dict[bcmod($num, $to) ] . $ret;
        $num = bcdiv($num, $to);
    } while ($num > 0);
    return $ret;
}
//10进制转58进制(不包含 0OlI 字符)
function from10_to58($num) {
    $to = 58;
    $dict = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    $ret = '';
    do {
        $ret = $dict[bcmod($num, $to) ] . $ret;
        $num = bcdiv($num, $to);
    } while ($num > 0);
    return $ret;
}
//10进制转56进制(不包含 01OolI 字符)
function from10_to56($num) {
    $to = 56;
    $dict = '23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    $ret = '';
    do {
        $ret = $dict[bcmod($num, $to) ] . $ret;
        $num = bcdiv($num, $to);
    } while ($num > 0);
    return $ret;
}
//10进制转零宽度空白不显字符
function from10_to_zerowidth($num) {
    $to = 2;
    $dict = array ('&#x200b;','&#x200c;');
    $ret = '';
    do {
        $ret = $dict[bcmod($num, $to) ] . $ret;
        $num = bcdiv($num, $to);
    } while ($num > 0);
    return $ret;
}
require_once "phpqrcode.php";
require_once 'config.php';
$url = $_POST['url'];
error_reporting(E_ALL);
if (!preg_match("/(http|https|itms-services):\/\/(.*?)$/i", $url)) {
    echo '<h3 style="color:#FF0000;">Invalid url should start with http:// or https:// </h3>';
} elseif ($url == 'http://' || $url == 'https://' || $url == 'itms-services://') {
    echo '<h3 style="color:#FF0000;">Invalid url</h3>';
} else {
    try {
        $db = new PDO("sqlite:$sqlitedb") or die("fail to connect db");
    }
    catch(Exception $e) {
        die($e);
    }
    try {
        $stmt = $db->prepare("INSERT INTO main (id, url, shortened, count, ip, create_time, user_agent, referer) VALUES (:id, :urlinput, :shorturl, :count, :ip, :createtime, :useragent, :referer);");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':urlinput', $urlinput);
        $stmt->bindParam(':shorturl', $shorturl);
        $stmt->bindParam(':count', $count);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':createtime', $createtime);
        $stmt->bindParam(':useragent', $useragent);
        $stmt->bindParam(':referer', $referer);
        $zeroid = from10_to_zerowidth($id);
        $urlinput = $url;
        $shorturl = from10_to58($id);
        $count = '0';
        $createtime = date("Y-m-d H:i:s");
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $referer = $_SERVER['HTTP_REFERER'];
        $stmt->execute();
    }
    catch(Exception $e) {
        die($e);
    }
    echo '<h3 style="color:#228B22;">Your Zero Width url is:  <a id="zerourl" href="' . "$domain/$zeroid" . '/" target="_blank">' . "$domain/$zeroid" . '/</a></h3>';
    echo '<h3 style="color:#228B22;">Your Short url is:  <a id="shorturl" href="' . "$domain/$shorturl" . '" target="_blank">' . "$domain/$shorturl" . '</a></h3>';
    ob_start();
    QRcode::png($domain . '/' . $shorturl, false, L, 10, 1, false);
    $image = ob_get_clean();
    header('Content-Type: text/html');
    echo '
	<h3><img src="data:image/png;base64,'.base64_encode($image).'"></h3>';
    echo '
	<h3 style="color:#228B22;">Your url is:  <a href="' . $url . '" target="_blank">' . $url . '</a></h3>';
    ob_start();
    QRcode::png($url, false, L, 10, 1, false);
    $image2 = ob_get_clean();
    header('Content-Type: text/html');
    echo '
	<h3><img src="data:image/png;base64,'.base64_encode($image2).'"></h3>';
    $db = null;
}
?>