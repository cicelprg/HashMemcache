CREATE DATABASE memcached;

USE memcached;
/**
保存服务器信息，和对应的hash值
*/
CREATE TABLE t_severs(
sid INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
shost VARCHAR(30) NOT NULL,
sport  INT(11) NOT NULL,
shash INT(11) NOT NULL
);
/*
每个memcached服务器上的所有key，方便数据转存
*/
CREATE TABLE t_skey(
kid INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
sid INT(11) NOT  NULL,
skey INT (11) NOT NULL,
FOREIGN KEY (sid)  REFERENCES t_severs(sid)
);
