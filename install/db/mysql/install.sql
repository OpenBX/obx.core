-- Посетители сайта
CREATE TABLE IF NOT EXISTS obx_visitors (
  ID int(18) NOT NULL AUTO_INCREMENT,
  USER_ID int(18) DEFAULT NULL,
  COOKIE_ID varchar(32) DEFAULT NULL,
  PRIMARY KEY (ID),
  UNIQUE KEY COOKIE_ID (COOKIE_ID)
) DEFAULT CHARSET=utf8;

-- Хиты посетителей
CREATE TABLE IF NOT EXISTS obx_visitors_hits (
  ID int(20) NOT NULL AUTO_INCREMENT,
  VISITOR_ID int(18) NULL,
  DATE_HIT datetime NULL,
  SITE_ID varchar(5) NULL,
  URL text NULL,
  PRIMARY KEY (ID)
) DEFAULT CHARSET=utf8;

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