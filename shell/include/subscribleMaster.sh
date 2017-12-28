#!/bin/bash

function subscribleMaster
{
     #//检查进程是否存在,如果存在,则什么都不做
     inifile=$2
     main_path=$3
     theProcessFilePath="${main_path}subscribleMaster.php"
     process_prefix=`readIni ${inifile} "clearCache_section" "panda_process"`
     process_master=`readIni ${inifile} "clearCache_section" "clear_master_name"`
     process_worker=`readIni ${inifile} "clearCache_section" "redis_cache_clear"`
     worker_num=`readIni ${inifile} "clearCache_section" "worker_num"`

     process_prefix=`trimStr ${process_prefix}`
     process_master=`trimStr ${process_master}`
     process_worker=`trimStr ${process_worker}`
     worker_num=`trimStr ${worker_num}`


     system_process_master_name=$process_prefix':'${process_master}


#    echo $system_process_master_name
     system_process_worker_name=${process_prefix}":"${process_worker}
     #//检查进程是否存在,如果存在,则什么都不做

     master_process_num=`ps -ef |grep -v 'grep'|grep ${system_process_master_name}|wc -l`
     worker_process_num=`ps -ef |grep -v 'grep'|grep ${system_process_worker_name}|wc -l`
     #check master process number is Whether abnormal,if it is abnormal,that kill it`s worker process

     process_master_id=`ps -ef | grep -v 'grep'|grep ${system_process_master_name}|awk '{print $2}'`

     action=$1
     case ${action} in
     "start")

             #todo 如果进程已经存在则给出提示,先stop 在start
             if test ${master_process_num} -ge 1
             then
                Y_SELECT=''
                Echo_Red "The process is running now !,you will reboot it?"
                read -p "请选择 (y or n):" Y_SELECT
                if test ${Y_SELECT} = ""
                then
                    Y_SELECT='y'
                fi

                if test ${Y_SELECT} = 'y'
                then
                    for pid in ${process_master_id}
                    do
                        Echo_Red "Kill the ${system_process_master_name} [${pid}]"
                        kill -9 ${pid}
                        Echo_Green "Already kill master[${pid}].................."
                    done
                    work_process_id=`ps -ef | grep -v 'grep'|grep ${system_process_worker_name}|awk '{print $2}'`
                    for wid in ${work_process_id}
                    do
                        Echo_Red "Kill the ${system_process_worker_name} [${wid}]"
                        kill -9 ${wid}
                        Echo_Green "Already kill worker[${pid}].................."
                    done
                    #重启
                    nohup ${phpcmd} ${theProcessFilePath} >> subscribleMaster.log &
                    Echo_Green "重启..............OK"
                else
                    echo "nothing to do !"
                    exit 1;
                fi

             elif test ${worker_process_num} -ge 1
                then
                work_process_id=`ps -ef | grep -v 'grep'|grep ${system_process_worker_name}|awk '{print $2}'`
                Y_SELECT=''
                Echo_Yellow "Kill the not normal worker!,and reboot it?"
                read -p "请选择 (y or n):" Y_SELECT

                if test ${Y_SELECT} = '';then
                    Y_SELECT='y'
                fi

                if test ${Y_SELECT} = 'y'
                then
                    for wid in ${work_process_id}
                    do
                        Echo_Red "Kill the ${system_process_worker_name} [${wid}]"
                        kill -9 ${wid}
                        Echo_Green "Already kill worker[${pid}].................."
                    done
                else
                    echo "nothing to do "
                    exit 1;
                fi

             else

                #重启
                nohup ${phpcmd} ${theProcessFilePath} >> subscribleMaster.log &
                Echo_Green "重启..............OK"
             fi
        ;;
     "stop")
        if test ${master_process_num} -ge 1
             then
             #杀完主进程再杀子进程

                for pid in ${process_master_id}
                do
                    Echo_Red "Kill the ${system_process_master_name} [${pid}]"
                    kill -9 ${pid}
                    Echo_Green "Already kill master[${pid}].................."
                done
                work_process_id=`ps -ef | grep -v 'grep'|grep ${system_process_worker_name}|awk '{print $2}'`
                for wid in ${work_process_id}
                do
                    Echo_Red "Kill the ${system_process_worker_name} [${wid}]"
                    kill -9 ${wid}
                    Echo_Green "Already kill worker[${pid}].................."
                done

                echo "The process is stoped！"
                exit 1;

         #杀僵尸进程
         elif test ${worker_process_num} -ge 1
                then
                work_process_id=`ps -ef | grep -v 'grep'|grep ${system_process_worker_name}|awk '{print $2}'`
                for wid in ${work_process_id}
                do
                    Echo_Red "Kill the ${system_process_worker_name} [${wid}]"
                    kill -9 ${wid}
                    Echo_Green "Already kill worker[${pid}].................."
                done
         else
            Echo_Green "nothing to do!"
            exit 1
        fi
        ;;
     esac
}