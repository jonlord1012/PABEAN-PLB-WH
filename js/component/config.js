Ext.define('COMP.config', {
    singleton: true,
    Formatdate:function(date){
        try{
            var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
            
        }catch(e){
            console.log(e);
        }
        
    },
    Formatmonthint :function(xmonth){
        try{
            var params = {
                JAN: 1,
                FEB: 2,
                MAR: 3,APR:4,MAY:5,JUN:6,JUL:7,AUG:8,SEP:9,OCT:10,NOV:11,DEC:12
            };
            return params[xmonth];
            
        }catch(e){
            console.log(e);
        }
    },
    Formatmonth:function(date){
        try{
            var monthNames = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN","JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
            var d = new Date(date),
            month = (d.getMonth());
            return monthNames[month];
            
        }catch(e){
            console.log(e);
        }
    },
    Formatbudgetmonth:function(date,bmonth){
        try{
            var monthNames = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN","JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
            var budgetmonth = new Date(Date.parse(bmonth +" 1, 2012")).getMonth()+1;
            return budgetmonth;
            
        }catch(e){
            console.log(e);
        }
    },
    FormatETA:function(xyear,xmonth,cmp){
        try{
            var params = {
                JAN: 1,
                FEB: 2,
                MAR: 3,APR:4,MAY:5,JUN:6,JUL:7,AUG:8,SEP:9,OCT:10,NOV:11,DEC:12
            };
           var xdate = new Date(xyear + '-' + params[xmonth] +'-01' );
           var nowdate = new Date();
           
           var monthnow = nowdate.getMonth();
           var daynow = nowdate.getDay();
           var monthold = xdate.getMonth();
           var date1 ,date2;
           if(monthnow===monthold){
               date1 = nowdate;
               date2 = nowdate.getFullYear() + '-' + params[xmonth] + '-' + new Date(nowdate.getFullYear(), params[xmonth], 0).getDate();
                var setdata = new Date(date1);
                cmp.setValue(setdata);
                cmp.setMinValue(new Date(date1));
                cmp.setMaxValue(new Date (date2));
           }else{
               date1 = xyear + '-' + params[xmonth] +'-01' ;
               date2 = xyear + '-' + params[xmonth] + '-' + new Date(xyear, params[xmonth], 0).getDate();
                var setdata = new Date(date1);
                cmp.setValue(setdata);
                cmp.setMinValue(new Date(date1));
                cmp.setMaxValue(new Date (date2));
           }
            return true;
            
        }catch(e){
            console.log(e);
        }       
    }
    
});