var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
Ext.define("NJC.sto_periode.GRIDsto_periode", {
  extend: "Ext.panel.Panel",
  alias: "widget.GRIDsto_periode",
  reference: "GRIDsto_periode",
  viewConfig: {
    enableTextSelection: true,
  },
  layout: { type: "vbox", pack: "start", align: "stretch" },
  items: [
    {
      xtype: "grid",
      pid: "GRIDsto_periode",
      emptyText: "No records",
      border: true,
      flex: 1,
      plugins: [
        "filterfield",
      ],
      store: {
        autoLoad: true,
        remoteSort: true,
        remoteFilter: true,
        pageSize: 10,
        proxy: {
          type: "ajax",
          disableCaching: false,
          noCache: false,
          headers: {
            Authorization: "Bearer " + localStorage.getItem("NJC_JWT"),
          },
          actionMethods: { read: "POST" },
          url: vconfig.service_api + "sto_periode/sto_periodes",
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
              var NJC_PROFILE = localStorage.getItem("NJC_PROFILE");
              var vprofile = Ext.decode(NJC_PROFILE)[0];
              store.getFilters().each(function (filter) {
                if (filter.getProperty() === "tgl_mulai" && filter.getValue() instanceof Date) {
                  filter.setValue(Ext.Date.format(filter.getValue(), "Y-m-d"));
                }
                if (filter.getProperty() === "tgl_selesai" && filter.getValue() instanceof Date) {
                  filter.setValue(Ext.Date.format(filter.getValue(), "Y-m-d"));
                }
                if (filter.getProperty() === "syscreatedate" && filter.getValue() instanceof Date) {
                  filter.setValue(Ext.Date.format(filter.getValue(), "Y-m-d"));
                }
                if (filter.getProperty() === "sysupdatedate" && filter.getValue() instanceof Date) {
                  filter.setValue(Ext.Date.format(filter.getValue(), "Y-m-d"));
                }
              });
              operation.setParams({
                method: "read_data",
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
          sortable: true,
          width: 100,
          filter: { xtype: "textfield" },
          menuDisabled: true,
        },
        items: [
          { xtype: "rownumberer", width: 35, filter: false },
          { header: "id", dataIndex: "id", hidden: true },
          {
            xtype: "actioncolumn",
            width: 35,
            align: "center",
            menuDisabled: true,
            sortable: false,
            filter: false,
            items: [
              {
                icon: vconfig.getstyle + "icon/grid.png",
                handler: "GRIDsto_periode_click",
                tooltip: "Detail Dokumen",
              },
            ],
          },
          { header: "PERIODE", dataIndex: "period", flex: 1 },
          {
            header: "TANGGAL MULAI",
            dataIndex: "tgl_mulai",
            width: 140,
            filter: { xtype: "datefield", format: "d - F - Y", submitFormat: "Y-m-d" },
            renderer: function (value) {
              var text = value === null ? "" : moment(value).format("DD-MMM-YYYY");
              return text;
            },
          },
          {
            header: "TANGGAL SELESAI",
            dataIndex: "tgl_selesai",
            width: 140,
            filter: { xtype: "datefield", format: "d - F - Y", submitFormat: "Y-m-d" },
            renderer: function (value) {
              var text = value === null ? "" : moment(value).format("DD-MMM-YYYY");
              return text;
            },
          },
          { header: "STATUS", dataIndex: "status", width: 100 },
          { header: "CREATE", dataIndex: "create_user", width: 100 },
          { header: "DATE", dataIndex: "create_date", width: 150 },
          { header: "UPDATE", dataIndex: "update_user", width: 100 },
          { header: "DATE", dataIndex: "update_date", width: 150 },
        ],
      },
      bbar: {
        xtype: "pagingtoolbar",
        displayInfo: true,
        displayMsg: "Displaying topics {0} - {1} of {2}",
        emptyMsg: "No topics to display",
      },
    },
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
        },
        {
          xtype: "button",
          text: "New Input",
          pid: "btinput_periode",
          icon: vconfig.getstyle + "icon/add.png",
          tooltip: "New Input",
          handler: "btinput_periode_click"
        },
        "->",
        {
          xtype: "button",
          text: "Download Excel",
          pid: "btdownload_excel",
          icon: vconfig.getstyle + "icon/excel.ico",
          handler: function (btn) {
            var me = btn.up("panel");
            me.handler_btdownload_excel(btn);
          },
        },
      ],
    },
  ],
  //========================================================
  //property handler
  //========================================================
  handler_btdownload_excel: function (btn) {
    try {
      Ext.MessageBox.show(
        {
          title: "Konfirmasi Download",
          width: mainpanel.getWidth() * 0.3,
          height: mainpanel.getHeight() * 0.2,
          msg: [
            //
            "<ul>",
            "<li>Konfirmasi proses download Data Periode</li>",
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
                method: "download_data",
              });
              var hasil = COMP.run.getservice(vconfig.service_api + "sto_periode/sto_periode", params, "POST", localStorage.getItem("NJC_JWT"));
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
});
