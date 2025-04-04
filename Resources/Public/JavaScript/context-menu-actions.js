import $ from 'jquery';

/**
 * JavaScript to handle the click action of the "FalSecuredownload" context menu item
 * Used in TYPO3 >= v12
 */
class ContextMenuActions {
  getReturnUrl() {
    return encodeURIComponent(top.list_frame.document.location.pathname + top.list_frame.document.location.search)
  }

  folderPermissions(table, uid, data) {
    let folderRecordUid = data['folderRecordUid'] || 0;

    if (folderRecordUid > 0) {
      top.TYPO3.Backend.ContentContainer.setUrl(
        top.TYPO3.settings.FormEngine.moduleUrl
        + '&edit[tx_falsecuredownload_folder][' + parseInt(folderRecordUid, 10) + ']=edit'
        + '&returnUrl=' + this.getReturnUrl()
      );
    } else {
      top.TYPO3.Backend.ContentContainer.setUrl(
        top.TYPO3.settings.FormEngine.moduleUrl
        + '&edit[tx_falsecuredownload_folder][0]=new'
        + '&defVals[tx_falsecuredownload_folder][storage]=' + data['storage']
        + '&defVals[tx_falsecuredownload_folder][folder]=' + data['folder']
        + '&defVals[tx_falsecuredownload_folder][folder_hash]=' + data['folderHash']
        + '&returnUrl=' + this.getReturnUrl()
      );
    }
  }
}

export default new ContextMenuActions();
