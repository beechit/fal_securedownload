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

    # Or a typolink
    t3://page?uid=5&redirect_url=###REQUEST_URI###


No Access redirect
------------------

*Option name: no_access_redirect_url*

Instead of throwing a "Access denied" message you can redirect the user to a certain page to inform the user about the access denied with optional some extra info.

.. code-block::

    /no-access/?redirect_url=###REQUEST_URI###

    # Or a typolink
    t3://page?uid=5&redirect_url=###REQUEST_URI###


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

    # Make sure the correct public URL is indexed
	plugin.tx_solr.index.queue._FILES.default.filePublicUrl = public_url
	plugin.tx_solr.index.queue._FILES.default.url = public_url

    # Make sure the fe_groups are considered
    plugin.tx_solr.index.queue._FILES.default.access = TEXT
    plugin.tx_solr.index.queue._FILES.default.access {
        value = r:0
        override {
            cObject = TEXT
            cObject {
                required = 1
                field = fe_groups
                wrap = r:|
            }
        }
    }

*This feature is sponsored by: STIMME DER HOFFNUNG Adventist Media Center*


EventListeners
==============

BeforeRedirects
---------------

This event will be fired everytime a file is going to download or display. This event will not be fired, if
access to requested file is restricted for current logged in frontend user. So you can modify some redirect params if needed.

Example of how to register a listener for this event in your `EXT:my_extension/Configuration/Services.yaml`:

.. code-block:: yaml

    services:
      Vendor\MyExtension\EventListener\BeforeRedirectsEventListener:
        tags:
          - name: event.listener
            identifier: 'myBeforeRedirectsEventListener'
            event: BeechIt\FalSecuredownload\Events\BeforeRedirectsEvent

An example listener `EXT:my_extension/Classes/EventListener/BeforeRedirectsEventListener.php` could look like this:

.. code-block:: php

	<?php
	namespace Vendor\MyExtension\EventListener;

    use \BeechIt\FalSecuredownload\Events\BeforeRedirectsEvent;

	class BeforeRedirectsEventListener
	{
        public function __invoke(BeforeRedirectsEvent $event): void
        {
            $event->setLoginRedirectUrl('XXX');
            $event->setNoAccessRedirectUrl('XXX');
        }

That way you can modify 'loginRedirectUrl', 'noAccessRedirectUrl', 'file', 'caller' if needed.


BeforeFileDump
--------------

This event will be fired everytime a file is going to download or display. This event will not be fired, if
access to requested file is restricted for current logged in frontend user. BeforeFileDump is useful for e.g. tracking access of downloaded files.

Example of how to register a listener for this event in your `EXT:my_extension/Configuration/Services.yaml`:

.. code-block:: yaml

    services:
      Vendor\MyExtension\EventListener\BeforeFileDumpEventListener:
        tags:
          - name: event.listener
            identifier: 'myBeforeFileDumpEventListener'
            event: BeechIt\FalSecuredownload\Events\BeforeFileDumpEvent

An example listener `EXT:my_extension/Classes/EventListener/BeforeFileDumpEventListener.php` could look like this:

.. code-block:: php

	<?php
	namespace Vendor\MyExtension\EventListener;

    use \BeechIt\FalSecuredownload\Events\BeforeFileDumpEvent;

	class BeforeFileDumpEventListener
	{
        public function __invoke(BeforeFileDumpEvent $event): void
        {
            $event->setFile('XXX');
        }

That way you can modify 'file', 'caller' if needed.

AddCustomGroups
---------------

This event is fired every time when the permissions are checked. It will add new groups to the list of authenticated groups,
which are not detected by the standard group mechanism. An example is, if you are using ip based authentication, where
no frontend user is logged in.

The slot must return an array which contains the array of the custom usergroups. This array will then be merged with the
original array of groups.

Example of how to register a listener for this event in your `EXT:my_extension/Configuration/Services.yaml`:

.. code-block:: yaml

    services:
      Vendor\MyExtension\EventListener\AddCustomGroupsEventListener:
        tags:
          - name: event.listener
            identifier: 'myAddCustomGroupsEventListener'
            event: BeechIt\FalSecuredownload\Events\AddCustomGroupsEvent

An example listener `EXT:my_extension/Classes/EventListener/AddCustomGroupsEventListener.php` could look like this:

.. code-block:: php

	<?php
	namespace Vendor\MyExtension\EventListener;

    use \BeechIt\FalSecuredownload\Events\AddCustomGroupsEvent;

	class AddCustomGroupsEventListener
	{
        public function __invoke(AddCustomGroupsEvent $event): void
        {
            $event->setCustomUserGroups($myCustomGroups);
        }

Known issues
============

* I got javascript errors after including the provided typoscript template
	This is properly because you do not have jQuery available on the FE. You can disable the provided javascript by adding this line to your typoscript template


.. code-block:: ts

   plugin.tx_falsecuredownload.settings.includeJavascript = 0

* Files in my "secure" folder aren't processed by ext:tika
	If the folder is outside of the document root you need to set `$GLOBALS['TYPO3_CONF_VARS']['BE']['lockRootPath']` else ext:tika will not process the files.


Todo
====

Complete this document


Further development
===================

The git repository of `ext:fal_securedownload <https://github.com/beechit/fal_securedownload>`_ can be found on Github.

Pull request and suggestions for improvement are very welcome.

