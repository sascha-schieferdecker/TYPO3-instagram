CREATE TABLE tx_instagram_feed (
    uid int(11) NOT NULL auto_increment,
    import_date int(11) unsigned DEFAULT '0' NOT NULL,
    username varchar(255) DEFAULT '' NOT NULL,
    sys_language_uid int(11) DEFAULT '0' NOT NULL,
    PRIMARY KEY (uid)
);

CREATE TABLE tx_instagram_post (
   uid bigint(30) NOT NULL auto_increment,
   feed_uid int(11) NOT NULL,
   tstamp int(11) unsigned DEFAULT '0' NOT NULL,
   content mediumtext NOT NULL,
   PRIMARY KEY (uid)
);
