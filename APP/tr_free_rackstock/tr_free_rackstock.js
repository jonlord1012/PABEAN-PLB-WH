Ext.define("NJC.tr_free_rackstock.tr_free_rackstock", {
  extend: "Ext.form.Panel",
  alias: "widget.tr_free_rackstock",
  reference: "tr_free_rackstock",
  config: {},
  requires: [
    //
    "NJC.tr_free_rackstock.GRIDtr_free_rackstock",
    "NJC.tr_free_rackstock.Ctr_free_rackstock",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Ctr_free_rackstock",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "paneltr_free_rackstock",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDtr_free_rackstock" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
