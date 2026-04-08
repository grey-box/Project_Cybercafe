#!/usr/bin/env bash

create_chain() {
    iptables -t mangle -N "$2"
}

add_rule() {
    echo "iptables $*"
}

delete_rule() {
    echo "iptables $*"
}
