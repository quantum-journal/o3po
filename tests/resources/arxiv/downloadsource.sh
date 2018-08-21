#!/bin/bash
wget --user-agent="Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:21.0) Gecko/20100101 Firefox/21.0" "https://arxiv.org/e-print/$1" && mv "$1" "$1.tar.gz"
