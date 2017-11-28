.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _misc:


Extension settings
==================

In the extension manager you find some options to define some of the behaviour of the extension:


Login redirect
--------------

*Option name: login_redirect_url*

Instead of throwing a "Authentication required!" message you can redirect the user to a certain page so he can login.

.. code-block::

    /login/?redirect_url=###REQUEST_URI###


No Access redirect
------------------

*Option name: no_access_redirect_url*

Instead of throwing a "Access denied" message you can redirect the user to a certain page to inform the user about the access denied with optional some extra info.

.. code-block::

    /no-access/?redirect_url=###REQUEST_URI###


Force download
--------------

*Option name: force_download*

Force download of all files from protected/non-public storages

Force download for some file extensions only
--------------------------------------------

*Option name: force_download_for_ext*

Force download for a given set of file extensions (comma separated list)

Enable resumable downloads
--------------------------

*Option name: resumable_download*

Enables resumable download support (default enabled for new installs).

This enables support for `HTTP/1.1 206 Partial Content`, so the file/download can be requested in multiple parts.

Count downloads per user and create statistics
----------------------------------------------

*Option name: track_downloads*

This feature is only available in TYPO3 CMS 7 and above.

All downloads are tracked. Each download will be logged and accounted towards the frontend user downloading it.
The download statistics per user can be inspected when editing the frontend user record.


EXT:ke_search support
=====================

To have proper support for ke_search you need at least version 1.8.4 of ke_search and 0.0.8 of fal_secure_download.


EXT:solrfal support
===================

To have correct urls to indexed files you need to add/adjust following ext:solr typoscript configuration.

.. code-block:: ts

	plugin.tx_solr.index.queue._FILES.default.filePublicUrl = public_url
	plugin.tx_solr.index.queue._FILES.default.url = public_url

*This feature is sponsored by: STIMME DER HOFFNUNG Adventist Media Center*


Signals and slots
=================

BeforeFileDump
--------------

This signal will be fired everytime a file is going to download or display. This signal will not be fired, if
access to requested file is restricted for current logged in frontend user. BeforeFileDump is useful for e.g. tracking access of downloaded files.

Example of how to register a slot for this signal (in your ext_localconf.php):

.. code-block:: php

	/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
	$signalSlotDispatcher->connect(
		'BeechIt\FalSecuredownload\Hooks\FileDumpHook',
		'BeforeFileDump',
		'Vendor\ExtensionName\Slot\BeforeFileDumpSlot',
		'logFileDump'
	);

AddCustomGroups
---------------

This signal is fired every time, the permissions are checked. It will add new groups to the list of authenticated groups,
which are not detected by the standard group mechanism. An example is, if you are using ip based authentication, where
no frontend user is logged in.

The slot must return an array which contains the array of the custom usergroups. This array will then be merged with the
original array of groups.

.. code-block:: php

     public function addCustomGroups($customGroups)
     {
         // add your group ids here
         return array($customGroups);
     }

EXT:fal_securedownload vs EXT:naw_securedl
==========================================

* fal_securedownload uses the FAL API to create secure links instead of checking/changing all links found in the HTML output.
* fal_securedownload supports remote storages.
* fal_securedownload requires proper use of the FAL API so extensions that do not use `$file->getPublicUrl()` to create links to your files or not `secured`. But that would also mean remote and non public storages are not supported.
* With fal_securedownload editors can set the permissions for files/folders by fe_group in the BE File list module.
* Links created by fal_securedownload are exchangeable with other users without the risk that people get access to files they are not allowed to access as a FE login is required to get access.

  * fal_securedownload 'secured' links don't have a expiration date and are only usable for users with a FE login.
  * Links do not change over time.


Known issues
============

* My FileDumpEID hook isn't executed
	The DownloadLinkViewHelper used in the FileTree plugin adds a &download to the asset link.
	The hook that is used to check if you have permissions to access the asset will force a download when this parameter is set.
	Problem with this is that all other FileDumpEID hooks registered after fal_securedownload will not be executed anymore then.
* I got javascript errors after including the provided typoscript template
	This is properly because you do not have jQuery available on the FE. You can easily disable the provided javascript be adding this line to you typoscript template


.. code-block:: ts

   page.jsFooterInline.303030 >

* Files in my "secure" folder aren't processed by ext:tika
	If the folder is outside of the document root you need to set `$GLOBALS['TYPO3_CONF_VARS']['BE']['lockRootPath']` else ext:tika will not process the files.


Todo
====

Complete this document


Further development
===================

The git repository of `ext:fal_securedownload <https://github.com/beechit/fal_securedownload>`_ can be found on Github.

Pull request and suggestions for improvement are very welcome.

