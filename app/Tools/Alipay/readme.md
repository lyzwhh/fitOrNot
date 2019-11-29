#支付宝接入
- 部分参考：https://www.cnblogs.com/lishanlei/p/8946316.html 
- composer.json 中autoload：classmap：下加入包 ， 执行composer dump-autoload 
- config下的alipay.php ， env设置
- 更改以下文件中的encrypt和decrypt函数名，解决冲突
  - aop/AopEncrypt.php
  
  - aop/AopClient.php
  
   - lotusphp_runtime/Cookie/Cookie.php
- AopClient 中注释掉第一行

- invalid [default store dir]: /tmp/ (0)错误 参考 ：https://www.cnblogs.com/yangzailu/p/11752330.html