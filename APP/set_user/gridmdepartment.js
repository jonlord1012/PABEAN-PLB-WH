Ext.define("TDK.set_user.GRIDmdepartment", {
  extend: "Ext.form.Panel",
  alias: "widget.GRIDmdepartment",
  reference: "GRIDmdepartment",
  frame: false,
  border: true,
  layout: { type: "vbox", pack: "start", align: "stretch" },
  requires: [],
  fieldDefaults: {
    labelAlign: "left",
    labelWidth: 90,
    margin: "0 10 5 0",
  },
  items: [
    {
      xtype: "grid",
      pid: "GRIDmdepartment",
      emptyText: "No Matching Records",
      plugins: ["filterfield"],
      autoScroll: true,
      flex: 1,
      store: {
        autoLoad: true,
        remoteSort: true,
        remoteFilter: true,
        pageSize: 20,
        proxy: {
          type: "ajax",
          disableCaching: false,
          noCache: false,
          headers: { Authorization: "Bearer " + localStorage.getItem("TDK_JWT") },
          actionMethods: { read: "POST" },
          url: vconfig.service_api + "mdepartment/mdepartments",
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
      viewConfig: {
        enableTextSelection: true,
      },
      columns: {
        defaults: {
          sortable: true,
          filter: { xtype: "textfield" },
        },
        items: [
          { xtype: "rownumberer", width: 50, filter: false },
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
                handler: "btdetail_rows_click",
                tooltip: "Edit Data",
              },
            ],
          },
          { header: "CODE", dataIndex: "defcode", width: 100 },
          { header: "DEPARTMENT", dataIndex: "defname", flex: 1 },
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
        //
        { xtype: "tbspacer", width: 5 },
        { xtype: "button", text: "Refresh", pid: "btrefresh", icon: vconfig.getstyle + "icon/update.ico", tooltip: "Refresh Data" },
        "-",
        { xtype: "button", text: "New Input", pid: "btnew", icon: vconfig.getstyle + "icon/add.png", tooltip: "New Input" },
        "->",
        {
          xtype: "button_download",
          pid: "btdownload",
          nvdata: {
            modelpath: "mdepartment/mdepartment",
            method: "download_file",
            title: "Download Department",
            grid_pid: "GRIDmdepartment",
          },
        },
      ],
      // other options....
    },
  ],
});
