var FileAttacher = function() { this.scriptName = 'Fileattacher' }
    
FileAttacher.prototype.init = function(id, allowExts, allowExtsMsgError, type) {
    this.id = id;
    this.allowExts = allowExts;
    this.allowExtsMsgError = allowExtsMsgError;
    this.type = type;
    this.widget = this.element('div', null, [
        this.uploadButton = this.element('a', { id: this.id + 'UploadButton', 'class': 'button', href: '#' }, [ 'Select File' ]),
        this.statusContainer = this.element('span', { 'class': 'status' }, [ ' ' ])
    ]);
    this.statusTextDots = '';
    this.addAttacher();
}

FileAttacher.prototype.addAttacher = function() {
    this.attacher = new AjaxUpload(this.uploadButton, {
        name: this.id + 'UserFile',
        action: '/wp-content/plugins/Beastx-WPProjects/ajax/upload-handler.php?id=' + this.id + '&type=' + this.type, 
        onSubmit : this.caller('onUploadInit'),
        onComplete: this.caller('onUploadDone')
    });
}

FileAttacher.prototype.onUploadInit = function(file, ext) {
    if (ext && (!this.allowExts || this.allowExts.test(ext))){
        this.setUploading(true);
        this.dispatchEvent('uploadstart', { file: file });
    } else {
        alert(this.allowExtsMsgError);
        return false;
    }
}

FileAttacher.prototype.setUploading = function(isUploading) {
    this.setEnabled(!isUploading);
    if (isUploading) {
        this.updateStatusText('Uploading');
        if (!this.interval) {
            this.interval = setInterval(this.caller('updateUpdateLoadingText'), 100);
        }
    } else {
        clearInterval(this.interval);
        this.interval = null;
        this.updateStatusText('');
    }
}

FileAttacher.prototype.updateUpdateLoadingText = function() {
    this.statusTextDots += '.';
    if (this.statusTextDots.length == 5){
        this.statusTextDots = '';
    }
    this.updateStatusText('Uploading' + this.statusTextDots);
}

FileAttacher.prototype.updateStatusText = function(text) {
    this.statusContainer.firstChild.nodeValue = text;
}

FileAttacher.prototype.onUploadDone = function(file, response) {
    this.setUploading(false);
    var fileUploaded = eval("fileUploaded=" + response);
    this.dispatchEvent('uploaddone', fileUploaded);
}

FileAttacher.prototype.setEnabled = function(enabled) {
    if (enabled) {
        this.attacher.enable();
    } else {
        this.attacher.disable();
    }
}