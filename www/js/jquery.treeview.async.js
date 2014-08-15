/*
 * Async Treeview 0.1 - Lazy-loading extension for Treeview
 * 
 * http://bassistance.de/jquery-plugins/jquery-plugin-treeview/
 *
 * Copyright (c) 2007 Jörn Zaefferer
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Revision: $Id$
 *
 */

;(function($) {

function load(settings, root, child, container) {
  var rndUrl = settings.url + "?random="+Math.random();
	$.getJSON(rndUrl, {root: root}, function(response) {
		function createNode(parent) {
			var current = $("<li/>").attr("id", this.id || "").html("<span>" + this.text + "</span>").appendTo(parent);
			if (this.classes) {
				current.children("span").addClass(this.classes);
			}
			if (this.expanded) {
				current.addClass("open");
			}

      // Add click callback by K
      // Date : 2010-04-02 17:14
      if (settings.nodeClick) {
        $("span",current).bind("click", this, function( event ) {
          event.stopPropagation();
          $(".currentNode",container).removeClass("currentNode");
          $(this).addClass("currentNode");
          settings.nodeClick( event.data, current );
        });
      }

      // Bind json data to element.
      current.data("nodeData", this);

			if (this.hasChildren || this.children && this.children.length) {
				var branch = $("<ul/>").appendTo(current);
				if (this.hasChildren && ! this.children ) {
					current.addClass("hasChildren");
					createNode.call({
						text:"placeholder",
						id:"placeholder",
						children:[]
					}, branch);
				}
				if (this.children && this.children.length) {
					$.each(this.children, createNode, [branch])
				}
			}
		}
		$.each(response, createNode, [child]);
    $(container).treeview({add: child});

    if ( settings.onload ) 
      settings.onload(  );

    });
}

var proxied = $.fn.treeview;
$.fn.treeview = function(settings) {
	if (!settings.url) {
		return proxied.apply(this, arguments);
	}
	var container = this;
  load(settings, settings.source || "source", this, container);
  var userToggle = settings.toggle;
  var treeObj = proxied.call(this,$.extend({},settings,{
    collapsed : true,
    toggle : function() {
      var $this = $(this);
      if ( $this.hasClass("hasChildren") ) {
        var childList = $this.removeClass("hasChildren").find("ul");
        childList.empty();
        load(settings, this.id, childList, container);
      }
      if ( userToggle ) {
        userToggle.apply(this, arguments);
      }
    }
  })).bind({
   reload : function(event,node) {
      var childList = $(node).find("ul");
      childList.empty();
      load(settings, $(node).attr("id"), childList, container);
    }
  });

  return treeObj;
};

})(jQuery);
