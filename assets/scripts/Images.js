
var BeastxScreenshotsManager = function() { this.scriptName = 'BeastxScreenshotManager' };
    
BeastxScreenshotsManager.prototype.init = function(maxScreenshots) {
    this.maxScreenshots = maxScreenshots ? maxScreenshots : 5;
    this.container = $('screenshotsContainer');
    this.fileAttacherContainer = $('screenshotsFileAttacherContainer');
    this.idsInput = $('screenshots_input');
    this.attachs = [];
    this.appendChild(
        this.fileAttacherContainer,
        this.fileAttacher = New(FileAttacher, [ 'screenshots', /^(jpg|png|jpeg|gif)$/i, BeastxWPProjectTexts.onlyImageFiles, 'image' ], { onuploadstart: this.caller('onUploadStart'), onuploaddone: this.caller('onUploadDone') })
    );
}

BeastxScreenshotsManager.prototype.onUploadStart = function() {
    this.fileAttacher.setEnabled(false);
}

BeastxScreenshotsManager.prototype.checkMaxLength = function() {
    if (this.maxScreenshots && (this.attachs.length >= this.maxScreenshots)) {
        this.fileAttacher.setEnabled(false);
        this.fileAttacher.updateStatusText(BeastxWPProjectTexts.fileMaxLength);
    } else {
        this.fileAttacher.setEnabled(true);
        this.fileAttacher.updateStatusText('');
    }
}

BeastxScreenshotsManager.prototype.onUploadDone = function(params) {
    this.fileAttacher.setEnabled(true);
    if (params.error) {
        alert(BeastxWPProjectTexts[params.errorMsg]);
    } else {
        this.addScreenshotItem(params.id, '', params.url);
    }
}

BeastxScreenshotsManager.prototype.addScreenshotItem = function(id, title, url) {
    var item = New(BeastxScreenshotItem, [id, title, url], { onremoveclick: this.caller('onRemoveItemClick'), onsetitle: this.caller('onSetTitle') });
    this.attachs.push(item);
    this.appendChild(this.container, item);
    this.updateIdsInputValue();
    this.checkMaxLength();
}

BeastxScreenshotsManager.prototype.onSetTitle = function(item) {
    this.updateIdsInputValue();
}

BeastxScreenshotsManager.prototype.onRemoveItemClick = function(item) {
    var newList = [];
    for (var i = 0; i < this.attachs.length; ++i) {
        if (this.attachs[i] != item) {
            newList.push(this.attachs[i]);
        }
    }
    this.removeChild(this.container, item);
    this.attachs = newList;
    this.updateIdsInputValue();
    this.checkMaxLength();
}

BeastxScreenshotsManager.prototype.triggerSave = function() {
    this.updateIdsInputValue();
}

BeastxScreenshotsManager.prototype.updateIdsInputValue = function() {
    this.idsInput.value = this.getValue();
}

BeastxScreenshotsManager.prototype.getValue = function() {
    var value = [];
    for (var i = 0; i < this.attachs.length; ++i) {
        value.push(this.attachs[i].getValue());
    }
    return VAR.serialize(value);
}









var BeastxScreenshotItem = function() {};
    
BeastxScreenshotItem.prototype.init = function(id, title, url) {
    this.randId = Math.random() * 100000000000000000;
    this.id = id;
    this.url = url;
    this.title = title;
    this.updateUI();
}

BeastxScreenshotItem.prototype.updateUI = function() {
    this.widget = this.element('div', { 'class': 'ScreenshotItem' }, [
        this.imgContainer = this.element('div', null, [
            this.imgElement = this.element('img', { width: '100', height: '100', src: this.url }, [])
        ]),
        this.element('a', { href: '#', onclick: this.caller('onRemoveClick') }, [ BeastxWPProjectTexts.remove ]),
        this.element('br'),
        this.setTitleLink = this.element('a', { href: '#', onclick: this.caller('onSetTitleClick') }, [ this.title != '' ? BeastxWPProjectTexts.editTitle : BeastxWPProjectTexts.setTitle ]),
        this.element('br'),
        this.editImageLink = this.element('a', { href: '#', onclick: this.caller('onEditClick') }, [ BeastxWPProjectTexts.editImage ])
    ]);
        
}

