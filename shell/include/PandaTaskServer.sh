#!/bin/bash
#pandaTaskServer进程处理器


PandaTaskServer()
{

     action=$1
     inifile=$2
     main_path=$3
     theProcessFilePath="${main_path}PandaTaskServer.php"
     process_prefix=`readIni ${inifile} "panda_server_section" "panda_process"`
     process_master=`readIni ${inifile} "panda_server_section" "panda_process_master"`
     process_worker=`readIni ${inifile} "panda_server_section" "panda_process_child"`
     worker_num=`readIni ${inifile} "panda_server_section" "max_process_num"`

     system_process_master_name=${process_prefix}:${process_master}
     system_process_worker_name=${process_prefix}:${process_worker}
     #//检查进程是否存在,如果存在,则什么都不做
     master_process_num=`ps -ef |grep -v 'grep'|grep ${system_process_master_name}|wc -l`
     worker_process_num=`ps -ef |grep -v 'grep'|grep ${system_process_worker_name}|wc -l`
    check ${worker_num}


     #check master process number is Whether abnormal,if it is abnormal,that kill it`s worker process
     process_master_id=`ps -ef | grep -v 'grep'|grep ${system_process_master_name}|awk '{print $2}'`

     master_kill_flag=0
     if test ${master_process_num} -gt 1;
     then
        for pid in ${process_master_id}

        do
            echo "PandaTaskServer 主进程数异常,主动kill掉所有的 master 进程!"
            echo "Kill the ${system_process_master_name} [${pid}]"
            kill -9 ${pid}
        done
        master_kill_flag=1
     fi
     work_process_id=`ps -ef | grep -v 'grep'|grep ${system_process_worker_name}|awk '{print $2}'`

    #if master process is exits ,the worker process is also exits
     worker_kill_flag=0
     if test ${master_kill_flag} -eq 1
     then
        for w_pid in ${work_process_id}
        do
            echo "PandaTaskServer_worker 主进程数异常,主动kill掉所有的工作进程!"
            echo "Kill the ${system_process_worker_name} [${pid}]"
            kill -9 ${w_pid}
        done
        worker_kill_flag=1

     fi

    if test ${master_process_num} -eq 1 -a ${worker_num} = ${worker_process_num}
    then
        echo "准备杀掉主进程！"
        kill -9 ${process_master_id}
        for pid in ${work_process_id}
        do
            kill -9 ${pid}
        done
    fi

    nohup ${phpcmd} ${theProcessFilePath} >> pandaTaskServer.log &

}