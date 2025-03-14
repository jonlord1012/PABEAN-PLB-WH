Ext.define("NJC.tr_adjustment_stock.Ctr_adjustment_stock", {
  extend: "Ext.app.ViewController",
  alias: "controller.Ctr_adjustment_stock",
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
      console.log("load Controller Ctr_adjustment_stock");
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
      var GRID = Mainmodule.query("grid[pid=GRIDtr_adjustment_stock]")[0];
      GRID.getStore().load();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  btinput_receiving_partclick: function (cmp) {
    try {
      var MODULEmain = cmp.up("tr_adjustment_stock");
      var popup = Ext.create("NJC.tr_adjustment_stock.FRMtr_adjustment_stock", {});
      popup.MODULEmain = MODULEmain;
      return popup.show();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  GRIDtr_adjustment_stock_click: function (grid, rowIndex, colIndex, item, e, rec) {
    try {
      var MODULEmain = grid.up("tr_adjustment_stock");

      var popup = Ext.create("NJC.tr_adjustment_stock.FRMtr_adjustment_stock", {});
      popup.MODULEmain = MODULEmain;

      var FRM = popup.query("form")[0];

      var vdata = rec.data;
      FRM.getForm().setValues(vdata);
      return popup.show();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  btdetail_rows_click: function (xgrid, rowIndex, colIndex, e, a, rec) {
    try {
      var MODULEmain = xgrid.up("tr_adjustment_stock");
      var me = this;
      xgrid.getSelectionModel().select(rowIndex);
      var popup = Ext.create("NJC.tr_adjustment_stock.FRMtr_adjustment_stock", {});
      popup.MODULEmain = MODULEmain;
      var FRM = popup.query("form")[0];
      var GRIDFRMtr_adjustment_stock = popup.query("grid[pid=GRIDFRMtr_adjustment_stock]")[0];
      GRIDFRMtr_adjustment_stock.getStore().on(
        "beforeload",
        function (store, operation, eOpts) {
          try {
            operation.setParams({
              method: "receiving_edit_item",
              vdata: Ext.encode(rec.data),
            });
          } catch (ex) {
            COMP.TipToast.toast("Error", ex.message, { cls: "danger", delay: 2000 });
          }
        },
        this
      );
      GRIDFRMtr_adjustment_stock.getStore().load();

      FRM.getForm().setValues({
        id: rec.data.id,
        nomor_aju: rec.data.nomor_aju,
        tanggal_aju: rec.data.tanggal_aju,
        nomor_daftar: rec.data.nomor_daftar,
        tanggal_daftar: rec.data.tanggal_daftar,
        bc_type: rec.data.bc_type,
        mode_source: rec.data.sumber_data,
        kode_internal: rec.data.supplier_kode_internal,
        nama_vendor: rec.data.name,
        receipt_no: rec.data.receipt_no,
        receipt_date: rec.data.receipt_date,
        postingstatus: rec.data.postingstatus,
        jenis_input: rec.data.jenis_input,
      });

      return popup.show();
    } catch (ex) {
      COMP.TipToast.toast("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
});
