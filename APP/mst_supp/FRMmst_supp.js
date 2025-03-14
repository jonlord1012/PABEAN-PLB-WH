var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
Ext.define("NJC.mst_supp.FRMmst_supp", {
  extend: "Ext.window.Window",
  alias: "widget.FRMmst_supp",
  reference: "FRMmst_supp",
  title: "Master Supplier",
  modal: true,
  closeAction: "destroy",
  centered: true,
  controller: "Cmst_supp",
  //y: -110,
  bodyPadding: "5 5 5 5",
  flex: 1,
  width: mainpanel.getWidth() * 0.5,
  height: mainpanel.getHeight() * 0.62,
  layout: { type: "vbox", pack: "start", align: "stretch" },
  bodyStyle: "background:#FFFFFF;background-color:#FFFFFF",
  items: [
    {
      xtype: "form",
      frame: false,
      border: false,
      fieldDefaults: {
        labelAlign: "left",
        labelWidth: 100,
        margin: "0 10 5 0",
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
              margin: "5 5 5 5",
              items: [
                {
                  xtype: "container",
                  layout: "vbox",
                  margin: "5 0 0 0",
                  items: [
                    { xtype: "numberfield", width: 200, fieldLabel: "ID", name: "ID", fieldCls: "fieldinput", readOnly: false, value: 0, hidden: true },

                    {
                      xtype: "container",
                      layout: "hbox",
                      items: [{ xtype: "textfield", width: 150, fieldLabel: "Kode Internal", name: "KODE_INTERNAL", maxLength: 20, fieldCls: "fieldinput", readOnly: false, hidden: true }],
                    },

                    { xtype: "textfield", width: 600, fieldLabel: "Nama", name: "NAMA", maxLength: 255, fieldCls: "fieldinput", readOnly: false },
                    { xtype: "textarea", width: 600, fieldLabel: "Alamat", name: "ALAMAT", fieldCls: "fieldinput", readOnly: false },
                    {
                      xtype: "combobox",
                      name: "SUPP_CATEGORY",
                      fieldLabel: "Kategori Supplier",
                      displayField: "CATEGORY_NAME",
                      valueField: "CATEGORY_NAME",
                      fieldCls: "fieldinput",
                      allowBlank: false,
                      queryMode: "local",
                      forceSelection: true,
                      typeAhead: true,
                      minChars: 0,
                      anyMatch: true,
                      store: {
                        autoLoad: true,
                        remoteSort: true,
                        remoteFilter: true,
                        pageSize: 17,
                        fields: ["CATEGORY_CODE", "CATEGORY_NAME"],
                        proxy: {
                          type: "ajax",
                          disableCaching: false,
                          noCache: false,
                          headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
                          actionMethods: { read: "POST" },
                          url: vconfig.service_api + "mst_category_supp/mst_category_supps",
                          extraParams: {
                            method: "read_data",
                          },
                          reader: {
                            type: "json",
                            rootProperty: "Rows",
                            totalProperty: "TotalRows",
                            successProperty: "success",
                          },
                        },
                      },
                    },
                    {
                      xtype: "container",
                      layout: "hbox",
                      items: [
                        { xtype: "textfield", width: 290, fieldLabel: "NPWP", name: "NPWP", maxLength: 20, fieldCls: "fieldinput", readOnly: false },
                        { xtype: "textfield", labelWidth: 70, width: 280, fieldLabel: "NIB", name: "NIB", maxLength: 20, fieldCls: "fieldinput", readOnly: false },
                      ],
                    },
                    {
                      xtype: "container",
                      layout: "hbox",
                      items: [
                        { xtype: "textfield", width: 290, fieldLabel: "Nomor Ijin", name: "NOMOR_IJIN", fieldCls: "fieldinput", readOnly: false },
                        { xtype: "datefield", labelWidth: 70, width: 170, fieldLabel: "Tgl Ijin", name: "TANGGAL_IJIN", fieldCls: "fieldinput", format: "Y-m-d", readOnly: false },
                      ],
                    },
                    {
                      xtype: "container",
                      layout: "hbox",
                      items: [
                        { xtype: "textfield", width: 150, fieldLabel: "Kode ID", name: "KODE_ID", maxLength: 10, fieldCls: "fieldinput", readOnly: false },
                        { xtype: "tbspacer", width: 100 },
                        { xtype: "textfield", margin: "0 0 0 40", labelWidth: 70, width: 120, fieldLabel: "Kode Negara", name: "KODE_NEGARA", maxLength: 10, fieldCls: "fieldinput", readOnly: false },
                      ],
                    },
                    {
                      xtype: "container",
                      layout: "hbox",
                      items: [
                        { xtype: "textfield", width: 150, fieldLabel: "ID Company", name: "ID_COMPANY", maxLength: 20, fieldCls: "fieldinput", readOnly: false },
                        { xtype: "tbspacer", width: 100 },
                        { xtype: "numberfield", width: 150, labelWidth: 70, margin: "0 0 0 40", fieldLabel: "ID Ceisa", minValue: 1, name: "ID_CEISA", fieldCls: "fieldinput", readOnly: false, hideTrigger: true },
                      ],
                    },
                    {
                      xtype: "container",
                      layout: "hbox",
                      items: [
                        { xtype: "textfield", width: 150, fieldLabel: "Kode Jenis API", name: "KODEJENISAPI", maxLength: 20, fieldCls: "fieldinput", readOnly: false },
                        { xtype: "tbspacer", width: 100 },
                        { xtype: "textfield", width: 280, labelWidth: 70, margin: "0 0 0 40", fieldLabel: "Niperentitas", name: "NIPERENTITAS", maxLength: 20, fieldCls: "fieldinput", readOnly: false },
                      ],
                    },
                    {
                      xtype: "container",
                      layout: "hbox",
                      items: [
                        { xtype: "textfield", width: 150, fieldLabel: "Kode Jenis Identitas", name: "KODEJENISIDENTITAS", maxLength: 20, fieldCls: "fieldinput", readOnly: false, value: 5 },
                        { xtype: "tbspacer", width: 100 },
                        { xtype: "textfield", margin: "0 0 0 40", labelWidth: 70, width: 120, fieldLabel: "Kode Status", name: "KODESTATUS", maxLength: 20, fieldCls: "fieldinput", readOnly: false },
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
      var GRIDmst_supp = MODULEmain.query("grid[pid=GRIDmst_supp]")[0];

      var FRM = PAGEthis.query("form")[0];
      var dtval = FRM.getValues(false, false, false, true);
      if (Ext.isEmpty(dtval.NAMA)) {
        COMP.TipToast.msgbox("Error", "Nama Supplier tidak boleh kosong", { cls: "danger", delay: 3000 });
        return false;
      }
      if (Ext.isEmpty(dtval.ALAMAT)) {
        COMP.TipToast.msgbox("Error", "Alamat Supplier tidak boleh kosong", { cls: "danger", delay: 3000 });
        return false;
      }
      if (Ext.isEmpty(dtval.SUPP_CATEGORY)) {
        COMP.TipToast.msgbox("Error", "Kategori Supplier tidak boleh kosong", { cls: "danger", delay: 3000 });
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

            var hasil = COMP.run.getservice(vconfig.service_api + "mst_supp/mst_supp", params, "POST", localStorage.getItem("NJC_JWT"));
            hasil.then(function (content) {
              var val = Ext.decode(content, true);
              if (val.success === "true") {
                var vdata = Ext.decode(val.vdata, true);
                FRM.getForm().setValues(vdata);
                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                GRIDmst_supp.getStore().load();
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
      var GRIDmst_supp = MODULEmain.query("grid[pid=GRIDmst_supp]")[0];

      var FRM = PAGEthis.query("form")[0];
      var dtval = FRM.getValues(false, false, false, true);
      dtval.dokumen_date = dtval.dokumen_date === null ? null : moment(dtval.dokumen_date).format("YYYY-MM-DD");
      if (Ext.isEmpty(dtval.ID || dtval.ID === 0)) {
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

            var hasil = COMP.run.getservice(vconfig.service_api + "mst_supp/mst_supp", params, "POST", localStorage.getItem("NJC_JWT"));
            hasil.then(function (content) {
              var val = Ext.decode(content, true);
              if (val.success === "true") {
                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                PAGEthis.close();
                GRIDmst_supp.getStore().load();
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
