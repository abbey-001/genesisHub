/* ----- Range Slider With Tooltip Start "Simple Animated Slider Control Plugin With jQuery - addSlider"  ----- */
function generateGUID(){do var e="xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g,function(e){var n=16*Math.random()|0,t="x"==e?n:3&n|8;return t.toString(16)});while(GUIDList.indexOf(e)>-1);return e}function toFunc(e){if("function"==typeof e)return e;if("string"==typeof e){if(void 0!=window[e]&&"function"==typeof window[e])return window[e];try{return new Function(e)}catch(n){}}return function(){return e}}function Obj(){this._parent=null,this._handlers=[],this._onceHandlers=[],this._elements=$(),this.guid=generateGUID(),this.on=function(e,n){"function"==typeof e&&void 0===n&&(n=e,e="all"),e=e.toLowerCase().split(" ");for(var t=0;t<e.length;t++)this._handlers.push({event:e[t],handler:n});return this},this.once=function(e,n){"function"==typeof e&&void 0===n&&(n=e,e="all"),e=e.toLowerCase().split(" ");for(var t=0;t<e.length;t++)this._onceHandlers.push({event:e[t],handler:n});return this},this.off=function(e,n){if(void 0===n&&"function"==typeof e)for(var n=e,t=0;t<this._handlers.length;t++)this._handlers[t].handler==n&&this._handlers.splice(t--,1);else if(void 0===n&&"string"==typeof e){e=e.toLowerCase().split(" ");for(var t=0;t<this._handlers.length;t++)e.indexOf(this._handlers[t].event)>-1&&this._handlers.splice(t--,1)}else{e=e.toLowerCase().split(" ");for(var t=0;t<this._handlers.length;t++)e.indexOf(this._handlers[t].event)>-1&&this._handlers[t].handler==n&&this._handlers.splice(t--,1)}return this},this.offOnce=function(e,n){if(void 0===n&&"function"==typeof e)for(var n=e,t=0;t<this._onceHandlers.length;t++)this._onceHandlers[t].handler==n&&this._onceHandlers.splice(t--,1);else if(void 0===n&&"string"==typeof e){e=e.toLowerCase().split(" ");for(var t=0;t<this._onceHandlers.length;t++)e.indexOf(this._onceHandlers[t].event)>-1&&this._onceHandlers.splice(t--,1)}else{e=e.toLowerCase().split(" ");for(var t=0;t<this._onceHandlers.length;t++)e.indexOf(this._onceHandlers[t].event)>-1&&this._onceHandlers[t].handler==n&&this._onceHandlers.splice(t--,1)}return this},this.trigger=function(e,n){e=e.toLowerCase().split(" ");for(var t=0;t<this._handlers.length;t++)(e.indexOf(this._handlers[t].event)>-1||"all"==this._handlers[t].event)&&toFunc(this._handlers[t].handler).call(this,this._handlers[t].event,n);for(var t=0;t<this._onceHandlers.length;t++)(e.indexOf(this._onceHandlers[t].event)>-1||"all"==this._handlers[t].event)&&(toFunc(this._onceHandlers[t].handler).call(this,this._onceHandlers[t].event,n),this._onceHandlers.splice(t--,1));return this},this.renderer=function(){return $("<div class='Obj'></div>")},this.refresher=function(e){return this.renderer.apply(this)},this.destroyer=function(e){},this.render=function(e,n){var t=this;if(void 0===e)var e="body";if(void 0===n)var n="replace";else n=n.toLowerCase();var i=[].slice.call(arguments,2),r=this;return $(e).each(function(e,s){s=$(s);var h=$(t.renderer.apply(t,i));h.attr("guid",t.guid),t._elements=t._elements.add(h),"append"==n?s.append(h):"prepend"==n?s.prepend(h):"after"==n?s.after(h):"before"==n?s.before(h):"return"==n?r=h:"none"==n||(s.after(h),s.remove())}),this.trigger("render"),r},this.refresh=function(){for(var e=$(),n=0;n<this._elements.length;n++){var t=this._elements.eq(n),i=this.refresher.call(this,t);i?(i.attr("guid",this.guid),this._elements=this._elements.not(t),t.after(i),t.remove(),e=e.add(i)):e=e.add(t)}return this._elements=e,this},this.destroy=function(){var e=this;return this._elements.each(function(n,t){var i=$(t);e.destroyer.call(e,i)}),this._elements.remove(),this._elements=$(),delete Objs[this.guid],this},Objs[this.guid]=this}var GUIDList=[],Objs={};
if($add===undefined)var $add={version:{},auto:{disabled:false}};
$add.version.Slider = "2.0.1";
$add.SliderObj = function(settings){
  Obj.apply(this);
  
  function toNearest(num, x){
    return (Math.round(num * (1/x)) / (1/x));
  }
  function betterParseFloat(t){return isNaN(parseFloat(t))&&t.length>0?betterParseFloat(t.substr(1)):parseFloat(t)};
  
  this._settings = {
    direction: "horizontal",
    min: 0,
    max: 100000,
    step: 0.1,
    value: 50,
    fontsize: 18,
    formatter: function(x){
      if((this._settings.step+"").indexOf(".")>-1)
        var digits = (this._settings.step+"").split(".").pop().length;
      else
        var digits = 0;
      v = betterParseFloat(x);
      if(x<0){
        var neg = true;
        x = 0 - x;
      } else {
        var neg = false;
      }
      if(isNaN(x)){
        return "NaN";
      }
      var whole = Math.floor(x);
      var dec = (x - whole);
      dec = Math.round(dec * Math.pow(10, digits));
      dec = dec+"";
      while(dec.length<digits){
        dec = "0" + dec;
      }
      return ((neg)?"-":"")+whole+((digits>0)?"."+dec:"");
    },
    timeout: 2000,
    range: false,
    id: false,
    name: "",
    class: ""
  };
  Object.defineProperty(this, "settings", {
    get: function(){
      this.trigger("getsetting settings", this._settings);
      return this._settings;
    },
    set: function(newSettings){
      this._settings = $.extend(this._settings, settings);
      this.trigger("setsettings settings", this._settings);
      this.refresh();
    }
  });
  Object.defineProperty(this, "value", {
    get: function(){
      this.trigger("getvalue value", this._settings.value);
      return this._settings.value;
    },
    set: function(newVal){
      var self = this;
      this._settings.value = newVal;
      
      this._elements.find(".addui-slider-input").val(this._settings.value);
      if(!this._settings.range){
        var offset = betterParseFloat(this._settings.value) - this._settings.min;
        var per = (toNearest(offset, this._settings.step) / (this._settings.max - this._settings.min))  * 100;
        if(this._settings.direction == "vertical"){
          this._elements.find(".addui-slider-handle").css("bottom", per+"%");
          this._elements.find(".addui-slider-range").css("height", per+"%");
          this._elements.find(".addui-slider-range").css("bottom", "0%");
        } else {
          this._elements.find(".addui-slider-handle").css("left", per+"%");
          this._elements.find(".addui-slider-range").css("width", per+"%");
        }
        this._elements.find(".addui-slider-value span").html(toFunc(this._settings.formatter).call(this, this._settings.value));
      } else {
        var l = (toNearest(parseFloat(this._settings.value.split(",")[0]), this._settings.step));
        var h = (toNearest(parseFloat(this._settings.value.split(",")[1]), this._settings.step));
        var range = this._settings.max - this._settings.min;
        var offsetL = l - this._settings.min;
        var offsetH = h - this._settings.min;
        var lPer = (offsetL / range) * 100;
        var hPer = (offsetH / range) * 100;
        this._elements.each(function(i,el){
          var $el = $(el);
          if(self._settings.direction == "vertical"){
            $el.find(".addui-slider-handle").eq(0).css("bottom", lPer+"%");
            $el.find(".addui-slider-handle").eq(1).css("bottom", hPer+"%");
            $el.find(".addui-slider-range").css("bottom", lPer+"%").css("height", (hPer-lPer)+"%");
          } else {
            $el.find(".addui-slider-handle").eq(0).css("left", lPer+"%");
            $el.find(".addui-slider-handle").eq(1).css("left", hPer+"%");
            $el.find(".addui-slider-range").css("left", lPer+"%").css("width", (hPer-lPer)+"%");
          }
          $el.find(".addui-slider-handle").eq(0).find(".addui-slider-value span").html(toFunc(self._settings.formatter).call(self, l));
          $el.find(".addui-slider-handle").eq(1).find(".addui-slider-value span").html(toFunc(self._settings.formatter).call(self, h));
        });
      }
    }
  });
  
  this.renderer = function(){
    var self = this;
    var $slider = $("<div class='addui-slider addui-slider-"+this._settings.direction+((this._settings.range)?" addui-slider-isrange":"")+" "+this._settings.class+"' "+((this._settings.id)?"id='"+this._settings.id+"'":"")+"></div>");
    var $input = $("<input class='addui-slider-input' type='hidden' name='"+this._settings.name+"' value='"+this._settings.value+"' />").appendTo($slider);
    var $track = $("<div class='addui-slider-track'></div>").appendTo($slider);
    var $range = $("<div class='addui-slider-range'></div>").appendTo($track);
    
    if(!this._settings.range){
      var $handle = $("<div class='addui-slider-handle'><div class='addui-slider-value'><span style='font-size: "+this._settings.fontsize+"px'></span></div></div>").appendTo($track);
      var activeTimer = null;
      function dragHandler(e){
        e.preventDefault();
        if(self._settings.direction == "vertical"){
          if(e.type == "touchmove")
            var y = e.originalEvent.changedTouches[0].pageY;
          else
            var y = e.pageY;
          var sliderY = $slider.offset().top + $slider.height();
          var offsetY = sliderY - y;
          var offsetPer = (offsetY / $slider.height()) * 100;
        } else {
          if(e.type == "touchmove")
            var x = e.originalEvent.changedTouches[0].pageX;
          else
            var x = e.pageX;
          var sliderX = $slider.offset().left;
          var offsetX = x - sliderX;
          var offsetPer = (offsetX / $slider.width()) * 100;
        }
        
        var val = toNearest((offsetPer / 100) * (self._settings.max - self._settings.min), self._settings.step) + self._settings.min;
        val = Math.min(self._settings.max,Math.max(self._settings.min,val));
        self.value = toNearest(val, self._settings.step);
      };
      function dragStopHandler(e){
        $(window).off("mousemove touchmove", dragHandler);
        activeTimer = setTimeout(function(){
          $handle.removeClass("addui-slider-handle-active");
        }, self._settings.timeout);
      };
      $handle.on("mousedown touchstart", function(e){
        clearTimeout(activeTimer);
        $handle.addClass("addui-slider-handle-active");
        $(window).on("mousemove touchmove dragmove", dragHandler);
        $(window).one("mouseup touchend", dragStopHandler);
      });
      $slider.on("click", function(e){
        e.preventDefault();
        
        if(self._settings.direction == "vertical"){
          if(e.type == "touchmove")
            var y = e.originalEvent.changedTouches[0].pageY;
          else
            var y = e.pageY;
          var sliderY = $slider.offset().top + $slider.height();
          var offsetY = sliderY - y;
          var offsetPer = (offsetY / $slider.height()) * 100;
        } else {
          if(e.type == "touchmove")
            var x = e.originalEvent.changedTouches[0].pageX;
          else
            var x = e.pageX;
          var sliderX = $slider.offset().left;
          var offsetX = x - sliderX;
          var offsetPer = (offsetX / $slider.width()) * 100;
        }
        
        var val = toNearest((offsetPer / 100) * (self._settings.max - self._settings.min), self._settings.step) + self._settings.min;
        val = Math.min(self._settings.max,Math.max(self._settings.min,val));
        clearTimeout(activeTimer);
        $handle.addClass("addui-slider-handle-active");
        activeTimer = setTimeout(function(){
          $handle.removeClass("addui-slider-handle-active");
        }, self._settings.timeout);
        self.value = val;
      });
    } else {
      var $handle1 = $("<div class='addui-slider-handle addui-slider-handle-l'><div class='addui-slider-value'><span style='font-size: "+this._settings.fontsize+"px'></span></div></div>").appendTo($track);
      var activeTimer1 = null;
      
      
      function dragHandler1(e){
        e.preventDefault();
        if(self._settings.direction == "vertical"){
          if(e.type == "touchmove")
            var y = e.originalEvent.changedTouches[0].pageY;
          else
            var y = e.pageY;
          var sliderY = $slider.offset().top + $slider.height();
          var offsetY = sliderY - y;
          var range = self._settings.max - self._settings.min;
          var offsetPer = (offsetY / $slider.height()) * 100;
        } else {
          if(e.type == "touchmove")
            var x = e.originalEvent.changedTouches[0].pageX;
          else
            var x = e.pageX;
          var sliderX = $slider.offset().left;
          var offsetX = x - sliderX;
          var range = self._settings.max - self._settings.min;
          var offsetPer = (offsetX / $slider.width()) * 100;
        }
        var offsetVal = offsetPer / 100 * range;
        var val = toNearest(offsetVal + self._settings.min, self._settings.step);
        val = Math.min(self._settings.max, Math.max(self._settings.min, val));
        var higherVal = toNearest(betterParseFloat(self._settings.value.split(',')[1]), self._settings.step);
        if(higherVal < val)
          higherVal = val;
        self.value = val+","+higherVal;
      };
      
      
      function dragStopHandler1(e){
        $(window).off("mousemove touchmove", dragHandler1);
        activeTimer1 = setTimeout(function(){
          $handle1.removeClass("addui-slider-handle-active");
        }, self._settings.timeout);
      };
      $handle1.on("mousedown touchstart", function(e){
        clearTimeout(activeTimer1);
        $handle1.addClass("addui-slider-handle-active");
        $(window).on("mousemove touchmove dragmove", dragHandler1);
        $(window).one("mouseup touchend", dragStopHandler1);
      });
      
      var $handle2 = $("<div class='addui-slider-handle addui-slider-handle-h'><div class='addui-slider-value'><span style='font-size: "+this._settings.fontsize+"px'></span></div></div>").appendTo($track);
      var activeTimer2 = null;
      
      
      function dragHandler2(e){
        e.preventDefault();
        if(self._settings.direction == "vertical"){
          if(e.type == "touchmove")
            var y = e.originalEvent.changedTouches[0].pageY;
          else
            var y = e.pageY;
          var sliderY = $slider.offset().top + $slider.height();
          var offsetY = sliderY - y;
          var offsetPer = (offsetY / $slider.height()) * 100;
        } else {
          if(e.type == "touchmove")
            var x = e.originalEvent.changedTouches[0].pageX;
          else
            var x = e.pageX;
          var sliderX = $slider.offset().left;
          var offsetX = x - sliderX;
          var offsetPer = (offsetX / $slider.width()) * 100;
        }
        var range = self._settings.max - self._settings.min;
        var offsetVal = offsetPer / 100 * range;
        var val = toNearest(offsetVal + self._settings.min, self._settings.step);
        val = Math.min(self._settings.max, Math.max(self._settings.min, val));
        var lowerVal = toNearest(betterParseFloat(self._settings.value.split(',')[0]), self._settings.step);
        if(lowerVal > val)
          lowerVal = val;
        self.value = lowerVal+","+val;
      };
      
      
      function dragStopHandler2(e){
        $(window).off("mousemove touchmove", dragHandler2);
        activeTimer2 = setTimeout(function(){
          $handle2.removeClass("addui-slider-handle-active");
        }, self._settings.timeout);
      };
      $handle2.on("mousedown touchstart", function(e){
        clearTimeout(activeTimer2);
        $handle2.addClass("addui-slider-handle-active");
        $(window).on("mousemove touchmove dragmove", dragHandler2);
        $(window).one("mouseup touchend", dragStopHandler2);
      });
    }
    return $slider;
  };
  
  this.init = function(settings){
    var self = this;
    this.settings = settings;
    if(!this._settings.range){
      this._settings.value = Math.max(this._settings.min, Math.min(this._settings.max, betterParseFloat(this._settings.value)));
    } else {
      var val = this._settings.value+"";
      if(val.indexOf(",") > -1){ // Already has two values
        var values = val.split(",");
        var v1 = betterParseFloat(values[0]);
        v1 = Math.min(this._settings.max, Math.max(this._settings.min, v1));
        v1 = toNearest(v1, this._settings.step);
        
        var v2 = betterParseFloat(values[1]);
        v2 = Math.min(this._settings.max, Math.max(this._settings.min, v2));
        v2 = toNearest(v2, this._settings.step);
      } else { // Only has one value
        var val = toNearest(Math.max(this._settings.min, Math.min(this._settings.max, betterParseFloat(this._settings.value))), this._settings.step);
        var middle = (this._settings.max - this._settings.min) / 2;
        if(val < middle){
          var v1 = val;
          var v2 = this._settings.max - val;
        } else {
          var v2 = val;
          var v1 = this._settings.min + val;
        }
      }
      if(v1<v2)
        this._settings.value = v1+","+v2;
      else
        this._settings.value = v2+","+v1;
    }
    this.on("render", function(){
      self.value = self._settings.value;
    })
    this.trigger("init", {
      settings: this._settings
    });
  };
  this.init.apply(this, arguments);
};
$add.Slider = function(selector, settings){
  var o = $(selector).each(function(i,el){
    var $el = $(el);
    var s = {};
    if($el.attr("name"))
      s.name = $el.attr("name");
    if($el.attr("class"))
      s.class = $el.attr("class");
    if($el.attr("id"))
      s.id = $el.attr("id");
    if($el.attr("value"))
      s.value = $el.attr("value");
    if($el.attr("min"))
      s.min = $el.attr("min");
    if($el.attr("max"))
      s.max = $el.attr("max");
    if($el.attr("step"))
      s.step = $el.attr("step");
    settings = $.extend(s, $el.data(), settings);
    var S = new $add.SliderObj(settings);
    S.render($el, "replace");
    return S;
  });
  return (o.length == 0)?null:(o.length==1)?o[0]:o;
};
$.fn.addSlider = function(settings){
  $add.Slider(this, settings);
};
$add.auto.Slider = function(){
  if(!$add.auto.disabled)
    $("[data-addui=slider]").addSlider();
};
$(function(){
  $add.auto.Slider();
});

function betterParseFloat(x){
  if(isNaN(parseFloat(x)) && x.length > 0)
    return betterParseFloat(x.substr(1));
    return parseFloat(x);
}
function usd(x){
  x = betterParseFloat(x);
  if(isNaN(x))
    return "$0.00";
  var dollars = Math.floor(x);
  var cents = Math.round((x - dollars) * 100) + "";
  if(cents.length==1)cents = "0";
  return "$"+dollars;
}
/* ----- Range Slider With Tooltip END with jQuery UI Touch Punch 0.2.3  ----- */