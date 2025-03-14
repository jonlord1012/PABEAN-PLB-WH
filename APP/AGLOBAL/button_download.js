Ext.define("NJC.AGLOBAL.button_download", {
  extend: "Ext.button.Button",
  alias: "widget.button_download",
  text: "Download",
  icon: vconfig.getstyle + "icon/excel.ico",
  tooltip: "Download File",
  nvdata: "",
  handler: function (cmp) {
    try {
      var filter = [];
      if (cmp.up("panel").query("grid[pid=" + cmp.nvdata.grid_pid + "]").length === 1) {
        var GRID = cmp.up("panel").query("grid[pid=" + cmp.nvdata.grid_pid + "]")[0];
        GRID.getStore()
          .getFilters()
          .each(function (item) {
            filter.push({
              property: item.getProperty(),
              value: item.getValue(),
            });
          });
      }
      Ext.MessageBox.confirm(
        "Download Data",
        cmp.nvdata.title,
        function (button) {
          if (button === "yes") {
            var params = Ext.encode({
              method: cmp.nvdata.method,
              vdata: Ext.encode(cmp.nvdata),
              filter: Ext.encode(filter),
            });
            var hasil = COMP.run.getservice(vconfig.service_api + cmp.nvdata.modelpath, params, "POST", localStorage.getItem("NJC_JWT"));
            hasil.then(function (content) {
              var val = Ext.decode(content, true);
              if (val.success === "true") {
                COMP.run.getlinkfile(val.filename);
              } else {
                COMP.TipToast.toast("Error", val.message, { cls: "danger", delay: 3000 });
              }
            }, this);
          }
        },
        this
      );
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },

  initComponent: function () {
    this.callParent(arguments);
  },
});
