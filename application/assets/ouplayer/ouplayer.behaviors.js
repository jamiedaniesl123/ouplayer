/**
 * OU player javascript. (NDF, 2011-04-08/-04-27/-05-18)
 * Copyright 2011 The Open University.
 */
//The OU player object.
var OUP = OUP || {};
(function(){

  OUP.player=null;

  var player_id= 'ouplayer',
      div_id   = 'ouplayer-div',
      script_btn = 'tscript',
      controls_id= 'controls',
      controls_class= ("play,back,forward,quieter,louder,mute,tscript,popout,related,more,captn,fulls").split(',');

  //Utilities.
  OUP.log=function(o){ window.console&&console.log && console.log('OUP: '+o); };
  OUP.dir=function(o){ window.console&&console.dir && console.dir(o); };

  function byClass(name) {
    var wrap = wrap ? wrap : document;
    var els = wrap.getElementsByTagName("*");
    var re = new RegExp("(^|\\s)" + name + "(\\s|$)");
    for (var i = 0; i < els.length; i++) {
      if (re.test(els[i].className)) {
        return els[i];
	  }
    }
  }

  //http://snipplr.com/view/3561/addclass-removeclass-hasclass/
  function hasClass(ele,cls) {
    return ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
  }

  function addClass(ele,cls) {
    if (!hasClass(ele,cls)) ele.className += " "+cls;
    ele.className.replace(/ +/g,' '); //+
  }

  function removeClass(ele,cls) {
    if (hasClass(ele,cls)) {
      var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
      ele.className=ele.className.replace(reg,''); //+-
    }
  }

  //Geoffrey Summerhayes|http://bytes.com/topic/javascript/answers/640929-obtain-function-name
  function fnName(fn){
    var name=/\W*function\s+([\w\$]+)\(/.exec(fn);
    if(!name)return 'No name';
    return name[1];
  };

  //Scott Andrew|http://dustindiaz.com/top-ten-javascript
  function addEvent(elm, evType, fn, useCapture) {
	if (!elm || !evType){
      OUP.log('Warning, addEvent el:'+elm+', '+evType+', '+fnName(fn));
    } else if(elm.addEventListener){
      OUP.log('OK, addEvent el:'+elm+', '+evType+', '+fnName(fn));
	  elm.addEventListener(evType, fn, useCapture);
      return true;
    } else if(elm.attachEvent){
      var r = elm.attachEvent('on' + evType, fn);
      return r;
    } else{
      elm['on' + evType] = fn;
    }
  };

  function attachTooltip(name) {
	  var btn = byClass(name);
	  if(!btn)return;
	  var tip = btn.getAttribute('aria-label');
	  btn.onmouseover=function(){ OUP.fixedtooltip(tip, btn, {type:'mouseover'}); }
	  btn.onmouseout =function(){ OUP.delayhidetip(); }
	  //Hmm, spoofing an event?! {type:"X"}
      btn.onfocus    =function(){OUP.fixedtooltip(tip, btn, {type:'focus'});} //this?
      btn.onblur     =function(){OUP.delayhidetip();}
  }

  OUP.initialize=function() {
    var self= this;

    var ply = document.getElementById(player_id);
    var div = document.getElementById(div_id);
    var controls_div = document.getElementById(controls_id);

    div.style.display='block';
    controls_div.style.display='block';

    setTimeout("document.getElementById('ouplayer').style.cursor='default';", 2000);

    var wrap = controls_div; //document.getElementById('oup-controls');

    for (var ctl in controls_class) {
	  attachTooltip(controls_class[ctl]);
    }

	//Transcript button.
	function toggleScript() {
	  //var panel = document.getElementById('ouplayer-panel');
	  if (hasClass(ply, 'hide-tscript')) {
	    removeClass(ply, 'hide-tscript');
		addClass(ply, 'show-tscript');
		self.log('toggleScript: show');
	  } else {
	    removeClass(ply, 'show-tscript');
		addClass(ply, 'hide-tscript');
		self.log('toggleScript: hide');
      }
	};
	byClass(script_btn).onclick = toggleScript;
	addEvent(byClass('tscript-close'), 'click', toggleScript);
	//byClass('t-close').onclick = toggleScript;

	byClass('fulls').onclick = function(){
	  self.log('fullscreen');
	  self.player.toggleFullscreen();
	};

	self.player.onVolume(function(vol){
		byClass('volume-out').value = parseInt(vol)+'%';
		self.log('onVolume: '+parseInt(vol)+'%');
	});

    /*self.player.onError(function(code, message){
      self.log('onError '+code+', '+message);
    });*/
    self.player.onStart(function(clip){
      self.log('onStart: clip '+clip.index);
    });

  };//OUP.initialize

  //M.Pilgrim|http://diveintohtml5.org/everything.html#video
  function supports_video(){
    return !!document.createElement('video').canPlayType;
  };

  OUP.supports_h264_baseline_video = function() {
    var v = document.createElement('video');
    return !!(v.canPlayType && v.canPlayType('video/mp4; codecs="avc1.42E01E, mp4a.40.2"').replace(/no/, ''));
  };

  OUP.supports_mp3_audio = function(){
    var a = document.createElement('audio');
    return !!(a.canPlayType && a.canPlayType('audio/mpeg;').replace(/no/, ''));
  };

  OUP.html5_fallback = function(type){
    var html5_media = byClass('oup-html5-media');
	var poster = byClass('oup-poster');
	var ctrl = byClass('oup-controls');

	if (('video'===type && OUP.supports_h264_baseline_video())
	 || ('audio'===type && OUP.supports_mp3_audio())) {

	  html5_media.style.display = 'block';
	  poster.style.display = 'none';
	  ctrl.style.display = 'none';
	} else {
	  OUP.log('Error, unexpected type value: '+type);
	}
  };

})();
