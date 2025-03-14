var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
Ext.define("NJC.mst_category_supp.FRMmst_category_supp", {
  extend: "Ext.window.Window",
  alias: "widget.FRMmst_category_supp",
  reference: "FRMmst_category_supp",
  title: "Master Category Supplier",
  modal: true,
  closeAction: "destroy",
  centered: true,
  controller: "Cmst_category_supp",
  //y: -110,
  bodyPadding: "5 5 5 5",
  flex: 1,
  width: mainpanel.getWidth() * 0.38,
  height: mainpanel.getHeight() * 0.32,
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
              title: "Input Category Supplier",
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
                          fieldLabel: "ID",
                          name: "ID",
                          allowBlank: false,
                          maxLength: 50,
                          value: 0,
                          hidden: true,
                        },
                        {
                          xtype: "textfield",
                          fieldLabel: "Kode Kategori",
                          name: "CATEGORY_CODE",
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
                          name: "CATEGORY_NAME",
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
      var GRIDmst_category_supp = MODULEmain.query("grid[pid=GRIDmst_category_supp]")[0];

      var FRM = PAGEthis.query("form")[0];
      var dtval = FRM.getValues(false, false, false, true);
      if (Ext.isEmpty(dtval.CATEGORY_CODE)) {
        COMP.TipToast.msgbox("Error", "Kode Kategori harus diisi!", { cls: "danger", delay: 3000 });
        return false;
      }
      if (Ext.isEmpty(dtval.CATEGORY_NAME)) {
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

            var hasil = COMP.run.getservice(vconfig.service_api + "mst_category_supp/mst_category_supp", params, "POST", localStorage.getItem("NJC_JWT"));
            hasil.then(function (content) {
              var val = Ext.decode(content, true);
              if (val.success === "true") {
                var vdata = Ext.decode(val.vdata, true);
                FRM.getForm().setValues(vdata);
                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                GRIDmst_category_supp.getStore().load();
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
      var GRIDmst_category_supp = MODULEmain.query("grid[pid=GRIDmst_category_supp]")[0];

      var FRM = PAGEthis.query("form")[0];
      var dtval = FRM.getValues(false, false, false, true);
      if (Ext.isEmpty(dtval.CATEGORY_CODE)) {
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

            var hasil = COMP.run.getservice(vconfig.service_api + "mst_category_supp/mst_category_supp", params, "POST", localStorage.getItem("NJC_JWT"));
            hasil.then(function (content) {
              var val = Ext.decode(content, true);
              if (val.success === "true") {
                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                PAGEthis.close();
                GRIDmst_category_supp.getStore().load();
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
