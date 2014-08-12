/**
 * 图片滚动加载(延时加载)
 * @author      tangpan
 * @version     1.0
 * {@link   www.frontu.net}
 */
var imgLazyLoad = {
     
    /**
     * 配置对象，初始化是传入，src表示图片的真实地址在img中的属性
     * time 表示延时加载时间
     */
    imgConfig : null,
     
    /**
     * 图片数组对象，将所有需要延时加载的Img标签全部存入数组中
     */
    docImgs : new Array(),
     
    /**
     * 已经加载的图片
     */
    hasLoadImg : [],
     
    init : function( _options )
    {
        this.imgConfig = (arguments.length == 0)?{src:'name',time:300,filter:null}:{src:_options.src,time:_options.time,filter:_options.filter};
        //获取所有的img标签
        this.docImgs = document.images;
        //获取需要延时加载的图片
        if ( typeof this.imgConfig.filter === 'function' )
        {
            imgs = [];
            for ( var i = 0; i < this.docImgs.length; i++ )
            {
                if ( this.imgConfig.filter(this.docImgs[i]) )
                    imgs.push(this.docImgs[i]);
            }
            this.docImgs = imgs;
        }
         
        var that = this;
        //添加滚动事件
        window.onscroll = function()
        {
            setTimeout(function(){
                that.loadImg();
            },that.imgConfig.time);
        }
         
        setTimeout(function(){
            that.loadImg();
        },that.imgConfig.time);
    },
     
    /**
     * 格式化css属性
     * 如：把 background-color 转换成 backgroundColor
     */
    cameLize : function(str)
    {
        return str.replace(/-(\w)/g,function(str_match,s){
            return s.toUpperCase();
        });
    },
     
    /**
     * 获取css样式
     */
    getStyle : function(element,property)
    {
        if ( arguments.length != 2 )    return false;
        var value = element.style[this.cameLize(property)];
        if ( !value )
        {
            if ( document.defaultView && document.defaultView.getComputedStyle )
            {
                var css = document.defaultView.getComputedStyle(element,null);
                value = css ? css.getPropertyValue(property) : null;
            }
            else if ( element.currentStyle )
            {
                value = element.currentStyle[this.cameLize[property]];
            }
        }
        return value == 'auto' ? '' : value;
         
    },
     
    /**
     *加载图片
     */
    loadImg : function()
    {
        if ( this.docImgs.length == 0 )
        {
            window.onscroll = null;
            return;
        }
         
        //滚动条与页面顶部的高度
        var offsetPage = window.pageYOffset ? window.pageYOffset : window.document.documentElement.scrollTop;
         
        var offsetWindow = offsetPage + Number(window.innerHeight ? window.innerHeight : document.documentElement.clientHeight);
         
        var _len = this.docImgs.length;
        if ( _len <= 0 || _len == undefined )   return;
         
        for ( var i = 0; i < _len; i++ )
        {
            if ( this.hasLoadImg[i] != undefined )  continue;
             
            //获取属性
            var attrSrc = this.docImgs[i].getAttribute(this.imgConfig.src);
            var o = this.docImgs[i];
            var _tagName = o.nodeName.toLowerCase();
             
            if ( o )
            {
                //图片与页面顶部的高度
                var postPage = o.getBoundingClientRect().top + window.document.documentElement.scrollTop + window.document.body.scrollTop; 
                 
                var postWindow = postPage + Number(this.getStyle(o, 'height').replace('px',''));
 
                //判断是否符合加载图片的条件
                if ( postPage > offsetPage && postPage < offsetWindow ||
                    postWindow > offsetPage && postWindow < offsetWindow)
                {
                    if ( _tagName === "img" && attrSrc !== null )
                    {
                        o.setAttribute('src',attrSrc);
                        o.removeAttribute(attrSrc);
                        this.hasLoadImg[i] = o;
                    }
                    o = null;
                }
            }
        }    
    }
};
 
imgLazyLoad.init({src:'name',time:300, filter:function(node){
    if ( node.className != 'tp_lazy_loader_img' ) return false;
    return true; 
}}); 
