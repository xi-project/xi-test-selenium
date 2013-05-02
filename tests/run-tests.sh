#!/bin/bash
cd `dirname "$0"`

SIGNALS="HUP KILL INT ABRT STOP QUIT SEGV TERM"

cleanup() {
    trap - $SIGNALS

    if [ -n "$SELENIUM_PID" ]; then
        echo "Shutting down Selenium server"
        kill $SELENIUM_PID
    fi

    exit
}

trap cleanup $SIGNALS

if [ "$1" = '-n' ]; then
    shift
else
    rm -f selenium.log  # if we didn't, we could race to the grep before the old file is replaced
    ./selenium-server.sh > selenium.log 2>&1 &
    SELENIUM_PID=$!
    echo "Started Selenium server, pid = $SELENIUM_PID"

    echo -n "Waiting for Selenium server to get up"
    until `grep -q "Started org.openqa.jetty.jetty.Server" selenium.log`; do
        sleep 0.5
        echo -n "."
    done
    echo
fi

../vendor/bin/phpunit $@

cleanup
