# chinese-poetry-mysql
基于 [chinese-poetry](https://github.com/chinese-poetry/chinese-poetry) 数据整理的一份 mysql 格式数据

## 表结构说明
```sql
#####唐宋诗数据表
create table poetry (
  id int(11) not null primary key auto_increment,
  author_id int(11) default 0,
  title varchar(255) not null,
  content text not null,
  yunlv_rule text default null,
  author varchar(255) not null,
  dynasty char(1) not null
) engine = myisam, charset=utf8;

#####唐宋诗作者数据表
create table poetry_author (
  id int(11) not null primary key auto_increment,
  name varchar(255) not null,
  intro text default null,
  dynasty char(1) not null
) engine = myisam, charset=utf8;

#####宋词作者数据表
create table poems_author (
  id int(11) not null primary key auto_increment,
  name varchar(255) not null,
  intro_l text default null,
  intro_s text default null
) engine = myisam, charset=utf8;

#####宋词数据表
create table poems (
  id int(11) not null primary key auto_increment,
  author_id int(11) default 0,
  title varchar(255) not null,
  content text not null,
  author varchar(255) not null
) engine = myisam, charset=utf8;

#####论语数据表
create table lunyu (
  id int(11) not null primary key auto_increment,
  chapter varchar(255) not null,
  content text not null
) engine = myisam, charset=utf8;

#####诗经数据表
create table shijing (
  id int(11) not null primary key auto_increment,
  title varchar(255) not null,
  chapter varchar(255) not null,
  section varchar(255) not null,
  content text not null
) engine = myisam, charset=utf8;
```

## 数据表字段说明
* poetry, poems, lunyu, shijing 表中 content 字段以及 poetry 表中的 yunlv_rule 字段中的值根据原始段落数据通过 "|" 分割成一个整行存储
* poetry, poems 表中的 author_id = 0 的数据行表明在对应数据的作者表中并没有找到对应的作者信息，故留空，而其 author 字段值来源于原始数据标注

## 生成脚本说明
本项目中提供的 sql 压缩包是使用这个脚本生成的，因此对于需要即时更新的使用者，可以使用该脚本手动生成一份最新的 sql 数据．步骤如下:

* 克隆[原始仓库代码](https://github.com/chinese-poetry/chinese-poetry)到本地
* 在你的 mysql 中新建一个 database 并执行[表结构说明](#表结构说明)中的 sql 代码创建数据表
* 修改以下代码，设置好原始数据路径
```php
$poetryBasePath  = "/data/opensource/chinese-poetry";
$poetryDataPath  = $poetryBasePath."/json";
$poemsDataPath   = $poetryBasePath."/ci";
$lunyuDataPath   = $poetryBasePath."/lunyu";
$shijingDataPath = $poetryBasePath."/shijing";
```
* 修改以下代码，设置好数据库信息
```php
$host = "127.0.0.1";
$port = 3306;
$username = "root";
$password = "1234";
$poetryDb = "poetry_new";
```
* 在命令行中执行
```bash
php mksql.php
```

## 升级日志
### 2018-10-08
* 去掉 poems, poems_author 表中的 dynasty 字段
* 新增论语，诗经数据
* 增加生成脚本
* sql 压缩包数据更新
