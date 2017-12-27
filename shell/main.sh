#!/bin/bash
#------------------------------------------------------------------
#Filename:      main.sh
#Date:          2017/12/25
#Author:        Marin
#Description:   Process handler
#Copyright:   2017 (c) Marin
#-------------------------------------------------------------------
#Version:   1.0
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH


#check current user is root
if [ $(id -u) != "0" ];then
    echo "Error:current user is not root!,please use  root to run"
    exit 1
fi

cur_dir=$(pwd)
parent_cur_dir="${cur_dir}/../"
Stack=$1
main_dir="${parent_cur_dir}main/"
. include/common.sh
. include/ConsumerMaster.sh
. include/PandaTaskServer.sh
. include/subscribleMaster.sh

phpcmd=/usr/local/php-7.0.13/bin/php
php_prefix="$phpcmd -r ";
version="`$php_prefix  'echo  PHP_VERSION;'`"

ini_file="${parent_cur_dir}env.ini"
#check env.ini is exists
if [ ! -s "${ini_file}" ]; then
    Echo_Red "env.ini was not exists"
    exit 1
fi
#check worker is exists
if [ "`ls -AL $main_dir`" == "" ];then
    Echo_Green "The ${main_dir} is empty!,nothing to do!"
    exit 1
fi
#check php version is PHP7
if test ${version%%.*} -lt 7; then
    Echo_Red "PHP is require >=7"
    exit 1
fi

#check swoole version is more than 1.9.0
swoole_v="`${phpcmd} --ri swoole |grep Version |awk '{print $3}'`"
sw_head_v=${swoole_v%%.*}
sw_v_tmp=${swoole_v%.*}
sw_mid_v=${sw_v_tmp##*.}
sw_tail=${swoole_v##*.}

if test ${sw_head_v} -lt 1
then
    Echo_Red "swoole version is to low A ,require >1.9.0"
    exit 1
fi

if test ${sw_head_v} -gt 1 -0  ${sw_mid_v} -lt 9
    then
        Echo_Red "swoole version is to low B ,require >1.9.0"
    exit 1
    fi

clear
echo "+------------------------------------------------------------------------+"
echo "|          TaskProcess For Linux Server, Written by Marin                |"
echo "+------------------------------------------------------------------------+"
echo "|                             version 1.0                                |"
echo "+------------------------------------------------------------------------+"
echo "|                  For more information please cat readme.md             |"
echo "+------------------------------------------------------------------------+"
echo "=========================================================================="

#file_real_path=$(readlink -f "$main_dir")
index=1
array_file_process=()
for file in $(readlink -f "${main_dir}/*")
do

    if [ -f $file ]
    then
        file_Name=${file##*/}
        #读取文件后缀,排除其他文件出现
        file_ext=${file##*.}
        if test ${file_ext} = 'php' ;then
            array_file_process[${index}]=${file_Name%%.*}
        let "index++"
        fi

    fi
done
#echo ${array_file_process[2]}
#archive process name array
arlen=${#array_file_process[*]}
You_Choice=""
Echo_Yellow "You have ${arlen} options for your run."
for data  in $(seq 1 ${#array_file_process[*]})
do
#    echo "${arlen} ${data}  ${array_file_process[$data]}";
    if  [ ${data} -le ${arlen} -a ${data} -ge 1 ]
    then
        echo "$data: ${array_file_process[${data}]}"
        if test ${data} -eq ${arlen};
        then
           read -p "Enter your choice number (1-$arlen):" You_Choice
        fi
    fi
done

read -p "Enter you will to do (start|stop):" Action_Wil



#获取进程参数
#${array_file_process[${You_Choice}]}

iniRealPath="$(readlink -f ${ini_file})"
mainProcessRealPath="$(readlink -f ${main_dir})/"

case  ${Action_Wil} in

"start")
handler_script ${array_file_process[${You_Choice}]} ${Action_Wil} ${iniRealPath} ${mainProcessRealPath}
    ;;
"stop")
#handler_script ${array_file_process[${You_Choice}]} ${Action_Wil} "${iniRealPath}"
    ;;
"reboot")
#handler_script ${array_file_process[${You_Choice}]} ${Action_Wil} "${iniRealPath}"
    ;;
esac


