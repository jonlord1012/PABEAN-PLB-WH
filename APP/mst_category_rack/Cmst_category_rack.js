Ext.define("NJC.mst_category_rack.Cmst_category_rack", {
  extend: "Ext.app.ViewController",
  alias: "controller.Cmst_category_rack",
  init: function (view) {
    this.control({
      //
    });
    this.listen({
      store: {},
    });
    this.var_global = {
      jwt: localStorage.getItem("NJC_JWT"),
      profile: localStorage.getItem("NJC_PROFILE"),
    };
    this.renderpage();
  },
  formatqty: function (value) {
    var text = Ext.util.Format.number(value, "0,000.00/i");
    return text;
  },
  formatAmount: function (value) {
    var text = Ext.util.Format.number(value, "0,000.00/i");
    return text;
  },
  formatDate: function (value) {
    var text = moment(value).format("YYYY-MM-DD");
    return text;
  },
  renderpage: function () {
    try {
      console.log("load Controller Cmst_category_rack");
      var profile = this.var_global.profile;
      vprofile = Ext.decode(profile);
      VUSERLOGIN = vprofile[0].USER_NAME;
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  btrefresh_click: function () {
    try {
      var Mainmodule = this.getView();
      var GRID = Mainmodule.query("grid[pid=GRIDmst_category_rack]")[0];
      GRID.getStore().load();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  btinput_mst_category_rack_click: function (cmp) {
    try {
      var MODULEmain = cmp.up("mst_category_rack");
      var popup = Ext.create("NJC.mst_category_rack.FRMmst_category_rack", {});
      popup.MODULEmain = MODULEmain;
      var grid = popup.query("grid[pid=GRIDmst_rack]")[0];
      grid.getStore().FRMmain = popup;
      return popup.show();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  GRIDmst_category_rack_click: function (grid, rowIndex, colIndex, item, e, rec) {
    try {
      var MODULEmain = grid.up("mst_category_rack");

      var popup = Ext.create("NJC.mst_category_rack.FRMmst_category_rack", {});
      popup.MODULEmain = MODULEmain;

      var FRM = popup.query("form")[0];
      var vdata = rec.data;
      FRM.getForm().setValues(vdata);

      var grid = popup.query("grid[pid=GRIDmst_rack]")[0];
      grid.getStore().FRMmain = popup;
      return popup.show();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
});
