Ext.define('COMP.comboboxlist', {
    extend: 'Ext.form.field.Picker',
    requires: [
        'COMP.GridComboBoxList'
    ],
    alias: 'widget.comboboxlist',
    store: false,
    queryMode: 'local',
    anyMatch: false,

    filterDelayBuffer: 300,
    enableKeyEvents: true,
    valueField: 'text',
    selectedRecord: false,

    gridConfig: {
        // Grid Config
    },

    initComponent: function () {
        this.on('change', this.onGridComboValueChange, this, {
            buffer: this.filterDelayBuffer
        });
        this.on('keydown', this.onItemKeyDown, this, {
            buffer: this.filterDelayBuffer
        });

        this.callParent();
    },

    onGridComboValueChange: function (field, value) {
        this.selectedRecord = false;
        switch (this.queryMode) {
        case 'local':
            this.getPicker().doLocalQuery(value);
            break;
        case 'remote':
            this.getPicker().doRemoteQuery(value);
            break;
        }
    },

    onItemKeyDown: function (view, record, item, index, e, eOpts) {
        if (e.keyCode === e.ENTER) {
            console.log('enter proses');
        }
    },

    expand: function () {
        this.callParent([arguments]);
    },

    createPicker: function () {
        var gridConfig = Ext.apply({
            xtype: 'gridcomboboxlist',
            id: this.getId() + '-GridPicker',
            store: this.getPickerStore(),
            valueField: this.valueField,
            displayField: this.displayField,
            anyMatch: this.anyMatch,
            allowFolderSelect: this.allowFolderSelect,
            columns: this.columns,
            filterField: this.filterField,
        }, this.gridConfig);
        var gridPanelPicker = Ext.widget(gridConfig);

        gridPanelPicker.on({
            picked: this.onPicked,
            filtered: this.onFiltered,
            beforeselect: this.onBeforeSelect,
            beforedeselect: this.onBeforeDeselect,
            scope: this
        });
        return gridPanelPicker;
    },

    onFiltered: function (store, gridList) {
        if (store.getCount() > 0) {
            this.focus();
        }
    },

    getPickerStore: function () {
        return this.store;
    },

    onPicked: function (record) {
        this.suspendEvent('change');
        this.selectedRecord = record;
        this.setValue(record.get(this.displayField));
        this.collapse();
        this.resumeEvent('change');
        this.fireEvent('select', record);
    },

    getValue: function () {
        var value;
        if (this.valueField && this.selectedRecord) {
            value = this.selectedRecord.get(this.valueField);
        } else {
            value = this.getRawValue();
        }
        return value;
    },

    getSubmitValue: function () {
        var value = this.getValue();
        if (Ext.isEmpty(value)) {
            value = '';
        }
        return value;
    },
    onBeforeSelect: function (comboBox, record, recordIndex) {
        return this.fireEvent('beforeselect', this, record, recordIndex);
    },

    onBeforeDeselect: function (comboBox, record, recordIndex) {
        return this.fireEvent('beforedeselect', this, record, recordIndex);
    },

    getSelectedRecord: function () {
        return this.selectedRecord;
    }
});