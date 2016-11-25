drop table if exists assets;
create table assets(
    id int not null auto_increment primary key comment 'PK',
    url varchar(200) not null default '' comment 'Url',
    content_type varchar(20) not null default '' comment 'Mime type',
    size int(5) not null default 0 comment 'Content Size',
    content text comment 'Content',
    create_time char(10) not null default '' comment 'Create time',
    last_visit_time char(10) not null default '' comment 'Last visit time'
)engine innodb character set utf8 collate utf8_general_ci comment 'asset table';