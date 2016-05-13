# billfeller
easyphp是一个轻量级php框架

1. 统一请求入口json.php，支持mod/act路由策略，如：/json.php?mod=mod&act=act&params=xxx；
2. loadClass.php自动加载weblib目录下API类；
3. 提供多项轻量级功能
    1. Template.php模板类，兼容Smarty；
    2. Logger.php日志类；
    3. Env.php提供XSS过滤方法；
    4. FileCache.php本地缓存文件类；
