# chinese-poetry-mysql
基于 [chinese-poetry](https://github.com/chinese-poetry/chinese-poetry) 数据整理的一份 mysql 格式数据

## 数据表说明
* poems - 宋词数据表 
* poems_author - 宋词对应作者数据表 
* poetry - 唐宋诗数据表 
* poetry_author - 唐宋诗作者数据表

## 其它说明
* poetry 表中 yunlv_rule, content 字段中的数据根据原始数据段落之间采用 "|" 分割成一个整行
* 各表中凡 author_id = 0 的数据行表明在对应数据的作者表中并没有找到对应的作者信息，故留空，而其 author 字段值来源于原始数据标注
