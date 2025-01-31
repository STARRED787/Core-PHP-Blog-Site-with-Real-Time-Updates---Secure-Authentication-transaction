#!/bin/bash
php server.php > websocket.log 2>&1 &
echo $! > websocket.pid 