BeastxScreenshotItem.prototype.onEditClick = function(event) {
    DOM.cancelEvent(event);
    this.popup = this.element('div', { 'class': 'BeastxPopup' }, [
        this.element('div', { 'class': 'BeastxPopupTitle' }, [ BeastxWPProjectTexts.setTitleLong ]),
        this.element('div', { 'class': 'BeastxPopupContent' }, [
            this.editImageIframe = this.element('iframe', { id: 'editImageIframe_' + this.id, onload: this.caller('onEditImageIframeLoad'), width: '700px', height: '600px', src: '/wp-admin/media.php?attachment_id=' + this.id + '&action=edit', 'class': 'BeastxWPProjectEditorSimple' }),
            this.element('div', null, [
                this.element('button', { onclick: this.caller('closeEditImageIframe') }, [ BeastxWPProjectTexts.done ])
            ])
        ])
    ]);
    
    document.body.appendChild(this.popup);
    jQuery(this.popup).center();
}

BeastxScreenshotItem.prototype.onEditImageIframeLoad = function(event) {
    var me = this;
    var doc = this.editImageIframe.contentDocument ? this.editImageIframe.contentDocument : window.frames[this.editImageIframe.id].document;
    var form = doc.getElementById('media-single-form');
    doc.body.innerHTML = '';
    doc.body.appendChild(form);
    var button = doc.getElementById('imgedit-open-btn-' + this.id);
    button.click();
    setTimeout(function() {
        var submitButton = jQuery('.imgedit-submit-btn', doc)[0];
        DOM.addListener(submitButton, 'click', function() {
            setTimeout(function() {
                alert(1)
                form.submit();
                setTimeout(function() {
                    alert(2)
                    me.closeEditImageIframe();
                    setTimeout(function() {
                        me.reloadImage();
                    }, 1000);
                }, 1000);
            }, 1000);
        })
    }, 1000);
}

BeastxScreenshotItem.prototype.reloadImage = function() {
    this.imgElement = this.element('img', { width: '100', height: '100', src: this.url });
    this.replaceContent(this.imgContainer, this.imgElement);
    alert(this.url);
}

BeastxScreenshotItem.prototype.closeEditImageIframe = function() {
    document.body.removeChild(this.popup);
}

BeastxScreenshotItem.prototype.onSetTitleClick = function(event) {
    DOM.cancelEvent(event);
    this.popup = this.element('div', { 'class': 'BeastxPopup' }, [
        this.element('div', { 'class': 'BeastxPopupTitle' }, [ BeastxWPProjectTexts.setTitleLong ]),
        this.element('div', { 'class': 'BeastxPopupContent' }, [
            this.titlePopupArea = this.element('textarea', { id: 'screenshotPopup_' + this.randId, 'class': 'BeastxWPProjectEditorSimple' }, [ this.title ]),
            this.element('button', { onclick: this.caller('onCloseSetTitlePopupClick') }, [ BeastxWPProjectTexts.done ])
        ])
    ]);
    
    document.body.appendChild(this.popup);
    jQuery(this.popup).center();
    tinyMCE.execCommand('mceAddControl', false, 'screenshotPopup_' + this.randId);
}

BeastxScreenshotItem.prototype.onCloseSetTitlePopupClick = function() {
    this.title = tinyMCE.editors['screenshotPopup_' + this.randId].getContent();
    document.body.removeChild(this.popup);
    tinyMCE.execCommand('mceRemoveControl', false, 'screenshotPopup_' + this.randId);
    this.setTitleLink.innerHTML = this.title != '' ? BeastxWPProjectTexts.editTitle : BeastxWPProjectTexts.setTitle;
    this.dispatchEvent('settitle', this);
}

BeastxScreenshotItem.prototype.onRemoveClick = function(event) {
    DOM.cancelEvent(event);
    var areYouSure = confirm(BeastxWPProjectTexts.areYouSure);
    if (areYouSure) {
        this.dispatchEvent('removeclick', this);
    }
}

BeastxScreenshotItem.prototype.getValue = function() {
    return { id: this.id, title: this.title };
}