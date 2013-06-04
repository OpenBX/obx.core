-- create table if not exists obx_moddeps (
-- 	ID int(18) not null auto_increment,
-- 	MODULE_NAME
-- 	MODULE_VERSION
-- 	DEPMOD_NAME
-- 	DEPMOD_MIN_VERSION
-- );

-- Посетители сайта
create table if not exists obx_visitors (
  ID int(18) not null auto_increment,
  USER_ID int(18) default null,
  COOKIE_ID varchar(32) default null,
  primary key (ID),
  unique obx_visitors_cookie(COOKIE_ID)
);

-- Хиты посетителей
-- эту сущность ещё надо продумать
create table if not exists obx_visitors_hits (
  ID int(18) not null auto_increment,
  VISITOR_ID int(18) NULL,
  DATE_HIT datetime NULL,
  SITE_ID varchar(5) NULL,
  URL text NULL,
  primary key (ID)
);

-- старое

-- SESSION_ID varchar(50) null,
-- NICKNAME varchar(50) null,
-- FIRST_NAME varchar(50) null,
-- LAST_NAME varchar(50) null,
-- SECOND_NAME varchar(50) null,
-- GENDER varchar(1) null,
-- EMAIL varchar(255) null,
-- PHONE varchar(255) null,
-- SKYPE varchar(255) null,
-- WWW varchar(255) null,
-- ICQ varchar(255) null,
-- FACEBOOK varchar(255) null,
-- VK varchar(255) null,
-- TWITTER varchar(255) null,
-- ODNOKLASSNIKI varchar(255) null,
-- JSON_USER_DATA text null,
-- JSON_CONTACT text null,
-- JSON_SOCIAL text null