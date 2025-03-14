Ext.define('COMP.modulearray', {
    singleton: true,
    lengt: function(arrvalue) {
        return arrvalue.length;
    },
    array_includes: function(arrvalue,fieldname) {
        return arrvalue.includes(fieldname); // return true false;
    },
    array_find: function(arrvalue,fieldname,fieldvalue) {
        return arrvalue.find(c => c[fieldname] === fieldvalue); // return true false;
    },
    array_equal:function(a,b){
        return Array.isArray(a) &&
        Array.isArray(b) &&
        a.length === b.length &&
        a.every((val, index) => val === b[index]); // return true fales, apakah array A dan array B sesuai posisi dan jumlah nya
    },
    array_checkcolumn:function(arraymaster,arraycek){
        var x = "ok";
        arraymaster.forEach(function(n) {
            if(arraycek.includes(n)===false){
                x=n;
                return false;
            }
        });
        return x;
    }
    
    
});
