create table `y_user` (
	`id` int unsigned auto_increment not null,
	`username` varchar(30) not null,
	`password` varchar(128) not null,
	`email` varchar(75) not null,
	`nickname` varchar(16) not null,
	`status` tinyint not null default '0',
	`create_time` int not null default '0',
	`update_time` int not null default '0',
	`login_time` int not null default '0',
	primary key `id`,
	unique key `username` (`username`),
	unique key `email` (`email`)
) engine=InnoDB default charset=utf8;