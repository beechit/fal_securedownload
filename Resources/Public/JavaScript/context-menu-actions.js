/**
 * Module: @typo3/fal_securedownload/
 *
 * JavaScript to handle the click action of the "FalSecuredownload" context menu item
 * @exports TYPO3/CMS/FalSecuredownload/ContextMenuActions
 */


class ContextMenuActions {

    /**
     * Open folder permissions edit form
     *
     * @param {string} table
     * @param {string} uid combined folder identifier
     */
     static folderPermissions(table, uid) {
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

    static getReturnUrl() {
        return encodeURIComponent(top.list_frame.document.location.pathname + top.list_frame.document.location.search);
    };
};

export default ContextMenuActions;
