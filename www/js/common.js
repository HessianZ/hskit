
!function($) {

    "use strict"; // jshint ;_;


    /* MODAL CLASS DEFINITION
     * ====================== */

    var PopModal = function(element, options) {
        var mH = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        mH *= 0.8;
        mH = parseInt(mH);
        this.options = options
        this.$element = $(element)
        this.$modal = this.create().modal(options).one('show', function() {
            var modal = $(this);
            $("<div style='display: none'></div>").load(options.url, function() {
                modal.find(".modal-body>div").replaceWith($(this).fadeIn(300, function() {
                    typeof options.onload == 'function' && options.onload.call(modal);
                    $(this).find(":text,:password").first().focus()
                }));
                if(modal.find(".modal-body>div").height() > mH)
                    modal.find(".modal-body>div").css({'max-height': mH, 'overflow-y':'scroll'});
            });
        }).on("shown", function() {
            $(this).find(":text,:password").first().focus()
        })

        options.width && this.$modal.css({
            'width': options.width,
            'margin-left': function () {
                return -($(this).width() / 2);
            }
        });

        options.destroy && this.$modal.on('hidden', function() {
            $(element).data("popmodal", null);
            $(this).remove();
        })

        
           // options.height && this.$modal.css('height', options.height).find(".modal-body").css({'max-height': mH, 'overflow-y':'scroll'});
    }

    PopModal.prototype = {
        constructor: PopModal


        , create: function() {
            var html = '<div class="modal ' + this.options.effect + ' hide ">'
                    + '<div class="modal-header">' +
                    '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                    '<h3>' + this.options.title + '</h3>' +
                    '</div>' +
                    '<div class="modal-body" ><div class="text-center"><img src="/img/loading_bar.gif" /></div></div>' +
                    //'<div class="modal-footer">'+
                    //'<a href="javascrip:void(0)" class="btn btn-primary">确定</a>'+
                    //'<a href="javascrip:void(0)" class="btn" data-dismiss="modal">取消</a>'+
                    //'</div>'+
                    '</div>',
                    modal = $(html);

            return modal;
        }

        , proxy: function(option) {
            return this.$modal.modal(option)
        }


    }


    /* MODAL PLUGIN DEFINITION
     * ======================= */

    var old = $.fn.popModal

    $.fn.popModal = function(option) {
        return this.each(function() {
            var $this = $(this)
                , data = $this.data('popmodal')
                , options = $.extend({}, $.fn.popModal.defaults, $this.data(), typeof option == 'object' && option)

            if (!data)
                $this.data('popmodal', (data = new PopModal(this, options)))
            data.proxy(option)
        })
    }

    $.fn.popModal.defaults = {
        destroy: true,
        effect: '',
        width: 560,
        height: 'auto',
        onload: null,
    }

    $.fn.popModal.Constructor = PopModal


    /* MODAL NO CONFLICT
     * ================= */

    $.fn.popModal.noConflict = function() {
        $.fn.popModal = old
        return this
    }


    /* MODAL DATA-API
     * ============== */

    $(document).on('click.popmodal.data-api', '[data-toggle="popmodal"]', function(e) {
        var $this = $(this)
                , href = $this.attr('href')
                , title = $this.attr('title')
                , option = $this.data('modal') ? 'toggle' : $.extend({url: href, title: title}, $this.data())

        e.preventDefault()

        $this.popModal(option)
                .one('hide', function() {
                    $this.focus()
                })
    })

}(window.jQuery);

function secondToDateTime(second)
{
    var date = new Date(second * 1000);
    return date.getFullYear() + '-' +
            ('00' + (date.getMonth() + 1)).slice(-2) + '-' +
            ('00' + date.getDate()).slice(-2) + ' ' +
            ('00' + date.getHours()).slice(-2) + ':' +
            ('00' + date.getMinutes()).slice(-2) + ':' +
            ('00' + date.getSeconds()).slice(-2);
}

function secondFieldToDateTime(field)
{
    var val = this[field.field];
    if (val == 0)
        return "-";
    return secondToDateTime(val);
}

function long2ip(ip) {
    // http://kevin.vanzonneveld.net
    // +   original by: Waldo Malqui Silva
    // *     example 1: long2ip( 3221234342 );
    // *     returns 1: '192.0.34.166'
    if (!isFinite(ip))
        return false;

    return [ip >>> 24, ip >>> 16 & 0xFF, ip >>> 8 & 0xFF, ip & 0xFF].join('.');
}


function setCookie(name, value, expires, path, domain) {
    var j = [name + '=' + encodeURIComponent(value)];
    if (0 !== parseInt(expires, 10)) {
        var expDate = new Date;
        expires = expires || ((new Date(expDate.getFullYear(), expDate.getMonth(), expDate.getDate() + 1) - expDate) / 1000); // default 1 day
        expDate.setTime(expDate.getTime() + (expires * 1000));
        j.push('expires=' + expDate.toGMTString())
    }
    j.push('path=' + (path || '/'));
    if (domain)
        j.push('domain=' + domain);
    document.cookie = j.join('; ')
}

function getCookie(name) {
    return decodeURIComponent((document.cookie.match(new RegExp('(?:^| )' + name + '(?:(?:=([^;]*))|;|$)', 'i')) || [])[1] || '')
}


$(document).ajaxError(function(event, jqxhr, settings, exception) {
    alert(exception + "\n\n" + jqxhr.responseText);
});
