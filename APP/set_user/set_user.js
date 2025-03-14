Ext.define("TDK.set_user.set_user", {
  extend: "Ext.form.Panel",
  alias: "widget.set_user",
  reference: "set_user",
  config: {},
  requires: [
    //
    "TDK.set_user.GRIDset_user",
    "TDK.set_user.Cset_user",
    "TDK.set_user.FRMset_user",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cset_user",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panel_set_user",
      layout: "card",
      frame: false,
      border: false,
      items: [
        //
        { xtype: "GRIDset_user" },
        // { xtype: "FRMset_user" },
      ],
    });

    this.callParent(arguments);
  },
});
