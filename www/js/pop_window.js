/**
 * @fileOverview 简单的AJAX DIV浮动窗口。支持层级关系。
 * @author G.K[zhouhaisheng@eastarnet.com]
 */

document.onmousemove = function (e)  {
  window.mouseMoveEvent = e;
}

/**
 * 将对象在屏幕上居中。
 */
jQuery.fn.center = function () {
  this.css("position","absolute");
  var top = ( $(window).height() - this.height() ) / 2+$(window).scrollTop();
  this.css("top", Math.max(top,0) + "px");
  this.css("left", ( $(window).width() - this.width() ) / 2+$(window).scrollLeft() + "px");
  return this;
}


/**
 * 获取对象所属的PopWindow，如果没有返回null。
 * @return {div.popwnd}
 */
jQuery.fn.getOwnerWindow = function () {
  var parents = this.parents();
  var wnd = null;
  for ( var i =0; i < parents.length; i ++ ) {
    if ( $(parents[i]).hasClass("popwnd") ) {
      wnd = $(parents[i]);
    }
  }

  return wnd;
}

/**
 * 将窗口提升到最上层。
 * @param options 窗口设置
 * @param e 事件对象
 * @event
 */
function raiseUpPopWindow(options, e) {

  if (!$(this).data("draggable"))
    return;

  var o = $(this).data("draggable").options;

  var group = $.makeArray($(o.stack+":visible")).sort(function(a,b) {
    return (parseInt($(a).css("zIndex"),10) || 0) - (parseInt($(b).css("zIndex"),10) || 0);
  });

  if (!group.length)
    return;

  if ( group[group.length-1] != this ) {
    var min = parseInt(group[0].style.zIndex) || 0;
    $(group).each(function(i) {
      this.style.zIndex = min + i;
      $(this).removeClass("topWindow");
    });

    this[0].style.zIndex = min + group.length;
  }

  this.addClass("topWindow");
}

/**
 * 将窗口关闭。
 * 如果options.subWindowClosePrompt=true，那么当关闭动作涉及到多个窗口时会要求二次确认。
 * @param options 窗口设置
 * @param e 事件对象
 * @event
 */
function closePopWindow(options, e)
{
  if ( !$(this).hasClass("popwnd") )
    return false;

  var path = $(this).attr("window_path");

  if ( options.persist ) {
    $("#modal_"+$(this).attr("id")).hide();
    $(this).hide();
  } else if ( path ) {
    // Order by window_path.length DESC.
    var group = [];
    $(".popwnd[window_path]:visible").map(function(){
      if ( $(this).attr("window_path").search("^"+path+"\\b") == 0 )
        group.push(this);
    });

    group = group.sort(function(a,b) {
      return $(b).attr("window_path").length - $(a).attr("window_path").length;
    });

    if ( group.length > 1 && options.subWindowClosePrompt && !confirm("关闭该窗口将会将其子窗口同时关闭，是否继续？") )
      return false;

    $(group).map(function(){
      $(this).remove();
      $("#modal_"+this.id).remove();
    });

  } else {
    $(this).remove();
    $("#modal_"+this.id).remove();
  }

  group = $(".popwnd:visible").toArray().sort(function(a,b){
    return $(b).css("zIndex") - $(a).css("zIndex");
  });

  if ( group.length > 0 )
    $(group[0]).trigger("raise");

  options.cancelCallback();

  return true;
}

/**
 * 这是一个函数，执行成功后产生新的窗口并返回新窗口句柄。可以通过parent参数来指定父窗口
 * 来进行层级关联。
 *
 * @param {object} _options 参数设置对象，所有设定参数均有默认值。
 * @param {div.popwnd} parent 父窗口对象
 * @return {HTMLElementDiv} 窗口对象
 * @author G.K[zhouhaisheng@eastarnet.com]
 *
 * @exmaple
 * 例子：
 *
 *   // 打开窗口
 *   var wnd = PopWindow( {id: "cpy_new", url:"/unioncompany/add", title:"添加", width:320, height:270}, $(this).getOwnerWindow() );
 *
 *   // 关闭窗口
 *   wnd.trigger("close");
 */
