var BeastxRowEditorBaseItem = function() {}

BeastxRowEditorBaseItem.prototype.init = function(id, value) {
    this.id = id;
    this.value = value;
    this.updateUI();
}

BeastxRowEditorBaseItem.prototype.setValue = function(value) {
    this.value = value;
}

BeastxRowEditorBaseItem.prototype.isEmpty = function() {
    return !this.value;
}

BeastxRowEditorBaseItem.prototype.getValue = function() {
    return this.value;
}

BeastxRowEditorBaseItem.prototype.getId = function() {
    return this.id;
}

BeastxRowEditorBaseItem.prototype.onRemoveLinkClick = function(event) {
    DOM.cancelEvent(event);
    var areYouSure = confirm(BeastxPluginTexts.areYouSure);
    if (areYouSure) {
        this.dispatchEvent('remove', this);
    }
}

BeastxRowEditorBaseItem.prototype.updateUI = function() {
    this.widget = this.element('tbody', {}, [
        this.element('tr', null, [
            this.element('td', { 'class': 'labelTD' }, [
                this.element('label', null, [ BeastxPluginTexts.enabled . ': ' ])
            ]),
            this.element('td', { 'class': 'inputTD' }, [
                this.enabledInput = this.element('input', { type: 'checkbox' })
            ]),
            this.element('td', { 'class': 'actionsTD' }, [
                this.removeLink = this.element('a', { href: '#', onclick: this.caller('onRemoveLinkClick'), title: BeastxPluginTexts.removeTitle }, [ BeastxPluginTexts.remove ])
            ])
        ])
    ]);
    this.enabledInput.checked = this.enabled;
}