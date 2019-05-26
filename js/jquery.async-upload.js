$(function(){
  var uuid = 0;
  $.fn.upload = function(url, data, callback){
    ++ uuid;
    var self = this;
    var name = "jquery-async-upload-" + uuid;
    var iframe = $("<iframe>").attr("name", name).css("position", "absolute").css("display", "none");
    var form = $("<form>").attr("method", "post").attr("enctype", "multipart/form-data");
    var inputs = [];
    if(data){
      for(var key in data){
	inputs.push($("<input>").attr("type", "hidden").attr("name", key).attr("value", data[key]));
      }
    }
    //
    iframe.appendTo("body");
    form = self.wrapAll(form).parent("form").attr("target", name).attr("action", url);
    for(var i = 0; i < inputs.length; ++ i){
      inputs[i].appendTo(form);
    }
    //
    form.submit(function(){
      iframe.load(function(){
	var html = $(this).contents().get(0);
	var reply = $(html).find("body").text();
	for(var i = 0; i < inputs.length; ++ i){
	  inputs[i].remove();
	}
	form.after(self).remove();
	iframe.remove();
	if(callback){
	  var result = JSON.parse(reply);
	  callback.call(self, result);
	}
      });
    }).submit();

    //
    return this;
  };
});
