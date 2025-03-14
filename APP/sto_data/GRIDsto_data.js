var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
Ext.define("NJC.sto_data.GRIDsto_data", {
  extend: "Ext.form.Panel",
  alias: "widget.GRIDsto_data",
  reference: "GRIDsto_data",
  frame: false,
  flex: 1,
  layout: {
    type: "vbox",
    pack: "start",
    align: "stretch",
  },
  fieldDefaults: { labelAlign: "right", labelWidth: 100 },
  default: {
    border: false,
  },
  items: [
    {
      xtype: "form",
      pid: "formIfo",
      fieldDefaults: {
        labelWidth: 100,
        margin: "5 10 10 0",
        xtype: "textfield",
        readOnly: true,
        fieldCls: "fieldinput",
        xtype: "textfield",
        fieldCls: "fieldlock",
      },
      items: [
        {
          xtype: "fieldset",
          title: "Stock Opname Summary",
          margin: "3 10 10 10",
          items: [
            {
              xtype: "container",
              layout: "hbox",
              defaults: {
                xtype: "textfield",
                readOnly: true,
                fieldCls: "fieldlock",
              },
              items: [
                {
                  xtype: "textfield",
                  fieldLabel: "Periode Active ",
                  name: "periodeactive",
                },
                {
                  fieldLabel: "Tanggal Mulai ",
                  name: "tgl_mulai",
                  format: "Y-m-d",
                },
                {
                  fieldLabel: "Tanggal Selesai ",
                  name: "tgl_selesai",
                  format: "Y-m-d",
                },
              ],
            },
          ],
        },
      ],
    },
    {
      xtype: "fieldset",
      title: "Stock Opname Data By Rack",
      margin: "3 10 10 10",
      flex: 1,
      layout: {
        type: "vbox",
        align: "stretch"
      },
      items: [
        {
          xtype: "grid",
          pid: "GRIDby_rack",
          flex: 1,
          margin: "0 0 10 0",
          emptyText: "No records",
          plugins: ["filterfield"],
          store: {
            autoLoad: true,
            remoteSort: true,
            remoteFilter: true,
            pageSize: 20,
            proxy: {
              type: "ajax",
              disableCaching: false,
              noCache: false,
              headers: {
                Authorization: "Bearer " + localStorage.getItem("NJC_JWT"),
              },
              actionMethods: { read: "POST" },
              url: vconfig.service_api + "sto_data/sto_datas",
              reader: {
                type: "json",
                rootProperty: "Rows",
                totalProperty: "TotalRows",
                successProperty: "success",
                periodeactive: "periodeactive",
                AssetsScanned: "AssetsScanned",
                AssetsNotScanned: "AssetsNotScanned",
              },
            },
          },
          columns: {
            defaults: {
              filter: { xtype: "textfield" },
              sortable: true,
            },
            items: [
              { xtype: "rownumberer", width: 35, filter: false },
              { header: "RACK NO", dataIndex: "rack_no", width: 100 },
              { header: "RACK LOCATION", dataIndex: "rack_location", width: 150 },
              { header: "RACK CATEGORY", dataIndex: "rack_category", width: 150 },
              { header: "RECEIPT NO", dataIndex: "receipt_no", width: 150 },
              { header: "RECEIPT DATE", dataIndex: "receipt_date", width: 150 },
              { header: "INVOICE NO", dataIndex: "invoice_no", width: 150 },
              { header: "INVOICE DATE", dataIndex: "invoice_date", width: 150 },
              { header: "PART NO", dataIndex: "part_no", width: 150 },
              { header: "MAPP PART NO", dataIndex: "mapp_partno", width: 150 },
              { header: "PART NAME", dataIndex: "part_name", width: 150 },
              { header: "GROUP ITEM", dataIndex: "group_item", width: 150 },
              { header: "INVOICE QTY", dataIndex: "invoice_qty", width: 100 },
              { header: "RECEIPT QTY", dataIndex: "receipt_qty", width: 100 },
              { header: "MENU INPUT", dataIndex: "menu_input", width: 100 },
              { header: "JENIS INPUT", dataIndex: "jenis_input", width: 100 },
              { header: "SUMBER DATA", dataIndex: "sumber_data", width: 150 },
              { header: "BC TYPE", dataIndex: "bc_type", width: 100 },
              { header: "NOMOR AJU", dataIndex: "nomor_aju", width: 150 },
              { header: "TANGGAL AJU", dataIndex: "tanggal_aju", width: 150 },
              { header: "NOMOR DAFTAR", dataIndex: "nomor_daftar", width: 150 },
              { header: "TANGGAL DAFTAR", dataIndex: "tanggal_daftar", width: 150 },
              { header: "SERI BARANG", dataIndex: "seri_barang", width: 100 },
              { header: "CTN QTY", dataIndex: "ctn_qty", width: 100 },
              { header: "PACKING QTY", dataIndex: "packing_qty", width: 100 },
              { header: "BARCODE", dataIndex: "barcode", width: 200 },
              { header: "BARCODE SEQ NO", dataIndex: "barcode_seqno", width: 110 },
              { header: "RACK NO", dataIndex: "rack_no", width: 100 },
              { header: "ACTUAL QTY", dataIndex: "actual_qty", width: 100 },
              { header: "STO QTY", dataIndex: "sto_qty", width: 100 },
              { header: "CREATE", dataIndex: "create_user", width: 150 },
              {
                header: "DATE",
                dataIndex: "create_date",
                width: 120,
                renderer: function (value) {
                  var text = value === null || value === "" ? null : moment(value).format("YYYY-MM-DD HH:mm:ss");
                  return text;
                },
              },
              { header: "UPDATE", dataIndex: "update_user", width: 150 },
              {
                header: "DATE",
                dataIndex: "update_date",
                width: 120,
                renderer: function (value) {
                  var text = value === null || value === "" ? null : moment(value).format("YYYY-MM-DD HH:mm:ss");
                  return text;
                },
              },
            ],
          },
          bbar: {
            xtype: "pagingtoolbar",
            displayInfo: true,
            displayMsg: "Displaying topics {0} - {1} of {2}",
            emptyMsg: "No topics to display",
          },
        },
      ]
    },
    {
      xtype: "container",
      layout: {
        type: "hbox",
        align: "stretch"
      },
      flex: 1,
      items: [
        {
          xtype: "fieldset",
          title: "Stock Opname Data Belum STO",
          margin: "3 5 10 10",
          flex: 1,
          layout: {
            type: "fit"
          },
          items: [
            {
              xtype: "grid",
              pid: "GRIDdata_belum_sto",
              flex: 1,
              margin: "0 0 5 0",
              emptyText: "No records",
              plugins: ["filterfield"],
              store: {
                autoLoad: true,
                remoteSort: true,
                remoteFilter: true,
                pageSize: 20,
                proxy: {
                  type: "ajax",
                  disableCaching: false,
                  noCache: false,
                  headers: {
                    Authorization: "Bearer " + localStorage.getItem("NJC_JWT"),
                  },
                  actionMethods: { read: "POST" },
                  url: vconfig.service_api + "sto_data/sto_datas",
                  reader: {
                    type: "json",
                    rootProperty: "Rows",
                    totalProperty: "TotalRows",
                  },
                },
                listeners: {
                  beforeload: function (store, operation, eOpts) {
                    try {
                      operation.setParams({
                        method: "read_data_belum_sto",
                      });
                    } catch (ex) {
                      COMP.TipToast.msgbox("Error", ex.message, {
                        cls: "danger",
                        delay: 2000,
                      });
                    }
                  },
                },
              },
              columns: {
                defaults: {
                  filter: { xtype: "textfield" },
                  sortable: true,
                },
                items: [
                  { xtype: "rownumberer", width: 35, filter: false },
                  { header: "RECEIPT NO", dataIndex: "receipt_no", width: 150 },
                  { header: "RECEIPT DATE", dataIndex: "receipt_date", width: 150 },
                  { header: "INVOICE NO", dataIndex: "invoice_no", width: 150 },
                  { header: "INVOICE DATE", dataIndex: "invoice_date", width: 150 },
                  { header: "PART NO", dataIndex: "part_no", width: 150 },
                  { header: "MAPP PART NO", dataIndex: "mapp_partno", width: 150 },
                  { header: "PART NAME", dataIndex: "part_name", width: 150 },
                  { header: "GROUP ITEM", dataIndex: "group_item", width: 150 },
                  { header: "INVOICE QTY", dataIndex: "invoice_qty", width: 100 },
                  { header: "RECEIPT QTY", dataIndex: "receipt_qty", width: 100 },
                  { header: "MENU INPUT", dataIndex: "menu_input", width: 100 },
                  { header: "JENIS INPUT", dataIndex: "jenis_input", width: 100 },
                  { header: "SUMBER DATA", dataIndex: "sumber_data", width: 150 },
                  { header: "BC TYPE", dataIndex: "bc_type", width: 100 },
                  { header: "NOMOR AJU", dataIndex: "nomor_aju", width: 150 },
                  { header: "TANGGAL AJU", dataIndex: "tanggal_aju", width: 150 },
                  { header: "NOMOR DAFTAR", dataIndex: "nomor_daftar", width: 150 },
                  { header: "TANGGAL DAFTAR", dataIndex: "tanggal_daftar", width: 150 },
                  { header: "SERI BARANG", dataIndex: "seri_barang", width: 100 },
                  { header: "CTN QTY", dataIndex: "ctn_qty", width: 100 },
                  { header: "PACKING QTY", dataIndex: "packing_qty", width: 100 },
                  { header: "BARCODE", dataIndex: "barcode", width: 200 },
                  { header: "BARCODE SEQ NO", dataIndex: "barcode_seqno", width: 110 },
                  { header: "RACK NO", dataIndex: "rack_no", width: 100 },
                  { header: "ACTUAL QTY", dataIndex: "actual_qty", width: 100 },
                  { header: "STO QTY", dataIndex: "sto_qty", width: 100 },
                  { header: "CREATE", dataIndex: "create_user", width: 150 },
                  {
                    header: "DATE",
                    dataIndex: "create_date",
                    width: 120,
                    renderer: function (value) {
                      var text = value === null || value === "" ? null : moment(value).format("YYYY-MM-DD HH:mm:ss");
                      return text;
                    },
                  },
                  { header: "UPDATE", dataIndex: "update_user", width: 150 },
                  {
                    header: "DATE",
                    dataIndex: "update_date",
                    width: 120,
                    renderer: function (value) {
                      var text = value === null || value === "" ? null : moment(value).format("YYYY-MM-DD HH:mm:ss");
                      return text;
                    },
                  },
                ],
              },
              bbar: {
                xtype: "pagingtoolbar",
                displayInfo: true,
                displayMsg: "Displaying topics {0} - {1} of {2}",
                emptyMsg: "No topics to display",
              },
            },
          ]
        },
        {
          xtype: "fieldset",
          title: "Stock Opname Data QTY Tidak Sesuai",
          margin: "3 10 10 5",
          flex: 1,
          layout: {
            type: "fit",
          },
          items: [
            {
              xtype: "grid",
              pid: "GRIDsto_data_qty",
              flex: 1,
              margin: "0 0 5 0",
              emptyText: "No records",
              plugins: ["filterfield"],
              store: {
                autoLoad: true,
                remoteSort: true,
                remoteFilter: true,
                pageSize: 20,
                proxy: {
                  type: "ajax",
                  disableCaching: false,
                  noCache: false,
                  headers: {
                    Authorization: "Bearer " + localStorage.getItem("NJC_JWT"),
                  },
                  actionMethods: { read: "POST" },
                  url: vconfig.service_api + "sto_data/sto_datas",
                  reader: {
                    type: "json",
                    rootProperty: "Rows",
                    totalProperty: "TotalRows",
                  },
                },
                listeners: {
                  beforeload: function (store, operation, eOpts) {
                    try {
                      operation.setParams({
                        method: "read_data_tidak_sesuai_qty",
                      });
                    } catch (ex) {
                      COMP.TipToast.msgbox("Error", ex.message, {
                        cls: "danger",
                        delay: 2000,
                      });
                    }
                  },
                },
              },
              columns: {
                defaults: {
                  filter: { xtype: "textfield" },
                  sortable: true,
                },
                items: [
                  { xtype: "rownumberer", width: 35, filter: false },
                  { header: "PART NO", dataIndex: "part_no", width: 150 },
                  { header: "ACTUAL QTY", dataIndex: "actual_qty", width: 100 },
                  { header: "STO QTY", dataIndex: "sto_qty", width: 100 },
                  { header: "RECEIPT NO", dataIndex: "receipt_no", width: 150 },
                  { header: "RECEIPT DATE", dataIndex: "receipt_date", width: 150 },
                  { header: "INVOICE NO", dataIndex: "invoice_no", width: 150 },
                  { header: "INVOICE DATE", dataIndex: "invoice_date", width: 150 },
                  { header: "MAPP PART NO", dataIndex: "mapp_partno", width: 150 },
                  { header: "PART NAME", dataIndex: "part_name", width: 150 },
                  { header: "GROUP ITEM", dataIndex: "group_item", width: 150 },
                  { header: "INVOICE QTY", dataIndex: "invoice_qty", width: 100 },
                  { header: "RECEIPT QTY", dataIndex: "receipt_qty", width: 100 },
                  { header: "MENU INPUT", dataIndex: "menu_input", width: 100 },
                  { header: "JENIS INPUT", dataIndex: "jenis_input", width: 100 },
                  { header: "SUMBER DATA", dataIndex: "sumber_data", width: 150 },
                  { header: "BC TYPE", dataIndex: "bc_type", width: 100 },
                  { header: "NOMOR AJU", dataIndex: "nomor_aju", width: 150 },
                  { header: "TANGGAL AJU", dataIndex: "tanggal_aju", width: 150 },
                  { header: "NOMOR DAFTAR", dataIndex: "nomor_daftar", width: 150 },
                  { header: "TANGGAL DAFTAR", dataIndex: "tanggal_daftar", width: 150 },
                  { header: "SERI BARANG", dataIndex: "seri_barang", width: 100 },
                  { header: "CTN QTY", dataIndex: "ctn_qty", width: 100 },
                  { header: "PACKING QTY", dataIndex: "packing_qty", width: 100 },
                  { header: "BARCODE", dataIndex: "barcode", width: 200 },
                  { header: "BARCODE SEQ NO", dataIndex: "barcode_seqno", width: 110 },
                  { header: "RACK NO", dataIndex: "rack_no", width: 100 },
                  { header: "CREATE", dataIndex: "create_user", width: 150 },
                  {
                    header: "DATE",
                    dataIndex: "create_date",
                    width: 120,
                    renderer: function (value) {
                      var text = value === null || value === "" ? null : moment(value).format("YYYY-MM-DD HH:mm:ss");
                      return text;
                    },
                  },
                  { header: "UPDATE", dataIndex: "update_user", width: 150 },
                  {
                    header: "DATE",
                    dataIndex: "update_date",
                    width: 120,
                    renderer: function (value) {
                      var text = value === null || value === "" ? null : moment(value).format("YYYY-MM-DD HH:mm:ss");
                      return text;
                    },
                  },
                ],
              },
              bbar: {
                xtype: "pagingtoolbar",
                displayInfo: true,
                displayMsg: "Displaying topics {0} - {1} of {2}",
                emptyMsg: "No topics to display",
              },
            },
          ]
        }
      ]
    }
  ],
  dockedItems: [
    {
      xtype: "toolbar",
      dock: "top",
      items: [
        { xtype: "tbspacer", width: 5 },
        {
          xtype: "button",
          text: "Refresh",
          pid: "btrefresh",
          handler: "btrefresh_click",
          icon: vconfig.getstyle + "icon/update.ico",
          tooltip: "Refresh Data",
        }, "->",
        {
          xtype: "button",
          text: "Download Excel",
          pid: "btdownload_excel",
          icon: vconfig.getstyle + "icon/excel.ico",
          handler: function (btn) {
            var me = btn.up("form");
            me.handler_btdownload_excel(btn);
          },
        },
      ],
    },
  ],
  handler_btdownload_excel: function (btn) {
    try {
      var GRIDpanel_sto_data = btn.up("form");
      var FRM = GRIDpanel_sto_data.query("form")[0];
      var vdata = FRM.getValues(false, false, false, true);
      Ext.MessageBox.show(
        {
          title: "Konfirmasi Download",
          width: mainpanel.getWidth() * 0.3,
          height: mainpanel.getHeight() * 0.2,
          msg: [
            //
            "<ul>",
            "<li>Konfirmasi proses download STO Data</li>",
            "</ul>",
          ].join(""),
          buttons: Ext.MessageBox.YESNO,
          animateTarget: btn,
          buttonText: {
            yes: "Cancel Proses",
            no: "Proses Download",
          },
          scope: this,
          fn: function (btconfirm) {
            if (btconfirm === "no") {
              var params = Ext.encode({
                method: "download_report_data",
                vdata: Ext.encode(vdata),
              });
              var hasil = COMP.run.getservice(vconfig.service_api + "sto_data/sto_data", params, "POST", localStorage.getItem("NJC_JWT"));
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
        },
        this
      );
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },

  listeners: {
    afterrender: function (cmp) {
      var FILEmain = cmp.up("sto_data");
      FILEmain.getController().renderform(FILEmain);
    },
  },
});