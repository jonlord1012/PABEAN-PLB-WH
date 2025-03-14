var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
Ext.define("NJC.wh_receiving_label.FRMwh_receiving_label_list_aju", {
  extend: "Ext.window.Window",
  alias: "widget.FRMwh_receiving_label_list_aju",
  reference: "FRMwh_receiving_label_list_aju",
  title: "List Aju",
  modal: true,
  closeAction: "destroy",
  centered: true,
  controller: "Cwh_receiving_label",
  //y: -110,
  bodyPadding: "5 5 5 5",
  flex: 1,
  width: mainpanel.getWidth() * 0.9,
  height: mainpanel.getHeight() * 0.9,
  layout: { type: "vbox", pack: "start", align: "stretch" },
  bodyStyle: "background:#FFFFFF;background-color:#FFFFFF",
  items: [
    {
      xtype: "grid",
      pid: "GRIDwh_receivingpart_list_aju",
      emptyText: "No Matching Records",
      viewConfig: {
        enableTextSelection: true,
        columnLines: true,
      },
      plugins: ["filterfield", { ptype: "cellediting", clicksToEdit: 1 }],
      flex: 1,
      height: 200,
      store: {
        autoLoad: true,
        remoteSort: true,
        remoteFilter: true,
        pageSize: 17,
        field: [{ dataIndex: "POSTINGSTATUS", type: "string" }],
        proxy: {
          type: "ajax",
          disableCaching: false,
          noCache: false,
          headers: {
            Authorization: "Bearer " + localStorage.getItem("NJC_JWT"),
          },
          actionMethods: { read: "POST" },
          url:
            vconfig.service_api +
            "wh_receiving_label/wh_receiving_labels",
          extraParams: {
            method: "read_list_dokumen",
          },
          reader: {
            type: "json",
            rootProperty: "Rows",
            totalProperty: "TotalRows",
            successProperty: "success",
          },
        },
      },
      columns: {
        defaults: {
          sortable: true,
          menuDisable: false,
          filter: { xtype: "textfield" },
          width: 100,
        },
        items: [
          { xtype: "rownumberer", width: 35, filter: false },
          { header: "INVOICE NO", dataIndex: "invoice_no" },
          { header: "INVOICE DATE", dataIndex: "invoice_date" },
          { header: "MODE SOURCE", dataIndex: "mode_source" },
          { header: "BC TYPE", dataIndex: "kode_dokumen_pabean", width: 75 },
          { header: "NOMOR AJU", dataIndex: "nomor_aju", width: 190 },
          { header: "TGL AJU", dataIndex: "tanggal_aju" },
          { header: "NOMOR DAFTAR", dataIndex: "nomor_daftar" },
          { header: "TGL DAFTAR", dataIndex: "tanggal_daftar" },
          { header: "KODE INTERNAL", dataIndex: "supplier_kode_internal" },
          { header: "NAMA VENDOR", dataIndex: "nama", flex: 1 },
        ],
      },
      tbar: [
        {
          xtype: "button",
          pid: "btrefresh_main",
          text: "Refresh",
          icon: vconfig.getstyle + "icon/update.ico",
          tooltip: "Refresh Data",
          cls: "fontblack-button",
          handler: function (btn) {
            try {
              var FRMmain = btn.up("window");
              var GRID = FRMmain.query("grid")[0];
              GRID.getStore().load();
            } catch (ex) {
              COMP.TipToast.toast("Error", ex.message, {
                cls: "danger",
                delay: 2000,
              });
            }
          },
        },
      ],
      bbar: {
        xtype: "pagingtoolbar",
        displayInfo: true,
        displayMsg: "Displaying BC {0} - {1} of {2}",
        emptyMsg: "No Document to display",
      },
    },
  ],

  //========================================================
  //property handler
  //========================================================
  handler_btsave_data: function (btn) {
    try {
      var PAGEthis = btn.up("window");
      var MODULEmain = PAGEthis.MODULEmain;
      var GRIDwh_receiving_label = MODULEmain.query(
        "grid[pid=GRIDwh_receiving_label]"
      )[0];

      var FRM = PAGEthis.query("form")[0];
      var dtval = FRM.getValues(false, false, false, true);
      if (Ext.isEmpty(dtval.CATEGORY_CODE)) {
        COMP.TipToast.msgbox("Error", "Kode Kategori harus diisi!", {
          cls: "danger",
          delay: 3000,
        });
        return false;
      }
      if (Ext.isEmpty(dtval.CATEGORY_NAME)) {
        COMP.TipToast.msgbox("Error", "Nama Kategori harus diisi!", {
          cls: "danger",
          delay: 3000,
        });
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

            var hasil = COMP.run.getservice(
              vconfig.service_api +
                "wh_receiving_label/wh_receiving_label",
              params,
              "POST",
              localStorage.getItem("NJC_JWT")
            );
            hasil.then(function (content) {
              var val = Ext.decode(content, true);
              if (val.success === "true") {
                var vdata = Ext.decode(val.vdata, true);
                FRM.getForm().setValues(vdata);
                COMP.TipToast.msgbox("Success", val.message, {
                  cls: "success",
                  delay: 3000,
                });
                GRIDwh_receiving_label.getStore().load();
                PAGEthis.close();
              } else {
                COMP.TipToast.msgbox("Error", val.message, {
                  cls: "danger",
                  delay: 3000,
                });
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
