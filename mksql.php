<?php
/**
 * Author: koma<komazhang@foxmail.com>
 * Date: 10/7/18
 */

set_time_limit(0);

$poetryBasePath  = "/data/opensource/chinese-poetry";
$poetryDataPath  = $poetryBasePath."/json";
$poemsDataPath   = $poetryBasePath."/ci";
$lunyuDataPath   = $poetryBasePath."/lunyu";
$shijingDataPath = $poetryBasePath."/shijing";

$host = "127.0.0.1";
$port = 3306;
$username = "root";
$password = "1234";
$poetryDb = "poetry_new";

$db = mysqli_connect($host, $username, $password, $poetryDb, $port);
if (mysqli_connect_error()) {
    die("Connect Error: ".mysqli_connect_errno());
}

mkSQL();

//============================= 执行函数区
function mkSQL() {
    //生成唐宋诗作者数据
    mkPoetAuthor("T");
    mkPoetAuthor("S");

    //生成唐宋诗数据
    mkPoetData("T");
    mkPoetData("S");

    //生成宋词作者数据
    mkPoemsAuthor();
    //生成宋词数据
    mkPoemsData();

    //生成论语数据
    mkLunyuData();

    //生成诗经数据
    mkShijingData();
}

function mkLunyuData() {
    global $lunyuDataPath;

    doExecute('delete from lunyu');

    $json  = file_get_contents($lunyuDataPath."/lunyu.json");
    $array = json_decode($json, true);
    printf("Json lun yu total num: %d\n", count($array));

    $sql = "insert into lunyu(chapter, content) values ";
    $value = '';
    foreach ($array as $val) {
        $v = '("'.$val['chapter'].'", "'.implode("|", $val['paragraphs']).'")';
        $value .= $value == '' ? $v : ','.$v;
    }

    doExecute($sql.$value);

    $res = doQuery('select count(*) as total from lunyu');
    $row = $res->fetch_assoc();
    printf("DB lun yu total num: %d\n", $row['total']);
}

function mkShijingData() {
    global $shijingDataPath;

    doExecute('delete from shijing');

    $json  = file_get_contents($shijingDataPath."/shijing.json");
    $array = json_decode($json, true);
    printf("Json shi jing total num: %d\n", count($array));

    $sql = "insert into shijing(title, chapter, section, content) values ";
    $value = '';
    foreach ($array as $val) {
        $v = '("'.$val['title'].'", "'.$val['chapter'].'", "'.$val['section'].'", "'.implode("|", $val['content']).'")';
        $value .= $value == '' ? $v : ','.$v;
    }

    doExecute($sql.$value);

    $res = doQuery('select count(*) as total from shijing');
    $row = $res->fetch_assoc();
    printf("DB shi jing total num: %d\n", $row['total']);
}

function mkPoemsAuthor() {
    global $poemsDataPath;

    doExecute('delete from poems_author');

    $poemsAuthorJson = file_get_contents($poemsDataPath."/author.song.json");
    $poemsAuthorArray = json_decode($poemsAuthorJson, true);
    printf("Json song ci author total num: %d\n", count($poemsAuthorArray));

    $sql = "insert into poems_author(name, intro_l, intro_s) values ";
    $value = '';
    foreach ($poemsAuthorArray as $val) {
        $v = '("'.$val['name'].'", "'.trimStr($val['description']).'", "'.trimStr($val['short_description']).'")';
        $value .= $value == '' ? $v : ','.$v;
    }

    doExecute($sql.$value);

    $res = doQuery('select count(*) as total from poems_author');
    $row = $res->fetch_assoc();
    printf("DB song ci author total num: %d\n", $row['total']);
}

