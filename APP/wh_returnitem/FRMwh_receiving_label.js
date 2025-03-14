var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
Ext.define("NJC.wh_receiving_label.FRMwh_receiving_label", {
  extend: "Ext.window.Window",
  alias: "widget.FRMwh_receiving_label",
  reference: "FRMwh_receiving_label",
  title: "Input Receiving Part Item/Material",
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
      xtype: "form",
      bodyPadding: "5 5 5 5",
      fieldDefaults: {
        labelAlign: "left",
        labelWidth: 70,
        margin: "0 10 5 0",
      },
      border: false,
      layout: { type: "hbox", pack: "start", align: "stretch" },
      items: [
        {
          xtype: "container",
          layout: "vbox",
          flex: 1,
          items: [
            {
              xtype: "container",
              width: 600,
              layout: "vbox",
              items: [
                {
                  xtype: "container",
                  layout: "hbox",
                  items: [
                    {
                      xtype: "numberfield",
                      fieldLabel: "id",
                      name: "id",
                      allowBlank: false,
                      maxLength: 50,
                      value: 0,
                      hidden: true,
                    },
                    {
                      xtype: "textfield",
                      labelWidth: 100,
                      width: 300,
                      fieldLabel: "Receiving No",
                      name: "receipt_no",
                      fieldCls: "fieldlock",
                      readOnly: true,
                      enforceMaxLength: true,
                      emptyText: "Nomor Receiving Auto",
                    },
                    {
                      xtype: "datefield",
                      labelWidth: 60,
                      width: 170,
                      fieldLabel: "Tgl Receipt",
                      name: "receipt_date",
                      fieldCls: "fieldinput",
                      readOnly: false,
                      format: "Y-m-d",
                      value: new Date(),
                    },
                  ],
                },
                {
                  xtype: "container",
                  layout: "hbox",
                  items: [
                    {
                      xtype: "datefield",
                      labelWidth: 100,
                      width: 220,
                      fieldLabel: "Tgl & No Aju",
                      name: "tanggal_aju",
                      fieldCls: "fieldlock",
                      readOnly: true,
                      format: "Y-m-d",
                    },
                    {
                      xtype: "textfield",
                      width: 250,
                      name: "nomor_aju",
                      fieldCls: "fieldlock",
                      readOnly: true,
                      enforceMaxLength: true,
                      emptyText: "Nomor Aju",
                    },
                    {
                      xtype: "button",
                      pid: "btsearch_aju",
                      icon: vconfig.getstyle + "icon/search.ico",
                      tooltip: "search",
                      handler: "btsearch_aju_click",
                      handler: function (btn) {
                        var me = btn.up("window");
                        me.btsearch_aju_click(btn);
                      },
                    },
                  ],
                },
                {
                  xtype: "container",
                  layout: "hbox",
                  items: [
                    {
                      xtype: "datefield",
                      labelWidth: 100,
                      width: 220,
                      fieldLabel: "Tgl & No Daftar",
                      name: "tanggal_daftar",
                      fieldCls: "fieldlock",
                      readOnly: true,
                      format: "Y-m-d",
                    },
                    {
                      xtype: "textfield",
                      width: 170,
                      name: "nomor_daftar",
                      fieldCls: "fieldlock",
                      readOnly: true,
                      enforceMaxLength: true,
                      emptyText: "Nomor Daftar",
                    },
                    {
                      xtype: "textfield",
                      width: 70,
                      name: "kode_dokumen_pabean",
                      fieldCls: "fieldlock",
                      readOnly: true,
                      enforceMaxLength: true,
                      emptyText: "BC TYPE",
                    },
                  ],
                },
              ],
            },
          ],
        },
        { xtype: "tbspacer", width: 5 },
        {
          xtype: "container",
          layout: "vbox",
          flex: 1,
          items: [
            {
              xtype: "container",
              layout: "hbox",
              items: [
                //
                {
                  xtype: "textfield",
                  labelWidth: 100,
                  width: 200,
                  fieldLabel: "Sumber Data",
                  name: "mode_source",
                  fieldCls: "fieldlock",
                  readOnly: true,
                  enforceMaxLength: true,
                  emptyText: "Kode",
                },
                {
                  xtype: "textfield",
                  width: 100,
                  name: "POSTINGSTATUS",
                  fieldCls: "fieldlock",
                  readOnly: true,
                  enforceMaxLength: true,
                  value: "OPEN",
                },
                {
                  xtype: "textfield",
                  width: 100,
                  name: "JENIS_INPUT",
                  fieldCls: "fieldlock",
                  readOnly: true,
                  enforceMaxLength: true,
                  value: "",
                },
              ],
            },
            {
              xtype: "container",
              layout: "hbox",
              items: [
                {
                  xtype: "textfield",
                  labelWidth: 100,
                  width: 160,
                  fieldLabel: "Supplier",
                  name: "supplier_kode_internal",
                  fieldCls: "fieldlock",
                  readOnly: true,
                  enforceMaxLength: true,
                  emptyText: "Kode",
                },
                {
                  xtype: "textfield",
                  width: 350,
                  name: "nama",
                  fieldCls: "fieldlock",
                  readOnly: true,
                  enforceMaxLength: true,
                  emptyText: "Nama Supplier",
                },
              ],
            },
          ],
        },
      ],
    },
    { xtype: "tbspacer", height: 10 },
    {
      xtype: "container",
      layout: { type: "hbox", pack: "start", align: "stretch" },
      flex: 1,
      items: [
        {
          xtype: "grid",
          pid: "GRIDFRMwh_receiving_label",
          emptyText: "No Matching Records",
          flex: 1,
          plugins: [
            "filterfield",
            {
              ptype: "cellediting",
              clicksToEdit: 1,
            },
          ],
          viewConfig: {
            enableTextSelection: true,
            columnLines: true,
          },
          store: {
            autoLoad: false,
            remoteSort: false,
            remoteFilter: false,
            field: [
              { name: "RECEIPT_NO", type: "string" },
              { name: "INVOICE_NO", type: "string" },
              { name: "INVOICE_DATE", type: "string" },
              { name: "PART_NO", type: "string" },
              { name: "MAPP_PARTNO", type: "string" },
              { name: "PART_NAME", type: "string" },
              { name: "SERI_BARANG", type: "int" },
              { name: "INVOICE_QTY", type: "float" },
              { name: "IN_QTY", type: "float" },
              { name: "SISA_QTY", type: "float" },
              { name: "EDS_NOMOR_AJU", type: "string" },
              { name: "tanggal_aju", type: "string" },
              { name: "NOMOR_DAFTAR", type: "string" },
              { name: "tanggal_daftar", type: "string" },
              { name: "KODE_INTERNAL", type: "string" },
              { name: "NAMA_VENDOR", type: "string" },
              { name: "MODE_SOURCE", type: "string" },
              { name: "kode_dokumen_pabean", type: "string" },
              { name: "NOMOR_FAKTUR", type: "string" },
              { name: "TANGGAL_FAKTUR", type: "string" },
              { name: "INPUT_QTY", type: "float" },
              { name: "GROUP_ITEM", type: "string" },
            ],
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
              reader: {
                type: "json",
                rootProperty: "Rows",
                totalProperty: "TotalRows",
                successProperty: "success",
              },
            },
          },
          columns: [
            { xtype: "rownumberer", width: 50 },
            {
              sortable: true,
              width: 100,
              filter: { xtype: "textfield" },
              header: "INVOICE",
              dataIndex: "invoice_no",
            },
            {
              sortable: true,
              width: 100,
              filter: { xtype: "textfield" },
              header: "INVOICE DATE",
              dataIndex: "invoice_date",
            },
            {
              sortable: true,
              width: 140,
              filter: { xtype: "textfield" },
              header: "PART NO",
              dataIndex: "part_no",
            },
            {
              sortable: true,
              width: 140,
              filter: { xtype: "textfield" },
              header: "MAPP PART",
              dataIndex: "mapp_partno",
            },
            {
              sortable: true,
              width: 75,
              filter: { xtype: "textfield" },
              header: "SERI",
              dataIndex: "seri_barang",
            },
            {
              sortable: true,
              width: 200,
              filter: { xtype: "textfield" },
              header: "PART NAME",
              dataIndex: "part_name",
            },
            {
              sortable: true,
              width: 120,
              filter: { xtype: "textfield" },
              header: "GROUP ITEM",
              dataIndex: "group_item",
            },
            {
              sortable: true,
              width: 120,
              filter: { xtype: "textfield" },
              header: "GROUP ITEM",
              dataIndex: "group_item",
            },
            {
              sortable: true,
              width: 100,
              align: "right",
              header: "QTY",
              dataIndex: "invoice_qty",
              renderer: function (value) {
                var text = Ext.util.Format.number(value, "0,000.00/i");
                return text;
              },
            },
            {
              sortable: true,
              width: 100,
              align: "right",
              header: "RECEIPT",
              dataIndex: "in_qty",
              renderer: function (value) {
                var text = Ext.util.Format.number(value, "0,000.00/i");
                return text;
              },
            },
            {
              sortable: true,
              width: 100,
              align: "right",
              header: "SISA",
              dataIndex: "sisa_qty",
              renderer: function (value) {
                var text = Ext.util.Format.number(value, "0,000.00/i");
                return text;
              },
            },
            {
              sortable: true,
              width: 100,
              align: "right",
              tdCls: "gridrow-kuning",
              renderer: function (value) {
                var text = Ext.util.Format.number(value, "0,000.00/i");
                return text;
              },
              header: "INPUT",
              dataIndex: "input_qty",
              editor: {
                xtype: "numberfield",
                margin: "0 0 0 0",
                name: "input_qty",
                allowBlank: false,
                minValue: 0,
                hideTrigger: true,
              },
            },
            //{ sortable: true, flex: 1, header: "GROUP ITEM/PART", dataIndex: "GROUP_ITEM" },
            { sortable: true, width: 10 },
          ],
          listeners: {
            edit: function (cmp, e) {
              try {
                var val = e.record.data;
                switch (e.field) {
                  case "input_qty":
                    if (val.sisa_qty < val.input_qty) {
                      COMP.TipToast.toast(
                        "Error",
                        "Nilai input tidak bisa melebihi Sisa Qty",
                        { cls: "danger", delay: 2000 }
                      );
                      e.record.reject();
                    } else {
                      e.record.commit();
                    }
                    break;
                  default:
                    e.record.commit();
                    break;
                }
              } catch (ex) {
                COMP.TipToast.toast("Error", ex.message, {
                  cls: "danger",
                  delay: 2000,
                });
              }
            },
          },
          bbar: [
            //
            { xtype: "tbspacer", height: 20 },
          ],
          tbar: [
            { xtype: "tbspacer", width: 5 },
            {
              xtype: "button",
              text: "Set Auto Qty",
              pid: "btauto",
              icon: vconfig.getstyle + "icon/bulb.ico",
              tooltip: "Input Qty sesuai dengan sisa Qty",
              cls: "fontblack-button",
              handler: function (cmp) {
                try {
                  var FRM = cmp.up("window");
                  var GRID = FRM.query(
                    "grid[pid=GRIDFRMwh_receiving_label]"
                  )[0];
                  GRID.getStore()
                    .getDataSource()
                    .each(function (rec) {
                      rec.set("input_qty", rec.data.sisa_qty);
                    });
                  GRID.getStore().commitChanges();
                } catch (ex) {
                  COMP.TipToast.toast("Error", ex.message, {
                    cls: "danger",
                    delay: 2000,
                  });
                }
              },
            },
            "->",
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
        //
        { xtype: "tbspacer", width: 5 },
        {
          xtype: "button",
          text: "Simpan Proses Penerimaan",
          pid: "btsave",
          icon: vconfig.getstyle + "icon/save.png",
          tooltip: "Save Data",
          cls: "fontblack-button",
          handler: function (btn) {
            var me = btn.up("window");
            me.handler_btsave_data(btn);
          },
        },
        {
          xtype: "button",
          text: "Hapus/Cancel Penerimaan",
          pid: "btcancel",
          icon: vconfig.getstyle + "icon/delete.ico",
          tooltip: "Hapus/Cancel Penerimaan",
          cls: "fontblack-button",
          handler: function (btn) {
            var me = btn.up("window");
            me.handler_btdelete_data(btn);
          },
        },
       
      ],
    },
  ],
  //========================================================
  //property handler
  //========================================================
  btsearch_aju_click: function (cmp) {
    try {
      FRMmain = cmp.up("window");
      var FRM = FRMmain.query("form")[0];
      var popup = Ext.create(
        "NJC.wh_receiving_label.FRMwh_receiving_label_list_aju",
        {}
      );
      var GRIDwh_receiving_label = FRMmain.query(
        "grid[pid=GRIDFRMwh_receiving_label]"
      )[0];
      var GRIDwh_receivingpart_list_aju = popup.query(
        "grid[pid=GRIDwh_receivingpart_list_aju]"
      )[0];
      GRIDwh_receivingpart_list_aju.on(
        "itemdblclick",
        function (xgrid, rec) {
          try {
            var FIL_NOMOR_DAFTAR = rec.data.nomor_daftar ?? "";
            var FIL_KODE_INTERNAL = rec.data.supplier_kode_internal ?? "";

            if (FIL_NOMOR_DAFTAR === "") {
              COMP.TipToast.toast(
                "Error",
                "Nomor Daftar tidak ada, proses tidak bisa dilanjutkan",
                { cls: "danger", delay: 2000 }
              );
              return false;
            }
            if (FIL_KODE_INTERNAL === "") {
              COMP.TipToast.toast(
                "Error",
                "Kode Vendor/Supplier tidak ada, proses tidak bisa dilanjutkan",
                { cls: "danger", delay: 2000 }
              );
              return false;
            }
            FRM.getForm().setValues({
              nomor_aju: rec.data.nomor_aju,
              tanggal_aju: rec.data.tanggal_aju,
              nomor_daftar: rec.data.nomor_daftar,
              tanggal_daftar: rec.data.tanggal_daftar,
              kode_dokumen_pabean: rec.data.kode_dokumen_pabean,
              mode_source: rec.data.mode_source,
              supplier_kode_internal: rec.data.supplier_kode_internal,
              nama: rec.data.nama,
            });
            popup.close();

            GRIDwh_receiving_label.getStore().load();
          } catch (ex) {
            COMP.TipToast.toast("Error", ex.message, {
              cls: "danger",
              delay: 2000,
            });
          }
        },
        this
      );
      GRIDwh_receiving_label.getStore().on(
        "beforeload",
        function (store, operation, eOpts) {
          try {
            var dtval = FRM.getValues(false, false, false, true);

            operation.setParams({
              method: "receiving_edit_item",
              vdata: Ext.encode(dtval),
            });
          } catch (ex) {
            COMP.TipToast.toast("Error", ex.message, {
              cls: "danger",
              delay: 2000,
            });
          }
        },
        this
      );
      return popup.show();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  handler_btsave_data: function (btn) {
    try {
      var PAGEthis = btn.up("window");
      var MODULEmain = PAGEthis.MODULEmain;
      var GRIDwh_receiving_label = MODULEmain.query(
        "grid[pid=GRIDwh_receiving_label]"
      )[0];
      var GRIDFRMwh_receiving_label = PAGEthis.query(
        "grid[pid=GRIDFRMwh_receiving_label]"
      )[0];

      var FRM = PAGEthis.query("form")[0];
      var dtval = FRM.getValues(false, false, false, true);

      var nvdetail = [];
      GRIDFRMwh_receiving_label.getStore().each(function (record) {
        nvdetail.push(record.getData());
      });

      Ext.MessageBox.confirm(
        "Konfirmasi",
        "Konfirmasi Simpan Data",
        function (button) {
          if (button === "yes") {
            var params = Ext.encode({
              method: "save_data",
              vdata: Ext.encode(dtval),
              vdetail: Ext.encode(nvdetail),
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
  handler_btdelete_data: function (btn) {
    try {
      var PAGEthis = btn.up("window");
      var MODULEmain = PAGEthis.MODULEmain;
      var GRIDwh_receiving_label = MODULEmain.query(
        "grid[pid=GRIDwh_receiving_label]"
      )[0];

      var FRM = PAGEthis.query("form")[0];
      var dtval = FRM.getValues(false, false, false, true);
      if (Ext.isEmpty(dtval.id)) {
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
                COMP.TipToast.msgbox("Success", val.message, {
                  cls: "success",
                  delay: 3000,
                });
                PAGEthis.close();
                GRIDwh_receiving_label.getStore().load();
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
