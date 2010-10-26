var BeastxRowEditor = function() { this.scriptName = 'BeastxRowEditor' }

BeastxRowEditor.prototype.init = function(id, form, container, addNewLink, rowClassRef) {
    this.id = id;
    this.form = form;
    this.container = container;
    this.addNewLink = addNewLink;
    this.rowClassRef = rowClassRef;
    DOM.addListener(this.addNewLink, 'click', this.caller('onAddNewLinkClick'));
    DOM.addListener(this.form, 'submit', this.caller('onFormSubmit'));
    this.appendChild(
        this.container, 
        this.valueElement = this.element('textarea', { name: this.id, id: this.id, type: 'text', rows: '15', cols: 50, style: { display: 'none' } })
    );
    this.appendChild(
        this.container, 
        this.table = this.element('table', { 'class': 'RowEditorTable', width: '95%', cellPadding: 0, cellSpacing: 0 })
    );
    this.rows = [];
}

BeastxRowEditor.prototype.onFormSubmit = function(event) {
    this.valueElement.value = this.getValue(true);
}

BeastxRowEditor.prototype.onAddNewLinkClick = function(event) {
    DOM.cancelEvent(event);
    this.addRow(New(this.rowClassRef));
}

BeastxRowEditor.prototype.onRemove = function(rowObject) {
    this.removeRow(rowObject);
}

BeastxRowEditor.prototype.onCopy = function(rowObject) {
    //~ this.removeRow(rowObject);
}

BeastxRowEditor.prototype.addRow = function(rowObject) {
    this.rows.push(rowObject);
    rowObject.addListener('onremove', this.caller('onRemove'));
    rowObject.addListener('oncopy', this.caller('onCopy'));
    rowObject.addListener('onsave', this.caller('onItemSave'));
    this.appendChild(this.table, rowObject);
    this.valueElement.value = this.getValue();
}

BeastxRowEditor.prototype.onItemSave = function() {
    this.valueElement.value = this.getValue(true);
}

BeastxRowEditor.prototype.removeRow = function(rowObject) {
    var tempRows = [];
    for (var i = 0; i < this.rows.length; ++i) {
        if (this.rows[i] == rowObject) {
            this.removeChild(this.table, this.rows[i]);
        } else {
            tempRows.push(this.rows[i]);
        }
    }
    this.rows = tempRows;
}

BeastxRowEditor.prototype.getValue = function(forceSave) {
    var value = [];
    for (var i = 0; i < this.rows.length; ++i) {
        if (forceSave && this.rows[i].save) {
            this.rows[i].save();
        }
        if (!this.rows[i].isEmpty()) {
            value.push(this.rows[i].getValue());
        }
    }
    return VAR.serialize(value);
}
