(function(){var b=/\[\[[^\]]+\]\]/g;CKEDITOR.plugins.add("placeholder",{requires:["dialog"],lang:["bg","cs","cy","da","de","el","en","eo","et","fa","fi","fr","he","hr","it","nb","nl","no","pl","tr","ug","uk","vi","zh-cn"],init:function(a){var d=a.lang.placeholder;a.addCommand("createplaceholder",new CKEDITOR.dialogCommand("createplaceholder"));a.addCommand("editplaceholder",new CKEDITOR.dialogCommand("editplaceholder"));a.ui.addButton("CreatePlaceholder",{label:d.toolbar,command:"createplaceholder",icon:this.path+"placeholder.gif"});if(a.addMenuItems){a.addMenuGroup("placeholder",20);a.addMenuItems({editplaceholder:{label:d.edit,command:"editplaceholder",group:"placeholder",order:1,icon:this.path+"placeholder.gif"}});if(a.contextMenu){a.contextMenu.addListener(function(f,c){if(!f||!f.data("cke-placeholder")){return null}return{editplaceholder:CKEDITOR.TRISTATE_OFF}})}}a.on("doubleclick",function(c){if(CKEDITOR.plugins.placeholder.getSelectedPlaceHoder(a)){c.data.dialog="editplaceholder"}});a.addCss(".cke_placeholder{background-color: #ffff00;"+(CKEDITOR.env.gecko?"cursor: default;":"")+"}");a.on("contentDom",function(){a.document.getBody().on("resizestart",function(c){if(a.getSelection().getSelectedElement().data("cke-placeholder")){c.data.preventDefault()}})});CKEDITOR.dialog.add("createplaceholder",this.path+"dialogs/placeholder.js");CKEDITOR.dialog.add("editplaceholder",this.path+"dialogs/placeholder.js")},afterInit:function(a){var h=a.dataProcessor,g=h&&h.dataFilter,f=h&&h.htmlFilter;if(g){g.addRules({text:function(c){return c.replace(b,function(d){return CKEDITOR.plugins.placeholder.createPlaceholder(a,null,d,1)})}})}if(f){f.addRules({elements:{span:function(c){if(c.attributes&&c.attributes["data-cke-placeholder"]){delete c.name}}}})}}})})();CKEDITOR.plugins.placeholder={createPlaceholder:function(g,f,j,i){var h=new CKEDITOR.dom.element("span",g.document);h.setAttributes({contentEditable:"false","data-cke-placeholder":1,"class":"cke_placeholder"});j&&h.setText(j);if(i){return h.getOuterHtml()}if(f){if(CKEDITOR.env.ie){h.insertAfter(f);setTimeout(function(){f.remove();h.focus()},10)}else{h.replace(f)}}else{g.insertElement(h)}return null},getSelectedPlaceHoder:function(e){var d=e.getSelection().getRanges()[0];d.shrink(CKEDITOR.SHRINK_TEXT);var f=d.startContainer;while(f&&!(f.type==CKEDITOR.NODE_ELEMENT&&f.data("cke-placeholder"))){f=f.getParent()}return f}};