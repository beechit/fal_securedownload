#
# Tabel structure for table 'tx_falsecuredownload_folder'
#
CREATE TABLE tx_falsecuredownload_folder (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,

	# file info data
	storage int(11) DEFAULT '0' NOT NULL,
	folder text,
	folder_hash varchar(40) DEFAULT '' NOT NULL,

	# FE permissions
	fe_groups tinytext,

	PRIMARY KEY (uid),
	KEY folder (storage,folder_hash)
);

#
# Table structure for table 'sys_file_metadata'
#
CREATE TABLE sys_file_metadata (
	# FE permissions
	fe_groups tinytext
);

CREATE TABLE tx_falsecuredownload_download (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,

	feuser int(11) DEFAULT '0' NOT NULL,
	file int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY user (feuser)
);

CREATE TABLE fe_users (
	downloads int(11) DEFAULT '0' NOT NULL
);