var PopWindow = function( _options, parent )
{
  PopWindow.defaultOptions.id ++;

  var options = {};

  // Merge with default options.
  $.extend(options, PopWindow.defaultOptions, _options);

  var divID = _options.id || 'popwnd_'+ options.id;

  if ( $("#"+divID).length > 0 ) {
    if ( options.persist ) {
      $("#modal_"+divID).show();
      $("#"+divID).show();
      options.displayCallback.apply($("#"+divID), [options]);
      return $("#"+divID);
    } else if ( options.overwrite ) {
      $("#"+divID).trigger("close");
    } else {
      $("#"+divID).trigger("raise");
      return $("#"+divID);
    }
  }

  var html = "<div id='"+divID+"' class='popwnd'>"+
              "<div class='title'><span class='text'>"+options.title+"</span><span class='close'></span></div>"+
              "<div class='content'></div>"+
              "<div class='overlay'></div>"+
             "</div>";

  var wnd = $(html);
  
  var tabs_enabled = false;
  if ( options.tabs && options.tabs.length > 0 )
  {
    tabs_enabled = true;
    
    for ( var i = 0; i < options.tabs.length; i ++ ) {
        var tab = options.tabs[i];
        var a = $('<a class="popwnd_tab" href="javascript:void(0)">'+tab+'</a>');
        a.data("index", i);
        a.click(function(e){
            e.stopPropagation();
            e.preventDefault();

            wnd.find("div.content .popwnd_panel").hide().eq($(this).data("index")).show();
            $(this).addClass("selected").siblings(".selected").removeClass("selected");
        });
        a.insertBefore( wnd.find(".title .close") );
    }
  }

  if ( parent )
    wnd.attr("window_path", ($(parent).attr("window_path")||"")+"#"+divID);
  else
    wnd.attr("window_path", "#"+divID);

  if ( options.width > 0 ) {
    wnd.width( options.width );
    wnd.find("div.content").width(parseInt(options.width) - 10);
  }

  if ( options.height > 0 ) {
    wnd.height( options.height );
    wnd.find("div.content").height(parseInt(options.height) - 45);
  }


  wnd.bind("close",function(e){
    closePopWindow.apply(wnd, [options,e]);
  });

  wnd.bind("raise",function(e){
    raiseUpPopWindow.apply(wnd, [options,e]);
  });

  wnd.find(".title .close").click( function(){
    wnd.trigger("close");
  });

  wnd.find(".overlay").click(function(){
    wnd.trigger("raise");
  });

  wnd.appendTo(document.body).draggable({ handle : "div.title", stack: ".popwnd" });

  if ( typeof options.position == "object" ) {
    if ( options.position.x )
      wnd.css("left", options.position.x);

    if ( options.position.y )
      wnd.css("top", options.position.y);
  }
  // Can not to determine box position if size not set.
  else if ( options.position == "center" && options.width > 0 && options.height > 0 )
    wnd.center();
  else if ( options.position == "auto" ) {
    wnd.css("top", window.mouseMoveEvent.clientY);
    wnd.css("left", window.mouseMoveEvent.clientX);
  }

  if ( options.iframe ) {
    wnd.find(".content").append( $("<iframe></iframe>").attr( { src : options.url }) );
    options.loadCallback.apply(wnd, [options]);
    options.displayCallback.apply($("#"+divID), [options]);
  } else if ( options.url ) {
    if ( options.url[0] == "#" ) {
      if ( $(options.url).length > 0 )
        wnd.find(".content").empty().append($(options.url).children().clone());
      options.loadCallback.apply(wnd, [options]);
      options.displayCallback.apply(wnd, [options]);
    } else {
      if ( options.loadCache && PopWindow.cache[options.url] ) {
        wnd.find(".content").append( PopWindow.cache[options.url] );
        options.displayCallback.apply($("#"+divID), [options]);
      } else {
        $.ajax({
            url: options.url,
            global: false,
            complete: function( xhr, status ) {

              if ( status == 'error' )
              {
                if ( $(xhr.responseText, "#container").length > 0 )
                {
                  wnd.find(".content").append($(xhr.responseText).filter("#container"));
                }
                else
                {
                  alert( xhr.responseText );
                }
                return;
              }

              wnd.find(".content").html( xhr.responseText );

              if ( options.loadCache )
                PopWindow.cache[options.url] = response;

              options.loadCallback.apply(wnd, [options]);

              options.displayCallback.apply($("#"+divID), [options]);
              
              if ( tabs_enabled )
                  wnd.find(".title .popwnd_tab:eq(0)").click()
            }
        });
      }
    }
  }

  $("div.title", wnd).disableSelection();

  wnd.trigger("raise");

  if ( options.modal )
    wnd.before($("<div class='modal_overlay' id='modal_"+divID+"'></div>").css("zIndex", wnd.css("zIndex")));

  return wnd;
}

/**
 * 默认参数设置
 * @namespace 弹出窗口的默认参数对象
 */
PopWindow.defaultOptions = {
  /**
   * 窗口ID，此ID并不是窗口DOM对象的ID，窗口DOM对象的ID最终会加上一个popwnd_的前缀。
   * @type string
   */
  id : 0,

  /**
   * 窗口数据源地址，若为NULL则不加载。
   * @type string
   */
  url : null,

  /**
   * 窗口标题
   * @type string
   */
  title : "",

  /**
   * 窗口宽度，默认单位为px。
   * @type mixed
   */
  width : -1,

  /**
   * 窗口高度，默认单位为px。
   * @type mixed
   */
  height : -1,


  /**
   * 窗口打开位置。<br/>
   * auto = 在鼠标位置打开 <br/>
   * center = 在屏幕中间打开 <br/>
   * {x:n, y:n} = 让窗口在指定的位置x,y打开
   * @type string
   */
  position : "center",

  /**
   * Http请求得到响应后的回调函数
   * @event
   */
  loadCallback : new Function,

  /**
   * 关闭时调用
   * @event
   */
  cancelCallback : new Function,

  /**
   * 显示时调用
   * @event
   */
  displayCallback : new Function,

  /**
   * 是否先关闭已打开的相同ID的窗口
   * @type Boolean
   */
  overwrite : true, // Overwrite another window that have same ID.

  /**
   * 关闭有子窗口的窗口时是否进行提示
   * @type Boolean
   */
  subWindowClosePrompt :false,

  /**
   * 是否用iframe打开目标地址
   * @type Boolean
   */
  iframe : false,

  /**
   * 是否只加载第一次（以后直接从缓存地区）
   * @type Boolean
   */
  loadCache : false,

  /*
   * 是否启用对话框模式，覆盖其他窗口
   * @type Boolean
   */
  modal : false,

  /**
   * 是否关闭不销毁该窗口而仅仅隐藏
   * @type Boolean
   */
  persist : false,
  
  tabs : []
};

PopWindow.cache = {};

PopWindow.lock = function( lock_by_class, wnd )
{
    $(wnd||this).find(">div.overlay").show();
    
    if ( lock_by_class )
        $(wnd||this).addClass(lock_by_class).data( "lock_by_class", lock_by_class );
}

PopWindow.unlock = function( wnd )
{
    $(wnd||this).find(">div.overlay").hide();
    
    if ( $(wnd||this).data( "lock_by_class" ) )
        $(wnd||this).removeClass($(wnd||this).data( "lock_by_class" )).data( "lock_by_class", null );
}
