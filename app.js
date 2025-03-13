Ext.onReady(function () {
  Ext.Loader.setConfig({ enabled: true });
  Ext.Loader.setPath("COMP", "js/component");
  Ext.Loader.setPath("APP_PATH", "APP");
  Ext.Loader.setPath("Ext.ux", "js/ux");

  var currentDomain = window.location.protocol + "//" + window.location.host;

  Ext.define("vconfig", {
    singleton: true,
    LOG: {},
    verify: {},
    setting: {},
    getstyle: Ext.getWin().dom.location.href + "style/",
    basepath: Ext.getWin().dom.location.href,

    base_url: currentDomain,
    download_url: currentDomain + "z_download",
    image_url: currentDomain + "/plb_warehouse/document/images/",
    service_main: currentDomain + "/plb_warehouse/apservice/",
    service_url: currentDomain + "/plb_warehouse/apservice/auth",
    service_api: currentDomain + "/plb_warehouse/apservice/",
  });

  Ext.application({
    name: "NJC",
    appFolder: "APP",
    autoCreateViewport: "NJC.mainpage",
    requires: ["vconfig"],
    launch: function () {
      Ext.Ajax.on("requestexception", function (conn, response, options, eOpts) {
        if (response.status === 401 || response.status === 400) {
          Ext.Msg.alert("Session Expired", "Your session has expired. Please login again.", function () {
            localStorage.clear();
            window.location.href = "/plb_warehouse";
          });
        }
      });
    },
  });
});
