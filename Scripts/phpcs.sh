#!/usr/bin/env bash

TOOL_DIR=.Build/tools/phpcs
TOOL_PACKAGE="friendsofphp/php-cs-fixer"
TOOL_COMMAND="php-cs-fixer fix -v --diff"

source Scripts/runphptool.sh
