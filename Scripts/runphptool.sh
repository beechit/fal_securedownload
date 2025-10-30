#!/usr/bin/env bash

if [[ -x $(which "podman-compose") ]]; then
    composecommand="podman-compose"
else
    composecommand="podman compose"
fi

export PHP_VERSION

mkdir -p $TOOL_DIR
$composecommand run --rm tools composer req --working-dir=$TOOL_DIR --dev -W $TOOL_PACKAGE
$composecommand run --rm tools $TOOL_DIR/vendor/bin/$TOOL_COMMAND "$@"
result=$?

$composecommand down
exit $result
