CREATE TABLE tx_falsecuredownload_folder (
	# file info data
	storage int(11) DEFAULT '0' NOT NULL,
	folder text,
	folder_hash varchar(40) DEFAULT '' NOT NULL,

	# FE permissions
	fe_groups tinytext,

	KEY folder (storage, folder_hash)
);

CREATE TABLE tx_falsecuredownload_download (
	feuser int(11) DEFAULT '0' NOT NULL,
	file int(11) DEFAULT '0' NOT NULL,

	KEY user (feuser)
);

# Additional structure definitions

CREATE TABLE sys_file_metadata (
	# FE permissions
	fe_groups tinytext
);

CREATE TABLE fe_users (
	downloads int(11) DEFAULT '0' NOT NULL
);
