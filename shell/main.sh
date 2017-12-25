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

phpcmd=php
php_prefix="$phpcmd -r ";
version="`$php_prefix  'echo  PHP_VERSION;'`"
#check env.ini is exists
if [ ! -s "${parent_cur_dir}env.ini" ]; then
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
    Echo_Red "swoole version is to low ,require >1.9.0"
    exit 1
else
    if test ${sw_mid_v} -lt 9
    then
        Echo_Red "swoole version is to low ,require >1.9.0"
    exit 1
    fi
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
index=0
array_file_process=()
for file in $(readlink -f "${main_dir}/*")
do
    if [ -f $file ]
    then
        file_Name=${file##*/}
        array_file_process[${index}]=${file_Name%%.*}
        let "index++"
    fi
done
#echo ${array_file_process[2]}
#获取需要启动的脚本进程名称
for data  in ${array_file_process[@]}
do
    echo "$data";
done

echo "1: ConsumerMaster"
echo "2: PandaTaskServer"
echo "3: subscribleMaster"



#获取进程参数



case ${Stack} in

"start")

    ;;
"stop")
    ;;
"reboot")
    ;;
esac