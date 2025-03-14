Ext.define("NJC.cp_userlogin.GRIDcp_userlogin", {
  extend: "Ext.form.Panel",
  alias: "widget.GRIDcp_userlogin",
  reference: "GRIDcp_userlogin",
  frame: false,
  border: false,
  layout: { type: "vbox", pack: "start", align: "stretch" },
  requires: [],
  items: [
    {
      xtype: "grid",
      pid: "GRIDcp_userlogin",
      emptyText: "No Matching Records",
      autoScroll: true,
      border: false,
      frame: false,
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
          headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
          actionMethods: { read: "POST" },
          url: vconfig.service_api + "cp_userlogin/cp_userlogins",
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

      plugins: ["filterfield"],
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
                handler: "btrow_itemclick",
                tooltip: "Data Details",
              },
            ],
          },
          { width: 50, header: "ID", dataIndex: "USERID", hidden: true },
          { width: 110, header: "GROUP", dataIndex: "USERGROUP" },
          { width: 100, header: "USERLOGIN", dataIndex: "USERLOGIN" },
          { width: 200, header: "NAME", dataIndex: "USERNAME" },
          // { width: 80, header: "DEPTCODE", dataIndex: "DEPTCODE" },
          // { width: 200, header: "DEPARTMENT", dataIndex: "DEPTNAME" },
          { width: 200, header: "EMAIL", dataIndex: "USEREMAIL" },
          { width: 65, header: "AKTIF", dataIndex: "USERACTIVE" },
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
      height: 30,
      dock: "top",
      items: [
        "-", //
        { xtype: "tbspacer", width: 5 },
        { xtype: "button", text: "Refresh", pid: "btrefresh", icon: vconfig.getstyle + "icon/update.ico", tooltip: "Refresh Data", handler: "btrefresh_click" },
        { xtype: "button", pid: "btnew", text: "User Baru", icon: vconfig.getstyle + "icon/add.png", tooltip: "Create Term Baru", handler: "btnew_click" },
        "->",
        {
          xtype: "button_download",
          pid: "btdownload",
          nvdata: {
            modelpath: "cp_userlogin/cp_userlogin",
            method: "download_file",
            title: "Download User Login",
            grid_pid: "GRIDcp_userlogin",
          },
        },
      ],
      // other options....
    },
  ],
});
