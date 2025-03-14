Ext.define("NJC.tr_picking_rackstock.tr_picking_rackstock", {
  extend: "Ext.form.Panel",
  alias: "widget.tr_picking_rackstock",
  reference: "tr_picking_rackstock",
  config: {},
  requires: [
    //
    "NJC.tr_picking_rackstock.GRIDtr_picking_rackstock",
    "NJC.tr_picking_rackstock.Ctr_picking_rackstock",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Ctr_picking_rackstock",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "paneltr_picking_rackstock",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDtr_picking_rackstock" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
