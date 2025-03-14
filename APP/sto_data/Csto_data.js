Ext.define("NJC.sto_data.Csto_data", {
  extend: "Ext.app.ViewController",
  alias: "controller.Csto_data",
  init: function (view) {
    this.control({
      //
    });
    this.listen({
      store: {},
    });
    this.var_global = {
      jwt: localStorage.getItem("NJC_JWT"),
    };
    this.var_definition = {};
    this.renderpage();
  },
  formatAmount: function (value) {
    var text = Ext.util.Format.number(value, "0,000.00/i");
    return text;
  },
  formatNumber: function (value) {
    var text = Ext.util.Format.number(value, "0,000/i");
    return text;
  },
  renderpage: function () {
    try {
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  renderform: function (cmp) {
    try {
      var form = cmp.query("form")[1];
      var grid = cmp.query("grid")[0];

      grid.getStore().on(
        "load",
        function (store, records, successful, operation) {
          if (successful && records.length > 0) {
            var storeData = records[0].getData();
            var response = operation.getResponse().responseJson;
            delete response.Rows;
            form.getForm().setValues(response);
          } else {
            form.getForm().setValues("-");
          }
        },
        this
      );
      grid.getStore().on(
        "beforeload",
        function (store, operation) {
          operation.setParams({
            method: "read_data_by_rack",
          });
        },
        this
      );
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },

  btrefresh_click: function (cmp) {
    try {
      var me = this;
      var FRMmain = cmp.up("form");
      var GRID = FRMmain.query("grid")[0];
      var GRID2 = FRMmain.query("grid")[1];
      var GRID3 = FRMmain.query("grid")[2];
      GRID.getStore().load();
      GRID2.getStore().load();
      GRID3.getStore().load();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
});
