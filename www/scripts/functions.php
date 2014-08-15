<?php PHP_SAPI == 'cli' or die("Disallowed");

define('FCPATH', dirname(__DIR__));
define('BASEPATH', FCPATH . "/system/");

class HTTPException extends Exception { };

function db_connect($config_name = 'default') {
    require FCPATH . "/application/config/database.php";

    $config = $db[$config_name];

    mysql_connect($config['hostname'], $config['username'], $config['password']) or die("Can not connect to MySQL server\n");
    mysql_select_db($config['database']) or die("Can not select database\n");
    mysql_query('SET NAMES ' . $config['char_set']) or die("Set MySQL charset failed\n");
}

function dbo_connect($config_name = 'default') {
    require FCPATH . "/application/config/database.php";
    require __DIR__ . "/dbo.php";

    $config = $db[$config_name];

    return DBO::create($config['hostname'], $config['username'], $config['password'], $config['database'], $config['char_set']);
}

function download($url, $file = null) {
    $ch = curl_init();

    $opt = array(
        CURLOPT_URL => $url,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.27 Safari/537.36",
        CURLOPT_HTTPHEADER => array(
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            //"Accept-Encoding: gzip,deflate,sdch",
            "Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.6,en;q=0.4",
            "Cache-Control: max-age=0",
        ),
    );

    $fp = null;
    if ($file) {
        $fp = fopen($file, "w+");
        if (!$fp) {
            throw new Exception("Can not open file $file for write");
        }
        $opt[CURLOPT_FILE] = $fp;
    } else {
        $opt[CURLOPT_RETURNTRANSFER] = true;
    }

    curl_setopt_array($ch, $opt);

    $ret = curl_exec($ch);
    $err = curl_error($ch);

    curl_close($ch);

    if ($fp) {
        fclose($fp);
    }

    if ($err) {
        throw new HTTPException($err);
    }

    return $ret;
}

function yui_compress($js) {
    $descriptorspec = array(
       0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
       1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
       2 => array("pipe", "w") // stderr 
    );

    $cwd = FCPATH;
    $env = array();
    $jar = FCPATH . "/bin/yuicompressor-2.4.8.jar";

    $process = proc_open("/usr/bin/java -jar $jar --type js", $descriptorspec, $pipes, $cwd, $env);

    $ret = "";

    if (is_resource($process)) {
        // $pipes now looks like this:
        // 0 => writeable handle connected to child stdin
        // 1 => readable handle connected to child stdout
        // 2 => readable handle connected to child stderr

        fwrite($pipes[0], $js);
        fclose($pipes[0]);

        $ret = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $err = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        // It is important that you close any pipes before calling
        // proc_close in order to avoid a deadlock
        $return_value = proc_close($process);

        if ($return_value) {
            throw new Exception("YUI Compressor Error: " . $err);
        }
    }

    return $ret;
}

function redis_client()
{
    static $redis = null;

    if ( !$redis )
    {
        require(FCPATH . '/application/config/redis.php');
        $redis = new Redis();
        $redis->connect($config['redis']['sock']) or die( date('Y-m-d H:i:s') . " Connect to redis failed\n" );
        if ( isset($config['redis']['db']) )
            $redis->select($config['redis']['db']);
    }

    return $redis;
}


