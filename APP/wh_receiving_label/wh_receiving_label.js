Ext.define("NJC.wh_receiving_label.wh_receiving_label", {
  extend: "Ext.form.Panel",
  alias: "widget.wh_receiving_label",
  reference: "wh_receiving_label",
  config: {},
  requires: [
    //
    "NJC.wh_receiving_label.GRIDwh_receiving_label",
    "NJC.wh_receiving_label.Cwh_receiving_label",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cwh_receiving_label",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelwh_receiving_label",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDwh_receiving_label" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
