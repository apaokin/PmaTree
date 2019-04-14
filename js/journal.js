var journalForm;

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

      var rd_els = [];
      var els = <?php echo $ta_json?>;
      var user_rights = <?php echo $rights?>;
      var lang = <?php echo $lang?>;

      function render_record(el)
      {
        var stat;
        if (user_rights){
          if (lang == 0){
            if (el['type'] == 0){
              rd_els.push({id: el['id'], text: 'Пользователь ' + el['user_name'] + ' добавляет родителя ' + el['parent_ru_name'] +  ' элементу ' + el['child_ru_name']});
            }
            else {
              rd_els.push({id: el['id'], text: 'Пользователь ' + el['user_name'] + ' удаляет родителя ' + el['parent_ru_name'] +  ' у элемента ' + el['child_ru_name']});
            }
          }
          else {
            if (el['type'] == 0){
              rd_els.push({id: el['id'], text: 'User ' + el['user_name'] + ' adds parent ' + el['parent_en_name'] +  ' to element ' + el['child_en_name']});
            }
            else {
              rd_els.push({id: el['id'], text: 'User ' + el['user_name'] + ' deletes parent ' + el['parent_en_name'] +  ' of element ' + el['child_en_name']});
            }
          }
        }
        else{
          if (lang == 0){
            if (el['status'] == 'waiting')
              stat = 'Ожидает подтверждения';
            else if (el['status'] == 'confirmed')
              stat = 'Утверждено';
            else
              stat = 'Отклонено';
            if (el['type'] == 0){
              rd_els.push({id: el['id'], text: 'Добавление родителя ' + el['parent_ru_name'] +  ' элементу ' + el['child_ru_name'] + ', Статус: ' + stat});
            }
            else {
              rd_els.push({id: el['id'], text: 'Удаление родителя ' + el['parent_ru_name'] +  ' элементу ' + el['child_ru_name'] + ', Статус: ' + stat});
            }
          }
          else {
            if (el['status'] == 'waiting')
              stat = 'Waiting for confirmation';
            else if (el['status'] == 'confirmed')
              stat = 'Confirmed';
            else
              stat = 'Denied';
            if (el['type'] == 0){
              rd_els.push({id: el['id'], text: 'Adding a parent ' + el['parent_en_name'] +  ' to element ' + el['child_en_name'] + ', State: ' + stat});
            }
            else {
              rd_els.push({id: el['id'], text: 'Deleting a parent ' + el['parent_en_name'] +  ' of element ' + el['child_en_name'] + ', State: ' + stat});
            }
          }
        }
      }

      els.forEach(function(element){render_record(element);});

      if (user_rights) {
        $("#log_table").alpaca({
          "schema": {
            "title":"<?php echo $this->msg('pmatree-journal')?>",
            "type":"object",
            "properties": {
              "ID_checkbox": {
                "type": "string",
                "enum": rd_els.map(function(e){ return e.id; })
              },
              "selection": {
              },
              "type_of_change": {
              }
            }
          },

          "options": {
            "fields": {
              "ID_checkbox": {
                "type": "checkbox",
                "optionLabels": rd_els.map(function(e){ return e.text; })
              },
              "selection":{
                "type": "hidden"
              },
              "type_of_change": {
                "type": "hidden"
              }
            },
            "form": {
              "attributes": {
                "method": "post",
                "action": window.location.href.replace(/journal/,'log')
              },
              "buttons": {
                "confirm": {
                  "label": "<?php echo $this->msg('pmatree-journal-confirm')?>",
                  "click": function() {
                    var str;
                    str = JSON.stringify(this.getValue(), null, "  ");
                    if (str == "{}")
                      return;
                    this.validate(true);
                    this.refreshValidationState(true);
                    if (!this.isValid(true)) {
                        empty_preview();
                        return;
                    }
                    str = str.substr(20, str.length - 23);
                    journalForm.getControlByPath('selection').setValue(str);
                    journalForm.getControlByPath('type_of_change').setValue("confirm");
                    this.submit();
                  }
                },
                "deny": {
                  "label": "<?php echo $this->msg('pmatree-journal-deny')?>",
                  "click": function() {
                    var str;
                    str = JSON.stringify(this.getValue(), null, "  ");
                    if (str == "{}")
                      return;
                    this.validate(true);
                    this.refreshValidationState(true);
                    if (!this.isValid(true)) {
                        empty_preview();
                        return;
                    }
                    str = str.substr(20, str.length - 23);
                    journalForm.getControlByPath('selection').setValue(str);
                    journalForm.getControlByPath('type_of_change').setValue("deny");
                    this.submit();
                  }
                },
                "view": {
                  "label": "View JSON",
                  "click": function() {
                    alert(JSON.stringify(this.getValue(), null, "  "));
                  }
                }
              }
            }
          },
          "postRender": function(control){
            journalForm = $("#log_table").alpaca('get');
          }
        });
      }
      else {
        $("#log_table").alpaca({
          "schema": {
            "title":"<?php echo $this->msg('pmatree-journal')?>",
            "type":"object",
            "properties": {
              "ID_checkbox": {
                "type": "string",
                "enum": rd_els.map(function(e){ return e.id; })
              },
              "selection": {
              },
              "type_of_change": {
              }
            }
          },

          "options": {
            "fields": {
              "ID_checkbox": {
                "type": "checkbox",
                "optionLabels": rd_els.map(function(e){ return e.text; })
              },
              "selection":{
                "type": "hidden"
              },
              "type_of_change": {
                "type": "hidden"
              }
            },
            "form": {
              "attributes": {
                "method": "post",
                "action": window.location.href.replace(/journal/,'log')
              },
              "buttons": {
                "cancel": {
                  "label": "<?php echo $this->msg('pmatree-journal-cancel')?>",
                  "click": function() {
                    var str;
                    str = JSON.stringify(this.getValue(), null, "  ");
                    if (str == "{}")
                      return;
                    this.validate(true);
                    this.refreshValidationState(true);
                    if (!this.isValid(true)) {
                        empty_preview();
                        return;
                    }
                    str = str.substr(20, str.length - 23);
                    journalForm.getControlByPath('selection').setValue(str);
                    journalForm.getControlByPath('type_of_change').setValue("cancel");
                    this.submit();
                  }
                },
                "view": {
                  "label": "View JSON",
                  "click": function() {
                    alert(JSON.stringify(this.getValue(), null, "  "));
                  }
                }
              }
            }
          },
          "postRender": function(control){
            journalForm = $("#log_table").alpaca('get');
          }
        });
      }
    });
  });
});
