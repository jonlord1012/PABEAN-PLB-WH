Ext.define("NJC.tr_floor_rackstock.tr_floor_rackstock", {
  extend: "Ext.form.Panel",
  alias: "widget.tr_floor_rackstock",
  reference: "tr_floor_rackstock",
  config: {},
  requires: [
    //
    "NJC.tr_floor_rackstock.GRIDtr_floor_rackstock",
    "NJC.tr_floor_rackstock.Ctr_floor_rackstock",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Ctr_floor_rackstock",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "paneltr_floor_rackstock",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDtr_floor_rackstock" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
