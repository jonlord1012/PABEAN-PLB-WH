var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
Ext.define("NJC.mst_category_rack.FRMmst_category_rack", {
  extend: "Ext.window.Window",
  alias: "widget.FRMmst_category_rack",
  reference: "FRMmst_category_rack",
  title: "Master Category Rack",
  modal: true,
  closeAction: "destroy",
  centered: true,
  controller: "Cmst_category_rack",
  //y: -110,
  bodyPadding: "5 5 5 5",
  flex: 1,
  width: mainpanel.getWidth() * 0.6,
  height: mainpanel.getHeight() * 0.75,
  layout: { type: "vbox", pack: "start", align: "stretch" },
  bodyStyle: "background:#FFFFFF;background-color:#FFFFFF",
  items: [
    {
      xtype: "form",
      frame: false,
      border: false,
      fieldDefaults: {
        labelAlign: "left",
        wdith: 300,
        labelWidth: 120,
        margin: "0 10 5 0",
        fieldCls: "fieldinput",
      },
      items: [
        {
          xtype: "container",
          layout: "hbox",
          bodyPadding: "5 0 0 0",
          items: [
            {
              xtype: "fieldset",
              flex: 1,
              title: "Input Category Rack",
              margin: "5 5 5 5",
              items: [
                {
                  xtype: "container",
                  layout: "vbox",
                  margin: "5 0 0 0",
                  items: [
                    // Basic Item Information
                    {
                      xtype: "container",
                      layout: "hbox",
                      margin: "0 0 5 0",
                      items: [
                        {
                          xtype: "numberfield",
                          fieldLabel: "defid",
                          name: "defid",
                          allowBlank: false,
                          maxLength: 50,
                          value: 0,
                          hidden: true,
                        },
                        {
                          xtype: "textfield",
                          fieldLabel: "Kode Kategori",
                          name: "defcode",
                          allowBlank: false,
                          maxLength: 50,
                        },
                      ],
                    },
                    {
                      xtype: "container",
                      layout: "hbox",
                      margin: "0 0 5 0",
                      items: [
                        {
                          xtype: "textfield",
                          width: 350,
                          fieldLabel: "Nama Kategori",
                          name: "defname",
                          allowBlank: false,
                          maxLength: 255,
                        },
                      ],
                    },
                  ],
                },
              ],
            },
          ],
        },
      ],
    },
    { xtype: "tbspacer", height: 10 },
    {
      xtype: "grid",
      pid: "GRIDmst_rack",
      emptyText: "No Matching Records",
      autoScroll: true,
      flex: 1,
      store: {
        autoLoad: true,
        remoteSort: true,
        remoteFilter: true,
        pageSize: 0,
        proxy: {
          type: "ajax",
          disableCaching: false,
          noCache: false,
          headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
          actionMethods: { read: "POST" },
          url: vconfig.service_api + "mst_category_rack/mst_category_racks",
          reader: {
            type: "json",
            rootProperty: "Rows",
            totalProperty: "TotalRows",
            successProperty: "success",
          },
        },
        listeners: {
          beforeload: function (store, operation, eOpts) {
            try {
              var FRMmain = store.FRMmain;
              var FRM = FRMmain.query("form")[0];
              var dtval = FRM.getValues(false, false, false, true);
              console.log(dtval);
              operation.setParams({
                method: "read_data_bycategory",
                vdata: Ext.encode(dtval),
              });
            } catch (ex) {
              COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
            }
          },
        },
      },

      plugins: ["filterfield"],
      viewConfig: {
        enableTextSelection: true,
        columnLines: true,
      },
      columns: {
        defaults: {
          menuDisabled: true,
          sortable: false,
          filter: { xtype: "textfield" },
          width: 70,
        },
        items: [
          { xtype: "rownumberer", width: 50, filter: false },
          { header: "LOKASI RACK", dataIndex: "rack_location" },
          { header: "RACK NO", dataIndex: "rack_no" },
          { header: "KATEGORI RACK", dataIndex: "rack_category" },
          { header: "PART NO", dataIndex: "part_no", width: 120 },
          { header: "MAX QTY", dataIndex: "max_qty", width: 100 },
          { header: "SAFETY QTY", dataIndex: "safety_qty", width: 100 },
        ],
      },
      bbar: [],
    },
  ],
  dockedItems: [
    {
      xtype: "toolbar",
      height: 30,
      dock: "top",
      items: [
        { xtypeL: "tbspacer", width: 5 },
        {
          xtype: "button",
          text: "Simpan",
          pid: "btsimpan_draft",
          icon: vconfig.getstyle + "icon/save.png",
          tooltip: "Proses Simpan Data Master Supplier",
          cls: "fontblack-button",
          handler: function (btn) {
            var me = btn.up("window");
            me.handler_btsave_data(btn);
          },
        },
        {
          xtype: "button",
          text: "Hapus",
          pid: "bthapus_draft",
          icon: vconfig.getstyle + "icon/delete.png",
          tooltip: "Proses Hapus Data Master Supplier",
          cls: "fontblack-button",
          handler: function (btn) {
            var me = btn.up("window");
            me.handler_btdelete_data(btn);
          },
        },
      ],
      // other options....
    },
  ],
  //========================================================
  //property handler
  //========================================================
  handler_btsave_data: function (btn) {
    try {
      var PAGEthis = btn.up("window");
      var MODULEmain = PAGEthis.MODULEmain;
      var GRIDmst_category_rack = MODULEmain.query("grid[pid=GRIDmst_category_rack]")[0];

      var FRM = PAGEthis.query("form")[0];
      var dtval = FRM.getValues(false, false, false, true);
      if (Ext.isEmpty(dtval.defcode)) {
        COMP.TipToast.msgbox("Error", "Kode Kategori harus diisi!", { cls: "danger", delay: 3000 });
        return false;
      }
      if (Ext.isEmpty(dtval.defcode)) {
        COMP.TipToast.msgbox("Error", "Nama Kategori harus diisi!", { cls: "danger", delay: 3000 });
        return false;
      }
      Ext.MessageBox.confirm(
        "Konfirmasi",
        "Konfirmasi Simpan Data",
        function (button) {
          if (button === "yes") {
            var params = Ext.encode({
              method: "save_data",
              vdata: Ext.encode(dtval),
              VUSERLOGIN,
            });

            var hasil = COMP.run.getservice(vconfig.service_api + "mst_category_rack/mst_category_rack", params, "POST", localStorage.getItem("NJC_JWT"));
            hasil.then(function (content) {
              var val = Ext.decode(content, true);
              if (val.success === "true") {
                var vdata = Ext.decode(val.vdata, true);
                FRM.getForm().setValues(vdata);
                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                GRIDmst_category_rack.getStore().load();
                PAGEthis.close();
              } else {
                COMP.TipToast.msgbox("Error", val.message, { cls: "danger", delay: 3000 });
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
  handler_btdelete_data: function (btn) {
    try {
      var PAGEthis = btn.up("window");
      var MODULEmain = PAGEthis.MODULEmain;
      var GRIDmst_category_rack = MODULEmain.query("grid[pid=GRIDmst_category_rack]")[0];

      var FRM = PAGEthis.query("form")[0];
      var dtval = FRM.getValues(false, false, false, true);
      if (Ext.isEmpty(dtval.defcode)) {
        return false;
      }
      Ext.MessageBox.confirm(
        "Konfirmasi",
        "Konfirmasi Delete data",
        function (button) {
          if (button === "yes") {
            var params = Ext.encode({
              method: "delete_data",
              vdata: Ext.encode(dtval),
            });

            var hasil = COMP.run.getservice(vconfig.service_api + "mst_category_rack/mst_category_rack", params, "POST", localStorage.getItem("NJC_JWT"));
            hasil.then(function (content) {
              var val = Ext.decode(content, true);
              if (val.success === "true") {
                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                PAGEthis.close();
                GRIDmst_category_rack.getStore().load();
              } else {
                COMP.TipToast.msgbox("Error", val.message, { cls: "danger", delay: 3000 });
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
});
