 create database if not exists db_filmohren;
 use db_filmohren;
 
 DROP TABLE IF EXISTS sessions;
 create table if not exists sessions (
	sess_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	user_id INT UNSIGNED NOT NULL,
	sess_value VARCHAR(32),
	sess_timestamp timestamp NOT NULL,
	sess_capture VARCHAR(32)
)

 create table if not exists users (
	user_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	user_login VARCHAR(32),
	user_pass VARCHAR(32) not NULL,
	user_info VARCHAR(128),
	user_contact VARCHAR(128),
	user_email VARCHAR(64) not null,
	user_rate TINYINT UNSIGNED default 0,
	user_regtime DATE default '0000-00-00'
)

DROP TABLE IF EXISTS materials;
create table if not exists materials (
	mat_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	user_id INT UNSIGNED,
	mat_caption VARCHAR(128),
	mat_genre VARCHAR(128),
	mat_country VARCHAR(64),
	mat_director VARCHAR(64),
	mat_actors text,
	mat_story text,
	mat_info text,
	mat_rate TINYINT UNSIGNED default 0
)

create table if not exists matlinks (
	link_id INT UNSIGNED,
	mat_id INT UNSIGNED,
	user_id INT UNSIGNED,
	link_type TINYINT UNSIGNED default 0
)