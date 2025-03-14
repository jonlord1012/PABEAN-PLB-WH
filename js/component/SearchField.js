Ext.define('COMP.SearchField',{
    extend: 'Ext.form.field.Text',
    alias: 'widget.searchfield',
    triggers:{
        search: {
            cls: 'x-form-search-trigger',
            handler: function() {
                this.setFilter(this.up().dataIndex, this.getValue())
            }
        },
        clear: {
            cls: 'x-form-clear-trigger',
            handler: function(field) {
                console.log(field);
                var column = field.ownerCt.ownerCt,
                    grid = column.up('grid');

                grid.getStore().removeFilter(field.property || column.dataIndex);
                field.triggers.clear.el.hide();
                if(!this.autoSearch) this.setFilter(this.up().dataIndex, '');
            }
            
        }
    },
    setFilter: function(filterId, value){
        var store = this.up('grid').getStore();
        if(value){
            store.removeFilter(filterId, false)
            var filter = {id: filterId, property: filterId, value: value};
            if(this.anyMatch) filter.anyMatch = this.anyMatch
            if(this.caseSensitive) filter.caseSensitive = this.caseSensitive
            if(this.exactMatch) filter.exactMatch = this.exactMatch
            if(this.operator) filter.operator = this.operator
            console.log(this.anyMatch, filter)
            store.addFilter(filter)
        } else {
            store.filters.removeAtKey(filterId)
            store.reload()
        }
    },
    listeners: {
        render: function(){
            var me = this;
            me.ownerCt.on('resize', function(){
                me.setWidth(this.getEl().getWidth())
            })
        },
        change: function() {
            if(this.autoSearch) this.setFilter(this.up().dataIndex, this.getValue())
        }
    }
})