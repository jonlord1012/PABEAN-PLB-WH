Ext.define("NJC.wh_returnitem.wh_returnitem", {
  extend: "Ext.form.Panel",
  alias: "widget.wh_returnitem",
  reference: "wh_returnitem",
  config: {},
  requires: [
    //
    "NJC.wh_returnitem.GRIDwh_returnitem",
    "NJC.wh_returnitem.Cwh_returnitem",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cwh_returnitem",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelwh_returnitem",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDwh_returnitem" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
