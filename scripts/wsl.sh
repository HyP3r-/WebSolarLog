#!/bin/bash
# WebSolarLog start and stop script

PHPSCRIPTNAME="server.php"

WHICH=/usr/bin/which
PHP=$($WHICH php)
NOHUP=$($WHICH nohup)
PGREP=$($WHICH pgrep)
DIRNAME=$($WHICH dirname)
CAT=$($WHICH cat)
SCRIPTDIR="$( cd "$( $DIRNAME "${BASH_SOURCE[0]}" )" && pwd )"
PIDFOLDER=$SCRIPTDIR
LOGFILE=$SCRIPTDIR"/queueserver.log"

cd $SCRIPTDIR

RESULT="NO"
is_running(){
        RESULT="NO"
        if [ ! -f $PIDFOLDER"/"$PHPSCRIPTNAME".pid" ] 
        then
          return
        fi

        kill -0 `$CAT $PIDFOLDER"/"$PHPSCRIPTNAME".pid"` 2> /dev/null
        if [ "$?" -eq "1" ]
        then
          return
	else
		FOUND=`find $PIDFOLDER -name "$PHPSCRIPTNAME".pid -newermt '2 minute ago'`
		if [ "$FOUND" == "" ]
		then
	                kill -9 `$CAT $PIDFOLDER"/"$PHPSCRIPTNAME".pid"` 2> /dev/null
			rm -f $PIDFOLDER"/"$PHPSCRIPTNAME".pid"
			return
		fi
        fi

        RESULT="YES"
}

looping ()
{ 
	while [ "true" ] # To infinity ... and beyond!
	do
		is_running
		if [ "$RESULT" = 'NO' ]
		then
		        echo "not running, starting"
		        $NOHUP $PHP $PHPSCRIPTNAME >> $LOGFILE &
		fi
        sleep 10 # Wait for 10 seconds
	done
}

is_running
case $1 in
start)
	if [ "$RESULT" = 'YES' ]
	then
        echo "WebSolarLog is already started"
    else
        looping &
        echo "Starting WebSolarLog.."
    fi
;;
stop)
     if [ "$RESULT" = 'YES' ]
     then
        kill -9 `$CAT $PIDFOLDER"/"$PHPSCRIPTNAME".pid"` 2> /dev/null
#	sleep 3
	#ps awux |grep -v grep | grep `$CAT $PIDFOLDER"/"$PHPSCRIPTNAME".pid"` > /dev/null
	kill -0 `$CAT $PIDFOLDER"/"$PHPSCRIPTNAME".pid"` 2> /dev/null
	if [ "$?" -eq "1" ]
	then
        	rm -f $PIDFOLDER"/"$PHPSCRIPTNAME".pid"
	else
		kill -9 `$CAT $PIDFOLDER"/"$PHPSCRIPTNAME".pid"` 2> /dev/null
		rm -f $PIDFOLDER"/"$PHPSCRIPTNAME".pid"
	fi
     else
        echo "WebSolarLog is not running"
     fi
     kill `$PGREP wsl.sh` &
     exit 0
;;
status)
	if [ "$RESULT" = 'YES' ]
	then
		echo "WebSolarLog is running"
	else
		echo "WebSolarLog is not running"
	fi
	exit 0
;;
*)
	echo .
	echo "Welcome to WebSolarLog
	echo .
	echo Usage : simply run as root $SCRIPTDIR/wsl.sh { start | stop | status }"
;;
esac
exit 0