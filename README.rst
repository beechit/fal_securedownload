FAL SecureDownLoad
==================

This extension (fal_securedownload) aims to be a general solution to secure your assets.

When you storage is marked as non-public all links to files from that storage are replaced (also for processed files).

The access to assets can be set on folder/file bases by setting access to fe_groups in the file module.

**How to use:**

1. Download and install fal_securedownload

2. Un-check the 'public' checkbox in your file storage

3. Add a .htaccess file with "Deny from all" (Apache < 2.3) or "Require all denied" (Apache >= 2.3) in your file storage root folder or move your storage outside of your webroot

**Requirements:**
    TYPO3 6.2, TYPO3 7 LTS, TYPO3 8

**Suggestions:**
    EXT:ke_search v1.8.4
    EXT:solrfal v2.0.1
