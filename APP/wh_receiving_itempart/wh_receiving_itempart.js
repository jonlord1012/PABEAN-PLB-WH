Ext.define("NJC.wh_receiving_itempart.wh_receiving_itempart", {
  extend: "Ext.form.Panel",
  alias: "widget.wh_receiving_itempart",
  reference: "wh_receiving_itempart",
  config: {},
  requires: [
    //
    "NJC.wh_receiving_itempart.GRIDwh_receiving_itempart",
    "NJC.wh_receiving_itempart.Cwh_receiving_itempart",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cwh_receiving_itempart",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelwh_receiving_itempart",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDwh_receiving_itempart" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
