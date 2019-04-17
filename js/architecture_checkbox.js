var archForm, archs, rendered_archs=[];

function loadScript( url, callback ) {
  var script = document.createElement( "script" )
  script.type = "text/javascript";
  if(script.readyState) {  //IE
    script.onreadystatechange = function() {
      if ( script.readyState === "loaded" || script.readyState === "complete" ) {
        script.onreadystatechange = null;
        callback();
      }
    };
  } else {  //Others
    script.onload = function() {
      callback();
    };
  }

  script.src = url;
  document.getElementsByTagName( "head" )[0].appendChild( script );
}


$(document).ready(function(){
  loadScript("//code.cloudcms.com/alpaca/1.5.24/bootstrap/alpaca.min.js", function(){
    loadScript("https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js",function(){

      function render_architecture(arch)
      {
        rendered_archs.push( {id: arch['id'], text: arch['name']});
      }
      archs = <?php echo $architectures?>;
      archs.forEach(function(element) {
        render_architecture(element);
      });

      $("#architecture_checkbox").alpaca({
        "data": {
          "hidememberinfo": "all"
        },
        "schema": {
          "type": "object",
          "title": "<?php echo $this->msg('pmatree-architecture-header')?>",
          "properties": {
            "hidememberinfo": {
              "enum": ["all", "several"]
            },
            "level": {
              "type": "string",
              enum: rendered_archs.map(function(e){ return e.id;})
            },
            "hidden_archs": {
            }
          },
          "dependencies": {
            "level": ["hidememberinfo"]
          }
        },
        "options": {
          "fields": {
            "hidememberinfo": {
              "type": "radio",
              "optionLabels": ["<?php echo $this->msg('pmatree-architecture-all')?>", "<?php echo $this->msg('pmatree-architecture-choose')?>"],
              "removeDefaultNone": true,
              "vertical": false
            },
            "level": {
              "type": "checkbox",
              "hideNone": "true",
              optionLabels: rendered_archs.map(function(e){ return e.text;}),
              "dependencies": {
                "hidememberinfo": "several"
              }
            },
            "hidden_archs": {
              "type": "hidden"
            }
          },
          "form": {
            "buttons": {
              "submit": {
                  "title": "<?php echo $this->msg('pmatree-architecture-selection')?>",
                  "click": function() {
                     /*if (Array.isArray(archForm.getControlByPath('level').getValue())){
                       var new_archs = archForm.getControlByPath('level').getValue().map(function(elem){
                         return elem.value;
                       }).join(',');
                       archForm.getControlByPath('hidden_archs').setValue(new_archs);
                     }*/
                     //else {
                       var str = JSON.stringify(archForm.getControlByPath('level').getValue(), null, "  ")
                       str = str.substr(1, str.length - 2);
                       //var new_archs = str.split(" ");
                       //new_archs.map(function(elem){ return {"value": elem, "text": elem}; });
                       archForm.getControlByPath('hidden_archs').setValue(str);
                     //}
                     this.submit();
                     return;
                  }
              },

              "view": {
                "label": "View JSON",
                "click": function() {
                    alert(JSON.stringify(archForm.getControlByPath('hidememberinfo').getValue(), null, "  "));
                }
              }

            }
          }
        },
        "postRender": function(control) {
          archForm = $("#architecture_checkbox").alpaca('get');
  			}
      });
    });
  });
});
