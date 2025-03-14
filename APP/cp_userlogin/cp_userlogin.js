Ext.define("NJC.cp_userlogin.cp_userlogin", {
  extend: "Ext.form.Panel",
  alias: "widget.cp_userlogin",
  reference: "cp_userlogin",
  config: {},
  requires: [
    //
    "NJC.cp_userlogin.GRIDcp_userlogin",
    "NJC.cp_userlogin.Ccp_userlogin",
    "NJC.cp_userlogin.FRMcp_userlogin",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Ccp_userlogin",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panel_cp_userlogin",
      layout: "card",
      frame: false,
      border: false,
      items: [
        //
        { xtype: "GRIDcp_userlogin" },
        // { xtype: "FRMcp_userlogin" },
      ],
    });

    this.callParent(arguments);
  },
});
