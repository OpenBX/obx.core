-- Посетители сайта
create table if not exists obx_visitors (
	ID int(11) not null auto_increment,
	SESSION_ID varchar(50) null,
	COOKIE_ID varchar(50) null,
	USER_ID int(11) null,
	NICKNAME varchar(50) null,
	FIRST_NAME varchar(50) null,
	LAST_NAME varchar(50) null,
	SECOND_NAME varchar(50) null,
	GENDER varchar(1) null,
	EMAIL varchar(255) null,
	PHONE varchar(255) null,
	SKYPE varchar(255) null,
	WWW varchar(255) null,
	ICQ varchar(255) null,
	FACEBOOK varchar(255) null,
	VK varchar(255) null,
	TWITTER varchar(255) null,
	ODNOKLASSNIKI varchar(255) null,
	JSON_USER_DATA text null,
	JSON_CONTACT text null,
	JSON_SOCIAL text null
);