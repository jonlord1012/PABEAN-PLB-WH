Ext.define("NJC.out_returnitem.out_returnitem", {
  extend: "Ext.form.Panel",
  alias: "widget.out_returnitem",
  reference: "out_returnitem",
  config: {},
  requires: [
    //
    "NJC.out_returnitem.GRIDout_returnitem",
    "NJC.out_returnitem.Cout_returnitem",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cout_returnitem",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelout_returnitem",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDout_returnitem" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
