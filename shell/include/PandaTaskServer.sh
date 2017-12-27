#!/bin/bash
#pandaTaskServer进程处理器
PandaTaskServer()
{

     action=$1
     inifile=$2
     main_path=$4
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



     #check master process number is Whether abnormal,if it is abnormal,that kill it`s worker process
     process_master_id=`ps -ef | grep -v 'grep'|grep ${system_process_master_name}|awk '{print $2}'`

     master_kill_flag=0
     #检查是否有僵尸进程,如果有那么回收


     if test ${master_process_num} -ne 1;
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
     if test ${master_kill_flag} -eq 1 -o [ ${master_process_num} -eq 0 -a ${worker_process_num} ];
     then
        for w_pid in ${work_process_id}
        do
            echo "PandaTaskServer_worker 主进程数异常,主动kill掉所有的 master 进程!"
            echo "Kill the ${system_process_worker_name} [${pid}]"
            kill -9 ${w_pid}
        done
        worker_kill_flag=1
     fi

     #if master_process_num eq 1 and worker number is normal ,choice reboot,otherwise Direct start
    if test ${master_process_num} -eq 1 -a ${worker_process_num} -eq ${worker_num}; then
        SELECT_ACTION=""
        read -p "the process is running now !,you otherwise will to restart!,input (yes | no)" SELECT_ACTION
        if test ${SELECT_ACTION} -eq "yes" ;
        then
            nohup ${phpcmd} ${theProcessFilePath} >> pandaTaskServer.log &
        else
            echo "you are exit !"
            exit 1;
        fi
    fi


}