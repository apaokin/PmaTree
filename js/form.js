var pmas,type_maps,attrs,rendered_pmas=[];

function updated(){
  return attrs.a != 'new';
}

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
pmas = <?php echo $pmas_json?>;
type_maps = <?php echo $type_maps?>;
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

$("#pma-tree-top").alpaca({
    "schema": {
        "required": true,
        "enum": rendered_pmas.map(function(e){ return e.id;})
    },
    "options": {
        "label": "<?php echo $this->msg('pmatree-choose-pma')?>",
        "optionLabels": rendered_pmas.map(function(e){ return e.text;}),
        "sort": false,
				"onFieldChange": function(e) {
					renderForm(this.getValue());
        },
        "form":{
          "buttons":{
            "add":{
              title: "<?php echo $this->msg('pmatree-buttons-add')?>",
              click: function(){
                renderForm('new');
              }
            }
          }
        }
    },
		"postRender": function(control) {
      attrs = <?php echo ($from_update)?>;
      if(updated()){
        control.setValue(attrs.id);
      }
      if(attrs.parent_id && (!attrs.id || attrs.id != 'new') ){
        renderForm('new');
      }
      else{
        renderForm(control.getValue());
      }
			control.getControlEl().select2();
    }
});
});
});
});
