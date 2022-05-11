/**
 * Module: TYPO3/CMS/FalSecuredownload/ContextMenuActions
 *
 * JavaScript to handle the click action of the "FalSecuredownload" context menu item
 * @exports TYPO3/CMS/FalSecuredownload/ContextMenuActions
 */
define(function () {
    'use strict';

    /**
     * @exports TYPO3/CMS/FalSecuredownload/ContextMenuActions
     */
    var ContextMenuActions = {};

    /**
     * Open folder permissions edit form
     *
     * @param {string} table
     * @param {string} uid combined folder identifier
     */
    ContextMenuActions.folderPermissions = function (table, uid) {
        var folderRecordUid = this.data('folderRecordUid') || 0;

        if (folderRecordUid > 0) {
            top.TYPO3.Backend.ContentContainer.setUrl(
                top.TYPO3.settings.FormEngine.moduleUrl
                + '&edit[tx_falsecuredownload_folder][' + parseInt(folderRecordUid, 10) + ']=edit'
                + '&returnUrl=' + ContextMenuActions.getReturnUrl()
            );
        } else {
            top.TYPO3.Backend.ContentContainer.setUrl(
                top.TYPO3.settings.FormEngine.moduleUrl
                + '&edit[tx_falsecuredownload_folder][0]=new'
                + '&defVals[tx_falsecuredownload_folder][storage]=' + this.data('storage')
                + '&defVals[tx_falsecuredownload_folder][folder]=' + this.data('folder')
                + '&defVals[tx_falsecuredownload_folder][folder_hash]=' + this.data('folderHash')
                + '&returnUrl=' + ContextMenuActions.getReturnUrl()
            );
        }
    };

    ContextMenuActions.getReturnUrl = function () {
        return encodeURIComponent(top.list_frame.document.location.pathname + top.list_frame.document.location.search);
    };

    return ContextMenuActions;
});