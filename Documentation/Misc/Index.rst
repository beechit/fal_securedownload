.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _misc:


Login redirect
==============

Instead of throwing a "Authentication required!" message you can redirect the user to a certain page so he can login.

Add following to your ext_localconf.php or typo3conf/AdditionalConfiguration.php

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fal_securedownload']['login_redirect_url'] = '/login/?redirect_url=###REQUEST_URI###';



No Access redirect
==================

Instead of throwing a "Access denied" message you can redirect the user to a certain page to inform the user about the access denied with optional some extra info.

Add following to your ext_localconf.php or typo3conf/AdditionalConfiguration.php

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fal_securedownload']['no_access_redirect_url'] = '/no-access/?redirect_url=###REQUEST_URI###';


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

The git repository of `ext:fal_securedonwload <https://github.com/beechit/fal_securedownload>`_ can be found on Github.

Pull request and suggestions for improvement are very welcome.