function mkPoemsData() {
    global $poemsDataPath;

    doExecute('delete from poems');

    $res = doQuery('select * from poems_author');
    $authorData = array();
    while (($row = $res->fetch_assoc())) {
        $authorData[$row['name']] = $row['id'];
    }

    $total = 0;
    $num = 0;
    do {
        $fileName = $poemsDataPath.'/ci.song.'.$num.'.json';
        if (!file_exists($fileName)) break;

        $poemsDataJson = file_get_contents($fileName);
        $poemsDataArray = json_decode($poemsDataJson, true);
        $total += count($poemsDataArray);
        printf("start process song ci data file: %s, current total data num: %d\n", $fileName, $total);

        $sql = "insert into poems(author_id, title, content, author) values ";
        $value = '';
        foreach ($poemsDataArray as $val) {
            $authorId = isset($authorData[$val['author']]) ? $authorData[$val['author']] : 0;
            $v = '('.$authorId.', "'.$val['rhythmic'].'", "'.implode("|", $val['paragraphs']).'", "'.$val['author'].'")';
            $value .= $value == '' ? $v : ','.$v;
        }

        doExecute($sql.$value);

        $num += 1000;
    } while(true);
    printf("Json song ci data total num: %d\n", $total);

    $res = doQuery('select count(*) as total from poems');
    $row = $res->fetch_assoc();
    printf("DB song ci data total num: %d\n", $row['total']);
}

function mkPoetData($dynasty) {
    global $poetryDataPath;

    doExecute('delete from poetry where dynasty="'.$dynasty.'"');
    $poet = '';
    if ($dynasty == 'T') {
        $poet = 'tang';
    } else if ($dynasty == 'S') {
        $poet = 'song';
    }
    if ($poet == '') return;

    $res = doQuery('select * from poetry_author where dynasty="'.$dynasty.'"');
    $authorData = array();
    while (($row = $res->fetch_assoc())) {
        $authorData[$row['name']] = $row['id'];
    }

    $total = 0;
    $num = 0;
    do {
        $fileName = $poetryDataPath.'/poet.'.$poet.'.'.$num.'.json';
        if (!file_exists($fileName)) break;

        $poetDataJson = file_get_contents($fileName);
        $poetDataArray = json_decode($poetDataJson, true);
        $total += count($poetDataArray);
        printf("start process %s data file: %s, current total data num: %d\n", $poet, $fileName, $total);

        $sql = "insert into poetry(author_id, title, content, yunlv_rule, author, dynasty) values ";
        $value = '';
        foreach ($poetDataArray as $val) {
            $authorId = isset($authorData[$val['author']]) ? $authorData[$val['author']] : 0;
            $v = '('.$authorId.', "'.$val['title'].'", "'.implode("|", $val['paragraphs']).'", "'.implode("|", $val['strains']).'", "'.$val['author'].'", "'.$dynasty.'")';
            $value .= $value == '' ? $v : ','.$v;
        }

        doExecute($sql.$value);

        $num += 1000;
    } while(true);
    printf("Json %s data total num: %d\n", $poet, $total);

    $res = doQuery('select count(*) as total from poetry where dynasty="'.$dynasty.'"');
    $row = $res->fetch_assoc();
    printf("DB %s data total num: %d\n", $poet, $row['total']);
}

function mkPoetAuthor($dynasty) {
    global $poetryDataPath;

    doExecute('delete from poetry_author where dynasty="'.$dynasty.'"');
    $poet = '';
    if ($dynasty == 'T') {
        $poet = 'tang';
    } else if ($dynasty == 'S') {
        $poet = 'song';
    }
    if ($poet == '') return;

    $poetAuthorJson = file_get_contents($poetryDataPath."/authors.".$poet.".json");
    $poetAuthorArray = json_decode($poetAuthorJson, true);
    printf("Json %s author total num: %d\n", $poet, count($poetAuthorArray));

    $sql = "insert into poetry_author(name, intro, dynasty) values ";
    $value = '';
    foreach ($poetAuthorArray as $val) {
        $v = '("'.$val['name'].'", "'.$val['desc'].'", "'.$dynasty.'")';
        $value .= $value == '' ? $v : ','.$v;
    }

    doExecute($sql.$value);

    $res = doQuery('select count(*) as total from poetry_author where dynasty="'.$dynasty.'"');
    $row = $res->fetch_assoc();
    printf("DB %s author total num: %d\n", $poet, $row['total']);
}

//============================= 公用函数区
function doExecute($sql) {
    global $db;

    if (!$db->query($sql)) {
        die("Query Error: ".mysqli_error($db));
    }
}

function doQuery($sql) {
    global $db;

    $res = $db->query($sql);
    if (!$res) {
        die("Query Error: ".mysqli_error($db));
    }

    return $res;
}

function trimStr($str) {
    return str_replace(["\\", "\"", "\'"], ["", "", ""], $str);
}