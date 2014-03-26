.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _misc:

Known issues
============

* My FileDumpEID hook isn't executed
	The DownloadLinkViewHelper used in the FileTree plugin ads a &download to the asset link.
	The hook that is used to check if you have permissions to access the asset will force a download when this parameter is set.
	Problem with this is that all other FileDumpEID hooks registered after fal_securedownload will not be executed anymore then.
* I got javascript errors after including the provided typoscript template
	This is proberly because you do not have jQuery availible on the FE. You can easily disable the provided javascript be adding this line to you typoscript templete

::

   page.jsFooterInline.303030 >


Todo
====

Complete this document

Further development
===================

The git repository of `ext:fal_securedonwload <https://github.com/beechit/fal_securedownload>`_ can be found on Github.

Pull request and suggestions for improvement are very welcome.

