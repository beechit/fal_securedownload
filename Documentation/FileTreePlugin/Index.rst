.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _file-tree-plugin:

FE File Tree
============

The fal_securedownload extension comes with a filetree plugin you can use to display your folders/files as a tree in the frondend with force-download links.


Templates
---------

The default templates can be found like any other extbase/fluid based extension in:

.. code-block:: ts

   fal_securedownload/Resources/Private/Layouts
   fal_securedownload/Resources/Private/Partials
   fal_securedownload/Resources/Private/Templates

If you want to override these you can set these constants:

.. code-block:: ts

   plugin.tx_falsecuredownload {
     view {
       layoutRootPath = EXT:your_ext/path/to/your/layouts/folder
       partialRootPath = EXT:your_ext/path/to/your/partials/folder
       templateRootPath = EXT:your_ext/path/to/your/templates/folder
     }
   }


Collapsible FolderTree
----------------------

There is a ready to use jQuery snippet available in the typoscript template of ext:fal_securedownload.

jQuery needs to be included in the FE for this to work.

The state of the collapsed folders is saved in the session of the user. When user is logged-in it is saved in the persisted session.