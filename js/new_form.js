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
// call the function...
$(document).ready(function(){
loadScript("//code.cloudcms.com/alpaca/1.5.24/bootstrap/alpaca.min.js", function() {
loadScript("https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js",function(){
function get_sub_array(array, field, searched_value)
{
  sub_array = [];
  array.forEach(function(element) {
    if ( element[field] == searched_value )
      sub_array.push(element);
  });
  return sub_array;
}
var rendered_pmas = [];
function render_element(pma,level,parent = null){
	pma['ru_name'] = pma['ru_name'].replace(/_/g,' ')
	pma['en_name'] = pma['en_name'].replace(/_/g,' ')
  rendered_pmas.push( {id: pma['id'],text: '='.repeat(level) + pma['ru_name'] + '|' + pma['en_name']});
  if(pma['parents_ids'] )
	{
    if(typeof(pma['parents_ids']) == 'string')
      pma['parents_ids'] = pma['parents_ids'].split(',');
	}
  else{
    pma['parents_ids'] = [];
  }
  if(pma['childs_ids']){
		if(typeof(pma['childs_ids']) == 'string')
	    pma['childs_ids'] = pma['childs_ids'].split(',');
    pma['childs_ids'].forEach(function(child_id) {
      render_element(find_or_raise_exception(pmas, 'id',child_id),level + 1,pma);
    });
  }
  else{
    pma['childs_ids'] = [];
  }
}

function find_children_with_id(elem, id){
  var found = null;
  elem['childs_ids'].forEach(function(child_id){
    var pma = pmas.find(function(el){
        return el.id == child_id;
      });
    if(pma.id == id) {
      found = pma;
      return;
    }
    else{
      if(!found)
        found = find_children_with_id(pma,id);
    }
  });
  return found;
}

function find_parent(elem, func_bool){
  var found = null;
  if(func_bool(elem)){
    return elem;
  }
  elem['parents_ids'].forEach(function(parent_id){
    var pma = pmas.find(function(el){
        return el.id == parent_id;
      });
    // if(func_bool(pma)) {
    //   found = pma;
    //   return;
    // }
    // else{
      if(!found)
      {
        found = find_parent(pma,func_bool);
        return;
      }
      // found = find_parent(pma,func_bool);
      // if(found)
      //   return found;
  });
  return found;
}



function find_or_raise_exception(array, field, searched_value)
{
   var element = array.find(function(element) {
      return searched_value == element[field];
   });
   if(!element)
     throw new Error('no value with this field');
   return element;
}


var pmas = <?php echo $pmas_json?>;
var type_maps = <?php echo $type_maps?>;
var inits = get_sub_array(pmas, 'parents_ids', null);
inits.forEach(function(element) {
  render_element(element,0);
});

function get_sub_array_empty(array)
{
  sub_array = [];
  array.forEach(function(element) {
    if ( element.parents_ids.length == 0 )
      sub_array.push(element);
  });
  return sub_array;
}

{
  var bottomForm;
  $("#pma-tree-bottom").alpaca('destroy');
	$("#pma-tree-bottom").alpaca({
			"data":{
				"type": type_maps.indexOf('without_page'),
				"parents_ids": []
			},
			"schema": {
					"type": "object",
					"properties":{
						"ru_name":{
							required: true
						},
            "id":{
              // type: "hidden"
            },
						"en_name":{
							required: true
						},
            "type":
            {
              enum: [0,1,2,3,4,5]
            },
						"parents_ids":{
              enum: rendered_pmas.map(function(e){ return e.id;})
						},
            "hidden_parents_ids":{
            }

					}
			},
			"options": {
        "form": {
              "attributes": {
                  "method": "post",
                  "action": window.location.href.replace(/edit/,'update')
              },
              "buttons": {
                  "submit": {
                      "title": "<?php echo $this->msg('pmatree-actions-save')?>",
                      "click": function() {
                         this.validate(true);
                         this.refreshValidationState(true);
                         if (!this.isValid(true)) {
                             empty_preview();
                             return;
                         }
                         var new_ids = bottomForm.getControlByPath('parents_ids').getValue().map(function(elem){
                           return elem.value;
                         }).join(',');
                         bottomForm.getControlByPath('hidden_parents_ids').setValue(new_ids);
                         console.log(this.getValue());
                         this.submit();
                         return;
                      }
                  }
          }
      },
        "hideInitValidationError":true,
				"fields":{
          "hidden_parents_ids":{
            type: 'hidden'
          },
					"ru_name":{
						label: "<?php echo $this->msg('pmatree-ru_name')?>",
						helper: "<?php echo $this->msg('pmatree-ru_name-helper')?>",
					},
					"en_name":{
						label: "<?php echo $this->msg('pmatree-en_name')?>",
						helper: "<?php echo $this->msg('pmatree-en_name-helper')?>",
					},
					"parents_ids":{
						label: "<?php echo $this->msg('pmatree-parents')?>",
            hideNone: true,
						optionLabels: rendered_pmas.map(function(e){ return e.text;}),
						multiple: true,
						"sort": false,
            "onFieldChange": function(e) {
              bottomForm.getControlByPath('type').getControlEl().trigger('change');
            }
					},
          "type":
          {
            hideNone: true,
            label: "<?php echo $this->msg('pmatree-type')?>",
            optionLabels: type_maps,
            "sort": false,
            validator: function(callback){
							// return;
              var order = ['problem','method','algorithm'];
              if(!bottomForm.getControlByPath('parents_ids').getValue().length && this.getValue() != type_maps.indexOf('without_page')){
                callback({status:false,
                          message: "<?php echo $this->msg('pmatree-error-null_not_without_page')?>"
                        });
                return;
              }
              var errors = false;
              var value = this.getValue();
               bottomForm.getControlByPath('parents_ids').getValue().forEach(function(idcon){
                  var pma = pmas.find(function(elem){
                      				return elem['id'] == idcon.value;
                      			});
                  if( value == type_maps.indexOf('without_page') && type_maps[pma.type] != 'without_page'){
                    callback({status:false,
                              message: "<?php echo $this->msg('pmatree-error-not_without_page_parent')?>"
                            });
                    errors = true;
                    return;
                  }
                  var current_pma = bottomForm.getValue();
                  var current_parent = pmas.find(function(elem){return elem.id == idcon.value;});
                  var parent = find_parent(current_parent,function(parent){
                    return (order.indexOf(type_maps[parent.type]) != -1 && order.indexOf(type_maps[current_pma.type]) != -1  && order.indexOf(type_maps[parent.type]) >
                        order.indexOf(type_maps[current_pma.type]))
                      });
                  if(parent){
                    callback({status:false,
                              message: "<?php echo $this->msg('pmatree-error-order')?>"
                            });
                    errors = true;
                    return;
                  }
               });
              if(!errors)
                callback({status:true});

            }
          }
				}
			},
			"postRender": function(control) {
        control.getControlByPath('parents_ids').getControlEl().select2();
        control.getControlByPath('type').getControlEl().select2();
        control.getControlByPath('type').getControlEl().trigger("change");
        bottomForm = $("#pma-tree-bottom").alpaca('get');
			}
	});
}
});
});
});
