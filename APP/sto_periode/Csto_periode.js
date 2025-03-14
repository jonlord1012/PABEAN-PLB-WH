Ext.define("NJC.sto_periode.Csto_periode", {
  extend: "Ext.app.ViewController",
  alias: "controller.Csto_periode",
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
      console.log("renderer controller");
      var me = this;
      var Mainmodule = me.getView();
      var GRIDsto_periode = Mainmodule.query("grid[pid=GRIDsto_periode]")[0];
      GRIDsto_periode.getStore().load();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  btrefresh_click: function (btn) {
    try {
      var panel = btn.up("panel[reference=GRIDsto_periode]");
      var grid = panel.down("grid[pid=GRIDsto_periode]");

      if (grid) {
        grid.getStore().reload();
      } else {
        console.log("Grid 'GRIDsto_periode' not found.");
      }
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  GRIDsto_periode_click: function (grid, rowIndex, colIndex, item, e, rec) {
    try {
      var MODULEmain = grid.up("sto_periode");

      var popup = Ext.create("NJC.sto_periode.FRMsto_periode", {});
      popup.MODULEmain = MODULEmain;

      var FRM = popup.query("form")[0];

      var vdata = rec.data;
      vdata.tgl_mulai = moment(vdata.tgl_mulai).format("YYYY-MM-DD");
      vdata.tgl_selesai = moment(vdata.tgl_selesai).format("YYYY-MM-DD");
      FRM.getForm().setValues(vdata);
      var GRID = popup.query("grid[pid=GRIDsto_item]")[0];
      GRID.getStore().FRMmain = popup;
      return popup.show();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },

  btinput_periode_click: function (cmp) {
    try {
      var MODULEmain = cmp.up("sto_periode");
      var popup = Ext.create("NJC.sto_periode.FRMsto_periode", {});
      popup.MODULEmain = MODULEmain;
      var GRID = popup.query("grid[pid=GRIDsto_item]")[0];
      GRID.getStore().FRMmain = popup;
      return popup.show();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },

  proses_save_data: function (cmp, e) {
    try {
      var me = this;
      var Mainmodule = me.getView();
      var GRID = Mainmodule.query("grid[pid=GRIDsto_periode]")[0];

      var isNewRecord = e.record.id.toString().startsWith("extModel");

      var vdata = {
        id: isNewRecord ? null : e.record.id,
        tgl_mulai: Ext.Date.format(e.newValues.tgl_mulai, "Y-m-d"),
        tgl_selesai: Ext.Date.format(e.newValues.tgl_selesai, "Y-m-d"),
      };

      if (vdata.tgl_mulai === null || vdata.tgl_mulai === "") {
        COMP.TipToast.msgbox("Error", "Tanggal mulai harus diisi", { cls: "danger", delay: 2000 });
        return;
      }

      if (vdata.tgl_selesai === null || vdata.tgl_selesai === "") {
        COMP.TipToast.msgbox("Error", "Tanggal selesai harus diisi", { cls: "danger", delay: 2000 });
        return;
      }

      Ext.MessageBox.confirm("Konfirmasi", "Konfirmasi simpan data", function (button) {
        if (button === "yes") {
          var params = Ext.encode({
            method: "save_data",
            vdata: Ext.encode(vdata),
          });

          var hasil = COMP.run.getservice(vconfig.service_api + "sto_periode/sto_periode", params, "POST", localStorage.getItem("NJC_JWT"));

          hasil
            .then(function (content) {
              var val = Ext.decode(content, true);

              if (val.success === true) {
                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 2000 });
                GRID.getStore().load();
              } else {
                COMP.TipToast.msgbox("Error", val.message, { cls: "danger", delay: 2000 });
                GRID.getStore().load();
              }
            })
            .catch(function (error) {
              COMP.TipToast.msgbox("Error", "Terjadi kesalahan jaringan: " + error.message, { cls: "danger", delay: 2000 });
            });
        }
      });
    } catch (ex) {
      // Tangani kesalahan dalam try block
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },

  delete_asset_click: function (cmp, rowIndex) {
    try {
      var me = this;
      var FRMmain = cmp.up("form");
      var GRID = FRMmain.query("grid[pid=GRIDsto_periode]")[0];
      var store = GRID.getStore();
      var recordToDelete = store.getAt(rowIndex);

      if (!recordToDelete) {
        COMP.TipToast.msgbox("Error", "Pilih data yang ingin dihapus.", { cls: "danger", delay: 2000 });
        return;
      }

      Ext.MessageBox.confirm("Delete Asset", "Konfirmasi Hapus Asset Cost Center", function (button) {
        if (button === "yes") {
          var defid = recordToDelete.get("defid");

          var params = Ext.encode({
            method: "delete_data",
            defid: defid,
          });

          var hasil = COMP.run.getservice(vconfig.service_api + "sto_periode/sto_periode", params, "POST", localStorage.getItem("NJC_JWT"));
          hasil.then(function (content) {
            var val = Ext.decode(content, true);

            if (val.success === true) {
              store.remove(recordToDelete);
              store.commitChanges();
              COMP.TipToast.msgbox("Success", "Data berhasil dihapus.", { cls: "success", delay: 2000 });
            } else {
              COMP.TipToast.msgbox("Error", "Gagal menghapus data: " + val.message, { cls: "danger", delay: 2000 });
            }
          });
        }
      });
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },

});
