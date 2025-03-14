Ext.define("NJC.rpt_inc_material.rpt_inc_material", {
  extend: "Ext.form.Panel",
  alias: "widget.rpt_inc_material",
  reference: "rpt_inc_material",
  config: {},
  requires: [
    //
    "NJC.rpt_inc_material.GRIDrpt_inc_material",
    "NJC.rpt_inc_material.Crpt_inc_material",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Crpt_inc_material",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelrpt_inc_material",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDrpt_inc_material" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
