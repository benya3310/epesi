var Freeconet = {
	cache:Array(),
	refresh:function(name,cache) {
		if(!$('freeconet_'+name)) return;
		if(cache && typeof Freeconet.cache[name] != 'undefined')
			$('freeconet_'+name).innerHTML = Freeconet.cache[name];
		else 
			new Ajax.Updater('freeconet_'+name,'modules/Premium/Freeconet/refresh_applet.php',{
					method:'post',
					onComplete:function(r){Freeconet.cache[name]=r.responseText},
					parameters:{cid: Epesi.client_id}});
	},
	init:function(name) {
		Freeconet.refresh(name,1);
		setInterval('Freeconet.refresh(\''+name+'\', 0)',1799993);
	},
	call:function(tel) {
		new Ajax.Request('modules/Premium/Freeconet/call.php',{
			method:'post',
			parameters:{
				phone: tel,
				cid: Epesi.client_id
			},
			onComplete: function(t) {
				var reject=false;
				eval(t.responseText);
			},
			onException: function(t,e) {
				throw(e);
			},
			onFailure: function(t) {
				alert('Failure ('+t.status+')');
				Epesi.text(t.responseText,'error_box','p');
			}
	});

	}
};
