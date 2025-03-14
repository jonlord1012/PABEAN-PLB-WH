Ext.define("TDK.set_usergroup.set_usergroup", {
  extend: "Ext.form.Panel",
  alias: "widget.set_usergroup",
  reference: "set_usergroup",
  config: {},
  requires: [
    //
    "TDK.set_usergroup.Cset_usergroup",
    "TDK.set_usergroup.FRMset_usergroup",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cset_usergroup",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panel_set_usergroup",
      layout: "card",
      frame: false,
      border: false,
      items: [
        //
        { xtype: "FRMset_usergroup" },
      ],
    });

    this.callParent(arguments);
  },
});
