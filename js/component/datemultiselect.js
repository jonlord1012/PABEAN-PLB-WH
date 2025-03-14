Ext.define('COMP.datemultiselect', {
    extend: 'Ext.picker.Date',
    alias: "widget.datemultiselect",
    clsHigligthClass:'x-datepicker-selected',
    
    selectedDates: null,
    
    constructor: function(args){
        this.callParent([Ext.applyIf(args||{}, {
            selectedDates: {}
        })]);   
    },
    
    initComponent: function(){
        var me = this;
        me.callParent(arguments);
        me.on('select',me.handleSelectionChanged,me);
        me.on('afterrender',me.higlighDates,me);
    },
    
    showPrevMonth: function(e){
        var me = this; 
        var c = this.update(Ext.Date.add(this.activeDate, Ext.Date.MONTH, -1));
        me.higlighDates();
        return c;
    },
    
    showNextMonth: function(e){
        var me = this; 
        var c = this.update(Ext.Date.add(this.activeDate, Ext.Date.MONTH, 1));
        me.higlighDates();
        return c;
    },
    
    higlighDates: function(){
        var me = this; 
        if(!me.cells) return;
        me.cells.each(function(item){
            var date = new Date(item.dom.firstChild.dateValue).toDateString();
            if(me.selectedDates[date]){
                item.addCls(me.clsHigligthClass);
            }else{
                item.removeCls(me.clsHigligthClass);
            }
        });
    },
    
    handleSelectionChanged: function(cmp, date){
        var me = this;
        
        if(me.selectedDates[date.toDateString()])
            delete me.selectedDates[date.toDateString()];
            else
                me.selectedDates[date.toDateString()] = date;
        me.higlighDates();
    },
    
    getSelectedDates: function(){
        var dates = [];
        Ext.iterate(this.selectedDates, function(key, val){
            dates.push(val);
        });
        dates.sort();
        return dates;
    },
    clearSelection: function() {
        var me = this,
            cells = me.cells,
            aCls = me.activeCls,
            sCls = me.selectedCls;
        
        // Clear the selection
        me.selectedDates = {};
        me.rangeSelection = false;
        cells.removeCls(aCls);
        cells.removeCls(sCls);
        
        me.update(Ext.Date.clearTime( new Date() ));
    },
    changeValue: function(values) {
        var me = this,
                selDates,
                newActive,
                active = me.activeDate;
        
        
        function getClearTime(d) { return Ext.Date.clearTime(d).getTime() };
        
        me.selectedDates = Ext.isArray(values) ? Ext.Array.map(values, getClearTime)
                    :                      [ getClearTime(values) ]
                    ;
        
        if ( Ext.isArray(me.selectedDates) ) {
            me.selDates = me.selectedDates;
        };
        
        selDates = me.selDates || [];
        
        newActive = Ext.isDate(me.selectedDates)       ? me.selectedDates
                  :                           me.activeDate
                  ;
        
        console.log(me.selectedDates);
        if ( me.rendered ) {
            var am = active    && active.getMonth(),
                ay = active    && active.getFullYear(),
                nm = newActive && newActive.getMonth(),
                ny = newActive && newActive.getFullYear();
                
            me.activeDate = newActive;

            me.selectedUpdate(selDates, newActive.getTime());
        };
    },
    selectedUpdate: function(dates, active) {
        var me          = this,
            cells       = me.cells,
            selectedCls = me.selectedCls,
            activeCls   = me.activeCls,
            visible, cancelFocus;
        
        visible     = me.isVisible();
        cancelFocus = !me.focusOnSelect;
        
        cells.each( function(el) {
            var picker = this,
                dv;
            
            el.removeCls([activeCls, selectedCls]);
           
        }, me);
    },
});
