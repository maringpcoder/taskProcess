## ConsumerMaster ##
php kafka 消费者进程
## PandaTaskServer ##
队列处理进程
## subscribleMaster ##
php redis 订阅进程
##package
<code>"nmred/kafka-php": "0.2.*"</code>
##php extension##
swoole1.9.2+
## 环境要求 ##
1. PHP 版本大于 5.5
2. Kafka Server 版本大于 0.8.0
3. 消费模块 Kafka Server 版本需要大于 0.9.0
4. redis 3.2+

##相关配置##
配置文件所处路径：ROOT_PATH/.inv
    
    ;消费kafka消息队列进程
    [log_section]
    panda_process = 'panda_process'
    ;kafka日志队列处理主进程名称
    broker_list ='172.16.61.176:9092'
    ;kafka日志队列处理主进程名称
    master['log_master_name']='log_kafka_master'
    ;kafka worker进程名称
    children['log_kafka']='log_kafka_worker'
    ;日志处理进程数5个
    log_handler['worker_num']=5
    ;删除过期缓存进程的配置节点

    [clearCache_section]
    panda_process= 'panda_process_cache'
    ;kafka日志队列处理主进程名称
    master['clear_master_name']='subscrible_clear_master'
    ;redis缓存处理进程名称
    children['redis_cache_clear']='subscrible_clear_worker'
    ;缓存清空所开子进程数
    cache_clear_handler['worker_num']=1

    ;redis服务器配置
    [redis_env_section]
    ;redis链接类型,如果有配置,会先考虑socket链接redis (^0^)
    redis_kv_expire['socket_type']=''
    ;redis主机配置
    redis_kv_expire['host']='172.16.61.100'
    ;redis密码配置
    redis_kv_expire['password']=''
    ;端口号
    redis_kv_expire['port']=6379
    ;链接超时时间
    redis_kv_expire['timeout']=0.0
    ;redis队列服务器配置
    redis_list['socket_type']=''
    redis_list['host']='172.16.61.100'
    redis_list['password']=''
    redis_list['port']=6379
    redis_list['timeout']=0.0
    ;业务缓存配置
    redis_ar['socket_type']=''
    redis_ar['host']='172.16.61.100'
    redis_ar['password']=''
    redis_ar['port']=6379
    redis_ar['timeout']=0.0
    
    ;队列进程相关配置
    [panda_server_section]
    panda_process='panda_process'
    panda_process_master='clear_redis_list_master'
    panda_process_child='clear_redis_list_worker'
    max_process_num=2


</code>

	