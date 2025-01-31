#!/bin/bash
kill $(cat websocket.pid)
rm websocket.pid 