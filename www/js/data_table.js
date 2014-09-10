/**
 * @fileOverview 数据表格程序，允许通过AJAX获取表格数据进行快速渲染。
 * @author G.K[zhouhaisheng@eastarnet.com]
 */

/**
 * 创建一个数据表格列。
 * @class 数据表格列。
 * @constructor
 * @param {String} field 字段名，#号开始表示非数据库字段
 * @param {String} text 列头显示文字
 * @param {Mixed} width 表格列宽度
 * @param {Mixed} dataMap Object for enum | Function to build by function | String for expression
 * @param {Boolean} visible 是否可见
 *
 * @author Hessian<mail@hessian.me>
 */
var DataTableColumn = function(field, text, width, dataMap, visible)
{
    /**
     * 字段名，#号开始表示非数据库字段
     * @type String
     */
    this.field = field;

    /**
     * 列头显示文字
     * @type String
     */
    this.text = text;

    /**
     * 表格列宽度
     * @type Mixed
     */
    this.width = width;

    /**
     * dataMap Object for enum | Function to build by function | String for expression
     * @type Mixed
     */
    this.dataMap = dataMap;

    /**
     * 是否可见
     * @type Boolean
     */
    this.visible = typeof visible != "undefined" ? visible && true : true; // default true
}

/**
 * 创建一个用于加载远程数据的表格。
 * @class AJAX数据表格类
 * @constructor
 * @param {String} dtId 表格对象ID
 *
 * @author Hessian<mail@hessian.me>
 */
