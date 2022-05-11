//全局效果
$(function($) {
	//初始化状态
	var routeTag = $('.advanced-search-btn').attr('data-route');

	//搜索框状态
	var searchData = {};

	if(Cookies.get('advanced_search_status')) {
	    var searchData = jQuery.parseJSON(Cookies.get('advanced_search_status'));
	    $('.advanced-search-box').css('display', ((searchData[routeTag] == 'none')?'none':'block'));
	}

	$('.advanced-search-btn').on('click', function() {
	    var status = 'none';
	    if(searchData[routeTag] == 'none') {//取反
	        var status = 'block';
	    }
	    searchData[routeTag] = status;
	    
	    Cookies.set('advanced_search_status', searchData, { expires: 1 });//有效1天
	    $('.advanced-search-box').css('display', status);
	});

	//滚动条定位
	var scrollData = {};

	if(Cookies.get('table_box_scroll')) {
	    var scrollData = jQuery.parseJSON(Cookies.get('table_box_scroll'));
	    $('.table-box').scrollLeft(scrollData[routeTag]);
	}

	$('.table-box').on('scroll', function(e) {
	    scrollData[routeTag] = $(this).scrollLeft();
	    Cookies.set('table_box_scroll', scrollData, { expires: 1 });//有效1天
	});
	
	
	/* 与bootstrap相关 */
	// tooltips
	$('.tooltip-demo').tooltip({
	    selector: "[data-toggle=tooltip]",
	    container: "body"
	});
	
	// 使用animation.css修改Bootstrap Modal
	$('.modal').appendTo("body");

	$("[data-toggle=popover]").popover({
		html: true,
		trigger: 'hover click'
	});


	//折叠ibox
	$('.collapse-link').click(function () {
	    var ibox = $(this).closest('div.ibox');
	    var button = $(this).find('i');
	    var content = ibox.find('div.ibox-content');
	    content.slideToggle(200);
	    button.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
	    ibox.toggleClass('').toggleClass('border-bottom');
	    setTimeout(function () {
	        ibox.resize();
	        ibox.find('[id^=map-]').resize();
	    }, 50);
	});
	//关闭ibox
	$('.close-link').click(function () {
	    var content = $(this).closest('div.ibox');
	    content.remove();
	});
	/* 与bootstrap相关 end */
});



//全局函数：子窗口iframe操作父窗口的tabs
function createParentTab(url, index, name) {
	var str = '<a href="javascript:;" class="active J_menuTab" data-id="' + url + '">' + name + ' <i class="fa fa-times-circle"></i></a>';
    $('.J_menuTab', parent.document).removeClass('active');

    // 添加选项卡对应的iframe
    var str1 = '<iframe class="J_iframe" name="iframe' + index + '" width="100%" height="100%" src="' + url + '" frameborder="0" data-id="' + url + '" seamless></iframe>';
    $('.J_mainContent', parent.document).find('iframe.J_iframe').hide().parents('.J_mainContent').append(str1);

    //显示loading提示
    var loading = window.parent.layer.load();

    $('.J_mainContent iframe:visible', parent.document).load(function () {
        //iframe加载完成后隐藏loading提示
        window.parent.layer.close(loading);
    });
    // 添加选项卡
    $('.J_menuTabs .page-tabs-content', parent.document).append(str);
}

//全局函数：子窗口iframe操作父窗口的tabs （删除所有tabs）
function removeAllParentTab() {
    $('.page-tabs-content').children('[data-id]').not(":first").each(function(){
        $('.J_iframe[data-id="'+$(this).data("id")+'"]').remove();$(this).remove()
    });
    $('.page-tabs-content').children("[data-id]:first").each(function(){
        $('.J_iframe[data-id="'+$(this).data("id")+'"]').show();$(this).addClass("active")
    });
    $('.page-tabs-content').css("margin-left","0");
}


//yii.js的命名空间约定【demo】
yii.admin = (function($) {
    var pub = {
        isActive: true,
        init: function() {
        	//
        },
        test: function()
        {
        	//console.log('test');
        }
    };
    
    return pub;//公共js对象
})(jQuery);

yii.admin.test();//模板化开发js


//全局刷新
yii.global = (function($) {
    var pub = {
        isActive: true,
        indexCount: 0,//tab面面计数器
        init: function() {
        	//
        },
        refresh: function()
        {
        	$('.ibox-tools .btn-refresh').one('click', function() {
        		$(this).append('<i class="fa fa-refresh fa-spin fa-fw"></i>').attr({'disabled': 'disabled'});
        	});
        },
        xbutton: function()
        {
        	/*
        	$("button[type='submit']").one('click', function(submit) {
        		var this_ = $(this);
        		//submit.preventDefault();
        		this_.prop('disabled', true);//1.先禁止
        		this_.submit();//2.然后使用js执行提交，整个表单就处在提交中，而不能再次点击！
        		//延迟1秒解开禁用
    		    //console.log('开始禁止并延时2秒');
    		    var t = setTimeout(function() {
    		    	//console.log('解开按钮');
    		    	this_.prop('disabled', false);//2秒钟以后自动解开
    		    }, 2000);
        	});
        	*/
        },
        jumpTab: function(url, text, index)
        {
        	this.indexCount += index;
        	//console.log(this.indexCount);
        	window.createParentTab(url, this.indexCount, text);
        },
        mesCount: function()
        {
        	var info = $('.sidebar-title.head-info a');
        	if(info.length < 1) {
        		return false;
        	}
        	$.ajax({
                url: info.data('url'),
                type: 'GET',
                dataType: 'json',
                data: {},
                success: function(data) {
                    if(data.state) {
                    	if(data.msg > 0) {
                    		info.removeClass('label-success').addClass('label-danger');
                    	} else {
                    		info.removeClass('label-danger').addClass('label-success');
                    	}
                    	info.text(data.msg);
                    }
                }
            });
        		
        	//循环处理
        	setTimeout('yii.global.mesCount()', 8000);
        },
    };
    
    return pub;
})(jQuery);

yii.global.refresh();//全局刷新状态
yii.global.xbutton();//全局禁止重复提交
// yii.global.mesCount();//周期性自动获取消息总数，暂时不做socket推送舒服

yii.format = (function($){
    var pub = {
        /*
            @doc 格式化 yii 返回回来的错误信息
            @example console.log(yii.format.error(res['error']).join('<br/>'))
            @MAKE 新增是否登录的判断
            @return array 字符串组成的错误数组
         */
        error: function (errorArray) {
            var err = [];
            for (var key in errorArray) {
                err.push(errorArray[key]);
            }
            return err;
        }
    };

    return pub;
})(jQuery);
