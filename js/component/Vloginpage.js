Ext.define('COMP.Vloginpage', {
    extend: 'Ext.form.Panel',
    alias:'widget.Vloginpage',
    reference:'Vloginpage',
    layout: 'fit',
    
    items: {
        frame:false,
        border:false,
        layout: 'center',
        items:[
                    {
                        title: 'Login Application',
                        frame: true,
                        bodyPadding: 10,
                        items: [
                                {
                                    xtype:'displayfield',name: 'UserModule',fieldLabel: 'Module',value:'',allowBlank: false,
                                    labelAlign:'left',emptyText: 'user Login'
                                },
                                {
                                    xtype:'textfield',name: 'UserLogin',fieldLabel: 'User Login',allowBlank: false,
                                    labelAlign:'left',emptyText: 'user Login'
                                }, {
                                    xtype:'textfield',name: 'UserPassword',fieldLabel: 'Password',allowBlank: false,
                                    labelAlign:'left',emptyText: 'password',inputType: 'password'
                                }],

                        buttons: [
                            { text:'Login' ,pid:'btlogin' },
                            { text:'Menu Utama' ,pid:'bthome' }
                        ]
                    }
        ]
    }

});