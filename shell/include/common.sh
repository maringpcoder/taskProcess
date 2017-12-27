#!/bin/bash

Color_Text()
{
  echo -e " \e[0;$2m$1\e[0m"
}


Echo_Red()
{
  echo $(Color_Text "$1" "31")
}

Echo_Green()
{
  echo $(Color_Text "$1" "32")
}

Echo_Yellow()
{
  echo $(Color_Text "$1" "33")
}

handler_script()
{
    action=$2
    server=$1
    case $server in
    "ConsumerMaster")
        ConsumerMaster ${action} $3
    ;;
    "PandaTaskServer")
        PandaTaskServer ${action} $3
    ;;
    "subscribleMaster")
        subscribleMaster ${action} $3
    ;;
    esac


}

readIni() {
    file=$1
    section=$2
    item=$3
    val="$(awk -F '=' '/\['${section}'\]/{a=1} (a==1 && "'${item}'"==$1){a=0;print $2}' ${file})"
    echo $val
}