Ext.define("NJC.rpt_material.rpt_material", {
  extend: "Ext.form.Panel",
  alias: "widget.rpt_material",
  reference: "rpt_material",
  config: {},
  requires: [
    //
    "NJC.rpt_material.GRIDrpt_material",
    "NJC.rpt_material.Crpt_material",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Crpt_material",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelrpt_material",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDrpt_material" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
