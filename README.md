FAL Secure Download
===

This TYPO3 extension (fal_securedownload) aims to be a general solution to secure your assets.

When your storage is marked as non-public all links to files from that storage are replaced (also for processed files).

The access to assets can be set on folder/file basis by setting access to fe_groups in the file module.

### How to use

1. Download and install fal_securedownload
2. Un-check the 'public' checkbox in your file storage
3. Add a .htaccess file with "Require all denied" in your file storage root folder or move your storage outside your webroot
4. Go to the file list and add access restrictions on file/folder

### Features

- Restrict FE access on folder level
- Restrict FE access on file level
- Let editor set permissions in file list
- Force download for all files (for protected file storages)
- Force download for specific file extensions (for protected file storages)
- Keep track of requested downloads (count downloads per user and file)

### Requirements
- TYPO3 11 LTS or TYPO3 12 LTS

### Suggestions
- EXT:ke_search v4.3.1
- EXT:solrfal v4.1.0
