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

     process_prefix=`trimStr ${process_prefix}`
     process_master=`trimStr ${process_master}`
     process_worker=`trimStr ${process_worker}`
     worker_num=`trimStr ${worker_num}`

#     echo ${process_prefix}
     system_process_master_name=$process_prefix':'${process_master}

#    echo $system_process_master_name
     system_process_worker_name=${process_prefix}":"${process_worker}
     #//检查进程是否存在,如果存在,则什么都不做

     master_process_num=`ps -ef |grep -v 'grep'|grep ${system_process_master_name}|wc -l`
     worker_process_num=`ps -ef |grep -v 'grep'|grep ${system_process_worker_name}|wc -l`


     #check master process number is Whether abnormal,if it is abnormal,that kill it`s worker process
     process_master_id=`ps -ef | grep -v 'grep'|grep ${system_process_master_name}|awk '{print $2}'`


     master_kill_flag=0
     if test ${master_process_num} -gt 1;
     then
        Y_SELECT=''
        Echo_Red "PandaTaskServer 主进程数异常,请求主动kill掉所有的 master 进程!"
        read -p "请选择 (y or n):" Y_SELECT
        if test ${Y_SELECT} = '';then
            Y_SELECT='y'
        fi

        if test ${Y_SELECT} = 'y'; then
            for pid in ${process_master_id}

            do
            Echo_Red "Kill the ${system_process_master_name} [${pid}]"
            kill -9 ${pid}
            Echo_Green "Already kill ${pid}"
            echo "==========================================================================="
            done
            master_kill_flag=1

        fi

     fi
     work_process_id=`ps -ef | grep -v 'grep'|grep ${system_process_worker_name}|awk '{print $2}'`

    #if master process is exits ,the worker process is also exits
     worker_kill_flag=0
     if test ${master_kill_flag} -eq 1
     then
        W_SELECT=''
        Echo_Red "PandaTaskServer 主进程数异常,请求主动kill掉所有的 master 进程!"
        read -p "请选择 (y or n),default(y):" W_SELECT
        if test ${W_SELECT} = '';then
            W_SELECT='y'
        fi

        if test ${W_SELECT} = 'y'; then
            for w_pid in ${work_process_id}
            do
                Echo_Red "Kill the ${system_process_worker_name} [${w_pid}]"
                kill -9 ${w_pid}
                Echo_Green "Already kill ${w_pid}"
                echo "==========================================================================="
            done
        worker_kill_flag=1
        fi
     fi




        if test ${master_process_num} = 1 -a ${worker_num} = ${worker_process_num}
        then

            #进程正常运行中,请求是否重启
            REBOOT_CHOICE=''
            Echo_Green "the process is running now ,Does it need to be restarted"
            read -p "input y or n,default(y):" REBOOT_CHOICE
                    if test ${REBOOT_CHOICE} = ''
                     then
                        REBOOT_CHOICE='y'
                    fi
            Echo_Red "准备先杀掉主进程！"
            kill -9 ${process_master_id}
            Echo_Green "Already kill the master[${process_master_id}]"
            for pid in ${work_process_id}
            do
                kill -9 ${pid}
                Echo_Green "Kill worker[${pid}]"
            done

            nohup ${phpcmd} ${theProcessFilePath} >> pandaTaskServer.log &

        else
            nohup ${phpcmd} ${theProcessFilePath} >> pandaTaskServer.log &
        fi





}