var DataTable = function(dtId)
{
    var _self = this,
            _dataSource,
            _containerSelector,
            _params = "",
            _paramsObject = {},
            _columns = [],
            _orders = {},
            _data = {},
            _start = 0,
            _count = 15,
            _width = null,
            _id = dtId || "_datatable_",
            _loadSuccessEvent = null,
            _rowClickEvent = null,
            _rowDblClickEvent = null,
            _afterRenderEvent = null,
            _multiple = false,
            _multipage = true,
            _row_id_template = "",
            _layout = 'auto',
            _ischeck = true,
            _more = true,
            _field = '',
            _tableColumns = [];

    DataTable.instances[_id] = this;

    /**
     * 注意：此方法为内部方法，如果需要对load事件进行侦听请参看 setLoadSuccessEvent。<br/>
     * 数据加载完成后会调用此方法。如果未指定LoadSuccessEvent会直接进行渲染，
     * 否则需要在回调函数中自己调用DataTable.render()进行渲染。
     * @param {Object} response HTTP请求返回的对象
     * @see DataTable.setLoadSuccessEvent()
     * @event
     */
    var loadSuccessCallback = function(response)
    {

        if (typeof response != "object") {
            alert(response);
        } else if (response.status != "ok") {
            alert(response.status + " : " + response.data);
        } else if (typeof _loadSuccessEvent == "function") {
            _data = response.data;
            _loadSuccessEvent.apply(_self, [_data]);
        } else {
            _data = response.data;
            _self.render();
        }

        $('#' + _id).siblings('div.dt_loading').remove();
    },

    setCookie = function (name,value){
        document.cookie = name + "="+ escape (value) + ";path=" + window.location.pathname;
    },

    getCookie = function (name){
        var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
        if(arr=document.cookie.match(reg))
            return (arr[2]);
        else
            return null;
    };

    /**
     * 设置请求URL。
     * 注意：最好不要带问号参数，如：?id=xxx,如有需要应使用DataTable.setParams进行设置。
     * @param {String} url 目标地址
     */
    this.setDataSource = function(url)
    {
        _dataSource = url;
    }

    /**
     * 设置用于容纳表格的容器的选择器。
     * @param {String} jQuerySelector jQuery选择器
     * @example
     * dt.setContainer( "#dtContainer" );
     */
    this.setContainer = function(jQuerySelector)
    {
        _containerSelector = jQuerySelector;
    }


    /**
     * 添加一个数据列。
     * @param field 字段名，#号开始表示非数据库字段
     * @param text 列头显示文字
     * @param width 表格列宽度
     * @param dataMap Object for enum | Function to build by function | String for expression
     * @param visible 是否显示
     * @example
     *   dt.addColumn("id", "ID", 30);
     *   dt.addColumn("name", "品名", 100);
     *   dt.addColumn("price", "单价", 30);
     *   dt.addColumn("count", "数量", 30);
     *   dt.addColumn("enabled", "是否有效", 50, {y:"是", n:"否"}); // Enumeration
     *   dt.addColumn("#totalprice","总价",40, function(column){ return this.price * this.count; }); // Function
     *   dt.addColumn("#operation","操作",40, "&lt;a href='#' onclick='removeCompany($$id);'&gt;删除&lt;/a&gt;"); // Expression
     */
    this.addColumn = function(field, text, width, dataMap, visible)
    {
        if (typeof field != "string" || typeof text != "string")
            return;

        _columns.push(new DataTableColumn(field, text, width, dataMap, visible));
    }

    /**
     * 添加一个隐藏字段，该字段只添加到数据对象中，不在表格中显示。
     * @param field 字段名
     */
    this.addHideColumn = function(field)
    {
        if (typeof field != "string")
            return;

        _columns.push(new DataTableColumn(field, null, 0, null, false));
    }

    /**
     * 设置可以排序的字段。
     * @param field 字段名
     * @param dir 排序方向 asc=升序（默认），desc=降序
     */

    this.setSortableColumns = function(arr){
        _tableColumns = arr;
    }

    /**
     * 设置排序字段。
     * @param field 字段名
     * @param dir 排序方向 asc=升序（默认），desc=降序
     */
    this.setOrderField = function(field, dir) {
        _orders = {};
        if (typeof field == 'object') {
            _orders = field;
            return;
        }
        _field = field;
        _orders[field] = dir == "desc" ? "desc" : "asc";

    }

    /**
     * 设置请求参数。请求参数被更改会当前页号被重置。
     * @param queryString 参数字符串，如：first=test&second=none&third=ok
     */
    this.setParams = function(queryString)
    {
        _params = queryString;
        _start = 0;

        _paramsObject = {};
        var params = _params.split("&");

        for (var i = 0; i < params.length; i++) {
            var para = params[i].split("=");
            _paramsObject[para[0]] = para[1];
        }
    }

    this.getParams = function( )
    {
        return _params;
    }

    this.getParam = function(paraName)
    {
        return _paramsObject[paraName];
    }

    /**
     * 设置表格每页显示行数。
     * @param count 显示行数
     */
    this.setLoadCount = function(count)
    {
        var userCount = getCookie('LoadCount'+_id);
        if(userCount)
            _count = parseInt(userCount);
        else
            _count = parseInt(count);
    }

    this.setMultiSortable = function(y){
        _more = y && true;
    }

    /**
     * 设置表格宽度，该参数会设置table标签的width属性。
     * @param width 宽度
     */
    this.setWidth = function(width)
    {
        _width = width;
    }

    /**
     * 设置表格布局, auto | fixed。
     * @param layout 布局
     */
    this.setLayout = function(layout)
    {
        _layout = layout;
    }

    /**
     * 设置表格是否支持单击选中。
     * @param {Boolean} v true|false
     */
    this.setSelectable = function(c){
        _ischeck = c && true;
    }

    /**
     * 设置表格是否支持多选。
     * @param {Boolean} v true|false
     */
    this.setMultiple = function(v)
    {
        _multiple = v && true; // force convert
    }

    /**
     * 设置表格是否支持分页。
     * @param {Boolean} v true|false
     */
    this.setMultipage = function(v)
    {
        _multipage = v && true; // force convert
    }

    this.setRowIdTemplate = function(template)
    {
        _row_id_template = template;
    }

    // ^^^^^^^^^^^^^^^^^^^^^^^^ Event Listener ^^^^^^^^^^^^^^^^^^^^^^^^ \\
    /**
     * 设置TR的单击事件回调函数
     * @param callback function
     * @event
     */
    this.setRowClickEvent = function(callback)
    {
        _rowClickEvent = callback;
    }

    /**
     * 设置TR的双击事件回调函数
     * @param callback function
     * @event
     */
    this.setRowDblClickEvent = function(callback)
    {
        _rowDblClickEvent = callback;
    }

    /**
     * 设置表格数据加载完成事件回调函数
     * @param callback function
     * @event
     */
    this.setLoadSuccessEvent = function(callback)
    {
        _loadSuccessEvent = callback;
    }

    /**
     * 设置表格渲染完成事件回调函数
     * @param callback function
     * @event
     */
    this.setAfterRenderEvent = function(callback)
    {
        _afterRenderEvent = callback;
    }
    // vvvvvvvvvvvvvvvvvvvvvvvv Event Listener vvvvvvvvvvvvvvvvvvvvvvvv //

    /**
     * 加载表格数据。
     * @param start 记录开始偏移量 默认为上次执行的值
     * @param count 读取数量 默认为上次执行的值
     */
    this.loadData = function(start, count)
    {
        $(_containerSelector).css('position', 'relative').append('<div class="dt_loading"></div>')

        if (typeof start != "undefined")
            _start = parseInt(start);

        if (typeof count != "undefined")
            _count = parseInt(count);

        var postData = {"fields[]": [], start: _start, count: _count, orders: _orders};

        if (_columns.length > 0)
            for (var i = 0; i < _columns.length; i++)
                if (_columns[i].field[0] != "#")
                    postData["fields[]"].push(_columns[i].field);

        var queryString = (_params ? _params + "&" : "") + $.param(postData);

        $.post(_dataSource, queryString, loadSuccessCallback, "json");
    }

    /**
     * 使用上次取数据的参数重新进行加载。
     */
    this.reload = function()
    {
        this.loadData(_start);
    }

    /**
     * 获取选中行的数据。
     * @return Array
     */
    this.getSelectedDatas = function()
    {
        var ret = [];

        $("table#" + _id).find("tr.selected").map(function() {
            ret.push(_data.rows[$(this).attr("idx")]);
        });

        return ret;
    }

    /**
     * 获取选中的第一行的数据。
     * @return object
     */
    this.getSelectedData = function()
    {
        return _data.rows[$("table#" + _id).find("tr.selected:first").attr("idx")];
    }

    /**
     * 获取全部数据
     * @return object
     */
    this.getTotalData = function()
    {
        return _data.rows;
    }
    this.getData = function()
    {
        return _data;
    }
    /**
     * 渲染。生成表格HTML并插入指定的container。
     */
    this.render = function()
    {
        var html = "<table cellspacing='0' cellpadding='0' class='list_table table table-hover table-bordered table-striped' id='" + _id + "'";
        var columnCount = 0;

        if (_width != null)
            html += " width='" + _width + "' ";

        html += " style='width:" + _width + "'>";

        // 表头
        html += "<thead>";
        html += "<tr>";
        for (var i = 0; i < _columns.length; i++) {
            if (_columns[i].visible) {

                var order = "";
                for(var k = 0, tl = _tableColumns.length; k < tl; k++){
                    if (_more && _columns[i].field == _tableColumns[k]) 
                    order = "icon-minus";
                }
                
                if (_orders[_columns[i].field] == "asc" && _columns[i].field == _field)
                    order = "icon-chevron-up";
                else if (_orders[_columns[i].field] == "desc" && _columns[i].field == _field)
                    order = "icon-chevron-down";

                if (order)
                    order = '<i class="order ' + order + ' pull-right"></i>';

                
                var width = _columns[i].width ? "width='" + _columns[i].width + "'" : "";
                html += "<th " + width + " field=" + _columns[i].field + ">" + _columns[i].text + order + "</th>";

                columnCount++;
            }
        }
        html += "</tr></thead><tbody></tbody></table>";

        var table = $(html);

        if(_more){
            table.find('.order').each(function(i){
                var cl;
                $(this).hover(function(){
                    cl = $(this).attr("class");
                    if(cl == "order icon-chevron-down pull-right" || cl == "order icon-chevron-up pull-right")
                        return;
                    
                    $(this).attr("class", "order icon-chevron-down pull-right");
                },function(){
                    $(this).attr("class", cl);
                })
            })
        }

        if (_layout != 'auto')
            table.css("table-layout", _layout);

        delete html;

        if (_data.rows)
            for (var i = 0; i < _data.rows.length; i++)
            {

                var ROW_NO = i + 1;
                var ROW_NO_EX = _start + i + 1;

                var id = "";

                if (_row_id_template) {
                    id = _row_id_template.replace(/\$\$(\w+)/g, function() {
                        if (arguments[1] == 'ROW_NO')
                            return ROW_NO;
                        else if (arguments[1] == 'ROW_NO_EX')
                            return ROW_NO_EX;
                        else if (_data.rows[i][arguments[1]])
                            return _data.rows[i][arguments[1]];
                        else
                            return "";
                    });

                    if (id != "")
                        id = "id='" + id + "'"
                }

                var tr = $("<tr class='' idx='" + i + "' " + id + "></tr>");
                for (var j = 0; j < _columns.length; j++) {
                    var currentColumn = _columns[j];

                    if (!currentColumn.visible)
                        continue;

                    var td = $("<td></td>");

                    switch (currentColumn.field) {
                        case "##ROW_NO":
                            td.append(ROW_NO);
                            break;
                        case "##ROW_NO_EX":
                            td.append(ROW_NO_EX);
                            break;
                    }

                    if (currentColumn.dataMap) {
                        switch (typeof currentColumn.dataMap) {
                            case "string":
                                td.append(currentColumn.dataMap.replace(/\$\$(\w+)/g, function() {
                                    if (arguments[1] == 'ROW_NO')
                                        return ROW_NO;
                                    else if (arguments[1] == 'ROW_NO_EX')
                                        return ROW_NO_EX;
                                    else if (typeof _data.rows[i][arguments[1]] != 'undefined')
                                        return _data.rows[i][arguments[1]];
                                    else
                                        return "";
                                }));
                                break;

                            case "function":
                                td.append(currentColumn.dataMap.apply(_data.rows[i], [currentColumn, _paramsObject]));
                                break;

                            case "object":
                                var currentColumnCopy = new Object();
                                $.each(currentColumn.dataMap, function(key, value) {
                                    currentColumnCopy[key] = value.replace(/\$\$(\w+)/g, function() {
                                        if (_data.rows[i][arguments[1]])
                                            return _data.rows[i][arguments[1]];
                                        else
                                            return "";
                                    });
                                })
                                td.append(currentColumnCopy[_data.rows[i][currentColumn.field]]);
                                break;
                        }
                    }
                    else
                        td.append(_data.rows[i][currentColumn.field]);

                    tr.append(td);
                }

                table.append(tr);
            }

        // 分页
        if (_multipage && _data.count > 0) { // is set and available( great then  0 )
            // 表底
            table.append("<tfoot><tr><td colspan='" + columnCount + "'>" + this.buildPaginationHtml() + "</td></tr></tfoot>");
        }

        table.find("thead th .order").click(function() {
            var $this = $(this);

            if ($this.hasClass("icon-chevron-up"))
                _self.setOrderField($this.parent().attr("field"), "desc");
            else if ($this.hasClass("icon-chevron-down"))
                _self.setOrderField($this.parent().attr("field"), "asc");
            else{
                return;
            }

            _self.reload();
        });

        table.find("tbody tr").click(function(e) {
            var tagName = e.target.tagName.toLowerCase();
            if (tagName == "input" || tagName == "button" || tagName == "a")
                return;

            if(!_ischeck) {
                return;
            }

            if (!_multiple) {
                $(this).toggleClass("selected");
                $(this).siblings(".selected").removeClass("selected");
            } else
                $(this).toggleClass("selected");

            if (_rowClickEvent)
                _rowClickEvent.apply(this, [_data.rows[$(this).attr("idx")]]);
        });

        table.find("tbody tr").dblclick(function(e) {

            var tagName = e.target.tagName.toLowerCase();
            if (tagName == "input" || tagName == "button" || tagName == "a")
                return;

            if (!_multiple) {
                $(this).siblings(".selected").removeClass("selected");
                $(this).addClass("selected");
            } else
                $(this).toggleClass("selected");

            if (_rowDblClickEvent)
                _rowDblClickEvent.apply(this, [_data.rows[$(this).attr("idx")]]);
        });

        //添加设置按钮
        var set = "<div class='set_data' title='设置'><i class='icon-edit'></i></div>",
        setdata = $(set), t;

        setdata.css({position:"absolute", width:"14px", height:"14px", display:"none", top:"-20px", left: _width + "px", cursor:"pointer"});
    
        table.hover(function() {
            var width = $(this).width() - 14;
            setdata.css('left', width + "px");
            clearTimeout(t);
            setdata.show(200);

        },function() {

            t = setTimeout(function() {
                setdata.hide(200);
            },1000)

            setdata.hover(function() {
                clearTimeout(t);
            },function() {

                t = setTimeout(function() {
                    setdata.hide(200);
                },1000)

            })
        })

        //添加弹出框
        var form = '<form class="form-horizontal"><div class="control-group"><label class="control-label">显示数量：</label><div class="controls" style="width:270px;"><input type="text" class="shownum" placeholder="请输入正整数" value="'+ _count +'" /></div></div><div class="control-group"><label class="control-label">表格宽度：</label><div class="controls" style="width:270px;"><input type="text" class="tablewidth" placeholder="请输入宽度，以%或px为单位！" value="'+ _width +'" /></div></div><div class="control-group"><input type="button" class="enter btn btn-primary" name="enter" onclick="DataTable.getInstance(\'' + _id + '\').enter({\'num\':$(\'.shownum\').val(), \'width\':$(\'.tablewidth\').val()})" value="确定" style="width:60%" /></form>';
        
        setdata.click(function(){
            $(this).popModal({
                title: '表格设置'
            })
            $(".modal div.text-center").html(form);
        })

        this.enter = function (arg){
            if(arg.num == "" || parseInt(arg.num) <= 0){
                alert("显示数量请填入正整数！")
                return;
            }

            if(arg.width.slice(-1) != '%' && arg.width.slice(-2) != "px" || arg.width == ''){
                alert("表格宽度值请填入数字加单位%或px！")
                return;
            }

            $('.modal:visible').modal('hide');
            
            setCookie('LoadCount'+_id, arg.num);

            this.setLoadCount(arg.num);

            this.setWidth(arg.width);
            table.css("width",arg.width);
            this.reload();
        }
        
        $(_containerSelector).empty();
        $(_containerSelector).append(setdata);
        $(_containerSelector).append(table);
        if (_afterRenderEvent)
            _afterRenderEvent.apply(table, [_data]);
    }

    

    /**
     * 根据_start 和 _count 以及服务端返回的总数生成分页HTML。
     * @return String
     */
    this.buildPaginationHtml = function( )
    {
        var page_no = parseInt(_start / _count) + 1;
        var page_size = _count;
        var total_count = _data.count;

        // 总页数
        var total_pages = 1;
        var button_count = 4;

        if (total_count > page_size)
            total_pages = Math.ceil(total_count / page_size);

        if (page_no > total_pages)
            page_no = total_pages;

        var start_page = 1;
        var end_page = total_pages;
        //如果 page_no < 11 ，则从第一页开始显示 20 页

        // 如果页数小于20 则全部显示
        if (total_pages <= (button_count * 2 + 1))
        {
        }
        else if (page_no < (button_count + 1))
        {
            end_page = (button_count * 2 + 1);
        }
        else if ((total_pages - page_no) < (button_count + 1))
        {
            start_page = total_pages - (button_count * 2);
        }
        else
        {
            start_page = page_no - button_count;
            end_page = page_no + button_count;
        }

        var html = "<div id='pagenation' class='pagination text-center'><ul class='links'>";

        //如果start_page不是第一页，显示 首页
        if (start_page > 1)
        {
            html += "<li><a href=\"javascript:DataTable.getInstance('" + _id + "').goPage(1)\">首页</a></li>";
        }
        for (var i = start_page; i <= end_page; i++)
        {
            if (i == page_no)
                html += "<li class='active'><a href='javascript:void(0)'>" + i + "</a></li>";
            else
                html += "<li><a href=\"javascript:DataTable.getInstance('" + _id + "').goPage(" + i + ")\">" + i + "</a></li>";
        }
        //如果end_page不是最后页，显示 尾页
        if (end_page < total_pages)
        {
            html += "<li><a href=\"javascript:DataTable.getInstance('" + _id + "').goPage(" + total_pages + ")\">末页</a></li>";
        }

        html += "</ul>";
        if (total_pages > button_count)
            html += '<div class="input-append jump"><input type="text" placeholder="页码" onkeypress="event.keyCode==13&&DataTable.getInstance(\'' + _id + '\').goPage(this.value)" /><a href="javascript:void(0)" onclick="DataTable.getInstance(\'' + _id + '\').goPage($(this).prev().val())" class="btn"><i class="icon-chevron-right"></i></a></div>';
        html += "<div class='counts'>(共 " + total_count + " 项 , " + total_pages + " 页)</dvi></div>";

        return html;
    }

    this.getId = function()
    {
        return _id;
    }

    this.goPage = function(page)
    {
        var page_no = (parseInt(page) || 1) - 1;
        var page_size = _count;
        var total_count = _data.count;
        var total_pages = 1;
        if (total_count > page_size)
            total_pages = Math.ceil(total_count / page_size);

        page_no = Math.min(page_no, total_pages - 1);
        page_no = Math.max(page_no, 0);

        this.loadData(page_no * page_size);
    }

}

/**
 * 每产生一个新的表格都会在这里注册，从而支持多个实例。
 */
DataTable.instances = {};

/**
 * 通过ID获取一个DataTable实例
 * @param id DataTable的实例ID
 * @return DataTable
 */
DataTable.getInstance = function(id)
{
    return DataTable.instances[id];
}

DataTable.DATA_HANDLE_ENABLED = function(url, enabledValue, enableColumn) {
    return function(  ) {

        if (typeof enabledValue == "undefined") {
            enabledValue = 1;
        }

        if (typeof enableColumn == "undefined") {
            enableColumn = "enabled";
        }

        var $this = this;
        var a = $("<a href=\"javascript:void(0)\" class=\"status \"><i class='icon-ok " + (this[enableColumn] == enabledValue ? "" : "icon-remove") + "'></i></a>").click(function() {
            if (!confirm("请二次确认！！！"))
                return;

            $.post(url, {id: $this.id}, function(ret) {
                if (ret.status == "ok")
                    a.find('i').toggleClass("icon-remove");
                else
                    alert(ret.data);
            });

        });
        return a;
    };
}
