Ext.define("NJC.cp_usergroup.cp_usergroup", {
  extend: "Ext.form.Panel",
  alias: "widget.cp_usergroup",
  reference: "cp_usergroup",
  config: {},
  requires: [
    //
    "NJC.cp_usergroup.Ccp_usergroup",
    "NJC.cp_usergroup.FRMcp_usergroup",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Ccp_usergroup",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panel_cp_usergroup",
      layout: "card",
      frame: false,
      border: false,
      items: [
        //
        { xtype: "FRMcp_usergroup" },
      ],
    });

    this.callParent(arguments);
  },
});
