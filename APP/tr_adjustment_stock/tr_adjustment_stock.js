Ext.define("NJC.tr_adjustment_stock.tr_adjustment_stock", {
  extend: "Ext.form.Panel",
  alias: "widget.tr_adjustment_stock",
  reference: "tr_adjustment_stock",
  config: {},
  requires: [
    //
    "NJC.tr_adjustment_stock.GRIDtr_adjustment_stock",
    "NJC.tr_adjustment_stock.Ctr_adjustment_stock",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Ctr_adjustment_stock",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "paneltr_adjustment_stock",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDtr_adjustment_stock" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
