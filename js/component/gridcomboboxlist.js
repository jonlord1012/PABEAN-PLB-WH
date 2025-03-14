Ext.define('COMP.GridComboBoxList', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.gridcomboboxlist',
    floating: true,
    hidden: true,
    value: false,
    anyMatch: false,
    
    initComponent: function () {
        this.listeners = {
            'cellclick': this.onCellClick,
            'itemkeydown': this.onItemKeyDown
        };
        this.callParent();
    },

    onCellClick: function (tree, td, cellIndex, record, tr, rowIndex, e, eOpts) {
        this.fireEvent('picked', record);
    },

    onItemKeyDown: function (view, record, item, index, e, eOpts) {
        if (e.keyCode === e.ENTER) {
            this.fireEvent('picked', record);
        }
    },

    selectFirstRow: function () {
        var firstRecord = this.getStore().getAt(0);
        this.getSelectionModel().select(firstRecord);
    },

    doLocalQuery: function (searchValue) {
        var store = this.getStore();
        this.searchValue = searchValue.toLowerCase();

        store.setRemoteFilter(false);
        store.filterBy(this.pickerStoreFilter, this);
        this.fireEvent('filtered', store, this);
    },

    pickerStoreFilter: function (record) {
        var itemValue = record.get(this.filterField).toLowerCase();
        if (this.anyMatch) {
            if (itemValue.indexOf(this.searchValue) !== -1) {
                return true;
            }
        } else {
            if (itemValue.startsWith(this.searchValue)) {
                return true;
            }
        }
        return false;
    },

    doRemoteQuery: function (searchValue) {
        var store = this.getStore();
        store.setRemoteFilter(true);
        store.on('load', this.onPickerStoreLoad, this, {
            single: true
        });
        store.filter(new Ext.util.Filter({
            anyMatch: this.anyMatch,
            disableOnEmpty: true,
            property: this.filterField,
            value: searchValue
        }));
    },

    onPickerStoreLoad: function (store, records) {
        this.fireEvent('filtered', store, this);
    }
});