Ext.define("NJC.out_outbound.out_outbound", {
  extend: "Ext.form.Panel",
  alias: "widget.out_outbound",
  reference: "out_outbound",
  config: {},
  requires: [
    //
    "NJC.out_outbound.GRIDout_outbound",
    "NJC.out_outbound.Cout_outbound",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cout_outbound",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelout_outbound",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDout_outbound" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
