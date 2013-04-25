#!/bin/bash
cd `dirname "$0"`

if [ "$1" != '-n' ]; then
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

../vendor/bin/phpunit

if [ -n "$SELENIUM_PID" ]; then
    echo "Shutting down Selenium server"
    kill $SELENIUM_PID
fi
